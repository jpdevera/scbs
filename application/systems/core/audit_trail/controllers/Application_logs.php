<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Application_logs extends SYSAD_Controller 
{
	protected $view_per 		= FALSE;
	protected $delete_per 		= FALSE;

	private $table_id 			= 'table_application_logs';
	private $path 				= '';
	private $date_now;

	private $module_js;

	protected $fields 				= array();
	protected $filter 				= array();
	protected $order 				= array();

	public function __construct()
	{
		parent::__construct();

		$this->date_now 		= date('Y-m-d H:i:s');

		$this->controller 		= strtolower(__CLASS__);

		$this->module 			= MODULE_APPLICATION_LOGS;

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
		

		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_AUDIT_TRAIL."/application_logs";

		$this->path 				= CORE_AUDIT_TRAIL.'/Application_logs'.'/get_list/';	

		$this->data_table_opt_gen 	= array(
			'table_id'	 		=> $this->table_id,
			'path' 				=> $this->path,
			'advanced_filter' 	=> true,
			'with_search'		=> true,
			'order' 			=> 1, 
			'sort_order' 		=> 'desc',
			'add_multi_del'		=> array(
				'msg' 			=> 'Log File(s)',
				'delete_path' 	=> CORE_AUDIT_TRAIL.'/Application_logs/delete',
				'extra_data' 	=> array(
					'schema' 	=> DB_CORE,
					'module'	=> $this->module
				),
			),
			'no_export' 		=> true,
			'no_colvis' 		=> true
		);

	}


	public function index($param = NULL)
	{
		$data = $resources = array();

		$tabs 		= array();

		$this->redirect_module_permission( $this->module );

		try
		{
			$resources['load_css'] 		= array( CSS_LABELAUTY, CSS_DATETIMEPICKER, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATATABLE_BUTTONS );
			$resources['load_js'] 		= array( JS_SELECTIZE, JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_BUTTON_EXPORT_EXTENSION, $this->module_js );
		
			$table_options 				= $this->data_table_opt_gen;

			$logs_dir 	= glob(APPPATH .  'logs'.DS.'*');
			
			foreach( $logs_dir as $dir )
			{
				if( is_dir($dir) )
				{
					$clean_dir 	= str_replace(APPPATH .  'logs'.DS, '', $dir);

					$tabs[] 	= $clean_dir;
				}
			}

			$json_datatable_options = json_encode( $table_options );

			$resources['datatable']		= $table_options;

			$resources['loaded_init'] 	= array(
				'materialize_select_init();',
				"refresh_new_datatable_params('".$this->table_id."');",
				"Application_logs.initForm('".$json_datatable_options."');",
			);

			$resources['load_delete']	= array(
				'application_log'	=> array(
					'delete_cntrl' 		=> 'Application_logs',
					'delete_method'		=> 'delete',
					'delete_module'		=> CORE_AUDIT_TRAIL
				)
			);

			/*$resources['load_materialize_modal'] = array(
				'modal_announcement' => array (
					'title' 		=> "Announcement",
					'size'			=> "md",
					'module' 		=> CORE_ANNOUNCEMENTS,
					'method'		=> 'modal_announcement',
					'controller'	=> __CLASS__,
					'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),
					'post' 			=> true
				),

			);

			$resources['load_delete']	= array(
				'announcement'	=> array(
					'delete_cntrl' 		=> 'Announcement',
					'delete_method'		=> 'delete',
					'delete_module'		=> CORE_ANNOUNCEMENTS
				)
			);*/
		}
		catch( PDOException $e )
		{	
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );

		}
		catch( Exception $e )
		{
			$msg  	= $this->rlog_error($e);	

			$this->error_index( $msg );

		}

		$data['tabs'] 				= $tabs;
		$data['table_id']			= $this->table_id;

		$this->template->load('application_logs', $data, $resources);
	}

	public function get_list($system = NULL)
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();

		$resources 				= array();

		try
		{
			$params 			= get_params();

			$logs_dir 	= glob(APPPATH .  'logs'.DS.'*');
			$dir_path 	= array();
			
			foreach( $logs_dir as $dir )
			{
				if( is_dir($dir) )
				{
					$clean_dir 	= str_replace(APPPATH .  'logs'.DS, '', $dir);

					$tabs[] 	= $clean_dir;

					$dir_path[$clean_dir] = $dir;
				}
			}

			$sel_system 		= ( !EMPTY( $system ) ) ? $system : $tabs[0];

			if( ISSET( $dir_path[$sel_system] ) )
			{
				$log_files 		= glob( $dir_path[$sel_system].DS.'*.txt' );

				if( !EMPTY( $params['sSearch'] ) )
				{

					$log_files 	= array_filter( $log_files, function( $value ) use( &$params ) {

						$regex 		= '/.*' . strtolower($params['sSearch']) . '/i';
						// var_dump(!EMPTY(preg_match($regex, strtolower($value))));
						return !EMPTY(preg_match($regex, strtolower($value)));

					} );

					// print_r($mov_row_arr);
				}

				if( ISSET($params['action']) AND ISSET($params['log_file']) AND !EMPTY( $params['log_file'] ) )
				{

					$log_files 	= array_filter( $log_files, function( $value ) use( &$params ) {

						$regex 		= '/.*' . strtolower($params['log_file']) . '/i';
						// var_dump(!EMPTY(preg_match($regex, strtolower($value))));
						return !EMPTY(preg_match($regex, strtolower($value)));

					} );

					// print_r($mov_row_arr);
				}

				arsort($log_files);		

				if( ISSET( $params['sSortDir_0'] ) AND $params['sSortDir_0'] == 'asc' AND 
						( ISSET( $params['sEcho'] ) AND intval($params['sEcho'] ) != 1 )
				)
				{
					asort($log_files);
				}


				if( ISSET( $params['sSortDir_0'] ) AND $params['sSortDir_0'] == 'desc' AND 
					( ISSET( $params['sEcho'] ) AND intval($params['sEcho'] ) != 1 )
				)
				{
					arsort($log_files);							
				}

				$start 			= $params['iDisplayStart'];
				$dis_length 	= $params['iDisplayLength'];

				$limit_arr 		= array_slice($log_files, $start, $dis_length);
				$fil_count 		= count($limit_arr);
				
				$cnt_result 		= count($log_files);

				$counter 			= 0;

				$output['sEcho'] 				= $params['sEcho'];
				$output['iTotalRecords'] 		= $cnt_result;
				$output['iTotalDisplayRecords'] = $cnt_result;
				$output['aaData']				= array();

				if(! EMPTY($limit_arr))
				{
					foreach($limit_arr as $r)
					{
						$file_name 		= str_replace($dir_path[$sel_system].DS, '', $r);

						$salt 		= gen_salt();

						$id_detail 	= array(
							$r,
							$salt
						);

						$token_edit 	= $this->generate_salt_token_arr( $id_detail, ACTION_EDIT, $this->module );

						$token_view 	= $this->generate_salt_token_arr( $id_detail, ACTION_VIEW, $this->module );

						$token_delete 	= $this->generate_salt_token_arr( $id_detail, ACTION_DELETE, $this->module );

						$post_delete_json = json_encode( array(
							'log_path'	=> base64_url_encode($r),
							'salt'			=> $salt,
							'token'			=> $token_delete['token_concat'],
							'action'		=> ACTION_DELETE,
							'module'		=> base64_url_encode($this->module)
						) );

						$del_json_obj = json_encode(array(
							'log_path' 	=> base64_url_encode($r),
							/*'salt'		=> $salt,
							'token' 	=> in_salt($encrypt_id . '/' . $this->security_action_del, $salt),
							'action' 	=> $this->security_action_del*/
						));

						$delete_class	= 'disabled';


						$link_download 		= base_url().CORE_AUDIT_TRAIL.'/Application_logs/download/'.base64_url_encode($r).'/download';

						$actions 			= '';

						$actions 			.= "<div class='table-actions'>";

						if( $this->view_per )
						{
							$link_view 		= base_url().CORE_AUDIT_TRAIL.'/Application_logs/download/'.base64_url_encode($r).'/view';
							$actions .= "<a href='".$link_view."' class='tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' target='_blank'><i class='grey-text material-icons'>visibility</i></a>";

							$actions .= "<a href='".$link_download."' class='tooltipped' data-tooltip='Download' data-position='bottom' data-delay='50' target='_blank'><i class='grey-text material-icons'>file_download</i></a>";
						}

						
						if( $this->delete_per )
						{
							$delete_class  = '';

							$delete_action = 'content_application_log_delete("Log File", "", "", this)';

							$actions .= "<a href='javascript:;' data-delete_post='".$post_delete_json."' onclick='".$delete_action."' class='tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
						}

						$actions .= "</div>";

						$counter++;


						/*if($cnt_result == $counter)
						{
							$resources['preload_modal'] = array("modal_announcement");
							// $resources['loaded_init'] = array("selectize_init();");
							$actions .= $this->load_resources->get_resource($resources, TRUE);
						}*/

						$rows[] 		 	= array(
							"<input type='hidden' data-disabled='".$delete_class."' class='dt_details ".$delete_class."' data-delete_post='".$del_json_obj."'>".'<a href="'.$link_download.'" target="_blank">'.$file_name.'</a>',
							$actions
						);
					}

					$output['iTotalRecords'] = $counter;
				}
			}
			

			$flag 				= 1;
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

	public function download($path, $type)
	{
		try
		{
			$decode_path = base64_url_decode($path);
			if( file_exists($decode_path) )
			{
				$arr 	= pathinfo($decode_path);

				$content	= file_get_contents($decode_path);

				if( EMPTY( $content ) )
				{
					throw new Exception('File Empty.');
					// header('Location:'.base_url().CORE_AUDIT_TRAIL.'/Application_logs');
				}
				
				if( $type == 'download' )
				{
					$this->load->helper('download');

					force_download( $arr['basename'], $content, $arr['extension'] );
				}
				else if( $type == 'view' )
				{
					echo '<pre>';
					echo $content;
				}
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
			
			$this->error_index( $msg );
		}
		catch(Exception $e)
		{
			$msg  	= $this->rlog_error($e, TRUE);
			
			$this->error_index( $msg );
		}
		
	}

	private function _filter( array $orig_params )
	{
		$par 			= $this->set_filter( $orig_params )
							->filter_string('log_path', TRUE)
							->filter_string('module', TRUE)
							;

		$params 		= $par->filter();
		
		return $params;

	}

	public function delete()
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
			$params 			= $this->_filter( $orig_params );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			if( !is_array($params['log_path']) )
			{
				check_salt( $params['log_path'], $params['salt'], $params['token'], $params['action'], $this->module );

				if( file_exists($params['log_path']) )  
				{
					$this->unlink_attachment($params['log_path']);
				}
			}
			else
			{
				foreach( $params['log_path'] as $log_path )
				{
					if( file_exists($log_path) )  
					{
						$this->unlink_attachment($log_path);
					}
				}
			}

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_deleted' );

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
			'reload' 				=> 'datatable',
			'datatable_id' 			=> $this->table_id
		);

		echo json_encode( $response );

    }
}