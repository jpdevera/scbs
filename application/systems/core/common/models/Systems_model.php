<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Systems_model extends SYSAD_Model {
	
	private $systems;
                
	public function __construct()
	{
		parent::__construct();
		
		$this->systems 	= parent::CORE_TABLE_SYSTEMS;
	}
	
	public function get_systems($system_code = NULL)
	{
		$result 		= array();

		try
		{
			$multiple 	= (IS_NULL($system_code))? TRUE : FALSE;
			$fields 	= array("system_code", "system_name", "link", "logo", "shared_module");
			$where 		= array("on_off_flag" => SYSTEM_ON);
			$order_by 	= array();
			
			if(!IS_NULL($system_code))
			{
				$where["system_code"] = $system_code;
			} 
			else 
			{
				$order_by["sort_order"] = "ASC";
			}	
				
			$result 	= $this->select_data($fields, $this->systems, $multiple, $where, $order_by);
		}	
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}

	public function get_systems_roles( array $roles )
	{

		$result 		= array();

		$val 			= array();
		$extra_val 		= array();

		$where 			= "";

		try
		{
			if( !EMPTY( $roles ) )
			{
				$count_roles				= count( $roles );

				$placeholder_count_roles 		= str_repeat( '?,', $count_roles );
				$placeholder_count_roles		= rtrim( $placeholder_count_roles, ',' );
				
				$where        	   		  	.= " AND a.role_code IN ( $placeholder_count_roles ) ";

				$extra_val 					= array_merge( $extra_val, $roles );
			}

			$query 	= "
				SELECT  a.system_code
				FROM 	%s a
				WHERE  	1 = 1
				$where
				GROUP 	BY a.system_code
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_SYSTEM_ROLES );
			
			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val);

		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}
	
		
}