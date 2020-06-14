<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow_task_predecessors_model extends SYSAD_Model 
{
	
	public function __construct()
	{
		parent::__construct();
	}

	public function insert_task_predecessor_multi(array $val)
	{
		try
		{
			$this->insert_data( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS, $val );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_task_predecessor(array $where)
	{
		try
		{
			$this->delete_data(SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS, $where);
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_workflow_task_predecessor( $task_id )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT	a.workflow_task_id, a.pre_workflow_task_id
				FROM 	%s a
				WHERE 	a.workflow_task_id = ?
";
			$val[] 		= $task_id;
			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS );

			$result 	= $this->query( $query, $val);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function select_not_task_predecessor( array $not_task_id, $workflow_id )
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			if( !EMPTY( $not_task_id ) )
			{
				$placeholder = rtrim(str_repeat("?,", count($not_task_id)), ",");
				$add_where .= " AND a.workflow_task_id NOT IN ($placeholder) ";
				$extra_val = array_merge( $not_task_id );
			}


			$query 		= "
				SELECT	a.workflow_task_id
				FROM 	%s a
				JOIN 	%s b
				ON 		a.workflow_task_id = b.workflow_task_id
				JOIN 	%s c 
				ON 		b.workflow_stage_id = c.workflow_stage_id
				WHERE 	c.workflow_id = ?
				$add_where
				GROUP 	BY a.workflow_task_id

";

			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, SYSAD_Model::CORE_WORKFLOW_STAGES );

			$val[] 		= $workflow_id;
			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function select_helper( array $fields, array $where, array $order = array(), array $group = array() )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$result 	= $this->select_data( $fields, SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS, TRUE, $where, $order, $group );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}