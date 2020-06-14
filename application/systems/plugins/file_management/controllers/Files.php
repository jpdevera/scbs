<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Files extends SYSAD_Controller {
	
	public $module;
	public $table_id;
	public $path;
	public $module_js;
	public $player_js;

	public $view_per 			= FALSE;
	public $edit_per 			= FALSE;
	public $add_per 			= FALSE;
	public $delete_per 			= FALSE;

	public $dt_options 			= array();
	private $date_now;

	private $table_columns 		= array(
		'a.file_id', 'a.file_name', 'a.display_name', 'a.original_name', 'a.description',
		'a.file_type', 'a.created_date', 'a.file_extension', 'a.module_code', 'a.created_by',
		'DATE_FORMAT(a.created_date,"%b %d, %Y") as created_date_format',
		'DATE_FORMAT(a.modified_date,"%b %d, %Y") as modified_date_format',
		'd.version_file_name', 'd.version', 'd.file_version_ids', 'm.visibility_id',
		'm.user_id as visible_user_id'
	);

	private $table_filter 		= array(
		'a-display_name'
	);

	private $table_order 		= array(
		'a.display_name', 'created_fullname', 'created_date'
	);

	public $module_dir_map 		= array(
		MODULE_FILE 			=> PATH_FILE_UPLOADS,
		// MODULE_USER 			=> PATH_FILE_USER_UPLOADS
	);

	public $file_type_dir_map 	= array(
		FILE_TYPE_DOCUMENTS		=> DIRECTORY_DOCUMENTS,
		FILE_TYPE_VIDEOS		=> DIRECTORY_VIDEOS,
		FILE_TYPE_AUDIOS		=> DIRECTORY_AUDIOS,
		FILE_TYPE_IMAGES		=> DIRECTORY_IMAGES,
	);

	public $show_view 		= array(
		'pdf', 'docx', 'xlsx', 'pptx'
	);
	
	public function __construct()
	{
		parent::__construct();
		
		$video_ext 			= explode(',', VIDEO_EXTENSIONS);
		$audio_ext 			= explode(',', AUDIO_EXTENSIONS);

		$this->show_view 	= array_merge( $this->show_view, $video_ext );
		$this->show_view 	= array_merge( $this->show_view, $audio_ext );

		$this->module 			= MODULE_FILE;
		$this->module_js 		= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_FILE_MANAGEMENT."/files";
		$this->player_js 		= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_FILE_MANAGEMENT."/player";
		
		$this->load->model('Files_model', 'files_mod');
		$this->load->model('File_versions_model', 'file_versions');
		$this->load->model( 'Access_rights_model', 'access_rights_mod' );

		$this->load->module( CORE_FILE_MANAGEMENT.'/Access_rights' );

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );

		$this->date_now 		= date('Y-m-d H:i:s');

		$bmname 	= aes_crypt('b.mname', FALSE,FALSE);
		$bfname 	= aes_crypt('b.fname', FALSE,FALSE);
		$blname 	= aes_crypt('b.lname', FALSE,FALSE);
		$cmname 	= aes_crypt('c.mname', FALSE,FALSE);
		$cfname 	= aes_crypt('c.fname', FALSE,FALSE);
		$clname 	= aes_crypt('c.lname', FALSE,FALSE);

		$this->table_columns[] 	= "IF( IFNULL(".$bmname.", '') != '',  
			CONCAT( ".$bfname.",' ',".$bmname.",' ',".$blname." ),
			CONCAT( ".$bfname.",' ',".$blname." )
		) as created_fullname";

		$this->table_columns[] 	= "IF( IFNULL(".$cmname.", '') != '',  
			CONCAT( ".$cfname.",' ',".$cmname.",' ',".$clname." ),
			CONCAT( ".$cfname.",' ',".$clname." )
		) as modified_fullname";

		$this->table_filter[] 	= "IF( IFNULL(".$bmname.", '') != '',  
			CONCAT( ".$bfname.",' ',".$bmname.",' ',".$blname." ),
			CONCAT( ".$bfname.",' ',".$blname." )
		) convert_to created_fullname";

		$this->table_filter[] 	= 'DATE_FORMAT(a.created_date,"%b %d, %Y") convert_to created_date_format';
		$this->table_filter[] 	= 'd.version';
	

		$this->dt_options 		= array(
			'table_id'		=> 'documents_dt_table',
			'path'			=> CORE_FILE_MANAGEMENT.'/Files/get_files_list',
			'advanced_filter' 	=> true,
			'with_search'		=> true,
			'post_data'			=> array(
				
			),
			'custom_option_callback'	=> 'Files.datatable_custom_option(options);',
			'order'			=> 2,
			'sort_order'	=> 'desc'
		);

		$this->dt_version_options = array(
			'table_id'		=> 'version_dt_table',
			'path'			=> CORE_FILE_MANAGEMENT.'/Files/get_file_version_list',
			'advanced_filter' 	=> true,
			'with_search'		=> true,
			'post_data'			=> array(
				
			),
			'modal'			=> '#modal_version_list'
		);
	}
	
	public function index()
	{	
		$data 						= array();
		$resources 					= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);			


			$module_salt_token 	= $this->generate_salt_token_arr( $this->module );

			$module_arr 		= array(
				'module'		=> $module_salt_token['id_enc'],
				'module_salt'	=> $module_salt_token['salt'],
				'module_token'	=> $module_salt_token['token']
			);
			
			$document 			= $this->generate_salt_token_arr( FILE_TYPE_DOCUMENTS );

			$document_arr 		= array(
				'file_type'			=> $document['id_enc'],
				'file_type_salt'	=> $document['salt'],
				'file_type_token'	=> $document['token']
			);

			$document_arr 		= array_merge( $document_arr, $module_arr );

			$document_json 		= json_encode( $document_arr );

			$images 			= $this->generate_salt_token_arr( FILE_TYPE_IMAGES );

			$images_arr 		= array(
				'file_type'			=> $images['id_enc'],
				'file_type_salt'	=> $images['salt'],
				'file_type_token'	=> $images['token']
			);

			$images_arr 		= array_merge( $images_arr, $module_arr );

			$images_json 		= json_encode( $images_arr );

			$videos 			= $this->generate_salt_token_arr( FILE_TYPE_VIDEOS );

			$videos_arr 		= array(
				'file_type'			=> $videos['id_enc'],
				'file_type_salt'	=> $videos['salt'],
				'file_type_token'	=> $videos['token']
			);

			$videos_arr 		= array_merge( $videos_arr, $module_arr );

			$videos_json 		= json_encode( $videos_arr );

			$audios 			= $this->generate_salt_token_arr( FILE_TYPE_AUDIOS );

			$audios_arr 		= array(
				'file_type'			=> $audios['id_enc'],
				'file_type_salt'	=> $audios['salt'],
				'file_type_token'	=> $audios['token']
			);

			$audios_arr 		= array_merge( $audios_arr, $module_arr );

			$audios_json 		= json_encode( $audios_arr );

			$data['document_json']	= $document_json;
			$data['images_json']	= $images_json;
			$data['videos_json']	= $videos_json;
			$data['audios_json']	= $audios_json;

			$resources['load_materialize_modal'] = array(
				'modal_upload_file' 		=> array(
					'title' 		=> 'File',
					'size' 			=> "md",
					'module' 		=> CORE_FILE_MANAGEMENT,
					'controller' 	=> 'Files',
					'custom_button'	=> array(
						'Set Access Rights' => array(
							"type"	=> "button",
							"action"=> 'Set Access Rights',
							'class' => 'green lighten-1 access_right_btn'
						),
						BTN_SAVE 	=> array("type" => "button", "action" => BTN_SAVING, 'class' => 'submit_file')
					),
					'post'			=> true,
					'method'		=> 'modal_files'
				),
				'modal_upload_file_version' 		=> array(
					'title' 		=> 'File Version',
					'size' 			=> "md",
					'module' 		=> CORE_FILE_MANAGEMENT,
					'controller' 	=> 'Files',
					'custom_button'	=> array(
						BTN_SAVE 	=> array("type" => "button", "action" => BTN_SAVING)
					),
					'post'			=> true,
					'method'		=> 'modal_file_version'
				),
				'modal_version_list' 		=> array(
					'title' 		=> 'Version List',
					'size' 			=> "md lg-w",
					'module' 		=> CORE_FILE_MANAGEMENT,
					'controller' 	=> 'Files',
					'modal_footer' 	=> false,
					'post'			=> true,
					'method'		=> 'modal_version_list'
				),
				'modal_access_rights'	=> array(
					'title' 		=> 'Access Right',
					'size' 			=> "sm md-h",
					'module' 		=> CORE_FILE_MANAGEMENT,
					'controller' 	=> 'Access_rights',
					'post'			=> true,
					'method'		=> 'modal_access_rights',
					'custom_button'	=> array(
						BTN_SAVE 	=> array("type" => "button", "action" => BTN_SAVING)
					),
				),
				'modal_video_player'	=> array(
					'title' 		=> 'Video Player',
					'size' 			=> "md md-w",
					'module' 		=> CORE_FILE_MANAGEMENT,
					'controller' 	=> 'Files',
					// 'modal_footer' 	=> false,
					'post'			=> true,
					'method'		=> 'modal_video_player',
					'custom_button'	=> array(),
					'complete_callback' => 'Files.close_modal_video()'
				),
				'modal_audio_player'	=> array(
					'title' 		=> 'Audio Player',
					'size' 			=> "md full",
					'module' 		=> CORE_FILE_MANAGEMENT,
					'controller' 	=> 'Files',
					// 'modal_footer' 	=> false,
					'post'			=> true,
					'method'		=> 'modal_audio_player',
					'custom_button'	=> array(),
					'complete_callback' => 'setPlayer.close_modal();'
				)
			);

			$resources['load_delete']	= array(
				'file'	=> array(
					'delete_cntrl' 		=> 'Files',
					'delete_method'		=> 'delete_file',
					'delete_module'		=> CORE_FILE_MANAGEMENT
				),
				'file_version'	=> array(
					'delete_cntrl' 		=> 'Files',
					'delete_method'		=> 'delete_file_version',
					'delete_module'		=> CORE_FILE_MANAGEMENT
				)
			);

			$resources['load_css']	= array(CSS_VIEWER);
			$resources['load_js']	= array(JS_LABELAUTY, JS_VIEWER_ALL);

			$data['add_per']		= $this->add_per;

			$this->template->load('files', $data, $resources);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg  	= $this->rlog_error($e, TRUE);	

			$this->error_index( $msg );
		}
	}

	public function tabs()
	{
		$orig_params 	= get_params();

		$resources 					= array();
		$data 						= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$params 	= $this->_filter_params( $orig_params );
			
			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			// check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			$resources['load_js']	= array();
			$resources['load_css']	= array();

			$resources['load_css'][] 	= CSS_DATATABLE_MATERIAL;
			$resources['load_css'][] 	= CSS_SELECTIZE;
			$resources['load_js'][] 	= JS_DATATABLE;
			$resources['load_js'][] 	= JS_DATATABLE_MATERIAL;

			$resources['load_js'][] 	= $this->module_js;
			$resources['load_js'][] 	= $this->player_js;

			$table 						= '';

			$extra_opt  				= array();

			$extra_opt['post_data']		= $orig_params;

			switch( $params['file_type'] ) 
			{
				case FILE_TYPE_DOCUMENTS :

					$table 				= 'documents_dt_table';

				break;

				case FILE_TYPE_AUDIOS :

					$table 				= 'audios_dt_table';

				break;

				case FILE_TYPE_VIDEOS :

					$table 				= 'videos_dt_table';

				break;

				case FILE_TYPE_IMAGES :

					$table 				= 'images_dt_table';

				break;
			}

			$datatable_options 			= array_merge( $this->dt_options, $extra_opt );

			$resources['datatable']		= $datatable_options;
			
			$page 						= strtolower( $params['file_type'] );

			$this->load->view("tabs/" . $page);

			$this->load_resources->get_resource($resources);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index_tab( $msg );
		}
		catch( Exception $e )
		{
			$msg  	= $this->rlog_error($e, TRUE);	

			$this->error_index_tab( $msg );
		}

	}

	public function modal_audio_player()
	{
		$data 			= array();
		$resources		= array();

		$orig_params 	= get_params();

		try
		{
			$this->redirect_module_permission($this->module);

			$params 			= $this->_filter_params( $orig_params );

			$resources['load_js']	= array(JS_LABELAUTY, JS_HOWLER_CORE, JS_SIRIWAVE, $this->player_js);
			$resources['load_css']	= array(CSS_AUDIO);

			$root_path 			= $this->get_root_path();
			$path_dir 			= $root_path.$params['path_dir'].$params['file_name'];
			$path_dir 			= str_replace(array('\\','/'), array(DS,DS), $path_dir);
			$url_file 			= '';

			if( file_exists( $path_dir ) )	
			{
				$url_file 		 = base_url().$params['path_dir'].$params['file_name'];

				$file_arr 		= array(
					array(
						'title'		=> $params['file_name'],
						'file'		=> $url_file,
						'howl'		=> null
					)
				);

				$ext 		= pathinfo( $path_dir, PATHINFO_EXTENSION );
				$path_inf 	= pathinfo( $path_dir );

				$resources['loaded_init']	= array(
					'setPlayer.init('.json_encode($file_arr).');'
				);

			}
			else
			{
				throw new Exception('File not found.');
			}

			$this->load->view('modals/audio_player', $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}
	}

	public function modal_video_player()
	{
		$data 			= array();
		$resources		= array();

		$orig_params 	= get_params();

		try
		{
			$this->redirect_module_permission($this->module);

			$params 			= $this->_filter_params( $orig_params );

			$resources['load_js']	= array();
			$resources['load_css']	= array();

			$resources['load_css'][] 	= CSS_VIDEO_PLAYER;
			$resources['load_js'][] 	= JS_VIDEO_PLAYER;

			$resources['load_js'][] 	= $this->module_js;
			$resources['loaded_init']	= array('Files.videos();');

			$root_path 			= $this->get_root_path();
			$path_dir 			= $root_path.$params['path_dir'].$params['file_name'];
			$path_dir 			= str_replace(array('\\','/'), array(DS,DS), $path_dir);
			$url_file 			= '';
			$mime 				= '';
				
			if( file_exists( $path_dir ) )	
			{
				$url_file 		 = base_url().$params['path_dir'].$params['file_name'];

				$ext 		= pathinfo( $path_dir, PATHINFO_EXTENSION );
				$path_inf 	= pathinfo( $path_dir );

				$mimes 			= get_mimes();

				$mimes['mkv']	= array('video/webm', 'video/x-matroska');

				if( ISSET( $mimes[$ext] ) )
				{
					$mime 		= $mimes[$ext];

					if( is_array( $mime ) )
					{
						$mime 	= $mime[0];
					}
				}
				else
				{
					throw new Exception('Invalid File.');
				}
			}
			else
			{
				throw new Exception('File not found.');
			}

			$data['url_file']	= $url_file;
			$data['mime']		= $mime;

			$this->load->view('modals/video_player', $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	
	}

	public function get_files_list()
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();

		$resources 				= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$orig_params 		= get_params();

			$flag 				= 1;

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			// check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			$result 				= array();

			$columns 				= $this->table_columns;
			$filter 				= $this->table_filter;
			$order 					= $this->table_order;

			$user_id 				= $this->session->user_id;

			$result 				= $this->files_mod->get_files_list( $columns, $filter, $order, $params, $user_id );

			$cnt_result 		= count($result['aaData']);

			$counter 			= 0;

			$output['sEcho'] 				= $params['sEcho'];
			$output['iTotalRecords'] 		= $cnt_result;
			$output['iTotalDisplayRecords'] = $result['filtered_length']['filtered_length'];
			$output['aaData']				= array();

			$list_parent 					= "";

			if(! EMPTY($result))
			{
				$cache_date 			= array();

			 	foreach( $result['aaData'] as $key => $r )
                {
                    $created_date    					= date( "F d, Y", strtotime( $r['created_date'] ) );

                    $cache_date[ $created_date ][] 		= $r;
                }
                
				foreach($cache_date as $key_date => $data)
				{
					$list_html 			= "";

					foreach( $data as $key => $r )
					{
						$id 		= $r['file_id'];
						$encoded 	= base64_url_encode($id);
						$salt 		= gen_salt();

						$token 		= in_salt( $id, $salt );

						$token_view = in_salt( $id.'/'.ACTION_VIEW, $salt );
						$token_edit = in_salt( $id.'/'.ACTION_EDIT, $salt );
						$token_del 	= in_salt( $id.'/'.ACTION_DELETE, $salt );

						$file_type_salt_token 	= $this->generate_salt_token_arr( $r['file_type'] );
						$module_salt_token 		= $this->generate_salt_token_arr( $r['module_code'] );

						$file_type_id 		= $file_type_salt_token['id_enc'];
						$file_type_salt 	= $file_type_salt_token['salt'];
						$file_type_token 	= $file_type_salt_token['token'];

						$module_id 			= $module_salt_token['id_enc'];
						$module_salt 		= $module_salt_token['salt'];
						$module_token 		= $module_salt_token['token'];

						$file_size_num 		= 0;
						$module_path 		= NULL;
						$file_type_path 	= NULL;
						$file_size 			= '0 KB';
						$version_num 		= '1.0';

						$path_dir 			= NULL;

						if( ISSET( $this->module_dir_map[ $r['module_code'] ] ) )
						{
							$module_path 	= $this->module_dir_map[ $r['module_code'] ];
						}

						if( ISSET( $this->file_type_dir_map[ $r['file_type'] ] ) )
						{
							$file_type_path = $this->file_type_dir_map[ $r['file_type'] ];
						}

						$file_name 			= '';

						if( !EMPTY( $r['file_name'] ) )
						{
							$file_name 		= $r['file_name'];

							if( $params['file_type'] != FILE_TYPE_ALBUMS )
							{
								if( !EMPTY( $module_path ) AND !EMPTY( $file_type_path ) )
								{
									$path_dir = $module_path.$file_type_path;
									$path_dir = str_replace(array('\\','/'), array(DS,DS), $path_dir);

									$root_path = $this->get_root_path();

									$path 	= $root_path.$module_path.$file_type_path.$file_name;
									$path 	= str_replace(array('\\','/'), array(DS,DS), $path);
									
									if( file_exists( $path ) )
									{
										$file_size 		= file_size_convert( filesize( $path ) );
										$file_size_num 	= filesize( $path );
									}
								}
							}
						}

						$show_vl 			= FALSE;
						
						if( !EMPTY( $r['version'] ) )
						{
							$version 		= explode(',', $r['version']);
							$version_num 	= end( $version );

							if( count( $version ) > 1 )
							{
								$show_vl 		= TRUE;
							}
						}

						$show_act 			= TRUE;
						$show_dl			= TRUE;
						$show_view 			= FALSE;

						if( in_array( $r['file_extension'], $this->show_view, TRUE ) )
						{
							$show_view 		= TRUE;
						}

						$data 				= array();

						$pass_data_arr 		= array(
							'file_type'			=> $file_type_id,
							'file_type_salt'	=> $file_type_salt,
							'file_type_token'	=> $file_type_token,
							'module'			=> $module_id,
							'module_salt'		=> $module_salt,
							'module_token'		=> $module_token,
							'file_id'			=> $encoded,
							'file_salt'			=> $salt,
							'file_token'		=> $token_edit,
							'file_action'		=> ACTION_EDIT
						);

						$data['orig_params_json'] 	= json_encode( $pass_data_arr );
						$data['params'] 	= $params;

						$output_file 		= strtolower( $params['file_type'] );

						$data['id']				= $id;
						$data['encoded'] 		= $encoded;
						$data['salt'] 			= $salt;
						$data['token_view'] 	= $token_view;
						$data['token_edit'] 	= $token_edit;
						$data['token_del']		= $token_del;
						$data['key']			= $counter;
						$data['details']		= $r;
						$data['file_size_num']	= $file_size_num;
						$data['file_size']		= $file_size;
						$data['version']		= $version_num;

						$data['edit_url'] 		= ACTION_EDIT.'/'.$encoded.'/'.$salt.'/'.$token_edit;
						$data['del_url']		= ACTION_DELETE.'/'.$encoded.'/'.$salt.'/'.$token_del;

						$per_user_view 			= $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_GROUPS );

						$per_user_edit 			= $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_GROUPS );

						$per_user_delete 		= $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_GROUPS );

						$all_view 				=  $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_ALL );

						$all_edit 				=  $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_ALL );

						$all_delete 			= $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_ALL );

						$data['edit_per'] 		= ( 
							( $this->edit_per AND $this->session->user_id == $r['created_by'] ) 
							OR ( $this->session->user_id != $r['created_by'] AND ( $per_user_edit OR $all_edit ) )
						);

						$data['delete_per'] 	= ( 
							( $this->delete_per AND $this->session->user_id == $r['created_by'] ) 
							OR ( $this->session->user_id != $r['created_by'] AND ( $per_user_delete OR $all_delete ) )

						);

						$data['view_per'] 		= ( 
							( $this->view_per AND $this->session->user_id == $r['created_by'] ) 
							OR ( $this->session->user_id != $r['created_by'] AND ( $per_user_view OR $all_view ) ) 
						);

						$data['view_per_photo']	= ( 
							( $this->view_per AND $this->session->user_id == $r['created_by'] ) 
							OR ( $this->session->user_id != $r['created_by'] AND ( $per_user_view OR $all_view ) ) 
						);

						$data['version_per'] 	= ( 
							( $this->session->user_id == $r['created_by'] AND $this->edit_per ) 
							OR ( $this->session->user_id != $r['created_by'] AND ( $per_user_edit OR $all_edit ) ) 

						);

						$data['download_per'] 	= ( 
							( $this->session->user_id == $r['created_by'] AND $this->view_per ) 
							OR ( $this->session->user_id != $r['created_by'] AND ( $per_user_view OR $all_view ) ) 
						);

						$data['show_act']		= $show_act;
						$data['show_dl']		= $show_dl;
						$data['show_view'] 		= $show_view;
						$data['show_vl'] 		= $show_vl;
						$data['force_url']		= base_url().'Upload/force_download?file='.$file_name.'&path='.$path_dir;
						$data['path_dir'] 		= $path_dir;

						$data['preview_url']	= base_url().'Upload/download?file='.$file_name.'&path='.$path_dir;
						$data['file_name']		= $file_name;

						$list_html 			.= $this->load->view('lists/'.$output_file, $data, TRUE );

						$counter++;

						if($counter == $cnt_result)
						{
							// $resources['preload_modal'] = array("modal_gender_issues");
							/*$resources['loaded_init'] 	= array("dropdown_button_init();");
							$list_html .= $this->load_resources->get_resource($resources, TRUE);*/
						}
					}

					$list_parent 		.= "
						<h5 class='page-content-title'>".$key_date."</h5>
				 		<div class='col m12 s12 p-n list'>
							<ul class=' list-view-custom file-type m-b-lg'>
								".$list_html."
							</ul>
						</div>

