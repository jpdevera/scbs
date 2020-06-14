<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Systems_application_model extends SYSAD_Model {
    
	private $core_systems;

	public function __construct()
	{	
		parent::__construct();

		$this->core_systems = parent::CORE_TABLE_SYSTEMS;
	}
	
	public function get_system_list($select_fields, $where_fields, $params)
	{
		try 
		{
			$where_filter_arr = array("A-system_name", "A-description", "if (A.on_off_flag = '1' ,'Active', 'Inactive') as status");

			$fields		= str_replace(' , ', ' ', implode(', ', $select_fields));
			
			$where		= $this->filtering($where_filter_arr, $params, FALSE);
			$order		= $this->ordering($where_fields, $params);
			$limit		= $this->paging($params);
			
			$filter_str 	= $where['search_str'];
			$filter_params	= $where['search_params'];
			
			$query = <<<EOS
				SELECT $fields
				FROM $this->core_systems A
				$filter_str
				$order
				$limit
EOS;
			
			$stmt  = $this->query($query, $filter_params);

			return $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	public function filtered_length($aColumns, $bColumns, $params)
	{
		try
		{
			$this->get_system_list($aColumns, $bColumns, $params);
	
			$query = <<<EOS
				SELECT FOUND_ROWS() cnt
EOS;
	
			$stmt = $this->query($query, NULL, TRUE, FALSE);
			
			return $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;			
		}	
	}	
	
	public function total_length()
	{
		try 
		{
			$fields		= array('COUNT(system_code) cnt');
			return $this->select_data($fields, $this->core_systems, FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	public function get_specific_system($system_code, $select_fields)
	{
		try
		{
			$fields = str_replace(" , ", " ", implode(", ", $select_fields));

			$query = <<<EOS
				SELECT SQL_CALC_FOUND_ROWS $fields
				FROM $this->core_systems
				WHERE system_code = ?
EOS;
			
			$stmt  = $this->query($query, array($system_code));

			return $stmt;
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;			
		}
	}


	public function insert_system($params)
	{
		try 
		{
			
			$system_val = array();
			$system_val['system_code'] = $params['system_code'];
			$system_val['system_name'] = $params['system_name'];
			$system_val['description'] = (!empty($params['description'])) ? $params['description'] : null;
			$system_val['link'] = $params['system_link'];
			$system_val['logo'] = (!empty($params['system_logo'])) ? $params['system_logo'] : null;
			$system_val['on_off_flag'] = 1;
			$system_val['sort_order'] = 1;

			$this->insert_data($this->core_systems, $system_val);

			return $params['system_code'];
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	public function update_system($params, $system_code)
	{
		try 
		{
			$system_val = array();
			$where = array();
			$where['system_code'] = $system_code;
			
			if( ISSET( $params['system_code'] ) )
			{
				$system_val['system_code'] = $params['system_code'];
			}

			$system_val['system_name'] = $params['system_name'];
			$system_val['description'] = (!empty($params['description'])) ? $params['description'] : null;
			$system_val['link'] = $params['system_link'];
			$system_val['logo'] = (!empty($params['system_logo'])) ? $params['system_logo'] : null;

			if( ISSET( $params['status'] ) )
			{
				$system_val['on_off_flag'] = (!empty($params['status'])) ? $params['status'] : 0; 
			}

			$this->update_data($this->core_systems, $system_val, $where);

			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	public function delete_system($system_code)
	{
		try 
		{
			
			$this->delete_data($this->core_systems, array("system_code" => $system_code));

		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	public function check_system_used($system_code)
	{
		$result 	= array();
		$val  		= array();

		try
		{
			$query 	= "
				SELECT 	COUNT(a.system_code) check_system_used
				FROM 	%s a
				JOIN 	%s b ON a.system_code = b.system_code
				WHERE 	a.system_code = ?
";
			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_SYSTEMS, SYSAD_Model::CORE_TABLE_MODULES );

			$val[] 	= $system_code;

			$result = $this->query( $query, $val, TRUE, FALSE );

		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function check_system( $module_code )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	COUNT(a.system_code) as check_system
				FROM 	%s a
				JOIN 	%s b ON a.system_code = b.system_code
				WHERE 	a.on_off_flag = ?
				AND 	b.module_code = ?
";
			$val[] 	= 1;
			$val[] 	= $module_code;

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_SYSTEMS, SYSAD_Model::CORE_TABLE_MODULES );

			$result = $this->query( $query, $val, TRUE, FALSE );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function check_system_redirection( $directory, $current_system = NULL )
	{
		$result 	= array();
		$val 		= array();

		$add_where 	= "";
		$extra_val 	= array();

		try
		{
			if( !EMPTY( $current_system ) )
			{
				$add_where .= " AND a.system_code = ? ";
				$extra_val[] = $current_system;
			}

			$query 	= "
				SELECT 	COUNT(a.system_code) as check_system_redirection,
						a.system_code, a.logo, a.shared_module
				FROM 	%s a
				WHERE 	a.on_off_flag 	= ?
				AND 	a.ci_directory 	= ?
					$add_where
";
			$val[] 	= 1;
			$val[] 	= $directory;

			$val 	= array_merge( $val, $extra_val );

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_SYSTEMS );

			$result = $this->query( $query, $val, TRUE, FALSE );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}