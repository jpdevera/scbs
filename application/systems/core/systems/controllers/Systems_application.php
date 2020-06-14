<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Systems_application extends SYSAD_Controller {
	
	private $controller;
	private $module;
	private $module_js;
	private $path;
	private $table_id;

	private $view_per 			= FALSE;
	private $edit_per 			= FALSE;
	private $add_per 			= FALSE;
	private $delete_per 		= FALSE;

	private $dt_options 	= array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->controller 	= strtolower(__CLASS__);
		$this->module 		= MODULE_SYSTEMS;
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_SYSTEMS."/".$this->controller;
		$this->path 		= CORE_SYSTEMS."/".$this->controller."/get_system_list";
		$this->table_id 	= "systems_table";
		
		$this->load->model('systems_application_model', 'systems_app');

		$this->dt_options 	= array(
			'table_id' 	=> $this->table_id, 
			'path'	 	=> $this->path, 
			'advanced_filter'	=> true, 
			'with_search' => true,
			
		);

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
	}
	
	public function index()
	{
		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$data 		= array();
			$resources 	= array();

			$resources['load_css'] = array(CSS_DATATABLE_MATERIAL, CSS_SELECTIZE);
			$resources['load_js'] = array(JS_DATATABLE, JS_DATATABLE_MATERIAL, $this->module_js);

			$resources['load_materialize_modal'] = array (
				'modal_systems' => array (
					'title' => "Systems",
					'size' => "sm-w md-h",
					'module' => CORE_SYSTEMS,
					'controller' => $this->controller
				)
			);

			$resources['datatable'] = $this->dt_options;
			
			$resources['loaded_init'] = array(
				'materialize_select_init();',
				'Systems.initObj();'
			);
		
			$this->template->load('systems', $data, $resources);
		}
		
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error( $e, TRUE );

			$this->error_index( $msg );
		}
	}
	
	public function get_system_list()
	{		

		$sEcho			= 0;
		$totalRecords	= 0;
		$totalDisplay	= 0;
		$aaData			= array();
		$flag			= 0;
		$msg			= "ERROR";
		
		try 
		{
			// $this->redirect_off_system($this->module);

			if($this->permission->check_permission($this->module) === FALSE)
			{
				throw new Exception($this->lang->line('err_unauthorized_access'));
			}
					
			$cnt = 0;
			$params		= get_params();
			$aColumns 	= array("A.system_code", "A.description", "A.system_name", "if (A.on_off_flag = '1', 'Active', 'Inactive') status", "logo", "A.core_flag");
			$bColumns 	= array("A.system_name", "A.description", "if (A.on_off_flag = '1' ,'Active', 'Inactive')");
			
			$system_list		= $this->systems_app->get_system_list($aColumns, $bColumns, $params);
			$iFilteredTotal 	= $this->systems_app->filtered_length($aColumns, $bColumns, $params);
			$iTotal 			= $this->systems_app->total_length();

			$cnt_system 		= count($system_list);

			$root_path 			= $this->get_root_path();

			foreach ($system_list as $aRow):
				$cnt++;
				$row = array();
				$action = "";
			
				$system_code = $aRow["system_code"];
				$id 		= base64_url_encode($system_code);
				$salt 		= gen_salt();
				$token 		= in_salt($system_code, $salt);			
				$url 		= $id."/".$salt."/".$token;

				$check_used_det 	= $this->systems_app->check_system_used($aRow["system_code"]);

				// PATH_SYSTEMS_UPLOADS
				
				$img_src = base_url() . PATH_SYSTEMS_UPLOADS . 'default_logo.png';

				$photo_path 	= "";

				if( !EMPTY( $aRow['logo'] ) )
				{
					$photo_path = $root_path.PATH_SYSTEMS_UPLOADS.$aRow['logo'];
					$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);
					
					if( file_exists( $photo_path ) )
					{
						$img_src = output_image($aRow['logo'], PATH_SYSTEMS_UPLOADS);
					}
					else
					{
						$photo_path = "";
					}
				}

				$avatar = '<span class="table-avatar-wrapper">';

				if( !EMPTY( $photo_path ) )
				{
					$avatar 	.= '<img class="avatar" width="20" height="20" src="'.$img_src.'" /> ';
				}
				else
				{
					$avatar 	.= '<img class="avatar default-avatar" data-name="'.$aRow["system_name"].'" /> ';
				}

				$avatar.='</span>';
				
				$row[] = $avatar.$aRow['system_name'];
				$row[] = $aRow['description'];
				$row[] = $aRow['status'];
				
				$action = "<div class='table-actions'>";

				$delete_class 	= "";

				if($this->edit_per)
				{
					$action .= "<a href='#modal_systems' class='modal_systems_trigger tooltipped' data-tooltip='Edit' data-position='bottom' data-delay='50' onclick=\"modal_systems_init('".$url."')\"><i class='material-icons'>mode_edit</i></a>";
				}
				
				if($this->delete_per)
				{
					$delete_class 	= "";
					$delete_tooltip = "Delete";

					if( ( !EMPTY( $check_used_det ) AND !EMPTY( $check_used_det['check_system_used'] ) )
						OR !EMPTY( $aRow['core_flag'] )
					)
					{
						$delete_class  	= "disabled";
						$delete_tooltip = "You are not allowed to delete this record.";
					}

					$action .= "<a href='javascript:;' onclick=\"content_delete('System', '".$id."')\" class='tooltipped ".$delete_class."' data-tooltip='".$delete_tooltip."' data-position='bottom' data-delay='50'><i class='material-icons'>delete</i></a>";
				}

			
				
				$action .= '</div>';
				
				if($cnt == $cnt_system)
				{
					$resources['preload_modal'] = array("modal_systems");
					$resources['loaded_init'] = array("Systems.initObj();");
					$action .= $this->load_resources->get_resource($resources, TRUE);
				}
				
				$row[] = $action;

				$aaData[] = $row;
			endforeach;
								
			
			$sEcho			= intval($params['sEcho']);
			$totalRecords	= $iTotal['cnt'];
			$totalDisplay	= $iFilteredTotal["cnt"];
			$flag 			= 1;
			$msg			= NULL;
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
		
		$output = array(
			'sEcho'					=> $sEcho,
			'iTotalRecords'			=> $totalRecords,
			'iTotalDisplayRecords'	=> $totalDisplay,
			'aaData'				=> $aaData,
			'flag'					=> $flag,
			'msg'					=> $msg
		);
		
		echo json_encode($output);
	}
	
	public function modal($encoded_id = NULL, $salt = NULL, $token = NULL)
	{
		$resources 	= array();
		$data		= array();

		$check_used  	= FALSE;

		try 
		{
			// $this->redirect_off_system($this->module);

			if( ! EMPTY($encoded_id)) // UPDATE
			{
				// CHECK SECURITY
				$hash_id = base64_url_decode($encoded_id);
				check_salt($hash_id, $salt, $token);
				
				$select_fields  = array("system_code", "system_name", "link", "on_off_flag", "logo", "description", 'core_flag');
				$info 			= $this->systems_app->get_specific_system($hash_id, $select_fields);

				$check_used_det = $this->systems_app->check_system_used( $hash_id );

				if( !EMPTY( $check_used_det ) AND !EMPTY( $check_used_det['check_system_used'] ) )
				{
					$check_used = TRUE;
				}

				if(empty($info))
					throw new Exception($this->lang->line('invalid_action'));

				$data['system_code'] = $info[0]['system_code'];
				$data['system_name'] = $info[0]['system_name'];
				$data['description'] = $info[0]['description'];
				$data['on_off_flag'] = $info[0]['on_off_flag'];
				$data['system_link'] = $info[0]['link'];
				$data['system_logo'] = $info[0]['logo'];
				$data['core_flag']	 = $info[0]['core_flag'];

			}

			$data['check_used'] 	= $check_used;
			
			$logo_arr = json_encode(array("id" =>"system_logo", "path" => PATH_SYSTEMS_UPLOADS));
			$logo_delete_arr = json_encode(array("id" =>"system_logo", "form_name" => "form_modal_systems", "path_images" => PATH_IMAGES, "default_image_preview" => "image_preview.png"));
			
			$resources['load_css'] 		= array(CSS_LABELAUTY, CSS_UPLOAD);
			$resources['load_js'] 		= array(JS_LABELAUTY, JS_UPLOAD, $this->module_js);
			$resources['loaded_init']	= array('Systems.modal_init()');
			$resources['upload'] = array(
				'system_logo' => array(
					'path' 					=> PATH_SYSTEMS_UPLOADS,
					'form_name' 			=> 'form_modal_systems',
					'allowed_types' 		=> 'jpeg,jpg,png,gif',
					'default_img_preview' 	=> 'image_preview.png',
					'page' 					=> 'site_settings',
					'multiple'				=> false,
					// 'auto_submit'			=> false,
					'successCallback'		=> 'Systems.successCallback('.$logo_arr.', data);',
					'deleteCallback'		=> 'Systems.deleteCallback('.$logo_delete_arr.', data);'
				)
			);

			$this->load->view('modals/systems', $data);
			$this->load_resources->get_resource($resources);
			
		}
		catch(PDOException $e)
		{
			$msg 	= $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}
	}

	public function save_system()
	{
		try
		{
			// $this->redirect_off_system($this->module);

			$msg = ERROR;
			$status = ERROR;
			$params = get_params();

			$this->_validate_system($params);

			// GET SECURITY VARIABLES
			$id		= filter_var($params['id'], FILTER_SANITIZE_STRING);
			$salt 	= $params['salt'];
			$token 	= $params['token'];
			
			// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
			check_salt($id, $salt, $token);

			$id 	= base64_url_decode($id);

			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$audit_table[]  = SYSAD_Model::CORE_TABLE_SYSTEMS;
			$audit_schema[]	= DB_CORE;

			$select_fields  = array("system_code", "system_name");

			if(!empty($id))
			{
				if($this->permission->check_permission(MODULE_SYSTEMS, ACTION_EDIT) === FALSE)
				{
					throw new Exception($this->lang->line('err_unauthorized_edit'));
				}
				
				$audit_action[]	= AUDIT_UPDATE;
				
				$prev_detail[]	= $this->systems_app->get_specific_system($id, $select_fields);

				$system_code  = $this->systems_app->update_system($params, $id);

				$msg = $this->lang->line('data_updated');
				
				
				$curr_detail[]  = $this->systems_app->get_specific_system($id, $select_fields);

				// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
				$activity = "%s has been updated";
			}
			else
			{
				if($this->permission->check_permission(MODULE_SYSTEMS, ACTION_ADD) === FALSE)
				{
					throw new Exception($this->lang->line('err_unauthorized_add'));
				}
				
				$audit_action[]	= AUDIT_INSERT;
				
				$prev_detail[]	= array();

				$system_code  = $this->systems_app->insert_system($params);

				$msg = $this->lang->line('data_saved');
				

				$curr_detail[]  = $this->systems_app->get_specific_system($system_code, $select_fields);

				// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
				$activity = "%s has been added";
			}

			$activity = sprintf($activity, $system_code);
	
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
			$msg = $e->getMessage();
		}

		$info = array(
			"status" => $status,
			"msg" => $msg,
			'datatable_options'	=> $this->dt_options
		);
		

		echo json_encode($info);
	}

	public function delete_sytem()
	{
		try
		{

			// $this->redirect_off_system($this->module);
				
			$status = ERROR;
			$params	= get_params();

			$action = AUDIT_DELETE;
	
			$id = base64_url_decode($params['param_1']);
				
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$audit_action[]	= AUDIT_DELETE;
			$audit_table[]  = SYSAD_Model::CORE_TABLE_SYSTEMS;
			$audit_schema[]	= DB_CORE;
	
			// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			$select_fields  = array("system_code", "system_name");
			$prev_detail[]  = $this->systems_app->get_specific_system($id, $select_fields);
			
			$this->systems_app->delete_system($id);
			$msg = $this->lang->line('data_deleted');
			
			$curr_detail[]  = array();
				
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
			$activity = "%s has been deleted";
			$activity = sprintf($activity, $prev_detail[0][0]['system_code']);
	
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
			"status" => $status,
			"msg" => $msg,
			"reload" => 'datatable',
			'datatable_options' => $this->dt_options
		);
	
		echo json_encode($info);
	}

	private function _validate_system(&$params) 
	{
		try 
		{
			
			// validate required field
			$required['system_name'] 	= 'System Name';

			if( ISSET( $params['system_code'] ) )
			{
				$required['system_code'] = 'System Code';
			}
		
			$required['system_link'] 	= 'System Link';

			$this->check_required_fields($params, $required);

			// validate input
			$validation['ticket_comment'] = array(
				'data_type'	=> 'string',
				'name'		=> 'Comment'
			);
			
			return $this->validate_inputs($params, $validation);
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
}