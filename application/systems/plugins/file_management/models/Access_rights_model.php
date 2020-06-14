<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Access_rights_model extends SYSAD_Model 
{
	public function __construct()
	{
		parent::__construct();

	}

	public function get_access_actions( $id = NULL )
	{
		$result 	= array();
		$val 		= array();

		$add_where 	= "";
		$extra_val 	= array();

		try
		{
			if( !EMPTY( $id ) )
			{
				$add_where 	.= " AND a.sys_param_value = ? ";
				$extra_val[] = $id;
			}

			$query 	= "
				SELECT	a.sys_param_code, a.sys_param_type, a.sys_param_name, a.sys_param_value
				FROM 	%s a
				WHERE 	a.sys_param_type = ?
				AND 	a.sys_param_value IN (?,?,?)
				$add_where
";

			$val[] 	= SYS_PARAM_ACTIONS;
			$val[] 	= ACTION_EDIT;
			$val[] 	= ACTION_DELETE;
			$val[] 	= ACTION_VIEW;

			$val 	= array_merge( $val, $extra_val );

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_SYS_PARAM );

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function check_file_visibility( array $where )
	{	
		$result 		= array();

		try
		{
			$fields 	= array( 'COUNT( file_id ) as check_file_visibility' );

			$result 	= $this->select_data( $fields, SYSAD_Model::CORE_TABLE_FILE_VISIBILITY, FALSE, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function check_all_access( $file_id, $action_id )
	{
		$result 		= array();

		try
		{
			$query 		= " 
				SELECT 	COUNT( a.file_id ) as check_all_access
                FROM 	%s a
                WHERE 	a.file_id = ?
                AND 	FIND_IN_SET(?, a.actions)
";
			
			$val[] 		= $file_id;
			$val[] 		= $action_id;

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_FILE_VISIBILITY );

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function check_access_per_user_action( array $where )
	{
		$result 		= array();

		try
		{
			$fields 	= array( 'COUNT( actions ) as check_access_rights' );

			$result 	= $this->select_data( $fields, SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS, FALSE, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_file_visibility_details( $file_id )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 	 	= "
				SELECT 	a.file_id, a.visibility_id, GROUP_CONCAT( DISTINCT a.group_id ) as group_ids,
						a.actions
				FROM 	%s a 
				WHERE 	a.file_id = ?
				GROUP 	BY a.file_id
";
			$val[] 		= $file_id;

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_FILE_VISIBILITY );

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_file_access_rights( $file_id )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT 	a.file_id, a.user_id,
						GROUP_CONCAT(a.actions) as access,
						b.group_id
				FROM 	%s a
				JOIN 	%s b 
				ON 		a.file_id = b.file_id 
				AND 	a.user_id = b.user_id
				WHERE 	a.file_id = ?
				GROUP 	BY a.file_id, a.user_id
";
			$val[] 		= $file_id;

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS, SYSAD_Model::CORE_TABLE_FILE_VISIBILITY );

			$result 	= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function insert_file_visibility( array $val )
	{
		try
		{
			$this->insert_data( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function insert_file_access_rights( array $val )
	{
		try
		{
			$this->insert_data( SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_file_visibility( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_TABLE_FILE_VISIBILITY, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_file_access_rights( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_TABLE_FILE_ACCESS_RIGHTS, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
}