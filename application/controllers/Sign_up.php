<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sign_up extends SYSAD_Controller 
{

	private $module;
	private $controller;
	private $module_js;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->module 		= MODULE_USER;
		$this->controller 	= strtolower(__CLASS__);
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_COMMON."/".$this->controller;
		
		$this->load->model(CORE_USER_MANAGEMENT . '/users_model', 'users', TRUE);
		$this->load->model(CORE_USER_MANAGEMENT . '/organizations_model', 'orgs', TRUE);
		$this->load->model('settings_model', 'settings', TRUE);
	}	
	
	public function modal()
	{
		try
		{
			$data 			= array();
			$resources 		= array();
			$data['orgs'] 	= $this->orgs->get_orgs();
			
			$constraints 	= $this->settings->get_settings_value(PASSWORD_CONSTRAINTS);
			$pass_const 	= array();
			
			foreach ($constraints as $row)
			{
				$pass_const[$row['setting_name']] = $row['setting_value'];
			}
			
			$pass_err 		= $this->get_pass_error_msg();
			$pass_length 	= $pass_const[PASS_CONS_LENGTH];
			$upper_length 	= $pass_const[PASS_CONS_UPPERCASE];
			$digit_length 	= $pass_const[PASS_CONS_DIGIT];
			$repeat_pass 	= $pass_const[PASS_CONS_REPEATING];
			
			$validate_password_length 	= (intval($pass_length) > 0) ? true : false;
			
			$resources["load_css"] 		= array(CSS_LABELAUTY, CSS_SELECTIZE);
			$resources["load_js"] 		= array(JS_LABELAUTY, JS_SELECTIZE, $this->module_js);
			
			$resources['loaded_init'] 	= array (
				'materialize_select_init();',
				'SignUp.initResetModal("'.$validate_password_length.'", "'.$pass_length.'", "'.$upper_length.'", "'.$digit_length.'", "'.$pass_err.'", "'.$repeat_pass.'");',
				'SignUp.save();'
			);
		}
		catch(PDOException $e)
		{			
			echo $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			echo $this->rlog_error($e, TRUE);
		}

		$this->load->view("modals/sign_up", $data);
		$this->load_resources->get_resource($resources);
	}
	
	public function process()
	{
		try
		{
			$account_creator = get_setting(ACCOUNT, "account_creator");

			$status 			= ERROR;
			$mail_flag 			= 0;
			$orig_params		= get_params();

			// SERVER VALIDATION
			$params = $this->set_filter( $orig_params )
					->filter_string( 'lname' )
					->filter_string( 'fname' )
					->filter_string( 'mname' )
					->filter_string( 'job_title' )
					->filter_string( 'gender' )
					->filter_string( 'email' )
					->filter_string( 'org' )
					->filter();
									
			$val = $this->_validate( $params, $orig_params, $action, $account_creator );
			
			// GET SECURITY VARIABLES
			$salt 	= $orig_params['salt'];
			$token 	= $orig_params['token'];
			
			// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
			check_salt(PROJECT_NAME, $salt, $token);

			$table_us = SYSAD_Model::CORE_TABLE_USERS;


			if( $account_creator == VISITOR_NOT_APPROVAL 
				OR $account_creator == VISITOR
			)
			{
				$table_us = SYSAD_Model::CORE_TABLE_TEMP_USERS;

			}
			// throw new Exception('aa');
			$email_exist 	= $this->_validate_email($val['email']);
			
			if($email_exist)
			{
				throw new Exception($this->lang->line('email_exist'));
			}
				
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
		
			$audit_action[]	= AUDIT_INSERT;
			$audit_table[]	= $table_us;
			$audit_schema[]	= DB_CORE;
			$prev_detail[]	= array();
			
			if( $account_creator == VISITOR_NOT_APPROVAL )
			{
				$status 		= APPROVED;

				$val['status']	= $status;

				// $val['role'] 	= array('AUTHENTICATED_USER');
			}
			else if( $account_creator == VISITOR )
			{
				$status 		= PENDING;

				$val['status']	= $status;
			}
			
			$id 	= $this->users->insert_user($val, $account_creator);

			// $curr_detail[] = $this->users->get_specific_user($id);

			$c_r_d 			= $this->users->get_details_for_audit(
				$table_us, array('user_id' => $id)
			);

			$curr_detail[] 	= $c_r_d;

			// INSERT FOR SECURITY ANSWER please follow the name for inputs

			if( ISSET( $params['security_question_id'] ) AND !EMPTY( $params['security_question_id'] ) )
			{
				$sec_val = array();
				foreach( $params['security_question_id'] as $key => $sec_id )	
				{
					$answer 	= NULL;
					if( ISSET( $params['security_answer'][$key] ) AND !EMPTY($params['security_answer'][$key]) )
					{
						$answer = $params['security_answer'][$key];

						$sec_val[$key]['user_id'] 				= $id;
						$sec_val[$key]['security_question_id'] 	= $sec_id;
						$sec_val[$key]['answer'] 				= $answer;
					}
				}

				if( !EMPTY( $sec_val ) )
				{
					$table_sec 		= SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS;
					if( $account_creator == VISITOR_NOT_APPROVAL 
						OR $account_creator == VISITOR
					)
					{		
						$table_sec 	= SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS;
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= $table_sec;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->users->insert_helper($table_sec, $sec_val);

					$curr_detail[] 		= $this->users->get_details_for_audit($table_sec, array('user_id' => $id));
				}
			}
				
			$msg 	= $this->lang->line('signup_success');
				
			// GET THE DETAIL AFTER INSERTING THE RECORD
				
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
			$activity = "%s has signed up";
			$activity = sprintf($activity, $val['fname'] . ' ' . $val['lname']);
								
			$this->audit_trail->log_audit_trail(
				$activity,
				$this->module,
				$prev_detail,
				$curr_detail,
				$audit_action,
				$audit_table,
				$audit_schema
			);

			if( $account_creator == VISITOR_NOT_APPROVAL )
			{
				$mail_flag 	= $this->_send_approved_email( $id, APPROVED, $account_creator );
			
			}
			else
			{
				$c_d 		= array();
				
				$c_d[] 		= $this->users->get_specific_user($id);
				$mail_flag 	= $this->_send_sign_up_email($c_d, FALSE, TRUE, $account_creator);
			}
			
			SYSAD_Model::commit();

			$status 	= SUCCESS;
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
			"mail_sent" => $mail_flag
		);
	
		echo json_encode($info);
	
	}
	
	private function _validate( array $params, array $orig_params, $action = NULL, $account_creator = NULL )
	{
		$required 				= array();
		$constraints 			= array();

		$arr 					= array();

		try
		{
			$required['lname']	= "Last Name";
			$required['fname']	= "First Name";
			$required['email']	= "Email";
			$required['org']	= "Agency";

			$constraints['lname']	= array(
				'name'			=> 'Last Name',
				'data_type'		=> 'string',
				'max_len'		=> '255'
			);
			$constraints['fname']	= array(
				'name'			=> 'First Name',
				'data_type'		=> 'string',
				'max_len'		=> '255'
			);
			$constraints['mname']	= array(
				'name'			=> 'Middle Name',
				'data_type'		=> 'string',
				'max_len'		=> '255'
			);
			$constraints['job_title']	= array(
				'name'			=> 'Position',
				'data_type'		=> 'string',
				'max_len'		=> '255'
			);
			$constraints['email']	= array(
				'name'			=> 'Email',
				'data_type'		=> 'email'
			);
			$constraints['org']	= array(
				'name'			=> 'Agency',
				'data_type'		=> 'string',
				'max_len'		=> '25'
			);
			$constraints['gender']	= array(
				'name'			=> 'Sex',
				'data_type'		=> 'string'
			);

			$this->check_required_fields( $params, $required );

			$this->validate_inputs( $params, $constraints );

			$v = $this->core_v;

			$v->blacklist_email_domain();
			if( $account_creator == VISITOR_NOT_APPROVAL 
				OR $account_creator == VISITOR
			)
			{
					$email = 'CAST('.aes_crypt('email', FALSE, FALSE).' AS char(100))';
					$v->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_TEMP_USERS.'|primary_id='.$email, '@custom_error_The email address entered has already been used. Please use a different email.');
			}
				$v->check('email', $params);

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

		$arr['lname']		= $params['lname'];
		$arr['fname']		= $params['fname'];
		$arr['mname']		= $params['mname'];
		$arr['gender']		= $params['gender'];
		$arr['email']		= $params['email'];
		$arr['org']			= $params['org'];
		$arr['job_title']	= $params['job_title'];

		return $arr;	
 	}
	
	private function _send_sign_up_email($user_details)
	{	
		try
		{
			$flag 			= 0;
			$email_data 	= array();
			$template_data 	= array();
	
			$salt 			= gen_salt(TRUE);
			$system_title 	= get_setting(GENERAL, "system_title");
			$system_email 	= get_setting(GENERAL, "system_email");

			// required parameters for the email template library
			$email_data["from_email"] 	= $system_email;
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"] 	= array($user_details[0][0]['email']);
			$email_data["subject"] 		= 'Your Pending Registration to ' . $system_title;
				
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

			$template_data["logo"] 			= $system_logo_src;
			
			$template_data["email"] 		= $user_details[0][0]['email'];
			$template_data["system_name"] 	= $system_title;
			$template_data["name"] 			= $user_details[0][0]['fname'] . ' ' . $user_details[0][0]['lname'];
				
			$flag = $this->email_template->send_email_template($email_data, "emails/sign_up", $template_data);
			
			return $flag;
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
	
	private function _validate_email($email)
	{
		try
		{
			$exist_flag = $this->users->check_email_exist($email);
			
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

	private function _send_approved_email($id, $status, $account_creator = NULL)
	{	
		try
		{
			$user_detail 	= $this->users->get_user_details($id, $account_creator);

			$flag 			= 0;
			$email_data 	= array();
			$template_data 	= array();
	
			$system_title 	= get_setting(GENERAL, "system_title");
				
			// required parameters for the email template library
			$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"] 	= array($user_detail['email']);
			$email_data["subject"] 		= ($status == APPROVED) ? 'Activate your Account' : 'Registration Denied';
				
			
			// additional set of data that will be used by a specific template
			$template_data["email"] 		= $user_detail['email'];
			$template_data["password"] 		= base64_url_encode($user_detail['password']);
			$template_data["reason"] 		= $user_detail['reason'];
			$template_data["name"] 			= $user_detail['fname'] . ' ' . $user_detail['lname'];
			$template_data["status"] 		= $status;
			$template_data["system_name"] 	= $system_title;
			$template_data["id"] 			= $id;
				
			$this->email_template->send_email_template($email_data, "emails/account", $template_data);

			$error 							= $this->email_template->get_email_errors();

			if( !EMPTY( $error ) )
			{
				RLog::error( "Email Error" ."\n" . var_export($error, TRUE) . "\n" );
			}

			//$flag = 1;
			$flag = $this->email->print_debugger();
			
			return $flag;
		}
		catch( PDOException $e )
		{
			echo $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			echo $this->rlog_error( $e, TRUE );
		}	
	
	}
		
}