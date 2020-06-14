 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This class is use to check if there are multi-authentication factors set
 * 
 * @author asiagate
 */
class Authentication_factors 
{
	protected $CI; //  Codeigniter instane
	protected $BC;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'auth_users');


		$this->BC = new Base_Controller;
	}

	/**
	 * Use This helper function to save in either table ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH ) based in account setting section auth factor ( AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
	 * 
	 * 
	 * @param  $user_id -- required. id of the user
	 * @param  $table -- required. ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
	 * @throws PDOException
	 * @throws Exception
	 * @return array - audit trail
	 */
	public function save_multi_auth_section_helper($user_id, $table, $auth_section)
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			$audit_details 		= $this->CI->auth_users->save_multi_auth_section_helper($user_id, $table, $auth_section);

			if( !EMPTY( $audit_details['audit_table'] ) )
			{
				$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
				$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
				$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
				$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
				$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );
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

		return array(
			'prev_detail'	=> $prev_detail,
			'curr_detail'	=> $curr_detail,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'audit_schema'	=> $audit_action
		);
	}


	/**
	 * Use This helper function to save in either table ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH ) based in account setting general auth  factor
	 * 
	 * 
	 * @param  $user_id -- required. id of the user
	 * @param  $table -- required. ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
	 * @throws PDOException
	 * @throws Exception
	 * @return array - audit trail
	 */
	public function save_multi_auth_helper($user_id, $table)
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			$audit_details 		= $this->CI->auth_users->save_multi_auth_helper($user_id, $table);

			if( !EMPTY( $audit_details['audit_table'] ) )
			{
				$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
				$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
				$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
				$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
				$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );
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

		return array(
			'prev_detail'	=> $prev_detail,
			'curr_detail'	=> $curr_detail,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'audit_schema'	=> $audit_action
		);
	}

	/**
	 * Use This helper function to check if authenticated user has already verified the authentication factor set based in authentication factor section either in (AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD).
	 * 
	 * 
	 * @param  $user_id -- required. ( id of the user )
	 * @param  $table -- required. ( either SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
	 * @param  $auth_section -- required. ( either AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
	 * @return array
	 */
	public function check_user_multi_auth_section($user_id, $table, $auth_section)
	{
		$multi_auth = check_user_multi_auth_section($user_id, $table, $auth_section);

		return $multi_auth;
	}

	/**
	 * Use This helper function to check if there are authentication factor per section
	 * 
	 * 
	 * @param  $auth_section -- required. ( either AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
	 * @return boolean
	 */
	public function check_authentication_factor_section_enabled($auth_section)
	{
		$check_multi_auth_enable 	= get_setting_authentication_factor($auth_section);

		return (!EMPTY( $check_multi_auth_enable ));
	}

	/**
	 * Use This helper function to filter input from auth factors
	 * 
	 * 
	 * @param  $orig_params array -- required. post data
	 * @throws PDOException
	 * @throws Exception
	 * @return array - post data
	 */
	public function filter_verify_code(array $orig_params)
	{
		$params 	= array();
		try
		{
			$par 	= $this->BC->set_filter($orig_params)
						->filter_number('authentication_factor_id', TRUE)
						->filter_number('user_id', TRUE)
						->filter_string('auth_code');

			$params 	= $par->filter();
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}

		return $params;
	}

	/**
	 * Use This helper function to validate input from auth factors
	 * 
	 * 
	 * @param  $params array -- required. post data
	 * @param  $save DEFAULT FALSE -- required. checks if validation is for saving or not
	 * @throws PDOException
	 * @throws Exception
	 * @return array - post data
	 */
	public function validate_vc(array $params, $save = FALSE)
	{
		try
		{
			$v 	= $this->BC->core_v;
			
			$v
				->required()
				->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id=user_id')
				->check('user_id', $params);

			$v
				->required()
				->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTORS.'|primary_id=authentication_factor_id')
				->check('authentication_factor_id', $params);

			if( $save )
			{
				$v 
					->required()
					->check('auth_code|Code', $params);
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

	/**
	 * Use This helper function to update the code of user multi auth and to update the expiration date either based on general auth factors or by auth section factors. Mainly used in sending verification code or resend code
	 * 
	 * 
	 * @param  $table -- -- required. ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
	 * @param  $pass_args array -- required. data to be validated,filtered,saved
	 * @param  $auth_section -- required. ( AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
	 * @throws PDOException
	 * @throws Exception
	 * @return array - audit trail
	 */
	public function update_multi_auth($table, array $pass_args = array(), $auth_section = NULL, $account_creator = NULL)	
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		
		try
		{
			$params 			= $pass_args;
			// $account_creator 	= get_setting(ACCOUNT, "account_creator");

			if( !EMPTY( $pass_args ) )
			{
				$configs 					= auth_factor_config($params['authentication_factor_id']);

				$upd_val 		= array(
					'user_id'	=> $params['user_id'],
					'authentication_factor_id'	=> $params['authentication_factor_id']
				);

				if( ISSET( $params['generate_token'] ) )
				{
					$gen_code 	= generate_verify_code();

					if( !EMPTY( $auth_section ) )
					{
						$get_auth_section_map 	= get_auth_section_map($auth_section);
						$auth_code_decay 		= get_setting(AUTH_FACTOR, $get_auth_section_map['code']);
					}
					else
					{
						$auth_code_decay 	= get_setting(AUTH_FACTOR, 'auth_login_code_decay');
					}

					$minutes_to_add 		= 5;

					if( !EMPTY( $auth_code_decay ) AND is_numeric($auth_code_decay) )
  					{
  						$minutes_to_add 	= $auth_code_decay;
  					}
  					
					$time = new DateTime();
					$time->add(new DateInterval('PT' . $minutes_to_add . 'M'));

					$expired_date = $time->format('Y-m-d H:i:s');

					$upd_val['code']			= base64_url_encode($gen_code);
					$upd_val['expired_date']	= $expired_date;

					$params['generated_code']	= $gen_code;
				}

				$main_where = array(
					'user_id'					=> $params['user_id'],
					'authentication_factor_id'	=> $params['authentication_factor_id']
				);

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= $table;
				$audit_action[] 	= AUDIT_UPDATE;
				$prev_detail[]  	= $this->CI->auth_users->get_details_for_audit( $table,
												$main_where
											 );

				$this->CI->auth_users->update_helper($table, $upd_val, $main_where);

				$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( $table,
												$main_where
											 );

				if( ISSET( $params['generate_token'] ) )
				{
					if( !EMPTY( $configs ) AND !EMPTY( $configs['send_function_method'] ) )
					{
						$this->{ $configs['send_function_method'] }( $params, $account_creator );
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

		return array(
			'prev_detail'	=> $prev_detail,
			'curr_detail'	=> $curr_detail,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'audit_schema'	=> $audit_action
		);
	}

	/**
	 * Use This helper function to send sms verifaction code
	 * 
	 * 
	 * @param  $params array -- required. post data
	 * @param  $account_creator default null -- account_creator in site_settngs
	 * @throws PDOException
	 * @throws Exception
	 */
	public function send_auth_sms(array $params, $account_creator = NULL)
	{
		try
		{
			$user_detail 	= $this->CI->auth_users->get_user_details($params['user_id'], $account_creator);

			if( !EMPTY( $user_detail['mobile_no'] ) )
			{
				$sys_logo 				 	= get_setting(GENERAL, "system_logo");
				$system_logo_src 			= base_url() . PATH_IMAGES . "logo_white.png";

				if( !EMPTY( $sys_logo ) )
				{
					$root_path 				= $this->BC->get_root_path();
					$sys_logo_path 			= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
					$sys_logo_path 			= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

					if( file_exists( $sys_logo_path ) )
					{
						$system_logo_src 	= base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
						$system_logo_src 	= @getimagesize($system_logo_src) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
					}
				}
				
				
				$status 		= ERROR;
				$email_data 	= array();
				$template_data 	= array();
		
				$salt 			= gen_salt(TRUE);
				$system_title 	= get_setting(GENERAL, "system_title");
					
				// required parameters for the email template library
				$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
				$email_data["from_name"] 	= $system_title;
				$email_data["to_email"] 	= array($user_detail['email']);
				$email_data["subject"] 		= 'Verification Code';

				$template_data["email"] 		= $user_detail['email'];
				$template_data["name"] 			= $user_detail['fname'] . ' ' . $user_detail['lname'];
				$template_data["system_name"] 	= $system_title;
				$template_data['logo']			= $system_logo_src;
				$template_data['user_id'] 		= $user_detail['user_id'];
				$template_data['generated_code'] = $params['generated_code'];

				$template_data["email_subject"] 		= 'please use this code to verify';
				
				$this->CI->load->library('Sms/Sms_api');

				// $this->CI->sms_api->setSmsApi(DEFAULT_SMS_API);

				$message 	= 'Hi '.$template_data['name'].' '.$template_data['email_subject'].' '.$template_data['generated_code'];

				$this->CI->sms_api->sendMessageToUser( $user_detail['mobile_no'], $message );
			}
		}
		catch( PDOException $e )
		{
			// echo $this->get_user_message($e);
			throw $e;
		}
		catch(Exception $e)
		{
			// echo $this->rlog_error( $e, TRUE );
			throw $e;
		}		
	}

	/**
	 * Use This helper function to send email verifaction code
	 * 
	 * 
	 * @param  $params array -- required. post data
	 * @param  $account_creator default null -- account_creator in site_settngs
	 * @throws PDOException
	 * @throws Exception
	 */
	public function send_auth_email( array $params, $account_creator = NULL )
	{
		try
		{
			$user_detail 	= $this->CI->auth_users->get_user_details($params['user_id'], $account_creator);

			if( ISSET( $params['new_email'] ) )
			{
				$user_detail['email'] = $params['new_email'];
			}

			$sys_logo 				 	= get_setting(GENERAL, "system_logo");
			$system_logo_src 			= base_url() . PATH_IMAGES . "logo_white.png";

			if( !EMPTY( $sys_logo ) )
			{
				$root_path 				= $this->BC->get_root_path();
				$sys_logo_path 			= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
				$sys_logo_path 			= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

				if( file_exists( $sys_logo_path ) )
				{
					$system_logo_src 	= base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
					$system_logo_src 	= @getimagesize($system_logo_src) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
				}
			}
			
			
			$status 		= ERROR;
			$email_data 	= array();
			$template_data 	= array();
	
			$salt 			= gen_salt(TRUE);
			$system_title 	= get_setting(GENERAL, "system_title");
				
			// required parameters for the email template library
			$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"] 	= array($user_detail['email']);
			$email_data["subject"] 		= 'Verification Code';

			$template_data["email"] 		= $user_detail['email'];
			$template_data["name"] 			= $user_detail['fname'] . ' ' . $user_detail['lname'];
			$template_data["system_name"] 	= $system_title;
			$template_data['logo']			= $system_logo_src;
			$template_data['user_id'] 		= $user_detail['user_id'];
			$template_data['generated_code'] = $params['generated_code'];

			$template_data["email_subject"] 		= 'Please use this code to verify';

			$template_data['logo']			= '
				<div style="background:#333333; padding:20px 30px; text-align:center;"><img src="'.$template_data['logo'].'" height="40" alt="logo" /></div>
';

			$this->CI->email_template->send_email_template_html($email_data, STATEMENT_CODE_EMAIL_VERIFY_CODE, $template_data);

			$errors 						= $this->CI->email_template->get_email_errors();

			if( !EMPTY( $errors ) )
			{
				$str 						= var_export( $errors, TRUE );

				RLog::error( "Email Error" ."\n" . $str . "\n" );
			}
		}
		catch( PDOException $e )
		{
			// echo $this->get_user_message($e);
			throw $e;
		}
		catch(Exception $e)
		{
			// echo $this->rlog_error( $e, TRUE );
			throw $e;
		}	
	}

	/**
	 * Use This helper function to verify code already checks if code is already expired
	 * 
	 * 
	* @param  $table -- required. ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
	 * @param  $orig_params array -- post data
	 * @throws PDOException
	 * @throws Exception
	 */
	public function verify_code($table, array $orig_params)
 	{
 		$msg 					= "";
		$flag  					= 0;

		// $orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$username 				= NULL;
		$password 				= NULL;
		$status 				= ERROR;
		$configs 				= array();

		$curr_det_u 			= array();

		try
		{

			$params 		 	= $this->filter_verify_code( $orig_params );

			$this->validate_vc( $params, TRUE );

			$get_user_multi_auth = $this->CI->auth_users->get_user_multi_auth($params['user_id'], $params['authentication_factor_id'], $table);

			// $table 			= SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH;

			// SYSAD_Model::beginTransaction();

			if( !EMPTY( $get_user_multi_auth ) )
			{
				if(!EMPTY($get_user_multi_auth[0]['expired_date']))
				{
					$date_now 		= strtotime(date('Y-m-d H:i:s'));
					$expired_date 	= strtotime($get_user_multi_auth[0]['expired_date']);

					/*if( $date_now >= $expired_date )
					{
						throw new Exception('Verification Code has expired. Please click resend');		
					}*/

				} 

				if( !EMPTY($get_user_multi_auth[0]['code']) )
				{
					$code_db = base64_url_decode($get_user_multi_auth[0]['code']);
					
					if( $code_db == $params['auth_code'] )
					{
						$upd_val 		= array();
						$main_where 	= array(
							'user_id'					=> $params['user_id'],
							'authentication_factor_id'	=> $params['authentication_factor_id']
						);

						$upd_val['authenticated_date'] 	= date('Y-m-d H:i:s');
						$upd_val['expired_date']		= NULL;
						$upd_val['expired_flag'] 		= ENUM_NO;

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= $table;
						$audit_action[] 	= AUDIT_UPDATE;
						$prev_detail[]  	= $this->CI->auth_users->get_details_for_audit( $table,
														$main_where
													 );

						$this->CI->auth_users->update_helper($table, $upd_val, $main_where);

						$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( $table,
														$main_where
													 );

						$curr_det_u 	= $this->CI->auth_users->get_user_details( $params['user_id'] );
						$configs 		= auth_factor_config($params['authentication_factor_id']);

						$username 		= $curr_det_u['username'];
						$password 		= $curr_det_u['password'];

						if( !EMPTY( $audit_schema ) )
						{

							/*$audit_name 	= 'User '.$curr_det_u['fname'].' '.$curr_det_u['lname'].' has verified '.$configs['header_txt'];

							$audit_activity = sprintf( $this->lang->line('audit_trail_update'), $audit_name);

							$this->CI->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );*/
						}

						/*$status 				= SUCCESS;
						$flag 					= 1;
						$msg 					= $configs['header_txt'].' Verified.';*/
					}
					else
					{
						throw new Exception('Verification code doesn\'t match.');
					}
				}
				else
				{
					throw new Exception('Verification code has not yet been generated.');
				}
			}

			// SYSAD_Model::commit();
		}
		catch( PDOException $e )
		{
			// SYSAD_Model::rollback();

			/*$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );*/

			throw $e;
		}
		catch (Exception $e) 
		{

			// SYSAD_Model::rollback();

			/*$this->rlog_error( $e );

			$msg 					= $e->getMessage();*/

			throw $e;
		}

		return array(
			'prev_detail'	=> $prev_detail,
			'curr_detail'	=> $curr_detail,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'audit_schema'	=> $audit_action,
			'username'		=> $username,
			'password'		=> $password,
			'configs'		=> $configs,
			'curr_det_u' 	=> $curr_det_u
		);


 	}


	/**
	 * Use This helper function to map the some common configs like the header txt in a label e.g. "Verify {$authentication_factor} or what sending method will be used based by (AUTHENTICATION_FACTOR_EMAIL, AUTHENTICATION_FACTOR_SMS)"
	 * 
	 * 
	 * @param  $authentication_factor -- required. ( AUTHENTICATION_FACTOR_EMAIL, AUTHENTICATION_FACTOR_SMS )
	 * @return array
	 */
 	public function auth_factor_config($authentication_factor)
 	{
 		return auth_factor_config($authentication_factor);
 	}

 	/**
	 * Use This helper function to generate the verification code change the length as needed.
	 * 
	 * 
	 * @param  $length -- required. DEAFULT 5
	 * @return array
	 */
	public function generate_verify_code($length = 5)
	{
		return generate_verify_code($length);
	}

	public function insert_temp_user_to_main($user_id, $account_creator = NULL)
 	{
 		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$real_us_id 			= NULL;

 		try
 		{
			$curr_detail_us 	= $this->CI->auth_users->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_TEMP_USERS, array('user_id' => $user_id)
			);

			if( !EMPTY( $curr_detail_us ) )
			{
				$user_detail 	= $curr_detail_us[0];
				$val 			= array();
				$org_code 		= NULL;

				if( ISSET($user_detail['org_code']) AND !EMPTY( $user_detail['org_code'] ) )
				{
					$org_code	= $user_detail['org_code'];
					unset($user_detail['org_code']);
				}

				unset($user_detail['user_id']);
				unset($user_detail['modified_by']);


				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USERS;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();
				
				$real_us_id 	= $this->CI->auth_users->insert_helper(SYSAD_Model::CORE_TABLE_USERS, $user_detail);

				$main_where 	= array(
					'user_id'	=> $real_us_id
				);

				$curr_det_u 	= $this->CI->auth_users->get_user_details( $real_us_id );

				$curr_detail[] 	= array($curr_det_u);

				$temp_user_where 	= array(
					'user_id' => $user_id
				);

				if( !EMPTY( $org_code ) )
				{
					$prev_org_where 	= array(
						'org_code'		=> $org_code
					);
					$prev_org_details 	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $prev_org_where);

					if( !EMPTY( $prev_org_details ) )
					{
						$prev_org 	= $prev_org_details[0];

						$check_org_code 	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATIONS, $prev_org_where );

						$new_org_code 	= $org_code;

						if( !EMPTY( $check_org_code ) )
						{
							$new_org_code 	= $org_code.$real_us_id;
							$prev_org['org_code'] 	= $new_org_code;
						}

						$prev_org['created_by']		= $real_us_id;
						$prev_org['created_date']	= date('Y-m-d H:i:s');
						
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[]  	= array();

						$main_org_where 	= array(
							'org_code'		=> $new_org_code
						);

						$this->CI->auth_users->insert_helper(SYSAD_Model::CORE_TABLE_ORGANIZATIONS, $prev_org);

						$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
														$main_org_where
													 );

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS,
														$prev_org_where
													 );

						$this->CI->auth_users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $prev_org_where);

						$curr_detail[] 		= array();
					}
				}

				$prev_temp_user_role = $this->CI->auth_users->get_details_for_audit(
					SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES,
					$temp_user_where
				);

				$prev_temp_user_agreement = $this->CI->auth_users->get_details_for_audit(
					SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS,
					$temp_user_where
				);

				if( !EMPTY( $prev_temp_user_role ) )
				{
					$ins_us_val = array();
					foreach( $prev_temp_user_role as $key => $user_role_det )
					{
						$ins_us_val[$key]['user_id'] 	= $real_us_id;
						$ins_us_val[$key]['role_code'] 	= $user_role_det['role_code'];
						$ins_us_val[$key]['main_role_flag'] 	= $user_role_det['main_role_flag'];
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ROLES;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->CI->auth_users->insert_helper(SYSAD_Model::CORE_TABLE_USER_ROLES, $ins_us_val);

					$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_ROLES,
													$main_where
												 );

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES,
													$temp_user_where
												 );

					$this->CI->auth_users->delete_helper( SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES, $temp_user_where );

					$curr_detail[] 		= array();
				}
				else
				{
					$get_default_roles = $this->CI->auth_model->get_default_sign_up_role();


					if( EMPTY( $get_default_roles ) )
					{
						throw new Exception('There are no roles assigned. Please contact system administrator.');
					}

					$val['role'] 	= array_column($get_default_roles, 'role_code');

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ROLES;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->CI->auth_users->_insert_user_roles($val['role'], $real_us_id, FALSE, TRUE);

					$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_ROLES,
													$main_where
												 );
				}

				if( !EMPTY( $prev_temp_user_agreement ) )
				{
					$ins_agr_val = array();
					foreach( $prev_temp_user_agreement as $key => $user_agr )
					{
						$ins_agr_val[$key]['user_id'] 			= $real_us_id;
						$ins_agr_val[$key]['agreement_flag'] 	= $user_agr['agreement_flag'];
						$ins_agr_val[$key]['agreed_date'] 		= $user_agr['agreed_date'];
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_AGREEMENTS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->CI->auth_users->insert_helper(SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, $ins_agr_val);

					$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_AGREEMENTS,
													$main_where
												 );

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS,
													$temp_user_where
												 );

					$this->CI->auth_users->delete_helper( SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS, $temp_user_where );

					$curr_detail[] 		= array();
				}
				else
				{

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_AGREEMENTS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->CI->auth_users->_insert_user_agreements($real_us_id);

					$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_AGREEMENTS,
													$main_where
												 );
				}

				$prev_user_org 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS,
													$temp_user_where
												 );

				$prev_user_secu 	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS,
												$temp_user_where
											 );

				$prev_user_auth 	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH,
												$temp_user_where
											 );

				if( !EMPTY( $prev_user_auth ) )
				{
					$prev_us_auth_v 		= array();

					foreach( $prev_user_auth as $key => $u_sec )
					{
						$prev_us_auth_v[$key]['user_id']					= $real_us_id;
						$prev_us_auth_v[$key]['authentication_factor_id']	= $u_sec['authentication_factor_id'];
						$prev_us_auth_v[$key]['code']						= $u_sec['code'];
						$prev_us_auth_v[$key]['authenticated_date']			= $u_sec['authenticated_date'];
						$prev_us_auth_v[$key]['expired_date']				= $u_sec['expired_date'];
						$prev_us_auth_v[$key]['expired_flag']				= $u_sec['expired_flag'];
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->CI->auth_users->insert_helper(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $prev_us_auth_v);

					$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH,
													$main_where
												 );

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $prev_user_auth;

					$this->CI->auth_users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, $temp_user_where);

					$curr_detail[] 		= array();	
				}

				if( !EMPTY( $prev_user_secu ) )
				{
					$prev_us_sec_v 		= array();

					foreach( $prev_user_secu as $key => $u_sec )
					{
						$prev_us_sec_v[$key]['user_id']					= $real_us_id;
						$prev_us_sec_v[$key]['security_question_id']	= $u_sec['security_question_id'];
						$prev_us_sec_v[$key]['answer']					= $u_sec['answer'];
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->CI->auth_users->insert_helper(SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS, $prev_us_sec_v);

					$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS,
													$main_where
												 );

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $prev_user_secu;

					$this->CI->auth_users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS, $temp_user_where);

					$curr_detail[] 		= array();	
				}

				if( !EMPTY( $prev_user_org ) )  
				{
					$prev_us_org_v 		= array();

					foreach( $prev_user_org as $key => $u_o )
					{
						$prev_us_org_v[$key]['user_id']		= $real_us_id;
						$prev_us_org_v[$key]['org_code']	= $u_o['org_code'];
						$prev_us_org_v[$key]['main_org_flag']	= $u_o['main_org_flag'];
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->CI->auth_users->insert_helper(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, $prev_us_org_v);

					$curr_detail[] 		= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS,
													$main_where
												 );

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $prev_user_org;

					$this->CI->auth_users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $temp_user_where);

					$curr_detail[] 		= array();
				}

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USERS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->CI->auth_users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USERS,
												$temp_user_where
											 );

				$this->CI->auth_users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USERS, $temp_user_where);

				$curr_detail[] 		= array();

				$audit_name 	= 'User '.$curr_det_u['fname'].' '.$curr_det_u['lname'];

				$audit_activity = sprintf( $this->CI->lang->line('audit_trail_add'), $audit_name);
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

 		return array(
			'prev_detail'	=> $prev_detail,
			'curr_detail'	=> $curr_detail,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'audit_schema'	=> $audit_action,
			'audit_activity'=> $audit_activity,
			'user_id'		=> $real_us_id
		);
 	}
}