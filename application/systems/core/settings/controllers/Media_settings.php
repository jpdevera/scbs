<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Media_settings extends SYSAD_Controller 
{
	private $module;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_MEDIA_SETTINGS;
		
		$this->load->model('site_settings_model', 'settings', TRUE);
	}

	public function index()
	{
		try
		{
			$resources = array();

			$module_js = HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_SETTINGS."/settings";

			$image_quality 	= get_setting(MEDIA_SETTINGS, "image_quality_compression");

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_NOUISLIDER);
			$resources['load_js'] 	= array(JS_LABELAUTY,JS_NOUISLIDER, JS_WNUMB, $module_js);

			$resources['loaded_init'] = array(
				'Settings.init_media_setting("'.$image_quality.'");',
				'Settings.save_media_setting();'
			);


			$permission 		= $this->permission->check_permission($this->module, ACTION_SAVE);

			$data 				= array(
				'permission'	=> $permission
			);

			$this->load->view('tabs/media_settings', $data);
			$this->load_resources->get_resource($resources);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index_tab($msg);
		}
		catch(Exception $e)
		{
			$msg  	= $this->rlog_error($e, TRUE);	

			$this->error_index_tab($msg);
		}
	}
	
	public function process()
	{
		try
		{
			// $this->redirect_off_system($this->module);
			
			$status = ERROR;
			$params	= get_params();
			$action = AUDIT_UPDATE;
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$fields = $this->settings->get_site_settings(MEDIA_LOCATION);
			
			foreach($fields as $field):
				$audit_action[]	= AUDIT_UPDATE;
				$audit_table[]	= SYSAD_Model::CORE_TABLE_SITE_SETTINGS;
				$audit_schema[]	= DB_CORE;
			
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			  	$prev_detail[] = array( $this->settings->get_site_settings(MEDIA_LOCATION, $field['setting_type'], $field['setting_name']) );
			  	$this->settings->update_settings($field['setting_type'], $params, $field['setting_name']);
					 
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_detail[] = array( $this->settings->get_site_settings(MEDIA_LOCATION, $field['setting_type'], $field['setting_name']) );
			endforeach;
			
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
			$activity = "%s has been updated";
			$activity = sprintf($activity, "Account settings");
			
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
			
			$msg = $this->lang->line('data_updated');
			
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
			"msg" => $msg
		);
	
		echo json_encode($info);
	
	}
}