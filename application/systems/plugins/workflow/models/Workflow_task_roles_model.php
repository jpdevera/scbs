<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow_task_roles_model extends SYSAD_Model 
{
	public function __construct()
	{	
		parent::__construct();

	}

	public function insert_stage_task_roles(array $val)
	{
		try
		{
			$this->insert_data( SYSAD_Model::CORE_WORKFLOW_TASK_ROLES, $val );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_stage_task_roles(array $where)
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_WORKFLOW_TASK_ROLES, $where );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}
	}
}