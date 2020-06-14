<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Holidays_model extends CBS_Model 
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
				$select_fields	= 'COUNT(holiday_id) total';
			}
			else
			{
				$select_fields =  "
					holiday_id,
					holiday_title,
					holiday_desc,
					DATE_FORMAT(holiday_date, '%b %d, %Y') holiday_date,
					IF(recurring_flag='Y', 'Yes', 'No') recurring
					
				";

				$filters	= array(
					"holiday_title",
					"holiday_desc", 
					"holiday_date"
				);

				$sorts	= array(
					"holiday_title",
					"holiday_desc", 
					"holiday_date"
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
					FROM ".parent::CBS_TABLE_CONFIG_HOLIDAYS."
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