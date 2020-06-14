<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reset_password extends SYSAD_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('../controllers/auth', 'auth');
	}

	public function initial_logged_in( $username, $salt, $initial_flag )
	{
		$this->auth->reset_password_form( $username, $salt, $initial_flag );
	}
}