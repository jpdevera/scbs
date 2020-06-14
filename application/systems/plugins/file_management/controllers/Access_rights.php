<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Access_rights extends SYSAD_Controller 
{
	private $module;
	private $table_id;
	private $path;
	private $module_js;

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	private $date_now;

	public function __construct()
	{
		parent::__construct();

		$this->module_js 		= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_FILE_MANAGEMENT."/access_rights";
		
		$this->load->module( CORE_FILE_MANAGEMENT.'/Files' );

		$this->load->model( 'Param_visibility_model', 'param_visibility' );
		$this->load->model( 'Access_rights_model', 'access_rights_o_mod' );
		$this->load->model(CORE_GROUPS.'/Groups_model', 'groups');
		$this->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'users');

		$this->module 			= $this->files->module;

		$this->view_per 		= $this->files->view_per;
		$this->edit_per 		= $this->files->edit_per;
		$this->add_per 			= $this->files->add_per;
		$this->delete_per 		= $this->files->delete_per;
	}

	private function _filter_params( array $orig_params )
	{
		$arr 				= $this->set_filter( $orig_params )
								->filter_string('file_type', TRUE)
								->filter_string('module', TRUE)
								->filter_number('file_id', TRUE)
								->filter_number('group_user', TRUE )
								->filter_string('file_type_vis', TRUE )
								->filter_string('module_vis', TRUE )
								->filter_number('file_id_vis', TRUE)
								->filter_string('file_display_name' )
								->filter_string('file_description' )
								->filter_number('access_rights_visibility', TRUE)
								->filter_number('access_rights_visibility_actions', TRUE)
								->filter_number('access_rights_visibility_group', TRUE)
								->filter_number('user_name', TRUE, array('explode' => TRUE) )
								->filter_number('group_id', TRUE)
								->filter_number('user_id', TRUE);

		$arr 				= $arr->filter();

		return $arr;
	}

	public function modal_access_rights()
	{
		$data 			= array();
		$resources		= array();

		$details 		= array();

		$orig_params 		= get_params();

		$visibilities 		= array();
		$action_access 		= array();
		$groups 			= array();

		$visible_constants 	= '';

		$visibility_details = array();
		$actions_all 		= array();
		$group_vis 			= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			if( !EMPTY( $orig_params['file_id'] ) )
			{
				check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

				$visibility_details 	= $this->access_rights_o_mod->get_file_visibility_details( $params['file_id'] );

				if( !EMPTY( $visibility_details ) AND !EMPTY( $visibility_details['actions'] ) )
				{
					$actions_all 		= explode(',', $visibility_details['actions']);
				}

				if( !EMPTY( $visibility_details ) AND !EMPTY( $visibility_details['group_ids'] ) )
				{
					$group_vis 			= explode(',', $visibility_details['group_ids']);
				}
			}


			$resources['load_js']			= array(JS_ADD_ROW, $this->module_js);
			$resources['loaded_init'] 		= array('selectize_init();');
			$resources['loaded_doc_init']	= array('Access_rights.init_modal("'.$params['file_type'].'", "'.$params['module'].'");',
				'Access_rights.visible_to("'.$params['file_type'].'");', 
				'Access_rights.access_rights_group("'.$params['file_type'].'");',
				'Access_rights.save("'.$params['file_type'].'");'
			);

			$js_file_constants 				= $this->get_constants( '^FILE_TYPE_', TRUE );
			$js_file_dir_constants 			= $this->get_constants( '^DIRECTORY_', TRUE );

			$directory_module_map 			= $this->files->module_dir_map;

			$directory_module_map_json 		= json_encode( $directory_module_map );

			$visible_constants 			= $this->get_constants( '^VISIBLE_', TRUE );

			$visibilities 				= $this->param_visibility->get_visibility();
			$action_access 				= $this->access_rights_o_mod->get_access_actions();
			$groups 					= $this->groups->get_all_groups();

			$data['visibility_details'] 	= $visibility_details;
			$data['group_vis'] 				= $group_vis;
			$data['actions_all'] 			= $actions_all;
			$data['js_file_constants']		= $js_file_constants;
			$data['js_file_dir_constants']	= $js_file_dir_constants;
			$data['visibilities']			= $visibilities;
			$data['action_access']			= $action_access;
			$data['groups']					= $groups;
			$data['visible_constants']		= $visible_constants;
			$data['directory_module_map_json']	= $directory_module_map_json;

			$data['params'] 				= $params;
			$data['orig_params'] 			= $orig_params;
			$data['orig_params_json'] 		= json_encode( $orig_params );

			$this->load->view('modals/access_rights', $data);
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

	public function load_access_rights()
	{

		$data 			 				= array();
		$resources 					 	= array();

		$orig_params 					= get_params();
		$access_rights 					= array();
		$actions 						= array();

		$users 							= array();
		$access_rights 					= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->_filter_params( $orig_params );	

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );

			if( !EMPTY( $orig_params['file_id'] ) )
			{
				check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

				$access_rights 			= $this->access_rights_o_mod->get_file_access_rights( $params['file_id'] );
			}

			$resources['loaded_init']	= array(
				'selectize_init();'
			);

			$resources['load_delete']	= array(
				'visibility' 			=> array(
					'delete_cntrl' 		=> 'Access_rights',
					'delete_method'		=> 'delete_access_rights',
					'delete_module'		=> CORE_FILE_MANAGEMENT
				)
			);

			$user_id_arg		= $this->session->user_id;

			$users 				= $this->users->get_all_users_active( $user_id_arg );
			$actions 			= $this->access_rights_o_mod->get_access_actions();

			if( ISSET( $params['group_user'] ) AND !EMPTY( $params['group_user'] ) )
			{
				$group_user 	= $this->groups->get_group_users( $params['group_user'], $user_id_arg, TRUE );
			}

			if( !EMPTY( $group_user ) )
			{
				$access_rights 	= array_merge( $access_rights, $group_user );
			}

			$data['users']				= $users;
			$data['actions']			= $actions;
			$data['access_rights']		= $access_rights;
			$data['orig_params'] 		= $orig_params;

			$this->load->view('tables/access_rights_table', $data );
			$this->load_resources->get_resource( $resources );
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

	private function _validate( array $params, $action = NULL )
	{
		$required 		= array();
		$constraints 	= array();

		$required['access_rights_visibility']	= 'Visibility';

		if( ISSET( $params['access_rights_visibility'] ) AND !EMPTY( $params['access_rights_visibility'] ) )
		{
			switch( $params['access_rights_visibility'] )
			{
				case VISIBLE_ALL :

					$required['access_rights_visibility_actions']		= 'Privilege For All';	
					/*$constraints['access_rights_visibility_actions']	= array(
						'data_type'	=> 'digit',
						'name'		=> 'Privilege For All'
					);*/

					if( ISSET( $params['access_rights_visibility_actions'] ) AND !EMPTY( $params['access_rights_visibility_actions'] ) )
					{
						$constraints['access_rights_visibility_actions']		= array(
							'name'			=> 'Action',
							'data_type'		=> 'db_value',
							'field'			=> ' COUNT( sys_param_value ) as check_sys ',
							'check_field'	=> 'check_sys',
							'where' 		=> 'sys_param_value',
							'table'			=> DB_CORE.'.'.SYSAD_Model::CORE_TABLE_SYS_PARAM
						);
					}


				break;

				case VISIBLE_GROUPS :
				case VISIBLE_INDIVIDUALS :

					$required['user_name']		= 'User';	
					/*$constraints['user_name']	= array(
						'name'		=> 'User'
					);*/

					if( $params['access_rights_visibility'] == VISIBLE_GROUPS ) 
					{	
						$required['access_rights_visibility_group']	= 'Groups';

						if( ISSET( $params['access_rights_visibility_group'] ) AND !EMPTY( $params['access_rights_visibility_group'] ) )
						{
							$constraints['access_rights_visibility_group']		= array(
								'name'			=> 'Groups',
								'data_type'		=> 'db_value',
								'field'			=> ' COUNT( group_id ) as check_grp ',
								'check_field'	=> 'check_grp',
								'where' 		=> 'group_id',
								'table'			=> DB_CORE.'.'.'`'.SYSAD_Model::CORE_TABLE_GROUPS.'`'
							);
						}
					}

					if( ISSET( $params['user_name'] ) AND !EMPTY( $params['user_name'] ) )
					{
						$constraints['user_name']		= array(
							'name'			=> 'Users',
							'data_type'		=> 'db_value',
							'field'			=> ' COUNT( user_id ) as check_user ',
							'check_field'	=> 'check_user',
							'where' 		=> 'user_id',
							'table'			=> DB_CORE.'.'.SYSAD_Model::CORE_TABLE_USERS
						);

						foreach( $params['user_name'] as $cnt => $user_name )
						{
							if( ISSET( $params['access_rights_priv'][ $cnt ] ) AND !EMPTY( $params['access_rights_priv'][ $cnt ] ) )
							{
								foreach( $params['access_rights_priv'][ $cnt ] as $cnt_priv => $priv )
								{
									$dec_priv 		= filter_var( base64_url_decode( $priv ), FILTER_SANITIZE_NUMBER_INT );

									$check_per_act 	= $this->access_rights_o_mod->get_access_actions( $dec_priv );
									// print_r($dec_priv);
									if( EMPTY( $check_per_act ) OR !ISSET( $check_per_act[0] ) OR EMPTY( $check_per_act[0] ) )
									{
										throw new Exception("Invalid Action at row ".( $cnt + 1 )."." );
									}
								}
							}
						}
					}

				break;
			}
		}

		
		$this->check_required_fields( $params, $required );

		$this->validate_inputs( $params, $constraints );
	}

	protected function process_visibility_value( array $params, $file_id, $action = NULL )
	{

		$val 					= array();

		$sub_val 				= array();
		
		if( ISSET( $params['access_rights_visibility'] ) AND !EMPTY( $params['access_rights_visibility'] ) )
		{
			switch( $params['access_rights_visibility'] )
			{
				case VISIBLE_ALL :

					$val[0]['file_id']		= $file_id;
					$val[0]['user_id']		= '0';

					if( ISSET( $params['access_rights_visibility_actions'] ) AND !EMPTY( $params['access_rights_visibility_actions'] ) )
					{
						$val[0]['actions']		= implode(',', $params['access_rights_visibility_actions']);
					}

					$val[0]['visibility_id']	= $params['access_rights_visibility'];

				break;

				case VISIBLE_ONLY_ME :

					$val[0]['file_id']		= $file_id;
					$val[0]['user_id']		= '0';
					$val[0]['visibility_id']	= $params['access_rights_visibility'];

				break;

				case VISIBLE_GROUPS :
				case VISIBLE_INDIVIDUALS :

					$sub_key 				= 0;

					if( ISSET( $params['user_name'] ) AND !EMPTY( $params['user_name'] ) )
					{
						foreach( $params['user_name'] as $f_cnt => $users )
						{
							if( ISSET( $params['group_id_enc'][ $f_cnt ] ) )
							{
								if( !EMPTY( $params['group_id_salt'][ $f_cnt ] ) )
								{
									$group_id_dec 				= filter_var( base64_url_decode( $params['group_id_enc'][ $f_cnt ] ), FILTER_SANITIZE_NUMBER_INT );

									check_salt( $group_id_dec, $params['group_id_salt'][ $f_cnt ], $params['group_id_token'][ $f_cnt ] );

									$val[ $f_cnt ]['group_id']	= $group_id_dec;
								}
								else
								{
									$val[ $f_cnt ]['group_id']	= NULL;
								}
							}

							$dec_user 						= $users;

							$val[ $f_cnt ]['file_id']		= $file_id;	

							if( $params['access_rights_visibility'] == VISIBLE_GROUPS ) 
							{
								$val[ $f_cnt ]['visibility_id']	= $params['access_rights_visibility'];
							}
							else
							{
								$val[ $f_cnt ]['visibility_id']	= VISIBLE_INDIVIDUALS;
							}

							$val[ $f_cnt ]['user_id']			= $dec_user;

							if( ISSET( $params[ 'access_rights_priv' ][ $f_cnt ] ) AND !EMPTY( $params['access_rights_priv'][ $f_cnt ] ) )
							{

								foreach( $params[ 'access_rights_priv' ][ $f_cnt ] as $s_cnt => $access )
								{
									$sub_val[ $sub_key ]['file_id']			= $file_id;
									$sub_val[ $sub_key ]['user_id']			= $dec_user;
									$sub_val[ $sub_key ]['actions']			= filter_var( base64_url_decode( $access ), FILTER_SANITIZE_NUMBER_INT );

									$sub_key++;
								}
							}

						}
					}

					/*print_r($params);
					throw new Exception("Error Processing Request");*/
				break;
			}

			
		}
		// print_r($sub_val);
		return array(
			"val"		=> $val,
			"sub_val"	=> $sub_val
		);
	
	}

	public function save()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 	= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= ( ISSET( $orig_params['file_id_vis'] ) AND !EMPTY( $orig_params['file_id_vis'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['file_id_vis'] ) AND !EMPTY( $orig_params['file_id_vis'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$main_where 			= array();

		$file_id_details 		= array();
		$file_id 				= NULL;

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type_vis'], $params['file_type_salt_vis'], $params['file_type_token_vis'] );
			check_salt( $params['module_vis'], $params['module_salt_vis'], $params['module_token_vis'] );

			$params['module'] 			= $params['module_vis'];
			$params['module_salt'] 		= $params['module_salt_vis'];
			$params['module_token']		= $params['module_token_vis'];
			$params['file_type'] 		= $params['file_type_vis'];
			$params['file_type_salt'] 	= $params['file_type_salt_vis'];
			$params['file_type_token'] 	= $params['file_type_token_vis'];
			$params['file_id'] 			= $params['file_id_vis'];
			$params['file_salt'] 		= $params['file_salt_vis'];
			$params['file_token'] 		= $params['file_token_vis'];
			$params['file_action'] 		= $params['file_action_vis'];


			$files_val 				= $this->files->validate( $params, $orig_params, $action );
			$val 					= $this->_validate( $params, $action );

			SYSAD_Model::beginTransaction();

			if( !$update )
			{
				$audit_file_details = $this->files->save_helper( $update, $files_val, $params );

				$audit_schema 		= array_merge( $audit_schema, $audit_file_details['audit_schema'] );
				$audit_table 		= array_merge( $audit_table, $audit_file_details['audit_table'] );
				$audit_action 		= array_merge( $audit_action, $audit_file_details['audit_action'] );
				$prev_detail 		= array_merge( $prev_detail, $audit_file_details['prev_detail'] );
				$curr_detail 		= array_merge( $curr_detail, $audit_file_details['curr_detail'] );

				$file_id 			= $audit_file_details['file_id'];
				$file_id_details 	= $this->generate_salt_token_arr( $file_id, ACTION_EDIT );

				$file_id_details['id_enc'];
				$file_id_details['salt'];
				$file_id_details['token_concat'];


			}
			else
			{
				check_salt( $params['file_id_vis'], $params['file_salt_vis'], $params['file_token_vis'], $params['file_action_vis'] );

				$audit_file_details = $this->files->save_helper( $update, $files_val, $params );

				$audit_schema 		= array_merge( $audit_schema, $audit_file_details['audit_schema'] );
				$audit_table 		= array_merge( $audit_table, $audit_file_details['audit_table'] );
				$audit_action 		= array_merge( $audit_action, $audit_file_details['audit_action'] );
				$prev_detail 		= array_merge( $prev_detail, $audit_file_details['prev_detail'] );
				$curr_detail 		= array_merge( $curr_detail, $audit_file_details['curr_detail'] );

				$file_id 			= $params['file_id_vis'];
				$file_id_details 	= $this->generate_salt_token_arr( $file_id, ACTION_EDIT );
			}

			$val 					= $this->process_visibility_value( $params, $file_id, $action );

			$main_where 			= array(
				'file_id'			=> $file_id
			);

			$check_file_visibility 	= $this->access_rights_o_mod->check_file_visibility( $main_where );

			if( !EMPTY( $val['val'] ) )
			{
				if( EMPTY( $check_file_visibility ) OR EMPTY( $check_file_visibility['check_file_visibility'] ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VISIBILITY;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->access_rights_o_mod->insert_file_visibility( $val['val'] );

					$curr_detail[] 		= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY,
											$main_where
										 );

					if( !EMPTY( $val['sub_val'] ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[]  	= array();

						$this->access_rights_o_mod->insert_file_access_rights( $val['sub_val'] );

						$curr_detail[] 		= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS,
												$main_where
											 );
					}
				}
				else
				{
				
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS,
											$main_where
										 );

					$this->access_rights_o_mod->delete_file_access_rights( $main_where );

					$curr_detail[] 		= array();
					

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VISIBILITY;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY,
											$main_where
										 );

					$this->access_rights_o_mod->delete_file_visibility( $main_where );

					$curr_detail[] 		= array();

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_VISIBILITY;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->access_rights_o_mod->insert_file_visibility( $val['val'] );

					$curr_detail[] 		= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY,
											$main_where
										 );

					if( !EMPTY( $val['sub_val'] ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[]  	= array();

						$this->access_rights_o_mod->insert_file_access_rights( $val['sub_val'] );

						$curr_detail[] 		= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS,
												$main_where
											 );
					}
				}
			}

			if( !EMPTY( $audit_schema ) )
			{

				$audit_name 				= 'File access right for '.$params['file_display_name'].'.';

				$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

				$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
			}

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );

			$this->files->dt_options['post_data']	= array(
				'file_type'			=> $orig_params['file_type_vis'],
				'file_type_salt'	=> $orig_params['file_type_salt_vis'],
				'file_type_token'	=> $orig_params['file_type_token_vis'],
				'module'			=> $orig_params['module_vis'],
				'module_salt'		=> $orig_params['module_salt_vis'],
				'module_token'		=> $orig_params['module_token_vis']
			);

			$orig_params['file_id'] 	= $file_id_details['id_enc'];
			$orig_params['file_salt'] 	= $file_id_details['salt'];
			$orig_params['file_token'] 	= $file_id_details['token_concat'];
			$orig_params['file_action'] = $file_id_details['sub_id_1'];
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
			'datatable_options' 	=> $this->files->dt_options,
			'file_type'				=> $params['file_type'],
			'module'				=> $params['module'],
			'orig_params' 			=> $orig_params
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

	public function delete_access_rights()
	{
		$orig_params 			= get_params();
		$flag  					= 0;
		$msg 					= '';

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();
		$audit_activity 		= '';

		$delete_per 			= $this->delete_per;

		$status 				= ERROR;

		$main_where 			= array();

		$file_type 				= NULL;

		try
		{
			// $this->redirect_off_system($this->module);
			
			$params 			= $this->_filter_params( $orig_params );

			check_salt( $params['file_type'], $params['file_type_salt'], $params['file_type_token'] );
			check_salt( $params['module'], $params['module_salt'], $params['module_token'] );
			check_salt( $params['file_id'], $params['file_salt'], $params['file_token'], $params['file_action'] );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$file_type 			= $params['file_type'];

			$main_where 		= array(
				'file_id'		=> $params['file_id'],
				'user_id'		=> $params['user_id']
			);

			$sub_where 		= array(
				'file_id'		=> $params['file_id'],
				'user_id'		=> $params['user_id']
			);

			SYSAD_Model::beginTransaction();

			$prev_access 		= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS, 
										$main_where
									);

			if( !EMPTY( $prev_access ) )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 		= SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS;
				$audit_action[] 	= AUDIT_DELETE;

				$prev_detail[] 		= $prev_access;

				$this->access_rights_o_mod->delete_file_access_rights( 
					$main_where
				);

				$curr_detail[] 		= array();
			}

			$audit_schema[] 	= DB_CORE;
			$audit_table[] 		= SYSAD_Model::CORE_TABLE_FILE_VISIBILITY;
			$audit_action[] 	= AUDIT_DELETE;

			$prev_access_vis 	= $this->access_rights_o_mod->get_details_for_audit( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY, 
									$main_where
								);

			$prev_detail[] 		= $prev_access_vis;

			$this->access_rights_o_mod->delete_file_visibility( 
				$main_where
			);

			$curr_detail[] 		= array();

			$audit_activity		= sprintf($this->lang->line( 'audit_trail_delete'), 'Access right' );

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();


			$msg 				= $this->lang->line('data_deleted');
			$flag 				= 1;
			$status 			= SUCCESS;

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
			"reload" 				=> 'function',
			"status" 				=> $status,
			'function' 				=> 'Access_rights.visible_to("'.$file_type.'");'
		);

		echo json_encode( $response );
	}

	public function check_access_permission( $file_id, $user_id, $action = NULL, $visibility = VISIBLE_ALL )
	{
		$check 			= FALSE;

		try
		{
			if( EMPTY( $action ) )
			{
				$check_has_access 	= $this->access_rights_o_mod->check_file_visibility(
					array(
						'file_id'	=> $file_id,
						'user_id'	=> $user_id
					)
				);

				if( !EMPTY( $check_has_access ) AND !EMPTY( $check_has_access['check_asset_visibility'] ) )
				{
					$check 			= TRUE;
				}
			}
			else
			{
				switch( $visibility )
				{
					case VISIBLE_ALL :

						$check_all_access 	= $this->access_rights_o_mod->check_all_access( $file_id, $action );

						if( !EMPTY( $check_all_access ) AND !EMPTY( $check_all_access['check_all_access'] ) )
						{
							$check 			= TRUE;
						}

					break;

					case VISIBLE_GROUPS :
					case VISIBLE_INDIVIDUALS :

						$check_user_access 	= $this->access_rights_o_mod->check_access_per_user_action( array(
							'file_id'	=> $file_id,
							'user_id' 	=> $user_id,
							'actions'	=> $action
						) );

						if( !EMPTY( $check_user_access ) AND !EMPTY( $check_user_access['check_access_rights'] ) )
						{
							$check 		= TRUE;
						}

					break;

					case VISIBLE_ONLY_ME :

					break;
				}
			}
		}
		catch( PDOException $e )
		{
			$this->rlog_error( $e );
			throw $e;
		}
		catch( Exception $e )
		{
			$this->rlog_error( $e );
			throw $e;
		}

		return $check;
	}
}