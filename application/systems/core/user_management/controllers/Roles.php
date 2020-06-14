<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Roles extends SYSAD_Controller 
{
	
	private $module;
	private $table_id;
	private $path;

	private $dt_options 	= array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module 		= MODULE_ROLE;
		$this->table_id 	= "roles_table";
		$this->path 		= CORE_USER_MANAGEMENT."/roles/get_role_list";
		
		$this->load->model('roles_model', 'roles');
		$this->load->model(CORE_COMMON.'/systems_model', 'systems');

		$this->dt_options 	= array(
			'table_id' 	=> $this->table_id, 
			'path'	 	=> $this->path, 
			'hidden_column' 	=> 4, 
			'advanced_filter'	=> true, 
			'with_search' => true
		);
	}
	
	public function index()
	{	
		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$data = $resources = array();

			$module_js = HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_USER_MANAGEMENT."/roles";
			
			$resources['load_css'] 	= array(CSS_DATATABLE_MATERIAL, CSS_SELECTIZE);
			$resources['load_js'] 	= array(JS_DATATABLE, JS_DATATABLE_MATERIAL, $module_js);
			
			$resources['datatable'] = $this->dt_options;

			$resources['load_materialize_modal'] = array(
			    'modal_roles' 		=> array (
					'title' 		=> "Create a role",
					'size'			=> "sm md-h",
					'module' 		=> CORE_USER_MANAGEMENT,
					'controller'	=> __CLASS__
			    )
			);

			$json_datatable_options	= json_encode( $this->dt_options );

			$data['add_per']		= $this->permission->check_permission($this->module, ACTION_ADD);

			$resources['loaded_init'] = array(
				'materialize_select_init();',
				'Roles.initObj();',
				"refresh_datatable('".$json_datatable_options."');"
			);
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
		
		$this->template->load('roles', $data, $resources);
	}
	
	public function get_role_list()
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();
		
		try
		{
			// $this->redirect_off_system($this->module);

			$params 	= get_params();
			$resources 	= array();
			$cnt 		= 0;
		
			$aColumns 	= array("A.role_code", "A.role_name", "IF(A.built_in_flag = 1, 'Yes', 'No') built_in", "GROUP_CONCAT(C.system_name SEPARATOR '<br>') systems", "IF(A.modified_date IS NULL, A.created_date, A.modified_date) date");
			$bColumns 	= array("role_code", "role_name", "systems", "built_in", "date");
		
			$roles 			= $this->roles->get_role_list($aColumns, $bColumns, $params);
			$iTotal 		= $this->roles->total_length();
			$iFilteredTotal = $this->roles->filtered_length($aColumns, $bColumns, $params);
		
			$output['aaData']				= array();
			$output['sEcho'] 				= intval($params['sEcho']);
			$output['iTotalRecords'] 		= $iTotal["cnt"];
			$output['iTotalDisplayRecords']	= $iFilteredTotal["cnt"];

			$edit_per 			= $this->permission->check_permission($this->module, ACTION_EDIT);
			$delete_per 		= $this->permission->check_permission($this->module, ACTION_DELETE);

			$role_cnt 			= count($roles);
		
			foreach ($roles as $arow)
			{
				$cnt++;
				
				$action 	= "";
			
				$role_code 	= $arow["role_code"];
				$id 		= base64_url_encode($role_code);
				$salt 		= gen_salt();
				$token 		= in_salt($role_code, $salt);			
				$url 		= $id."/".$salt."/".$token;
				
				$users_cnt 	= $this->roles->get_users_count($role_code);
				$ctr 		= $users_cnt['cnt'];
				
				$action_class = ($arow['built_in'] == "Yes") ? "disabled" : "";
				$delete_tooltip = ($arow['built_in'] == "Yes") ? "You are not allowed to delete this record." : "Delete";
				$edit_tooltip = ($arow['built_in'] == "Yes") ? "You are not allowed to edit this record." : "Edit";
				$edit_href = ($arow['built_in'] == "Yes") ? "javascript:;" : "#modal_roles"; 
				
				if($arow["built_in"] == "No")
				{
					$edit_action =  " onclick=\"modal_roles_init('".$url."')\" ";
					
					$delete_action = ($ctr > 0) ? 'alert_msg("'.ERROR.'", "'.$this->lang->line("parent_delete_error").'")' : 'content_delete("role", "'.$id.'")';
				}else{
					$delete_action = $edit_action = "";
				}
				$action = "<div class='table-actions'>";
				
				if($edit_per)
				{
					$action .= "<a href='".$edit_href."' class='tooltipped ".$action_class."' data-tooltip='".$edit_tooltip."' data-position='bottom' data-delay='50' ".$edit_action."><i class='material-icons'>mode_edit</i></a>";
				}
				
				if($delete_per)
				{
					$action .= "<a href='javascript:;' onclick='".$delete_action."' class='tooltipped ".$action_class."' data-tooltip='".$delete_tooltip."' data-position='bottom' data-delay='50'><i class='material-icons'>delete</i></a>";
				}

				$action .= '</div>';

				if($cnt == $role_cnt)
				{
					$resources['preload_modal'] = array("modal_roles");
					$resources['loaded_init'] 	= array("selectize_init();");
					$action .= $this->load_resources->get_resource($resources, TRUE);
				}

				$rows[] = array(
					$arow['role_code'],
					$arow['role_name'],
					$arow['systems'],
					$arow['built_in'],
					$arow['date'],
					$action
				);
			
			}
			
			$flag 			= 1;
			
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
		
		$output['aaData']			= $rows;
		$output['flag']				= $flag;
		$output['msg']				= $msg;
		
		echo json_encode($output);
	}
		
	public function modal($id = NULL, $salt = NULL, $token = NULL)
	{		
		try
		{
			// $this->redirect_off_system($this->module);

			$data = $resources = array();

			$sel_sys_roles 		= array();

			$def_sys_opt 		= array();
			$system_roles 		= array();
			
			if(!IS_NULL($id))
			{
				$id = base64_url_decode($id);
				
				// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
				check_salt($id, $salt, $token);
				
				$data["role"] = $this->roles->get_role_details($id);
				$system_roles = $this->roles->get_system_roles($id);

				if( !EMPTY( $system_roles ) )
				{
					$sel_sys_roles = array_column( $system_roles, 'system_code');
				}
			/*	if($system_roles){
					for ($i=0; $i<count($system_roles); $i++){
						$system_arr[] = $system_roles[$i]["system_code"]; 
					}
					$resources['multiple']['system_role'] = $system_arr;
				}*/
			}

			$systems						= $this->systems->get_systems();

			if(!IS_NULL($id))
			{
				$def_sys_opt 				= $system_roles;
			}

			$system_codes 					= array_column( $systems, 'system_code');
			$system_names 					= array_column( $systems, 'system_name');
			
			$data["systems"] 				= $systems;
			$data['system_json']			= json_encode( array_combine($system_codes, $system_names) );
			$data['sel_sys_roles']			= $sel_sys_roles;
			$data['def_sys_opt'] 			= $def_sys_opt;
			
			$resources['load_css'] 		= array(CSS_LABELAUTY, CSS_SELECTIZE, CSS_SUMO_SELECT);
			$resources['load_js'] 		= array(JS_LABELAUTY, JS_SELECTIZE, JS_SUMO_SELECT);
			$resources['loaded_init'] 	= array(
				'Roles.initModal();',
				'Roles.save();'
			);
			$resources['selectize'] 	= array (
				'selectize' 		=> array(
					'type' 				=> 'default'
				)
			);

			$resources['sumo_select'] 	= array(
				'#system_role' 		=> array(
					'selectAll' 	=> true,
					'search' 		=> true
				)
			);
			
			$this->load->view("modals/roles", $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);

			redirect(base_url() . 'errors/modal/500/'.base64_url_encode($msg) , 'location');
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			redirect(base_url() . 'errors/modal/500/'.base64_url_encode($msg) , 'location');
		}	
	}
		
	public function process()
	{
		try
		{
			// $this->redirect_off_system($this->module);

			$status = ERROR;
			$params	= get_params();

			// GET SECURITY VARIABLES
			$id			= filter_var($params['id'], FILTER_SANITIZE_STRING);
			$salt 		= $params['salt'];
			$token 		= $params['token'];

			if(EMPTY($id))
			{
				$action = AUDIT_INSERT;
			}
			else
			{
				$action = AUDIT_UPDATE;	
			}
				
			// SERVER VALIDATION
			$this->_validate($params, $action);
			
			// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
			check_salt($id, $salt, $token);
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$audit_table[] 	= SYSAD_Model::CORE_TABLE_ROLES;
			$audit_schema[]	= DB_CORE;
				
			if(EMPTY($id))
			{
				$audit_action[]	= AUDIT_INSERT;
				
				$prev_detail[]	= array();
				
				$id 			= $this->roles->insert_role($params);
				$msg 			= $this->lang->line('data_saved');
				
				// GET THE DETAIL AFTER INSERTING THE RECORD
				$curr_detail[] 	= array($this->roles->get_role_details($id));	
				
				// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
				$activity 		= "created a new user role ( %s ).";
				$activity 		= sprintf($activity, $params['role_name']);
			}
			else
			{
				$audit_action[]	= AUDIT_UPDATE;
				
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
				$prev_detail[] 	= array($this->roles->get_role_details($id));
				
				$this->roles->update_role($params);
				$msg 			= $this->lang->line('data_updated');
				
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_detail[] 	= array($this->roles->get_role_details($id));
				
				// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
				$activity 		= "updated role details ( %s ).";
				$activity 		= sprintf($activity, $params['role_name']);
			}
			
			
	
			// LOG AUDIT TRAIL
			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module, 
				$prev_detail, 
				$curr_detail, 
				$audit_action, 
				$audit_table,
				$audit_schema
			);
			
			SYSAD_Model::commit();
			$status = SUCCESS;
			
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
		
		$info = array(
			"status" 	=> $status,
			"msg" 		=> $msg,
			"datatable_options" => $this->dt_options
		);
	
		echo json_encode($info);
	
	}
		
	public function delete_role($id = NULL, $salt = NULL, $token = NULL)
	{
		try
		{
			// $this->redirect_off_system($this->module);
			
			$status = ERROR;
			$params	= get_params();
				
			$action = AUDIT_DELETE;
	
			// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
			$id 	= base64_url_decode($params['param_1']);
				
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$audit_action[]	= AUDIT_DELETE;
			$audit_table[] 	= SYSAD_Model::CORE_TABLE_ROLES;
			$audit_schema[]	= DB_CORE;
	
			// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			$prev_detail[] 	= array($this->roles->get_role_details($id));
			
			$this->roles->delete_role($id);
			$msg 			= $this->lang->line('data_deleted');
				
			// GET THE DETAIL AFTER UPDATING THE RECORD
			$curr_detail[] 	= array($this->roles->get_role_details($id));
				
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
			$activity 		= "deleted a role ( %s ).";
			$activity 		= sprintf($activity, $prev_detail[0][0]['role_name']);
	
			// LOG AUDIT TRAIL
			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module, 
				$prev_detail, 
				$curr_detail, 
				$audit_action, 
				$audit_table,
				$audit_schema
			);
				
			SYSAD_Model::commit();
			$status = SUCCESS;
				
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
	
		$info = array(
			"status" 	=> $status,
			"msg" 		=> $msg,
			"reload" 	=> 'datatable',
			"datatable_options" => $this->dt_options
		);
	
		echo json_encode($info);
	}
	
	private function _validate($params, $action = NULL)
	{
		$required 		= array();
		$constraints	= array();

		if($action == AUDIT_INSERT)
		{
			$required['role_code']	= 'Role code';
		}	
			
		$required['role_name']		= 'Role';

		$this->check_required_fields( $params, $required );

		$this->validate_inputs( $params, $constraints );
	}
	
}