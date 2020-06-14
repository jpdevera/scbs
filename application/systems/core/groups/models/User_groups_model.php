<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_groups_model extends SYSAD_Model 
{
                
	public function __construct()
	{	
		parent::__construct();
	}
	
	public function insert_user_group( array $val )
	{
		try
		{
			$this->insert_data( SYSAD_Model::CORE_TABLE_USER_GROUPS, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_user_group( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_TABLE_USER_GROUPS, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_user_groups_details( $user_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.user_id, a.group_id, a.admin_flag, b.group_color, b.group_name
				FROM 	%s a 
				JOIN 	%s b 
				ON 		a.group_id = b.group_id
				WHERE 	a.user_id = ?
";
			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_USER_GROUPS, '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`' );

			$val[] 	= $user_id;

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_user_groups( $group_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.user_id, a.group_id, a.admin_flag
				FROM 	%s a 
				WHERE 	a.group_id = ?
";
			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_USER_GROUPS );

			$val[] 	= $group_id;

			$result = $this->query( $query, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}