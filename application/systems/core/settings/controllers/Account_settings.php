<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account_settings extends SYSAD_Controller {

	private $module;
	private $login_with_arr = array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_AUTH_SETTINGS;
		
		$this->load->model('site_settings_model', 'settings', TRUE);

		$this->login_with_arr = unserialize(LOGIN_WITH_ARR);
	}
	
	public function index()
	{
		$auth_login_factors = array();
		$auth_account_factors = array();
		$auth_password_factors = array();

		try
		{
			// $this->redirect_off_system($this->module);
			$resources = array();
			
			$module_js = HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_SETTINGS."/settings";
			
			$account_creator = strtolower(get_setting(ACCOUNT, "account_creator"));
			$login_via = strtolower(get_setting(LOGIN, "login_via"));
			$password_expiry = get_setting(PASSWORD_EXPIRY, "password_expiry");
			$password_initial_set = strtolower(get_setting(PASSWORD_INITIAL_SET, "password_creator"));

			$username_initial_set = strtolower(get_setting('USERNAME_CREATE', "username_creator"));
			
			$resources['load_css'] = array(CSS_LABELAUTY, CSS_SELECTIZE);
			$resources['load_js'] = array(JS_LABELAUTY, JS_NUMBER, JS_SELECTIZE, $module_js);
			$resources['loaded_init'] = array(
					'Settings.initAccountSettings("'.$account_creator.'", "'.$login_via.'", "'.$password_expiry.'", "'.$password_initial_set.'", "'.$username_initial_set.'");',
				'Settings.saveAccountSettings()'
			);

			$permission 		= $this->permission->check_permission($this->module, ACTION_SAVE);

			$auth_login_factors 		= $this->settings->get_authentication_sections_factors(AUTH_SECTION_LOGIN);

			$auth_account_factors 		= $this->settings->get_authentication_sections_factors(AUTH_SECTION_ACCOUNT);

			$auth_password_factors 		= $this->settings->get_authentication_sections_factors(AUTH_SECTION_PASSWORD);

			$login_sys_param 	= get_sys_param_val('LOGIN', 'LOGIN_WITH');

			$with_ip_blacklist 	= get_sys_param_val('IP_ADDRESS', 'IP_BLACKLIST');

			$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

			$ch_with_ip_blacklist 	= ( !EMPTY( $with_ip_blacklist ) AND !EMPTY( $with_ip_blacklist['sys_param_value'] ) ) ? TRUE : FALSE;

			$roles 		= $this->settings->get_roles();

			$data 				= array(
				'permission'	=> $permission,
				'auth_login_factors'	=> $auth_login_factors,
				'auth_account_factors'	=> $auth_account_factors,
				'auth_password_factors'	=> $auth_password_factors,
				'login_with_arr' 		=> $this->login_with_arr,
				'ch_login_sys_param' 	=> $ch_login_sys_param,
				'ch_with_ip_blacklist'  => $ch_with_ip_blacklist,
				'roles'			=> $roles
			);

			
			$this->load->view('tabs/account_settings', $data);
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

	private function _validate(array $params)
	{
		try
		{
			$low_length = ( EMPTY($params['constraint_lowercase'] ) ) ? 0 : intval($params['constraint_lowercase']);
			$upp_length = ( EMPTY($params['constraint_uppercase'] ) ) ? 0 : intval($params['constraint_uppercase']);
			$lett_length = ( EMPTY($params['constraint_letter'] ) ) ? 0 : intval($params['constraint_letter']);
			$dig_length = ( EMPTY($params['constraint_digit'] ) ) ? 0 : intval($params['constraint_digit']);
			$spec_length = ( EMPTY($params['constraint_special_character'] ) ) ? 0 : intval($params['constraint_special_character']);
			$pass_length = ( EMPTY($params['constraint_length'] ) ) ? 0 : intval($params['constraint_length']);

			$total_lett_len = $low_length + $upp_length;

			$total_length = $low_length + $upp_length + $lett_length + $dig_length + $spec_length;

			/*if( $total_lett_len > $lett_length )
			{
				throw new Exception('Lowercase Length and Uppercase length must not be greater than Letter length.');	
			}*/

			if( $total_length > $pass_length )
			{
				throw new Exception('Lowercase Length, Uppercase length, Letter length, Special Character length, Digit length must not be greater than Password length.');
			}

			if( ISSET( $params['apply_username_constraints'] ) )
			{
				$us_digit_length = ( EMPTY($params['constraint_username_digit'] ) ) ? 0 : intval($params['constraint_username_digit']);
				$us_letter_length = ( EMPTY($params['constraint_username_letter'] ) ) ? 0 : intval($params['constraint_username_letter']);
				$us_min_length 	= ( EMPTY($params['constraint_username_min_length'] ) ) ? 0 : intval($params['constraint_username_min_length']);
				$us_max_length 	= ( EMPTY($params['constraint_username_max_length'] ) ) ? 0 : intval($params['constraint_username_max_length']);

				$tota_use_length	= $us_digit_length + $us_letter_length;

				if( $tota_use_length > $us_max_length )
				{
					throw new Exception('Username Letter, Digit length must not be greater than Username Maximum Length.');		
				}
			}

			$v 	= $this->core_v;

			$v 
				->exist(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTORS.'|primary_id=authentication_factor_id')
					->sometimes()
				->check('auth_login_factor', $params);

			$v 
				->required()->sometimes(function() use(&$params)
				{
					return ( !EMPTY( $params['auth_login_factor'] ) AND !EMPTY( $params['auth_login_factor'][0] ) );
				})
				->is_numeric()->sometimes()
				->check('auth_login_code_decay|Code Decay', $params);

			$v 
				->exist(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTORS.'|primary_id=authentication_factor_id')
					->sometimes()
				->check('auth_password_factor', $params);

			$v 
				->required()->sometimes(function() use(&$params)
				{
					return ( !EMPTY( $params['auth_password_factor'] ) AND !EMPTY( $params['auth_password_factor'][0] ) );
				})
				->is_numeric()->sometimes()
				->check('auth_password_code_decay|Code Decay', $params);

			if( ISSET($params['account_creator']) AND 
				( $params['account_creator'] == VISITOR OR $params['account_creator'] == VISITOR_NOT_APPROVAL )

			)
			{

				$v 
					->exist(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTORS.'|primary_id=authentication_factor_id')
						->sometimes()
					->check('auth_account_factor', $params);

				$v 
					->required()->sometimes(function() use(&$params)
					{
						return ( !EMPTY( $params['auth_account_factor'] ) AND !EMPTY( $params['auth_account_factor'][0] ) );
					})
					->is_numeric()->sometimes()
					->check('auth_account_code_decay|Code Decay', $params);
			}

			if( ISSET( $params['login_api'][0] ) AND !EMPTY( $params['login_api'][0] ) )
			{
				$v 
					->in(array_keys($this->login_with_arr))->sometimes()
					->check('login_api|Login With', $params);
			}

			$with_ip_blacklist 	= get_sys_param_val('IP_ADDRESS', 'IP_BLACKLIST');
			$ch_with_ip_blacklist 	= ( !EMPTY( $with_ip_blacklist ) AND !EMPTY( $with_ip_blacklist['sys_param_value'] ) ) ? TRUE : FALSE;

			if( $ch_with_ip_blacklist )
			{
				if( ISSET( $params['ip_blacklist'][0] ) AND !EMPTY( $params['ip_blacklist'][0] ) )
				{
					$v 
						->required()
						->check('ip_blacklist|IP List', $params);
				}
			}

			if( ISSET( $params['role_override'][0] ) AND !EMPTY( $params['role_override'][0] ) )
			{
				$v 
					->exist(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ROLES.'|primary_id=role_code')
						->sometimes()
					->check('role_override|Role', $params);
			}

			if( ISSET( $params['login_attempt_soft'] ) AND !EMPTY( $params['login_attempt_soft'] ) )
			{
				if( EMPTY( $params['login_attempt_soft_sec'] ) )
				{
					throw new Exception('Number of seconds a user is soft blocked is required.');
				}
				else 
				{
					if( $params['login_attempt_soft_sec'] < 10 )
					{
						throw new Exception('Number of seconds must be equal to or higher than 10.');
					}
				}
			}

		/*	if( ISSET( $params['enable_multi_auth_factor'] ) )
			{
				$v
					->required()
						->sometimes()
					->exist(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTORS.'|primary_id=authentication_factor_id')
						->sometimes()
					->check('authentication_factor', $params);

				$v 
					->required()
					->is_numeric()
					->check('auth_code_decay|Code Decay', $params);
			}*/

			$v->assert(FALSE);

		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch( Exception $e )
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
			
			$params = $this->set_filter($params)
						->filter_number('authentication_factor', TRUE)
						->filter_number('auth_login_factor', TRUE)
						->filter_number('auth_account_factor', TRUE)
						->filter_number('auth_password_factor', TRUE)
						->filter_string('login_api', TRUE)
						->filter_string('role_override', TRUE)
						->filter();


			$action = AUDIT_UPDATE;
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$fields = $this->settings->get_site_settings(AUTHENTICATION);

			$this->_validate($params);
			
			foreach($fields as $field):
				$audit_action[]	= AUDIT_UPDATE;
				$audit_table[]	= SYSAD_Model::CORE_TABLE_SITE_SETTINGS;
				$audit_schema[]	= DB_CORE;
			
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			  	$prev_detail[] = array( $this->settings->get_site_settings(AUTHENTICATION, $field['setting_type'], $field['setting_name']) );
			  	$this->settings->update_settings($field['setting_type'], $params, $field['setting_name']);
					 
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_detail[] = array( $this->settings->get_site_settings(AUTHENTICATION, $field['setting_type'], $field['setting_name']) );
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