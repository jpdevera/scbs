<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Site_info extends SYSAD_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function modal()
	{
		$data 			= array();
		$resources		= array();

		try
		{
			// $this->redirect_off_system($this->module);
			
			$this->load->view('modals/site_info');
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	
	}
}