<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings_model extends SYSAD_Model {
	
	private $site_settings;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->site_settings 	= parent::CORE_TABLE_SITE_SETTINGS;
	}
        
	public function get_settings_value($setting_type, array $order = array())
	{
		$result 		= array();

		try
		{				
			$where 		= array("setting_type" => $setting_type);
			$fields	 	= array("setting_name", "setting_value");
			
			$result 	= $this->select_data($fields, $this->site_settings, TRUE, $where, $order);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	
	}	
	
	public function get_specific_setting($setting_type, $setting_name)
	{
		$result 	= array();

		try
		{
			$where 	= array();
			
			$fields = array("setting_value");
			$where["setting_type"] = $setting_type;
			$where["setting_name"] = $setting_name;
				
			$result = $this->select_data($fields, $this->site_settings, FALSE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
        
}
