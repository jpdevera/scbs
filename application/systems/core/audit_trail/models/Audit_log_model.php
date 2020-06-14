<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Audit_log_model extends SYSAD_Model {
	
	private $audit_trail;
	private $audit_trail_detail;
	private $users;
	private $modules;
                
	public function __construct()
	{
		parent::__construct();
		
		$this->audit_trail 			= parent::CORE_TABLE_AUDIT_TRAIL;
		$this->audit_trail_detail 	= parent::CORE_TABLE_AUDIT_TRAIL_DETAIL;
		$this->users 				= parent::CORE_TABLE_USERS;
		$this->modules 				= parent::CORE_TABLE_MODULES;
	}			
	
	public function get_audit_log($audit_log_id, $archived = FALSE)
	{
		$result 	= array();

		try
		{

			$fname_cast = 'CAST('.aes_crypt('B.fname', FALSE, FALSE).' AS char(100))';
			$lname_cast = 'CAST('.aes_crypt('B.lname', FALSE, FALSE).' AS char(100))';

			$table = $this->audit_trail;

			if( $archived )
			{
				$table = SYSAD_Model::CORE_TABLE_ARCHIVED_AUDIT_TRAIL;
			}

			$query = <<<EOS
				SELECT A.*, DATE_FORMAT(activity_date,'%m/%d/%Y %r') activity_date, CONCAT($fname_cast, ' ', $lname_cast) name, C.module_name
				FROM $table A, $this->users B, $this->modules C
				WHERE A.user_id = B.user_id
				AND A.module_code = C.module_code
				AND A.audit_trail_id = ?
EOS;
			$stmt 	= $this->query($query, array($audit_log_id), TRUE, FALSE);
			
			$result = $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		

		return $result;
	}	
	
	public function get_audit_log_details($audit_log_id, $archived = FALSE)
	{
		$result 	= array();

		try
		{
			$fields = array("*");
			$where 	= array("audit_trail_id" => $audit_log_id);

			$table = $this->audit_trail_detail;

			if( $archived )
			{
				$table = SYSAD_Model::CORE_TABLE_ARCHIVED_AUDIT_TRAIL_DETAIL;
			}

				
			$result = $this->select_data($fields, $table, TRUE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;	
	}	
	
	public function get_audit_log_list($aColumns, $bColumns, $params)
	{
		$result 		= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			$fname_w 	= "CAST( ".aes_crypt('B.fname', FALSE, FALSE)." as char(100) ) ";
			$lname_w 	= "CAST( ".aes_crypt('B.lname', FALSE, FALSE)." as char(100) ) ";

			$cColumns 	= array("CONCAT(".$fname_w.",' ',".$lname_w.") convert_to fullname", "C-module_name", "A-activity", "DATE_FORMAT(activity_date,'%m/%d/%Y %T') convert_to activity_date", "A-ip_address");
			$fields 	= str_replace(" , ", " ", implode(", ", $aColumns));
		
			$sWhere = $this->filtering($cColumns, $params, TRUE);
			$sOrder = $this->ordering($bColumns, $params);
			$sLimit = $this->paging($params);
			
			$filter_str 	= $sWhere["search_str"];
			$filter_params 	= $sWhere["search_params"];
			
			if(ISSET($params['system_code']) AND $params['system_code'] != "0"  )
			{
				$val = array($params['system_code']);
				$val = array_merge($val,$filter_params);
				$filter_sys_code 	= " AND C.system_code = ? ";
			} else {
				$filter_sys_code 	= "";
				$val 			 	= $filter_params;
			}

			if( ISSET( $params['date_from'] ) AND !EMPTY( $params['date_from'] ) 
				AND ( !ISSET( $params['date_to'] ) OR EMPTY( $params['date_to'] ) )
			)
			{
				$add_where .= " AND DATE(A.activity_date) = ? ";	

				$extra_val[]= $params['date_from'];
			}

			if( ISSET( $params['date_to'] ) AND !EMPTY( $params['date_to'] ) 
				AND ( !ISSET( $params['date_from'] ) OR EMPTY( $params['date_from'] ) )
			)
			{

				$add_where .= " AND DATE(A.activity_date) = ? ";	

				$extra_val[]= $params['date_to'];
			}

			if( 
				( ISSET( $params['date_from'] ) AND !EMPTY( $params['date_from'] ) ) AND
				( ISSET( $params['date_to'] ) AND !EMPTY( $params['date_to'] ) )
			)
			{
				$add_where .= " AND DATE(A.activity_date) BETWEEN ? AND ? ";	

				$extra_val[]= $params['date_from'];
				$extra_val[]= $params['date_to'];
			}

			$table = $this->audit_trail;

			if( !EMPTY( $params['checked_arc'] ) )
			{
				$table = SYSAD_Model::CORE_TABLE_ARCHIVED_AUDIT_TRAIL;
			}
			
			$query = <<<EOS
				SELECT SQL_CALC_FOUND_ROWS $fields
				FROM $table A, $this->users B, $this->modules C
				WHERE A.user_id = B.user_id
				AND A.module_code = C.module_code
				$filter_sys_code
				$filter_str
				$add_where
	        	$sOrder
	        	$sLimit
EOS;

			$val 	= array_merge( $val, $extra_val );

			$stmt 	= $this->query($query, $val);
			
			$result = $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;	
	}
	
	public function filtered_length($aColumns, $bColumns, $params)
	{
		$result 	= array();

		try
		{
			$this->get_audit_log_list($aColumns, $bColumns, $params);
		
			$query 	= <<<EOS
				SELECT FOUND_ROWS() cnt
EOS;
			$result = $this->query($query, NULL, TRUE, FALSE);
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	public function total_length()
	{
		$result 	= array();

		try
		{
			$fields = array("COUNT(audit_trail_id) cnt");
			
			$result = $this->select_data($fields, $this->audit_trail, FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}

	public function get_first_default_name($table, $schema)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				SELECT 	*
				FROM 	information_schema.columns
				WHERE 	TABLE_NAME = ?
				AND 	TABLE_SCHEMA = ?
				AND ( ( DATA_TYPE IN ('varchar', 'char', 'text')
						AND COLUMN_NAME LIKE '%name%' )
				  OR 
					DATA_TYPE IN ('varchar', 'char', 'text') 
				  )
				  AND COLUMN_NAME NOT LIKE '%code%'
				  AND COLUMN_NAME != 'org_parent'
				LIMIT 	1
";

			$val[] 	= $table;
			$val[] 	= $schema;

			$result = $this->query($query, $val, TRUE, FALSE);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
		
	}

	public function get_reference_table($table, $column, $schema)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				SELECT 	*
				FROM 	information_schema.KEY_COLUMN_USAGE
				WHERE 	TABLE_NAME 	= ?
				AND 	COLUMN_NAME = ?
				AND 	TABLE_SCHEMA = ?
				AND 	REFERENCED_TABLE_NAME IS NOT NULL
				LIMIT 	1

";
			$val[] 	= $table;
			$val[]  = $column;
			$val[]  = $schema;

			$result = $this->query($query, $val, TRUE, FALSE);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_name_val($table, $column, $schema, $value, $name_field = NULL)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$name_str 	= '';
			if( !EMPTY( $name_field ) )
			{
				$name_str = ', '.$name_field.' as val_name ';
			}

			$query 		= "
				SELECT 	* $name_str
				FROM 	$schema.$table
				WHERE 	$column = ?

";
			$val[] 	= $value;

			$result = $this->query($query, $val, TRUE, FALSE);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;	
	}

	public function insert_select_audit_trail($date_from, $date_to)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				INSERT 	INTO %s ( user_id, module_code, activity, ip_address, user_agent, activity_date, import_audit_trail_id )
				SELECT  a.user_id, a.module_code, a.activity, a.ip_address, a.user_agent, a.activity_date, a.audit_trail_id
				FROM 	%s a
				WHERE 	DATE(a.activity_date) BETWEEN ? AND ?
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ARCHIVED_AUDIT_TRAIL, SYSAD_Model::CORE_TABLE_AUDIT_TRAIL );

			$val[] 		= $date_from;
			$val[] 		= $date_to;
			
			$this->query( $query, $val, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function insert_select_audit_trail_detail($date_from, $date_to)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				INSERT 	INTO %s ( audit_trail_id, trail_schema, action, field, prev_detail, curr_detail )
				SELECT 	c.audit_trail_id, a.trail_schema, a.action, a.field, a.prev_detail, a.curr_detail
				FROM 	%s a 
				JOIN 	%s b 
					ON 	a.audit_trail_id = b.audit_trail_id 
				JOIN 	%s c 	
					ON 	a.audit_trail_id = c.import_audit_trail_id
				WHERE 	DATE(b.activity_date) BETWEEN ? AND ?
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ARCHIVED_AUDIT_TRAIL_DETAIL, SYSAD_Model::CORE_TABLE_AUDIT_TRAIL_DETAIL, SYSAD_Model::CORE_TABLE_AUDIT_TRAIL, SYSAD_Model::CORE_TABLE_ARCHIVED_AUDIT_TRAIL );

			$val[] 		= $date_from;
			$val[] 		= $date_to;

			$this->query( $query, $val, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_select_audit_trail($date_from, $date_to)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				DELETE 
				FROM 	%s
				WHERE 	DATE(activity_date) BETWEEN ? AND ?
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_AUDIT_TRAIL );

			$val[] 		= $date_from;
			$val[] 		= $date_to;

			$this->query( $query, $val, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_select_audit_trail_detail($date_from, $date_to)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				DELETE 	a
				FROM 	%s a 
				JOIN 	%s b 
					ON 	a.audit_trail_id = b.audit_trail_id 
				WHERE 	DATE(b.activity_date) BETWEEN ? AND ?
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_AUDIT_TRAIL_DETAIL, SYSAD_Model::CORE_TABLE_AUDIT_TRAIL );

			$val[] 		= $date_from;
			$val[] 		= $date_to;

			$this->query( $query, $val, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
}