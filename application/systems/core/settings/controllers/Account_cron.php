<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account_cron extends SYSAD_Controller {

	private $role_override_arr = array();
	
	public function __construct()
	{
		parent::__construct();
		
		if(!$this->input->is_cli_request()) show_404();
		$this->load->model(CORE_USER_MANAGEMENT.'/users_model','users', TRUE);

		$role_override 			= get_setting(LOGIN, 'role_override');
		$role_override 			= trim($role_override);

		$role_override_arr 		= array();

		if( !EMPTY( $role_override ) )
		{
			$role_override_arr 	= explode(',', $role_override);

			$this->role_override_arr = $role_override_arr;
		}

	}
	
	public function index()
	{ 
		$pass_expiry_arr 	= $this->users->get_settings_arr(PASSWORD_EXPIRY);
		$login_arr 			= $this->users->get_settings_arr(LOGIN);
		/*echo '<pre>';
		print_r($this->users->get_accounts_to_remind(0, $has_interval, TRUE));
		die;*/
		$this->update_temp_users_for_deactivation();
		
		$this->run_cron_job_pass( $pass_expiry_arr ); 
		$this->user_to_deactivate_pass_expiry( $pass_expiry_arr );
		$this->user_to_deactivate_last_logged_in( $login_arr );
	}
	
	public function run_cron_job_pass( array $pass_expiry_arr = array() )
	{
		try
		{
			$email_data = array();
			$template_data = array();
			$to_email = array();
			$template_data_indexes = array("name", "remaining");
			
			$system_title = get_setting(GENERAL, "system_title");
			
			
			if(!ISSET($pass_expiry_arr[PASS_EXP_EXPIRY]) || $pass_expiry_arr[PASS_EXP_EXPIRY] == '0' || EMPTY($pass_expiry_arr[PASS_EXP_EXPIRY])) 
				return TRUE;

			$duration 		= $pass_expiry_arr[PASS_EXP_DURATION];
			$has_interval 	= FALSE;

			if( ISSET( $pass_expiry_arr[PASS_EXP_REMINDER] ) AND !EMPTY( $pass_expiry_arr[PASS_EXP_REMINDER] ) )
			{
				$duration 	= $pass_expiry_arr[PASS_EXP_DURATION] - $pass_expiry_arr[PASS_EXP_REMINDER];
				$has_interval = TRUE;
			}
		
			$accounts 		= $this->users->get_accounts_to_remind($duration, $has_interval, TRUE);
			$user_id_arr 	= array(); 		
			
			foreach ($accounts as $account):

				if( !EMPTY( $this->role_override_arr ) )
				{
					if( !EMPTY( $account['role_codes'] ) )
					{
						$role_codes 	= explode(',', $account['role_codes']);

						foreach( $this->role_override_arr as $r )
						{
							if( in_array($r, $role_codes) )
							{
								continue;
							}
						}
					}
				}

				$user_id_arr[] 	= $account['user_id'];
				$to_email[] 	= $account['email'];
				$template_data["name"][$account['email']] 		= $account['full_name'];
				$template_data["remaining"][$account['email']] 	= $account['remaining'];

			endforeach;

			// required parameters for the email template library
			$email_data["from_email"] = get_setting(GENERAL, "system_email");
			$email_data["from_name"] = $system_title;
			$email_data["to_email"] = $to_email;
			$email_data["subject"] = 'Account Expiration';

			if( !EMPTY( $user_id_arr ) )
			{
				
				$this->email_template->send_email_template($email_data, "emails/account_expiration", $template_data, $template_data_indexes);
			}

			if( !EMPTY( $user_id_arr ) )
			{
				$this->users->update_email_pw_flag($user_id_arr);
			}

			echo $this->lang->line('email_sent');
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

	public function user_to_deactivate_pass_expiry( array $pass_expiry_arr = array() )
	{
		try
		{

			if(!ISSET($pass_expiry_arr[PASS_EXP_EXPIRY]) || $pass_expiry_arr[PASS_EXP_EXPIRY] == '0' || EMPTY($pass_expiry_arr[PASS_EXP_EXPIRY])) 
				return TRUE;

			if(!ISSET($pass_expiry_arr[PASS_EXP_DURATION]) || $pass_expiry_arr[PASS_EXP_DURATION] == '0' || EMPTY($pass_expiry_arr[PASS_EXP_DURATION])) 
				return TRUE;

			$duration 	= $pass_expiry_arr[PASS_EXP_DURATION];
			$user_list 	= array();
			$user_list 	=  $this->users->get_accounts_to_remind($duration, TRUE);

			$user_id 	= array();
			
			if(! EMPTY($user_list) )
			{
				$count = count($user_list);

				for($i = 0; $i < $count; $i++)
				{
					$check_role 	= FALSE;
					if( !EMPTY( $this->role_override_arr ) )
					{
						if( !EMPTY( $user_list[$i]['role_codes'] ) )
						{
							$role_codes 	= explode(',', $user_list[$i]['role_codes']);

							foreach( $this->role_override_arr as $r )
							{
								if( in_array($r, $role_codes, TRUE) )
								{
									$check_role = TRUE;
								}
							}
						}
					}

					if( $check_role )
					{
						continue;
					}
					// use to update email flag
					$user_id[] 	= $user_list[$i]['user_id']; 
				}

				$expired 		= get_sys_param_code(SYS_PARAM_STATUS, EXPIRED);

				if( !EMPTY( $user_id ) )
				{
					$this->users->deactivate_user($user_id, $expired['sys_param_code']);
				}
			}
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

	public function update_temp_users_for_deactivation()
	{
		try
		{
			$this->users->update_temp_users_for_deactivation();
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


	public function user_to_deactivate_last_logged_in( array $login_arr = array() )
	{
		try
		{

			if(!ISSET($login_arr['log_in_deactivation']) || $login_arr['log_in_deactivation'] == '0' || EMPTY($login_arr['log_in_deactivation'])) 
				return TRUE;

			if(!ISSET($login_arr['log_in_deactivation_duration']) || $login_arr['log_in_deactivation_duration'] == '0' || EMPTY($login_arr['log_in_deactivation_duration'])) 
				return TRUE;

			$duration 	= $login_arr['log_in_deactivation_duration'];

			$user_list 	= array();
			$active 	= get_sys_param_code(SYS_PARAM_STATUS, ACTIVE);
			$user_list 	=  $this->users->get_user_for_deactivate_last_logged_in($duration, $active['sys_param_code']);
			$user_id 	= array();
			
			if(! EMPTY($user_list) )
			{
				$count = count($user_list);
				
				for($i = 0; $i < $count; $i++)
				{
					$check_role = FALSE;
					if( !EMPTY( $this->role_override_arr ) )
					{
						if( !EMPTY( $user_list[$i]['role_codes'] ) )
						{
							$role_codes 	= explode(',', $user_list[$i]['role_codes']);

							foreach( $this->role_override_arr as $r )
							{
								if( in_array($r, $role_codes, TRUE) )
								{
									$check_role = TRUE;
								}
							}
						}
					}

					if( $check_role )
					{
						continue;
					}
					// use to update email flag
					$user_id[] 	= $user_list[$i]['user_id']; 
				}

				$inactive 		= get_sys_param_code(SYS_PARAM_STATUS, INACTIVE);
				
				if( !EMPTY( $user_id ) )
				{
					$this->users->deactivate_user($user_id, $inactive['sys_param_code']);
				}
			}
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
}