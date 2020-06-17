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
				$select_fields	= 'COUNT(customer_id) total';
			}
			else
			{
				$select_fields =  "
					customer_id,
					B.title_name,
					DECRYPT(A.first_name) first_name,
					DECRYPT(A.last_name) last_name,
					DATE_FORMAT(DECRYPT(A.birth_date), '%b %d, %Y') birth_date
					
				";

				$filters	= array(
					"title_name",
					"first_name", 
					"last_name",
					"birth_date"
				);

				$sorts	= array(
					"title_name",
					"first_name", 
					"last_name",
					"birth_date"
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
					FROM ".parent::CBS_TABLE_CUSTOMERS." A
					JOIN ".parent::CBS_TABLE_CONFIG_TITLES." B ON A.title_id=B.title_id
				) A
				$where
				$order
				$limit
			";

			// die($query);
			
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

	public function get_customer_information($customer_id)
	{
		try
		{
			$query = "
				SELECT
					A.title_id, 
					DECRYPT(A.last_name) last_name,
					DECRYPT(A.middle_name) middle_name,
					DECRYPT(A.first_name) first_name,
					DECRYPT(A.ext_name) ext_name,
				    DATE_FORMAT(DECRYPT(A.birth_date), '%m/%d/%Y') birth_date,
				    DECRYPT(A.birth_place) birth_place,
				    A.sex_code,
				    A.civil_status_id,
				    A.religion_id,
				    A.citizenship_type,
				    A.height,
				    A.weight
				FROM 
				customers A
			";	
			return  $this->query($query, [$customer_id], TRUE, FALSE);
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}

	

}