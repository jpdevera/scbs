<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Unauthorized extends Base_Controller 
{
	
	public function __construct() 
	{
		parent::__construct();
	}	
	
	public function index() 
    { 
		$this->load->view('unauthorized');
    }


    public function invalid_link( $reject_dpa = NULL, $invalid_Login = NULL, $custom_message = NULL )
    {
    	$data['reject_dpa'] 	= $reject_dpa;
    	$data['reject_dpa_msg'] = $this->lang->line('reject_dpa_msg');

    	$data['invalid_Login']	= $invalid_Login;
    	$data['invalid_Login_msg']	= (!EMPTY($custom_message)) ? base64_url_decode($custom_message) : $this->lang->line('invalid_login');

    	$data['blacklist_ip']	= $blacklist_ip;
    	$this->load->view('invalid_link', $data);
    }

    public function check_session()
	{
		$check 		= 0;
		// $params 	= get_params();

		try
		{
			$check 	= ($this->session->has_userdata('user_id') == TRUE) ? 1 : 0;
		}
		catch( PDOException $e )
		{
			$this->rlog_error( $e );

			$msg 	= $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
			$this->rlog_error( $e );

			$msg 	= $e->getMessage();
		}

		$response 	= array(
			'check' => $check
		);

		echo json_encode( $response );
	}	

	public function session_expired_modal()
	{
		$data 		= array();
		$resources 	= array();

		try
		{
			$module_js 				= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_COMMON."/session_expired";

			// $resources['load_css'] 	= array('login');
			$resources['load_js'] 	= array( $module_js );
			$resources['loaded_init']	= array( 'Session_expired.log_in();' );

			$this->load->view("modals/session_expired", $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
		}
	}

	public function warning_expired_sess_modal()
	{
		$data 		= array();
		$resources 	= array();

		try
		{
			$module_js 				= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_COMMON."/session_expired";
			$resources['load_js'] 	= array( $module_js );

			$resources['loaded_init']	= array( 'Session_expired.warning_sess_expired();' );

			$this->load->view("modals/warning_sess_expired", $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
		}
	}
}