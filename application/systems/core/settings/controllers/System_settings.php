<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class System_settings extends SYSAD_Controller 
{
	private $module;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_SYSTEM_SETTINGS;
		
		$this->load->model('site_settings_model', 'settings', TRUE);
	}

	public function index()
	{
		$sms_apis 		= array();

		try
		{
			$resources = array();
/*
			$term_cond_arr 	= json_encode(array("id" =>"terms_conditions", "path" => PATH_TERM_CONDITIONS_UPLOADS, "multiple" => true, 'special' => true));*/

			$module_js = HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_SETTINGS."/settings";

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_SELECTIZE, CSS_UPLOAD);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_SELECTIZE, JS_UPLOAD, $module_js);

			$email_where 			= array();

			$email_where["fields"] 		= array("sys_param_name", "sys_param_value");
			$email_where["where"] 		= array("sys_param_type" => SYS_PARAM_SMTP);
			$email_where["multiple"] 	= TRUE;
			$email_params = get_values("sys_param_model", "get_sys_param", $email_where, CORE_COMMON);

			$email_data 	= array();

			if( !EMPTY( $email_params ) )
			{
				foreach( $email_params as $e_k => $e_v )
				{
					$low_ek 		= strtolower($e_v['sys_param_name']);
					$check_setting 	= get_setting('SMTP', $low_ek);
					
					if( !EMPTY( $check_setting ) AND $check_setting != " " )
					{
						$value 		= $check_setting;
					}
					else
					{
						$value 		= $e_v['sys_param_value'];
					}

					$email_data[$low_ek]	= $value;
				}
			}
			
			$resources['loaded_init'] = array(
				'Settings.init_sys_setting();',
				'Settings.save_sys_settings();'
			);
			
			$permission 		= $this->permission->check_permission($this->module, ACTION_SAVE);
			$sms_apis 			= unserialize(SMS_APIS);

			$data 				= array(
				'permission'	=> $permission,
				'email_data'	=> $email_data,
				'sms_apis'		=> $sms_apis
			);

			$resources['upload'] = array(
				'cert_file' 			=> array(
					'path' 					=> PATH_CERTIFICATION_FILE_UPLOADS, 
					'allowed_types' 		=> 'crt,pem',
					'show_preview'			=> true,
					'max_file' 				=> 1,
					"multiple"  			=> true,
					// 'drag_drop'				=> true,
					'max_file_size'			=> '13107200',
					/*'successCallback'		=> 'Settings.successCallbackTerm('.$term_cond_arr.', data, files);',
					'deleteCallback'		=> 'Settings.deleteCallbackTerm('.$term_cond_arr.', data);',*/
					// 'special' 				=> true
				),
			);

			$this->load->view('tabs/system_settings', $data);
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

	private function _validate( array $params )
	{
		try
		{
			$v 	= $this->core_v;

			$email_where["fields"] 		= array("sys_param_name", "sys_param_value");
			$email_where["where"] 		= array("sys_param_type" => SYS_PARAM_SMTP);
			$email_where["multiple"] 	= TRUE;
			$email_params = get_values("sys_param_model", "get_sys_param", $email_where, CORE_COMMON);
			$email_data 	= array();

			if( !EMPTY( $email_params ) )
			{
				foreach( $email_params as $e_k => $e_v )
				{
					$low_ek 		= strtolower($e_v['sys_param_name']);
					$value 			= $e_v['sys_param_value'];

					$v->required()
						->check($low_ek, $params);
				}

			}

			$sms_apis 	= unserialize(SMS_APIS);

			$v
				->in(array_keys($sms_apis))->sometimes()
				->check('sms_api', $params);

			$v->assert(FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
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

			$params = $this->set_filter($params)
						->filter_string('sms_api', TRUE)
						->filter();
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$fields = $this->settings->get_site_settings(SYSTEM_SETTINGS);

			$this->_validate($params);

			$email_where["fields"] 		= array("sys_param_name", "sys_param_value");
			$email_where["where"] 		= array("sys_param_type" => SYS_PARAM_SMTP);
			$email_where["multiple"] 	= TRUE;
			$email_params = get_values("sys_param_model", "get_sys_param", $email_where, CORE_COMMON);
			$email_data 	= array();

			if( !EMPTY( $email_params ) )
			{
				foreach( $email_params as $e_k => $e_v )
				{
					$low_ek 		= strtolower($e_v['sys_param_name']);
					$value 			= $e_v['sys_param_value'];

					$email_data[$low_ek]	= $value;
				}

			}

			
			foreach($fields as $field):
				$audit_action[]	= AUDIT_UPDATE;
				$audit_table[]	= SYSAD_Model::CORE_TABLE_SITE_SETTINGS;
				$audit_schema[]	= DB_CORE;
			
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			  	$prev_detail[] = array( $this->settings->get_site_settings(SYSTEM_SETTINGS, $field['setting_type'], $field['setting_name']) );
			  	$this->settings->update_settings($field['setting_type'], $params, $field['setting_name']);
					 
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_detail[] = array( $this->settings->get_site_settings(SYSTEM_SETTINGS, $field['setting_type'], $field['setting_name']) );

				if( ISSET( $email_data[$field['setting_name']] ) )
				{
					if( ISSET( $params[$field['setting_name']] ) AND !EMPTY($params[$field['setting_name']]) )
					{
						$upd_val = array(
							'sys_param_value'	=> $params[$field['setting_name']]
						);

						$upd_where 	= array(
							'sys_param_type' => SYS_PARAM_SMTP,
							'sys_param_name' => strtoupper($field['setting_name'])
						);

						$this->settings->update_helper(SYSAD_Model::CORE_TABLE_SYS_PARAM, $upd_val, $upd_where);
					}
				}
			endforeach;

			$fields_system = $this->settings->get_site_settings('NOTIFICATION_CRON');

			if( !EMPTY( $fields_system ) )
			{
				foreach($fields_system as $field)
				{
					$audit_action[]	= AUDIT_UPDATE;
					$audit_table[]	= SYSAD_Model::CORE_TABLE_SITE_SETTINGS;
					$audit_schema[]	= DB_CORE;
				
					// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
				  	$prev_detail[] = array( $this->settings->get_site_settings('NOTIFICATION_CRON', $field['setting_type'], $field['setting_name']) );
				  	$this->settings->update_settings($field['setting_type'], $params, $field['setting_name']);
						 
					// GET THE DETAIL AFTER UPDATING THE RECORD
					$curr_detail[] = array( $this->settings->get_site_settings('NOTIFICATION_CRON', $field['setting_type'], $field['setting_name']) );				
				}
			}
			
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