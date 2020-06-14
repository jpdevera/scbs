<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manage_settings extends SYSAD_Controller 
{

	private $module 		= MODULE_SETTINGS;

	private $perm_site 		= FALSE;
	private $perm_auth 		= FALSE;
	private $perm_media 	= FALSE;
	private $perm_dpa 		= FALSE;
	private $perm_sys 		= FALSE;
	
	public function __construct()
	{
		parent::__construct();

		$this->perm_site 	= $this->permission->check_permission(MODULE_SITE_SETTINGS);
		$this->perm_auth 	= $this->permission->check_permission(MODULE_AUTH_SETTINGS);
		$this->perm_media 	= $this->permission->check_permission(MODULE_MEDIA_SETTINGS);
		$this->perm_dpa 	= $this->permission->check_permission(MODULE_DATA_PRIVACY_SETTING);
		$this->perm_sys 	= $this->permission->check_permission(MODULE_SYSTEM_SETTINGS);
	}
	
	public function index()
	{
		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$data 		= array();
			$resources 	= array();
			$module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_SETTINGS."/settings";
			
			$resources['load_js'] 		= array($module_js);
			$resources['loaded_init'] 	= array(
				'Settings.initForm();'
			);

			$data['perm_site'] 			= $this->perm_site;
			$data['perm_auth'] 			= $this->perm_auth;
			$data['perm_media'] 		= $this->perm_media;
			$data['perm_dpa'] 			= $this->perm_dpa;
			$data['perm_sys'] 			= $this->perm_sys;
			
			$this->template->load('manage_settings', $data, $resources);
		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);

			redirect(base_url() . 'errors/modal/500/'.base64_url_encode($msg) , 'location');
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			redirect(base_url() . 'errors/modal/500/'.base64_url_encode($msg) , 'location');
		}	
	}
}