<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stages extends SYSAD_Controller 
{
	
	private $module;
	private $dt_options 	= array();

	private $stage_js;
	private $steps_js;

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_WORKFLOW;

		$this->stages_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/stages";

		$this->load->model('Workflow_stages_model', 'workflow_stages');
		$this->load->model('Workflow_stage_tasks_model', 'workflow_stage_tasks');
		$this->load->model('Workflow_task_predecessors_model', 'workflow_task_predecessors');
		$this->load->model('Workflow_task_roles_model', 'workflow_task_roles');
		$this->load->model('Workflow_task_appendable_model', 'workflow_task_appendable');
		$this->load->model('Workflow_task_actions_model', 'workflow_task_actions');

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

		$workflow_stages = array();
		$stage_cnt 		 = "";
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

				$action 	= $params['workflow_action'];

				$workflow_stages = $this->workflow_stages->get_workflow_stages( $params['workflow_main'] );

				$stage_cnt 	= count( $workflow_stages );
			}

			$data['workflow_stages']	= $workflow_stages;
			$data['stage_cnt']			= $stage_cnt;
			$data['action'] 			= $action;

			$html 		.= $this->load->view('tables/stages', $data, TRUE);
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

	private function _validate( array $params, array $orig_params, $action = NULL )
	{
		$arr 					= array();

		$required 				= array();
		$constraints 			= array();

		try
		{
			$required['stage_name']			= 'Stage Name';
			$constraints['stage_name']		= array(
				'name'			=> 'Stage Name',
				'data_type'		=> 'string',
				'max_len'		=> '100'
			);

			$required['stage_sequence']		= 'Stage Sequence';
			$constraints['stage_sequence']	= array(
				'name'			=> 'Stage Sequence',
				'data_type'		=> 'digit'
			);

			$this->check_required_fields( $params, $required );

			$this->validate_inputs( $params, $constraints );

			if( ISSET( $params['stage_sequence'] ) AND !EMPTY( $params['stage_sequence'] ) )
			{
				foreach( $params['stage_sequence'] as $key => $s_q )
				{
					$stage_name 	= ( ISSET( $params['stage_name'][ $key ] ) ) ? $params['stage_name'][ $key ] : '';

					if( ISSET( $params['workflow_stage'][$key] ) AND !EMPTY( $params['workflow_stage'][$key] ) )
					{
						check_salt( $params['workflow_stage'][$key], $params['workflow_stage_salt'][$key], $params['workflow_stage_token'][$key] );

						$arr[$key]['workflow_stage_id']	= $params['workflow_stage'][$key];
					}
					else
					{
						$arr[$key]['workflow_stage_id']	= NULL;
					}

					$arr[$key]['stage_name'] 	= $stage_name;
					$arr[$key]['sequence_no']	= $s_q;
					$arr[$key]['workflow_id']	= $params['workflow_main'];
					$arr[$key]['skip_flag']		= ( !EMPTY( $params['skippable_hid'][$key] ) ) ? ENUM_YES : ENUM_NO;
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch (Exception $e) 
		{
			throw $e;
		}

		return $arr;
	}

	private function _adjust_prerequisites( array $curr_stage_det, array $prev_stage_det, $workflow_id )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		$stage_adj 				= array();

		try
		{
			if( !EMPTY( $curr_stage_det ) AND !EMPTY( $prev_stage_det ) )
			{
				$check_first_stage_task 	= $this->workflow_stage_tasks->check_first_stage_task( $workflow_id );

				foreach( $curr_stage_det as $c_k => $c_r )
				{
					if( ISSET( $prev_stage_det[ $c_k ] ) )
					{
						if( $prev_stage_det[ $c_k ]['sequence_no'] != $c_r['sequence_no'] )
						{
							if( $c_r['sequence_no'] < $prev_stage_det[ $c_k ]['sequence_no'] )
							{
								$stage_adj[$c_k]['workflow_stage_id']	= $c_r['workflow_stage_id'];
								$stage_adj[$c_k]['sequence_no']			= $c_r['sequence_no'];

								if( !EMPTY( $check_first_stage_task ) )
								{
									if( $check_first_stage_task['workflow_stage_id'] == $c_r['workflow_stage_id'] )
									{
										$f_where 	= array(
											'workflow_task_id'	=> $check_first_stage_task['workflow_task_id']
										);

										$audit_schema[] 	= DB_CORE;
										$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS;
										$audit_action[] 	= AUDIT_DELETE;
										$prev_detail[]  	= $this->workflow_task_predecessors->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS,
											$f_where
										);

										$this->workflow_task_predecessors->delete_task_predecessor( $f_where );

										$curr_detail[] 				= array();
									}
								}
							}
						}
					}
				}
			}

			if( !EMPTY( $stage_adj ) )
			{
				foreach( $stage_adj as $s_k => $stage )
				{
					$prerequisites 	= $this->workflow_stage_tasks->get_previous_tasks( $workflow_id, $stage['sequence_no'] );

					if( !EMPTY( $prerequisites ) )
					{
						$pre_req_ids = array_column($prerequisites, 'workflow_task_id');

						$d_where 			= array(
							'pre_workflow_task_id' 	=> array( 'NOT IN', $pre_req_ids ),
							'workflow_task_id'		=> array( 'IN', $pre_req_ids )
						);

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->workflow_task_predecessors->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS,
							$d_where
						);

						$this->workflow_task_predecessors->delete_task_predecessor( $d_where );

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

		$stage_adj 				= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->set_filter( $orig_params )
									->filter_number( 'workflow_stage', TRUE )
									->filter_string( 'stage_name' )
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
				$empt_msg 		= sprintf( $this->lang->line('workflow_empty'), 'stage.' );

				throw new Exception( $empt_msg );
			}

			check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );

			$workflow_id 		= $params['workflow_main'];

			$main_where 		= array(
				'workflow_id'	=> $workflow_id
			);

			$val 				= $this->_validate( $params, $orig_params, $action );

			SYSAD_Model::beginTransaction();

			if( !$update )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_STAGES;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$this->workflow_stages->insert_workflow_stages( $val );

				$curr_detail[] 	 	= $this->workflow_stages->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_STAGES,
					$main_where
				);
			}
			else
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_STAGES;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_stage_det 	= $this->workflow_stages->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_STAGES,
					$main_where
				);
				$prev_detail[]  	= $prev_stage_det;

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_STAGES;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$this->workflow_stages->insert_workflow_stages( $val );

				$curr_stage_det 	= $this->workflow_stages->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_STAGES,
					$main_where
				);

				$curr_detail[] 	 	= $curr_stage_det;

				$adjust_pre_req 	= $this->_adjust_prerequisites( $curr_stage_det, $prev_stage_det, $workflow_id );

				if( !EMPTY( $adjust_pre_req['audit_schema'] ) )
				{
					$audit_schema 		= array_merge( $audit_schema, $adjust_pre_req['audit_schema'] );
					$audit_table 		= array_merge( $audit_table, $adjust_pre_req['audit_table'] );
					$audit_action 		= array_merge( $audit_action, $adjust_pre_req['audit_action'] );
					$prev_detail 		= array_merge( $prev_detail, $adjust_pre_req['prev_detail'] );
					$curr_detail 		= array_merge( $curr_detail, $adjust_pre_req['curr_detail'] );
				}
			}

			$audit_name 				= 'Workflow Stage.';

			$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

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
			'status'				=> $status,
			'orig_params' 			=> $orig_params
		);

		echo json_encode( $response );	
	}

	public function delete_stage()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();

		$orig_params 			= get_params();

		$delete_per 			= $this->delete_per;

		$msg 					= '';
		$flag 					= 0;
		$status 				= ERROR;

		$main_where 			= array();

		try
		{
			// $this->redirect_off_system($this->module);
			
			$params 			= $this->set_filter( $orig_params )
									->filter_number('stage_id', TRUE)
									->filter();

			check_salt( $params['stage_id'], $params['stage_salt'], $params['stage_token'] );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'workflow_stage_id'	=> $params['stage_id']
			);

			SYSAD_Model::beginTransaction();

			$task_ids 			= $this->workflow_stage_tasks->get_workflow_stage_task_by_stage( $params['stage_id'] );

			if( !EMPTY( $task_ids ) )
			{
				$t_id 			= array_column($task_ids, 'workflow_task_id');
				
				$sub_where 		= array(
					'workflow_task_id'	=> array( 'IN', $t_id )
				);

				$pd_where 			= array(
					'pre_workflow_task_id'	=> array( 'IN', $t_id )
				);

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->workflow_task_predecessors->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS,
					$pd_where
				);

				$this->workflow_task_predecessors->delete_task_predecessor( $pd_where );

				$curr_detail[] 				= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->workflow_task_predecessors->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_PREDECESSORS,
					$sub_where
				);

				$this->workflow_task_predecessors->delete_task_predecessor( $sub_where );

				$curr_detail[] 				= array();

				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $this->workflow_task_appendable->get_details_for_audit(
					SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE,
					$sub_where
				);

				$this->workflow_task_appendable->delete_stage_task_appendable( $sub_where );

				$curr_detail[] 				= array();

				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_ROLES;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $this->workflow_task_roles->get_details_for_audit(
					SYSAD_Model::CORE_WORKFLOW_TASK_ROLES,
					$sub_where
				);

				$this->workflow_task_roles->delete_stage_task_roles( $sub_where );

				$curr_detail[] 				= array();

				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $this->workflow_task_actions->get_details_for_audit(
					SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS,
					$sub_where
				);

				$this->workflow_task_actions->delete_task_actions( $sub_where );

				$curr_detail[] 				= array();

				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit(
					SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS,
					$sub_where
				);

				$this->workflow_stage_tasks->delete_task_other_details( $sub_where );

				$curr_detail[] 				= array();

				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit(
					SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS,
					$sub_where
				);

				$this->workflow_stage_tasks->delete_stage_task( $sub_where );

				$curr_detail[] 				= array();
			}

			$prev_stage 				= $this->workflow_stages->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_STAGES,
				$main_where
			);

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_STAGES;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $prev_stage;

			$this->workflow_stages->delete_stage( $main_where );

			$curr_detail[] 				= array();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_STAGES;
			$audit_action[] 			= AUDIT_UPDATE;
			$prev_detail[] 				= $this->workflow_stages->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_STAGES,
				$main_where
			);

			$this->workflow_stages->update_sequence( $prev_stage[0]['workflow_id'], $prev_stage[0]['sequence_no'] );

			$curr_detail[] 				= $this->workflow_stages->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_STAGES,
				$main_where
			);

			$audit_activity				= sprintf($this->lang->line( 'audit_trail_delete'), $prev_stage[0]['stage_name'] );

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();

			$msg 				= $this->lang->line('data_deleted');
			$flag 				= 1;
			$status 			= SUCCESS;
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
			"flag" 					=> $flag,
			"msg" 					=> $msg,
			'status'				=> $status,
			'reload'				=> 'function',
			'function'				=> 'Stages.load_table();Steps.load_table();'
		);

		echo json_encode( $response );
	}

	public function check_for_prerequsite()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 	= get_params();

		$check 			= 0;

		try
		{
			$params 			= $this->set_filter( $orig_params )
									->filter_number( 'workflow_stage', TRUE )
									->filter_number('workflow_main', TRUE)
									->filter_number('sequence')
									->filter();

			check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );
			check_salt( $params['workflow_stage'], $params['workflow_stage_salt'], $params['workflow_stage_token'] );

			$stage_det 			= $this->workflow_stages->get_specific_stage( $params['workflow_stage'] ); 

			if( !EMPTY( $stage_det ) )
			{
				if( $stage_det['sequence_no'] != $params['sequence'] )
				{
					if( $params['sequence'] < $stage_det['sequence_no'] )
					{
						$check_for_prerequisite 	= $this->workflow_stages->check_for_prerequisite( $params['workflow_stage'] );
						
						if( !EMPTY( $check_for_prerequisite ) AND !EMPTY( $check_for_prerequisite['check_prerequisites'] ) )
						{
							$check 	= 1;
						}
					}
				}
			}
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

		$response 	= array(
			'check'	=> $check
		);

		echo json_encode( $response );
	}
}