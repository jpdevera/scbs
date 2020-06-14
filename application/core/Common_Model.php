<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_Model extends Base_Model {
	
	public function __construct()
	{
		parent::__construct();
	}

	public function insert_data($table, $fields, $return_new_id=FALSE, $on_dup_update=FALSE, $on_dup_field_id=NULL)
	{
		try {

			return parent::insert_data($table, $fields, $return_new_id, $on_dup_update);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

	}

	public function update_data($table, $fields, $where)
	{
		try {

			return parent::update_data($table, $fields, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

	}

	public function delete_data($table, $where)
	{
		try
		{
			return parent::delete_data($table, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	public function select_data($fields,$table, $multiple, $where = array(), $order = array(), $group = array())
	{
		try
		{
			return parent::select_data($fields,$table, $multiple, $where, $order, $group);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

}
