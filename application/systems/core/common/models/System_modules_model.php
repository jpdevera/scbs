<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class System_modules_model extends SYSAD_Model {
	
	private $toggle_fields;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->toggle_fields = parent::CORE_TABLE_TOGGLE_FIELDS;
	}
	
	public function get_fields($module)
	{
		$result = array();

		try
		{		
			$fields 	= array("field_name");
			$where 		= array("module_code" => $module);
			
			$result 	= $this->select_data($fields, $this->toggle_fields, TRUE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		//print_r($result);
		return $result;
	}
	
}