<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload_model extends SYSAD_Model 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function delete_helper($table, array $where)
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

	public function insert_helper( $table, array $val )
	{
		$id 	= NULL;

		try
		{
			$id 	= $this->insert_data( $table, $val, TRUE, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function update_helper( $table, array $val, array $where )
	{
		try
		{
			$this->update_data( $table, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_file_by_desired_path( $desired_path )
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
				WHERE 	a.desired_path = ?
					$add_where
";
			$val[] 		= $desired_path;

			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_FILE_DB_STORAGE
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

	public function get_file_by_sys_file_name( $sys_file_name )
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
				WHERE 	a.sys_file_name = ?
					$add_where
";
			$val[] 		= $sys_file_name;

			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_FILE_DB_STORAGE
			);

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}