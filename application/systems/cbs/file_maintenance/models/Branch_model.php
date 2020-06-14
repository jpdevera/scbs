<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Branch_model extends CBS_Model 
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
				$select_fields	= 'COUNT(branch_id) total';
			}
			else
			{
				$select_fields =  "
					branch_id,
					brn_code,
					brn_name,
					institution_name,
					DATE_FORMAT(system_date, '%b %d, %Y') system_date
					
				";

				$filters	= array(
					"brn_code", 
					"brn_name",
					"institution_name",
					"system_date"

				);

				$sorts	= array(
					"brn_code", 
					"brn_name",
					"institution_name",
					"system_date"
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
					FROM ".parent::CBS_TABLE_BRANCHES."
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

	

}