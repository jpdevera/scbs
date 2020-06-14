<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer_model extends CBS_Model 
{
                
	public function __construct()
	{
		parent::__construct();
		
	}

	public function get_data_list($params=NULL)
	{	
		try
		{
			$where = $filter = $order = $limit = "";
			$val = $filter_val = array();
			
			// If params variable is null, get the total count of records
			if($params === NULL)
			{
				$select_fields	= 'COUNT(type_id) total';
			}
			else
			{
				$select_fields =  "
					type_id,
					type_code,
					type_name,
					IF(position='D', 'Debit', 'Credit') position
					
				";

				$filters	= array(
					"type_code",
					"type_name", 
					"position"
				);

				$sorts	= array(
					"type_code",
					"type_name", 
					"position"
				);

				$filter	= $this->filtering($filters, $params, FALSE);  
				$order	= $this->ordering($sorts, $params);
				$limit	= $this->paging($params);

				$where	= $filter["search_str"];
				$val	= $filter["search_params"];

			}

			$query = "
				SELECT
					SQL_CALC_FOUND_ROWS *
				FROM 
				(
					SELECT 
						$select_fields
					FROM ".parent::CBS_TABLE_GL_TYPES."
				) A
				$where
				$order
				$limit
			";
			
			// If params variable is null, get the total records
			if(empty($params))
			{
				$total	= $this->query($query, $val, TRUE, FALSE);
				return $total['total'];
			}
			else
			{
				return array(
					'records'			=> $this->query($query, $val),
					'display_records'	=> $this->_get_display_records()
				);
			}
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}

	}

	private function _get_display_records()
	{
		try
		{
			$query = 'SELECT FOUND_ROWS() cnt';	
			$count = $this->query($query, NULL, TRUE, FALSE);
			
			return $count['cnt'];
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}

	public function get_types()
	{
		try
		{
			$query = "
				SELECT 
					type_id,
					type_code,
					type_name,
					position,
					active_flag
				FROM $this->gl_types
			";	
			$count = $this->query($query, NULL, TRUE, FALSE);
			
			return $count['cnt'];
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}

	

}