";
				}

				$lists 			= "
					 <div class='row m-n p-sm p-t-n'>
					 	".$list_parent."
					</div>
";

				$rows[] 		 	= array(
					$lists,
					"",
					"",
					""
				);

				$output['iTotalRecords'] = $counter;
			}
		}
		catch(PDOException $e)
		{
			$this->rlog_error($e);
				
			$flag	= 0;
			$msg	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$this->rlog_error($e);
				
			$flag	= 0;
			$msg	= $e->getMessage();
		}
	
		$output['aaData'] 	= $rows;
		$output['flag']		= $flag;
		$output['msg']		= $msg;

		echo json_encode($output);
	}

	public function modal_file_version()
	{
		$data 			= array();
		$resources		= array();

		$details 		= array();

		$orig_params 		= get_params();

		$version 			= '1.0';

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

			$details 			= $this->files_mod->get_specific_file( $params['file_id'] );

			if( !EMPTY( $details ) )
			{
				if( $details['created_by'] != $this->session->user_id )
				{
					$per_user_edit 	= $this->access_rights->check_access_permission( $details['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_GROUPS );

					$all_edit 		=  $this->access_rights->check_access_permission( $details['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_ALL );

					if( !$per_user_edit AND !$all_edit )
					{
						throw new Exception( $this->lang->line( 'err_unauthorized_edit' ) );
					}
				}
			}

			if( !EMPTY( $details ) )
			{
				if( !EMPTY( $details['version'] ) )
				{
					$versions 	= explode(',', $details['version']);

					$version  	= end( $versions );
				}
			}

			$disabled 								= FALSE;

			if( !EMPTY( $action ) AND $action == ACTION_VIEW )
			{
				$disabled 							= TRUE;
			}

			$resources['loaded_init'] 	= array();
			$resources['load_js'] 		= array();
			$resources['load_css'] 		= array();

			$upload_common 				= array(
				'path' 				=> PATH_FILE_UPLOADS, 
				'allowed_types' 	=> 'jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx,csv,ppt,pptx', 
				// 'show_progress' => 1,
				'show_preview'			=> 1,
				'default_img_preview'	=> 'image_preview.png',
				'max_file' 				=> 1,
				'multiple' 				=> 0,
				'successCallback'		=> "Files.successCallback(files,data,xhr,pd,true);",
				// 'max_file_size'			=> '13107200',
				'auto_submit'			=> false,
				// 'max_file_size'			=> '13107200',
				'multiple_obj'			=> true,
				'show_download'			=> true,
				'delete_form'			=> '#form_modal_upload_file',
				'delete_path'			=> CORE_FILE_MANAGEMENT.'/Files',
				'delete_path_method'	=> 'delete_files',
				'drag_drop' 			=> true,
				'disable'				=> $disabled
			);

			$resources['upload'] 		= array(
				'version' 				=> array()
			);

			switch( $params['file_type'] )
			{
				case FILE_TYPE_DOCUMENTS :

					$upload_common['allowed_types']		= DOCUMENT_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_DOCUMENTS;

				break;

				case FILE_TYPE_IMAGES :

					$upload_common['allowed_types']		= IMAGE_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_IMAGES;

				break;

				case FILE_TYPE_AUDIOS :

					$upload_common['allowed_types']		= AUDIO_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_AUDIOS;

				break;

				case FILE_TYPE_VIDEOS :

					$upload_common['allowed_types']		= VIDEO_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_VIDEOS;

				break;
			}

			$resources['upload']['version']		= array_merge( $resources['upload']['version'], $upload_common );

			$js_file_constants 				= $this->get_constants( '^FILE_TYPE_', TRUE );
			$js_file_dir_constants 			= $this->get_constants( '^DIRECTORY_', TRUE );

			$directory_module_map 			= $this->module_dir_map;

			$directory_module_map_json 		= json_encode( $directory_module_map );

			$resources['load_css'][] 		= CSS_LABELAUTY;

			$resources['load_js'][] 		= JS_LABELAUTY;
			$resources['load_js'][] 		= $this->module_js;
			$resources['loaded_init'][] 	= 'Files.init_modal("'.$params['file_type'].'", "'.$params['module'].'");';
			$resources['loaded_init'][] 	= 'Files.save("'.$params['file_type'].'", "'.$params['module'].'", true);';

			$data['params'] 				= $params;
			$data['orig_params'] 			= $orig_params;
			$data['js_file_constants']		= $js_file_constants;
			$data['js_file_dir_constants']	= $js_file_dir_constants;
			$data['id'] 					= $orig_params['file_id'];
			$data['salt'] 					= $orig_params['file_salt'];
			$data['token'] 					= $orig_params['file_token'];
			$data['action'] 				= $orig_params['file_action'];
			$data['details'] 				= $details;
			$data['version'] 				= $version;

			$data['directory_module_map_json']	= $directory_module_map_json;

			
			$this->load->view('modals/version', $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	
	}

	public function modal_files( $action = NULL, $id = NULL, $salt = NULL, $token = NULL )
	{
		$data 			= array();
		$resources		= array();

		$details 		= array();

		$orig_params 		= get_params();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			$disabled 								= FALSE;

			if( !EMPTY( $action ) AND $action == ACTION_VIEW )
			{
				$disabled 							= TRUE;
			}

			if( !EMPTY( $action ) )
			{
				$id_dec 		= filter_var( base64_url_decode( $id ), FILTER_SANITIZE_NUMBER_INT );
				
				check_salt( $id_dec, $salt, $token, $action );

				$details 		= $this->files_mod->get_specific_file( $id_dec );
			}

			$modal_page 		= strtolower( $params['file_type'] );

			$resources['loaded_init'] 	= array();
			$resources['load_js'] 		= array();
			$resources['load_css'] 		= array();
			$resources['preload_modal'] = array();

			$upload_common 				= array(
				'path' 				=> PATH_FILE_UPLOADS, 
				'allowed_types' 	=> 'jpeg,jpg,png,gif,pdf,doc,docx,xls,xlsx,csv,ppt,pptx', 
				// 'show_progress' => 1,
				'show_preview'			=> 1,
				'default_img_preview'	=> 'image_preview.png',
				'max_file' 				=> 1,
				'multiple' 				=> 0,
				'successCallback'		=> "Files.successCallback(files,data,xhr,pd);",
				// 'max_file_size'			=> '13107200',
				'auto_submit'			=> false,
				// 'max_file_size'			=> '13107200',
				'multiple_obj'			=> true,
				'show_download'			=> true,
				'delete_form'			=> '#form_modal_upload_file',
				'delete_path'			=> CORE_FILE_MANAGEMENT.'/Files',
				'delete_path_method'	=> 'delete_files',
				'drag_drop' 			=> true,
				'disable'				=> $disabled,
				'dont_delete_in_server' => true
			);

			$multi_check 					= '';

			if( ISSET( $params['multiple'] ) AND !EMPTY( $params['multiple'] ) )
			{
				$upload_common['multiple']	= 1;
				$upload_common['max_file']	= 5;

				$upload_common['custom_html_func']	= 'Files.custom_html_func( check_load, cus_id, "'.$params['file_type'].'", upload_stat, curr_upl_obj, dis_inp, filename );';

				$multi_check 				= '1';
			}

			$upload_common['successCallback']	= 'Files.successCallback(files,data,xhr,pd, undefined, "'.$multi_check.'", prev_form_file);';

			$resources['upload'] 		= array(
				// 'document' 				=> array()
			);

			$upload_multi 				= strtolower( $params['file_type'] );

			switch( $params['file_type'] )
			{
				case FILE_TYPE_DOCUMENTS :

					$upload_common['allowed_types']		= DOCUMENT_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_DOCUMENTS;

					$resources['upload']['document'] 	= array();

					$resources['upload']['document']	= array_merge( $resources['upload']['document'], $upload_common );

					$upload_multi 				 		= 'document';

				break;

				case FILE_TYPE_IMAGES :

					$upload_common['allowed_types']		= IMAGE_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_IMAGES;

					$resources['upload']['images'] 		= array();

					$resources['upload']['images']		= array_merge( $resources['upload']['images'], $upload_common );

				break;

				case FILE_TYPE_AUDIOS :

					$upload_common['allowed_types']		= AUDIO_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_AUDIOS;

					$resources['upload']['audios'] 		= array();

					$resources['upload']['audios']		= array_merge( $resources['upload']['audios'], $upload_common );



				break;

				case FILE_TYPE_VIDEOS :

					$upload_common['allowed_types']		= VIDEO_EXTENSIONS;
					$upload_common['path']				= PATH_FILE_UPLOADS.DIRECTORY_VIDEOS;

					$resources['upload']['videos'] 		= array();

					$resources['upload']['videos']		= array_merge( $resources['upload']['videos'], $upload_common );

				break;
			}

			if( ISSET( $params['multiple'] ) AND !EMPTY( $params['multiple'] ) )
			{
				$modal_page 				= 'upload_multiple';
			}

			$js_file_constants 				= $this->get_constants( '^FILE_TYPE_', TRUE );
			$js_file_dir_constants 			= $this->get_constants( '^DIRECTORY_', TRUE );

			$directory_module_map 			= $this->module_dir_map;

			$directory_module_map_json 		= json_encode( $directory_module_map );

			$resources['load_js'][] 		= $this->module_js;
			$resources['loaded_init'][] 	= 'Files.init_modal("'.$params['file_type'].'", "'.$params['module'].'", "'.$multi_check.'");';
			$resources['loaded_init'][] 	= 'Files.save("'.$params['file_type'].'", "'.$params['module'].'", undefined, "'.$multi_check.'");';
			$resources['preload_modal'][] 	= 'modal_access_rights';

			$orig_params['file_id'] 		= $id;
			$orig_params['file_salt'] 		= $salt;
			$orig_params['file_token'] 		= $token;
			$orig_params['file_action'] 	= $action;

			$data['params'] 				= $params;
			$data['orig_params'] 			= $orig_params;
			$data['orig_params_json'] 		= json_encode( $orig_params );

			$data['js_file_constants']		= $js_file_constants;
			$data['js_file_dir_constants']	= $js_file_dir_constants;
			$data['id'] 					= $id;
			$data['salt'] 					= $salt;
			$data['token'] 					= $token;
			$data['action'] 				= $action;
			$data['details']				= $details;
			$data['upload_multi']			= $upload_multi;
			$data['multi_check']			= $multi_check;

			$data['directory_module_map_json']	= $directory_module_map_json;

			if( !EMPTY( $details ) )
			{
				if( $details['created_by'] != $this->session->user_id )
				{
					$per_user_edit 	= $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_GROUPS );

					$all_edit 		=  $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_ALL );

					$per_user_view 	= $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_GROUPS );

					$all_view 		=  $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_ALL );

					if( !$per_user_edit AND !$all_edit AND !$per_user_view AND !$all_view )
					{
						throw new Exception( $this->lang->line( 'err_unauthorized_edit' ) );
					}
				}
			}
			
			$this->load->view('modals/'.$modal_page, $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	
	}

	private function _filter_params( array $orig_params )
	{
		$arr 				= $this->set_filter( $orig_params )
								->filter_string( 'file_type', TRUE )
								->filter_string( 'module', TRUE )
								->filter_string( 'file_display_name' )
								->filter_string( 'file_description' )
								->filter_number( 'file_id', TRUE )
								->filter_number('file_version', TRUE)
								->filter_string('display_name_multi')
								->filter_string('file_description_multi');

		$arr 				= $arr->filter();

		return $arr;
	}

	public function validate( array $params, array $orig_params, $action = NULL, $multi_check = FALSE )
	{
		$arr 					= array();

		$required 				= array();
		$constraints 			= array();

		$arr['description'] 	= NULL;

		try
		{

			if( $multi_check )
			{
				$required['display_name_multi']			= 'Display Name';
				$constraints['display_name_multi']		= array(
					'name'			=> 'Display Name',
					'data_type'		=> 'string',
					'max_len'		=> '255'
				);

				if( ISSET( $orig_params['file_description_multi'] ) AND !EMPTY( $orig_params['file_description_multi'] ) )
				{
					$constraints['file_description_multi']		= array(
						'name'			=> 'Description',
						'data_type'		=> 'string',
						'max_len'		=> '60000'
					);
				}
			}
			else
			{
				$required['file_display_name']			= 'Display Name';
				$constraints['file_display_name']		= array(
					'name'			=> 'Display Name',
					'data_type'		=> 'string',
					'max_len'		=> '255'
				);

				if( ISSET( $orig_params['file_description'] ) AND !EMPTY( $orig_params['file_description'] ) )
				{
					$constraints['file_description']		= array(
						'name'			=> 'Description',
						'data_type'		=> 'string',
						'max_len'		=> '60000'
					);

					$arr['description']	= $params['file_description'];
				}
			}

			$this->check_required_fields( $params, $required );

			$this->validate_inputs( $params, $constraints );

			if( !$multi_check )
			{
				$arr['display_name']	= $params['file_display_name'];
			}

			$arr['module_code']			= $params['module'];
			$arr['file_type']			= $params['file_type'];

			if( $action == ACTION_ADD )
			{
				$arr['created_by']		= $this->session->user_id;
				$arr['created_date']	= $this->date_now;
			}
			else
			{
				$arr['modified_by']		= $this->session->user_id;
				$arr['modified_date']	= $this->date_now;
			}

		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $arr;
	}

	public function save_helper( $update, array $val, array $params )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$file_id 				= NULL;

		$main_where 			= array();

		$revision_val 			= array();

		$file_version_id 		= NULL;

		$curr_display_name 		= NULL;

		try
		{
			if( !$update )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$file_id 			= $this->files_mod->insert_files( $val );

				$main_where 		= array(
					'file_id'		=> $file_id
				);

				$curr_file 			= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
					$main_where
				);

				$curr_detail[] 	 	= $curr_file;

				$curr_display_name 	= $curr_file[0]['display_name'];

				$revision_val 		= $val;

				$revision_val['file_id']	= $file_id;
				$revision_val['minor_revision_flag']	= 0;

				$version 			= 1.0;

				$revision_val['version']	= $version;

				$sub_where 			= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VERSIONS;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				unset( $revision_val['module_code'] );

				$file_version_id 	= $this->file_versions->insert_file_versions( $revision_val );

				$sub_where 			= array(
					'file_version_id' => $file_version_id
				);

				$curr_detail[] 		= $this->file_versions->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
					$sub_where
				);

			}
			else
			{
				check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

				$file_id 			= $params['file_id'];

				$main_where 		= array(
					'file_id'		=> $params['file_id']
				);

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE;
				$audit_action[] 	= AUDIT_UPDATE;
				$prev_detail[]  	= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
					$main_where
				);

				$this->files_mod->update_files( $val, $main_where );

				$curr_file 			= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
					$main_where
				);

				$curr_detail[] 	 	= $curr_file;

				$curr_display_name 	= $curr_file[0]['display_name'];
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch (Exception $e)
		{
			throw $e;
		}

		return array(
			'audit_schema'	=> $audit_schema,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'prev_detail'	=> $prev_detail,
			'curr_detail' 	=> $curr_detail,
			'file_id' 		=> $file_id,
			'file_version_id' 	=> $file_version_id,
			'curr_display_name' => $curr_display_name
		);
	}

	public function save()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$audit_action 			= AUDIT_UPDATE;
		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= ( ISSET( $orig_params['file_id'] ) AND !EMPTY( $orig_params['file_id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['file_id'] ) AND !EMPTY( $orig_params['file_id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$main_where 			= array();

		$file_id_details 		= array();
		$file_id 				= NULL;

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			$permission 		= ( !$update ) ? $this->add_per : $this->edit_per;
			$per_msg 			= ( !$update ) ? $this->lang->line( 'err_unauthorized_add' ) : $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}	

			$val 				= $this->validate( $params, $orig_params, $action );

			SYSAD_Model::beginTransaction();

			$audit_details 		= $this->save_helper( $update, $val, $params );

			$audit_schema 		= array_merge( $audit_schema, $audit_details['audit_schema'] );
			$audit_table 		= array_merge( $audit_table, $audit_details['audit_table'] );
			$audit_action 		= array_merge( $audit_action, $audit_details['audit_action'] );
			$prev_detail 		= array_merge( $prev_detail, $audit_details['prev_detail'] );
			$curr_detail 		= array_merge( $curr_detail, $audit_details['curr_detail'] );

			$file_id_details 	= $this->generate_salt_token_arr( $audit_details['file_id'], ACTION_EDIT );

			$main_where 		= array(
				'file_id'		=> $audit_details['file_id']
			);

			$check_file_visibility 			= $this->access_rights_mod->check_file_visibility( $main_where );

			if( EMPTY( $check_file_visibility ) OR EMPTY( $check_file_visibility['check_file_visibility'] ) )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VISIBILITY;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$file_val 			= array();

				$actions_val 		= array( ACTION_EDIT, ACTION_VIEW, ACTION_DELETE );

				$file_val[0]['file_id']			= $audit_details['file_id'];
				$file_val[0]['user_id']			= '0';
				$file_val[0]['actions']			= implode(',', $actions_val);
				$file_val[0]['visibility_id']	= VISIBLE_ALL;

				$this->access_rights_mod->insert_file_visibility( $file_val );

				$curr_detail[] 		= $this->access_rights_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY,
										$main_where
									 );
			}

			$this->dt_options['post_data']	= array(
				'file_type'			=> $orig_params['file_type'],
				'file_type_salt'	=> $orig_params['file_type_salt'],
				'file_type_token'	=> $orig_params['file_type_token'],
				'module'			=> $orig_params['module'],
				'module_salt'		=> $orig_params['module_salt'],
				'module_token'		=> $orig_params['module_token']
			);

			$audit_name 				= 'Files '.$params['file_display_name'].'.';

			$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );
		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e)
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status'				=> $status,
			'datatable_options' 	=> $this->dt_options,
			'file_type'				=> $params['file_type'],
			'module'				=> $params['module']
		);

		if( !EMPTY( $file_id_details ) )
		{
			$response['file_id'] 		= $file_id_details['id_enc'];
			$response['file_salt'] 		= $file_id_details['salt'];
			$response['file_token'] 	= $file_id_details['token_concat'];
			$response['file_action'] 	= $file_id_details['sub_id_1'];
		}

		echo json_encode( $response );	
	}

	public function save_revision()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$audit_action 			= AUDIT_UPDATE;
		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= ( ISSET( $orig_params['file_id'] ) AND !EMPTY( $orig_params['file_id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['file_id'] ) AND !EMPTY( $orig_params['file_id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$main_where 			= array();

		$file_version_id 		= NULL;
		$file_version_id_det 	= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

			$permission 		= $this->edit_per;
			$per_msg 			= $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}	

			$details 		= $this->files_mod->get_specific_file( $params['file_id'] );

			if( !EMPTY( $details ) )
			{
				if( $details['created_by'] != $this->session->user_id )
				{
					$per_user_edit 	= $this->access_rights->check_access_permission( $details['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_GROUPS );

					$all_edit 		=  $this->access_rights->check_access_permission( $details['file_id'], $this->session->user_id, ACTION_EDIT, VISIBLE_ALL );

					if( !$per_user_edit AND !$all_edit )
					{
						throw new Exception( $this->lang->line( 'err_unauthorized_add' ) );
					}
				}
			}

			$val 				= $this->validate( $params, $orig_params, ACTION_ADD );

			$revision_val 		= $val;

			$revision_val['file_id']	= $params['file_id'];
			$revision_val['minor_revision_flag']	= 1;

			$version 			= 1.0;
			$version_num 		= 0;

			$latest_version 	= $this->file_versions->get_latest_version( $params['file_id'] );

			if( !EMPTY( $latest_version ) )
			{
				$version 		= $latest_version['version'];
			}

			if( ISSET( $params['minor_revision_flag'] ) )
			{
				$version_num 			= round( $version ) + 1;
				$version_num 			= $version_num.'.0';
				
				$revision_val['minor_revision_flag'] = 0;
			}
			else
			{

				$version_num_arr 	= explode('.',  $version);

				$version_num_dec 	= $version_num_arr[1] + 1;
				
				$new_version 		= $version_num_arr[0].'.'.$version_num_dec;
				
				$version_num 		= $new_version;
				
			}

			$revision_val['version']	= $version_num;
			
			SYSAD_Model::beginTransaction();

			$main_where 		= array(
				'file_id'		=> $params['file_id']
			);

			$sub_where 			= array();

			$audit_schema[] 	= DB_CORE;
			$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VERSIONS;
			$audit_action[] 	= AUDIT_INSERT;
			$prev_detail[]  	= array();

			unset( $revision_val['module_code'] );

			$file_version_id 	= $this->file_versions->insert_file_versions( $revision_val );

			$sub_where 			= array(
				'file_version_id' => $file_version_id
			);

			$curr_detail[] 		= $this->file_versions->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
				$sub_where
			);

			$audit_schema[] 	= DB_CORE;
			$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE;
			$audit_action[] 	= AUDIT_UPDATE;
			$prev_file 			= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
				$main_where
			);

			$prev_detail[]  	= $prev_file;

			$this->files_mod->update_files( $val, $main_where );

			$curr_detail[] 		= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
				$main_where
			);

			$file_version_id_det 	= $this->generate_salt_token_arr( $file_version_id );

			$this->dt_options['post_data']	= array(
				'file_type'			=> $orig_params['file_type'],
				'file_type_salt'	=> $orig_params['file_type_salt'],
				'file_type_token'	=> $orig_params['file_type_token'],
				'module'			=> $orig_params['module'],
				'module_salt'		=> $orig_params['module_salt'],
				'module_token'		=> $orig_params['module_token']
			);

			$audit_name 				= 'Add file version '.$prev_file[0]['display_name'].' version '.$version_num.'.';

			$audit_activity 			= sprintf( $this->lang->line('audit_trail_add'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );
		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e)
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status'				=> $status,
			'datatable_options' 	=> $this->dt_options,
			'file_type'				=> $params['file_type'],
			'module'				=> $params['module']
		);

		if( !EMPTY( $file_version_id_det ) )
		{
			$response['file_version_id'] 		= $file_version_id_det['id_enc'];
			$response['file_version_salt'] 		= $file_version_id_det['salt'];
			$response['file_version_token'] 	= $file_version_id_det['token'];
		}

		echo json_encode( $response );	
	}

	public function update_file_attachment()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$audit_action 			= AUDIT_UPDATE;
		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= ( ISSET( $orig_params['file_id'] ) AND !EMPTY( $orig_params['file_id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['file_id'] ) AND !EMPTY( $orig_params['file_id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$main_where 			= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

			if( ISSET( $params['revision'] ) AND !EMPTY( $params['revision'] ) )
			{
				check_salt( $params['file_version'], $params['file_version_salt'], $params['file_version_token'] );
			}

			$permission 		= $this->edit_per;
			$per_msg 			= $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}	

			$main_where 		= array(
				'file_id'		=> $params['file_id']
			);

			SYSAD_Model::beginTransaction();

			if( ISSET( $params['files'] ) AND !EMPTY( $params['files'] ) )
			{
				foreach( $params['files'] as $key => $file )
				{
					$orig_file_name 		= NULL;

					if( ISSET( $params['files_orig_filename'][$key] ) AND !EMPTY( $params['files_orig_filename'][$key] ) )
					{
						$orig_file_name 	= $params['files_orig_filename'][$key];
					}

					$file_type 				= $this->categorize_file( $file );

					$module_path 		= NULL;
					$file_type_path 	= NULL;
					$path_dir_file 		= NULL;

					$val 					= array();
					$val['modified_by']		= $this->session->user_id;
					$val['modified_date']	= $this->date_now;

					$val['file_name']		= $file;
					$val['original_name']	= $orig_file_name;
					$val['file_type']		= $file_type['file_type'];
					$val['file_extension']	= $file_type['file_extension'];

					if( ISSET( $params['revision'] ) AND !EMPTY( $params['revision'] ) )
					{
						$sub_where 			= array(
							'file_version_id'	=> $params['file_version']
						);
					}
					else
					{
						$sub_where 			= $main_where;
					}

					$rev_val 					= array();
					$rev_val['modified_by']		= $this->session->user_id;
					$rev_val['modified_date']	= $this->date_now;

					$rev_val['file_name']		= $file;
					$rev_val['original_name']	= $orig_file_name;
					$rev_val['file_type']		= $file_type['file_type'];
					$rev_val['file_extension']	= $file_type['file_extension'];

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VERSIONS;
					$audit_action[] 	= AUDIT_UPDATE;
					$prev_detail[]  	= $this->file_versions->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
						$sub_where
					);

					$this->file_versions->update_file_revisions( $rev_val, $sub_where );

					$curr_detail[] 		= $this->file_versions->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
						$sub_where
					);

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE;
					$audit_action[] 	= AUDIT_UPDATE;
					$prev_file 			= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
						$main_where
					);

					$prev_detail[]  	= $prev_file;

					$this->files_mod->update_files( $val, $main_where );

					$curr_detail[] 		= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
						$main_where
					);

					if( ( !ISSET( $params['revision'] ) OR EMPTY( $params['revision'] ) )
						AND !EMPTY( $prev_file[0]['file_name'] )
					)
					{
						if( ISSET( $this->module_dir_map[ $params['module'] ] ) )
						{
							$module_path 	= $this->module_dir_map[ $params['module'] ];
						}

						if( ISSET( $this->file_type_dir_map[ $params['file_type'] ] ) )
						{
							$file_type_path = $this->file_type_dir_map[ $params['file_type'] ];
						}

						if( !EMPTY( $module_path ) AND !EMPTY( $file_type_path ) )
						{
							$path_dir_file = $module_path.$file_type_path.$prev_file[0]['file_name'];
							$path_dir_file = str_replace(array('\\','/'), array(DS,DS), $path_dir_file);
						}

						if( !EMPTY( $path_dir_file ) )
						{
							$this->unlink_attachment( $path_dir_file );
						}
					}
				}
			}

			$audit_name 				= 'Files '.$params['file_display_name'].'.';

			$audit_activity 			= sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();

			$this->dt_options['post_data']	= array(
				'file_type'			=> $orig_params['file_type'],
				'file_type_salt'	=> $orig_params['file_type_salt'],
				'file_type_token'	=> $orig_params['file_type_token'],
				'module'			=> $orig_params['module'],
				'module_salt'		=> $orig_params['module_salt'],
				'module_token'		=> $orig_params['module_token']
			);

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );

		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e)
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status'				=> $status,
			'datatable_options' 	=> $this->dt_options
		);

		echo json_encode( $response );	
	}

	public function categorize_file( $file )
	{
		$file_type 			= NULL;
		$file_extension		= NULL;

		try
		{
			$file_details 	= pathinfo( $file );

			$document_arr 	= explode(',', DOCUMENT_EXTENSIONS);
			$image_arr 		= explode(',', IMAGE_EXTENSIONS);
			$audio_arr 		= explode(',', AUDIO_EXTENSIONS);
			$video_arr 		= explode(',', VIDEO_EXTENSIONS);

			if( !EMPTY( $file_details ) )
			{
				if( ISSET( $file_details['extension'] ) )
				{
					$file_extension = strtolower( $file_details['extension'] );

					if( in_array( $file_extension, $document_arr ) )
					{
						$file_type 	= FILE_TYPE_DOCUMENTS;
					}
					else if( in_array( $file_extension, $image_arr ) )
					{
						$file_type 	= FILE_TYPE_IMAGES;
					}
					else if( in_array( $file_extension, $audio_arr ) )
					{
						$file_type 	= FILE_TYPE_AUDIOS;
					}
					else if( in_array( $file_extension, $video_arr ) )
					{
						$file_type 	= FILE_TYPE_VIDEOS;
					}
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return array(
			'file_type'			=> $file_type,
			'file_extension'	=> $file_extension
		);
	}
	
	public function modal_version_list()
	{
		$data 			= array();
		$resources		= array();

		$details 		= array();

		$orig_params 		= get_params();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);			

			$params 			= $this->_filter_params( $orig_params );

			$details 			= $this->files_mod->get_specific_file( $params['file_id'] );

			if( !EMPTY( $details ) )
			{
				if( $details['created_by'] != $this->session->user_id )
				{
					$per_user_view 	= $this->access_rights->check_access_permission( $details['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_GROUPS );

					$all_view 		=  $this->access_rights->check_access_permission( $details['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_ALL );

					if( !$per_user_view AND !$all_view )
					{
						throw new Exception( $this->lang->line( 'err_unauthorized_edit' ) );
					}
				}
			}

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

			$resources['load_css'] 	= array();
			$resources['load_js'] 	= array();

			$this->dt_version_options['post_data']	= $orig_params;

			$resources['datatable'] = $this->dt_version_options;

			$this->load->view('modals/version_list', $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	
	}

	public function get_file_version_list()
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();

		$resources 				= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$orig_params 		= get_params();

			$flag 				= 1;

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

			$result 				= array();

			$columns 				= array(
				'a.file_id', 'a.file_version_id', 'a.file_name', 'a.original_name', 'a.description',
				'a.file_type', 'a.file_extension', 'a.minor_revision_flag', 'a.version',
				'a.created_date', 'a.modified_date', 'a.display_name', 'a.created_by', 'd.module_code', 'd.created_by as orig_creator'
			);

			$filter 				= array(
				'a-version', 'a-display_name', 'a-original_name', 'a-description'
			);

			$order 					= array(
				'a.version', 'a.display_name', 'a.original_name', 'a.description'
			);

			$result 				= $this->file_versions->get_file_version_list( $columns, $filter, $order, $params );

			$cnt_result 		= count($result['aaData']);

			$counter 			= 0;

			$output['sEcho'] 				= $params['sEcho'];
			$output['iTotalRecords'] 		= $cnt_result;
			$output['iTotalDisplayRecords'] = $result['filtered_length']['filtered_length'];
			$output['aaData']				= array();

			if(! EMPTY($result))
			{
				foreach( $result['aaData'] as $key => $r )
                {
                	$actions 			= "<div class='table-actions'>";

                	$id_enc 		= base64_url_encode( $r['file_version_id'] );
					$salt 			= gen_salt();
					$token_view 	= in_salt( $r['file_version_id'].'/'.ACTION_VIEW, $salt );
					$token_edit 	= in_salt( $r['file_version_id'].'/'.ACTION_EDIT, $salt );
					$token_delete 	= in_salt( $r['file_version_id'].'/'.ACTION_DELETE, $salt );

                	$module_path 		= NULL;
					$file_type_path 	= NULL;
					$path_dir 			= NULL;

					if( $r['orig_creator'] != $this->session->user_id )
					{
						$per_user_view 		= $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_GROUPS );

						$all_view 			= $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_VIEW, VISIBLE_ALL );

						$per_user_delete 	=  $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_GROUPS );

						$all_delete 		=  $this->access_rights->check_access_permission( $r['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_ALL );
					}

					$latest_version 	= $this->file_versions->get_latest_version( $params['file_id'] );

					if( ISSET( $this->module_dir_map[ $r['module_code'] ] ) )
					{
						$module_path 	= $this->module_dir_map[ $r['module_code'] ];
					}

					if( ISSET( $this->file_type_dir_map[ $r['file_type'] ] ) )
					{
						$file_type_path = $this->file_type_dir_map[ $r['file_type'] ];
					}

					if( !EMPTY( $module_path ) AND !EMPTY( $file_type_path ) )
					{
						$path_dir = $module_path.$file_type_path;
						$path_dir = str_replace(array('\\','/'), array(DS,DS), $path_dir);
					}

					$download_url 	= base_url().'Upload/download?file='.$r['file_name'].'&path='.$path_dir;

                	if( 
                		( $r['orig_creator'] == $this->session->user_id AND $this->view_per ) 
                		OR ( $r['orig_creator'] != $this->session->user_id AND ( $per_user_view OR $all_view ) )
					)
					{
						if( in_array($r['file_extension'], $this->show_view, TRUE) )
						{
							$actions 		.= '<a class="tooltipped" target="_blank" data-tooltip="Preview" data-file="'.$r['file_name'].'" data-position="bottom" onclick="viewerjs(this, event)" data-delay="50" href="'.$download_url.'" ><i class="material-icons">open_in_new</i></a>';
						}

						$actions 		.= '<a class="tooltipped" target="_blank" data-tooltip="Download" data-position="bottom" data-delay="50" href="'.$download_url.'" ><i class="material-icons">file_download</i></a>';
					}

					$post_data 		= $orig_params;
					$post_data['file_version']			= $id_enc;
					$post_data['file_version_salt']		= $salt;
					$post_data['file_version_token']	= $token_delete;
					$post_data['file_version_action']	= ACTION_DELETE;

					$post_data_json = json_encode( $post_data );

					if( !EMPTY( $latest_version ) )
					{
						if( $cnt_result > 1 AND $latest_version['file_version_id'] == $r['file_version_id'] )
						{
							if( 
								( $r['orig_creator'] == $this->session->user_id AND $this->delete_per ) 
								OR ( $r['orig_creator'] != $this->user_id AND ( $per_user_delete OR $all_delete ) )
							)
							{
								// $url_delete 		=  ACTION_DELETE.'/'.$id_enc.'/'.$salt.'/'.$token_delete;
								$delete_action 		= "content_file_version_delete('File Version', '', '', this)";
								 
								$actions 			.= '<a class="cursor-pointer tooltipped" data-delete_post=\''.$post_data_json.'\' onclick="'.$delete_action.'" data-tooltip="Delete"  data-position="bottom" data-delay="50"><i class="material-icons">delete</i></a>';
							}
						}
					}

                	$actions .= "</div>";

                	$rows[] 		 	= array(
						$r['version'],
						$r['display_name'],
						$r['original_name'],
						$r['description'],
						$actions
					);

                	$counter++;
                }

                $output['iTotalRecords'] = $counter;
			}

		}
		catch(PDOException $e)
		{
			$this->rlog_error($e);
				
			$flag	= 0;
			$msg	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$this->rlog_error($e);
				
			$flag	= 0;
			$msg	= $e->getMessage();
		}
	
		$output['aaData'] 	= $rows;
		$output['flag']		= $flag;
		$output['msg']		= $msg;

		echo json_encode($output);
	}

	public function delete_file_version()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();

		$orig_params 			= get_params();

		$delete_per 			= $this->delete_per;

		$msg 					= '';
		$flag 					= 0;
		$status 				= ERROR;

		$main_where 			= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			=  $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );
			check_salt( $params['file_version'], $params['file_version_salt'], $params['file_version_token'], $params['file_version_action'] );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'file_version_id'	=> $params['file_version']
			);

			$sub_where 		= array(
				'file_id'	=> $params['file_id']
			);

			$details 		= $this->files_mod->get_specific_file( $params['file_id'] );

			if( $details['created_by'] != $this->session->user_id )
			{
				$per_user_delete= $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_GROUPS );

				$all_delete 	=  $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_ALL );

				if( !$per_user_delete AND !$all_delete )
				{
					throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
				}
			}

			SYSAD_Model::beginTransaction();

			$module_path 		= NULL;
			$file_type_path 	= NULL;
			$path_dir 			= NULL;

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_TABLE_FILE_VERSIONS;
			$audit_action[] 			= AUDIT_DELETE;

			$prev_version 				= $this->file_versions->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
				$main_where
			);

			$prev_detail[] 				= $prev_version;

			$this->file_versions->delete_file_revisions( $main_where );

			$curr_detail[] 				= array();

			$prev_file 					= $this->files_mod->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_FILE,
				$sub_where
			);

			if( ISSET( $this->module_dir_map[ $prev_file[0]['module_code'] ] ) )
			{
				$module_path 	= $this->module_dir_map[ $prev_file[0]['module_code'] ];
			}

			if( ISSET( $this->file_type_dir_map[ $prev_version[0]['file_type'] ] ) )
			{
				$file_type_path = $this->file_type_dir_map[ $prev_version[0]['file_type'] ];
			}

			if( !EMPTY( $module_path ) AND !EMPTY( $file_type_path ) )
			{
				$path_dir = $module_path.$file_type_path.$prev_version[0]['file_name'];
				$path_dir = str_replace(array('\\','/'), array(DS,DS), $path_dir);
			}

			if( !EMPTY( $path_dir ) )
			{
				$this->unlink_attachment( $path_dir );
			}

			$latest_version 	= $this->file_versions->get_latest_version( $params['file_id'] );

			if( !EMPTY( $latest_version ) )   
			{
				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_TABLE_FILE;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $prev_file;

				$upd_val 					= array(
					'display_name'		=> $latest_version['display_name'],
					'file_name'			=> $latest_version['file_name'],
					'original_name'		=> $latest_version['original_name'],
					'description'		=> $latest_version['description'],
					'file_type'			=> $latest_version['file_type'],
					'file_extension'	=> $latest_version['file_extension'],
					'modified_by'		=> $this->session->user_id,
					'modified_date'		=> $this->date_now
				);

				$this->files_mod->update_files( $upd_val, $sub_where );

				$curr_detail[] 			= $this->files_mod->get_details_for_audit(
					SYSAD_Model::CORE_TABLE_FILE,
					$sub_where
				);
			}

			$audit_activity				= sprintf($this->lang->line( 'audit_trail_delete'), $prev_version[0]['display_name'] );

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();

			$msg 				= $this->lang->line('data_deleted');
			$flag 				= 1;
			$status 			= SUCCESS;

			$this->dt_options['post_data']			= $orig_params;
			$this->dt_version_options['post_data']	= $orig_params;
		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			"flag" 					=> $flag,
			"msg" 					=> $msg,
			"reload" 				=> 'datatable',
			"status" 				=> $status,
			"datatable_options" 	=> $this->dt_options,
			'extra_reload' 			=> 'datatable',
			'extra_datatable_options'	=> $this->dt_version_options
		);

		echo json_encode( $response );
	}

	public function save_multiple_files()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$audit_action 			= AUDIT_UPDATE;
		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= FALSE;
		$action 				= ACTION_ADD;

		$main_where 			= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			$permission 		= ( !$update ) ? $this->add_per : $this->edit_per;
			$per_msg 			= ( !$update ) ? $this->lang->line( 'err_unauthorized_add' ) : $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}

			$this->validate( $params, $orig_params, $action, TRUE );

			SYSAD_Model::beginTransaction();

			if( ISSET( $params['files'] ) AND !EMPTY( $params['files'] ) )
			{
				foreach( $params['files'] as $key => $file )
				{
					$prev_detail 			= array();
					$curr_detail 			= array();
					$audit_table 			= array();
					$audit_action 			= array();
					$audit_schema 			= array();

					$disp_name 				= NULL;
					$description 			= NULL;
					$orig_name 				= NULL;

					if( ISSET( $params['display_name_multi'][$key] ) AND !EMPTY( $params['display_name_multi'][$key] ) )
					{
						$disp_name 			= $params['display_name_multi'][$key];
					}

					if( ISSET( $params['file_description_multi'][$key] ) AND !EMPTY( $params['file_description_multi'][$key] ) )
					{
						$description 		= $params['file_description_multi'][$key];
					}

					if( ISSET( $params['files_orig_filename'][$key] ) AND !EMPTY( $params['files_orig_filename'][$key] ) )
					{
						$orig_name 			= $params['files_orig_filename'][$key];
					}

					$val 					= array();

					$val['module_code']		= $params['module'];
					$val['file_type']		= $params['file_type'];
					$val['file_name']		= $file;
					$val['original_name'] 	= $orig_name;
					$val['display_name'] 	= $disp_name;
					$val['description'] 	= $description;
					$val['created_by']		= $this->session->user_id;
					$val['created_date']	= $this->date_now;

					$audit_details 			= $this->save_helper( $update, $val, $params );

					$curr_file 				= $audit_details['curr_display_name'];

					$audit_schema 		= array_merge( $audit_schema, $audit_details['audit_schema'] );
					$audit_table 		= array_merge( $audit_table, $audit_details['audit_table'] );
					$audit_action 		= array_merge( $audit_action, $audit_details['audit_action'] );
					$prev_detail 		= array_merge( $prev_detail, $audit_details['prev_detail'] );
					$curr_detail 		= array_merge( $curr_detail, $audit_details['curr_detail'] );

					$main_where 		= array(
						'file_id'		=> $audit_details['file_id']
					);

					$check_file_visibility 			= $this->access_rights_mod->check_file_visibility( $main_where );

					if( EMPTY( $check_file_visibility ) OR EMPTY( $check_file_visibility['check_file_visibility'] ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VISIBILITY;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[]  	= array();

						$file_val 			= array();

						$actions_val 		= array( ACTION_EDIT, ACTION_VIEW, ACTION_DELETE );

						$file_val[0]['file_id']			= $audit_details['file_id'];
						$file_val[0]['user_id']			= '0';
						$file_val[0]['actions']			= implode(',', $actions_val);
						$file_val[0]['visibility_id']	= VISIBLE_ALL;

						$this->access_rights_mod->insert_file_visibility( $file_val );

						$curr_detail[] 		= $this->access_rights_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY,
												$main_where
											 );
					}

					$audit_name 				= 'Files '.$curr_file.'.';

					$audit_activity 			= sprintf( $this->lang->line('audit_trail_add'), $audit_name);

					$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
				}
			}

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );

			$this->dt_options['post_data']	= array(
				'file_type'			=> $orig_params['file_type'],
				'file_type_salt'	=> $orig_params['file_type_salt'],
				'file_type_token'	=> $orig_params['file_type_token'],
				'module'			=> $orig_params['module'],
				'module_salt'		=> $orig_params['module_salt'],
				'module_token'		=> $orig_params['module_token']
			);
		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e)
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status'				=> $status,
			'datatable_options' 	=> $this->dt_options,
			'file_type'				=> $params['file_type'],
			'module'				=> $params['module']
		);

		echo json_encode( $response );	
	}

	public function update_multiple_file_attachment()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$audit_action 			= AUDIT_UPDATE;
		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$main_where 			= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			$permission 		= $this->edit_per;
			$per_msg 			= $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}	

			SYSAD_Model::beginTransaction();

			if( ISSET( $params['files'] ) AND !EMPTY( $params['files'] ) )
			{
				foreach( $params['files'] as $key => $file )
				{
					if( ISSET( $params['file_prev_name'][$key] ) AND !EMPTY( $params['file_prev_name'][$key] ) )
					{
						$prev_main_where 		= array(
							'file_name'		=> $params['file_prev_name'][$key]
						);
						
						$file_type 				= $this->categorize_file( $file );

						$module_path 		= NULL;
						$file_type_path 	= NULL;
						$path_dir_file 		= NULL;

						$val 					= array();
						$val['modified_by']		= $this->session->user_id;
						$val['modified_date']	= $this->date_now;

						$val['file_name']		= $file;
						$val['file_type']		= $file_type['file_type'];
						$val['file_extension']	= $file_type['file_extension'];

						$prev_file 				= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
							$prev_main_where
						);
						
						if( !EMPTY( $prev_file ) )
						{
							$main_where 		= array(
								'file_id'		=> $prev_file[0]['file_id']
							);

							$sub_where 			= array(
								'file_id'		=> $prev_file[0]['file_id']
							);

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VERSIONS;
							$audit_action[] 	= AUDIT_UPDATE;
							$prev_detail[]  	= $this->file_versions->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
								$sub_where
							);

							$this->file_versions->update_file_revisions( $val, $sub_where );

							$curr_detail[] 		= $this->file_versions->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
								$sub_where
							);

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE;
							$audit_action[] 	= AUDIT_UPDATE;

							$prev_detail[]  	= $prev_file;

							$this->files_mod->update_files( $val, $main_where );

							$curr_file 			= $this->files_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE,
								$main_where
							);

							$curr_detail[] 		= $curr_file;

							$audit_name 				= 'Files '.$curr_file[0]['display_name'].'.';

							$audit_activity 			= sprintf($this->lang->line('audit_trail_update'), $audit_name);

							$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
						}
					}
				}
			}

			SYSAD_Model::commit();

			$this->dt_options['post_data']	= array(
				'file_type'			=> $orig_params['file_type'],
				'file_type_salt'	=> $orig_params['file_type_salt'],
				'file_type_token'	=> $orig_params['file_type_token'],
				'module'			=> $orig_params['module'],
				'module_salt'		=> $orig_params['module_salt'],
				'module_token'		=> $orig_params['module_token']
			);

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );

		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e)
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status'				=> $status,
			'datatable_options' 	=> $this->dt_options
		);

		echo json_encode( $response );	
	}

	public function delete_file()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();

		$orig_params 			= get_params();

		$delete_per 			= $this->delete_per;

		$msg 					= '';
		$flag 					= 0;
		$status 				= ERROR;

		$main_where 			= array();

		try
		{
			// $this->redirect_off_system($this->module);
				
			$params 		 	= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'file_id'	=> $params['file_id']
			);

			$details 		= $this->files_mod->get_specific_file( $params['file_id'] );

			if( !EMPTY( $details ) AND $details['created_by'] != $this->session->user_id )
			{
				$per_user_delete = $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_GROUPS );

				$all_delete 	=  $this->access_rights->check_access_permission( $params['file_id'], $this->session->user_id, ACTION_DELETE, VISIBLE_ALL );

				if( !$per_user_delete AND !$all_delete )
				{
					throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
				}
			}

			SYSAD_Model::beginTransaction();

			$module_path 		= NULL;
			$file_type_path 	= NULL;
			$path_dir 			= NULL;

			$prev_access 		= $this->access_rights_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS, 
										$main_where
									);

			if( !EMPTY( $prev_access ) )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 		= SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS;
				$audit_action[] 	= AUDIT_DELETE;

				$prev_detail[] 		= $prev_access;

				$this->access_rights_mod->delete_file_access_rights( 
					$main_where
				);

				$curr_detail[] 		= array();
			}

			$audit_schema[] 	= DB_CORE;
			$audit_table[] 		= SYSAD_Model::CORE_TABLE_FILE_VISIBILITY;
			$audit_action[] 	= AUDIT_DELETE;

			$prev_access_vis 	= $this->access_rights_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY, 
									$main_where
								);

			$prev_detail[] 		= $prev_access_vis;

			$this->access_rights_mod->delete_file_visibility( 
				$main_where
			);

			$curr_detail[] 		= array();

			// FILE AND FILE VERSION DELETE

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_TABLE_FILE_VERSIONS;
			$audit_action[] 			= AUDIT_DELETE;


			$prev_version 				= $this->file_versions->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_FILE_VERSIONS,
				$main_where
			);

			$prev_detail[] 				= $prev_version;

			$this->file_versions->delete_file_revisions( $main_where );

			$curr_detail[] 				= array();

			$prev_file 					= $this->files_mod->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_FILE,
				$main_where
			);

			if( ISSET( $this->module_dir_map[ $prev_file[0]['module_code'] ] ) )
			{
				$module_path 	= $this->module_dir_map[ $prev_file[0]['module_code'] ];
			}

			if( ISSET( $this->file_type_dir_map[ $prev_version[0]['file_type'] ] ) )
			{
				$file_type_path = $this->file_type_dir_map[ $prev_version[0]['file_type'] ];
			}

			$audit_schema[]			= DB_CORE;
			$audit_table[] 			= SYSAD_Model::CORE_TABLE_FILE;
			$audit_action[] 		= AUDIT_DELETE;
			$prev_detail[] 			= $prev_file;

			$this->files_mod->delete_file( $main_where );

			$curr_detail[] 				= array();

			$audit_activity				= sprintf($this->lang->line( 'audit_trail_delete'), $prev_file[0]['display_name'] );

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			if( !EMPTY( $module_path ) AND !EMPTY( $file_type_path ) )
			{
				if( !EMPTY( $prev_version ) )   
				{
					$prev_ver = array_column($prev_version, 'file_name');
				}

				$root_path 	= $this->get_root_path();

				$real_path 	= $root_path.$module_path.$file_type_path;
				$real_path 	= str_replace(array('\\', '/'), array(DS, DS), $real_path);

				foreach( $prev_ver as $file_name )
				{
					if( file_exists( $real_path.$file_name ) )
					{
						$this->unlink_attachment( $real_path.$file_name );
					}
				}

				$path_dir = $module_path.$file_type_path.$prev_file[0]['file_name'];
				$path_dir = str_replace(array('\\','/'), array(DS,DS), $path_dir);
			}

			SYSAD_Model::commit();

			$msg 				= $this->lang->line('data_deleted');
			$flag 				= 1;
			$status 			= SUCCESS;

			$this->dt_options['post_data']			= $orig_params;
		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			"flag" 					=> $flag,
			"msg" 					=> $msg,
			"reload" 				=> 'datatable',
			"status" 				=> $status,
			"datatable_options" 	=> $this->dt_options
		);

		echo json_encode( $response );
	}
	
}