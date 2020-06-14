<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Errors_public extends Base_Controller
{
	public function __construct() 
	{
		parent::__construct();

	  $this->load->module( 'Errors' );

	}
	
	public function index( $status = NULL, $message = NULL, $system = SYSTEM_CORE )
	{		
		$this->errors->index( $status, $message, $system );
	}

	public function modal( $status = NULL, $message = NULL, $system = SYSTEM_CORE )
	{	
		$this->errors->modal( $status, $message, $system );
	}
}