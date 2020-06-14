<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Workflow_model extends SYSAD_Model {
	
	private $process;
	private $users;
	
	public function __construct()
	{	
		parent::__construct();
		
		$this->process = parent::CORE_TABLE_PROCESS;
		$this->users = parent::CORE_TABLE_USERS;
	}
                
	public function get_workflows($aColumns, $bColumns, $params)
	{
		try
		{
			$cColumns = array("A.process_id", "A.name", "A.description", "A.num_stages", "CONCAT(B.fname, ' ', B.lname)");
			
			$fields = str_replace(" , ", " ", implode(", ", $aColumns));
			
			$sWhere = $this->filtering($cColumns, $params, TRUE);
			$sOrder = $this->ordering($bColumns, $params);
			$sLimit = $this->paging($params);
			
			$filter_str = $sWhere["search_str"];
			$filter_params = $sWhere["search_params"];
			
			$query = <<<EOS
				SELECT SQL_CALC_FOUND_ROWS $fields 
				FROM $this->process A, $this->users B
				WHERE A.created_by = B.user_id 
				$filter_str
	        	$sOrder
	        	$sLimit
EOS;

			$val = array($filter_params);
			$stmt = $this->query($query, $val);
			
			return $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;			
		}
	}
	
	public function filtered_length($aColumns, $bColumns, $params)
	{
		try
		{
			$this->get_workflows($aColumns, $bColumns, $params);
			
			$query = <<<EOS
				SELECT FOUND_ROWS() cnt
EOS;
	
			$stmt = $this->query($query, NULL, TRUE, FALSE);
		
			return $stmt;
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;			
		}	
	}
	
	
	public function total_length()
	{
		try
		{
			$fields = array("COUNT(process_id) cnt");
			
			return $this->select_data($fields, $this->process, FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;			
		}	
	}

	public function insert_workflows( array $val )
	{
		$id 		= NULL;

		try
		{
			$id 	= $this->insert_data( SYSAD_Model::CORE_WORKFLOWS, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function update_workflow( array $val, array $where )
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_WORKFLOWS, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_workflow_list( array $columns, array $filter, array $order_arr, array $params )
	{
		$val 					= array();
		$result 				= array();
		$filter_str 			= '';
		$filter_params			= '';

		$add_where 				= '';
		$extra_val 			 	= array();

		try
		{
			$fields 			= str_replace( " , " , " ", implode( ", ", $columns ) );

			$where 				= $this->filtering( $filter, $params, TRUE );

			$order 				= $this->ordering( $order_arr, $params );

			$limit 				= $this->paging($params);

			$filter_str 		= $where['search_str'];

			$filter_params 		= $where['search_params'];

			if( ISSET( $params['search_status_link'] ) )
			{
				$add_where 	.= " AND a.active_flag = ? ";
				$extra_val[] = $params['search_status_link'];
			} 
			else if( ISSET( $params['status_link'] ) )
			{
				$add_where 	.= " AND a.active_flag = ? ";
				$extra_val[] = $params['status_link'];
			}


			if( ISSET( $params['search_append_link'] ) )
			{
				$add_where 	.= " AND a.appendable_flag = ? ";
				$extra_val[] = $params['search_append_link'];
			}
			else if( ISSET( $params['append_link'] ) )
			{
				$add_where 	.= " AND a.appendable_flag = ? ";
				$extra_val[] = $params['append_link'];
			}

			$query 		= "
				SELECT 	SQL_CALC_FOUND_ROWS $fields
				FROM 	%s a 
				LEFT 	JOIN (
					SELECT	a1.workflow_id, COUNT(a1.workflow_stage_id) as count_stages
					FROM 	%s a1
					GROUP 	BY a1.workflow_id
				) b 	ON a.workflow_id = b.workflow_id
				WHERE 	1 = 1
				$add_where
				$filter_str
				$order
				$limit
";
			$replacements 	= array(
				SYSAD_Model::CORE_WORKFLOWS,
				SYSAD_Model::CORE_WORKFLOW_STAGES
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);

			$val 		= array_merge( $val, $extra_val );

			$val 		= array_merge( $val, $filter_params );
			
			$result['aaData'] 	= $this->query( $query, $val);

			$query2 			= "
				SELECT 	FOUND_ROWS() filtered_length
";

			$result['filtered_length'] 	= $this->query( $query2, array(), TRUE, FALSE );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_workflow_specific( $worklow_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.workflow_id, a.workflow_name, a.description, a.appendable_flag, a.active_flag
				FROM 	%s a
				WHERE 	a.workflow_id = ?
";

			$val[] 	= $worklow_id;

			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOWS );

			$result = $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	public function get_appendable_workflows( $workflow_id = NULL )	
	{
		$result 	= array();
		$val 		= array();

		$add_where 	= "";
		$extra_val 	= array();

		try
		{
			if( !EMPTY( $worklow_id ) )
			{
				$add_where 		.= " AND a.workflow_id != ? ";
				$extra_val[]	= $worklow_id;
			}

			$query 	= "
				SELECT	a.workflow_id, a.workflow_name
				FROM 	%s a
				JOIN 	%s b 
				ON 		a.workflow_id = b.workflow_id
				WHERE 	a.appendable_flag = ?
				AND 	a.active_flag = ?
				$add_where
				GROUP 	BY a.workflow_id
";
			$query 	= sprintf( $query, SYSAD_Model::CORE_WORKFLOWS, SYSAD_Model::CORE_WORKFLOW_STAGES );

			$val[] 	= ENUM_YES;
			$val[] 	= ENUM_YES;

			$val 	= array_merge( $val, $extra_val );

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_workflow_sub_table_audit( $workflow_id )
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT	a.*, b.*, c.*, d.*, e.*
				FROM 	%s a
				JOIN 	%s b 
				ON 		a.workflow_stage_id = b.workflow_stage_id
				LEFT 	JOIN %s c 
				ON 		b.workflow_task_id = c.workflow_task_id
				LEFT 	JOIN %s d 
				ON 		b.workflow_task_id = d.workflow_task_id
				LEFT 	JOIN %s e 
				ON 		b.workflow_task_id = e.workflow_task_id
				WHERE 	a.workflow_id = ?
";

			$query 	= sprintf($query, 
				SYSAD_Model::CORE_WORKFLOW_STAGES, 
				SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS,
				SYSAD_Model::CORE_WORKFLOW_TASK_ROLES,
				SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE,
				SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS

			);

			$val[] 	= $workflow_id;

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function delete_workflow( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_WORKFLOWS, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_workflow_status_cnt( $status )
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT 	COUNT(a.workflow_id) as workflow_status_cnt
				FROM 	%s a
				WHERE 	a.active_flag = ?
";
			
			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOWS );

			$val[] 		= $status;

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_appendable_workflows_cnt()
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT 	COUNT(a.workflow_id) as workflow_appendable_cnt
				FROM 	%s a
				WHERE 	a.appendable_flag = ?
";
			
			$query 		= sprintf( $query, SYSAD_Model::CORE_WORKFLOWS );

			$val[] 		= ENUM_YES;

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}