<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_param_model extends SYSAD_Model {
	
	private $sys_param;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->sys_param 	= parent::CORE_TABLE_SYS_PARAM;
	}
	
	public function get_sys_param($params)
	{
		$result 				= array();

		try
		{		
			if(!EMPTY($params["where"]))
			{	
				$where 			= array();
				foreach ($params["where"] as $k => $v):
					$where[$k] 	= $v;
				endforeach;
			}
			
			$result 			= $this->select_data($params["fields"], $this->sys_param, $params["multiple"], $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
}