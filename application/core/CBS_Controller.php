<?php if (!defined('BASEPATH')) exit('No direct script access is allowed');

class CBS_Controller extends Base_Controller
{

	protected static $system = SYSTEM_CBS;

	public function __construct() {
		parent::__construct();
		// $this->lang->load('sos_lang', 'language/english');
	}

	public function validate_security(&$params)
	{
		try
		{
			$security			= explode('/', $params['security']);

			$encrypt_id 		= $security[0];
			$salt 				= $security[1];
			$token				= $security[2];
			$security_action	= $security[3];

			check_salt($encrypt_id, $salt, $token, $security_action);

			$params['decrypt_id'] 		= $this->decrypt($encrypt_id);
			$params['salt']				= $salt;
			$params['token'] 			= $token;
			$params['security_action']	= $security_action;

		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
}
