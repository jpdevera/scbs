<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Errors extends Base_Controller
{

	protected $err_options 	= array(
		ERROR_CODE_500 		=> array(
			'heading' 		=> 'Oops! Something went wrong.',
			'message' 		=> 'Please contact your system administrator.',
			'err_status' 	=> ERROR_CODE_500
		),
		ERROR_CODE_404		=> array(
			'heading' 		=> "Oops! You're lost.",
			'message' 		=> "",
			'err_status' 	=> ERROR_CODE_404
		),
		ERROR_CODE_401 		=> array(
			'heading' 		=> "Oops! You're not authorized.",
			'message' 		=> "",
			'err_status' 	=> ERROR_CODE_401
		),
		ERROR_CODE_402		=> array(
			'heading'		=> "Oops! The Link you're trying to open is either broken or missing.",
			'message' 		=> "",
			'err_status' 	=> ERROR_CODE_402
		),
		ERROR_CODE_NO_ORG	=> array(
			'heading'		=> "Oops! The Link you're trying to open is either broken or missing.",
			'message' 		=> "",
			'err_status' 	=> ERROR_CODE_NO_ORG
		)
	);

	public function __construct() 
	{
		parent::__construct();

		$this->err_options[ERROR_CODE_404]['message'] = $this->lang->line( 'err_page_404_msg' );
		$this->err_options[ERROR_CODE_401]['message'] = $this->lang->line( 'err_unauthorized_access' );

		$this->err_options[ERROR_CODE_500]['heading'] = $this->lang->line( 'err_page_500_heading' );
		$this->err_options[ERROR_CODE_500]['message'] = $this->lang->line( 'err_page_500_msg' );

		$this->err_options[ERROR_CODE_402]['heading'] = $this->lang->line( 'err_page_402_heading' );

		$this->err_options[ERROR_CODE_NO_ORG]['heading'] 	= $this->lang->line( 'err_page_no_org_heading' );
		$this->err_options[ERROR_CODE_NO_ORG]['message'] 	= $this->lang->line( 'err_page_no_org_message' );
	}
	
	public function index( $status = NULL, $message = NULL, $system = SYSTEM_CORE )
	{		
		$params 		= array_merge($_POST, $_GET);

		$err_status 	= !EMPTY( $status )	? $status : ERROR_CODE_404;

		$data 			= array();

		if( ISSET( $this->err_options[ $err_status ] ) )
		{
			$data 		= $this->err_options[ $err_status ];
		}
		else 
		{
			$data 		= $this->err_options[ERROR_CODE_404];
		}

		if( !EMPTY( $params ) )
		{
			if( ISSET( $params['message'] ) AND !EMPTY( $params['message'] ) )
			{
				$data['message'] = base64_url_decode($params['message']);
			}

			if( ISSET( $params['system'] ) AND !EMPTY( $params['system'] ) )
			{
				$system 		= $params['system'];
			}
		}
		else
		{

			if( !EMPTY( $message ) )
			{
				$data['message'] = base64_url_decode($message);
			}
		}

		if( ISSET( $params['tab'] ) AND !EMPTY( $params['tab'] ) )
		{
			$this->load->view('errors/html/error_main_general', $data);
		}
		else
		{
	
			$this->template->load( 'errors/html/error_main_general', $data, array(), $system );
		}

	}

	public function modal( $status = NULL, $message = NULL, $system = SYSTEM_CORE )
	{	
		$resources 		= array();

		$module_js 				= HMVC_FOLDER."/".SYSTEM_CORE."/errors";

		$resources['load_js']	= array(
			$module_js
		);

		$resources['loaded_init']	= array(
			'Errors.hide_btn();'
		);

		$err_status 	= !EMPTY( $status )	? $status : ERROR_CODE_404;

		$data 			= array();

		$params 		= get_params();

		if( ISSET( $this->err_options[ $err_status ] ) )
		{
			$data 		= $this->err_options[ $err_status ];
		}
		else 
		{
			$data 		= $this->err_options[ERROR_CODE_404];
		}

		$data['modal'] 	= TRUE;

		if( !EMPTY( $params ) )
		{
			if( ISSET( $params['message'] ) AND !EMPTY( $params['message'] ) )
			{
				$data['message'] = base64_url_decode($params['message']);
			}

			if( ISSET( $params['system'] ) AND !EMPTY( $params['system'] ) )
			{
				$system 		= $params['system'];
			}
		}
		else
		{
			if( !EMPTY( $message ) )
			{
				$data['message'] = base64_url_decode($message);
			}
		}

		$this->load->view( 'errors/html/error_main_general', $data );
		$this->load_resources->get_resource($resources);
	}
}