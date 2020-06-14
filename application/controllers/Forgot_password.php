<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Forgot_password extends SYSAD_Controller 
{
	
	private $module_js;
	private $controller;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->controller 	= strtolower(__CLASS__);
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_COMMON."/".$this->controller;
		
		$this->load->model(CORE_USER_MANAGEMENT . "/users_model", "users", TRUE);

		$this->load->library('Authentication_factors');
	}
	
	public function modal()
	{
		$segments 			= array();
 		$lists 				= array();
 		$class_step 		= '';
 		$pass_data 			= array();
 		$required_mobile 	= FALSE;

 		$client_val_email 	= array();
 		$client_acc_det 	= array();

		try
		{
			$data = $resources = array();

			$lists 			= array(
				'Email Address', 
			);

			$segments 		= array(
				'fw_email_address'
			);

			// 'fw_security_question'
			// 'Security Question'

			$auth_account_factors 	= get_setting(AUTH_FACTOR, 'auth_password_factor');
			$auth_account_factor_arr = array();

			if( !EMPTY( $auth_account_factors ) )
			{
				$auth_account_factor_arr = explode(',', $auth_account_factors);
			}

			/*if( in_array(AUTHENTICATION_FACTOR_EMAIL, $auth_account_factor_arr) )
			{*/
				$lists[] 	= 'Means of Verification';
				$segments[] = 'fw_email_verification';
			// }

			if( in_array(AUTHENTICATION_FACTOR_SMS, $auth_account_factor_arr) )
			{
				/*$lists[] 	= 'Mobile Verification Code';
				$segments[] = 'fw_mobile_verification';*/
				$required_mobile = TRUE;
			}

			$lists[] 		= 'Set New Password';
			$segments[] 	= 'fw_set_new_password';

			$class_step 				= trim( convertNumberToWord( count( $lists ) ) ).'-step';
			
			$client_val_email 			= $this->validate_email(array(), TRUE);
			$client_acc_det 			= $this->validate_account_details( array(), TRUE );

			$client_acc 				= $client_acc_det['clientSideAjd'];
			$cons_array 				= $client_acc_det['cons_array'];
			$user_cons_arr 				= $client_acc_det['user_cons_arr'];

			$resources['load_css'] 		= array( 'custom', CSS_WIZARD);
			$resources['load_js'] 		= array(JS_WIZARD, $this->module_js);
			$resources['loaded_init'] 	= array(
				'Wizard.init();', 
				$client_val_email['customJS'], $client_acc['customJS'], 'ForgotPw.save();'
			);

			if( !EMPTY( $cons_array ) )
			{
				$resources['loaded_init'][] = 'password_constraints( '.$cons_array.' );';
			}

			$data['lists']					= $lists;
			$data['class_step']				= $class_step;
			$data['segments']				= $segments;

			$data['orig_params'] 			= array(
				'user_id'	=> '',
				'salt'		=> '',
				'token'		=> '',
				'action'	=> ''
			);
			$data['params'] 				= $data['orig_params'];
			$data['client_val_email'] 		= $client_val_email;
			$data['required_mobile'] 		= $required_mobile;
			$data['client_acc'] 			= $client_acc;

			$data['pass_data']				= $data;
			
			$this->load->view("modals/forgot_password_new", $data);
			$this->load_resources->get_resource($resources);
		}
		catch( PDOException $e )
		{
			echo $this->get_user_message($e);
		}
		catch( Exception $e )
		{
			echo $this->rlog_error($e, TRUE);
		}
	}

	private function _filter_sign_up( array $orig_params )
 	{
 		$par 			= $this->set_filter( $orig_params )
 							->filter_email('email')
							->filter_number('security_question_id', TRUE)
							->filter_string('us_answ', TRUE)
							->filter_string('security_question_answers')
							->filter_string('sec_text', TRUE)
							->filter_number('user_id', TRUE)
							->filter_string('sub_tab');

		$params 		= $par->filter();
		
		return $params;
 	}

 	public function validate_post_email()
 	{
 		$valid 	= FALSE;
 		$msg 	= "";

 		try
 		{
 			$orig_params 	= get_params();
 			$params 		= $this->_filter_sign_up($orig_params);
 			
 			$this->validate_email($params);

 			$valid 			= true;
 		}
 		catch( PDOException $e )
 		{
 			$msg 	= $this->get_user_message($e);
 			// throw $e;
 		}
 		catch( Exception $e )
 		{
 			$msg 	= $e->getMessage();
 		}

 		$response 	= array(
 			'valid'	=> $valid,
 			'msg'	=> $msg
 		);

 		echo json_encode($response);
 	}

 	public function verification($authentication_factor_id, $user_id, $data_page_key, $security_questions = FALSE)
 	{
 		$data = $resources = array();

 		$configs 						= array();
 		$client_vc 	= array();
 		$auth_data 	= array();

 		$page 		= 'verification';
 		$no_next 	= FALSE;

 		$security_question_view 		= '';

 		try
		{
			$auth_fac_id_dec 			= base64_url_decode($authentication_factor_id);
			$user_id_dec 				= base64_url_decode($user_id);

			$user_details 				= $this->users->get_user_details($user_id_dec);

			$configs 					= $this->authentication_factors->auth_factor_config($auth_fac_id_dec);

			$client_vc 					= $this->validate_verify_code(array(), TRUE);

			$module_js 					= HMVC_FOLDER."/multi_auth";

			$resources['load_js']		= array($this->module_js, $module_js);
			$resources['loaded_init']	= array($client_vc['customJS']);

			$check_multi_auth_enable 	= $this->authentication_factors->check_authentication_factor_section_enabled(AUTH_SECTION_PASSWORD);

			if( !$security_questions )
			{

				if( EMPTY( $check_multi_auth_enable ) )
				{
					$check_multi_auth_enable = TRUE;
				}

				if( $check_multi_auth_enable )
				{
					$auth_factors 			= get_setting_authentication_factor(AUTH_SECTION_PASSWORD);

					$multi_auth 		= $this->users->get_user_multi_auth($user_id_dec);

					SYSAD_Model::beginTransaction();

					if( EMPTY( $multi_auth ) )
					{
						$this->authentication_factors->save_multi_auth_section_helper($user_id_dec, SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, AUTH_SECTION_PASSWORD);
					}

					$auth_factors[] 	= AUTHENTICATION_FACTOR_EMAIL;

					if( in_array($auth_fac_id_dec, $auth_factors) )
					{
						$check_by_auth 		= $this->users->get_user_multi_auth($user_id_dec, $auth_fac_id_dec);

						if( EMPTY( $check_by_auth ) )
						{
							$this->users->insert_helper(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, array(
								'user_id'	=> $user_id_dec,
								'authentication_factor_id' => $auth_fac_id_dec
							));
						}

						$get_auth 			= $this->users->get_user_multi_auth($user_id_dec, $auth_fac_id_dec);
						
						if( !EMPTY( $get_auth ) AND !EMPTY( $get_auth[0] ) )
						{
							$auth_data 	= array(
								'user_id'		=> base64_url_encode($get_auth[0]['user_id']),
								'authentication_factor_id' => base64_url_encode($get_auth[0]['authentication_factor_id'])
							);

							$auth_params 					= $this->authentication_factors->filter_verify_code($auth_data);

							$this->authentication_factors->validate_vc($auth_params);

							$configs 					= $this->authentication_factors->auth_factor_config($auth_params['authentication_factor_id']);

							$auth_params['generate_token'] 	= 1;

							$this->authentication_factors->update_multi_auth(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $auth_params, AUTH_SECTION_PASSWORD);
						}
					}

					SYSAD_Model::commit();
				}
			}
			else
			{

				$security_questions 	= $this->users->get_security_questions_with_answer($user_id_dec);
							
				if( !EMPTY( $security_questions ) )
				{
					$security_question_view = $this->load->view('tabs/security_question_list', array('security_questions' => $security_questions, 'req_pass' => 'true', 'data_page_key' => $data_page_key ), TRUE);
				}
				else
				{
					$security_question_view = $this->load->view('tabs/security_question_list', array('security_questions' => array(), 'req_pass' => 'true', 'data_page_key' => $data_page_key ), TRUE);
				}

				$page 		= 'fw_security_question';
				$no_next 	= TRUE;
			}

			$resources['loaded_init'][] = "Multi_auth.resend('', ".json_encode($auth_data).");";
		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();
			$msg 	= $this->get_user_message($e);
			
			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			SYSAD_Model::rollback();
			$msg 	= $this->rlog_error($e, TRUE);
			
			$this->error_index( $msg );
		}

		$data['configs'] 		= $configs;
		$data['data_page_key'] 	= $data_page_key;
		$data['client_vc']		= $client_vc;
		$data['auth_data']		= $auth_data;
		$data['no_next'] 		= $no_next;
		$data['security_question_view'] = $security_question_view;

		$this->load->view('tabs/'.$page, $data);
		$this->load_resources->get_resource($resources);
 	}

 	public function validate_verify_code(array $params = array(), $forJs = FALSE)
 	{
 		try
 		{
 			$v 	= $this->core_v;

 			$v 
 				->required(NULL, '#client_verification_code')->sometimes('sometimes', NULL, $forJs)
 				->check('verification_code', $params); 			

 		
    		if( $forJs )
			{
				return $v->getClientSide();
			}
			else
			{
				$v->assert(FALSE);
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

 	public function validate_account_details(array $params, $forJs = FALSE, $account_creator = NULL)
 	{
 		$cons_array 			= '';
 		$user_cons_arr 			= '';

 		try
 		{
 			if( $forJs )
 			{
	 			$pass_const 		= $this->users->get_settings_arr(PASSWORD_CONSTRAINTS);
				$user_const 		= $this->users->get_settings_arr(USERNAME_CONSTRAINTS);

				$user_has_cons 		= get_setting( USERNAME, 'apply_username_constraints' );

				$pass_err 			= $this->get_pass_error_msg();
				$pass_length 		= $pass_const[PASS_CONS_LENGTH];
				$upper_length 		= $pass_const[PASS_CONS_UPPERCASE];
				$digit_length 		= $pass_const[PASS_CONS_DIGIT];
				$repeat_pass 		= $pass_const[PASS_CONS_REPEATING];
				$letter_length 		= $pass_const['constraint_letter'];
				$spec_length 			= $pass_const['constraint_special_character'];

				$user_err 			= $this->get_username_error_msg();

				$user_min 			= $user_const[USERNAME_MIN_LENGTH];
				$user_max 			= $user_const[USERNAME_MAX_LENGTH];
				$user_dig 			= $user_const[USERNAME_DIGIT];
				$user_lett 			= $user_const['constraint_username_letter'];

				$user_cons_arr 		= array(
					'user_has_cons'		=> $user_has_cons,
					'user_min_length' 	=> $user_min,
					'user_max_length'	=> $user_max,
					'user_digit_length'	=> $user_dig,
					'user_err'			=> $user_err,
					'user_lett_length' 	=> $user_lett
				);

				$user_cons_arr 		= json_encode( $user_cons_arr );

				$cons_array = array(
					'pass_err'		=> $pass_err,
					'pass_length'	=> $pass_length,
					'upper_length'	=> $upper_length,
					'digit_length'	=> $digit_length,
					'repeat_pass'	=> $repeat_pass,
					'pass_same'		=> 0,
					'spec_length' 	=> $spec_length,
					'letter_length' 	=> $letter_length
				);

				$cons_array 		= json_encode( $cons_array );
			}

 			$v 	= $this->core_v;

 			$v 
 				->required(NULL, '#client_password')
 				->check('password', $params);

 			$v 
 				->required(NULL, '#client_confirm_password')
 				->key_value(array('confirm_password', 'equals', 'password'))
 				->checkArr('confirm_password', $params, array(), FALSE);

 			if( $forJs )
			{
				return array(
					'clientSideAjd' => $v->getClientSide(),
					'cons_array'	=> $cons_array,
					'user_cons_arr'	=> $user_cons_arr
				);
			}
			else
			{
				$v->assert(FALSE);
			}

 			if( !$forJs )
 			{
 				$this->_validate_user_account($params, $account_creator);
 			}
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

 	private function _validate_user_account( array $params, $account_creator = NULL )
	{
		try
		{
			if(!EMPTY($params['password']))
			{
				$this->users->check_password_history($params['user_id'], $params['password']);

			/*	if( EMPTY( preg_match('/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\s]+$/', $params['password'] ) ) )
				{
					throw new Exception("Password contains an illegal character.");
				}*/
			}
			else
			{
				throw new Exception(sprintf($this->lang->line('is_required'), "Password"));
			}

			if( !EMPTY( $params['password'] ) )
			{
				$user_details 	= array();
				$username 		= NULL;

				if( ISSET( $params['user_id'] ) )
				{
					$user_details 	= $this->users->get_user_details($params['user_id']);

					if( !EMPTY( $user_details ) )
					{
						$username = $user_details['username'];
					}
				}
							
				$check_password = $this->validate_password( $params['password'], $username );

				if( $check_password !== TRUE )
				{
					throw new Exception($check_password);
				}
			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}
		/*if( !EMPTY( $account_creator ) AND $account_creator == VISITOR_NOT_APPROVAL )
		{}*/
	}

 	public function validate_email(array $params = array(), $forJs = FALSE)
 	{
 		try
 		{
 			$v 	= $this->core_v;

 			$email = 'CAST('.aes_crypt('email', FALSE, FALSE).' AS char(100))';

 			$v 
 				->required(NULL, '#client_email')
 				->email(NULL, '#client_email')->sometimes('sometimes', NULL, $forJs)
 				->blacklist_email_domain()
 				->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$email, '@custom_error_Sorry, we could not find your account..')->sometimes('sometimes', NULL, $forJs)
 				->check('email', $params);

 			/*$v 	
 				->required()
 				->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_SECURITY_QUESTIONS.'|primary_id=security_question_id')
 				->check('security_question_id', $params);*/


    		if( $forJs )
			{
				return $v->getClientSide();
			}
			else
			{
				$v->assert(FALSE);
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
	
	public function reset_password_link($id, $key)
	{
		try
		{
			$data = $resources = array();

			$data['id'] 	= $id;
			$data['key'] 	= $key;
			
			$pass_const 		= $this->users->get_settings_arr(PASSWORD_CONSTRAINTS);
			$pass_err 			= $this->get_pass_error_msg();
			$pass_length 		= $pass_const[PASS_CONS_LENGTH];
			$upper_length 		= $pass_const[PASS_CONS_UPPERCASE];
			$digit_length 		= $pass_const[PASS_CONS_DIGIT];
			$repeat_pass 		= $pass_const[PASS_CONS_REPEATING];
			$letter_length 		= $pass_const['constraint_letter'];
			$spec_length 			= $pass_const['constraint_special_character'];

			$cons_array 		= array(
				'pass_err'		=> $pass_err,
				'pass_length'	=> $pass_length,
				'upper_length'	=> $upper_length,
				'digit_length'	=> $digit_length,
				'repeat_pass'	=> $repeat_pass,
				'pass_same'		=> 0,
				'letter_length'	=> $letter_length,
				'spec_length'	=> $spec_length
			);

			$cons_array 		= json_encode( $cons_array );
		
		
			$resources['load_js'] = array($this->module_js);	
			$resources['loaded_init'] = array(
				'password_constraints( '.$cons_array.' );',
				'ForgotPw.initResetModal("'.$pass_length.'", "'.$pass_length.'", "'.$upper_length.'", "'.$digit_length.'", "'.$pass_err.'");',
				'ForgotPw.saveReset();'
			);
		}
		catch( PDOException $e )
		{
			echo $this->get_user_message($e);
		}
		catch( Exception $e )
		{
			echo $this->rlog_error($e, TRUE);
		}
			
		$this->load->view("modals/reset_password", $data);
		$this->load_resources->get_resource($resources);
	}
	
	public function request_reset()
	{
		$flag 	= 0;
		$msg 	= "";
		
		try
		{
			$status = ERROR;
			$orig_params	= get_params();
			//$email = filter_var($params['email'], FILTER_SANITIZE_EMAIL);
			// $email 		= $params['email'];
	
			$params = $this->set_filter( $orig_params )
						->filter_string( 'email' )
						->filter();
				
			$val 	= $this->_validate( $params, $orig_params );
			$email 	= $val['email'];
			
			if(EMPTY($email)) throw new Exception($this->lang->line('email_required'));

			$salt 		= gen_salt(TRUE);
	
			$user_info 	= $this->auth_model->get_active_user($email, BY_EMAIL, TRUE);
			
			if(EMPTY($user_info)) throw new Exception($this->lang->line('contact_admin'));

			$allowed_status 	= array(
				STATUS_ACTIVE
			);

			if( !in_array( $user_info['status'], $allowed_status ) )
			{
				throw new Exception($this->lang->line('contact_admin'));
			}
	
			$username 	= $user_info['username'];
	
			// SEND RESET PASSWORD INSTRUCTION
			$this->_send_reset_password($username, $email, $salt);
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$this->auth_model->update_reset_salt($salt, $username);
			
			SYSAD_Model::commit();
	
			$status = SUCCESS;
			$msg = $this->lang->line('reset_password');
	
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
	
		$result 		= array(
			"status" 	=> $status,
			"msg" 		=> $msg
		);
	
		echo json_encode($result);
	}
	
	
	private function _send_reset_password($username, $email, $salt){
	
		try
		{
			$email_data 	= array();
			$template_data 	= array();
	
			
			$system_title 	= get_setting(GENERAL, "system_title");
				
			// required parameters for the email template library
			$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"]	 	= array($email);
			$email_subject 				= 'Reset Password';
			$email_data["subject"] 		= $email_subject;
				
			// additional set of data that will be used by a specific template
			$sys_logo 		 			= get_setting(GENERAL, "system_logo");
			$system_logo_src 			= base_url() . PATH_IMAGES . "logo_white.png";

			if( !EMPTY( $sys_logo ) )
			{
				$root_path 			= $this->get_root_path();

				$sys_logo_path 		= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
				$sys_logo_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

				if( file_exists( $sys_logo_path ) )
				{
					$system_logo_src = output_image($sys_logo, PATH_SETTINGS_UPLOADS);

					$system_logo_src = getimagesize($sys_logo_path) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
				}
			}

			$template_data["logo"] 	= $system_logo_src;
			
			$template_data["email_subject"] = $email_subject;
			$template_data["email"] 		= $email;
			$template_data["system_name"] 	= $system_title;
			$template_data["username"] 		= $username;
			$template_data["salt"] 			= $salt;
				
			$this->email_template->send_email_template($email_data, "emails/reset_password", $template_data);
	
		}
		catch(PDOException $e)
		{			
			$msg = $this->rlog_error($e, TRUE);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
		}
	}
	
	
	public function reset($email, $salt)
	{
	
		$msg 	= "";
		$data 	= array();
		$resources = array();
	
		try
		{
			if(EMPTY($email) OR EMPTY($salt))
			{
				throw new Exception($this->lang->line('invalid_action'));
			}
			
			$email 	= base64_url_decode($email);
			$salt 	= base64_url_decode($salt);
			
			// CHECK IF A USER'S PASSWORD SALT HAS BEEN SUCCESSFULLY RESET THROUGH THE EMAIL RECEIVED
		
			$cnt 	= $this->auth_model->get_reset_salt($email, $salt);
			
			if($cnt == 0) throw new Exception($this->lang->line('invalid_action'));
		
			$id		= in_salt($email, $salt, TRUE);
			$key 	= $salt;

			$resources['load_materialize_modal'] = array (
				'modal_reset_pw' 	=> array (
					'fixed_header' 	=> false,
					'size' 			=> "sm-w lg-h",
					'controller' 	=> $this->controller,
					'modal_footer' 	=> false,
					'method' 		=> "reset_password_link/" . $id . "/" . $key,
					'modal_type' 	=> "open"
				)
			);
			
			$this->load->view('login', $data);
			$this->load_resources->get_resource($resources);
	
		}
		catch(PDOException $e)
		{			
			echo $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			echo $this->rlog_error($e, TRUE);
		}
	
	}

	public function validate_security_question(array $params = array(), $forJs = FALSE)
 	{
 		try
 		{
 			$v 	= $this->core_v;

 			$v 	
 				->required()
 				->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_SECURITY_QUESTIONS.'|primary_id=security_question_id')->sometimes()
 				->check('security_question_id', $params);

 			if( ISSET( $params['us_answ'] ) AND !EMPTY( $params['us_answ'] ) )
 			{
 				foreach( $params['us_answ'] as $key => $us_answ )
 				{
 					$now_answ 	= ( ISSET( $params['security_question_answers'][$key] ) ) ? $params['security_question_answers'][$key] : '';

 					if( !EMPTY( $now_answ ) AND $now_answ != $us_answ )
 					{
 						$sec_text = ( ISSET( $params['sec_text'][$key] ) ) ? $params['sec_text'][$key] : '';

 						throw new Exception('Invalid Answer for Question "'.$sec_text.'".');
 					}
 				}
 			}

    		if( $forJs )
			{
				return $v->getClientSide();
			}
			else
			{
				$v->assert(FALSE);
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
	
	public function process_forgot_password()
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

		$security_questions 	= array();
		$security_question_view = '';

		$user_id 				= NULL;
		$user_id_enc 			= NULL;
		$user_salt 				= NULL;
		$user_token 			= NULL;

		$action 				= NULL;

		$click_li 				= false;
		$close_modal 			= false;

		try
		{
			$params 		 	= $this->_filter_sign_up( $orig_params );

			if( ISSET( $params['user_id'] ) AND !EMPTY( $params['user_id'] ) )
			{
				check_salt($params['user_id'], $params['salt'], $params['token'], $params['action']);

				$user_id 	= $params['user_id'];
			}

			if( ISSET( $params['tab_type'] ) AND !EMPTY( $params['tab_type'] ) )
			{
				switch( $params['tab_type'] )
				{
					case 'email' :
						$this->validate_email($params);

						$user_active 	= $this->auth_model->get_active_user($params['email'], 'email', TRUE);

						if( !EMPTY( $user_active ) )
						{
							/*$security_questions 	= $this->users->get_security_questions_with_answer($user_active['user_id']);
							
							if( !EMPTY( $security_questions ) )
							{
								$security_question_view = $this->load->view('tabs/security_question_list', array('security_questions' => $security_questions, 'req_pass' => 'false', 'data_page_key' => $params['data_page_key'] + 1), TRUE);
								
								$flag 	= 1;
								$msg 	= "Please answer your security question(s).";
								$status = SUCCESS;
							}
							else
							{
								$security_question_view = $this->load->view('tabs/security_question_list', array('security_questions' => array(), 'req_pass' => 'false', 'data_page_key' => $params['data_page_key'] + 1), TRUE);
								
								$flag 	= 1;
								$msg 	= "Please answer your security question(s).";
								$status = SUCCESS;
							}*/

							$flag 	= 1;
							$msg 	= "Please choose means of verification.";
							$status = SUCCESS;

							$user_id 	= $user_active['user_id'];

							$user_salt 	= gen_salt();

							$id_detail 	= array(
								$user_id,
								$user_salt
							);

							$token_edit 	= $this->generate_salt_token_arr( $id_detail, ACTION_EDIT );

							$user_token 	= $token_edit['token_concat'];
							$user_id_enc  	= base64_url_encode($user_id);
							$action 		= ACTION_EDIT;	

							$url_arr 		= array(
								'user_id'	=> $user_id_enc,
								'salt'		=> $user_salt,
								'token'		=> $user_token,
								'action'	=> $action
							);
						}

						$click_li = true;
					break;

					case 'security_question':
						$this->validate_security_question($params);

						$flag 	= 1;
						$status = SUCCESS;
						$msg 	= "Please provide verification code.";

						$click_li = true;
					break;

					case 'verification' :
						if( ISSET( $params['sub_tab'] ) )
						{
							if( $params['sub_tab'] == 'security_question_link' )
							{
								$this->validate_security_question($params);
							}
							else
							{
								$this->validate_verify_code($params);		
							}
						}
						else
						{
							throw new Exception("Please choose a means of verification.");
						}
						
					break;

					case 'password' :
						$this->validate_account_details($params);
					break;
				}
			}

			SYSAD_Model::beginTransaction();

			if( ISSET( $params['tab_type'] ) AND !EMPTY( $params['tab_type'] ) )
			{
				switch( $params['tab_type'] )
				{
					case 'verification':

						if( ISSET( $params['sub_tab'] ) )
						{
							if( $params['sub_tab'] == 'security_question_link' )
							{

							}
							else
							{
								$table 			= SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH;
						
								/*if( !EMPTY( $multi_auth ) )
								{*/
									if(ISSET($params['verification_code']))
									{
										$orig_params['auth_code'] = $params['verification_code'];
									}

									$audit_details 	= $this->authentication_factors->verify_code($table, $orig_params);

									if( !EMPTY( $audit_details['audit_table'] ) )
									{
										$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
										$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
										$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
										$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
										$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );

										$audit_name 				= 'Forgot Password Code Verified';

										$audit_activity 			= $audit_name;

										$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
									}
								// }
							}
						}

						$flag 	= 1;
						$status = SUCCESS;
						$msg 	= "Successfully verified. Please set new Password.";
					break;

					case 'password' :

						$upd_val 	= array();

						if( !EMPTY($params['password']) )
						{
							$clean_password 		= preg_replace('/\s+/', '', $params['password']);
							$pass_salt 				= gen_salt(TRUE);
							$upd_val['password'] 	= in_salt($clean_password, $pass_salt, TRUE);
							$upd_val["salt"] 		= $pass_salt;
						}

						$upd_val['modified_by']		= NULL;
						$upd_val['modified_date']	= date('Y-m-d H:i:s');

						$main_where 		= array(
							'user_id'		=> $user_id
						);

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USERS;
						$audit_action[] 	= AUDIT_UPDATE;
						$prev_detail[]  	= array($this->users->get_user_details($user_id));

						$this->users->update_helper( SYSAD_Model::CORE_TABLE_USERS, $upd_val, $main_where );
						

						$user_details 		= $this->users->get_user_details($user_id);

						$audit_name 				= 'Forgot Password. Password updated';

						$audit_activity 			= $audit_name;

						$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

						$flag 	= 1;
						$status = SUCCESS;
						$msg 	= "Password updated. Please try to login.";

						$close_modal = true;

					break;
				}
			}

			SYSAD_Model::commit();
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
			'status' 				=> $status,
			'security_question_view'	=> $security_question_view,
			'user_id'				=> $user_id,
			'user_id_enc'			=> $user_id_enc,
			'user_salt' 			=> $user_salt,
			'user_token'			=> $user_token,
			'action'				=> $action,
			'click_li' 				=> $click_li,
			'close_modal' 			=> $close_modal
		);

		echo json_encode($response);
	}
	
	public function update()
	{
		$flag 	= 0;
		$msg 	= "";
	
		try
		{
			$status = ERROR;
			$params = get_params();
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			
			$this->_check_reset_fields($params);
			$id			= $params["id"];
			$key 		= $params["key"];
			$password 	= $params["password"];
	
			$info 		= $this->auth_model->get_active_user($key, BY_RESET_SALT);
			
			if(EMPTY($info)) throw new Exception($this->lang->line('invalid_action'));
	
			$email 		= $info["email"];
			if($id != in_salt($email, $key, TRUE)) throw new Exception($this->lang->line('invalid_action'));
	
			$this->auth_model->update_password($email, $password);
	
			$msg 	= $this->lang->line('password_reset');
			
			SYSAD_Model::commit();
			$status = SUCCESS;
	
		}
		catch(PDOException $e)
		{			
			$msg = $this->rlog_error($e, TRUE);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
		}
		
		$result 		= array(
			"status" 	=> $status,
			"msg"	 	=> $msg
		);
	
		echo json_encode($result);
	}
	
	
	private function _check_reset_fields($params)
	{
		if(!ISSET($params["id"]) OR EMPTY($params["id"])) throw new Exception($this->lang->line('invalid_action'));
		if(!ISSET($params["key"]) OR EMPTY($params["key"])) throw new Exception($this->lang->line('invalid_action'));
		
		if(ISSET($params["password"]) && !EMPTY($params['password']))
		{
			if(!ISSET($params["password2"]) OR EMPTY($params['password2']))
				throw new Exception($this->lang->line('confirm_password'));
			
			$info = $this->auth_model->get_active_user($params["key"], BY_RESET_SALT);
			$this->users->check_password_history($info['user_id'], $params['password2']);
			
			if( EMPTY( preg_match('/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\s]+$/', $params['password'] ) ) )
			{
				throw new Exception("Password contains an illegal character.");
			}

			if( !EMPTY( $params['password'] ) )
			{
				$us_name 		= NULL;
				
				if( !EMPTY( $params['user_id'] ) )
				{
					$username 		= $info['username'];

					if( !EMPTY( $username ) )
					{
						$us_name 	= $username['username'];
					}
				}

				$check_password = $this->validate_password( $params['password'], $us_name );

				if( $check_password !== TRUE )
				{
					throw new Exception($check_password);
				}
			}
		}
		else
		{
			throw new Exception($this->lang->line('password_required'));
		}
		
	}
	
	private function _validate( array $params, array $orig_params )
	{
		$constraints 			= array();
		$arr 					= array();

		$constraints['email']	= array(
			'name'			=> 'Email',
			'data_type'		=> 'email'
		);


		$this->validate_inputs( $params, $constraints );

		$arr['email']	= $params['email'];

		return $arr;	
 	}
		
}


/* End of file forgot_password.php */
/* Location: ./application/controllers/forgot_password.php */