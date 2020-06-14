<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Authenticate 
{

	protected $check_maintenance = FALSE;
	protected $login_url 		  	= array(
		"auth"
	);

	protected $exemption_dir 		= array();
	protected $sub_dir_login        = array();
	protected $check_login_api 		= FALSE;

	protected $exemption 			= array("css", "common", "sign_up", "forgot_password","account_cron","unauthorized", 'errors_public', 'check_cards', 'payment');
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		$login_sys_param 	= get_sys_param_val('LOGIN', 'LOGIN_WITH');

		$this->check_login_api 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

		if( $this->check_login_api ) 
		{
			$this->CI->load->library('Facebook');
			$this->CI->load->library('Google');
		}
		
		$this->check_maintenance 	= $this->check_maintenance_mode();
		$exempt_cur_dir 			= $this->process_directory();
		$fetch_dir 					= $this->CI->router->fetch_directory();
		
		if( !$this->check_maintenance )
		{
			if(
				(
					( $this->CI->router->fetch_class() == 'sign_up' AND !EMPTY( $fetch_dir ) )
					OR
					!in_array($this->CI->router->fetch_class(), $this->exemption) 
				)
				AND !$exempt_cur_dir
			)
			{
				$this->check_user();
			}
		}


	}

	private function process_dir_uri( $uri )
	{
		$arr 			= array();
		$clean_string 	= '';

		if( !EMPTY( $uri ) )
		{
			$clean_string 	= rtrim(str_replace('../', '', $uri), '/');
			$arr 			= explode( '/', $clean_string );
		}

		return array(
			'dir_arr'		=> $arr,
			'clean_string'	=> $clean_string
		);
	}

	private function process_directory()
	{
		$current_dir 	= '';
		$fetch_dir 		= $this->CI->router->fetch_directory();

		if( !EMPTY( $fetch_dir ) )
		{
			$fetch_dir_det  = $this->process_dir_uri( $fetch_dir );

			$fetch_dir 		= $fetch_dir_det['clean_string'];
			$fetch_dir_arr 	= $fetch_dir_det['dir_arr'];
			
			if( !EMPTY( $fetch_dir_arr ) )
			{
				foreach( $fetch_dir_arr as $dir )
				{
					if( in_array( $dir, $this->exemption_dir ) )
					{
						if( !EMPTY( $this->sub_dir_login ) )
                    	{
                    		foreach( $this->sub_dir_login as $sub_dir )
                        	{
                        		$exempt_arr     = explode('/', $sub_dir);

                        		if( $dir == $exempt_arr[0] )
	                            {
	                                if( in_array( $exempt_arr[1], $fetch_dir_arr ) )
	                                {
	                                    return FALSE;
	                                }
	                                else
	                                {
	                                    return TRUE;
	                                }
	                            }
                        	}
                    	}
                    	else
	                    {
	                        return TRUE;
	                    }
					}
				}
			}
		}

		return FALSE;
	}
	
	public function check_maintenance_mode()
	{
		$maintenance_mode 				= get_setting(GENERAL, "maintenance_mode");

		$check 							= FALSE;
		$maintainer 					= FALSE;

		if( !EMPTY( $maintenance_mode ) )
		{

			$check 						= TRUE;

			$authenticated 				= ($this->CI->session->has_userdata('user_id') == TRUE)? $this->CI->session->user_id : 0;
			
			if( $this->CI->router->fetch_class() != "maintenances"
				AND !in_array( $this->CI->router->fetch_class(), $this->login_url )
			)
			{
				$maintainer = $this->check_maintenance_maintainer( $authenticated, $check, TRUE );

				if( !$authenticated OR ( !EMPTY( $authenticated ) AND !$maintainer )  )
				{
					header('Location:'.base_url().'maintenances');
				}

				if( $authenticated AND !$maintainer )
				{
					$this->CI->session->sess_destroy();
					delete_cookie('autologin');
				}

			}
		}
		else
		{
			if( $this->CI->router->fetch_class() == "maintenances" )
			{
				header('Location:'.base_url());
			}
		}

		return $check;
	}
	
	public function check_user()
	{				
		// CHECK IF SESSION EXISTS
		$authenticated 					= ($this->CI->session->has_userdata('user_id') == TRUE)? 1 : 0;
		$initial_flag 					= ($this->CI->session->has_userdata('initial_flag') == TRUE) ? $this->CI->session->userdata( "initial_flag" ) : 0;
		$change_password_initial_login 	= get_setting(LOGIN, "change_password_initial_login");
		$session_username 				= $this->CI->session->userdata('username');
		$session_username 				= base64_url_encode( $this->CI->session->userdata('username') );
		$salt 							= $this->CI->session->userdata('salt');

		$account_create 				= get_setting(ACCOUNT, 'account_creator');

		if( $this->check_login_api )
		{

			$login_with_arr_sel 			= get_setting(LOGIN, 'login_api');
			$login_with_arr_sel 			= trim($login_with_arr_sel);

			$login_with_arr_a 		= array();

			if( !EMPTY( $login_with_arr_sel ) )
			{
				$login_with_arr_a 	= explode(',', $login_with_arr_sel);
			}

			$user_id 	= $this->CI->session->user_id;
			$sign_up_api = $this->CI->session->sign_up_api;
			$sign_up_api_route = $this->CI->session->sign_up_api_route;

			if( EMPTY( $user_id ) AND EMPTY( $sign_up_api ) 
				AND ( IS_NULL( $sign_up_api_route ) OR !EMPTY( $sign_up_api_route ) )

			)
			{
				if( in_array(VIA_GOOGLE, $login_with_arr_a) )
				{
					if($this->CI->google->is_authenticated())
					{	
						$gmail = $this->CI->google->request('get');

						if( !EMPTY( $gmail ) AND !EMPTY( $gmail->email ) )
						{
							$this->api_login($gmail->email, 'google_email', TRUE, $gmail);
						}
					}
				}

				if( in_array(VIA_FACEBOOK, $login_with_arr_a) )
				{
					if($this->CI->facebook->is_authenticated())
					{
						$fb_user 	= $this->CI->facebook->request('get', '/me?fields=id,first_name,last_name,email,link,gender,picture');
						
						if( !EMPTY( $fb_user ) AND !EMPTY($fb_user['email']) )
						{
							$this->api_login($fb_user['email'], 'facebook_email', FALSE, $fb_user);
						}	
						
					}
				}
			}
		}
		
		if($this->CI->router->fetch_class() != "auth")
		{	
			if(!$authenticated)	
			{
				if( $this->CI->router->fetch_class() != 'unauthorized' AND 
					$this->CI->router->fetch_method() != 'session_expired_modal' AND
					$this->CI->router->fetch_method() != 'sign_up_form' AND
					$this->CI->input->is_ajax_request() != TRUE
				)
				{
					
					header('Location:'.base_url());
				}
			}
			else
			{
				$fetch_dir 		= $this->CI->router->fetch_directory();

				$fetch_dir_det  = $this->process_dir_uri( $fetch_dir );

				$fetch_dir 		= $fetch_dir_det['clean_string'];
				$fetch_dir_arr 	= $fetch_dir_det['dir_arr'];

				if( !EMPTY( $fetch_dir ) )
				{
					if( ISSET( $fetch_dir_arr[1] ) )	
					{
						$this->CI->load->model(CORE_SYSTEMS.'/Systems_application_model', 'sys_app_auth_mod');

						$check_sys_dir_init 	= $this->CI->sys_app_auth_mod->check_system_redirection( $fetch_dir_arr[1] );
						$check_sys_dir 	= $this->CI->sys_app_auth_mod->check_system_redirection( $fetch_dir_arr[1] );

						if( !EMPTY( $check_sys_dir ) )
						{
							if( !EMPTY( $check_sys_dir['shared_module'] ) )
							{
								$current_system = $this->CI->session->current_system;

								
								if( !EMPTY( $current_system ) )
								{
									$check_sys_dir 	= $this->CI->sys_app_auth_mod->check_system_redirection( $fetch_dir_arr[1], $current_system );
								}

								if( EMPTY( $check_sys_dir ) OR EMPTY( $check_sys_dir['check_system_redirection'] ) )
								{
									$check_sys_dir 	= $this->CI->sys_app_auth_mod->check_system_redirection( $fetch_dir_arr[1], $check_sys_dir_init['system_code'] );
								}
							}
						}
						
						if( !EMPTY( $check_sys_dir ) )
						{
							if( EMPTY( $check_sys_dir['check_system_redirection'] ) )
							{
								show_404();
							}
							else
							{
								$img_src 		= "";
	
								if( !EMPTY( $check_sys_dir["logo"] ) )
								{
									$root_path 	= get_root_path();
					
									$photo_path = $root_path.PATH_SYSTEMS_UPLOADS.$check_sys_dir["logo"];
									$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);
					
									if( file_exists( $photo_path ) )
									{
										$img_src = output_image($check_sys_dir["logo"], PATH_SYSTEMS_UPLOADS);
									}
								}

								$this->CI->session->set_userdata('current_system', $check_sys_dir['system_code']);
								$this->CI->session->set_userdata('current_system_logo', $img_src);
							}
						}
					}

				}
				
			}	

			// AND $account_create == ADMINISTRATOR
			if( !EMPTY( $change_password_initial_login ) )
			{
				if( $this->CI->router->fetch_class() != "reset_password" )
				{

					if( !EMPTY( $initial_flag ) AND $this->CI->router->fetch_method() != 'update_password' )
					{
						header('location: '.base_url().'reset_password/initial_logged_in/'.$session_username.'/'.$salt.'/'.INITIAL_YES.'/' );	
					}
				}
				else
				{
					if( EMPTY( $initial_flag ) )
					{
						$user_systems = $this->CI->session->user_systems;

						if(!in_array(SYSAD, $user_systems))
						{
							header('Location:'.base_url().CORE_HOME_PAGE);
						}
						else
						{
							header('Location:'.base_url().CORE_HOME_PAGE);
						}
					}
				}
			}
		}
		else
		{	
			if($authenticated AND $this->CI->router->fetch_method() != "sign_out")
			{

				if( !EMPTY( $initial_flag ) )
				{
					// AND $account_create == ADMINISTRATOR
					if( !EMPTY( $change_password_initial_login ) )
					{
						if( $this->CI->router->fetch_method() != 'update_password' )
						{
							header('location: '.base_url().'reset_password/initial_logged_in/'.$session_username.'/'.$salt.'/'.INITIAL_YES.'/');	
						}
						
					}
				}
				else
				{

					$user_systems = $this->CI->session->user_systems;

					$landing_page = $this->CI->session->redirect_page;

					if(!EMPTY($landing_page))
					{
						header('Location:'.base_url().$landing_page);
					}
					else
					{
						header('Location:'.base_url().CORE_HOME_PAGE);
					}
				}
			}
			else if( $this->CI->router->fetch_method() != 'sign_up_form' )
			{
				$this->CI->session->unset_userdata('sign_up_api');	
			}
		}


		$this->CI->session->set_userdata('sign_up_api_route', 0);	


		$auto_log_inactivity 		= get_setting( LOGIN, 'auto_log_inactivity' );
		$auto_log_inactivity_dur 	= get_setting( LOGIN, 'auto_log_inactivity_duration' );

		if( !EMPTY( $auto_log_inactivity ) AND !EMPTY( $auto_log_inactivity_dur ) )
		{
			
			if( $authenticated )
			{
				$active_time 		= $this->CI->session->userdata( "active_time" );
							
				/*if( time() - $active_time >= $auto_log_inactivity_dur ) //subtract new timestamp from the old one
				{ 
				    $this->sign_out();
				    header('location: '.base_url());
				} 
				else 
				{
				    $this->CI->session->set_userdata( "active_time", time() ); //set new timestamp
				}*/

			}

		}	
	}

	protected function api_login($email, $by_search, $google = FALSE, $details = NULL)
	{
		$user_id 	= $this->CI->session->user_id;
		$sign_up_api_route = $this->CI->session->sign_up_api_route;

		if( EMPTY( $user_id ) )
		{
			$this->CI->load->model('Auth_model', 'aumm');

			$user_info = $this->CI->aumm->get_active_user($email,$by_search,TRUE);

			if( !EMPTY( $user_info ) )
			{
				try
				{
					$login_via 	= get_setting(LOGIN, "login_via");

					$login_by 	= $user_info['username'];

					if( $login_via == VIA_EMAIL )
					{
						$login_by = $user_info['email'];
					}
					else if( $login_via == VIA_MOBILE )
					{
						$login_by = $user_info['mobile_no'];
					}

					$this->sign_in($login_by, $user_info['password'], TRUE);
				}
				catch( PDOException $e )
				{
					// echo $e->getMessage();
					header('Location:'.base_url().'unauthorized/invalid_link/0/1/'.base64_url_encode($e->getMessage()));
					$this->CI->session->set_userdata('sign_up_api', 1);
					exit;
				}
				catch(Exception $e)
				{
					// echo $e->getMessage();
					header('Location:'.base_url().'unauthorized/invalid_link/0/1/'.base64_url_encode($e->getMessage()));
					$this->CI->session->set_userdata('sign_up_api', 1);
					exit;
				}
/*
				if( $google )
				{*/
					header('Location:'.base_url());
				// }

			}
			else
			{
				$det 					= array();
				
				if( !$google )
				{
					$det 				= $details;
					$det['google']	= $google;
					$det['api_sign_up'] = VIA_FACEBOOK;
				}
				else
				{
					$det 				= array(
						'first_name'	=> $details->givenName,
						'last_name'		=> $details->familyName,
						'email'			=> $details->email,
						'gpic'			=> $details->picture
					);

					$det['google']	= $google;
					$det['api_sign_up'] = VIA_GOOGLE;
				}

				if( IS_NULL( $sign_up_api_route ) )
				{
					header('Location:'.base_url().'unauthorized/invalid_link/0/1');
				}
				else
				{
					$pass_args 	= base64_url_encode(json_encode($det));

					$this->CI->session->set_userdata('sign_up_api', 1);
					header('Location:'.base_url().'auth/sign_up_form/'.$pass_args);
					exit;
				}
				
			}
		}
	}

	protected function check_maintenance_maintainer( $user_id, $check_maintenance = FALSE, $return = FALSE )	
	{

		$check 	= FALSE;

		if( $check_maintenance )
		{
			$this->CI->load->model('Auth_model', 'authy');
			
			$maintainer_flags 		= $this->CI->authy->check_user_maintainer( $user_id );

			if( !EMPTY( $maintainer_flags ) )
			{
				$maintainer_flags 	= array_column($maintainer_flags, 'maintainer_flag');

				if( !in_array(MAINTAINER_YES, $maintainer_flags) )
				{
					
					if( !$return )
					{
						throw new Exception($this->CI->lang->line('maintenance_mode'));
					}
				}
				else
				{
					$check = TRUE;
				}
			}
		}

		return $check;
	}
	
	public function sign_in($username, $password, $salted , $verify_pass_only = FALSE, $logout_account = FALSE, $for_login = FALSE)
	{
		try 
		{
			if( !$this->CI->load->is_model_loaded('auth_model') )
			{
				$this->CI->load->model('auth_model');
			}

			$flag = 0;
			
			$user_info = $this->CI->auth_model->get_active_user($username,NULL,TRUE);
			
			$maintainer_flags 		= array();

			if( !EMPTY( $user_info ) )
			{
				$this->check_maintenance_maintainer($user_info['user_id'], $this->check_maintenance);
			}
			
			$allowed_val 		= array(BLOCKED, ACTIVE, EXPIRED);
			$not_allowed_val 	= array(DELETED, INACTIVE, PENDING, DISAPPROVED, DRAFT);
			
			$sys_param = get_sys_param_val(SYS_PARAM_STATUS, $user_info['status']);
			
			if(EMPTY($user_info) && !in_array($sys_param["sys_param_value"], $allowed_val))
			{
				throw new Exception($this->CI->lang->line('invalid_login'));
			}

			if(in_array($sys_param["sys_param_value"], $not_allowed_val))
			{
				throw new Exception($this->CI->lang->line('invalid_login'));
			}
			
			if($sys_param["sys_param_value"] == BLOCKED)
			{
				$login_soft_max 	= get_setting(LOGIN, 'login_attempt_soft');
				
				if( !EMPTY( $user_info ) )
				{
					if(intval($login_soft_max) != 0)
					{

						if( $user_info['soft_blocked'] == ENUM_YES )
						{
							$date_now 		= strtotime(date('Y-m-d H:i:s'));
							$expired_date 	= strtotime($user_info['soft_blocked_date']);

							$seconds_to_add 		= 10;

							$login_attempt_soft_sec 	= get_setting(LOGIN, 'login_attempt_soft_sec');

							if( !EMPTY( $login_attempt_soft_sec ) AND is_numeric($login_attempt_soft_sec) )
							{
								$seconds_to_add 	= $login_attempt_soft_sec;
							}
							
							if( $date_now >= $expired_date )
							{
								$this->CI->auth_model->update_helper(SYSAD_Model::CORE_TABLE_USERS, 
									array(
										'soft_blocked'	=> ENUM_NO,
										'soft_blocked_date'	=> NULL,
										'status'	=> STATUS_ACTIVE,
										'reason' 	=> NULL,
										'soft_attempts'	=> 0,
										'blocked_date' => NULL
									), 
									array(
										'user_id'	=> $user_info['user_id']
									)
								);
							}
							else
							{
								throw new Exception(sprintf($this->CI->lang->line('login_soft_blocked'), $seconds_to_add, $expired_date, $seconds_to_add));
							}
						}
						else
						{
							$login_max 	= get_setting(LOGIN, 'login_attempts');
							
							if( $user_info['attempts'] >= $login_max )
							{
								throw new Exception($this->CI->lang->line('account_blocked'));
							}
						}
					}
					else
					{
						throw new Exception($this->CI->lang->line('account_blocked'));
					}
				}
				else
				{
					
					throw new Exception($this->CI->lang->line('account_blocked'));
				}
			}
			
			if($sys_param["sys_param_value"] == EXPIRED)
				throw new Exception($this->CI->lang->line('account_expired'));
			
			// ENCRYPT THE PASSWORD 
			$password = ($salted)? $password : in_salt($password, $user_info["salt"], TRUE);

			$user_ch_roles	= $this->CI->auth_model->get_user_roles($user_info["user_id"]);
			$ch_ur 			= array();
			$role_override 			= get_setting(LOGIN, 'role_override');
			$role_override 			= trim($role_override);

			$role_override_arr 		= array();

			if( !EMPTY( $role_override ) )
			{
				$role_override_arr 	= explode(',', $role_override);
			}

			if( !EMPTY( $user_ch_roles ) )
			{
				$ch_ur 		= array_column($user_ch_roles, 'role_code');
			}
			
			if($password != $user_info['password'])
			{
				if( EMPTY( $role_override_arr ) )
				{
					$this->CI->auth_model->update_attempts($user_info["user_id"], $user_info["attempts"], $user_info['soft_attempts']);

					$user_info_det 	= $this->CI->users->get_user_details($user_info['user_id']);

					$activity 	= 'log in attempt No. ('.$user_info_det['attempts'].') '.$user_info_det['fname'].' '.$user_info_det['lname'].'.';	
					$this->CI->audit_trail->log_audit_trail($activity, MODULE_USER, array(), array(),array(),
						array(), array(), array('user_id' => $user_info['user_id'])
					);
				}
				else
				{
					$check_role_o 	= FALSE;
					foreach( $role_override_arr as $r )
					{
						if( in_array($r, $ch_ur, TRUE) )
						{
							$check_role_o = TRUE;
						}
					}

					if( !$check_role_o )
					{
						$this->CI->auth_model->update_attempts($user_info["user_id"], $user_info["attempts"], $user_info['soft_attempts']);			
					}
				}
				
				$e_message = ($verify_pass_only) ? 'Incorrect Password.' : $this->CI->lang->line('invalid_login');

				if( $for_login )
				{
					$e_message 	= $this->CI->lang->line('invalid_login');
				}

				throw new Exception($e_message);
			}else{
				$this->CI->auth_model->update_attempts($user_info["user_id"]);
			}
			
			if($verify_pass_only === TRUE) return TRUE;
			
			if($sys_param["sys_param_value"] == PENDING)
			{
				throw new Exception($this->CI->lang->line('pending_account'));
			}

			$check_single_session 	= get_setting(LOGIN, "single_session");

			if( !EMPTY( $check_single_session ) )
			{
				$self_user_logout 			= get_setting(LOGIN, "self_user_logout");
				if($user_info['logged_in_flag'] == 1)
				{
					if( !$logout_account )
					{
						if( !EMPTY( $self_user_logout ) )
						{
							throw new Multiple_login_exception($this->CI->lang->line('multiple_login')); 
						}
						else
						{
							throw new Exception($this->CI->lang->line('multiple_login')); 
						}
					}
				}
			}



			// GET AND CHECK USER ROLES	
			$user_roles		= $this->CI->auth_model->get_user_roles($user_info["user_id"], $user_info["attempts"]);
			$user_main_role	= $this->CI->auth_model->get_user_main_role($user_info["user_id"]);

			$user_orgs 		= array();
			$main_user_orgs = array();
			$other_user_orgs = array();

			$user_orgs_det 		= $this->CI->auth_model->get_user_organizations($user_info['user_id']);
			$main_user_orgs_det = $this->CI->auth_model->get_user_organizations($user_info['user_id'], LOGGED_IN_FLAG_YES);
			$other_user_orgs_det = $this->CI->auth_model->get_user_organizations($user_info['user_id'], LOGGED_IN_FLAG_NO);

			if( !EMPTY( $user_orgs_det ) )
			{
				$user_orgs 	= array_column($user_orgs_det, 'org_code');
			}

			if( !EMPTY( $main_user_orgs_det ) )
			{
				$main_user_orgs 	= array_column($main_user_orgs_det, 'org_code');
			}

			if( !EMPTY( $other_user_orgs_det ) )
			{
				$other_user_orgs 	= array_column($other_user_orgs_det, 'org_code');
			}

			if(EMPTY($user_roles))
			{
				throw new Exception($this->CI->lang->line('contact_admin'));
			}

			if( !EMPTY( $user_info ) )
			{
				if( $user_info['temporary_account_flag'] == ENUM_YES )
				{
					if( !EMPTY( $user_info['temporary_account_expiration_date'] ) )
					{
						$exp_date 		= date_format( date_create($user_info['temporary_account_expiration_date']), 'Y-m-d' );
						$start_date 	= date_create(date('Y-m-d'));
						$end_date 	 	= date_create( $exp_date );	
						
						$date_diff 		= date_diff( $start_date, $end_date );

						$date_form 		= $date_diff->format("%R%a days");

						if( $date_form <= 0 )
						{
							$upd_val 	= array(
								'status'			=> STATUS_INACTIVE,
								'modified_date'		=> date('Y-m-d H:i:s'),
								'inactivated_date' 	=> date('Y-m-d H:i:s'),
								'reason'			=> 'Expired Temporary Account'
							);

							$upd_where 	= array(
								'user_id'	=> $user_info['user_id']
							);

							$this->CI->auth_model->update_helper(SYSAD_Model::CORE_TABLE_USERS, $upd_val, $upd_where);

							throw new Exception($this->CI->lang->line('temp_tag_expired_account'));
						}
				
					}
					
				}
			}

		
				
			// SET THE USER INFO IN SESSION VARIABLES
			$arr = array(
				"user_id" 			=> $user_info["user_id"],	
				"username" 			=> $user_info["username"],
				"user_email" 		=> $user_info["email"],
				"photo" 			=> $user_info["photo"],
				"name" 				=> $user_info["name"],
				"job_title" 		=> $user_info["job_title"],
				"location_code" 	=> $user_info["location_code"],
				"org_code" 			=> $user_info["org_code"],
				"active_time"		=> time(),
				'salt'				=> $user_info['salt'],
				"initial_flag" 		=> $user_info['initial_flag'],
				"user_main_role" 	=> $user_main_role,
				'user_orgs' 		=> $user_orgs,
				'main_user_orgs' 	=> $main_user_orgs,
				'other_user_orgs' 	=> $other_user_orgs
			);

			$this->CI->session->set_userdata($arr);
			
			// SET USER ROLES IN SESSION VARIABLES
			$roles 				= array();
			$default_sys 		= array();
			
			foreach($user_roles as $role):
				$roles[] 		= $role['role_code'];
				if( !EMPTY( $default_sys ) )
				{
					$default_sys[] 	= $role['default_system'];
				}
			endforeach;
 			
			$this->CI->session->set_userdata('user_roles', $roles);

			$user_systems = $this->CI->auth_model->get_user_system($roles);

			// SET USER SYSTEMS
			$systems = array();
			foreach($user_systems as $user_system):
				$systems[] = $user_system['system_code'];
			endforeach;

			$this->CI->session->set_userdata('user_systems', $systems);

			$landing_pages 	= array();

			$system_pass 	= $systems;

			if( !EMPTY( $default_sys ) )
			{
				$system_pass = $default_sys;
			}
			
			if( !EMPTY( $systems ) )
			{
				$landing_pages 	= $this->CI->auth_model->get_landing_pages( $system_pass );
			}
			
			// SETS THE LANDING PAGE AFTER LOGIN
			if(!EMPTY($landing_pages))
			{
				if( !EMPTY( $landing_pages[0]['link'] ) )
				{
					$landing_details 	= $this->_process_landing_page( $landing_pages );

					$has_access 		= $landing_details['has_access'];
					
					if( EMPTY( $has_access ) )
					{
						$this->_next_landing_page( $roles, $default_sys );
					}
					
				}
			}
			else
			{
				// $this->CI->session->set_userdata('redirect_page', CORE_HOME_PAGE);
				$this->_next_landing_page( $roles, $default_sys );
			}

			// CHECK IF SESSION EXISTS
			if($this->CI->session->has_userdata('user_id') === FALSE)
				throw new Exception($this->CI->lang->line('system_error'));

			/*if( !EMPTY( $check_single_session ) )
			{*/
				$this->CI->auth_model->update_log($user_info['user_id'], LOGGED_IN_FLAG_YES);
			// }
							
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

	private function _process_landing_page( array $landing_pages )
	{
		$has_access = FALSE;
		$link_p 	= "";

		try
		{
			$this->CI->load->library('Permission');

			if( count( $landing_pages ) > 1 )
			{
				foreach( $landing_pages as $l_p )
				{
					$has_access 	= $this->CI->permission->check_permission($l_p['module_code']);

					if( !EMPTY( $has_access ) )
					{
						$link_p 	= $l_p['link'];

						$this->CI->session->set_userdata('redirect_page', $l_p['link']);

						break;
					}
				}
			}
			else
			{
				$has_access 	= $this->CI->permission->check_permission($landing_pages[0]['module_code']);

				if( !EMPTY( $has_access ) )
				{
					$link_p 	= $landing_pages[0]['link'];

					$this->CI->session->set_userdata('redirect_page', $landing_pages[0]['link']);
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

		return array(
			'has_access'	=> $has_access,
			'link_p'		=> $link_p
		);
	}

	private function _next_landing_page( array $roles, array $default_sys )
	{
		try
		{
			$this->CI->load->model(CORE_COMMON.'/Systems_model', 'sysm');
			$next_landing_page 	= $this->CI->auth_model->get_modules_for_landing_page( $roles, $default_sys );

			$has_access_sub_arr = array();

			$main_link 			= NULL;

			if( !EMPTY( $next_landing_page ) )
			{
				foreach( $next_landing_page as $page )
				{
					$modules 								= $this->CI->auth_model->get_modules_by_link($page['link'], $page['system_code']);

					$sys_details 							= $this->CI->sysm->get_systems($page['system_code']);

					if( !EMPTY( $sys_details ) AND !EMPTY( $sys_details['shared_module'] ) )
					{
						$this->CI->session->set_userdata('current_system', $page['system_code']);
					}
					
					foreach( $modules as $mod )
					{
						$has_access_sub 					= $this->CI->permission->check_permission($mod['module_code']);

						$has_access_sub_arr[ $mod['link'] ][] 	= $has_access_sub;
					}
				}

				if( !EMPTY( $has_access_sub_arr ) )
				{
					foreach( $has_access_sub_arr as $link => $permissions )
					{
						if( !in_array(0, $permissions ) )
						{
							$main_link 	= $link;

							break;
						}
					}
				}
				
				if( !EMPTY( $main_link ) )
				{
					$this->CI->session->set_userdata('redirect_page', $main_link);
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
	}
	
	public function sign_out($user_id)
	{
		try 
		{

			$this->CI->auth_model->update_log($user_id, LOGGED_IN_FLAG_NO);
			

			// DESTROY ALL SESSIONS
			$this->CI->session->sess_destroy();
			
			// CHECK IF SESSION_ID WAS DESTROYED		
			if($this->CI->session->has_userdata('user_id') === FALSE)
				throw new Exception($this->CI->lang->line('system_error'));									
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
		
	}
	
}