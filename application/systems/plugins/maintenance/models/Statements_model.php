<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Statements_model extends SYSAD_Model 
{
	private $statements;
	private $statement_uploads;
	private $statement_module_types;
	private $statement_types;
                
	public function __construct()
	{
		parent::__construct();

		$this->statements 			= parent::CORE_TABLE_STATEMENTS;
		$this->statement_uploads 	= parent::CORE_TABLE_STATEMENT_UPLOADS;
		$this->statement_module_types = parent::CORE_TABLE_STATEMENT_MODULE_TYPES;
		$this->statement_types 		= parent::CORE_TABLE_STATEMENT_TYPES;
		
	}

	public function get_list( array $columns, array $filter, array $order_arr, array $params )
	{
		$val 					= array();
		$result 				= array();
		$filter_str 			= '';
		$filter_params			= '';

		$add_where 				= '';
		$extra_val 			 	= array();

		try
		{
			$fields 			= str_replace( " , " , " ", implode( ", ", $columns ) );

			$where 				= $this->filtering( $filter, $params, TRUE );

			$order 				= $this->ordering( $order_arr, $params );

			$limit 				= $this->paging($params);

			$filter_str 		= $where['search_str'];

			$filter_params 		= $where['search_params'];

			$query 		= "
				SELECT 	SQL_CALC_FOUND_ROWS $fields
                FROM 	%s a 
                LEFT	JOIN %s b 
                	ON 	a.statement_id = b.statement_id
                LEFT 	JOIN %s c 
                	ON 	a.statement_module_type_id = c.statement_module_type_id
                LEFT 	JOIN %s d 
                	ON 	a.statement_type_id = d.statement_type_id
               	WHERE 	1 = 1
				$add_where
				$filter_str
				GROUP 	BY a.statement_id
				$order
				$limit	
";
			$replacements 	= array(
				$this->statements,
				$this->statement_uploads,
				$this->statement_module_types,
				$this->statement_types
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);

			$val 		= array_merge( $val, $extra_val );

			$val 		= array_merge( $val, $filter_params );
			
			$result['aaData'] 	= $this->query( $query, $val, TRUE );
			
			$query2 			= "
				SELECT 	FOUND_ROWS() filtered_length
";
	
			$result['filtered_length'] 	= $this->query( $query2, array(), TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_specific_statement($statement_id)
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
				SELECT 	a.*,
						GROUP_CONCAT(b.sys_file_name ORDER BY b.statement_upload_id) as sys_file_names,
						GROUP_CONCAT(b.orig_file_name ORDER BY b.statement_upload_id) as orig_file_names,
						GROUP_CONCAT(b.custom_filename ORDER BY b.statement_upload_id) as custom_filenames,
						GROUP_CONCAT(DISTINCT c.statement_token) as statement_tokens
				FROM 	%s a 
				LEFT 	JOIN %s b 
					ON 	a.statement_id = b.statement_id
				LEFT 	JOIN %s c 
					ON 	a.statement_id = c.statement_id
				WHERE 	a.statement_id = ?
";
			$query 		= sprintf( $query, $this->statements, $this->statement_uploads, 
				SYSAD_Model::CORE_TABLE_STATEMENT_TOKENS
			);

			$val[] 		= $statement_id;

			$result 	= $this->query($query, $val, TRUE, FALSE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;		
	}

	public function get_specific_statement_by_statement_code($statement_code)
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
				SELECT 	a.*,
						GROUP_CONCAT(b.sys_file_name ORDER BY b.statement_upload_id) as sys_file_names,
						GROUP_CONCAT(b.orig_file_name ORDER BY b.statement_upload_id) as orig_file_names,
						GROUP_CONCAT(b.custom_filename ORDER BY b.statement_upload_id) as custom_filenames
				FROM 	%s a 
				LEFT 	JOIN %s b 
					ON 	a.statement_id = b.statement_id
				WHERE 	a.statement_code = ?
";
			$query 		= sprintf( $query, $this->statements, $this->statement_uploads );

			$val[] 		= $statement_code;

			$result 	= $this->query($query, $val, TRUE, FALSE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;		
	}

	public function get_specific_statement_many(array $statement_id)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			if( !EMPTY( $statement_id ) )
			{
				$count 				= count( $statement_id );
				$placeholder 		= str_repeat('?,', $count);
				$placeholder 		= rtrim($placeholder, ',');

				$add_where .= " AND a.statement_id IN ( $placeholder ) ";
				$extra_val	= array_merge( $extra_val, $statement_id );
			}

			$query 		= "
				SELECT 	a.*,
						GROUP_CONCAT(b.sys_file_name ORDER BY b.statement_upload_id) as sys_file_names,
						GROUP_CONCAT(b.orig_file_name ORDER BY b.statement_upload_id) as orig_file_names,
						GROUP_CONCAT(b.custom_filename ORDER BY b.statement_upload_id) as custom_filenames
				FROM 	%s a 
				LEFT 	JOIN %s b 
					ON 	a.statement_id = b.statement_id
				WHERE 	1 = 1
					$add_where
				GROUP 	BY a.statement_id
";
			$query 		= sprintf( $query, $this->statements, $this->statement_uploads );

			$val 		= array_merge($val, $extra_val);
			;
			$result 	= $this->query($query, $val, TRUE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;		
	}

	public function insert_statements( array $val )
	{
		$id 	= NULL;

		try
		{
			$id = $this->insert_data( $this->statements, $val, TRUE, TRUE );
		}
		catch( PDOException $e )    
		{
			throw $e;
		}

		return $id;
	}

	public function update_statements( array $val, array $where )
	{
		try
		{
			$this->update_data( $this->statements, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_statements( array $where )
	{
		try
		{	
			$this->delete_data( $this->statements, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function insert_statement_uploads( array $val )
	{
		$id 	= NULL;

		try
		{
			$id = $this->insert_data( $this->statement_uploads, $val, TRUE, TRUE );
		}
		catch( PDOException $e )    
		{
			throw $e;
		}

		return $id;
	}

	public function update_statement_uploads( array $val, array $where )
	{
		try
		{
			$this->update_data( $this->statement_uploads, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_statement_uploads( array $where )
	{
		try
		{	
			$this->delete_data( $this->statement_uploads, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_helper( $table, array $where )
	{
		try
		{	
			$this->delete_data( $table, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_statement_module_types()
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
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	1 = 1
					$add_where
";
			$query 		= sprintf( $query,
				$this->statement_module_types
			);

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}


	public function get_statement_types()
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
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	1 = 1
					$add_where
";
			$query 		= sprintf( $query,
				$this->statement_types
			);

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}