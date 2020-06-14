<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modules_model extends SYSAD_Model 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_modules( $module_code = NULL, $parent_module = NULL, $system_code = NULL )
	{
		$result 			= array();
		$where				= array();
		$order 				= array();

		try
		{
			$fields 		= array(
				'module_code', 'module_name', 'link'
			);

			if( !EMPTY( $parent_module ) )
			{
				$where['parent_module'] 	= $parent_module;
			}

			if( !EMPTY( $module_code ) )
			{
				$where['module_code'] 	= $module_code;	
			}

			if( !EMPTY( $system_code ) )  
			{
				$where['system_code']	= $system_code;
			}

			$where['enabled_flag']		= 1;

			$table 						= DB_CORE.'.'.SYSAD_Model::CORE_TABLE_MODULES;

			$order['sort_order']		= 'ASC';

			$result 		= $this->select_data( $fields, $table, TRUE, $where, $order );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_reports( $parent_module, $system = NULL )
	{
		$result 			= array();
		$where				= array();
		$order 				= array();

		try
		{
			$fields 		= array(
				'module_code', 'module_name', 'link'
			);

			$where['parent_module'] 	= $parent_module;

			if( !EMPTY( $system ) )
			{
				$where['system_code']		= $system;
			}
			else
			{
				$where['system_code']		= strtoupper( IDMRIS );
			}

			$where['enabled_flag']		= 1;

			$table 						= DB_CORE.'.'.SYSAD_Model::CORE_TABLE_MODULES;

			$order['sort_order']		= 'ASC';
			
			$result 		= $this->select_data( $fields, $table, TRUE, $where, $order );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}