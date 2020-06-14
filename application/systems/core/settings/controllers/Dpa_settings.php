<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dpa_settings extends SYSAD_Controller 
{
	private $module;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_DATA_PRIVACY_SETTING;
		
		$this->load->model('site_settings_model', 'settings', TRUE);
	}

	public function index()
	{
		try
		{

			$resources = array();

			$term_cond_arr 	= json_encode(array("id" =>"terms_conditions", "path" => PATH_TERM_CONDITIONS_UPLOADS, "multiple" => true, 'special' => true));

			$module_js = HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_SETTINGS."/settings";

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_SELECTIZE, CSS_UPLOAD);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_SELECTIZE, JS_UPLOAD, JS_EDITOR, $module_js);

			$resources['upload'] = array(
				'terms_conditions' 			=> array(
					'path' 					=> PATH_TERM_CONDITIONS_UPLOADS, 
					'allowed_types' 		=> 'pdf,doc,docx,xls,xlsx,csv,ppt,pptx',
					'show_preview'			=> true,
					'max_file' 				=> 5,
					"multiple"  			=> true,
					// 'drag_drop'				=> true,
					'max_file_size'			=> '13107200',
					'successCallback'		=> 'Settings.successCallbackTerm('.$term_cond_arr.', data, files);',
					'deleteCallback'		=> 'Settings.deleteCallbackTerm('.$term_cond_arr.', data);',
					'special' 				=> true
				),
			);

			$resources['loaded_init'] = array(
				'Settings.init_dpa_setting();',
				'Settings.save_dpa_settings();'
			);

			$get_statements 		= $this->settings->get_statements(STATEMENT_MODULE_TYPE_DPA);

			$permission 		= $this->permission->check_permission($this->module, ACTION_SAVE);

			$data 				= array(
				'permission'	=> $permission,
				'statements'	=> $get_statements
			);

			$this->load->view('tabs/dpa_settings', $data);
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

			if( ISSET( $params['dpa_enable'] ) )
			{
				if( !ISSET( $params['has_agreement_text'] ) OR EMPTY( $params['has_agreement_text'] ) )
				{
					throw new Exception('Please choose a Data Privacy Type.');
				}
				else
				{
					if( $params['has_agreement_text'] == DATA_PRIVACY_TYPE_BASIC )
					{
						$v 
							->required()
							->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_STATEMENTS.'|primary_id=statement_id')->sometimes()
							->check('agremment_text|Statement', $params);
					}
					else if( $params['has_agreement_text'] == DATA_PRIVACY_TYPE_STRICT )
					{
						if( !ISSET( $params['dpa_strict_mode'] ) OR EMPTY( $params['dpa_strict_mode'] ) )
						{
							throw new Exception('Please choose a Data Privacy Strict Type Mode.');
						}
					}
				}
			}

			if( ISSET( $params['dpa_email_enable'] ) )
			{
				$v
					->required()
					->check('email_domain', $params);
			}

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
						->filter_number('agremment_text', TRUE)
						->filter();
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$fields = $this->settings->get_site_settings(DPA_LOCATION);

			$this->_validate($params);

			$dpa_encryption = $this->settings->get_site_settings(DPA_LOCATION, DPA_SETTING, 'encryption');
			
			foreach($fields as $field):
				$audit_action[]	= AUDIT_UPDATE;
				$audit_table[]	= SYSAD_Model::CORE_TABLE_SITE_SETTINGS;
				$audit_schema[]	= DB_CORE;
			
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			  	$prev_detail[] = array( $this->settings->get_site_settings(DPA_LOCATION, $field['setting_type'], $field['setting_name']) );
			  	$this->settings->update_settings($field['setting_type'], $params, $field['setting_name']);
					 
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_detail[] = array( $this->settings->get_site_settings(DPA_LOCATION, $field['setting_type'], $field['setting_name']) );
			endforeach;

			$param_encryption = ( ISSET( $params['encryption'] ) ) ? '1' : '0';
			
			if( $param_encryption != $dpa_encryption['setting_value'] )
			{
				$columns 	= $this->settings->get_all_varbinary_column();
			
				$this->settings->update_encrypt_decrypt($columns);	
			}



			/**/
			
			
			
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