<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Announcements_model extends SYSAD_Model 
{
	
	private $announcements;
	private $user_announcements;
                
	public function __construct()
	{
		parent::__construct();
		
		$this->announcements 			= parent::CORE_TABLE_ANNOUNCEMENTS;
		$this->user_announcements 		= parent::CORE_TABLE_USER_ANNOUNCEMENTS;
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
               	WHERE 	1 = 1
				$add_where
				$filter_str
				$order
				$limit	
";
			$replacements 	= array(
				SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS,
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

	public function get_users( $role = NULL )
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
				SELECT 	a.user_id
				FROM 	%s a 
				WHERE 	a.status = ?
					AND a.user_id != 0
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_USERS );

			$val[] 		= STATUS_ACTIVE;			

			$result 	= $this->query($query, $val, TRUE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;			
		
	}

	public function get_specific_announcement($announcement_id)
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
				WHERE 	a.announcement_id = ?
";
			$query 		= sprintf( $query, $this->announcements );

			$val[] 		= $announcement_id;

			$result 	= $this->query($query, $val, TRUE, FALSE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;		
	}
	
	public function get_all_user_announcements_not_read($user_id, $read_flag = ENUM_NO)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			if( !EMPTY( $read_flag ) )
			{
				$add_where .= " AND b.read_flag = ? ";
				$extra_val[] = $read_flag;
			}

			$query 		= "
				SELECT 	a.*, b.user_id
				FROM 	%s a 
				JOIN 	%s b ON a.announcement_id = b.announcement_id
				WHERE 	b.user_id = ?
				 $add_where
";
			$query 		= sprintf( $query, $this->announcements, $this->user_announcements );

			$val[] 		= $user_id;
			
			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query($query, $val, TRUE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function insert_user_announcement( array $val )
	{
		$id 	= NULL;

		try
		{
			$id = $this->insert_data( SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS, $val, TRUE, TRUE );
		}
		catch( PDOException $e )    
		{
			throw $e;
		}

		return $id;
	}

	public function update_user_announcements( array $val, array $where )
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_user_announcement( array $where )
	{
		try
		{	
			$this->delete_data( SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}


	public function insert_announcement( array $val )
	{
		$id 	= NULL;

		try
		{
			$id = $this->insert_data( SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS, $val, TRUE, TRUE );
		}
		catch( PDOException $e )    
		{
			throw $e;
		}

		return $id;
	}

	public function update_announcement( array $val, array $where )
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_announcement( array $where )
	{
		try
		{	
			$this->delete_data( SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
}