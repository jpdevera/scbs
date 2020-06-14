<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow_stage_tasks_model extends SYSAD_Model 
{
	public function __construct()
	{	
		parent::__construct();

	}

	public function get_workflow_stage_task_ids( $workflow_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	b.workflow_task_id
				FROM 	%s a 
				LEFT	JOIN %s b 
				ON 		a.workflow_stage_id = b.workflow_stage_id
				WHERE 	a.workflow_id = ?
				ORDER 	BY a.sequence_no, b.sequence_no

";
			$val[] 	= $workflow_id;
			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS );

			$result = $this->query( $query, $val);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_workflow_stage_task_by_stage( $stage_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query	= "
				SELECT	a.workflow_task_id
				FROM 	%s a 
				WHERE 	a.workflow_stage_id = ?
";
			$val[] 	= $stage_id;
			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS );

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_workflow_stage_task( $workflow_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.workflow_stage_id,
						a.workflow_id,
				        a.stage_name,
				        a.sequence_no,
				        b.workflow_task_id,
				        b.task_name,
				        b.sequence_no as task_sequence,
				        b.tat_in_days,
				        b.version_flag, 
				        b.get_flag,
				        c.actor_role_codes,
				        d.append_wf,
				        e.task_action_ids,
        				e.display_statuses,
        				g.value as approval_type
				FROM 	%s a 
				LEFT	JOIN %s b 
				ON 		a.workflow_stage_id = b.workflow_stage_id
				LEFT 	JOIN (
					SELECT	a1.workflow_task_id,
							a1.actor_role_code,
				            GROUP_CONCAT( actor_role_code SEPARATOR '|' ) as actor_role_codes
				    FROM 	%s a1
				    GROUP 	BY a1.workflow_task_id
				) c 	ON b.workflow_task_id = c.workflow_task_id
				LEFT 	JOIN (
					SELECT	a1.workflow_task_id,
							a1.workflow_id,
				            GROUP_CONCAT( a1.workflow_id SEPARATOR '|' ) as append_wf
				    FROM 	%s a1
				    GROUP 	BY a1.workflow_task_id
				) d 	ON b.workflow_task_id = d.workflow_task_id
				LEFT 	JOIN (
					SELECT 	a1.task_action_id, a1.workflow_task_id,
							a1.display_status,
				            GROUP_CONCAT( a1.task_action_id ORDER BY a1.workflow_task_id SEPARATOR '|'  ) as task_action_ids,
				            GROUP_CONCAT( a1.display_status ORDER BY a1.workflow_task_id SEPARATOR '|'  ) as display_statuses
				    FROM 	%s a1
				    GROUP 	BY a1.workflow_task_id
				) e 	ON b.workflow_task_id = e.workflow_task_id
				LEFT 	JOIN %s g 
					ON 	b.workflow_task_id = g.workflow_task_id
					AND g.code = ?
				WHERE 	a.workflow_id = ?
				ORDER 	BY a.sequence_no, b.sequence_no

";
			$val[] 	= 'APPROVAL_TYPE';
			$val[] 	= $workflow_id;
			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, SYSAD_Model::CORE_WORKFLOW_TASK_ROLES, SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE, SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS, SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS );

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function insert_stage_task(array $val)
	{
		$id 	= NULL;

		try
		{
			$id 	= $this->insert_data( SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, $val, TRUE );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function update_stage_task(array $val, array $where)
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, $val, $where );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_stage_task(array $where)
	{
		try
		{
			$this->delete_data(SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, $where);
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function update_sequence( $workflow_stage_id, $sequence_no )
	{
		$val 	= array();

		try
		{
			$query	= "
				UPDATE 	%s 				
				SET 	sequence_no = sequence_no - 1
				WHERE 	workflow_stage_id = ?
				AND 	sequence_no > ?
";				
			$val[] 	= $workflow_stage_id;
			$val[] 	= $sequence_no;

			$query  = sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS );

			$this->query( $query, $val, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

	}

	public function check_first_stage_task( $workflow_id )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT	b.workflow_task_id, a.workflow_stage_id
				FROM 	%s a
				JOIN 	%s b 
				ON 		a.workflow_stage_id = b.workflow_stage_id
				WHERE 	a.workflow_id = ?
				AND 	a.sequence_no = 1
				AND 	b.sequence_no = 1
";
			$val[] 		= $workflow_id;

			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS );

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_previous_tasks( $workflow_id, $stage_sequence, $step_sequence = NULL, $include_first = FALSE )
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			if( !EMPTY( $step_sequence ) )
			{

				if( !EMPTY( $include_first ) )
				{
					$add_where .= " AND CONCAT(a.sequence_no,'.',b.sequence_no) <= ? ";
				}
				else
				{
					$add_where .= " AND CONCAT(a.sequence_no,'.',b.sequence_no) < ? ";	
				}

				$extra_val[] 	= $stage_sequence.'.'.$step_sequence;
			}

			$query 		= "
				SELECT	b.workflow_task_id, b.task_name
				FROM 	%s a
				JOIN 	%s b 
				ON 		a.workflow_stage_id = b.workflow_stage_id
				WHERE 	a.workflow_id = ?
				AND 	a.sequence_no <= ?
				$add_where
				ORDER 	BY a.sequence_no, b.sequence_no
";
			$val[] 		= $workflow_id;
			$val[] 		= $stage_sequence;
			
			$val 		= array_merge( $val, $extra_val );

			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS );
			
			$result 	= $this->query( $query, $val);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function check_has_step( $workflow_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query	= "
				SELECT	COUNT(a.workflow_task_id) as check_has_step
				FROM 	%s a 
				JOIN 	%s b 
				ON 		a.workflow_stage_id = b.workflow_stage_id
				WHERE 	b.workflow_id = ?
";

			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, SYSAD_Model::CORE_WORKFLOW_STAGES );

			$val[] 	= $workflow_id;

			$result = $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_specific_step( $step_id )  
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query	= "
				SELECT	a.workflow_task_id, a.sequence_no
				FROM 	%s a 
				WHERE 	a.workflow_task_id = ?
";

			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS );

			$val[] 	= $step_id;

			$result = $this->query( $query, $val, TRUE, FALSE );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	} 

	public function check_for_prerequisite( $step_id )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT  COUNT(a.workflow_task_id) as check_prerequisites
                FROM 	%s a
                JOIN 	%s b
                ON 		b.workflow_task_id = b.workflow_task_id
                WHERE 	a.workflow_task_id = ?
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS );

			$val[] 		= $step_id;
			
			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function insert_task_other_details(array $val)
	{
		$id 	= NULL;

		try
		{
			$id 	= $this->insert_data( SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS, $val, TRUE, TRUE );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function update_task_other_details(array $val, array $where)
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS, $val, $where );
		}	
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_task_other_details(array $where)
	{
		try
		{
			$this->delete_data(SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS, $where);
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
}