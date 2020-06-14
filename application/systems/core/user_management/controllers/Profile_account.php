<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Profile_account extends SYSAD_Controller 
{

	private $module;
	private $module_js;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module 		= MODULE_PROFILE;
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_USER_MANAGEMENT."/profile";
		
		$this->load->model('users_model', 'users', TRUE);
		$this->load->model(CORE_GROUPS.'/Groups_model', 'groups');
		$this->load->model(CORE_GROUPS.'/User_groups_model', 'user_groups');

		$this->load->library('Authentication_factors');
	}
	
	public function index()
	{
		$ext_names 			= array();

		try
		{
			// $this->redirect_off_system($this->module);
			// $this->redirect_module_permission($this->module);
			
			$data = $resources = array();

			$all_groups 			= array();

			$user_groups 			= array();
			
			$id 			= $this->session->user_id;
			$user 			= $this->users->get_user_details($id);

			$user_g 		= $this->user_groups->get_user_groups_details( $id );

			

			/*if( !EMPTY( $user_g ) )
			{
				$user_groups = array_column( $user_g, 'group_id');
			}*/

			// $all_groups 	= $this->groups->get_all_groups();

			$data['user'] 	= $user;
			// $data['all_groups']		= $all_groups;
			$data['user_groups']	= $user_g;
			
			$resources['load_css']	= array(CSS_LABELAUTY, CSS_SELECTIZE);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_SELECTIZE, $this->module_js);
			
			$resources['loaded_init'] = array(
				'Profile.initObj();',
				'Profile.save();'
			);

			$login_sys_param 	= get_sys_param_val('LOGIN', 'LOGIN_WITH');

			$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

			$data['ch_login_sys_param'] 	= $ch_login_sys_param;

			$ext_names 		= $this->users->get_details_for_audit(SYSAD_Model::CORE_TABLE_PARAM_EXTENSION_NAME, array());
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

		$data['ext_names'] = $ext_names;
		
		$this->load->view('tabs/profile_account', $data);
		$this->load_resources->get_resource($resources);
		
	}

	public function modal_verify_code()
	{
		$data 							= array();
		$resources 						= array();
		$orig_params 					= get_params();
		$params 						= array();

		$configs 						= array();
		$client_side 					= array();

		try
		{

			$params 					= $this->authentication_factors->filter_verify_code($orig_params);

			$client_side 				= $this->validate_id_details(array(), TRUE);
			
			/*$this->authentication_factors->validate_vc($params);

			$configs 					= $this->authentication_factors->auth_factor_config($params['authentication_factor_id']);

			$params['generate_token'] 	= 1;*/

			// SYSAD_Model::beginTransaction();

			// $this->authentication_factors->update_multi_auth(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $params, AUTH_SECTION_ACCOUNT);

			// SYSAD_Model::commit();

			$module_js 					= HMVC_FOLDER."/multi_auth";

			$resources['load_js']		= array('auth', $module_js);
			$resources['loaded_init']	= array( $client_side['customJS'], 'Multi_auth.verify_btn("profile");', 'Multi_auth.resend("profile");', 'Multi_auth.send_code("profile");');
		}
		catch( PDOException $e )
		{
			// SYSAD_Model::rollback();
			$msg 	= $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			// SYSAD_Model::rollback();
			$msg 	= $this->rlog_error( $e, TRUE );
		}

		$data['orig_params'] 	= $orig_params;
		$data['params'] 		= $params;
		$data['configs'] 		= $configs;
		$data['client_side'] 	= $client_side;

		$this->load->view("modals/verify_profile_code", $data);
		$this->load_resources->get_resource($resources);
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

 			$email = 'CAST('.aes_crypt('email', FALSE, FALSE).' AS char(100))';

 			$v 
 				->required(NULL, '#client_new_email')
 				->email(NULL, '#client_new_email')->sometimes('sometimes', NULL, $forJs)
 				->blacklist_email_domain()
 				->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$email, '@custom_error_The email address entered has already been used. Please use a different email.')->sometimes('sometimes', NULL, $forJs)
 				->check('new_email', $params);

 			$login_with_arr_sel 		= get_setting(LOGIN, 'login_api');
			$login_with_arr_sel 		= trim($login_with_arr_sel);

			$login_with_arr_a 		= array();

			if( !EMPTY( $login_with_arr_sel ) )
			{
				$login_with_arr_a 	= explode(',', $login_with_arr_sel);
			}

			$facebook_email = 'CAST('.aes_crypt('facebook_email', FALSE, FALSE).' AS char(100))';
			$google_email = 'CAST('.aes_crypt('google_email', FALSE, FALSE).' AS char(100))';

			if( in_array(VIA_FACEBOOK, $login_with_arr_a) )
			{
			
				$v 
					->email()->sometimes();
					if( ISSET( $params['facebook_email'] ) AND !EMPTY( $params['facebook_email'] ) )
					{
						$v->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$facebook_email.'|exclude_id=user_id|exclude_value='.$id, '@custom_error_The facebook email address entered has already been used. Please use a different email.')->sometimes();
					}
					$v->check('facebook_email', $params);
			}

			if( in_array(VIA_GOOGLE, $login_with_arr_a) )
			{

				$v 
					->email()->sometimes();
					if( ISSET( $params['google_email'] ) AND !EMPTY( $params['google_email'] ) )
					{
						$v->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_USERS.'|primary_id='.$google_email.'|exclude_id=user_id|exclude_value='.$id, '@custom_error_The google email address entered has already been used. Please use a different email.')->sometimes();
					}
					$v->check('google_email', $params);
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

 	private function _filter_sign_up( array $orig_params )
 	{
 		$par 			= $this->set_filter( $orig_params )
							->filter_email('new_email');

		$params 		= $par->filter();
		
		return $params;
 	}

 	public function send_code()
 	{
 		$msg 					= "";
		$flag  					= 0;
		$status 				= ERROR;

		$orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		try
		{
			$params 					= $this->authentication_factors->filter_verify_code($orig_params);

			$sec_params 				= $this->_filter_sign_up($orig_params);

			// $params 					= array_merge($params, $sec_params);
			$params['new_email']		= $sec_params['new_email'];

			$this->authentication_factors->validate_vc($params);

			$this->validate_id_details($params);

			$configs 					= $this->authentication_factors->auth_factor_config($params['authentication_factor_id']);

			$params['generate_token'] 	= 1;

			SYSAD_Model::beginTransaction();

			$this->authentication_factors->update_multi_auth(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $params, AUTH_SECTION_ACCOUNT);
			

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= 'Verification Code was sent to your '.$configs['header_txt'].'.';
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
			'status' 				=> $status
		);

		echo json_encode( $response );

 	}
}