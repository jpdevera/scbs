<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Groups_model extends SYSAD_Model 
{
                
	public function __construct()
	{	
		parent::__construct();
	}

	public function get_groups_list( array $columns, array $filter, array $order_arr, array $params )
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

			if( ISSET( $params['status_link'] ) )
			{
				$add_where 	.= " AND a.active_flag = ? ";
				$extra_val[] = $params['status_link'];
			}

			$query 		= "
				SELECT 	SQL_CALC_FOUND_ROWS $fields
				FROM 	%s a 
				WHERE 	1 = 1
				$add_where
				$filter_str
				$order
				$limit
";

			$replacements 	= array(
				'`'.SYSAD_Model::CORE_TABLE_GROUPS.'`'
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);

			$val 		= array_merge( $val, $extra_val );

			$val 		= array_merge( $val, $filter_params );
			
			$result['aaData'] 	= $this->query( $query, $val );

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

	public function get_specific_group( $group_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.group_id, a.group_name, a.group_description, a.group_color,
						a.active_flag
				FROM 	%s a 
				WHERE 	a.group_id = ?
";
			$query 	= sprintf( $query, '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`' );

			$val[] 	= $group_id;

			$result = $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}	

	public function insert_groups( array $val )
	{
		$id 		= NULL;

		try
		{
			$id 	= $this->insert_data( '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`', $val, TRUE );
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $id;
	}

	public function update_groups( array $val, array $where )
	{
		try
		{
			$this->update_data( '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`', $val, $where );
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	public function delete_group( array $where )
	{
		try
		{
			$this->delete_data( '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`', $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_groups_status_cnt( $status )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT 	COUNT(a.group_id) as group_status_cnt
				FROM 	%s a
				WHERE 	a.active_flag = ?
";
			
			$query 		= sprintf( $query, '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`' );

			$val[] 		= $status;

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_all_groups( $status = ENUM_YES )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT	a.group_id, a.group_name
				FROM 	%s a
				WHERE 	a.active_flag = ?
";
			$query 	= sprintf( $query, '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`' );

			$val[] 	= $status;

			$result = $this->query( $query, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_group_users( $id, $user_id = NULL, $group_by = FALSE )
	{
		$result			= array();
		$val 			= array();
		$where  		= "";
		$extra_val  	= array();
		$group_by_str 	= "";

		try
		{

			if( !is_array( $id ) )
			{
				$where 	.= " AND a.group_id = ? ";
				$extra_val[] 	= $id;
			}
			else
			{
				$count_g 		= COUNT( $id ); 
				$placeholder 	= str_repeat( '?,', $count_g );
				$placeholder 	= rtrim( $placeholder, ',' );

				$where 			.= " AND a.group_id IN ( $placeholder ) ";
				$extra_val 		= array_merge( $extra_val, $id );

				if( !EMPTY( $user_id ) )
				{
					$where 			.= " AND b.user_id != ? ";	
					$extra_val[] 	= $user_id;
				}
				
			}

			$fields	 	= "b.user_id, b.fname, b.lname, a.admin_flag, 
				IF( IFNULL(b.mname, '') != '',  
					CONCAT( b.fname,' ',b.mname,' ',b.lname ),
					CONCAT( b.fname,' ',b.lname )
				) as fullname, 

				c.group_id, c.group_name

";

			if( $group_by )
			{
				$group_by_str	 = " GROUP BY b.user_id ";
			}

			$query 		= "
				SELECT 	$fields
				FROM 	%s a
				LEFT 	JOIN %s b
				ON 		b.user_id = a.user_id
				AND 	b.status = ?
				JOIN 	%s c 
				ON 		a.group_id = c.group_id
				WHERE  	1 = 1
				$where
				$group_by_str
				ORDER BY a.admin_flag DESC, b.fname ASC
			";

			$val[] 	= STATUS_ACTIVE;

			$val 	= array_merge( $val, $extra_val );

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_USER_GROUPS, SYSAD_Model::CORE_TABLE_USERS, '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`' );
			
			$result = $this->query( $query, $val );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
}