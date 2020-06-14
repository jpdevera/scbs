<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow_settings extends SYSAD_Controller 
{
	private $module;
	private $module_js;

	public function __construct()
	{
		parent::__construct();
		
		$this->module 		= MODULE_WORKFLOW;
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/workflow_settings";

		$this->load->model(CORE_SETTINGS.'/Site_settings_model', 'settings');
	}

	public function index( $hash, $salt, $token )
	{
		$data 			= array();
		$resources 		= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module, ACTION_SETTINGS);

			$resources['load_css'] 	= array( CSS_LABELAUTY );
			$resources['load_js']	= array( JS_LABELAUTY, $this->module_js );
			$resources['loaded_init'] = array( 'Workflow_settings.init_settings();', 'Workflow_settings.save_settings();' );

			$extra 					= array(
				'order'				=> array( 'sort_order' => 'ASC' )
			);

			$workflow_settings 		= get_settings( WORKFLOW_TAB, $extra );

			$data['workflow_settings']	= $workflow_settings;

			$de_hash 	= base64_url_decode( $hash );

			check_salt( $de_hash, $salt, $token );

			$this->template->load('workflow_settings', $data, $resources);
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
	
	public function save()
	{
		$status = ERROR;
		$params	= get_params();
		$action = AUDIT_UPDATE;

		$msg 	= '';

		try
		{
			// $this->redirect_off_system($this->module);
			
			$worfklow = $this->settings->get_site_settings(WORKFLOW_LOCATION);

			SYSAD_Model::beginTransaction();

			foreach($worfklow as $worfklow):
				$audit_action[]	= AUDIT_UPDATE;
				$audit_table[]	= SYSAD_Model::CORE_TABLE_SITE_SETTINGS;
				$audit_schema[]	= DB_CORE;
			
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			  	$prev_detail[] = array( $this->settings->get_site_settings(WORKFLOW_LOCATION, $worfklow['setting_type'], $worfklow['setting_name']) );
			  	$this->settings->update_settings($worfklow['setting_type'], $params, $worfklow['setting_name']);
					 
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_detail[] = array( $this->settings->get_site_settings(WORKFLOW_LOCATION, $worfklow['setting_type'], $worfklow['setting_name']) );
			endforeach;

			$activity = "%s has been updated";
			$activity = sprintf($activity, "Workflow settings");
			
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