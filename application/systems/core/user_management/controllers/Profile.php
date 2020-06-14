<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profile extends SYSAD_Controller 
{

	private $module;
	private $module_js;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module 		= MODULE_PROFILE;
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_USER_MANAGEMENT."/profile";
		
		$this->load->model('users_model', 'users', TRUE);
		$this->load->model('organizations_model', 'orgs', TRUE);
	}
	
	public function index()
	{
		$system 		= SYSTEM_CORE;

		try
		{
			// $this->redirect_off_system($this->module);
			// $this->redirect_module_permission($this->module);
			
			$data = $resources = array();
			
			$resources['load_js'] 	= array($this->module_js);
			
			$resources['loaded_init'] = array(
				'Profile.initForm();'
			);

			$user_id 	= $this->session->user_id;

			$systems 	= $this->auth_model->get_systems_by_permission( $user_id );

			if( !EMPTY( $systems ) )
			{
				$system = $systems[0]['ci_directory'];
				
				if( $system == SYSTEM_PLUGIN )
				{
					$system 	= SYSTEM_CORE;
				}	
			}
			
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
		
		$this->template->load('profile', $data, $resources, $system);
		
	}
		
	public function process($is_password_update = NULL)
	{
		try
		{
			// $this->redirect_off_system($this->module);
			
			$status = ERROR;
			$msg 	= "";
			$params	= get_params();
			$params = $this->set_filter($params)
						->filter_string('ext_name', TRUE)
						->filter();
			$name 	= "";

			if( !ISSET( $params['contact_type'] ) )
			{
				$params['contact_type'] 	= 0;
			}

			$this->load->library('Authentication_factors');

			$check_auth_factors 	= $this->authentication_factors->check_authentication_factor_section_enabled(AUTH_SECTION_ACCOUNT);
	
			// SERVER VALIDATION
			if(ISSET($is_password_update))
				$this->_validate_password($params);
			else
				$this->_validate($params);
			
			// GET SECURITY VARIABLES
			$id		= filter_var($params['user_id'], FILTER_SANITIZE_NUMBER_INT);
			$salt 	= $params['salt'];
			$token 	= $params['token'];
			// $current_password = $params['current_password'];
			
			// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
			check_salt($id, $salt, $token);
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();

			if( !EMPTY( $check_auth_factors ) )
			{
				$this->authentication_factors->save_multi_auth_section_helper($id, SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, AUTH_SECTION_ACCOUNT);

				$get_user_multi_auth 	= $this->users->get_user_multi_auth($id, AUTHENTICATION_FACTOR_EMAIL, SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, TRUE);

				if( !EMPTY( $get_user_multi_auth ) )
				{
					throw new Exception('Please verify your email.');
				}

			}
			
			$audit_action[]	= AUDIT_UPDATE;
			$audit_table[]	= SYSAD_Model::CORE_TABLE_USERS;
			$audit_schema[]	= DB_CORE;
			
			// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
			$prev_detail[] = $this->users->get_specific_user($id);
			
			if(ISSET($is_password_update))
			{
				$this->users->update_user_password($params);
				$activity = "changed his/her password";
			}else{
				$this->users->update_user($params);
					
				// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
				$name 	  = $params['fname'] . ' ' . $params['lname'];
				$activity = "updated his/her own profile.";
				$activity = sprintf($activity, $name);
			}
			
			$msg = $this->lang->line('data_updated');
			
			// GET THE DETAIL AFTER UPDATING THE RECORD
			$curr_detail[] 	= $this->users->get_specific_user($id);

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
				
			SYSAD_Model::commit();
			
			$status = SUCCESS;
			
			if(!ISSET($is_password_update))
			{
				$arr 	= array(
					"photo" 	=> $params['image'],
					"job_title" => $params['job_title'],
					"name" 		=> $name
				);			
			
				$this->session->set_userdata($arr);
			}
			
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
			"msg" 		=> $msg
		);
		
		if(!ISSET($is_password_update))
		{
			$info['image'] 		= $params['image'];
			$info['job_title'] 	= $params['job_title'];
			$info['name'] 		= $name;
		}
		
		echo json_encode($info);
	
	}
	
	public function validate_password_prof($password = NULL, $front_end = TRUE)
	{
		try
		{
			$params 	= get_params();

			$password 	= ( !EMPTY( $params ) AND !EMPTY( $params["password"] ) ) ? $params["password"] : $password;

			$password 	= filter_var($password, FILTER_SANITIZE_STRING);
			$this->authenticate->sign_in($this->session->username, $password, FALSE, TRUE);
			$flag 		= 1;
		}
		catch(Exception $e)
		{
			$flag = 0;
		}
		
		$info 	= array("flag" => $flag);
		
		if($front_end)
		{
			echo json_encode($info);
		}
		else
		{
			return $flag;
		}
	}
	
	private function _validate($params, $action = NULL)
	{
		try
		{
			$required 	= array();

			$required['lname']	= "Last name";
			$required['fname']	= "First name";

			
			// $required['mname']	= "Middle initial";
			
			if(!EMPTY($params['current_password']))
			{
				$required['email']	= "Email";

				if(!EMPTY($params['password']))
				{
					if(EMPTY($params['confirm_password']))
						throw new Exception('Please confirm your password.');
						
					$this->users->check_password_history($this->session->user_id, $params['confirm_password']);

					if( EMPTY( preg_match('/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\s]+$/', $params['password'] ) ) )
					{
						throw new Exception("Password contains an illegal character.");
					}
				}
				else
				{
					throw new Exception(sprintf($this->lang->line('is_required'), "Password"));
				}
			}

			$this->check_required_fields( $params, $required );

			if( !EMPTY( $params['password'] ) )
			{
				if( !EMPTY( $params['user_id'] ) )
				{
					$us_name 		= NULL;
					$username 		= $this->users->get_specific_user( $params['user_id'] );

					if( !EMPTY( $username ) )
					{
						$us_name 	= $username[0]['username'];
					}
				}

				$check_password = $this->validate_password( $params['password'], $us_name );

				if( $check_password !== TRUE )
				{
					throw new Exception($check_password);
				}
			}

			$user_id = NULL; 

 			if(ISSET($params['user_id']) AND !EMPTY($params['user_id']))
 			{
 				$user_id = $params['user_id'];
 			}

			$v = $this->core_v;

			$mobile_no 	= $params['mobile_no'];

			if( !EMPTY( $mobile_no ) )
 			{
 				if( strlen($mobile_no) == 10 )  
 				{
 					$mobile_no = '0'.$mobile_no;
 				}
 			}
 			
 			if( !EMPTY($mobile_no ) )
 			{
 				$v 
 					->mobile_no_unique($user_id)
 					->check('mobile_no', $mobile_no);
 			}

 			if(ISSET($params['ext_name']))
 			{
 				$v 	
    			->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_PARAM_EXTENSION_NAME.'|primary_id=param_extension_name')->sometimes('sometimes')
    			->check('ext_name', $params);
 			}

			$v->assert(FALSE);
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
	
	private function _validate_password($params, $action = NULL)
	{
		try
		{
			$required 	= array();
			
			$required['current_password']	= "Current Password";
			
			$this->check_required_fields( $params, $required );
			
			if(!EMPTY($params['current_password']))
			{
				if(!EMPTY($params['password']))
				{
					if(EMPTY($params['confirm_password']))
						throw new Exception('Please confirm your password.');
						
						$this->users->check_password_history($this->session->user_id, $params['confirm_password']);
						
						if( EMPTY( preg_match('/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\s]+$/', $params['password'] ) ) )
						{
							throw new Exception("Password contains an illegal character.");
						}
				}
				else
				{
					throw new Exception(sprintf($this->lang->line('is_required'), "<strong>Password</strong>"));
				}
			}
			
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

	public function modal()
	{
		$system 		= SYSTEM_CORE;

		try
		{
			// $this->redirect_off_system($this->module);
			// $this->redirect_module_permission($this->module);
			
			$data = $resources = array();
			
			$resources['load_js'] 	= array($this->module_js);
			
			$resources['loaded_init'] = array(
				"$('#link_tab_profile_account').trigger('click');"
			);

		
			$user_id 	= $this->session->user_id;

			$systems 	= $this->auth_model->get_systems_by_permission( $user_id );

			if( !EMPTY( $systems ) )
			{
				$system = $systems[0]['ci_directory'];
				
				if( $system == SYSTEM_PLUGIN )
				{
					$system 	= SYSTEM_CORE;
				}	
			}

			$data['users_gender_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_GENDER');
			$data['users_mname_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_MIDDLE_NAME');
			$data['users_ename_inp'] 				= get_sys_param_val('USERS_INPUT', 'USERS_EXT_NAME');
			$data['users_job_title_inp'] 			= get_sys_param_val('USERS_INPUT', 'USERS_JOB_TITLE');
			$data['users_org_inp'] 					= get_sys_param_val('USERS_INPUT', 'USERS_ORG');
			
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
		
		$this->load->view('profile', $data);
		$this->load_resources->get_resource($resources);
		
	}
}