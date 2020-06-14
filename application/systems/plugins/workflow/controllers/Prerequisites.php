<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Prerequisites extends SYSAD_Controller 
{
	
	private $module;
	private $dt_options 	= array();

	private $prerequisites_js;

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	public function __construct()
	{
		parent::__construct();

		$this->module = MODULE_WORKFLOW;

		$this->load->model('Workflow_task_predecessors_model', 'workflow_task_predecessors');
		$this->load->model('workflow_stage_tasks_model', 'workflow_stage_tasks');
		$this->load->model('Workflow_stages_model', 'workflow_stages');

		$this->load->module(CORE_WORKFLOW.'/Manage_workflow');

		$this->prerequisites_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/prerequisites";

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
	}

	public function load_table()
	{
		$html 			= "";

		$resources 		= array();
		$data 			= array();

		$orig_params 	= get_params();
		$stage_task 	= array();

		$first_task 	= array();

		$workflow_id 	= NULL;
		$action 		= NULL;

		try
		{
			// $this->redirect_off_system($this->module);

			$params 	= $this->set_filter( $orig_params )
							->filter_number('workflow_main', TRUE)
							->filter();

			if( !EMPTY( $params['workflow_main'] ) )
			{
				check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );

				$workflow_id 			= $params['workflow_main'];
				$action 				= $params['workflow_action'];

				$workflow_stage_task 	= $this->workflow_stage_tasks->get_workflow_stage_task( $params['workflow_main'] );

				$first_task 			= $this->check_first_stage_task( $workflow_id );

				$stage_task 			= $this->manage_workflow->process_stage_task( $workflow_stage_task );
			}

			$data['stage_task']			= $stage_task;
			$data['first_task']			= $first_task;
			$data['workflow_id']		= $workflow_id;
			$data['preq_obj'] 			= $this;
			$data['action']				= $action;

			$html 		.= $this->load->view('tables/prerequisites', $data, TRUE);
		}
		catch( PDOException $e )
		{

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		echo $html;
	}

	public function check_first_stage_task( $workflow_id )
	{
		$first_task 				= array();

		try
		{
			$first_task 			= $this->workflow_stage_tasks->check_first_stage_task( $workflow_id );
		}
		catch( PDOException $e )
		{

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		return $first_task;
	}

	public function get_previous_tasks( $workflow_id, $stage_sequence, $step_sequence )
	{
		$previous_task 				= array();

		try
		{
			$previous_task 			= $this->workflow_stage_tasks->get_previous_tasks( $workflow_id, $stage_sequence, $step_sequence );
		}
		catch( PDOException $e )
		{

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		return $previous_task;
	}

	private function _validate_data()
	{
		$required 		= array();
		$constraints 	= array();

		$sub_required 			= array();
		$sub_constraints 		= array();

		$constraints['prerequisites']		= array(
			'name'			=> 'Prerequisites',
			'data_type'		=> 'db_value',
			'field'			=> ' COUNT( workflow_task_id ) as check_task ',
			'check_field'	=> 'check_task',
			'where' 		=> 'workflow_task_id',
			'table'			=> DB_CORE.'.'.SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS
		);

		return array(
			'required'			=> $required,
			'constraints'		=> $constraints,
			'sub_required'		=> $sub_required,
			'sub_constraints'	=> $sub_constraints
		);
	}

	private function _filter( array $orig_params )
	{
		$par 			= $this->set_filter( $orig_params )
							->filter_number( 'prerequisites', TRUE );

		$params 		= $par->filter();

		return $params;
	}

	private function _validate_save( array $params, array $orig_params, $workflow_id, $action = NULL )
	{
		$arr 			= array();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			if( ISSET( $params['prerequisites'] ) AND !EMPTY( $params['prerequisites'] ) )
			{
				$cnt_val 	= 0;

				$sub_required 		= array();
				$sub_constraints 	= array();

				$validation_data 	= $this->_validate_data();

				$sub_required 		= $validation_data['required'];
				$sub_constraints 	= $validation_data['constraints'];

				$clean_val 			= array();
				$clean_params 		= array();

				$task_ids 			= array();

				foreach( $params['prerequisites'] as $task_id => $values )
				{	
					$arr_params 		= array();
					
					$task_ids[] 		= $task_id;
					$pre_step 			= $values;

					$arr_params 		= array(
						'prerequisites' => $pre_step
					);

					$clean_par 			= $this->_filter( $arr_params );

					$clean_params 		= array_merge( $clean_params, $clean_par );

					$save_val 			= $this->_process_val( $clean_params, $task_id );

					$clean_val 			= array_merge( $clean_val, $save_val );
				}

				if( !EMPTY( $sub_required ) )
				{
					$this->check_required_fields( $clean_params, $sub_required );
				}

				if( !EMPTY( $sub_constraints ) )
				{
					$this->validate_inputs( $clean_params, $sub_constraints );
				}

				if( !EMPTY( $clean_val ) AND !EMPTY( $task_ids ) ) 
				{
					$del_task_id 		= $task_ids;

					/*$not_sel_task 		= $this->workflow_task_predecessors->select_helper(
						array( 'workflow_task_id' ),
						array(
							'workflow_task_id'	=> array( 'NOT IN', $task_ids )
						),
						array(),
						array('workflow_task_id')
					);*/

					$not_sel_task 			= $this->workflow_task_predecessors->select_not_task_predecessor( $task_ids, $workflow_id );
					
					if( !EMPTY( $not_sel_task ) )
					{
						$not_task 		= array_column($not_sel_task, 'workflow_task_id');

						$del_task_id 	= array_merge( $task_ids, $not_task );
					}

					$m_where 			= array(
						'workflow_task_id'	=> array( 'IN', $task_ids )
					);

					$d_where 			= array(
						'workflow_task_id'	=> array( 'IN', $del_task_id )
					);

					if( !EMPTY( $del_task_id ) )
					{

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->workflow_task_predecessors->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS,
							$d_where
						);

						$this->workflow_task_predecessors->delete_task_predecessor( $d_where );

						$curr_detail[] 				= array();
					}

					if( !EMPTY( $clean_val ) )
					{

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[]  	= array();

						$this->workflow_task_predecessors->insert_task_predecessor_multi( $clean_val );

						$curr_detail[] 				= $this->workflow_task_predecessors->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS, 
							$m_where
						);
					}
				}
			}
			else
			{
				// $stages_id 					= array_column($workflow_stages, 'workflow_stage_id');

				$workflow_task 				= $this->workflow_stage_tasks->get_workflow_stage_task_ids( $workflow_id );

				if( !EMPTY( $workflow_task ) )
				{
					$task_ids 				= array_column($workflow_task, 'workflow_task_id');

					$m_where 			= array(
						'workflow_task_id'	=> array( 'IN', $task_ids )
					);

					$prev_task 			= $this->workflow_task_predecessors->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS,
						$m_where
					);

					if( !EMPTY( $prev_task ) )
					{

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $prev_task;

						$this->workflow_task_predecessors->delete_task_predecessor( $m_where );

						$curr_detail[] 				= array();
					}
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return array(
			'audit_schema'	=> $audit_schema,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'prev_detail'	=> $prev_detail,
			'curr_detail' 	=> $curr_detail
		);
	}

	private function _process_val( array $params, $task_id )
	{
		$arr 	= array();

		if( ISSET( $params['prerequisites'] ) AND !EMPTY( $params['prerequisites'] ) )
		{
			foreach( $params['prerequisites'] as $key => $p_r )
			{
				$arr[$key]['workflow_task_id'] 		= $task_id;
				$arr[$key]['pre_workflow_task_id'] 	= $p_r;
			}
		}

		return $arr;
	}

	public function save()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 	= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= ( !EMPTY( $orig_params['workflow_main'] ) AND !EMPTY( $orig_params['workflow_main'] ) ) ? TRUE : FALSE;
		$action 				= ( !EMPTY( $orig_params['workflow_main'] ) AND !EMPTY( $orig_params['workflow_main'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$main_where 			= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->set_filter( $orig_params )
									->filter_number( 'workflow_main', TRUE )
									->filter();

			$permission 		= ( !$update ) ? $this->add_per : $this->edit_per;
			$per_msg 			= ( !$update ) ? $this->lang->line( 'err_unauthorized_add' ) : $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}

			if( EMPTY( $params['workflow_main'] ) )
			{
				$empt_msg 		= sprintf( $this->lang->line('workflow_empty'), 'prerequisite.' );

				throw new Exception( $empt_msg );
			}

			check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );

			$check_has_stage 	= $this->workflow_stages->check_has_stage( $params['workflow_main'] );

			if( EMPTY( $check_has_stage ) OR EMPTY( $check_has_stage['check_has_stage'] ) )
			{
				$stage_msg 		= sprintf( $this->lang->line('workflow_stage_empty'), 'prerequisite.' );

				throw new Exception( $stage_msg );

			}

			$check_has_step 	= $this->workflow_stage_tasks->check_has_step( $params['workflow_main'] );

			if( EMPTY( $check_has_step ) OR EMPTY( $check_has_step['check_has_step'] ) )
			{
				$step_msg 		= sprintf( $this->lang->line('workflow_step_empty'), 'prerequisite.' );

				throw new Exception( $step_msg );

			}

			$workflow_id 		= $params['workflow_main'];

			$main_where 		= array(
				'workflow_id'	=> $workflow_id
			);
			
			SYSAD_Model::beginTransaction();

			$val 				= $this->_validate_save( $params, $orig_params, $workflow_id, $action );

			if( !EMPTY( $val['audit_schema'] ) )
			{

				$audit_schema 				= array_merge( $audit_schema, $val['audit_schema'] );
				$audit_table 				= array_merge( $audit_table, $val['audit_table'] );
				$audit_action 				= array_merge( $audit_action, $val['audit_action'] );
				$prev_detail 				= array_merge( $prev_detail, $val['prev_detail'] );
				$curr_detail 				= array_merge( $curr_detail, $val['curr_detail'] );

				$audit_name 				= 'Workflow Prerequisites.';

				$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

				$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
			}

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );

		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e)
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status'				=> $status
		);

		echo json_encode( $response );	
	}

	public function get_workflow_task_predecessor( $task_id )
	{
		$predecessors 			= array();

		try
		{
			$predecessors 		= $this->workflow_task_predecessors->get_workflow_task_predecessor( $task_id );
		}
		catch( PDOException $e )
		{

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		return $predecessors;
	}

}