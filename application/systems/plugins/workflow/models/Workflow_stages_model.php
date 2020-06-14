<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow_stages_model extends SYSAD_Model 
{
	public function __construct()
	{	
		parent::__construct();

	}

	public function insert_workflow_stages(array $ins_val)
	{
		$val 			= array();
		$placeholder 	= '';

		$fields 		= '';
		$fields_arr 	= array();

		$on_dup_str 	= '';

		try
		{	
			if( !EMPTY( $ins_val ) )
			{
				foreach( $ins_val as $main_key => $m_v )
				{
					$placeholder .= "(";

					$count		= count( $m_v );

					$val 		= array_merge( $val, array_values( $m_v ) );

					$fields_arr = array_keys( $m_v );
					$fields 	= implode(',', $fields_arr);

					$placeholder .= str_repeat( '?,', $count );
					$placeholder = rtrim( $placeholder, ',' );

					$placeholder .= "),";
				}

				$placeholder 	= rtrim( $placeholder, ',' );

				if( !EMPTY( $fields_arr ) )
				{
					foreach( $fields_arr as $f )
					{
						$on_dup_str .= $f." = VALUES( ".$f." ),";
					}

					$on_dup_str  	.= rtrim( $on_dup_str, ',' );
				}

				$query 	= "
					INSERT INTO %s
					( $fields )
					VALUES $placeholder
					ON DUPLICATE KEY UPDATE 
					$on_dup_str
";	

				$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES );

				$this->query( $query, $val , FALSE);
			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	public function insert_workflow_stages_single( array $val )
	{
		$id 		= NULL;

		try
		{
			$id 	= $this->insert_data( SYSAD_Model::CORE_WORKFLOW_STAGES, $val, TRUE );
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $id;
	}

	public function delete_stage( array $where )
	{
		try
		{
			$this->delete_data(SYSAD_Model::CORE_WORKFLOW_STAGES, $where);
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_workflow_stages( $worflow_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.workflow_id, a.workflow_stage_id, a.stage_name, a.sequence_no, a.skip_flag
				FROM 	%s a 
				WHERE 	a.workflow_id = ?
				ORDER 	BY a.sequence_no
";
			$val[] 	= $worflow_id;

			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES );

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function update_sequence( $workflow_id, $sequence_no )
	{
		$val 	= array();

		try
		{
			$query	= "
				UPDATE 	%s 				
				SET 	sequence_no = sequence_no - 1
				WHERE 	workflow_id = ?
				AND 	sequence_no > ?
";				
			$val[] 	= $workflow_id;
			$val[] 	= $sequence_no;

			$query  = sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES );

			$this->query( $query, $val, FALSE);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

	}

	public function get_specific_stage( $stage_id )  
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query	= "
				SELECT	a.workflow_stage_id, a.sequence_no
				FROM 	%s a 
				WHERE 	a.workflow_stage_id = ?
";

			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES );

			$val[] 	= $stage_id;

			$result = $this->query( $query, $val, TRUE, FALSE );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	} 

	public function check_has_stage( $workflow_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query	= "
				SELECT	COUNT(a.workflow_stage_id) as check_has_stage
				FROM 	%s a 
				WHERE 	a.workflow_id = ?
";

			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES );

			$val[] 	= $workflow_id;

			$result = $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function check_for_prerequisite( $stage_id )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT  COUNT(a.workflow_stage_id) as check_prerequisites
                FROM 	%s a
                JOIN 	%s b 
                ON 		a.workflow_stage_id = b.workflow_stage_id
                JOIN 	%s c
                ON 		b.workflow_task_id = c.workflow_task_id
                WHERE 	a.workflow_stage_id = ?
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOW_STAGES, SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS );

			$val[] 		= $stage_id;
			
			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}