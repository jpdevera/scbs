<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow_task_actions_model extends SYSAD_Model 
{
	public function __construct()
	{	
		parent::__construct();

	}

	public function insert_task_actions(array $val)
	{
		try
		{
			$this->insert_data( SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS, $val );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_task_actions(array $where)
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS, $where );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_task_actions( $step_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT	a.workflow_task_id, a.task_action_id, a.display_status, a.process_stop_flag
				FROM 	%s a
				WHERE 	a.workflow_task_id = ?
";
	
			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS );

			$val[] 	= $step_id;

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}