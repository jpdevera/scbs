<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_validate_model extends SYSAD_Model 
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

	public function validate_db_value( $table, $field, array $where )
	{
		$result 		= array();

		try
		{
			$result		= $this->select_data( $field, $table, FALSE, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}