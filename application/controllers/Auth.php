<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends SYSAD_Controller 
{

	private $module_sign_up_js;

	public function __construct() 
	{
		parent::__construct();
		
		$this->load->model(CORE_USER_MANAGEMENT . '/users_model', 'users', TRUE);

		$this->load->library('Authentication_factors');

		$this->module_sign_up_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_COMMON."/sign_up";
	}
		
	public function index( $logout_inactivity = NULL, $extra_arr = NULL )
	{	
		$data 		= array();
		$check_has_agreement_text 	= 0;
		$fb_url 	= NULL;
		$g_url 		= NULL;

		$data['pass_username'] 	= '';

		try
		{
			$resources['load_materialize_modal'] 	= array (
			    'modal_forgot_pw' 	=> array (
					'title' 		=> "Forgot Password",
					'size' 			=> "xs md-w md-h",
					'controller' 	=> "forgot_password",
					'fixed_header' 	=> false,
					'modal_footer' 	=> false
			    ),
			     'modal_logout'		=> array(
			    	'title' 		=> 'Log-out',
			    	'size' 			=> "xs xs-w sm-h",
			    	'controller' 	=> 'Auth',
			    	'fixed_header' 	=> false,
			    	'custom_button'	=> array(
						'Logout' 	=> array("type" => "button", "action" => BTN_SAVING)
					),
					'cancel_btn_flag' => false
		    	),
			    'modal_sign_up' 	=> array (
					'fixed_header' 	=> false,
					'size' 			=> "lg xl-h",
					'controller' 	=> "sign_up",
					'modal_footer' 	=> false,
					'footer_div_none'	=> true
			    ),
			    'modal_term_condition'  	=> array(
			    	'title' => "Terms and condition",
					'size' => "lg-w lg-h",
					'controller' => "auth",
					'method' => "modal_term_condition",
					'fixed_header' => false,
					'modal_footer' => false,
					'footer_div_none'	=> true,
					'post'			=> true
		    	),
		    	'modal_verify_code' => array(
		    		'title' => "Verify Code",
					'size' => "sm-w sm-h",
					'controller' => "auth",
					'method' => "modal_verify_code",
					'fixed_header' => false,
					'modal_footer' => false,
					'footer_div_none'	=> true,
					'post'			=> true
		    	)
			);

			$check_has_agreement_text	= get_setting( AGREEMENT, 'has_agreement_text' );


			$login_sys_param 	= get_sys_param_val('LOGIN', 'LOGIN_WITH');

			$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

			if( !EMPTY( $extra_arr ) )
			{
				$extra_arr 		= (array) json_decode(base64_url_decode($extra_arr));

				$data['pass_username']	= $extra_arr['username'];

				$upd_where 	= array(
					'user_id'		=> $extra_arr['user_id'],
					'ip_address'	=> $extra_arr['ip_address'],
					'os'			=> $extra_arr['os']
				); 

				$get_user_device_location = $this->auth_model->get_user_device_location( $upd_where['user_id'], $upd_where['ip_address'], $upd_where['os'] );

				if( EMPTY( $get_user_device_location ) OR EMPTY( $get_user_device_location['user_id'] ) )
				{

					SYSAD_Model::beginTransaction();

					$upd_val 	= array(
						'authorized'	=> ENUM_YES,
						'authorized_date'=> date('Y-m-d H:i:s')
					);

					$this->users->update_helper( SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH, $upd_val, $upd_where );

					SYSAD_Model::commit();
				}
			}


			$data['ch_login_sys_param'] 		= $ch_login_sys_param;
			$data['check_has_agreement_text']	= $check_has_agreement_text;
			$data['logout_inactivity']			= $logout_inactivity;

			if( $ch_login_sys_param )
			{

				$fb_url 							= $this->facebook->login_url();
				$g_url 								= $this->google->login_url();

				$data['fb_url'] 					= $fb_url;
				$data['g_url'] 						= $g_url;
			}
			else
			{
				$data['fb_url'] 					= "";
				$data['g_url'] 						= "";	
			}

			$data['login_api_route']				= base_url().'auth/login_api_route/';
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			SYSAD_Model::rollback();
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);	

			SYSAD_Model::rollback();
		}

		$this->load->view('login', $data);
		$this->load_resources->get_resource($resources);
	}	

	public function device_location_auth(array $user_info)
	{
		try
		{
			$device_location_auth	= get_setting(LOGIN, "device_location_auth");

			if( !EMPTY( $device_location_auth ) )
			{
				$this->load->library('Browser');

				$os 		= $this->browser->getPlatform();
				$browser 	= $this->browser->getBrowser();
				$realIP 	= file_get_contents("https://ipinfo.io/json");

				$ipObj 		= json_decode($realIP);

				$local_ip 	= getUserIpAddr();

				$count_user_device_location 	= $this->auth_model->count_user_device_location($user_info['user_id']);
				
				$ins_val 	= array(
					'user_id'		=> $user_info['user_id'],
					'ip_address'	=> ( EMPTY( $ipObj->ip ) ) ? $local_ip : $ipObj->ip,
					'browser'		=> $browser,
					'os'			=> $os,
					'location'		=> ( EMPTY( $ipObj->ip ) ) ? $local_ip : $ipObj->country.', '.$ipObj->region.', '.$ipObj->city,
					'local_ip_address' => $local_ip
				);

				if( EMPTY($count_user_device_location) OR EMPTY($count_user_device_location['check_user_device_location']) )
				{
					$ins_val['authorized'] 		= ENUM_YES;
					$ins_val['authorized_date'] = date('Y-m-d H:i:s');
					$this->users->insert_helper(SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH, $ins_val);
				}
				else
				{
					$get_user_device_location = $this->auth_model->get_user_device_location( $user_info['user_id'], $ipObj->ip, $os );

					if( EMPTY( $get_user_device_location ) OR EMPTY( $get_user_device_location['user_id'] ) )
					{
						$this->sign_out($user_info['user_id'], TRUE);

						$this->users->insert_helper(SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH, $ins_val);

						$curr_user_device_location = $this->auth_model->get_user_device_location( $user_info['user_id'], $ipObj->ip, $os, TRUE );

						$this->email_new_device_location($curr_user_device_location, $user_info);

						throw new Exception($this->lang->line('new_device_location'));
					}
				}				
				
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
 		 
	public function email_new_device_location(array $user_device_location, $user_info)
	{ 
		try
		{ 
			$user_detail 	= $this->users->get_user_details($user_info['user_id']);

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


			$email_data 	= array();
			$template_data 	= array();
	
			$salt 			= gen_salt(TRUE);
			$system_title 	= get_setting(GENERAL, "system_title");

			$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"] 	= array($user_detail['email']);
			$email_data["subject"] 		= 'Authorize new Device Location';

			$url_arr 						= json_encode(array(
				'user_id'		=> $user_info['user_id'],
				'ip_address'	=> $user_device_location['ip_address'],
				'os'			=> $user_device_location['os'],
				'username'		=> $user_detail['username']
			));

			$template_data["name"] 			= $user_detail['fname'] . ' ' . $user_detail['lname'];
			$template_data["ip_address"] 	= $user_device_location['ip_address'];
			$template_data['logo']			= $system_logo_src;
			$template_data['system_name']	= $system_title;
			$template_data["platform"] 		= $user_device_location['os'];
			$template_data["browser"] 		= $user_device_location['browser'];
			$template_data["location"] 		= $user_device_location['location'];
			$template_data['auth_link'] 	= base_url().'auth/index/0/'.base64_url_encode($url_arr);
			$template_data['authorize_link'] = '
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
					  href="'.$template_data['auth_link'].'">
					I authorize</a>
';

			$template_data['logo']			= '
				<div style="background:#333333; padding:20px 30px; text-align:center;"><img src="'.$template_data['logo'].'" height="40" alt="logo" /></div>
';

			// $this->email_template->send_email_template($email_data, "emails/new_device_location", $template_data);

			$this->email_template->send_email_template_html($email_data, STATEMENT_CODE_EMAIL_NEW_DEVICE_LOCATION, $template_data);

			$errors 						= $this->email_template->get_email_errors();

			if( !EMPTY( $errors ) )
			{
				$str 						= var_export( $errors, TRUE );

				RLog::error( "Email Error" ."\n" . $str . "\n" );
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
	
	public function sign_in($username = NULL, $password = NULL) 
	{
		$initial_flag 		= 0;
		$redirect_page 		= "";

		$check_has_agreement_text 	= 0;
		$user_agreed 				= 0;
		$checked_term_conditions 	= 0;

		$multi_auth 			= array();

		try 
		{
			$flag 			= 0;
			$msg 			= "";
			$salted 		= FALSE;

			if(!IS_NULL($username) AND !IS_NULL($password))
			{
				$username = filter_var($username, FILTER_SANITIZE_STRING);
				$username = base64_url_decode($username);
				$password = filter_var(base64_url_decode($password), FILTER_SANITIZE_STRING);
				
				$this->auth_model->update_status($username);
				
				$salted = TRUE;
			} 
			else 
			{
				$orig_params 	= get_params();
				
				$params = $this->set_filter( $orig_params )
						->filter_string( 'username' )
						->filter_string( 'password' )
						->filter();
				
				$val = $this->_validate( $params, $orig_params );

				$username 	= filter_var($val['username'], FILTER_SANITIZE_STRING);
				$password 	= filter_var($val['password'], FILTER_SANITIZE_STRING);

			}	
			
			if(EMPTY($username)) throw new Exception($this->lang->line('username_required'));
			if(EMPTY($password)) throw new Exception($this->lang->line('password_required'));

			$check_has_agreement_text	= get_setting( AGREEMENT, 'has_agreement_text' );
			$checked_term_conditions 	= get_setting(GENERAL, "term_conditions");

			if( !EMPTY($checked_term_conditions) OR ( !EMPTY( $check_has_agreement_text ) AND $check_has_agreement_text == DATA_PRIVACY_TYPE_BASIC ) )
			{
				$user_details				= $this->auth_model->get_active_user($username, NULL, TRUE);

				$this->authenticate->sign_in($username, $password, $salted, TRUE, FALSE, TRUE);

				$user_agreed_details 		= $this->auth_model->get_user_agreement($user_details['user_id']);

				if( !EMPTY($user_agreed_details))
				{
					if( !EMPTY( $user_agreed_details['agreement_flag'] ) )
					{
						$this->users->update_last_logged_in( $username );

						$this->authenticate->sign_in($username, $password, $salted);

						if( !EMPTY( $change_password_initial_login ) )
						{

							$initial_flag 				= ($this->session->has_userdata('initial_flag') == TRUE) ? $this->session->userdata( "initial_flag" ) : 0;
						}
					}

					$user_agreed 			= $user_agreed_details['agreement_flag'];
				}
			}
			else
			{
				$this->authenticate->sign_in($username, $password, $salted);

				$this->users->update_last_logged_in( $username );

				$change_password_initial_login 	= get_setting(LOGIN, "change_password_initial_login");

				if( !EMPTY( $change_password_initial_login ) )
				{

					$initial_flag 				= ($this->session->has_userdata('initial_flag') == TRUE) ? $this->session->userdata( "initial_flag" ) : 0;
				}
			}

			$user_info 	= $this->auth_model->get_active_user($username);

			$this->device_location_auth($user_info);

			$check_multi_auth_enable 	= $this->authentication_factors->check_authentication_factor_section_enabled(AUTH_SECTION_LOGIN);

			if( $check_multi_auth_enable )
			{
				SYSAD_Model::beginTransaction();
				$this->authentication_factors->save_multi_auth_section_helper($user_info['user_id'], SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, AUTH_SECTION_LOGIN);

				SYSAD_Model::commit();

				$multi_auth = $this->authentication_factors->check_user_multi_auth_section($user_info['user_id'], SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, AUTH_SECTION_LOGIN);

				if( !EMPTY( $multi_auth ) )
				{
					$this->sign_out($user_info['user_id'], TRUE);
				}
			}

			$activity 	= 'logged in '.$user_info['name'].'.';

			$this->audit_trail->log_audit_trail(
				$activity, 
				MODULE_USER
			);
			
			
			$flag 			= 1;
			$redirect_page 	= (($this->session->has_userdata('redirect_page')) === TRUE ) ? $this->session->redirect_page : '';
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

			if( $e instanceof Multiple_login_exception )
			{
				$flag = 2;
			}
		}
		catch( Multiple_login_exception $e )
		{
			SYSAD_Model::rollback();
			$msg 	= $e->getMessage();
			$flag 	= 2;
		}
		
		$result = array(
			"flag" 			=> $flag,
			"msg" 			=> $msg,
			"redirect_page" => $redirect_page,
			'initial_flag'	=> $initial_flag,
			"check_has_agreement_text"	=> $check_has_agreement_text,
			"user_agreed"	=> $user_agreed,
			'multi_auth' => $multi_auth,
			'checked_term_conditions' => $checked_term_conditions
		);

		if( !EMPTY( $initial_flag ) )
		{
			$result["username"] 	= base64_url_encode( $this->session->userdata('username') );
			$result['salt'] 		= $this->session->userdata('salt');
		}
		
		if($salted)
		{
			$this->authenticate->check_user();	
		} 
		else 
		{
			echo json_encode($result);
		}	
		
	}

	public function api_reroute($google = NULL)
	{
		try
		{
			$login_sys_param 		= get_sys_param_val('LOGIN', 'LOGIN_WITH');

			$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

			if( $ch_login_sys_param )
			{

				if( !EMPTY( $google ) ) 
				{
					header('Location:'.$this->google->login_url());
				}
				else
				{
					header('Location:'.$this->facebook->login_url());
				}

				$this->session->set_userdata('sign_up_api_route', 1);
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);	
		}
	}

	public function login_api_route($google = NULL)
	{
		try
		{
			$login_sys_param 		= get_sys_param_val('LOGIN', 'LOGIN_WITH');

			$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

			$this->session->unset_userdata('sign_up_api_route');

			if( $ch_login_sys_param )
			{


				if( !EMPTY( $google ) ) 
				{
					header('Location:'.$this->google->login_url());
				}
				else
				{
					header('Location:'.$this->facebook->login_url());
				}
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);	
		}
	}

	public function get_term_condition_file()
	{

		$flag 				= 0;

		try
		{
			$params 		= get_params(TRUE, TRUE);
			
			if( ISSET( $params['file'] ) )
			{
				$this->load->module('Upload');

				/*$path 		= FCPATH.PATH_TERM_CONDITIONS_UPLOADS.$params['file'];
				$path 		= str_replace(array('\\','/'), array(DS,DS), $path);*/

				$path 		= PATH_STATEMENTS;

				$this->upload->force_download( $params['file'], $path);

				/*if( file_exists( $path ) )
				{
					$ext 		= pathinfo( $path, PATHINFO_EXTENSION );

					if( strtolower( $ext ) == 'pdf' )
					{
						$pdf 	= file_get_contents( $path );

						header("Content-type: application/pdf");
						header("Content-Disposition: inline; filename=".$params['file']."");

						readfile( $path );

					}
					else 
					{
						$this->load->helper('download');

						force_download( $path, NULL );
					}

					$flag 		= 1;
				}
				else
				{
					throw new Exception('File not found.');
				}*/
			}
		}
		catch( PDOException $e )
		{
			$this->rlog_error( $e );
		}
		catch(Exception $e)
		{
			$this->rlog_error( $e );
		}

		if( !$flag )
		{
			redirect(base_url().'Errors/index/402/');
		}
		
	}
	
	public function sign_out($user_id = NULL, $no_echo = FALSE)
	{
		try
		{
			$flag 	= 0;
			$msg 	= "";

			$id 		= $this->session->user_id;

			if( !EMPTY( $user_id ) )
			{
				$id 	= $user_id;
			}
		
			$this->authenticate->sign_out($id);

			$user_info 	= $this->users->get_user_details($id);

			$activity 	= 'logged out '.$user_info['fname'].' '.$user_info['lname'].'.';

			$this->audit_trail->log_audit_trail(
				$activity, 
				MODULE_USER
			);
			
			// Unset autologin variable
			delete_cookie('autologin');
			$flag 	= 1;							
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);
		}
		
		$result		= array(
			"flag" 	=> $flag,
			"msg" 	=> $msg
		); 

		if( !$no_echo )
		{
			echo json_encode($result);
		}
			
	}
	
	public function verify($id, $email)
	{
		$msg 		= "";
		$data 		= array();
		$resources 	= array();
	
		try
		{
			if(EMPTY($id) OR EMPTY($email))
			{
				throw new Exception($this->lang->line('invalid_action'));
			}

			$account_creator = get_setting(ACCOUNT, "account_creator");
			
			$is_verified = $this->auth_model->check_user_status(base64_url_decode($id), TRUE, $account_creator);
			
			if(EMPTY($is_verified))
			{
				header('Location:'.base_url().'unauthorized/invalid_link');
			}
			
			$resources['load_materialize_modal'] = array (
				'modal_verify_account' 	=> array (
					'fixed_header' 		=> false,
					'size' 				=> "sm-w lg-h",
					'controller' 		=> "auth",
					'modal_footer' 		=> false,
					'method' 			=> "modal_verify_account/" . $id . "/" . $email,
					'modal_type' 		=> "open"
				)
			);
			
			$this->load->view('login', $data);
			$this->load_resources->get_resource($resources);
	
		}
		catch(PDOException $e)
		{			
			echo $this->get_user_message($e);

			// redirect(base_url() . 'errors/index/500/'.base64_url_encode($msg) , 'location');
		}
		catch(Exception $e)
		{
			echo $this->rlog_error($e, TRUE);

			// redirect(base_url() . 'errors/index/500/'.base64_url_encode($msg) , 'location');
		}
	}
	
	public function modal_verify_account($id, $email)
	{
		try
		{
			$data 		= array();
			$resources 	= array();
			
			$data['id'] 	= $id;
			$data['email'] 	= $email;
			
			$module_js 		= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_COMMON."/forgot_password";
			
			$pass_const 		= $this->users->get_settings_arr(PASSWORD_CONSTRAINTS);
			$user_const 		= $this->users->get_settings_arr(USERNAME_CONSTRAINTS);

			$user_has_cons 		= get_setting( USERNAME, 'apply_username_constraints' );
			
			$pass_err 			= $this->get_pass_error_msg();
			$pass_length 		= $pass_const[PASS_CONS_LENGTH];
			$upper_length 		= $pass_const[PASS_CONS_UPPERCASE];
			$digit_length 		= $pass_const[PASS_CONS_DIGIT];
			$repeat_pass 		= $pass_const[PASS_CONS_REPEATING];
			$letter_length 		= $pass_const['constraint_letter'];
			$lower_length 		= $pass_const['constraint_lowercase'];
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
				'letter_length' 	=> $letter_length,
				'lower_length'	=> $lower_length
			);

			$cons_array 		= json_encode( $cons_array );
			
			$resources['load_js'] 	= array($module_js);
			$resources['loaded_init'] = array(
				'password_constraints( '.$cons_array.' );',
				'username_constraints( '.$user_cons_arr.' );',
				'ForgotPw.saveUser();'
			);
			
			$this->load->view("modals/verify_account", $data);
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

	private function _validate_user_account( array $params, $account_creator = NULL )
	{
		try
		{
			if(!EMPTY($params['password']))
			{
				$this->users->check_password_history($params['user_id'], $params['password']);

				if( EMPTY( preg_match('/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\s]+$/', $params['password'] ) ) )
				{
					throw new Exception("Password contains an illegal character.");
				}
			}
			else
			{
				throw new Exception(sprintf($this->lang->line('is_required'), "Password"));
			}

			if( EMPTY( $params['username'] ) )
			{
				throw new Exception(sprintf($this->lang->line('is_required'), "Username"));
			}

			if( !EMPTY( $params['password'] ) )
			{
							
				$check_password = $this->validate_password( $params['password'], $params['username'] );

				if( $check_password !== TRUE )
				{
					throw new Exception($check_password);
				}
			}

			$v 	= $this->core_v;

			$username_cast = 'CAST('.aes_crypt('username', FALSE, FALSE).' AS char(100))';

			if( !EMPTY( $params['username'] ) )
			{

				$v
					->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$username_cast, '@custom_error_This username already exists in our system.')
					->check('username', $params);

				$v->assert(FALSE);
			}

			if( !EMPTY( $account_creator ) 
				AND ( $account_creator == VISITOR_NOT_APPROVAL OR $account_creator == VISITOR )
			)
			{
				if( !EMPTY( $params['username'] ) )
				{

					$v
						->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_TEMP_USERS.'|primary_id='.$username_cast, '@custom_error_This username already exists in our system.')
						->check('username', $params);

					$v->assert(FALSE);
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
	
	public function update_user_account()
	{
		$redirect_page 		= "";

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		try
		{
			$account_creator = get_setting(ACCOUNT, "account_creator");

			$status = ERROR;
			$params	= get_params();

		
			$decode_id	= base64_url_decode($params['user_id']);
			$id			= filter_var($decode_id, FILTER_SANITIZE_NUMBER_INT);

			$params['user_id'] 			= $decode_id;

			$this->_validate_user_account( $params, $account_creator );

			/*$get_default_roles = $this->auth_model->get_default_sign_up_role();

			if( EMPTY( $get_default_roles ) )
			{
				throw new Exception('There are no roles assigned. Please contact system administrator.');
			}*/
		
			// GET SECURITY VARIABLES
			
			// BEGIN TRANSACTION			
			SYSAD_Model::beginTransaction();
			
			$params['verified_account'] = TRUE;
			
			$this->users->update_status($params, $account_creator);

			if( $account_creator == VISITOR_NOT_APPROVAL OR $account_creator == VISITOR )
			{

				$audit_details 		= $this->authentication_factors->insert_temp_user_to_main($params['user_id'], $account_creator);

				if( !EMPTY( $audit_details['audit_table'] ) )
				{
					$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
					$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
					$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
					$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
					$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );

					if( !EMPTY( $audit_schema ) )
					{
						$audit_activity = $audit_details['audit_activity'];

						$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
					}
				}

			}

			$msg 		= $this->lang->line('data_updated');


			$redirect_page 	= (($this->session->has_userdata('redirect_page')) === TRUE ) ? $this->session->redirect_page : '';
			
			SYSAD_Model::commit();
			$status 	= SUCCESS;
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();

			$this->rlog_error($e, TRUE);

			$msg = $this->get_user_message($e, array(), array(1062 => 'Sorry, The username '.$params['username'].' already exists.'));
		
			// $msg = $this->rlog_error($e, TRUE);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
				
			$msg = $this->rlog_error($e, TRUE);
		}
		
		$info 			= array(
			"status" 	=> $status,
			"msg" 		=> $msg,
			"redirect_page" => $redirect_page
		);
	
		echo json_encode($info);
	}

	public function change_password_owner( $username, $salt, $initial_flag, $to_sign_in )
	{
		$this->reset_password_form( $username, $salt, $initial_flag, $to_sign_in );
	}

	public function reset_password_form($username, $reset_salt, $initial_flag = INITIAL_NO, $to_sign_in = FALSE )
	{		
		try 
		{
			if(empty($username) OR empty($reset_salt)) throw new Exception("Invalid request.");

			$username 				= base64_url_decode($username);
			
			// GET USER INFO USING USER ID AND RESET SALT
			$info = $this->auth_model->get_user_by_id_reset_salt( $username, $reset_salt, $initial_flag );
			
			if(empty($info)) throw new Exception("Invalid request.");
			
			$data					= array();
			$data['id']				= in_salt($username, $reset_salt);
			$data['key']			= $reset_salt;
			$data['initial_flag'] 	= $initial_flag;
			$data['to_sign_in'] 	= $to_sign_in;
			$data['username']		= $username;

			$pass_const 			= $this->users->get_settings_arr(PASSWORD_CONSTRAINTS);
			
			$pass_err 				= $this->get_pass_error_msg();
			$pass_length 			= $pass_const[PASS_CONS_LENGTH];
			$upper_length 			= $pass_const[PASS_CONS_UPPERCASE];
			$digit_length 			= $pass_const[PASS_CONS_DIGIT];
			$repeat_pass 			= $pass_const[PASS_CONS_REPEATING];
			$letter_length 			= $pass_const['constraint_letter'];
			$lower_length 		= $pass_const['constraint_lowercase'];
			$spec_length 			= $pass_const['constraint_special_character'];

			$cons_array = array(
				'pass_err'		=> $pass_err,
				'pass_length'	=> $pass_length,
				'upper_length'	=> $upper_length,
				'digit_length'	=> $digit_length,
				'repeat_pass'	=> $repeat_pass,
				'letter_length'	=> $letter_length,
				'spec_length' 	=> $spec_length,
				'pass_same' 	=> 0,
				'lower_length'	=> $lower_length
			);

			$cons_array 		= json_encode( $cons_array );

			$resources['loaded_init']	= array(
				'password_constraints( '.$cons_array.' );'
			);
			
			$this->load->view('forms/reset_password_form', $data);
			$this->load_resources->get_resource($resources);
			
		}
		catch(PDOException $e)
		{
			echo $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			echo $this->rlog_error( $e, TRUE );
		}			
	}

	private function _check_reset_fields($params, $username)
	{

		$required 	= array();

		$required["password"]			= "Password";
		$required["retype_password"]	= "Confirm Password";

		if(!ISSET($params["id"]) OR EMPTY($params["id"])) throw new Exception($this->lang->line('err_invalid_data'));
		if(!ISSET($params["key"]) OR EMPTY($params["key"])) throw new Exception($this->lang->line('err_invalid_data'));

		$this->check_required_fields( $params, $required );
		
		if($params["password"] != $params["retype_password"]) throw new Exception('Confirm Password is invalid');

		if( !EMPTY( $params['password'] ) )
		{
			$check_password = $this->validate_password( $params['password'], $username );

			if( $check_password !== TRUE )
			{
				throw new Exception($check_password);
			}

			// $this->users->check_password_history($this->session->user_id, $params['confirm_password']);

			if( EMPTY( preg_match('/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\s]+$/', $params['password'] ) ) )
			{
				throw new Exception("Password contains an illegal character.");
			}
		}

	}

	public function update_password()
	{	
		$flag 			= 0;
		$msg 			= "";
		$initial_flag 	= 0;

		try
		{
			$params 		= get_params();

			$id				= $params["id"];
			$key 			= $params["key"];
			$password 		= $params["password"];
			$initial_flag 	= $params['initial_flag'];

			if( $initial_flag )
			{	
				$info 		= $this->auth_model->get_active_user_for_reset( $key, BY_SALT );
			}
			else 
			{
				$info 		= $this->auth_model->get_active_user_for_reset($key, BY_RESET_SALT, INACTIVE);
			}
			
			$this->_check_reset_fields($params, $info['username']);

			$prev_detail 	= array();
			$curr_detail 	= array();

			//CHECK IF THE PASSWORD CONTAINS REPEATED CHARACTERS
		/*	if( max( array_count_values( str_split( $password ) ) )>1 )
				throw new Exception( $this->lang->line( 'no_repeat_char') );*/


			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			// CHECKS IF THIS IS THE INITIAL LOG IN OF THE USER 
			if(EMPTY($info)) throw new Exception($this->lang->line('err_unauthorized_access'));

			$this->users->check_password_history( $info['user_id'], $password );
	
			$username 		= $info["username"];
	
			if( $id != in_salt( $username, $key ) ) 
				throw new Exception($this->lang->line('err_unauthorized_access'));
			
			$password_salt 	= in_salt($password, $info['salt']);
			//echo 'PASSWORD  SALT' . $password_salt . '\n';
			//echo 'SALT  ' . $info['salt'] . '\n';

			
			/*$password_hist 	= $this->user->check_password_hist( $info['user_id'], $password_salt );

			if( $info['password'] == $password_salt ) 
				throw new Exception('Error. Reusing password is not allowed');

			if(! EMPTY($password_hist))
				throw new Exception('Error. Reusing password is not allowed');*/

			// SAVE IN PASSWORD HISTORY

			$audit_table[] 	= SYSAD_Model::CORE_TABLE_USERS;
			$audit_schema[]	= DB_CORE;
			$audit_action[]	= AUDIT_UPDATE;

			$prev_detail[] 	= array( $this->users->get_user_details( $info['user_id'] ) );

			$password 		= preg_replace('/\s+/', '', $password);
			
			$this->auth_model->update_password($info['email'], $password);

			$curr_detail[] 	= array( $this->users->get_user_details( $info['user_id'] ) );

			$activity 		= "%s's password has been updated";
			$activity 		= sprintf($activity, $username);

			if( !EMPTY( $initial_flag ) )
			{
				$this->audit_trail->log_audit_trail(
					$activity, 
					MODULE_USER, 
					$prev_detail, 
					$curr_detail, 
					$audit_action, 
					$audit_table,
					$audit_schema
				);
				$this->session->set_userdata( "initial_flag", 0 );
			}

			SYSAD_Model::commit();
	
			$msg 			= "Password has been reset. You can now login using your new password.";
			$flag 			= 1;
	
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();

			$msg 			= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			// IF THE TRANSACTION IS NOT SUCCESSFUL, ROLLBACK ALL CHANGES
			SYSAD_Model::rollback();

			$msg 			= $this->rlog_error( $e, TRUE );
		}
	
		$result = array(
			"msg" 			=> $msg,
			"flag" 			=> $flag,
			"initial_flag" 	=> $this->session->userdata( "initial_flag" )
		);
	
		echo json_encode($result);
	}

	public function update_user_agreement()
	{
		$msg 		= '';

		$status = ERROR;
		$params	= get_params();
		$flag 	= 0;		

		$redirect_page = '';

		$sign_up 		= 0;
		$username 		= NULL;
		$password 		= NULL;

		$user_id 		= NULL;

		try
		{
			if( EMPTY( $params['sign_up_check'] ) )
			{
				$this->_validate_user_agreement( $params );
			}

			SYSAD_Model::rollback();

			if( !EMPTY( $params['sign_up_check'] ) )
			{
				$sign_up 	= $params['sign_up_check'];
			}
			else
			{
				$user_info 	= $this->auth_model->get_active_user( $params['username'], NULL, TRUE );

				$user_id 	= $user_info['user_id'];

				$this->auth_model->update_user_agreement( $user_info['user_id'] );

				$this->authenticate->sign_in($params['username'], $params['password'], FALSE);

			}

			if( !EMPTY( $user_id ) )
			{
				$user_detail 	= $this->users->get_user_details($user_id);

				$password 		= $user_detail['password'];
				$username 		= $user_detail['username'];
			}

			$flag 		= 1;

			$redirect_page 	= (($this->session->has_userdata('redirect_page')) === TRUE ) ? $this->session->redirect_page : '';

			SYSAD_Model::commit();
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

		$result 	= array(
			"flag" 	=> $flag,
			"msg" 	=> $msg,
			'redirect_page' => $redirect_page,
			'sign_up'		=> $sign_up,
			'username'		=> $username,
			'password'		=> $password,
			'user_id'		=> $user_id
		);

		echo json_encode( $result );
	}

	private function _validate_user_agreement( array $params )
	{
		if( EMPTY( $params['agreement'] ) )
		{
			throw new Exception('You must agree first before proceeding.');
		}

		$required['username'] 		= 'Username';
		$required['password'] 		= 'Password';		

		$this->check_required_fields( $params, $required );

		$this->authenticate->sign_in( $params['username'], $params['password'], FALSE, TRUE );
	}

	public function modal_term_condition($for = NULL)
	{
		$data 							= array();
		$resources 						= array();
		$params 						= array();
		$sign_up 						= 0;

		$segments 			= array();
 		$lists 				= array();
 		$class_step 		= '';
 		$pass_data 			= array();

		try
		{
			$module_js 					= HMVC_FOLDER."/terms";

			$resources['load_css'] 		= array( 'custom', CSS_WIZARD);
			$resources['load_js']		= array(JS_WIZARD, 'auth', $module_js);
			$resources['loaded_init']	= array('Wizard.init();', 'Terms.init();', 'Terms.proceed();');

			$get_agremment_text 		= get_setting( AGREEMENT, 'agremment_text' );
			$agreement_uploads 			= get_setting( AGREEMENT, 'agreement_uploads' );
			$checked_term_conditions 	= get_setting(GENERAL, 'term_conditions');
			$check_has_agreement_text	= get_setting( AGREEMENT, 'has_agreement_text' );

			if( ISSET( $params['sign_up'] ) AND !EMPTY( $params['sign_up'] ) )
			{
				$sign_up 				= 1;
			}

			$agreement_text 				= '';
			$terms_text 					= '';

			if( !EMPTY( $checked_term_conditions ) )
			{
				$lists[] 	= 'Terms and Condtions';
				$segments[] = 'tm_terms_conditions';

				$term_value 		= get_setting( GENERAL, 'term_condition_value' );
				$term_val_arr 		= explode(',', $term_value);

				$terms_text 		= $this->process_statements($term_val_arr);
			}

			if( ( !EMPTY( $check_has_agreement_text ) AND $check_has_agreement_text == DATA_PRIVACY_TYPE_BASIC ) AND !EMPTY( $get_agremment_text ) )
			{

				$lists[] 	= 'Data Privacy Statement';
				$segments[] = 'tm_data_privacy_statement';

				$aggr_txt_arr 				= explode(',', $get_agremment_text);
				
				$agreement_text 			= $this->process_statements($aggr_txt_arr);
			}		

			$class_step 				= trim( convertNumberToWord( count( $lists ) ) ).'-step';
			$num_step 					= count($lists);
			
			$data['agreement_text'] 	= $agreement_text;

			// $data['agreement_uploads']	= ( !EMPTY( $agreement_uploads ) ) ? explode('|', $agreement_uploads) : array();

			$data['agreement_uploads'] 	= array();

			$data['sign_up'] 			= $sign_up;
			$data['lists']					= $lists;
			$data['class_step']				= $class_step;
			$data['segments']				= $segments;
			$data['num_step'] 				= $num_step;
			$data['terms_text'] 			= $terms_text;

			$data['pass_data']				= $data;
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error( $e, TRUE );
		}

		if( !EMPTY( $for ) )
		{
			$this->load->view("modals/".$for.'_term', $data);
		}
		else
		{
			$this->load->view("modals/term_condition", $data);
		}
		$this->load_resources->get_resource($resources);
	}

	protected function process_statements(array $ids)
	{
		$agreement_text 			= '';

		try
		{
			if( !EMPTY( $ids ) )
			{
				$this->load->model(CORE_MAINTENANCE.'/Statements_model', 'auth_sm');

				$statement_detail 			= $this->auth_sm->get_specific_statement_many($ids);

				if( !EMPTY( $statement_detail ) )
				{
					foreach( $statement_detail as $sd )
					{
						switch( $sd['statement_type_id'] )
						{
							case STATEMENT_TYPE_TEXT :

								$agreement_text 	.= ( !EMPTY( $sd['statement'] ) ) ? html_entity_decode( $sd['statement'] ) : '';
								$agreement_text 	.= '<br/>';
							break;

							case STATEMENT_TYPE_LINK :
								$agreement_text 	.= $sd['statement_link'];
								$agreement_text 	.= '<br/>';
							break;

							case STATEMENT_TYPE_FILE :
								if( !EMPTY( $sd['sys_file_names'] ) )
								{
									$files = explode(',', $sd['sys_file_names']);
									$orig_file = explode(',', $sd['orig_file_names']);

									foreach( $files as $key => $file )
									{
										$path 			= FCPATH.PATH_STATEMENTS.$file;
										$path 			= str_replace(array('\\', '/'), array(DS, DS), $path);

										$origFile 		= '';

										if( ISSET( $orig_file[$key] ) )
										{
											$origFile 	= $orig_file[$key];
										}

										$pathinfo 		= pathinfo( $path );
										$extension 		= strtolower( $pathinfo['extension'] );

										$view_path 		= base_url().'auth/get_term_condition_file?file='.$file;

										$agreement_text .= "<a href='".$view_path."' target='_blank' class='".$extension."'>".$origFile."</a>, ";
									}
									
									$agreement_text = rtrim($agreement_text, ", ");
									$agreement_text 	.= '<br/>';
								}
							break;
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

		return $agreement_text;
	}

	public function modal_verify_code()
	{
		$data 							= array();
		$resources 						= array();
		$orig_params 					= get_params();
		$params 						= array();

		$configs 						= array();

		try
		{

			$params 					= $this->authentication_factors->filter_verify_code($orig_params);
			$this->authentication_factors->validate_vc($params);

			$configs 					= $this->authentication_factors->auth_factor_config($params['authentication_factor_id']);

			$params['generate_token'] 	= 1;

			SYSAD_Model::beginTransaction();

			$this->authentication_factors->update_multi_auth(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $params, AUTH_SECTION_LOGIN);

			SYSAD_Model::commit();

			$module_js 					= HMVC_FOLDER."/multi_auth";

			$resources['load_js']		= array('auth', $module_js);
			$resources['loaded_init']	= array('Multi_auth.verify_btn();', 'Multi_auth.resend();');
		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();
			$msg 	= $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			$msg 	= $this->rlog_error( $e, TRUE );
		}

		$data['orig_params'] 	= $orig_params;
		$data['params'] 		= $params;
		$data['configs'] 		= $configs;

		$this->load->view("modals/verify_code", $data);
		$this->load_resources->get_resource($resources);
	}

	public function resend_code()
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

		try
		{
			$params 		 	= $this->authentication_factors->filter_verify_code( $orig_params );

			$this->authentication_factors->validate_vc( $params );

			$params['generate_token'] 	= 1;

			SYSAD_Model::beginTransaction();

			$account_creator 	= NULL;
			$table 				= SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH;
			$section 			= AUTH_SECTION_LOGIN;

			if( ISSET( $params['temp_flag'] ) AND !EMPTY( $params['temp_flag'] ) )
			{
				$account_creator = get_setting(ACCOUNT, "account_creator");
				$table 			 = SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH;
				$section 		 = AUTH_SECTION_ACCOUNT;
			}

			$this->authentication_factors->update_multi_auth($table, $params, $section, $account_creator);

			SYSAD_Model::commit();


			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= 'Verification Code was resent.';
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
			/*'username'				=> $username,
			'password'				=> $password*/
		);

		echo json_encode( $response );
	}

	private function _validate( array $params, array $orig_params )
	{
		$constraints 			= array();
		$arr 					= array();

		$constraints['username']	= array(
			'name'			=> 'Username',
			'data_type'		=> 'string',
			'max_len'		=> '100'
		);
		
		$constraints['password']	= array(
			'name'			=> 'Username',
			'data_type'		=> 'password'
		);


		$this->validate_inputs( $params, $constraints );

		$arr['username']	= $params['username'];
		$arr['password']	= $params['password'];

		return $arr;	
 	}

 	public function approve_reject_invitation($user_id = NULL, $salt = NULL, $token = NULL, $status = NULL)
 	{
 		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		try
		{
			$user_id 	= base64_url_decode($user_id);

			check_salt($user_id, $salt, $token);

			$user_detail = $this->users->get_user_details($user_id);

			if( EMPTY( $user_detail ) )
			{
				throw new Exception('Invalid Link.');
			}
			else
			{
				if( EMPTY( $user_detail['expire_dpa_date'] ) )
				{
					throw new Exception('Invalid Link.');
				}
				else 
				{
					$date_now = date('Y-m-d H:i:s');
					$date_str = strtotime($date_now);

					$expire_d = strtotime($user_detail['expire_dpa_date']);

					if( $date_now >= $expire_d )
					{
						throw new Exception('Invalid Link.');		
					}
				}
			}

			$upd_val 	= array();

			$main_where 	= array(
				'user_id'	=> $user_id
			);

			$upd_val['modified_by']	= $user_id;
			$upd_val['modified_date'] = date('Y-m-d H:i:s');

			SYSAD_Model::beginTransaction();

			if( $status == APPROVED )
			{
				$upd_val['expire_dpa_date'] = NULL;
				$upd_val['status'] 		= STATUS_ACTIVE;

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USERS;
				$audit_action[] 	= AUDIT_UPDATE;
				$prev_detail[]  	= array($this->users->get_user_details( $user_id ));

				$this->users->update_helper(SYSAD_Model::CORE_TABLE_USERS, $upd_val, $main_where);

				$curr_det_u 	= $this->users->get_user_details( $user_id );

				$curr_detail[] 	= array($curr_det_u);

				if( !EMPTY( $audit_schema ) )
				{
					$audit_name 	= 'User '.$curr_det_u['fname'].' '.$curr_det_u['lname'];

					$audit_activity = sprintf( $this->lang->line('audit_trail_update'), $audit_name);

					$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
				}

				header('Location:'.base_url().'Auth/sign_in/'.base64_url_encode($user_detail['username']).'/'.base64_url_encode($user_detail['password']));
			}
			else
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ROLES;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_ROLES, $main_where);

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USER_ROLES, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_AGREEMENTS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, $main_where);

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH, $main_where);

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH, $main_where);

				$curr_detail[] 		= array();


				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_AUDIT_TRAIL;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_AUDIT_TRAIL, $main_where);

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_AUDIT_TRAIL, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_HISTORY;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_HISTORY, $main_where);

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USER_HISTORY, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, $main_where);

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USERS;
				$audit_action[] 	= AUDIT_DELETE;
				// $prev_detail[]  	= array();

				$curr_det_u 	= $this->users->get_user_details( $user_id );
				$prev_detail[]  	= array($curr_det_u);

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USERS, $main_where);
			
				$curr_detail[] 	= array();

				if( !EMPTY( $audit_schema ) )
				{
					$audit_name 	= 'User '.$curr_det_u['fname'].' '.$curr_det_u['lname'];

					$audit_activity = sprintf( $this->lang->line('audit_trail_delete'), $audit_name);

					$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
				}				

				// echo "<script type='text/javascript'>settimeout('self.close()',5000);</script>";
				header('Location:'.base_url().'unauthorized/invalid_link/1/');
			}

			SYSAD_Model::commit();

			$flag 					= 1;
			$msg 					=$this->lang->line( 'data_saved' );
			$status 	= SUCCESS;
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

			header('Location:'.base_url().'unauthorized/invalid_link');

		}
 	}

 	public function verify_code()
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

		$username 				= NULL;
		$password 				= NULL;
		$status 				= ERROR;

		try
		{

			$table 			= SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH;

			SYSAD_Model::beginTransaction();

			$audit_details 	= $this->authentication_factors->verify_code($table, $orig_params);

			if( !EMPTY( $audit_details['audit_table'] ) )
			{
				$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
				$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
				$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
				$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
				$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );
			}

			$curr_det_u 	= $audit_details['curr_det_u'];
			$configs 		= $audit_details['configs'];
			$username 		= $audit_details['username'];
			$password 		= $audit_details['password'];

			if( !EMPTY( $audit_schema ) )
			{

				$audit_name 	= 'User '.$curr_det_u['fname'].' '.$curr_det_u['lname'].' has verified '.$configs['header_txt'];

				$audit_activity = sprintf( $this->lang->line('audit_trail_update'), $audit_name);

				$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

				$status 				= SUCCESS;
				$flag 					= 1;
				$msg 					= $configs['header_txt'].' Verified.';
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
			'username'				=> $username,
			'password'				=> $password
		);

		echo json_encode( $response );

 	}

 	private function _filter_sign_up( array $orig_params )
 	{
 		$par 			= $this->set_filter( $orig_params )
 							->filter_string('gender')
							->filter_string('last_name')
							->filter_string('first_name')
							->filter_string('company_name')
							->filter_string('company_short_name')
							->filter_string('middle_initial')
							->filter_string('ext_name', TRUE)
							->filter_string('main_org')
							->filter_string('other_orgs')
							->filter_string('job_title')
							->filter_number('user_id', TRUE)
							->filter_email('email')
							->filter_email('email_hid')
							->filter_number('security_question_id', TRUE)
							->filter_string('username');

		$params 		= $par->filter();
		
		return $params;
 	}

 	public function sign_up_form( $orig_params = NULL, $trigger_next = FALSE)
 	{
 		$segments 			= array();
 		$lists 				= array();
 		$class_step 		= '';
 		$pass_data 			= array();

 		$ext_names 			= array();

 		$main_orgs 			= array();
		$other_orgs 		= array();
		$all_orgs 			= array();
		$security_questions = array();
		$client_basic_info 	= array();
		$client_id_info 	= array();
		$client_vc 			= array();
		$client_acc 		= array();

		$user_details 		= array();
		$user_id 			= NULL;
		$params 			= array();

		$required_mobile 	= FALSE;
		$disable_email_ver 	= '';
		$hide_resend_email 	= '';
		$email_form_label 	= 'Please verify your email by providing the verification code.';

		$disable_mob_ver 	= '';
		$hide_resend_mob 	= '';
		$mob_form_label 	= 'Please verify your mobile no. by providing the verification code.';

		$email_ver_data 	= array();
		$mob_ver_data 		= array();

		$sec_answers 		= array();

		$set_auto_username 	= FALSE;
		$username_gen 		= '';

		$email_domain_str 		= '';

 		try
		{

			$account_creator 	= get_setting(ACCOUNT, "account_creator");

			$username_creation 	= get_setting('USERNAME_CREATE', "username_creator");
			$system_title 		= get_setting(GENERAL, "system_title");

			$dpa_email_enable 				= get_setting( DPA_SETTING, 'dpa_email_enable' );
			$check_dpa_email_enable 		= ( !EMPTY( $dpa_email_enable ) ) ? TRUE : FALSE;
			$email_domain 					= get_setting( DPA_SETTING, 'email_domain' );

			if( $check_dpa_email_enable )
			{
				if( !EMPTY( $email_domain ) )
				{
					$email_domain_str 		= 'Email address with "'.$email_domain.'" domain is not allowed.';
				}
			}

			$login_sys_param 		= get_sys_param_val('LOGIN', 'LOGIN_WITH');

			$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

			$login_with_arr_sel 		= get_setting(LOGIN, 'login_api');
			$login_with_arr_sel 		= trim($login_with_arr_sel);

			$login_with_arr_a 		= array();

			if( !EMPTY( $login_with_arr_sel ) )
			{
				$login_with_arr_a 	= explode(',', $login_with_arr_sel);
			}

			$this->load->model(CORE_USER_MANAGEMENT.'/Organizations_model', 'orgs');

			$orig_params 	= base64_url_decode($orig_params);

			$orig_params 	= ( array ) json_decode( $orig_params );

			if( $username_creation == SET_SYSTEM_GENERATED )
			{
				$set_auto_username 		= TRUE;
			}

			$fb_pic 					= NULL;
			// var_dump($orig_params);die;
			if( EMPTY( $orig_params ) OR !ISSET($orig_params['user_id']) )
			{
				$or_par 		= array(
					'user_id'	=> '',
					'salt'		=> '',
					'token'		=> '',
					'action'	=> ''
				);

				if( !EMPTY( $orig_params ) )
				{
					$orig_params 	= array_merge($orig_params, $or_par);
				}
				else
				{
					$orig_params 	= $or_par;
				}
			}

			if( ISSET( $orig_params['picture'] ) )
			{
				if( ISSET( $orig_params['google'] ) )
				{
					if( !EMPTY( $orig_params['google'] ) )
					{

					}
					else
					{
						$fb_pic 		= $orig_params['picture'];
					}
				}

				unset($orig_params['picture']);
			}

			$params 		= $this->_filter_sign_up( $orig_params );

			$check_user_aggr	= FALSE;

			$org_details 		= array();

			if( ISSET( $params['user_id'] ) AND !EMPTY( $params['user_id'] ) )
			{
				check_salt( $params['user_id'], $params['salt'], $params['token'], $params['action'] );

				$user_id 	= $params['user_id'];

				$user_details = $this->users->get_user_details($user_id, $account_creator);

				$org_details_arr 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, array(
					'org_code'	=> $user_details['org_code']
				) );

				if( !EMPTY( $org_details_arr ) )
				{
					$org_details 	= $org_details_arr[0];
				}

				$sec_answers  = $this->users->get_security_answer_temp($user_id);
				
				if( $username_creation == SET_SYSTEM_GENERATED )
				{
					$username_gen 	= generate_username($user_details['fname'], $user_details['lname'], $user_id);
				}

				$temp_user_aggr 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS, array(
					'user_id'	=> $user_id
				) );

				if( !EMPTY($temp_user_aggr) )
				{
					if( !EMPTY( $temp_user_aggr[0]['agreement_flag'] ) )
					{
						$check_user_aggr = TRUE;
					}
				}
			}

			$client_basic_info 		= $this->validate_basic_info( array(), TRUE );
			$client_id_info 		= $this->validate_id_details(array(), TRUE);
			$client_vc 				= $this->validate_verify_code( array(), TRUE );
			$client_acc_det 		= $this->validate_account_details( array(), TRUE, $account_creator );

			$client_acc 			= $client_acc_det['clientSideAjd'];

			$cons_array 			= $client_acc_det['cons_array'];
			$user_cons_arr 			= $client_acc_det['user_cons_arr'];
			
			$resources['load_css']		= array( CSS_LABELAUTY, CSS_SELECTIZE, CSS_WIZARD );
			$resources['load_js']		= array( JS_LABELAUTY, JS_SELECTIZE, JS_NUMBER, JS_WIZARD, $this->module_sign_up_js );
			$resources['loaded_init']	= array( $client_basic_info['customJS'], $client_id_info['customJS'], $client_vc['customJS'], $client_acc['customJS'], 'Wizard.init();', 'SignUp.init_form("'.$trigger_next.'");', 'SignUp.resend();');

			if( !EMPTY( $cons_array ) )
			{
				$resources['loaded_init'][] = 'password_constraints( '.$cons_array.' );';
			}

			if( !EMPTY( $user_cons_arr ) )
			{
				if( $username_creation != SET_SYSTEM_GENERATED )    
				{
					$resources['loaded_init'][] = 'username_constraints( '.$user_cons_arr.' );';
				}
			}

			$resources['load_materialize_modal']	= array(
				 'modal_term_condition'  	=> array(
			    	'title' => "Terms and condition",
					'size' => "lg-w lg-h",
					'controller' => "auth",
					'method' => "modal_term_condition",
					'fixed_header' => false,
					// 'modal_footer' => false,
					'permission'	=> false,
					'footer_div_none'	=> true,
					'post'			=> true
		    	)
			);

			$auth_account_factors 	= get_setting(AUTH_FACTOR, 'auth_account_factor');
			$auth_account_factor_arr = array();

			if( !EMPTY( $auth_account_factors ) )
			{
				$auth_account_factor_arr = explode(',', $auth_account_factors);
			}

			$lists 			= array(
				'Basic_info', 'Identification Details'
			);

			$segments 		= array(
				'basic_info', 'identification_detail'
			);

			if( in_array(AUTHENTICATION_FACTOR_EMAIL, $auth_account_factor_arr) )
			{
				$lists[] 	= 'Email Verification Code';
				$segments[] = 'email_verification';
			}

			if( in_array(AUTHENTICATION_FACTOR_SMS, $auth_account_factor_arr) )
			{
				$lists[] 	= 'Mobile Verification Code';
				$segments[] = 'mobile_verification';
				$required_mobile = TRUE;
			}

			if( !EMPTY( $user_id ) )
			{
				$check_email_ver 	= $this->users->get_user_multi_auth($user_id, AUTHENTICATION_FACTOR_EMAIL, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH);

				$check_sms_ver 		= $this->users->get_user_multi_auth($user_id, AUTHENTICATION_FACTOR_SMS, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH);

				$email_ver_data 	= (ISSET($check_email_ver[0])) ? $check_email_ver[0] : array();
				$mob_ver_data  		= (ISSET($check_sms_ver[0])) ? $check_sms_ver[0] : array();

				if( !EMPTY( $check_email_ver ) AND !EMPTY( $check_email_ver[0]['authenticated_date'] ) )
				{
					$disable_email_ver = 'disabled';
					$hide_resend_email = 'hide';
					$email_form_label  = 'Email has been verified. Please proceed.';
				}

				if( !EMPTY( $check_sms_ver ) AND !EMPTY( $check_sms_ver[0]['authenticated_date'] ) )
				{
					$disable_mob_ver = 'disabled';
					$hide_resend_mob = 'hide';
					$mob_form_label  = 'Mobile No. has been verified. Please proceed.';
				}
			}

			$lists[] 		= 'Account Details';
			$segments[] 	= 'account_details';

			$class_step 				= trim( convertNumberToWord( count( $lists ) ) ).'-step';

			$ext_names 		= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_PARAM_EXTENSION_NAME, array());

			$orgs			= $this->orgs->get_orgs();

			$main_orgs_det 	= array();
			$other_orgs_det = array();

			if( !EMPTY( $user_id ) )
			{
				$main_orgs_det 					= $this->auth_model->get_user_organizations_temp($user_id, LOGGED_IN_FLAG_YES);

				$other_orgs_det 				= $this->auth_model->get_user_organizations_temp($user_id, LOGGED_IN_FLAG_NO);
			}

			if( !EMPTY( $main_orgs_det ) )
			{
				$main_orgs 					= array_column($main_orgs_det, 'org_code');
			}

			if( !EMPTY( $other_orgs_det ) )
			{
				$other_orgs 				= array_column($other_orgs_det, 'org_code');
			}
			
			$all_orgs 						= $this->orgs->get_orgs_all();
			$security_questions 			= $this->users->get_security_questions_temp($user_id);

			$data['ch_login_sys_param'] 	= $ch_login_sys_param;
			$data['login_with_arr_a'] 		= $login_with_arr_a;

			if( $ch_login_sys_param )
			{

				$fb_url 							= $this->facebook->login_url();
				$g_url 								= $this->google->login_url();
				
				$data['fb_url'] 					= $fb_url;
				$data['g_url'] 						= $g_url;
			}
			else
			{
				$data['fb_url'] 					= "";
				$data['g_url'] 						= "";	
			}

			$check_has_agreement_text	= get_setting( AGREEMENT, 'has_agreement_text' );
			$checked_term_conditions 	= get_setting(GENERAL, "term_conditions");
			$has_agreement_check 		= FALSE;

			if( !EMPTY($checked_term_conditions) OR ( !EMPTY( $check_has_agreement_text ) AND $check_has_agreement_text == DATA_PRIVACY_TYPE_BASIC ) )
			{
				$has_agreement_check 		= TRUE;
			}

			$data['api_reroute'] 					= base_url().'auth/api_reroute/';
			$not_req_sec_question 					= get_setting(AUTH_FACTOR, "not_req_sec_question");

			$data['not_req_sec_question']			= $not_req_sec_question;
			$data['has_agreement_check']			= $has_agreement_check;
			$data['check_user_aggr']				= $check_user_aggr;
			$data['system_title'] 					= $system_title;

			$data['users_gender_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_GENDER');
			$data['users_mname_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_MIDDLE_NAME');
			$data['users_ename_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_EXT_NAME');
			$data['users_job_title_inp'] 			= get_sys_param_val('USERS_INPUT', 'USERS_JOB_TITLE');
			$data['users_subs_notif_inp'] 			= get_sys_param_val('USERS_INPUT', 'USERS_PROD_SUBS_NOTIF');
			$data['sign_up_org_name'] 				= get_sys_param_val('USERS_INPUT', 'SIGN_UP_ORG_NAME');
			$data['sign_up_short_name'] 			= get_sys_param_val('USERS_INPUT', 'SIGN_UP_SHORT_NAME');
			$data['org_details'] 					= $org_details;
			
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);	
		}

		$data['all_orgs']				= $all_orgs;
		$data['lists']					= $lists;
		$data['class_step']				= $class_step;
		$data['segments']				= $segments;
		$data['main_orgs']				= $main_orgs;
		$data['other_orgs']				= $other_orgs;
		$data['ext_names']				= $ext_names;
		$data['security_questions'] 	= $security_questions;
		$data['client_basic_info'] 		= $client_basic_info;
		$data['client_id_info'] 		= $client_id_info;
		$data['client_vc'] 				= $client_vc;
		$data['user_details']			= $user_details;
		$data['params']				 	= $params;
		$data['orig_params']			= $orig_params;
		$data['required_mobile'] 		= $required_mobile;
		$data['disable_email_ver'] 		= $disable_email_ver;
		$data['hide_resend_email'] 		= $hide_resend_email;
		$data['email_form_label'] 		= $email_form_label;
		$data['disable_mob_ver'] 		= $disable_mob_ver;
		$data['hide_resend_mob'] 		= $hide_resend_mob;
		$data['mob_form_label'] 		= $mob_form_label;
		$data['email_ver_data'] 		= $email_ver_data;
		$data['mob_ver_data'] 			= $mob_ver_data;
		$data['client_acc'] 			= $client_acc;
		$data['set_auto_username'] 		= $set_auto_username;
		$data['username_gen'] 			= $username_gen;
		$data['email_domain_str'] 		= $email_domain_str;

		$data['pass_data']				= $data;
		$data['sec_answers'] 			= $sec_answers;

		$this->load->view('sign_up_form', $data);
		$this->load_resources->get_resource($resources);
 	}

 	public function validate_basic_info(array $params = array(), $forJs = FALSE)
 	{
 		try
 		{
 			$v 	= $this->core_v;

 			$sign_up_org_name 				= get_sys_param_val('USERS_INPUT', 'SIGN_UP_ORG_NAME');
			$sign_up_short_name 			= get_sys_param_val('USERS_INPUT', 'SIGN_UP_SHORT_NAME');

			if( !EMPTY( $sign_up_org_name['sys_param_value'] ) ) 
			{
				$v 	
    			->required(NULL, '#client_company_name')
    			->maxlength(255, '#client_company_name')->sometimes('sometimes', NULL, $forJs)
    			->check('company_name', $params);				
			}

			if( !EMPTY( $sign_up_short_name['sys_param_value'] ) ) 
			{
				$v 	
    			->required(NULL, '#client_company_short_name')
    			->maxlength(25, '#client_company_short_name')->sometimes('sometimes', NULL, $forJs)
    			->check('company_short_name', $params);				
			}

 			if( ISSET( $params['gender'] ) AND !EMPTY( $params['gender'] ) )
 			{
 				$v 
	 				// ->required()
	 				->in(array(MALE, FEMALE))
	 				->check('gender', $params);
	 		}

 			$v 	
    			->required(NULL, '#client_last_name')
    			->maxlength(100, '#client_last_name')->sometimes('sometimes', NULL, $forJs)
    			->check('last_name', $params);

 			$v 	
    			->required(NULL, '#client_first_name')
    			->maxlength(100, '#client_first_fname')->sometimes('sometimes', NULL, $forJs)
    			->check('first_name', $params);

    		if( ISSET( $params['middle_initial'] ) AND !EMPTY( $params['middle_initial'] ) )
 			{
    			$v 	
	    			->maxlength(50, '#client_middle_initial')->sometimes('sometimes', NULL, $forJs)
	    			->check('middle_initial', $params);
	    	}

	    	if( ISSET( $params['ext_name'] ) AND !EMPTY( $params['ext_name'] ) )
 			{

	    		$v 	
	    			->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_PARAM_EXTENSION_NAME.'|primary_id=param_extension_name')->sometimes('sometimes', NULL, $forJs)
	    			->check('ext_name', $params);
	    	}

    	/*	$v 
    			->maxlength(50, '#client_nickname')->sometimes('sometimes', NULL, $forJs)
    			->check('nickname', $params);

    		$v 	
    			->required(NULL, '#client_main_org')
    			->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ORGANIZATIONS.'|primary_id=org_code')->sometimes('sometimes', NULL, $forJs)
    			->check('main_org', $params);

    		$v 	
    			->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ORGANIZATIONS.'|primary_id=org_code')->sometimes('sometimes', NULL, $forJs)
    			->check('other_orgs', $params);*/

    		if( ISSET( $params['job_title'] ) AND !EMPTY( $params['job_title'] ) )
 			{
	    		$v 
	    			->maxlength(200, '#client_job_title')->sometimes('sometimes', NULL, $forJs)
	    			->check('job_title', $params);

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

 	public function validate_id_details(array $params = array(), $forJs = FALSE)
 	{
 		try
 		{
 			$v 	= $this->core_v;

 			$user_id = NULL; 

 			if(ISSET($params['user_id']) AND !EMPTY($params['user_id']))
 			{
 				$user_id = $params['user_id'];
 			}

 			$auth_account_factors 	= get_setting(AUTH_FACTOR, 'auth_account_factor');
			$auth_account_factor_arr = array();

			if( !EMPTY( $auth_account_factors ) )
			{
				$auth_account_factor_arr = explode(',', $auth_account_factors);
			}

 			$email = 'CAST('.aes_crypt('email', FALSE, FALSE).' AS char(100))';

 			$v 
 				->required(NULL, '#client_email')
 				->email(NULL, '#client_email')->sometimes('sometimes', NULL, $forJs)
 				->blacklist_email_domain()
 				->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_TEMP_USERS.'|primary_id='.$email.'|exclude_id=user_id|exclude_value='.$user_id, '@custom_error_The email address entered has already been used. Please use a different email.')->sometimes('sometimes', NULL, $forJs)
 				->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$email, '@custom_error_The email address entered has already been used. Please use a different email.')->sometimes('sometimes', NULL, $forJs)
 				->check('email', $params);

 			if( ISSET( $params['mobile_no'] ) )
 			{
				$mobile_no 	= $params['mobile_no'];

				if( !EMPTY( $mobile_no ) )
	 			{
	 				if( strlen($mobile_no) == 10 )  
	 				{
	 					$mobile_no = '0'.$mobile_no;
	 				}
	 			}

	 			$v->required(NULL, '#client_mobile_no', '#clientmessageonly_mobile_no')
 					->sometimes(function() use(&$auth_account_factor_arr)
 					{
 						return (in_array(AUTHENTICATION_FACTOR_SMS, $auth_account_factor_arr));
 					}, NULL, $forJs)
 				->mobileno('', FALSE, '#client_mobile_no')->sometimes('sometimes', NULL, $forJs)
 				->mobile_no_unique($user_id)->sometimes('sometimes', NULL, $forJs)
 				->mobile_no_unique($user_id, TRUE)->sometimes('sometimes', NULL, $forJs)
 				->check('mobile_no', $mobile_no);
	 		}

 			$check_ans = array();

 			$not_req_sec_question = get_setting(AUTH_FACTOR, "not_req_sec_question");

 			if( EMPTY( $not_req_sec_question ) )
 			{

	 			if( ISSET( $params['security_question_answers'] ) AND !EMPTY( $params['security_question_answers'] ) )
	 			{

		 			foreach( $params['security_question_answers'] as $sec_ans )
		 			{
		 				if( EMPTY( $sec_ans ) )
		 				{
		 					$check_ans[] = FALSE;
		 				}
		 				else
		 				{
		 					$check_ans[] = TRUE;
		 				}
		 			}
		 		}

	 			if( !EMPTY($check_ans) AND !in_array(TRUE, $check_ans, TRUE) )
	 			{
	 				throw new Exception('Please select your security question and indicate answer.');
	 			}
	 		}

	 		if( EMPTY( $not_req_sec_question ) )
	 		{

	 			$v 	
	 				->required()
	 				->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_SECURITY_QUESTIONS.'|primary_id=security_question_id')
	 				->check('security_question_id', $params);
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

 	public function validate_verify_code(array $params = array(), $forJs = FALSE)
 	{
 		try
 		{
 			$v 	= $this->core_v;

 			$v 
 				->required(NULL, '#client_email_verification_code')->sometimes('sometimes', NULL, $forJs)
 				->check('email_verification_code', $params); 			

 			$v 
 				->required(NULL, '#client_mobile_verification_code')->sometimes('sometimes', NULL, $forJs)
 				->check('mobile_verification_code', $params); 			

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
				$lower_length 		= $pass_const['constraint_lowercase'];
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
					'lower_length'	=> $lower_length,
					'digit_length'	=> $digit_length,
					'repeat_pass'	=> $repeat_pass,
					'pass_same'		=> 0,
					'spec_length' 	=> $spec_length,
					'letter_length' 	=> $letter_length,
					'lower_length' 	=> $lower_length
				);

				$cons_array 		= json_encode( $cons_array );
			}

 			$v 	= $this->core_v;

 			$v 
 				->required(NULL, '#client_username')
 				->check('username', $params);

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

 	public function save_sign_up()
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

		$update 				= ( ISSET( $orig_params['user_id'] ) AND !EMPTY( $orig_params['user_id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['user_id'] ) AND !EMPTY( $orig_params['user_id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$ins_val 				= array();

		$add  					= FALSE;

		$main_where 			= array();

		$user_id 				= NULL;
		$user_id_enc 			= NULL;
		$user_salt 				= NULL;
		$user_token 			= NULL;

		$action 				= NULL;

		$new_url 				= NULL;

		$disable_verify 		= false;
		$email_auth_fac_id 		= NULL;
		$mobile_auth_fac_id 	= NULL;

		$redirect_to_login 		= false;

		$ref 					= FALSE;

		try
		{
			$params 		 	= $this->_filter_sign_up( $orig_params );

			$account_creator 	= get_setting(ACCOUNT, "account_creator");

			if( ISSET( $params['tab_type'] ) AND !EMPTY( $params['tab_type'] ) )
			{
				switch( $params['tab_type'] )
				{
					case 'basic_info' :

						$this->validate_basic_info($params);

						if( ISSET( $params['gender'] ) AND !EMPTY( $params['gender'] ) )
						{

							$sp_gender 		= get_sys_param_code(SYS_PARAM_GENDER, filter_var($params['gender'], FILTER_SANITIZE_STRING));

							$ins_val['gender']= $sp_gender["sys_param_code"];
						}

						$ins_val['lname'] = array($params['last_name'], 'ENCRYPT');
						$ins_val['fname'] = array($params['first_name'], 'ENCRYPT');
						$ins_val['status']= STATUS_INCOMPLETE;
						$ins_val['product_subscription_notif_flag'] 	= ENUM_NO;

						if( ISSET( $params['subs_checkbox'] ) )
						{
							$ins_val['product_subscription_notif_flag'] = ENUM_YES;
						}

						if( !EMPTY($params['middle_initial']) )
						{
							$ins_val['mname'] = array($params['middle_initial'], 'ENCRYPT');		
						}

						if( !ISSET( $params['user_id'] ) OR EMPTY( $params['user_id'] ) )
						{
							$ref 		= TRUE;
						}

						if( ISSET($params['ext_name']) AND !EMPTY($params['ext_name']) )
						{
							$ins_val['ext_name'] = array($params['ext_name'], 'ENCRYPT');		
						}

					/*	if( !EMPTY($params['nickname']) )
						{
							$ins_val['nickname'] = array($params['nickname'], 'ENCRYPT');		
						}*/

						if( ISSET($params['job_title']) AND !EMPTY($params['job_title']) )
						{
							$ins_val['job_title'] = array($params['job_title'], 'ENCRYPT');		
						}

						if( ISSET($params['main_org']) AND !EMPTY($params['main_org']) )
						{

							$ins_val['org_code'] 	= $params['main_org'];
						}

						$ins_val['receive_sms_flag'] = ENUM_YES;	
						$ins_val['receive_email_flag'] = ENUM_YES;
						$ins_val['sign_up_api']				= NULL;

						if( ISSET( $params['api_sign_up'] ) AND !EMPTY( $params['api_sign_up'] ) )
						{
							if( !EMPTY($params['email_hid']) )
							{
								$ins_val['email'] = array($params['email_hid'], 'ENCRYPT');		
							}

							if( $params['api_sign_up'] == VIA_FACEBOOK )
							{
								$ins_val['facebook_email']		= array($params['email_hid'], 'ENCRYPT');		
							}
							else if( $params['api_sign_up'] == VIA_GOOGLE )
							{
								$ins_val['google_email']		= array($params['email_hid'], 'ENCRYPT');		
							}

							$ins_val['sign_up_api']		= $params['api_sign_up'];
						}

						/*if( ISSET( $params['receive_email'] ) )
						{
							$ins_val['receive_email_flag'] = ENUM_YES;
						}

						if( ISSET( $params['receive_sms'] ) )
						{
							$ins_val['receive_sms_flag'] = ENUM_YES;	
						}*/

					break;

					case 'identification_detail' :

						$this->validate_id_details($params);

						if( !EMPTY($params['email']) )
						{
							$ins_val['email'] = array($params['email'], 'ENCRYPT');		
						}

						if( !EMPTY($params['mobile_no']) )
						{
							$ins_val['mobile_no'] = array($params['mobile_no'], 'ENCRYPT');		
						}


					break;

					case 'verification' :
						$this->validate_verify_code($params);

						$ins_val['modified_date']	= date('Y-m-d H:i:s');
					break;

					case 'account_detail':
						$this->validate_account_details($params, FALSE, $account_creator);

						if( !EMPTY($params['username']) )
						{
							$ins_val['username'] = array(preg_replace('/\s+/', '', $params['username'] ), 'ENCRYPT');		
						}

						if( !EMPTY($params['password']) )
						{
							$clean_password 		= preg_replace('/\s+/', '', $params['password']);
							$pass_salt 				= gen_salt(TRUE);
							$ins_val['password'] 	= in_salt($clean_password, $pass_salt, TRUE);
							$ins_val["salt"] 		= $pass_salt;
						}

						if( $account_creator == VISITOR )
						{
							$ins_val['status']		= STATUS_PENDING;
						}
						else if( $account_creator == VISITOR_NOT_APPROVAL )
						{
							$ins_val['status']		= STATUS_ACTIVE;
						}

					break;
				}
			}

			SYSAD_Model::beginTransaction();

			$msg 					=$this->lang->line( 'data_saved' );

			if( !EMPTY( $ins_val ) )
			{
				if( !$update )
				{
					$ins_val['created_by']		= NULL;
					$ins_val['created_date']	= date('Y-m-d H:i:s');

					$add 				= TRUE;

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USERS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$user_id 			= $this->users->insert_helper( SYSAD_Model::CORE_TABLE_TEMP_USERS, $ins_val );

					$main_where 		= array(
						'user_id'		=> $user_id
					);

					$user_details 		= $this->users->get_user_details($user_id, $account_creator);

					$curr_detail[] 				= array($user_details);

					if( ISSET( $params['terms_checkbox'] ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[]  	= array();

						$date_now = date('Y-m-d H:i:s');

						$user_id 			= $this->users->insert_helper( SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS, array(
							'user_id'		=> $user_id,
							'agreement_flag'	=> 1,
							'agreed_date'		=> $date_now
						) );

						$curr_detail[] 				= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS, $main_where );
						
					}
				}
				else
				{
					check_salt($params['user_id'], $params['salt'], $params['token'], $params['action']);

					$user_id 			= $params['user_id'];

					$main_where 		= array(
						'user_id'		=> $user_id
					);

					$ins_val['modified_by']		= NULL;
					$ins_val['modified_date']	= date('Y-m-d H:i:s');

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USERS;
					$audit_action[] 	= AUDIT_UPDATE;
					$prev_detail[]  	= array($this->users->get_user_details($user_id, $account_creator));

					$this->users->update_helper( SYSAD_Model::CORE_TABLE_TEMP_USERS, $ins_val, $main_where );
					

					$user_details 		= $this->users->get_user_details($user_id, $account_creator);

					$curr_detail[] 				= array($user_details);
				}

				
				if( !EMPTY( $user_id ) )
				{
					if( ISSET( $params['company_short_name'] ) AND !EMPTY( $params['company_short_name'] ) )
					{
						$exists_org_code 	= ( ISSET( $params['exists_org_code'] ) AND !EMPTY( $params['exists_org_code'] ) ) ? $params['exists_org_code'] : NULL;
						$new_org_code 		= str_replace(' ', '_', $params['company_short_name']);
						$new_org_code 		= preg_replace('/[^A-Za-z0-9\_]/', '', $new_org_code);
						$new_org_code 		= strtoupper($new_org_code);

						$validator_db 		= $this->core_v->getValidator();
						$validate_org_code 	=  $validator_db
													->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS.'|primary_id=org_code|exclude_id=org_code|exclude_value='.$exists_org_code)
													->validate($new_org_code);

						if( !EMPTY( $validate_org_code ) )
						{
							$new_org_code 	= $new_org_code.$user_id;
						}

						$org_val 			= array(
							'org_code'		=> $new_org_code,
							'name'			=> $params['company_name'],
							'short_name'	=> $params['company_short_name']
						);

						if( EMPTY( $exists_org_code ) )
						{
							$org_val['created_by']		= NULL;
							$org_val['created_date']	= date('Y-m-d H:i:s');

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$this->users->insert_helper( SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $org_val );

							$main_org_where 		= array(
								'org_code'		=> $new_org_code
							);

							$curr_detail[] 				= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $main_org_where);

							$this->users->update_helper( SYSAD_Model::CORE_TABLE_TEMP_USERS, array('org_code' => $new_org_code), $main_where );

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$this->users->_insert_user_organizations_temp(array($new_org_code), $user_id, TRUE);

							$curr_detail[] 		= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $main_where);
						}
						else
						{
							unset($org_val['org_code']);
							$org_val['modified_by']		= NULL;
							$org_val['modified_date']	= date('Y-m-d H:i:s');

							$main_org_where 		= array(
								'org_code'		=> $exists_org_code
							);

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_UPDATE;
							$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $main_org_where);

							$this->users->update_helper( SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $org_val, $main_org_where );

							$curr_detail[] 				= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $main_org_where);
						}
					}

					if( $params['tab_type'] == 'basic_info' )
					{
						/*if( ISSET( $params['main_org'] ) AND !EMPTY( $params['main_org'] ) )
						{
							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_DELETE;
							$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $main_where);

							$this->users->delete_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, array('user_id' => $user_id, 'main_org_flag' => 1));

							$curr_detail[] 		= array();

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$this->users->_insert_user_organizations_temp(array($params['main_org']), $user_id, TRUE);

							$curr_detail[] 		= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $main_where);
						}

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $main_where);

						$this->users->delete_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, array('user_id' => $user_id, 'main_org_flag' => 0));

						$curr_detail[] 		= array();

						if(!EMPTY($user_id) AND 
							ISSET($params['other_orgs']) AND !EMPTY($params["other_orgs"])
							AND ISSET($params['other_orgs'][0]) AND !EMPTY($params["other_orgs"][0])
						)
						{
							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();
							
							$this->users->_insert_user_organizations_temp($params["other_orgs"], $user_id);

							$curr_detail[] 		= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $main_where);
						}*/

						$check_multi_auth_enable 	= $this->authentication_factors->check_authentication_factor_section_enabled(AUTH_SECTION_ACCOUNT);

						if( $check_multi_auth_enable )
						{
							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$this->authentication_factors->save_multi_auth_section_helper($user_id, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, AUTH_SECTION_ACCOUNT);

							$curr_detail[] 		= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, $main_where);

							$check_email_ver 	= $this->users->get_user_multi_auth($user_id, AUTHENTICATION_FACTOR_EMAIL, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH);

							$check_sms_ver 		= $this->users->get_user_multi_auth($user_id, 	AUTHENTICATION_FACTOR_SMS, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH);

							$email_ver_data 	= (ISSET($check_email_ver[0])) ? $check_email_ver[0] : array();
							$mob_ver_data  		= (ISSET($check_sms_ver[0])) ? $check_sms_ver[0] : array();

							if( !EMPTY( $email_ver_data ) )
							{
								$email_auth_fac_id = base64_url_encode($email_ver_data['authentication_factor_id']);
							}

							if( !EMPTY( $mob_ver_data ) )
							{
								$mobile_auth_fac_id = base64_url_encode($mob_ver_data['authentication_factor_id']);
							}
						}

					}
					else if( $params['tab_type'] == 'identification_detail' )
					{

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS, $main_where);

						$this->users->delete_data(SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS, $main_where);

						$curr_detail[] 		= array();

						if( ISSET( $params['security_question_answers'] ) AND !EMPTY($params['security_question_answers']) )
						{
							$sec_ans_val 	= $this->process_sec_ans_val($params['security_question_answers'], $params, $user_id);

							if( !EMPTY($sec_ans_val) )
							{
								$audit_schema[] 	= DB_CORE;
								$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS;
								$audit_action[] 	= AUDIT_INSERT;
								$prev_detail[]  	= array();

								$this->users->insert_helper( SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS, $sec_ans_val );

								$curr_detail[] 				= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS, $main_where);
							}

						}

						$multi_auth = $this->authentication_factors->check_user_multi_auth_section($user_id, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, AUTH_SECTION_ACCOUNT);

						if( !EMPTY( $multi_auth ) )
						{
							$auth_params = $this->authentication_factors->filter_verify_code($multi_auth);

							$this->authentication_factors->validate_vc($auth_params);

							$auth_params['generate_token']	= 1;

							$this->authentication_factors->update_multi_auth(SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, $auth_params, AUTH_SECTION_ACCOUNT, $account_creator);
						}
						
					}
					else if( $params['tab_type'] == 'verification' )
					{
						$table 			= SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH;

						$multi_auth = $this->authentication_factors->check_user_multi_auth_section($user_id, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, AUTH_SECTION_ACCOUNT);

						$disable_verify = TRUE;


						if( !EMPTY( $multi_auth ) )
						{
							if(ISSET($params['email_verification_code']))
							{
								$multi_auth['auth_code'] = $params['email_verification_code'];
							}
							else if(ISSET($params['mobile_verification_code']))
							{
								$multi_auth['auth_code'] = $params['mobile_verification_code'];	
							}
							
							$audit_details 	= $this->authentication_factors->verify_code($table, $multi_auth);

							if( !EMPTY( $audit_details['audit_table'] ) )
							{
								$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
								$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
								$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
								$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
								$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );
							}

							$multi_auth_rec = $this->authentication_factors->check_user_multi_auth_section($user_id, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, AUTH_SECTION_ACCOUNT);

							if( !EMPTY( $multi_auth_rec ) )
							{

								$auth_params = $this->authentication_factors->filter_verify_code($multi_auth_rec);

								$this->authentication_factors->validate_vc($auth_params);

								$auth_params['generate_token']	= 1;

								$this->authentication_factors->update_multi_auth(SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, $auth_params, AUTH_SECTION_ACCOUNT, $account_creator);
							}
						}
					}
					else if( $params['tab_type'] == 'account_detail' )
					{
						if( $account_creator == VISITOR )
						{
							$msg 	= $this->lang->line('sign_up_with_approval_success');
							$redirect_to_login = TRUE;
						}
						else if( $account_creator == VISITOR_NOT_APPROVAL )
						{
							$audit_details 		= $this->authentication_factors->insert_temp_user_to_main($user_id, $account_creator);

							if( !EMPTY( $audit_details['audit_table'] ) )
							{
								$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
								$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
								$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
								$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
								$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );
							}

							$msg 	= $this->lang->line('sign_up_without_approval_success');
							$redirect_to_login = TRUE;
						}
					}
				}

				$audit_name 				= 'User '.$user_details['fname'].' '.$user_details['lname'];

				$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

				$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

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

				$new_url 		= base_url().'Auth/sign_up_form/'.base64_url_encode(json_encode($url_arr)).'/';
			}

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			
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
			'user_id'				=> $user_id,
			'user_id_enc'			=> $user_id_enc,
			'user_salt' 			=> $user_salt,
			'user_token'			=> $user_token,
			'action'				=> $action,
			'new_url'				=> $new_url,
			'add'					=> $add,
			'disable_verify' 		=> $disable_verify,
			'email_auth_fac_id' 	=> $email_auth_fac_id,
			'mobile_auth_fac_id' 	=> $mobile_auth_fac_id,
			'redirect_to_login' 	=> $redirect_to_login,
			'ref' 					=> $ref
		);

		echo json_encode( $response );
 	}

 	protected function process_sec_ans_val(array $security_question_answers, $params, $user_id)
 	{
 		$arr 	= array();
 		try
 		{	
 			$cnt = 0;
 			foreach( $security_question_answers as $key => $sec_ans )
 			{
 				if( !EMPTY( $sec_ans ) )
 				{
 					if( ISSET( $params['security_question_id'][$key] ) )
 					{
 						$arr[$cnt]['user_id']	= $user_id;
 						$arr[$cnt]['security_question_id']	= $params['security_question_id'][$key];
 						$arr[$cnt]['answer']	= $sec_ans;

 						$cnt++;
 					}
 				}
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

 		return $arr;
 	}

 	public function modal_logout()
	{
		$data 		= array();
		$resources 	= array();

		$params 	= get_params();

		try
		{
			$data['username']	= $params['username'];
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error( $e, TRUE );
		}

		$this->load->view('modals/auto_logout', $data);
		$this->load_resources->get_resource($resources);
	}

	private function _filter_logout( array $orig_params )
	{
		$par 			= $this->set_filter( $orig_params )
							->filter_string('log_out_username')
							->filter_string('log_out_password');


		$params 		= $par->filter();

		return $params;
	}

	public function auto_logout()
	{
		$flag 	= 0;
		$msg 	= "";
		$status = ERROR;

		$orig_params 	= get_params();

		try
		{
			$params 	= $this->_filter_logout( $orig_params );

			$v 			= $this->core_v;

			$v->required()
				->check('log_out_username|Username', $params);

			$v->required()
				->check('log_out_password|Password', $params);

			$v->assert(FALSE);

			$this->authenticate->sign_in($params['log_out_username'], $params['log_out_password'], FALSE, TRUE, TRUE);

			$user_details				= $this->auth_model->get_active_user($params['log_out_username'], NULL, TRUE);
			
			if( !EMPTY( $user_details ) )
			{
				$this->auth_model->update_log($user_details['user_id'], LOGGED_IN_FLAG_NO);

				$flag 	= 1;
				$msg 	= $this->lang->line('self_logout_success');
			}
			else
			{
				$flag 	= 0;
				throw new Exception($this->lang->line('invalid_login'));
			}
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
		}

		$result 	= array(
			"flag" 		=> $flag,
			"msg" 		=> $msg,
			"status"	=> $status
		);

		echo json_encode( $result );
	}
}