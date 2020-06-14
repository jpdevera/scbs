<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profile_password extends SYSAD_Controller 
{

	private $module;
	private $module_js;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module 		= MODULE_PROFILE;
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_USER_MANAGEMENT."/profile";
		
		$this->load->model('users_model', 'users', TRUE);

		$this->load->library('Authentication_factors');
	}
	
	public function index()
	{
		$email_ver_readonly 	= '';
		$email_auth_data 		= array();

		try
		{
			// $this->redirect_off_system($this->module);
			// $this->redirect_module_permission($this->module);
			
			$data = $resources = array();
			
			$id 			= $this->session->user_id;
			$user 			= $this->users->get_user_details($id);

			$check_multi_auth_enable 	= $this->authentication_factors->check_authentication_factor_section_enabled(AUTH_SECTION_ACCOUNT);

			$email_ver 					= FALSE;

			if( $check_multi_auth_enable )
			{
				$auth_factors 			= get_setting_authentication_factor(AUTH_SECTION_ACCOUNT);

				$multi_auth 		= $this->users->get_user_multi_auth($user['user_id']);

				SYSAD_Model::beginTransaction();

				if( EMPTY( $multi_auth ) )
				{
					$this->authentication_factors->save_multi_auth_section_helper($user['user_id'], SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, AUTH_SECTION_ACCOUNT);
				}

				if( in_array(AUTHENTICATION_FACTOR_EMAIL, $auth_factors) )
				{
					$email_ver 			= TRUE;

					$email_auth 		= $this->users->get_user_multi_auth($user['user_id'], AUTHENTICATION_FACTOR_EMAIL);

					if( !EMPTY( $email_auth ) AND !EMPTY( $email_auth[0] ) )
					{
						$email_auth_data 	= array(
							'user_id'		=> base64_url_encode($email_auth[0]['user_id']),
							'authentication_factor_id' => base64_url_encode($email_auth[0]['authentication_factor_id'])
						);
					}
				}

				SYSAD_Model::commit();
			}

			if( $email_ver ) 
			{
				$email_ver_readonly 	= 'readonly="readonly"';
			}

			$data['user'] 	= $user;
			
			$resources['load_js'] 	= array($this->module_js);
			
			$pass_const 	= $this->users->get_settings_arr(PASSWORD_CONSTRAINTS);
			
			$pass_err 		= $this->get_pass_error_msg();
			$pass_length 	= $pass_const[PASS_CONS_LENGTH];
			$upper_length 	= $pass_const[PASS_CONS_UPPERCASE];
			$digit_length 	= $pass_const[PASS_CONS_DIGIT];
			$letter_length 		= $pass_const['constraint_letter'];
			$repeat_pass 	= $pass_const[PASS_CONS_REPEATING];
			$lower_length 		= $pass_const['constraint_lowercase'];
			$spec_length 			= $pass_const['constraint_special_character'];
			
			$cons_array = array(
					'pass_err'		=> $pass_err,
					'pass_length'	=> $pass_length,
					'upper_length'	=> $upper_length,
					'digit_length'	=> $digit_length,
					'repeat_pass'	=> $repeat_pass,
					'pass_same'		=> 0,
					'letter_length' => $letter_length,
					'spec_length'	=> $spec_length,
					'lower_length'	=> $lower_length
			);
			
			$cons_array 		= json_encode( $cons_array );
			
			$resources['loaded_init'] = array(
				'password_constraints( '.$cons_array.' );',
				'Profile.initModal("'.$pass_length.'", "'.$upper_length.'", "'.$digit_length.'", "'.$pass_err.'");',
				'Profile.savePassword();'
			);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
			
			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);
			
			$this->error_index( $msg );
		}

		$data['email_ver_readonly'] 	= $email_ver_readonly;
		$data['email_auth_data'] 		= $email_auth_data;
		
		$this->load->view('tabs/profile_password', $data);
		$this->load_resources->get_resource($resources);
		
	}
}