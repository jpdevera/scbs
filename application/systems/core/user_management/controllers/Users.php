<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends SYSAD_Controller 
{

	private $table_id;
	private $path;
	private $module_js;

	private $download_per 		= FALSE;
	private $import_per 		= FALSE;

	private $dt_options 			= array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module_code	= MODULE_USER;
		$this->table_id 	= 'users_table';
		$this->path 		= CORE_USER_MANAGEMENT.'/users/get_user_list';
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_USER_MANAGEMENT."/users";
		
		$this->load->model('users_model', 'users');
		$this->load->model('organizations_model', 'orgs');
		$this->load->model('roles_model', 'roles');
		
		$this->load->model(CORE_GROUPS.'/Groups_model', 'groups');
		$this->load->model(CORE_GROUPS.'/User_groups_model', 'user_groups');

		$this->dt_options 			= array(
			'table_id' 			=> $this->table_id,
			'path' 				=> $this->path,
			'advanced_filter' 	=> true, 
			'with_search' 		=> true,
			'export_file_name'	=> 'users',
			'export_title'		=> 'Users',
			'hidden_column'		=> 3,
			// 'select'			=> true,
			'search_func' 		=> 'Users.search_func(search_params);',
			'func_callback' => 'Users.func_callback(tb, default_setting);',
			'extra_bulk_custom_button'=> array(
				'deactivate'	=> array(
					'text' 	=> 'Deactivate'
				),
				'block' 		=> array(
					'text'	=> 'Block'
				),
				'activate'	=> array(
					'text'	=> 'Activate'
				),
				'unblock'	=> array(
					'text'	=> 'Unblock'
				)
			),
			'add_multi_del'		=> array(
				'msg' 			=> 'User',
				'delete_path' 	=> CORE_USER_MANAGEMENT.'/Users/delete_multi_dt',
				'extra_data' 	=> array(
					'schema' 	=> DB_CORE,
					'module'	=> $this->module_code
				),
				'check_callback'	=> 'Users.check_callback(self, default_setting, tb);',
				'custom_button_func'=> 'Users.custom_button_func(rows, default_setting, table_obj, tb);',
				'tables' 		=> array(
				)
			)
		);
		
		
		try
		{
			// CHECK MODULE PERMISSIONS
			$this->permission_module	= $this->permission->check_permission($this->module_code);
			$this->permission_view		= $this->permission->check_permission($this->module_code, ACTION_VIEW);
			$this->permission_add		= $this->permission->check_permission($this->module_code, ACTION_ADD);
			$this->permission_edit		= $this->permission->check_permission($this->module_code, ACTION_EDIT);
			$this->permission_delete	= $this->permission->check_permission($this->module_code, ACTION_DELETE);

			$this->download_per 	= $this->permission->check_permission( $this->module_code, ACTION_DOWNLOAD );
			$this->import_per 		= $this->permission->check_permission( $this->module_code, ACTION_IMPORT );
			
			// Set security variables
			$encrypt_module = $this->encrypt($this->module_code);
			
			$this->security_action_add	= $encrypt_module . $this->encrypt(ACTION_ADD);
			$this->security_action_edit	= $encrypt_module . $this->encrypt(ACTION_EDIT);
			$this->security_action_del	= $encrypt_module . $this->encrypt(ACTION_DELETE);
			$this->security_action_view	= $encrypt_module . $this->encrypt(ACTION_VIEW);
			
			$this->is_construct_error 	= FALSE;
			$this->construct_error_msg 	= NULL;
		}
		catch (PDOException $e)
		{
			$this->is_construct_error	= TRUE;
			$this->construct_error_msg	= $this->get_user_message($e);
		}
		catch (Exception $e)
		{
			$this->is_construct_error	= TRUE;
			$this->construct_error_msg	= $e->getMessage();
		}		
	}
	

	public function index($param = NULL)
	{
		$data 			= array();
		$resources 		= array();
		
		try
		{	
			// IF ERROR IN __construct()
			if($this->is_construct_error)
				throw new Exception($this->construct_error_msg);
				
			// CHECK PERMISSION IN MODULE
			if ( ! $this->permission_module)
				throw new Exception($this->lang->line('err_unauthorized_access'));
			
			$data['statistics'] 	= $this->users->get_user_status_count();			
			
			$datatable_options 			= $this->dt_options;
			$datatable_options['path'] 	= $this->path . '/' . STATUS_ACTIVE;
			
			$data['param'] 				= $param;
			$resources['load_css'] 		= array(CSS_LABELAUTY, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATATABLE_BUTTONS, CSS_DATATABLE_SELECT);
			$resources['load_js'] 		= array(JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_BUTTON_EXPORT_EXTENSION, JS_DATATABLE_SELECT, $this->module_js);
			$resources['datatable'] 	= $datatable_options;

			$json_datatable_options 	= json_encode( $datatable_options );
			
			$active_users 				= $this->_construct_active_users_list();

			$active_users 				= '
				<nav class="cd-side-sub-nav">
					<a href="javascript:;" id="toggle-side-sub-nav"></a>
					<ul class="list-sub-nav scroll-pane scroll-dark" style="height:calc(100%-156px);" id="active_users_list">
						'.$active_users.'
					</ul>
				</nav>
';

			$data['sub_nav'] 			= $active_users;
			$resources['load_materialize_modal'] = array (
				'modal_user_mgmt' => array (
					'title' => 'User',
					'size' 	=> "lg",
					'module' => CORE_USER_MANAGEMENT,
					'controller' => __CLASS__,
					'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),
				),
				 'modal_org_import' 					=> array(
			    	'title' 		=> "Import Users",
			    	'size' 			=> "xxs sm-h",
					'module' 		=> CORE_USER_MANAGEMENT,
					'method'		=> 'modal_org_import',
					'controller'	=> 'Organizations',
					'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),
					'post' 			=> true
			    ),
				  'modal_error_import'	=> array(
			    	'title'			=> 'Import Error',
			    	'size' 			=> "full",
			    	'module' 		=> CORE_USER_MANAGEMENT,
					'method'		=> 'modal_error_import',
					'controller'	=> 'Organizations',
					'post' 			=> true,
					'permission' 	=> false
			    )
			);
			
			/* STATUS FILTERING OPTIONS */
			$active_datatable_options 			= $datatable_options;
			$active_datatable_options['path'] 	= $this->path . '/' . STATUS_ACTIVE;
			$json_active_datatable_options 		= json_encode( $active_datatable_options);
			
			$inactive_datatable_options			= $datatable_options;
			$inactive_datatable_options['path'] = $this->path . '/' . STATUS_INACTIVE;
			$json_inactive_datatable_options 	= json_encode( $inactive_datatable_options);
			
			$blocked_datatable_options			= $datatable_options;
			$blocked_datatable_options['path'] 	= $this->path . '/' . STATUS_BLOCKED;
			$json_blocked_datatable_options 	= json_encode( $blocked_datatable_options);
			
			$resources['loaded_init'] 	= array(
				'Users.initObj();',
				"refresh_datatable('".$json_datatable_options."');",
				"refresh_datatable('".$json_active_datatable_options."','#link_active_btn');",
				"refresh_datatable('".$json_inactive_datatable_options."','#link_inactive_btn');",
				"refresh_datatable('".$json_blocked_datatable_options."','#link_blocked_btn');"
			);

			$data['add_per']		= $this->permission_add;
			$data['import_per']		= $this->import_per;
			$data['download_per']	= $this->download_per;
			
			// START: Construct security variables for adding a record
			$encrypt_id	= $this->encrypt(0);
			$salt		= gen_salt();
			$token		= in_salt($encrypt_id . '/' . $this->security_action_add, $salt);
			
			$data['security']	= $encrypt_id . '/' . $salt . '/' . $token . '/' . $this->security_action_add;
			$data['module']		= base64_url_encode($this->module_code);
			// END: Construct security variables
			
			$this->template->load('users', $data, $resources);
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
	
	
	public function get_user_list($filter_status = NULL)
	{
		
		// Variables needed for the datatable 		
		$total_records	= $display_records = $flag = 0;
		$table_data		= array();
		$msg			= $this->lang->line('err_page_500_heading');
		
		try 
		{
			$params	= get_params();
			
			if( ISSET( $params['status'] ) )
			{
				$filter_status 	= $params['status'];
			}


			if( EMPTY( $filter_status ) )
			{
				$filter_status 	= STATUS_ACTIVE;
			}
			
			$total_records		= $this->users->get_user_list($filter_status);

			$records_info 		= $this->users->get_user_list($filter_status, $params);
			$records			= $records_info['records'];
			$display_records	= $records_info['display_records'];
			
			foreach($records as $record)
			{
				$avatar = $this->_construct_avatar($record);

				$reason_field = '';
				
				// Construct the dynamic column
				if( ! is_null($filter_status))
				{
					if($filter_status == STATUS_INACTIVE)
					{
						$column = date_format(date_create($record["last_logged_in_date"]), "m/d/Y \<br/> h:ia");
					}
					else
					{
						$column = $record["roles"];						
					}

					switch( $filter_status )
					{
						case STATUS_INACTIVE :
						case STATUS_EXPIRED :
						case STATUS_BLOCKED :
							$reason_field = $record['reason_remarks'];
						break;

						default :
							$reason_field = $record['date_created'];
						break;
					}
				}
				else 
				{
					$column = $record["sys_param_name"];
				}
				
				// Construct table actions
				$encrypt_id	= $this->encrypt($record["user_id"]);				
				$salt		= gen_salt();
				$actions	= '';

				$del_json_obj = json_encode(array(
					'user_id' 	=> $encrypt_id,
					/*'salt'		=> $salt,
					'token' 	=> in_salt($encrypt_id . '/' . $this->security_action_del, $salt),
					'action' 	=> $this->security_action_del*/
				));

				switch( $filter_status )
				{
					case STATUS_INACTIVE :
					case STATUS_EXPIRED :

						$token_ac		= in_salt($encrypt_id . '/' . $this->security_action_del, $salt);
						$url_act		= $encrypt_id . '/' . $salt . '/' . $token_ac . '/' . $this->security_action_del.'/'.ACTIVE;
						
						$delete_action_act	= 'content_delete("user","'.$url_act.'", undefined, "activate", true)';

						$actions.= "<a href='javascript:;' onclick='".$delete_action_act."' class='tooltipped' data-tooltip='Activate' data-position='bottom' data-delay='50'><i class='material-icons'>check_circle</i></a>";

					break;

					case STATUS_BLOCKED :

						$token_bl		= in_salt($encrypt_id . '/' . $this->security_action_del, $salt);
						$url_bl		= $encrypt_id . '/' . $salt . '/' . $token_bl . '/' . $this->security_action_del.'/'.ACTIVE;
						
						$delete_action_bl	= 'content_delete("user","'.$url_bl.'", undefined, "unblock", true)';

						$actions.= "<a href='javascript:;' onclick='".$delete_action_bl."' class='tooltipped' data-tooltip='Unblock' data-position='bottom' data-delay='50'><i class='material-icons'>refresh</i></a>";

					break;

					default : 

						$new_salt 	= gen_salt();

						if($this->permission_view)
						{
							$token_v		= in_salt($encrypt_id . '/' . $this->security_action_edit, $new_salt);
							$url_v		= $encrypt_id . '/' . $new_salt . '/' . $token_v . '/' . $this->security_action_edit.'/'.ACTION_VIEW;
							
							$actions.= "<a href='#modal_user_mgmt' class='tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_user_mgmt_init('".$url_v."', undefined, 'View User')\"><i class='material-icons'>visibility</i></a>";					
						}

					break;

				}
				
				if($this->permission_edit)
				{
					$token		= in_salt($encrypt_id . '/' . $this->security_action_edit, $salt);
					$url		= $encrypt_id . '/' . $salt . '/' . $token . '/' . $this->security_action_edit;
					
					$actions.= "<a href='#modal_user_mgmt' class='tooltipped' data-tooltip='Edit' data-position='bottom' data-delay='50' onclick=\"modal_user_mgmt_init('".$url."')\"><i class='material-icons'>mode_edit</i></a>";					
				}
				
				if($this->permission_delete)
				{
					$token		= in_salt($encrypt_id . '/' . $this->security_action_del, $salt);
					$url		= $encrypt_id . '/' . $salt . '/' . $token . '/' . $this->security_action_del;
					
					$delete_action	= 'content_delete("user","'.$url.'")';
					$delete_class	= '';
					$delete_tooltip	= 'Delete';
					
					if( ! empty($record['built_in_flag']) )
					{
						$delete_action	= '';
						$delete_class	= 'disabled';
						$delete_tooltip	= 'You are not allowed to delete this record';
					}
										
					$actions.= "<a href='javascript:;' onclick='".$delete_action."' class='tooltipped ".$delete_class."' data-tooltip='".$delete_tooltip."' data-position='bottom' data-delay='50'><i class='material-icons'>delete</i></a>";
				}
				
				$action 	= "<div class='table-actions'>";
				
				$table_data[] = array(
					$avatar . "<input type='hidden' data-disabled='".$delete_class."' class='dt_details ".$delete_class."' data-delete_post='".$del_json_obj."'>".$record["username"],
					$record["merge_name"],
					$record["organization_name"],
					$record["email"],
					$column,
					$reason_field,
					'<div class="table-actions">' . $actions . '</div>'
				);
			}
			
			$flag	= 1;
			$msg	= "";
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
			
		}
		
		echo json_encode(
			array(
				'aaData'				=> $table_data,
				'sEcho'					=> intval($params['sEcho']),
				'iTotalRecords'			=> $total_records,
				'iTotalDisplayRecords'	=> $display_records,
				'flag'					=> $flag,
				'msg'					=> $msg
			)
		);
	}
	
	
	private function _construct_avatar($record)
	{
		try 
		{
			$photo_path = $is_online = '';
			$img_src	= base_url().PATH_IMAGES . "avatar.jpg";

			$is_online = ($record['logged_in_flag'] == LOGGED_IN_FLAG_YES) ? "<span class='online'></span>" : "";
			
			if( ! empty($record['photo']) )
			{
				$root_path  = $this->get_root_path();
				$photo_path = $root_path . PATH_USER_UPLOADS . $record['photo'];
				$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);
				
				if( file_exists( $photo_path ) )
				{
					
					$check_upl = $this->check_custom_path();
					
					if( ! empty($check_upl) )
					{
						$img_src = output_image($record['photo'], PATH_USER_UPLOADS);
					}
					else
					{
						$img_src = base_url() . PATH_USER_UPLOADS . $record['photo'];
					}
				}				
			}
			
			$contact_flag = ($record['contact_flag'] == 1) ? "<i class='material-icons small'>fiber_manual_record</i>" : "";
			
			if( ! empty($photo_path) )
			{
				$img = '<img class="avatar" width="20" height="20" src="'.$img_src.'" /> ' . $contact_flag;
			}
			else
			{
				$img = '<img class="avatar default-avatar" data-name="'.$record['fname'].'" /> ' . $contact_flag;
			}
			
			return '<span class="table-avatar-wrapper">' . $img . $is_online.'</span>';

		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	public function modal($encrypt_id, $salt, $token, $security_action, $extra_act = NULL)
	{
		$main_orgs = array();
		$other_orgs = array();

		$disabled_str = '';
		$disabled_upl = '';

		$add_b 		= FALSE;
		$det 		= array();

		try 
		{
			// Check security variables
			check_salt($encrypt_id, $salt, $token, $security_action);

			if( $extra_act == ACTION_VIEW )
			{
				$disabled_str = 'disabled';
				$disabled_upl = true;
			}
			
			$user_info	= $this->users->get_user_details($this->decrypt($encrypt_id));
			$user_id	= isset($user_info['user_id']) ? $user_info['user_id'] : 0;
			
			$data = $resources = $other_roles = $main_role = array();			
			
			// Check allowed security actions
			switch($security_action)
			{
				case $this->security_action_add:
					if( ! empty($user_id) )
					{
						throw new Exception($this->lang->line('err_unauthorized_add'));
					}
						
					if($this->permission_add === FALSE)
					{
						throw new Exception($this->lang->line('err_unauthorized_add'));
					}

					$add_b 	= TRUE;
				break;
							
				case $this->security_action_edit:
					if(empty($user_id) )
						throw new Exception($this->lang->line('err_unauthorized_edit'));
						
					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_edit'));
					
						$det 			= $user_info;
						$data['user'] 	= $user_info;
						
						$user_roles 	= $this->roles->get_user_roles($user_id);
						$main_role_arr 	= $this->roles->get_user_roles($user_id, 1);
						
						$other_roles 	= array_column($user_roles, 'role_code');
						$main_role 		= array_column($main_role_arr, 'role_code');

						$user_g 		= $this->user_groups->get_user_groups_details( $user_id );

						if( !EMPTY( $user_g ) )
						{
							$user_groups = array_column( $user_g, 'group_id');
						}
						
						for ($i=0; $i<count($user_roles); $i++)
						{
							$user_roles_arr[] = $user_roles[$i]["role_code"];
						}
						
						/*if(!EMPTY($user_info["org_code"]))
							$resources['single'] 	= array('org' => $user_info["org_code"]);*/
							
						if(!EMPTY($user_roles_arr))
							$resources['multiple'] 	= array('role' => $user_roles_arr);
				break;
				
				default:
					throw new Exception($this->lang->line('err_unauthorized_access'));
				break;
			}
		
			$dpa_enable 			= check_dpa_enable();
			$has_agreement_text 	= get_dpa_privacy_type();
			$strict_mode 			= get_setting(DPA_SETTING, 'dpa_strict_mode');
			$confirm_dpa_message 	= $this->lang->line('confirm_dpa_message');
			$confirm_dpa_message_body = $this->lang->line('confirm_dpa_message_body');
			
			$admnin_set_password 	= FALSE;
			$admin_set_username 	= FALSE;
			$user_groups 			= array();
			
			$password_creation 	= get_setting(PASSWORD_INITIAL_SET, "password_creator");
			$username_creation 	= get_setting('USERNAME_CREATE', "username_creator");

			if( $password_creation == SET_ADMINISTRATOR )
			{
				$admnin_set_password 		= TRUE;
			}

			if( $username_creation == SET_ADMINISTRATOR )
			{
				$admin_set_username 		= TRUE;
			}
			
			$orgs	= $this->orgs->get_orgs_all_lazy();
			$roles 	= $this->roles->get_roles();
			$all_orgs = $this->orgs->get_orgs_all_lazy();

			$main_orgs_det 					= $this->auth_model->get_user_organizations($user_id, LOGGED_IN_FLAG_YES);

			$other_orgs_det 				= $this->auth_model->get_user_organizations($user_id, LOGGED_IN_FLAG_NO);

			$all_orgs_arr 					= array_column($all_orgs, 'org_code');

			if( !EMPTY( $main_orgs_det ) )
			{
				$main_orgs 					= array_column($main_orgs_det, 'org_code');
				
				foreach( $main_orgs as $mo )
				{

					if( !EMPTY($all_orgs_arr) AND !in_array($mo, $all_orgs_arr, TRUE) )
					{
						$spec_o = $this->orgs->get_orgs_details_with_parents($mo);

						if( !EMPTY( $spec_o ) )
						{
							$all_orgs = array_merge($all_orgs, array($spec_o));
						}
					}
				}
			}
			
			if( !EMPTY( $other_orgs_det ) )
			{
				$other_orgs 				= array_column($other_orgs_det, 'org_code');

				foreach( $other_orgs as $oo )
				{
					if( !EMPTY($all_orgs_arr) AND !in_array($oo, $all_orgs_arr, TRUE) )
					{
						$o_o_spec = $this->orgs->get_orgs_details_with_parents($oo);

						if( !EMPTY( $o_o_spec ) )
						{
							$all_orgs = array_merge($all_orgs, array($o_o_spec));
						}
					}
				}
			}

			$all_orgs 	= $this->process_parents($all_orgs);

			$data['add_b'] 					= $add_b;
			$data['disabled_str'] 			= $disabled_str;
			$data['main_orgs']				= $main_orgs;
			$data['other_orgs']				= $other_orgs;
			$data['all_orgs'] 				= $all_orgs;
			$data['admnin_set_password']	= $admnin_set_password;
			$data['admin_set_username'] 	= $admin_set_username;
			$data['orgs'] 					= $orgs;
			$data['roles'] 					= $roles;
			$data['role_json'] 				= json_encode( $roles );
			$data['dpa_enable'] 			= $dpa_enable;
			$data['has_agreement_text'] 	= $has_agreement_text;
			$data['confirm_dpa_message'] 	= $confirm_dpa_message;
			$data['confirm_dpa_message_body'] = $confirm_dpa_message_body;

			$users_multiple_org 	= get_sys_param_val('USERS_INPUT', 'USERS_MULTI_ORG');
			$check_users_multiple_org = ( !EMPTY( $users_multiple_org ) ) ? $users_multiple_org['sys_param_value'] : TRUE;
			
			$data['users_multiple_org']	= $check_users_multiple_org;
						
			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_SELECTIZE, CSS_DATETIMEPICKER, CSS_SUMO_SELECT);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_SELECTIZE, JS_SUMO_SELECT, JS_LAZY_SELECTIZE, JS_DATETIMEPICKER, $this->module_js);

			/*$resources['sumo_select'] 	= array(
				'#other_sel_orgs' 		=> array(
					'selectAll' 	=> true,
					'search' 		=> true,
					// 'modal_id'		=> 'modal_user_mgmt',
					// 'csvDispCount'	=> 0
				)
			);*/
			
			$arr = json_encode(array("id" =>"avatar", "path" =>  str_replace('\\', '\\\\', PATH_USER_UPLOADS) ) );
			$resources['upload'] 	= array(
				'avatar' => array(
					'path' 				=> PATH_USER_UPLOADS,
					'allowed_types' 	=> 'jpeg,jpg,png,gif',
					'show_progress' 	=> 1,
					'show_preview' 		=> 1,
					'successCallback'	=> "Users.successCallback('".$arr."', data);",
					'deleteCallback'	=> "Users.deleteCallback('".$arr."');",
					'disable'				=> $disabled_upl
				)
			);

			if( !EMPTY($dpa_enable) AND $has_agreement_text == DATA_PRIVACY_TYPE_STRICT )
			{
				if( $strict_mode == DATA_PRIVACY_STRICT_CONSENT_FORM )
				{
					$resources['upload']['consent_form']	= array(
						'path' 				=> PATH_USER_UPLOADS,
						'allowed_types'		=> 'pdf,doc,docx',
						'show_progress' 	=> 1,
						'show_preview' 		=> 1,
						'max_file' 				=> 1,
						'multiple' 				=> 1,
						'successCallback'		=> "Users.consentFormSuccessCallback(files,data,xhr,pd);",
						'max_file_size'			=> '13107200',
						'auto_submit'			=> false,
						
						'multiple_obj'			=> true,
						'show_download'			=> true,
						'delete_path'			=> CORE_USER_MANAGEMENT.'/Users',
						'delete_path_method'	=> 'save_consent_file',
						'delete_form'			=> '#form_modal_user_mgmt',
						// 'dont_delete_in_server'	=> true,
						'disable'				=> $disabled_upl
					 );
				}
			}

			/*'consent_form'			=> array(
					'path' 				=> PATH_USER_UPLOADS,
					'allowed_types'		=> 'pdf,doc,docx',
					'show_progress' 	=> 1,
					'show_preview' 		=> 1,
				 )*/



			$resources['loaded_doc_init']	= array(
				'Users.initForm();'
			);
			
			$resources['loaded_init'] = array(
				'Users.save();'
			);
			
			$org_drop = array();
			
			if( !EMPTY( $orgs ) )
			{
				$org_drop = $this->process_org_dropdown( $orgs );
			}

			$all_groups 	= $this->groups->get_all_groups();

			$login_sys_param 	= get_sys_param_val('LOGIN', 'LOGIN_WITH');

			$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;
			

			$data['real_orgs']		= $org_drop;
			$data['other_roles'] 	= $other_roles;
			$data['main_role'] 		= $main_role;
			$data['all_groups']		= $all_groups;
			$data['user_groups']	= $user_groups;
			$data['strict_mode'] 	= $strict_mode;
			$data['ch_login_sys_param'] = $ch_login_sys_param;

			$data['users_gender_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_GENDER');
			$data['users_mname_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_MIDDLE_NAME');
			$data['users_ename_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_EXT_NAME');
			$data['users_job_title_inp'] 			= get_sys_param_val('USERS_INPUT', 'USERS_JOB_TITLE');
			$data['users_org_inp'] 					= get_sys_param_val('USERS_INPUT', 'USERS_ORG');
			
			
			$this->load->view("modals/users", $data);
			$this->load_resources->get_resource($resources);
	
			
			
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);
			
			$this->error_modal($msg);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
			
			$this->error_modal($msg);			
		}
		
	}

	protected function process_parents(array $orgs = array())
	{

		try
		{
			if( !EMPTY( $orgs ) )
			{
				foreach( $orgs as $key => $o )
				{
					$name 		= '';

					if( !EMPTY( $o['org_parents'] ) )
					{
						$org_par_arr 	= explode(',', $o['org_parents']);

						foreach( $org_par_arr as $o_arr )
						{
							$name 	= $this->_process_parent_name($o_arr).'<br/>';
						}
					}

					$name 	= rtrim($name, ', </br>');

					$orgs[$key]['org_parent_names'] = $name;
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $orgs;
	}

	protected function _process_parent_name($org_code, $name = '')
	{

		try
		{
			$details 	= $this->orgs->get_orgs_details_with_parents($org_code);

			if( !EMPTY( $details['org_parents'] ) )
			{
				$org_par_arr 	= explode(',', $details['org_parents']);

				$name 		= $details['name'].', ';
				
				foreach( $org_par_arr as $o )
				{
					$name 	.= $this->_process_parent_name($o, $name);
				}
			}
			else
			{

				$name 	= $details['name'].', ';
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

		return $name;
	}
	
	public function modal2($id = NULL, $salt = NULL, $token = NULL)
	{
		$admnin_set_password 	= FALSE;
		$all_groups 			= array();

		$user_groups 			= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);
			
			$data = $resources = array();


			$password_creation 				= get_setting(PASSWORD_INITIAL_SET, "password_creator");

			if( $password_creation == SET_ADMINISTRATOR )
			{
				$admnin_set_password 		= TRUE;
			}
			
			$orgs 			= $this->orgs->get_orgs();
			$roles 			= $this->roles->get_roles();

			$role_json 		= json_encode( $roles );

			$data['admnin_set_password']	= $admnin_set_password;
			$data['orgs'] 	= $orgs;
			$data['roles'] 	= $roles;
			$data['role_json'] = $role_json;
			
			$other_roles 	= array();
			$main_role 		= array();
			
			if(!IS_NULL($id))
			{
				$id = base64_url_decode($id);
				
				// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
				check_salt($id, $salt, $token);
				
				$user 			= $this->users->get_user_details($id);
				$data['user'] 	= $user;
				
				$user_roles 	= $this->roles->get_user_roles($id);
				$main_role_arr 	= $this->roles->get_user_roles($id, 1);
				
				$other_roles 	= array_column($user_roles, 'role_code');
				$main_role 		= array_column($main_role_arr, 'role_code');

				$user_g 		= $this->user_groups->get_user_groups_details( $id );

				if( !EMPTY( $user_g ) )
				{
					$user_groups = array_column( $user_g, 'group_id');
				}
				
				for ($i=0; $i<count($user_roles); $i++)
				{
					$user_roles_arr[] = $user_roles[$i]["role_code"];
				}
				
				/*if(!EMPTY($user["org_code"]))
					$resources['single'] 	= array('org' => $user["org_code"]);*/
					
				if(!EMPTY($user_roles_arr))
					$resources['multiple'] 	= array('role' => $user_roles_arr);
							
			}
			
			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_SELECTIZE, CSS_UPLOAD);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_SELECTIZE, JS_UPLOAD, $this->module_js);
			
			$arr = json_encode(array("id" =>"avatar", "path" =>  str_replace('\\', '\\\\', PATH_USER_UPLOADS) ) );
			$resources['upload'] 	= array(
				'avatar' => array(
					'path' 				=> PATH_USER_UPLOADS,
					'allowed_types' 	=> 'jpeg,jpg,png,gif',
					'show_progress' 	=> 1,
					'show_preview' 		=> 1,
					'successCallback'	=> "Users.successCallback('".$arr."', data);",
					'deleteCallback'	=> "Users.deleteCallback('".$arr."');"
				)
			);

			$resources['loaded_doc_init']	= array(
				'Users.initForm();'
			);
			
			$resources['loaded_init'] = array(
				'Users.save();'
			);
			
			$org_drop = array();
			
			if( !EMPTY( $orgs ) )
			{
				$org_drop = $this->process_org_dropdown( $orgs );
			}

			$all_groups 	= $this->groups->get_all_groups();
			
			$data['real_orgs']		= $org_drop;
			$data['other_roles'] 	= $other_roles;
			$data['main_role'] 		= $main_role;
			$data['all_groups']		= $all_groups;
			$data['user_groups']	= $user_groups;
			
			$this->load->view("modals/users", $data);
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

	private function _process_org_dropdown( array $orgs = array(), $org_parent = NULL, array $details = array() )
	{
		$option 	= '';

		static $concat 			= '';
		static $deep 			= 0;

		if( !EMPTY( $orgs ) )
		{
			foreach( $orgs as $org )
			{
				if( $org['org_parent'] == $org_parent )
				{

					if( $deep > 0 )
					{
						$concat 	.= '&emsp;';
					}
					else
					{
						$concat 	= '';
					}

					$org_name		= $concat.$org['name'];

					$selected 		= ( ISSET( $details['org_code'] ) AND !EMPTY( $details['org_code'] ) AND $details['org_code'] == $org['org_code'] ) ? 'selected' : '';

					$option .= '<option value="'.$org["org_code"].'" '.$selected.'>'.$org_name.'</option>';

					++$deep;

					$option .= $this->_process_org_dropdown( $orgs, $org['org_code'] );

					$concat 	= '';

					--$deep;


				}

			}
		}

		return $option;
	}

	public function process_org_dropdown( array $details = array() )
	{

		$option = '';
			
		try
		{
			$orgs 	= $this->orgs->get_orgs_all_lazy();

			$option = $this->_process_org_dropdown( $orgs, NULL, $details );
		}
		catch( PDOException $e )
		{
			$this->rlog_error( $e );
		}
		catch( Exception $e )
		{
			$this->rlog_error( $e );
		}

		return $option;
	}

	public function process_groups( array $params, $user_id )
	{
		$arr 	= array();

		try
		{
			if( ISSET( $params['groups'] ) AND !EMPTY( $params['groups'] )
				AND ISSET( $params['groups'][0] ) AND !EMPTY( $params['groups'][0] )
			)
			{	
				foreach( $params['groups'] as $key => $groups )
				{
					$arr[$key]['user_id']		= $user_id;
					$arr[$key]['group_id']		= $groups;
					$arr[$key]['admin_flag']	= 1;
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

		return $arr;
	}
		
	public function process()
	{
		$status 	= ERROR;

		$new_id 	= NULL;
		$new_salt 	= NULL;
		$new_token 	= NULL;

		$statistics = array();

		try
		{
			// $this->redirect_off_system($this->module);

			$orig_params	= get_params();

			$params 		= $this->set_filter( $orig_params )
								->filter_number('groups', TRUE)
								->filter_date('temp_expiration_date')
								->filter();
			
			$action = (EMPTY($params['user_id']))? AUDIT_INSERT : AUDIT_UPDATE;

			$password_creation 		= get_setting( PASSWORD_INITIAL_SET, 'password_creator' );
			$username_creation 		= get_setting( 'USERNAME_CREATE', 'username_creator' );

			$params['password_creation']			= $password_creation;

			$system_generated_password 				= generate_password();

			$params['system_generated_password'] 	= $system_generated_password;

			$id		= filter_var($params['user_id'], FILTER_SANITIZE_NUMBER_INT);

			// SERVER VALIDATION
			$this->_validate($params, $action, $id);
	
			// GET SECURITY VARIABLES
			
			$salt 	= $params['salt'];
			$token 	= $params['token'];
			
			$name 	= $params['fname']. ' ' . $params['lname'];
	
			// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
			check_salt($id, $salt, $token);
			
			$user_id 		= ($action == AUDIT_INSERT) ? 0 : $id;
			$email_exist 	= $this->_validate_email($params['email'], $user_id);

			$dpa_enable 			= check_dpa_enable();
			$has_agreement_text 	= get_dpa_privacy_type();
			$strict_mode 			= get_setting(DPA_SETTING, 'dpa_strict_mode');

			SYSAD_Model::beginTransaction();
			
			$audit_table[] 	= SYSAD_Model::CORE_TABLE_USERS;
			$audit_schema[]	= DB_CORE;
			$audit_action[]	= $action;
			
			if(!$email_exist AND EMPTY($id))
			{
				if( $username_creation == SET_SYSTEM_GENERATED 
				OR $username_creation == SET_ACCOUNT_OWNER
				)
				{

					$usname 	= generate_username($params['fname'], $params['lname']);
					
					$params['username']	= $usname;
				}

				$prev_detail[] 	= array();

				if( !EMPTY( $dpa_enable ) )
				{
					if( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT )
					{
						if( $strict_mode == DATA_PRIVACY_STRICT_EMAIL_NOTIF )
						{
							$params['status'] = DPA_PENDING;
						}
						else
						{
							if( !ISSET( $params['status'] ) )
							{
								$params['status']	= ACTIVE;
							}			
						}
					}
					else
					{
						if( !ISSET( $params['status'] ) )
						{
							$params['status']	= ACTIVE;
						}
					}
				}
				else
				{
					if( !ISSET( $params['status'] ) )
					{
						$params['status']	= ACTIVE;
					}
				}
				
				$id 			= $this->users->insert_user($params);
				$msg 			= $this->lang->line('data_saved');

				$new_id 		= $id;
				
				// GET THE DETAIL AFTER INSERTING THE RECORD
				$curr_detail[] 	= $this->users->get_specific_user($id);	
				
				// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
				$activity 		= "created a new user account ( %s ).";
				$activity 		= sprintf($activity, $name);				
			
			} 
			else if( $email_exist AND !EMPTY($id) )
			{
								
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
				$prev_detail[] 	= $this->users->get_specific_user($id);
				
				$this->users->update_user($params);


				$new_id 		= $id;
					
				$msg 			= $this->lang->line('data_updated');
				
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_detail[] 	= $this->users->get_specific_user($id);
				
				// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
				$activity 		= "updated user account details ( %s ). ";
				$activity 		= sprintf($activity, $name);				
				
			} 
			else 
			{				
				throw new Exception($this->lang->line('email_exist'));
			}

			$new_id 	= $this->encrypt($id);
			$new_salt 	= gen_salt();
			$new_token  = in_salt($id, $new_salt);

			if( !EMPTY( $id ) )
			{
				$user_groups_val 		= $this->process_groups( $params, $id );

				$main_where 			= array(
					'user_id'		=> $id
				);

				if( $action == AUDIT_UPDATE )
				{
					$prev_group 		= $this->user_groups->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_GROUPS,
											$main_where
										 );

					if( !EMPTY( $prev_group ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_GROUPS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $prev_group;

						$this->user_groups->delete_user_group( $main_where );

						$curr_detail[] 		= array();
					}
				}

				if( !EMPTY( $user_groups_val ) )
				{

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_GROUPS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->user_groups->insert_user_group( $user_groups_val );

					$curr_detail[] 		= $this->user_groups->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_GROUPS,
											$main_where
										 );

				}
			}
			
			// LOG AUDIT TRAIL
			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module_code, 
				$prev_detail, 
				$curr_detail, 
				$audit_action, 
				$audit_table,
				$audit_schema
			);

			if(!EMPTY($id) AND 
				( ISSET($params["send_email"]) AND !EMPTY($params["send_email"]) )
				OR 
				( 
					$params['password_creation'] == SET_SYSTEM_GENERATED 
					OR $params['password_creation']	== SET_ACCOUNT_OWNER
				)
			)
			{
				if( $action == AUDIT_INSERT )
				{
					if( EMPTY( $dpa_enable ) )
					{
						$status 	= $this->_send_welcome_email($id, $params);
					}
				}
			}

			if( !EMPTY( $dpa_enable ) )
			{
				if( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT )
				{
					if( $strict_mode == DATA_PRIVACY_STRICT_EMAIL_NOTIF )
					{
						if( $action == AUDIT_INSERT )
						{
							$this->_send_dpa_email($id, $params);
						}
					}
					else
					{
						if( $action == AUDIT_INSERT )
						{
							$status 	= $this->_send_welcome_email($id, $params);		
						}
					}

				}
				else
				{
					if( $action == AUDIT_INSERT )
					{
						$status 	= $this->_send_welcome_email($id, $params);
					}
				}
			}

			if( $id == $this->session->user_id )
			{
				$arr 		= array(
					"photo"	=> $params['image']
				);

				$this->session->set_userdata($arr);
			}
									
			SYSAD_Model::commit();
			$status 	= SUCCESS;

			$statistics = $this->users->get_user_status_count();
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			$msg 	= $this->rlog_error($e, TRUE);
		}
	
		$info = array(
			"status" 	=> $status,
			"msg" 		=> $msg,
			"table_id" 	=> $this->table_id,
			"path" 		=> $this->path,
			"datatable_options" => $this->dt_options,
			'new_id'	=> $new_id,
			'new_salt'	=> $new_salt,
			'new_token'	=> $new_token,
			'statistics' => $statistics
		);
	
		echo json_encode($info);
	}

	public function _send_dpa_email($id, array $params = array(), $for_insert = FALSE, $raw_arg_password = NULL)
	{
		try
		{
			$user_detail 	= $this->users->get_user_details($id);
			$created_by 	= $this->users->get_user_details($user_detail['created_by']);

			$sys_logo 				 	= get_setting(GENERAL, "system_logo");
			$system_logo_src 			= base_url() . PATH_IMAGES . "logo_white.png";

			if( !EMPTY( $sys_logo ) )
			{
				$root_path 				= $this->get_root_path();
				$sys_logo_path 			= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
				$sys_logo_path 			= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

				if( file_exists( $sys_logo_path ) )
				{
					$system_logo_src 	= base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
					$system_logo_src 	= @getimagesize($system_logo_src) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
				}
			}
			
			
			$status 		= ERROR;
			$email_data 	= array();
			$template_data 	= array();
	
			$salt 			= gen_salt(TRUE);
			$system_title 	= get_setting(GENERAL, "system_title");
				
			// required parameters for the email template library
			$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"] 	= array($user_detail['email']);
			$email_data["subject"] 		= 'Invitation to be a user in '.$system_title;

			$now 				= new DateTime(); 
			$now->add(new DateInterval("P1D"));
			$expired_date 		= $now->format('Y-m-d H:i:s');

			$template_data["email"] 		= $user_detail['email'];
			$template_data["password"] 		= base64_url_encode($user_detail['password']);
			$template_data['username']		= $user_detail['username'];
			$template_data["reason"] 		= $user_detail['reason'];
			$template_data["name"] 			= $user_detail['fname'] . ' ' . $user_detail['lname'];
			$template_data["created_by"] 	= $created_by['fname'] . ' ' . $created_by['lname'];
			$template_data['creator']		= $template_data['created_by'];
			$template_data["system_name"] 	= $system_title;
			$template_data['logo']			= $system_logo_src;
			$template_data["email_subject"] = 'This is an invitation to be a user in '.$system_title;
			$template_data['user_id'] 		= $user_detail['user_id'];
			$template_data['user_id_enc'] 	= $this->encrypt($user_detail['user_id']);
			$template_data['salt'] 			= gen_salt();
			$template_data['token'] 		= in_salt( $user_detail['user_id'], $template_data['salt'] );
			$template_data['approved_reject_link'] = base_url().'Auth/approve_reject_invitation/'.$template_data['user_id_enc'].'/'.$template_data['salt'].'/'.$template_data['token'].'/';

			$template_data['logo']			= '
				<div style="background:#333333; padding:20px 30px; text-align:center;"><img src="'.$template_data['logo'].'" height="40" alt="logo" /></div>
';

			$template_data['approve_btn']	= '
				<a class="btn waves-effect green lighten-2" style="border: none;
				  border-radius: 2px;
				  display: inline-block;
				  height: 36px;
				  line-height: 36px;
				  padding: 0 2rem;
				  text-transform: uppercase;
				  vertical-align: middle;
				  -webkit-tap-highlight-color: transparent;
				  background: #1E90FF;
				  color : #fff;
				  text-decoration: none;" 
				  href="'.$template_data['approved_reject_link'].'/'.APPROVED.'/">
				  Approve
				</a>
';

			$template_data['reject_btn']	= '
				<a class="btn waves-effect red lighten-2" style="border: none;
				  border-radius: 2px;
				  display: inline-block;
				  height: 36px;
				  line-height: 36px;
				  padding: 0 2rem;
				  text-transform: uppercase;
				  vertical-align: middle;
				  -webkit-tap-highlight-color: transparent;
				  color : #fff;
				  background: #8B0000;
				  text-decoration: none;" 
				  href="'.$template_data['approved_reject_link'].'/'.DISAPPROVED.'/">
				Reject
			</a>
';

			$raw_password 					= '';

			if( ISSET( $params['password'] ) )
			{
				$raw_password 					= preg_replace('/\s+/', '', $params['password']);
			}

			if( $params['password_creation'] == SET_SYSTEM_GENERATED )
			{
				$raw_password 				= $params['system_generated_password'];
			}

			if( !EMPTY( $raw_arg_password ) )
			{
				$raw_password 	= $raw_arg_password;
			}

			$template_data['raw_password'] = $raw_password;

			$message = '';

			if( $for_insert )
			{
				$message 	= $this->email_template->get_email_template(STATEMENT_CODE_EMAIL_DPA, $template_data);
			}
			else
			{
				$this->email_template->send_email_template_html($email_data, STATEMENT_CODE_EMAIL_DPA, $template_data);
			}

			$errors 						= $this->email_template->get_email_errors();

			if( !EMPTY( $errors ) )
			{
				$str 						= var_export( $errors, TRUE );

				RLog::error( "Email Error" ."\n" . $str . "\n" );
			}
			else
			{
				$this->users->update_helper( SYSAD_Model::CORE_TABLE_USERS, array('expire_dpa_date' => $expired_date), array('user_id' => $user_detail['user_id']) );
			}

			if( $for_insert )
			{
				return array(
					'message'	=> $message,
					'email_data' => $email_data,
					'template_data' => $template_data
				);
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
	}

	public function save_consent_file()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		try
		{
			$params 			= $this->set_filter($orig_params)
									->filter_number('user_id', TRUE)
									->filter();

			$check_user = base64_url_decode($orig_params['user_id']);

			if( EMPTY( $check_user ) )
			{
				$user_id = $orig_params['user_id'];
			}
			else
			{
				$user_id = $params['user_id'];
			}

			check_salt($user_id, $params['salt'], $params['token']);
			
			$permission 		= $this->permission_edit;
			$per_msg 			= $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}

			SYSAD_Model::beginTransaction();

			$upd_val 	= array();
			$main_where = array();

			$upd_val['modified_by']	= $this->session->user_id;
			$upd_val['modified_date']	= date('Y-m-d H:i:s');

			if( ISSET( $params['op'] ) AND $params['op'] == 'delete' )
			{
				$upd_val['consent_form_sys_filename'] = NULL;
				$upd_val['consent_form_orig_filename'] = NULL;
			}
			else
			{

				$upd_val['consent_form_sys_filename'] = $params['consent_form'];
				$upd_val['consent_form_orig_filename'] = $params['consent_form_orig_filename'];
			}

			$main_where['user_id']	= $user_id;

			$audit_schema[] 		= DB_CORE;
			$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USERS;
			$audit_action[] 	= AUDIT_UPDATE;
			$prev_detail[]  	= array($this->users->get_user_details($user_id));
											 
			$this->users->update_helper(SYSAD_Model::CORE_TABLE_USERS, $upd_val, $main_where);

			$curr_us 			= $this->users->get_user_details($user_id);
			$curr_detail[]  	= array($curr_us);

			$audit_name 				= $curr_us['fname'].' '.$curr_us['lname'].' Consent File.';

			$audit_activity 			= sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module_code, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					=$this->lang->line( 'data_saved' );
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
			'flag'		=> $flag,
			"status" 	=> $status,
			"msg" 		=> $msg,
			"table_id" 	=> $this->table_id,
			"path" 		=> $this->path,
			"datatable_options" => $this->dt_options,
		);

		echo json_encode( $response );

	}

	private function _filter( array $orig_params, array $par_keys )
	{
		$par 			= $this->set_filter( $orig_params )
							->filter_string( 'user_id', TRUE );
		
		
		$params 		= $par->filter();
		
		return $params;
	}

	public function delete_multi_dt()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();

		$orig_params 			= get_params();

		// $delete_per 			= $this->delete_per;

		$msg 					= '';
		$flag 					= 0;
		$status 				= ERROR;

		$main_where 			= array();

		$statistics 	= array();
		$statistics_json = '';

		try
		{
			$orig_params 			= get_params();
			unset( $orig_params['CSRFToken'] );
			$par_keys 				= array_keys( $orig_params );

			if( ISSET( $orig_params['tables'] ) )
			{
				$tables 				= $orig_params['tables'];
				unset( $orig_params['tables'] );
			}
			else
			{
				$tables 				= SYSAD_Model::CORE_TABLE_USERS;
			}

			if( ISSET( $orig_params['extra_data'] ) )
			{
				$extra_data 			= $orig_params['extra_data'];
				unset( $orig_params['extra_data'] );
			}

			$params 				= $this->_filter( $orig_params, $par_keys );

			$stat = STATUS_DELETED;

			if( ISSET( $params['pass_status'] ) )
			{
				$stat = $params['pass_status'];
				unset($params['pass_status']);
			}

			$real_par 				= $params;

			foreach( $params as $columns => $val )
			{
				$main_where[$columns] 	= array( 'IN', $val );
			}
			
			SYSAD_Model::beginTransaction();

			$val					= array();
			$val['status'] 		= $stat;

			
			if( $stat == STATUS_BLOCKED )
			{
				$val['blocked_by']		= $this->session->user_id;
				$val['blocked_date']	= date('Y-m-d H:i:s');
				
				$val['reason'] 			= 'Manually Blocked';
			}
			else if( $stat == STATUS_INACTIVE )
			{
				$val['inactivated_by']		= $this->session->user_id;
				$val['inactivated_date']	= date('Y-m-d H:i:s');	
				$val['reason'] 				= 'Manually Deactivated';
			}
			else
			{
				$upd_val['inactivated_by']		= NULL;
				$upd_val['inactivated_date']	= NULL;	
				$upd_val['blocked_by']		= NULL;
				$upd_val['blocked_date']	= NULL;
				$val['reason'] 				= NULL;

			}

			// $params['user_id'] 		= $user_id;
			
			$audit_action[]	= AUDIT_UPDATE;
			$audit_table[] 	= SYSAD_Model::CORE_TABLE_USERS;
			$audit_schema[]	= DB_CORE;				
			$prev_detail[]	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USERS,
									$main_where
								 );			

			$this->users->update_helper(SYSAD_Model::CORE_TABLE_USERS, $val, $main_where);

			$curr_detail[] 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USERS,
									$main_where
								 );	
			
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL

			if( $val['status'] == STATUS_DELETED )
			{
				$msg		= $this->lang->line('data_deleted');
				$activity 	= "deleted a user account ( %s ).";
			}
			else if( $val['status'] == STATUS_ACTIVE )
			{
				$msg		= 'Records was successfully activated';
				$activity 	= "activated a user account ( %s ).";
			}
			else if( $val['status'] == STATUS_INACTIVE )
			{
				$msg		= 'Records was successfully deactivated';
				$activity 	= "deactivated a user account ( %s ).";
			}
			else if( $val['status'] == STATUS_BLOCKED )
			{
				$msg		= 'Records was successfully blocked';
				$activity 	= "blocked user accounts.";
			}
			
			// $activity 	= sprintf($activity, $user_info['fname'] . ' ' . $user_info['lname']);
			
			// LOG AUDIT TRAIL
			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module_code, 
				$prev_detail, 
				$curr_detail, 
				$audit_action, 
				$audit_table,
				$audit_schema
			);

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;

			$statistics 	= $this->users->get_user_status_count();

			$statistics_json = json_encode($statistics);
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
			'extra_function'		=> 'Users.extra_function('.$statistics_json.');',
			'datatable_id' 			=> $this->table_id,
			'datatable_options'		=> $this->dt_options
		);

		echo json_encode( $response );	
	}
	
	public function delete_user()
	{
		$statistics 	= array();
		$statistics_json = '';

		try
		{
			// $this->redirect_off_system($this->module);
			
			$status 	= ERROR;
			$params		= get_params();
	
			// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
			$url 				= explode('/', $params['param_1']);
			$encrypt_id			= $url[0];
			$salt				= $url[1];
			$token				= $url[2];
			$security_action	= $url[3];

			$extra_status 		= NULL;

			if( ISSET( $url[4] ) )
			{
				$extra_status 	= $url[4];
			}
			
			check_salt($encrypt_id, $salt, $token, $security_action);
			
			$user_info	= $this->users->get_specific_user($this->decrypt($encrypt_id), FALSE, FALSE);
			$user_id	= isset($user_info['user_id']) ? $user_info['user_id'] : 0;
			
			switch($security_action)
			{
				case $this->security_action_del:
				
					if( empty($user_id) )
						throw new Exception($this->lang->line('err_unauthorized_delete'));	
						

					if( ! $this->permission_delete)					
						throw new Exception( $this->lang->line("cant_delete_user") );		
				break;

				default:
					throw new Exception($this->lang->line('err_unauthorized_delete'));
				break;
			}
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$params					= array();
			$params['status_id'] 	= ( !EMPTY( $extra_status ) ) ? $extra_status : DELETED;

			if( ! empty( $user_info['built_in_flag'] ) AND $params['status_id'] == DELETED )
			{
				throw new Exception( $this->lang->line("cant_delete_user") );		
			}

			$params['user_id'] 		= $user_id;
			
			$this->users->update_status($params);

			if( $params['status_id'] == DELETED )
			{

				$this->users->update_helper(SYSAD_Model::CORE_TABLE_USERS, 
					array(
						'email' 	=> array( $user_info['email'].'deleted_'.$user_id, 'ENCRYPT' ),
						'username'  => array( $user_info['username'].'deleted_'.$user_id, 'ENCRYPT' ),
						'mobile_no'  => array( $user_info['mobile_no'].'deleted_'.$user_id, 'ENCRYPT' ),

					), 
					array('user_id' => $user_id)
				);
			}
			
			$audit_action[]	= AUDIT_DELETE;
			$audit_table[] 	= SYSAD_Model::CORE_TABLE_USERS;
			$audit_schema[]	= DB_CORE;				
			$prev_detail[]	= array($user_info);			
			$curr_detail[] 	= $this->users->get_specific_user($user_id);
			
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
			$msg		= $this->lang->line('data_deleted');
			$activity 	= "deleted a user account ( %s ).";
			$activity 	= sprintf($activity, $user_info['fname'] . ' ' . $user_info['lname']);
			
			// LOG AUDIT TRAIL
			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module_code, 
				$prev_detail, 
				$curr_detail, 
				$audit_action, 
				$audit_table,
				$audit_schema
			);
			
			SYSAD_Model::commit();
			
			$status = SUCCESS;
		
			$statistics 	= $this->users->get_user_status_count();

			$statistics_json = json_encode($statistics);
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			$msg = $this->rlog_error($e, TRUE);
		}
	
		echo json_encode(
			array(
				"status" 	=> $status,
				"msg" 		=> $msg,
				"reload" 	=> 'datatable', 
				"datatable_options" => $this->dt_options,
				'extra_reload' 	=> 'function',
				'extra_function' => 'Users.extra_function('.$statistics_json.');'
			)
		);
	}
	
	private function _validate($params, $action = NULL, $id = NULL)
	{
		$required 		= array();
		$constraints	= array();

		try
		{

			$required['lname']	= 'Last name';
			$required['fname']	= 'First name';
			$required['email']	= 'Email';
			if( ISSET( $params['org'] ) )
			{
				$required['org']	= 'Department/Agency';
			}

			$dpa_enable 			= check_dpa_enable();
			$has_agreement_text 	= get_dpa_privacy_type();
			$strict_mode 			= get_setting(DPA_SETTING, 'dpa_strict_mode');

			if( !EMPTY( $dpa_enable ) )
			{
				if( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT )
				{
					if( $strict_mode == DATA_PRIVACY_STRICT_CONSENT_FORM )
					{
						$required['consent_form'] = 'Consent Form';
					}
				}
			}
			
			if($params["contact_type"] == 0)
			{
				$required['main_role']	= 'Main Role';
			}
		
			if($action == AUDIT_INSERT)
			{
				if($params["contact_type"] == 0)
				{
					if( $params['password_creation'] == SET_ADMINISTRATOR )
					{
						$required['password'] 	= 'Password';
							
						if(EMPTY($params['confirm_password']))
							throw new Exception('Please confirm your password.');
							
						if($params['password'] != $params['confirm_password'])
							throw new Exception('Password did not match.');
					}
				
				}
			}
			
			if($params["contact_type"] == 0)
			{
				if( !EMPTY( $params['role'] ) AND !EMPTY( $params['role'][0] ) )
				{
					if( in_array( $params['main_role'][0], $params['role'] ) )
					{
						throw new Exception('There may be a duplicate role in both main role and other roles.');
					}
				}
			}

			if( ISSET( $params['groups'] ) AND !EMPTY( $params['groups'] )
				AND ISSET( $params['groups'][0] ) AND !EMPTY( $params['groups'][0] )
			)
			{
				$constraints['groups']		= array(
					'name'			=> 'Groups',
					'data_type'		=> 'db_value',
					'field'			=> ' COUNT( group_id ) as check_group ',
					'check_field'	=> 'check_group',
					'where' 		=> 'group_id',
					'table'			=> DB_CORE.'.'.'`'.SYSAD_Model::CORE_TABLE_GROUPS.'`'
				);
			}

			$this->check_required_fields( $params, $required );

			$this->validate_inputs( $params, $constraints );

			$v = $this->core_v;

			if( ISSET( $params['temp_account_flag'] ) )
			{
				$v 
					->required()
					->check('temp_expiration_date|Expiration Date', $params);
			}

			$login_with_arr_sel 		= get_setting(LOGIN, 'login_api');
			$login_with_arr_sel 		= trim($login_with_arr_sel);

			$login_with_arr_a 		= array();

			if( !EMPTY( $login_with_arr_sel ) )
			{
				$login_with_arr_a 	= explode(',', $login_with_arr_sel);
			}

			$facebook_email = 'CAST('.aes_crypt('facebook_email', FALSE, FALSE).' AS char(100))';
			$google_email = 'CAST('.aes_crypt('google_email', FALSE, FALSE).' AS char(100))';

			if( in_array(VIA_FACEBOOK, $login_with_arr_a) )
			{
			
				$v 
					->email()->sometimes();
					if( ISSET( $params['facebook_email'] ) AND !EMPTY( $params['facebook_email'] ) )
					{
						$v->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$facebook_email.'|exclude_id=user_id|exclude_value='.$id, '@custom_error_The facebook email address entered has already been used. Please use a different email.')->sometimes();
					}
					$v->check('facebook_email', $params);
			}

			if( in_array(VIA_GOOGLE, $login_with_arr_a) )
			{

				$v 
					->email()->sometimes();
					if( ISSET( $params['google_email'] ) AND !EMPTY( $params['google_email'] ) )
					{
						$v->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$google_email.'|exclude_id=user_id|exclude_value='.$id, '@custom_error_The google email address entered has already been used. Please use a different email.')->sometimes();
					}
					$v->check('google_email', $params);
			}

			$mobile_no 	= $params['mobile_no'];

			if( !EMPTY( $mobile_no ) )
 			{
 				if( strlen($mobile_no) == 10 )  
 				{
 					$mobile_no = '0'.$mobile_no;
 				}
 			}
 			
 			if( !EMPTY($mobile_no ) )
 			{
 				$v 
 					->mobile_no_unique($id)
 					->check('mobile_no', $mobile_no);
 			}

			$v->assert(FALSE);
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}
	
	private function _validate_email($email, $id)
	{
		try
		{
			$exist_flag = $this->users->check_email_exist($email, $id);
			
			return $exist_flag['email_exist'];
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
		
	}
	
	private function _send_welcome_email($id, array $params = array(), $for_insert = FALSE, $raw_arg_password = NULL)
	{	
		try
		{
			$user_detail 	= $this->users->get_user_details($id);
			$created_by 	= $this->users->get_user_details($user_detail['created_by']);

			$sys_logo 				 	= get_setting(GENERAL, "system_logo");
			$system_logo_src 			= base_url() . PATH_IMAGES . "logo_white.png";

			if( !EMPTY( $sys_logo ) )
			{
				$root_path 				= $this->get_root_path();
				$sys_logo_path 			= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
				$sys_logo_path 			= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

				if( file_exists( $sys_logo_path ) )
				{
					$system_logo_src 	= base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
					$system_logo_src 	= @getimagesize($system_logo_src) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
				}
			}
			
			
			$status 		= ERROR;
			$email_data 	= array();
			$template_data 	= array();
	
			$salt 			= gen_salt(TRUE);
			$system_title 	= get_setting(GENERAL, "system_title");
				
			// required parameters for the email template library
			$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"] 	= array($user_detail['email']);
			$email_data["subject"] 		= 'New User Account';
				
			// additional set of data that will be used by a specific template
			$template_data["email"] 		= $user_detail['email'];
			$template_data["password"] 		= base64_url_encode($user_detail['password']);
			$template_data['username']		= $user_detail['username'];
			$template_data["reason"] 		= $user_detail['reason'];
			$template_data["name"] 			= $user_detail['fname'] . ' ' . $user_detail['lname'];
			$template_data["created_by"] 	= $created_by['fname'] . ' ' . $created_by['lname'];
			$template_data["creator"] 		= $template_data["created_by"];
			$template_data["system_name"] 	= $system_title;
			$template_data['logo']			= $system_logo_src;
			$template_data["email_subject"] = 'New User Account';

			$raw_password 					= '';

			$template_data['logo']			= '
				<div style="background:#333333; padding:20px 30px; text-align:center;"><img src="'.$template_data['logo'].'" height="40" alt="logo" /></div>
';

			if( ISSET( $params['password'] ) )
			{
				$raw_password 					= preg_replace('/\s+/', '', $params['password']);
			}

			if( $params['password_creation'] == SET_SYSTEM_GENERATED )
			{
				$raw_password 				= $params['system_generated_password'];
			}

			if( !EMPTY( $raw_arg_password ) )
			{
				$raw_password 	= $raw_arg_password;
			}

			$template_data['raw_password'] = $raw_password;
			$template_data['password'] 		= $raw_password;

			$change_password_url 			= base_url().'auth/change_password_owner/'.base64_url_encode( $user_detail['username'] ).'/'.$user_detail['salt'].'/'.INITIAL_YES.'/1/';

			$template_data['change_password_url'] = $change_password_url;

			$template_data['link'] = '
				<div style="border:1px solid #E3DEB5; background:#FBF7DC; border-radius:2px; padding:15px; word-break:keep-all; margin-top:20px;">
				  <p style="font-size:14px; font-family:"Open Sans", arial;margin:10px 0; line-height:25px; word-break:keep-all;">
					<a style="word-wrap:break-word" href="'.$change_password_url.'" title="Change Password" target="_blank">Change Password</a>
				  </p>
				</div>
';

			$message = '';

			if( $for_insert )
			{
				if( $params['password_creation'] == SET_ACCOUNT_OWNER ) 
				{
					// $message 	= $this->load->view('emails/account_owner', $template_data, TRUE);
					$message 	= $this->email_template->get_email_template(STATEMENT_CODE_EMAIL_ACCOUNT_OWNER, $template_data);
				}
				else
				{
					$message 	= $this->email_template->get_email_template(STATEMENT_CODE_EMAIL_WELCOME_USER, $template_data);
				}
			}	
			else
			{
				if( $params['password_creation'] == SET_ACCOUNT_OWNER ) 
				{
					// $this->email_template->send_email_template($email_data, "emails/account_owner", $template_data);

					$this->email_template->send_email_template_html($email_data, STATEMENT_CODE_EMAIL_ACCOUNT_OWNER, $template_data);
				}
				else
				{
					$this->email_template->send_email_template_html($email_data, STATEMENT_CODE_EMAIL_WELCOME_USER, $template_data);
				}
			}


			$status = SUCCESS;

			$errors 						= $this->email_template->get_email_errors();

			if( !EMPTY( $errors ) )
			{
				$str 						= var_export( $errors, TRUE );

				RLog::error( "Email Error" ."\n" . $str . "\n" );
			}

			if( $for_insert )
			{
				return array(
					'message'	 => $message,
					'email_data' => $email_data,
					'template_data' => $template_data
				);
			}

			return $status;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	
	}

	public function check_if_created_user()
	{
		$msg 	= "";
		$params = get_params();

		$check_cnt  = 0;

		try
		{
			// $check 	= $this->users->get_specific_user( $this->user_id );

			if( ISSET( $params['user_id'] ) AND !EMPTY( $params['user_id'] ) AND $params['user_id'] == $this->session->user_id )
			{
				$check_cnt = 1;
			}
		}
		catch( PDOException $e )
		{
			$this->rlog_error( $e );

			$msg = $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			$this->rlog_error( $e );

			$msg = $e->getMessage();
		}

		$response 		= array(
			'msg'	 	=> $msg,
			'check'		=> $check_cnt,
			'name'		=> $this->session->name
		);

		echo json_encode( $response );
	}

	public function refresh_list()
	{
		$html 	= $this->_construct_active_users_list();
		echo $html;
	}

	private function _construct_active_users_list()
	{
		$active_users 	= $this->users->get_active_users();
		$html 			= "";

		$html 			.= '<li class="list-sub-nav-parent">Active Users</li>';

		if( !EMPTY( $active_users ) )
		{
			$root_path 	= $this->get_root_path();	

			$check_upl 	= $this->check_custom_path();

			foreach($active_users as $user)
			{
				$img_src 		= base_url().PATH_IMAGES . "avatar.jpg";

				$photo_path 	= "";

				if( !EMPTY( $user['photo'] ) )
				{
					$photo_path = $root_path.PATH_USER_UPLOADS.$user['photo'];
					$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);
					
					if( file_exists( $photo_path ) )
					{
						if( !EMPTY( $check_upl ) )
						{
							$img_src = output_image($user['photo'], PATH_USER_UPLOADS);
						}
						else
						{
							$img_src = base_url() . PATH_USER_UPLOADS . $user['photo'];
						}
					}
					else
					{
						$photo_path = "";
					}
				}

				if( !EMPTY( $photo_path ) )
				{
					$avatar 	= '<img class="circle"width="35" height="35" src="'.$img_src.'" /> ';
				}
				else
				{
					$avatar 	= '<img class="circle default-avatar" width="30" height="30" data-name="'.$user["name"].'" /> ';
				}

		    	/*if( !EMPTY( $user['photo'] ) )
				{

					$photo_path = FCPATH.PATH_USER_UPLOADS.$user['photo'];
					$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);

					if( file_exists( $photo_path ) )
					{
						$img_src = base_url() . PATH_USER_UPLOADS . $user['photo'];
					}
				}*/
				
				$attribute		= ($user['user_id'] == $this->session->user_id) ? 'class="grey-text text-lighten-3" ' : 'class="tooltipped grey-text text-lighten-3" data-position="bottom" data-delay="50" data-tooltip="Force Log Out"'; 
				$force_logout 	= ($user['user_id'] == $this->session->user_id) ? "javascript:;": "force_logout('".base64_url_encode($user['user_id'])."')";
				$is_own_account = ($user['user_id'] == $this->session->user_id) ? '' : '<i class="material-icons">settings_power</i>';
				
				$html.='<li class="">
				   			<a href="javascript:;" onclick="'.$force_logout.'" '.$attribute.'>
								<div class="table-display">
									<div class="table-cell s3 valign-top">
										'.$avatar.'
									</div>
					 				<div class="table-cell s6 valign-top p-l-xs">
					 					
					 					<div class="font-semibold m-b-xs">'.$user['name'].'</div>
					 					<small class="mute">'.$user['username'].'</small>
					 				</div>
									<div class="table-cell s3 valign-top right-align user-logout-icon">
					 					'.$is_own_account.'
					 				</div>
								</div>
				   			</a>
				  	</li>';
			}
		}
		
		return $html;
	}

	public function force_sign_out()
	{		
		try
		{
			$msg 		= "";
			$flag 		= 0;
			$params 	= get_params();
			
			$user_id 	= base64_url_decode($params["user_id"]);
			$this->auth_model->update_log($user_id, LOGGED_IN_FLAG_NO);
			
			$flag 		= 1;
			$msg 		= $this->lang->line('data_saved');
			
			$list 		= $this->_construct_active_users_list();
		
		}
		catch( PDOException $e )
		{
			$this->rlog_error( $e );

			$msg 	= $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			$this->rlog_error( $e );

			$msg 	= $e->getMessage();
		}
		
		$result		= array(
			"flag" 	=> $flag,
			"msg" 	=> $msg,
			"list"	=> $list
		); 
												
		echo json_encode($result);
	}


	public function download_template($extra = NULL)
	{
		try
		{
			$per_msg 			= $this->lang->line( 'err_unauthorized_download' );

			if( !$this->download_per )
			{
				throw new Exception( $per_msg );
			}

			$extra_arr 	= array();

			if( !EMPTY( $extra ) )
			{
				$extra_json 	= json_decode(base64_url_decode($extra));
				$extra_arr 		= (array) $extra_json;

				foreach( $extra_json as $k => $ex )
				{
					$extra_arr[$k] = (array) $ex;
				}
				
			}

			$this->load->library('Import_template');
			
			$php_excel 				= new PHPExcel();

			$system_title 			= get_setting(GENERAL, "system_title");

			$model_obj 				= $this->itm;

			$php_excel->getProperties()
			   ->setCreator($system_title)
			   ->setTitle('Users Import Template')
			   ->setDescription('A facility to upload multiple users through excel')
			   ->setSubject('Users Import Template')
			   ->setKeywords('Users Import Template')
			   ->setCategory('upload');


			$this->import_template->column_with_dropdown[] = 'gender';
			$this->import_template->column_with_dropdown[] = 'status';
			$this->import_template->column_with_dropdown[] = 'role_code';
			$this->import_template->column_with_dropdown[] = 'org_code';
			

			$references 		= new \PHPExcel_Worksheet($php_excel, REFERENCE_SHEET);
	    	
	    	$this->import_template->set_sheet_protection_password( $references, TEMPLATE_PASSWORD );

	    	$php_excel->addSheet($references);

	    	$this->init_reference( $references, array('extension_code' => 'ename') );

	    	$password_creation 	= get_setting(PASSWORD_INITIAL_SET, "password_creator");
			$username_creation 	= get_setting('USERNAME_CREATE', "username_creator");

			$user_col_arr 		= array(
					'user_id',
					'org_code',
					'location_code',
					'created_by', 
					'created_date',
					'modified_by',
					'modified_date',
					'salt', 
					'reset_salt',
					'ext_name',
					'photo',
					'logged_in_flag',
					'consent_form_sys_filename',
					'consent_form_orig_filename',
					'expire_dpa_date',
					'mail_flag',
					'contact_flag',
					'reason',
					'attempts',
					'initial_flag',
					'pw_email_flag',
					'last_logged_in_date',
					'inactivated_by',
					'inactivated_date',
					'blocked_by',
					'blocked_date',
					'built_in_flag',
					'receive_email_flag',
					'receive_sms_flag',
					'temporary_account_flag',
					'temporary_account_expiration_date',
					'facebook_email',
					'google_email',
					'last_logged_in_attempt_date',
					'soft_blocked',
					'soft_blocked_date',
					'soft_attempts'
				);

			$req_arr 	= array(
    			'lname',
    			'fname',
    			'email',
    			/*'password',
    			'username',*/
    			// 'gender',
    			'role_code',
    			'org_code'
    		);

			if( $password_creation != SET_ADMINISTRATOR )
			{
				$user_col_arr[] = 'password';
			}
			else
			{
				$req_arr[] 		= 'password';
			}

			if( $username_creation != SET_ADMINISTRATOR )
			{
				$user_col_arr[] = 'username';
			}
			else
			{
				$req_arr[] 		= 'username';
			}

			$users 				= $this->itm->get_columns_table_import( $model_obj::CORE_TABLE_USERS, 
				$user_col_arr
			);

			/*$organization_parent 	= $this->itm->get_columns_table_import( $model_obj::CORE_TABLE_ORGANIZATION_PARENTS, array('path') );*/

			$filename 				= 'Users_import_template.xlsx';

	    	$loc_worksheet_obj_arr 		= array();

	    	$this->import_template->sheet_input[] 	= USERS_SHEET;

	    	$users_sheet 		= $this->import_template->write_to_worksheet( $php_excel, USERS_SHEET, $users, FALSE, array(), array(
	    			'role_code',
	    			'org_code'
	    		), TRUE, 
	    		$req_arr, array(),
	    		DB_CORE,
	    		$extra_arr
	    	);


	    	$php_excel->removeSheetByIndex(0);

	    	if( !EMPTY( $this->import_template->sheet_columns ) )
	    	{
	    		foreach( $this->import_template->sheet_columns as $sheet_name => $cols )
	    		{
	    			$sheet_obj 		= $php_excel->getSheetByName($sheet_name);

	    			$this->import_template->set_sheet_protection_password($sheet_obj);

	    			$end_col 		= end( $cols );
	    			
	    			$sheet_obj->getStyle( $cols[0].'2:'.$end_col.TEMPLATE_DR_NUM_DV )
	    				->getProtection()
	    				->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
	    		}
	    	}

	    	$php_excel->setActiveSheetIndexByName(USERS_SHEET);
			// $writer = \PHPExcel_IOFactory::createWriter($surrenderer_intervention_sheet, 'Excel2007');
			$writer = \PHPExcel_IOFactory::createWriter($users_sheet, 'Excel2007');
			// Excel5
			
			ob_end_clean();
			header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			
			$writer->save('php://output');
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);	

			$this->error_index( $msg );
		}
	}

	public function init_reference( PHPExcel_Worksheet $surrenderer_references, array $change_names = array() )
	{
		try
		{
			$active_flag 	= $this->import_template->get_active_flag('answer', 'answer_name', TRUE);
			$gender 		= $this->import_template->get_genders();
			$user_status 	= $this->import_template->get_user_status();
			$roles 			= $this->users->get_roles_import();
			$orgs  			= $this->users->get_orgs_import();

	    	if( !EMPTY( $active_flag ) )
	    	{
	    		$active_flag_sheet 			= $this->import_template->write_append( $surrenderer_references, $active_flag, array(), $change_names );
	    	}
	    	else
	    	{
	    		$active_flag_sheet 			= 0;
	    	}

	    	if( !EMPTY( $gender ) )
	    	{
	    		$gend_sheet 			= $this->import_template->write_append( $surrenderer_references, $gender, $active_flag_sheet, $change_names );
	    	}
	    	else
	    	{
	    		$gend_sheet 			= $active_flag_sheet;
	    	}

	    	if( !EMPTY( $user_status ) )
	    	{
	    		$status_sheet 			= $this->import_template->write_append( $surrenderer_references, $user_status, $gend_sheet, $change_names );
	    	}
	    	else
	    	{
	    		$status_sheet 			= $gend_sheet;
	    	}

	    	if( !EMPTY( $roles ) )
	    	{
	    		$roles_sheet 			= $this->import_template->write_append( $surrenderer_references, $roles, $status_sheet, $change_names );
	    	}
	    	else
	    	{
	    		$roles_sheet 			= $status_sheet;
	    	}

	    	if( !EMPTY( $orgs ) )
	    	{
	    		$org_sheet 			= $this->import_template->write_append( $surrenderer_references, $orgs, $roles_sheet, $change_names );
	    	}
	    	else
	    	{
	    		$org_sheet 			= $roles_sheet;
	    	}
	    	
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch(Exception $e)
		{
			$this->rlog_error($e);
			throw $e;
		}
	}


	public function import()
	{

		$msg 					= "";
		$flag  					= 0;
		$status 				= ERROR;

		$orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$params 			= $orig_params;
		$path 				= NULL;
		$header 			= array();
		$err_arr 			= array();
		$upl_arr 			= array();

		try
		{
			$per_msg 			= $this->lang->line( 'err_unauthorized_import' );

			if( !$this->import_per )
			{
				throw new Exception( $per_msg );
			}
			

			$this->_validate_import( $params );

			$this->load->library('Notification_queues');
			$this->load->library('Import_template'); 
			$this->load->library('Doc_parser');


			if( ISSET( $params['org_import'] ) AND !EMPTY( $params['org_import'] ) )
			{
				$path 			= FCPATH.PATH_USER_IMPORTS.$params['org_import'];
				$path 			= str_replace(array('/', '\\'), array(DS, DS), $path);

				$model_obj 		= $this->users;

				if( file_exists( $path ) )
				{
					$data 		= $this->doc_parser->parse($path, USERS_SHEET, TRUE);

					$users 				= $this->itm->get_columns_table_import( $model_obj::CORE_TABLE_USERS);

					if( ISSET( $data['by_row'][USERS_SHEET][1]['formatted_value'] ) )
					{
						$header = $data['by_row'][USERS_SHEET][1]['formatted_value'];
					}
						
					if( !EMPTY( $users ) )
					{
						$users[] = 'org_code';
						$users[] = 'role_code';
						$users[] = 'middle_name';
						if( !EMPTY( $header ) )
						{
							foreach( $header as $h )
							{
								if( !in_array($h, $users, TRUE) )
								{
									throw new Exception('Invalid File Format for header ('.$h.').');
								}
							}
						}
						else
						{
							throw new Exception('Invalid File Format.');
						}
					}

					if( !ISSET($data['by_first_row']) OR EMPTY( $data['by_first_row'] ) )
					{
						throw new Exception('File was empty.');
					}

					if( ISSET( $data['by_row'][USERS_SHEET] ) )
					{
						if( ISSET( $data['by_row'][USERS_SHEET][1] ) )
						{
							unset($data['by_row'][USERS_SHEET][1]);
						}

						SYSAD_Model::beginTransaction();

						$process_data = $this->process_data($data['by_row'][USERS_SHEET], $header);
									
						$ins_arr 	  = $process_data['ins_arr'];
						$err_arr 	  = $process_data['err_arr'];
						$parent_org_arr = $process_data['parent_org_arr'];
						$succ_rows 	= $process_data['succ_rows'];
						$user_id_arr = $process_data['user_id_arr'];
						$upl_arr 		= $process_data['upl_arr'];

						if( !EMPTY( $user_id_arr ) )
						{
							$main_where 		= array(
								'user_id'		=> array( 'IN', $user_id_arr )
							);

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USERS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$curr_detail[] 				= $this->users->get_user_details( $user_id_arr );

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ROLES;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$curr_detail[] 				= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_ROLES,
												$main_where
											 );

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$curr_detail[] 				= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS,
												$main_where
											 );

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_AGREEMENTS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$curr_detail[] 				= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_AGREEMENTS,
												$main_where
											 );

							$audit_name 				= 'Users Imported';

							$audit_activity 			= sprintf( $this->lang->line('audit_trail_add'), $audit_name);

							$this->audit_trail->log_audit_trail( $audit_activity, $this->module_code, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
						}

						SYSAD_Model::commit();
						
						if( !EMPTY( $err_arr ) )
						{
							if( !EMPTY( $succ_rows ) ) 
							{
								$succ_rows_str = implode(', ', $succ_rows);
								throw new Import_exception('Row(s) '.$succ_rows_str.' was imported but, there are some errors found.');
							}
							else
							{
								throw new Import_exception('There are some errors found.');
							}
						}
					}
				}
				else
				{
					throw new Exception("File doesn't exists.");
				}
			}

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					=$this->lang->line( 'data_imported' );
		}
		catch( PDOException $e )
		{
			if( !EMPTY( $path ) )    
			{
				if( file_exists( $path ) )
				{
					unlink($path);
				}
			}

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{
			if( !EMPTY( $path ) )    
			{
				if( file_exists( $path ) )
				{
					unlink($path);
				}
			}

			if( $e instanceof Import_exception )
			{
				$flag 	= 2;
			}
			else
			{
				SYSAD_Model::rollback();
			}

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}
		catch( Import_exception $e )
		{
			if( !EMPTY( $path ) )    
			{
				if( file_exists( $path ) )
				{
					unlink($path);
				}
			}
			// SYSAD_Model::rollback();
			$msg 	= $this->rlog_error($e, TRUE);
			$flag 	= 2;
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status' 				=> $status,
			'err_arr' 				=> $err_arr,
			'upl_arr' 				=> $upl_arr,
			'header'  				=> $header,
			'datatable_options'		=> $this->dt_options,
			'datatable_id'			=> $this->table_id
		);

		echo json_encode( $response );
	}

	protected function process_data(array $data = array(), array $header = array())
	{
		$ins_arr 			= array();
		$err_arr 			= array();
		$parent_org_arr 	= array();
		$succ_rows 			= array();
		$role_arr 			= array();

		$user_id_arr 		= array();

		$upl_arr 			= array();

		try
		{

			$password_creation 	= get_setting(PASSWORD_INITIAL_SET, "password_creator");
			$username_creation 	= get_setting('USERNAME_CREATE', "username_creator");
			$dpa_enable 			= check_dpa_enable();
			$strict_mode 			= get_setting(DPA_SETTING, 'dpa_strict_mode');
			$has_agreement_text 	= get_dpa_privacy_type();

			$enc_col_arr 		= array(
				'lname',
				'fname',
				'mname',
				'nickname',
				'email',
				'job_title',
				'mobile_no',
				'contact_no'
			);

			if( !EMPTY( $data ) )
			{
				$k 	= 0;
				$e 	= 0;
				foreach( $data as $row_k => $d )
				{
					$arr 		= array();
					$err_msg 	= '';

					foreach( $d['formatted_value'] as $key => $v )
					{
						$real_val 			= explode(TEMPLATE_DR_MARK, $v);

						$hname 				= $header[$key];

						if( $header[$key] == 'middle_name' )
						{
							$hname 			= 'mname';
						}

						$arr[$hname] 		= $real_val[0];
					}

					if( !EMPTY( $arr ) )
					{

						$validate = $this->core_v;

						$email 	= 'CAST('.aes_crypt('email', FALSE, FALSE).' AS char(100))';
						$usname = 'CAST('.aes_crypt('username', FALSE, FALSE).' AS char(100))';

						if( $username_creation == SET_ADMINISTRATOR )
						{

							$validate 
								->required()
								->maxlength(100)
									->sometimes()
								->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$usname)
									->sometimes()
								->check('username', $arr);
						}

						if( $password_creation == SET_ADMINISTRATOR )
						{

							$validate 
								->required()
								->check('password', $arr);
						}

						$validate 
							->required()
							->maxlength(100)
								->sometimes()
							->check('fname', $arr);

						$validate 
							->required()
							->maxlength(100)
								->sometimes()
							->check('lname', $arr);

						$validate 
							->maxlength(100)
								->sometimes()
							->check('mname', $arr);

						$validate 
							->maxlength(20)
								->sometimes()
							->check('nickname', $arr);

						$validate 
							// ->required()
							->in(array('GENDER_MALE', 'GENDER_FEMALE'))
								->sometimes()
							->check('gender', $arr);

						$validate 
							->required()
							->maxlength(200)
								->sometimes()
							->email()
								->sometimes()
							->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$email)
								->sometimes()
							->blacklist_email_domain()
								->sometimes()
							->check('email', $arr);

						$validate 
							->maxlength(200)
								->sometimes()
							->check('job_title', $arr);

						$validate 
							->phone()
								->sometimes()
							->check('contact_no', $arr);

						$mobile_no 	= $arr['mobile_no'];

						if( !EMPTY( $mobile_no ) )
			 			{
			 				if( strlen($mobile_no) == 10 )  
			 				{
			 					$mobile_no = '0'.$mobile_no;
			 				}
			 			}

						$validate 
							->mobileno()
								->sometimes()
							->mobile_no_unique()
								->sometimes()
							->check('mobile_no', $mobile_no);

						$validate 
							->required()
							->in(array(STATUS_ACTIVE, STATUS_INACTIVE))
								->sometimes()
							->check('status', $arr);

						$validate 
							->required()
							->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ROLES.'|primary_id=role_code')
								->sometimes()
							->check('role_code', $arr);

						$validate 
							->required()
							->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ORGANIZATIONS.'|primary_id=org_code')
								->sometimes()
							->check('org_code', $arr);

						if( $validate->validation_fails() )
						{
							if( !EMPTY( $validate->errors()->all() ) )
							{
								$validate->errors()->start_delimiter = '<li>';
								$validate->errors()->end_delimiter   = '</li>';
								$err_msg = $validate->errors()->toStringErr(array(), FALSE);

								$validate->resetMessage();
							}
						}

						if( !EMPTY( $err_msg ) )
						{
							foreach( $d['formatted_value'] as $key => $v )
							{
								$err_arr[$e][$header[$key]]	= $v;
							}

							$err_arr[$e]['row_index'] 	= ( $row_k - 1 );
							$err_arr[$e]['error_msg'] 	= $err_msg;

							$e++;
						}
						else
						{
							$succ_rows[] 	= ( $row_k - 1 );
							
							foreach( $d['formatted_value'] as $key => $v )
							{
								$real_val 					= explode(TEMPLATE_DR_MARK, $v);
								$org_code 					= NULL;
								$iins_arr 					= array();

								$upl_arr[$k][$header[$key]]	= $v;

								$hname 				= $header[$key];

								if( $header[$key] == 'middle_name' )
								{
									$hname 			= 'mname';
								}

								if( in_array($hname, $enc_col_arr) )
								{
									$ins_arr[$hname] = array( $real_val[0], 'ENCRYPT' );
								}
								else
								{
									$ins_arr[$hname] = $real_val[0];
								}

								if( $hname == 'role_code' )
								{
									$role_arr[$hname] = $real_val[0];

									unset($ins_arr[$hname]);
								}

								if( $hname == 'org_code' )
								{
									$parent_org_arr[$hname] = $real_val[0];

									unset($ins_arr[$hname]);
								}

								
								$ins_arr['created_by'] 	= $this->session->user_id;
								$ins_arr['created_date'] 	= date('Y-m-d H:i:s');
							}

							$salt 			= gen_salt(TRUE);

							$clean_password = '';

							if( $password_creation == SET_ADMINISTRATOR )
							{
								$clean_password 	= preg_replace('/\s+/', '', $ins_arr['password']);
								
								$ins_arr["password"] 	= in_salt($clean_password, $salt, TRUE);
								$ins_arr["salt"] 		= $salt;
							}

							if( $password_creation == SET_SYSTEM_GENERATED 
								OR $password_creation == SET_ACCOUNT_OWNER
							)
							{
								$clean_password 	= generate_password();;
								
								$ins_arr["password"] 	= in_salt($clean_password, $salt, TRUE);
								$ins_arr["salt"] 		= $salt;
							}

							if( $username_creation == SET_SYSTEM_GENERATED 
								OR $username_creation == SET_ACCOUNT_OWNER
							)
							{

								$usname 	= generate_username($ins_arr['fname'], $ins_arr['lname']);
								
								$ins_arr['username']	= array( $usname, 'ENCRYPT' );
							}
							else
							{
								$ins_arr['username']	= array( $ins_arr['username'], 'ENCRYPT' );	
							}

							$ins_arr['org_code']	= $parent_org_arr['org_code'];	

							if( !EMPTY( $dpa_enable ) )
							{
								if( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT )
								{
									if( $strict_mode == DATA_PRIVACY_STRICT_EMAIL_NOTIF )
									{
										$ins_arr['status'] = DPA_PENDING;
									}
									else
									{
										if( !ISSET( $ins_arr['status'] ) )
										{
											$ins_arr['status']	= ACTIVE;
										}			
									}
								}
								else
								{
									if( !ISSET( $ins_arr['status'] ) )
									{
										$ins_arr['status']	= ACTIVE;
									}
								}
							}
							else
							{
								if( !ISSET( $ins_arr['status'] ) )
								{
									$ins_arr['status']	= ACTIVE;
								}
							}
										
							$user_id 	= $this->users->insert_helper( SYSAD_Model::CORE_TABLE_USERS, $ins_arr );

							$role_arr['user_id']			= $user_id;
							$role_arr['main_role_flag'] 	= 1;
							$parent_org_arr['user_id'] 		= $user_id;
							$parent_org_arr['main_org_flag'] 	= 1;

							if( !EMPTY( $role_arr ) AND !EMPTY( $user_id ) )
							{
								$this->users->insert_helper( SYSAD_Model::CORE_TABLE_USER_ROLES, $role_arr );
							}

							if( !EMPTY( $parent_org_arr ) AND !EMPTY( $user_id ) )
							{
								$this->users->insert_helper( SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, $parent_org_arr );
							}

							if( !EMPTY( $user_id ) )
							{
								$this->users->insert_helper( SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, array('user_id' => $user_id) );

								$email_detail 	= array();

								$ins_arr['password_creation'] = $password_creation;

								if( EMPTY( $dpa_enable ) )
								{
									$email_detail = $this->_send_welcome_email( $user_id, $ins_arr, TRUE, $clean_password );
								}
								else
								{
									if( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT )
									{
										if( $strict_mode == DATA_PRIVACY_STRICT_EMAIL_NOTIF )
										{
											$email_detail = $this->_send_dpa_email( $user_id, $ins_arr, TRUE, $clean_password );
										}
										else
										{
											$email_detail = $this->_send_welcome_email( $user_id, $ins_arr, TRUE, $clean_password );
										}
									}
									else
									{
										
										$email_detail 	= $this->_send_welcome_email( $user_id, $ins_arr, TRUE, $clean_password );
									}
								}
								
								if( !EMPTY( $email_detail ) )
								{
									$this->notification_queues->insert_email_to_multi_user(
										array( $this->session->user_id ),
										$email_detail['message'],
										$this->session->user_id,
										array( 'subject' => $email_detail['email_data']['subject'] )
									);
								}
							}

							$user_id_arr[] = $user_id;

							$upl_arr[$k]['row_index'] 	= ( $row_k - 1 );
							$upl_arr[$k]['msg'] 		= 'Detail(s) uploaded.';
							
							$k++;
						}

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
			'header'	=> $header,
			'ins_arr'   => $ins_arr,
			'parent_org_arr' => $parent_org_arr,
			'err_arr'	=> $err_arr,
			'succ_rows' => $succ_rows,
			'role_arr'	=> $role_arr,
			'upl_arr' 		=> $upl_arr,
			'user_id_arr' => $user_id_arr
		);
	}

	private function _validate_import( array $params )
	{
		try
		{
			$v 	= $this->core_v;

			$v 
				->required()
				->check('org_import|Import File', $params);

			$v->assert(FALSE);
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}

	public function get_lazy_orgs()
	{
		$options 		= array();
		$orig_params 	= get_params();

		try
		{
			$params 	= $orig_params;

			$index 		= 0;
			$keyword 	= '';

			if( ISSET( $params['index'] ) )
			{
				$index 	= $params['index'];
			}

			if( ISSET( $params['keyword'] ) )
			{
				$keyword = $params['keyword'];
			}

			$sel_val 			= ( ISSET( $params['sel_val'] ) ) ? $params['sel_val'] : array();
			$acts 				= $this->orgs->get_orgs_all_lazy( $index, $keyword );
			$acts 				= $this->process_parents($acts);

			if( !EMPTY( $acts ) )
			{
				foreach( $acts as $gm )
				{
					$id_gs 			= $gm['org_code'];

					if( !EMPTY( $sel_val ) AND in_array($gm['org_code'], $sel_val) )
					{
						continue;
					}
					
					$options[] 		= array(
		    			'text'		=> $gm['name'],
		    			'value' 	=> $id_gs,
		    			'org_parent_names'	=> $gm['org_parent_names']
		    		);
				}
			}

		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);

			// $this->error_modal($msg);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			// $this->error_modal($msg);
		}

		echo json_encode($options);
	}
}
