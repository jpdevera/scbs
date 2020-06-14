<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Steps extends SYSAD_Controller 
{

	const BY_ROLE 			= 'BY_ROLE',
		  BY_POSITION 		= 'BY_POSITION';
	
	private $module;
	private $dt_options 	= array();

	private $steps_js;

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	protected $approval_type_arr = array();


	public function __construct()
	{
		parent::__construct();

		$this->module = MODULE_WORKFLOW;

		$this->steps_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/steps";

		$this->load->model('Workflow_stages_model', 'workflow_stages');
		$this->load->model('Workflow_stage_tasks_model', 'workflow_stage_tasks');
		$this->load->model('Workflow_model', 'workflow_mod');
		$this->load->model(CORE_USER_MANAGEMENT.'/Roles_model', 'roles');
		$this->load->model('Workflow_task_roles_model', 'workflow_task_roles');
		$this->load->model('Workflow_task_appendable_model', 'workflow_task_appendable');
		$this->load->model('Workflow_task_actions_model', 'workflow_task_actions');
		$this->load->model('Param_task_actions_model', 'param_task_actions');
		$this->load->model('Workflow_task_predecessors_model', 'workflow_task_predecessors');

		$this->load->module(CORE_WORKFLOW.'/Manage_workflow');

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );

		$this->approval_type_arr 	= array(
			self::BY_ROLE 		=> 'Role',
			self::BY_POSITION 	=> 'Position'
		);
	}

	public function load_table()
	{
		$html 			= "";

		$resources 		= array();
		$data 			= array();

		$orig_params 	= get_params();

		$workflow_stage_task = array();
		$stage_task 		 = array();

		$first_stage_name 	 = 'Stage 1';

		$roles 			= array();
		$append_wf 		= array();

		$workflow_id 	= NULL;

		$action 		= NULL;

		try
		{
			// $this->redirect_off_system($this->module);

			$params 	= $this->set_filter( $orig_params )
							->filter_number('workflow_main', TRUE)
							->filter();

			$check_name = 'stages_flag';
			$check 		= get_setting( WORKFLOW_FLAG, $check_name );

			if( !EMPTY( $params['workflow_main'] ) )
			{
				check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );

				$action 				= $params['workflow_action'];

				$workflow_id 			= $params['workflow_main'];

				$workflow_stage_task 	= $this->workflow_stage_tasks->get_workflow_stage_task( $params['workflow_main'] );

				$stage_task 			= $this->manage_workflow->process_stage_task( $workflow_stage_task );

				if( EMPTY( $check ) )
				{
					$workflow_details 	= $this->workflow->get_workflow_specific( $params['workflow_main'] );

					$first_stage_name 	= $workflow_details['workflow_name'];
				}
			}

			$roles 						= $this->roles->get_roles();
			$append_wf 					= $this->workflow_mod->get_appendable_workflows( $workflow_id );

			$data['stage_task']			= $stage_task;
			$data['first_stage_name']	= $first_stage_name;
			$data['roles']				= $roles;
			$data['append_wf'] 			= $append_wf;
			$data['action'] 			= $action;

			$data['approval_type_arr']  = $this->approval_type_arr;

			$html 		.= $this->load->view('tables/steps', $data, TRUE);
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

	private function _validate_data()
	{
		$required 		= array();
		$constraints 	= array();

		$sub_required 			= array();
		$sub_constraints 		= array();

		$required['step_name']			= 'Step Name';
		$required['step_sequence']		= 'Step Sequence';
		$required['turnaround_time']	= 'Turnaround Time';
		$required['approval_type'] 		= 'Approval Type';

		$constraints['step_name']		= array(
			'data_type'		=> 'string',
			'name'			=> 'Step Name',
			'max_len'		=> '100'
		);

		$constraints['step_sequence']	= array(
			'data_type'		=> 'digit',
			'name'			=> 'Step Sequence'
		);

		$constraints['turnaround_time']	= array(
			'data_type'		=> 'digit',
			'name'			=> 'Turnaround Time'
		);

		$constraints['approval_type']	= array(
			'data_type'		=> 'enum',
			'name'			=> 'Approval Type',
			'allowed_values'=> array_keys($this->approval_type_arr)
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
							->filter_string( 'step_name' )
							->filter_number( 'step_sequence' )
							->filter_number( 'workflow_step', TRUE )
							->filter_number( 'turnaround_time' )
							->filter_string('approval_type', TRUE);

		$params 		= $par->filter();

		return $params;
	}

	private function _validate_save( array $params, array $orig_params, $workflow_id, $check, $action = NULL )
	{
		$arr 			= array();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			if( ISSET( $params['step_sequence'] ) AND !EMPTY( $params['step_sequence'] ) )
			{
				$cnt_val 	= 0;

				$no_stage 	= FALSE;

				foreach( $params['step_sequence'] as $key_id => $values )
				{
					if( is_array( $values ) )
					{
						$sub_required 		= array();
						$sub_constraints 	= array();
						$arr_params 		= array();

						$step_names 		= $params['step_name'][$key_id];
						$step_id 			= $params['workflow_step'][$key_id];
						$step_salt 			= $params['workflow_step_salt'][$key_id];
						$step_token 		= $params['workflow_step_token'][$key_id];
						$tat 				= $params['turnaround_time'][$key_id];
						$is_ver 			= $params['is_version_hid'][$key_id];
						$is_get				= $params['is_gettable_hid'][$key_id];
						$roles 				= $params['actor'][$key_id];
						$approval_type 		= $params['approval_type'][$key_id];
						$appendable 		= ( ISSET( $params['appendable'] ) ) ? $params['appendable'][$key_id] : NULL;
						$action_par 		= $params['action'][$key_id];
						$o_proc 			= $params['process_stop_flag'][$key_id];
						$display_status 	= $params['display_status'][$key_id];

						$clean_params 		= array();

						$validation_data 	= $this->_validate_data();

						$sub_required 		= $validation_data['required'];
						$sub_constraints 	= $validation_data['constraints'];

						$arr_params 		= array(
							'step_sequence'	=> $values,
							'step_name'		=> $step_names,
							'workflow_step'	=> $step_id,
							'workflow_step_salt' => $step_salt,
							'workflow_step_token'=> $step_token,
							'turnaround_time' 	 => $tat,
							'is_version_hid'	=> $is_ver,
							'is_gettable_hid'	=> $is_get,
							'actor'				=> $roles,
							'appendable'		=> $appendable,
							'action'			=> $action_par,
							'display_status'	=> $display_status,
							'process_stop_flag' => $o_proc,
							'approval_type' 	=> $approval_type
						);

						/*foreach( $values as $k => $v )
						{
							$arr_params['step_sequence'][] 	= $v;
							$arr_params['step_name'][] 		= $step_names[ $k ];
							$arr_params['workflow_step'][] 			= $step_id[ $k ];
							$arr_params['workflow_step_salt'][] 	= $step_salt[ $k ];
							$arr_params['workflow_step_token'][] 	= $step_token[ $k ];
							$arr_params['turnaround_time'][] 		= $tat

							$cnt_val++;
						}*/
						
						$clean_params 		= $this->_filter( $arr_params );

						$this->check_required_fields( $clean_params, $sub_required );

						$this->validate_inputs( $clean_params, $sub_constraints );
						
						$audit_details 		= $this->_save_tasks( $clean_params, $key_id, $workflow_id );

						if( !EMPTY( $audit_details['audit_schema'] ) )
						{
							$audit_schema 		= array_merge( $audit_schema, $audit_details['audit_schema'] );
							$audit_table 		= array_merge( $audit_table, $audit_details['audit_table'] );
							$audit_action 		= array_merge( $audit_action, $audit_details['audit_action'] );
							$prev_detail 		= array_merge( $prev_detail, $audit_details['prev_detail'] );
							$curr_detail 		= array_merge( $curr_detail, $audit_details['curr_detail'] );
						}
					}
					else
					{
						$no_stage = TRUE;
					}
				}

				if( $no_stage )
				{
					if( EMPTY( $check ) )
					{
						$clean_params 		= $this->_filter( $params );

						$validation_data	= $this->_validate_data();

						$sub_required 		= $validation_data['required'];
						$sub_constraints 	= $validation_data['constraints'];

						$this->check_required_fields( $clean_params, $sub_required );
						$this->validate_inputs( $clean_params, $sub_constraints );

						$workflow_details 	= $this->workflow->get_workflow_specific( $workflow_id );
						$stage_name 		= 'Stage 1';

						if( !EMPTY( $workflow_details ) )
						{
							$stage_name 	= $workflow_details['workflow_name'];
						}

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_STAGES;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[]  	= array();

						$stage_val 			= array(
							'workflow_id'	=> $workflow_id,
							'stage_name'	=> $stage_name,
							'sequence_no'	=> 1
						);

						$stage_id_db 		= $this->workflow_stages->insert_workflow_stages_single( $stage_val );

						$curr_detail[] 	 	= $this->workflow_stages->get_details_for_audit( SYSAD_Model::CORE_WORKFLOWS,
							array(
								'workflow_id'	=> $workflow_id
							)
						);

						if( !EMPTY( $stage_id_db ) )
						{
							$audit_details 		= $this->_save_tasks( $clean_params, $stage_id_db, $workflow_id );

							if( !EMPTY( $audit_details['audit_schema'] ) )
							{
								$audit_schema 		= array_merge( $audit_schema, $audit_details['audit_schema'] );
								$audit_table 		= array_merge( $audit_table, $audit_details['audit_table'] );
								$audit_action 		= array_merge( $audit_action, $audit_details['audit_action'] );
								$prev_detail 		= array_merge( $prev_detail, $audit_details['prev_detail'] );
								$curr_detail 		= array_merge( $curr_detail, $audit_details['curr_detail'] );
							}
						}
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

	private function _adjust_prerequisites_task( array $step_ids, $stage_id, $workflow_id )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			$stage_detail 		= $this->workflow_stages->get_specific_stage( $stage_id );

			if( !EMPTY( $stage_detail ) )
			{
				foreach( $step_ids as $k => $step_detail )
				{
					$prerequisites 	= $this->workflow_stage_tasks->get_previous_tasks( $workflow_id, $stage_detail['sequence_no'], $step_detail['sequence_no'], TRUE );
				
					if( !EMPTY( $prerequisites ) )
					{
						$pre_req_ids = array_column($prerequisites, 'workflow_task_id');

						$d_where 			= array(
							'pre_workflow_task_id' 	=> array( 'NOT IN', $pre_req_ids ),
							'workflow_task_id'		=> $step_detail['step_id']
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

	private function _save_tasks( array $clean_params, $stage_id, $workflow_id )
	{
		$arr 			= array();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		$task_adjust 			= array();

		try
		{
			$cnt 				= 0;

			foreach( $clean_params['step_sequence'] as $k => $v )
			{
				$arr['task_name']				= $clean_params['step_name'][$k];
				$arr['workflow_stage_id']		= $stage_id;
				$arr['sequence_no']				= $v;
				$arr['tat_in_days']				= $clean_params['turnaround_time'][$k];
				$arr['version_flag']			= ( !EMPTY($clean_params['is_version_hid'][$k]) ) ? ENUM_YES : ENUM_NO;
				$arr['get_flag']				= ( !EMPTY($clean_params['is_gettable_hid'][$k]) ) ? ENUM_YES : ENUM_NO;

				$step_id_per 					= NULL;

				$main_where 					= array();

				$o_role_per 					= array();
				$o_append 						= array();
				$o_act  						= array();
				$o_disp_status  				= array();

				$act_step 						= ACTION_ADD;

				if( ISSET( $clean_params['workflow_step'][ $k ] ) AND !EMPTY( $clean_params['workflow_step'][ $k ] ) )
				{
					check_salt( $clean_params['workflow_step'][ $k ], $clean_params['workflow_step_salt'][ $k ], $clean_params['workflow_step_token'][ $k ] );

					$step_id_per 				= $clean_params['workflow_step'][ $k ];

					$o_role_per 				= ( ISSET( $clean_params['actor'][$step_id_per] ) ) ? $clean_params['actor'][$step_id_per] : array();
					$o_append 					= ( ISSET( $clean_params['appendable'][$step_id_per] ) ) ? $clean_params['appendable'][$step_id_per] : array();
					$o_act 						= ( ISSET( $clean_params['action'][$step_id_per] ) ) ? $clean_params['action'][$step_id_per] : array();
					$o_disp_status 				= ( ISSET( $clean_params['display_status'][$step_id_per] ) ) ? $clean_params['display_status'][$step_id_per] : array(); 
					$o_proc 						= ( ISSET( $clean_params['process_stop_flag'][$step_id_per] ) ) ? $clean_params['process_stop_flag'][$step_id_per] : array();

					$o_approval_type 			= ( ISSET( $clean_params['approval_type'][$k] ) ) ? $clean_params['approval_type'][$k] : ""; 

					$act_step 					= ACTION_EDIT;

					$main_where 				= array(
						'workflow_task_id'		=> $step_id_per
					);

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS;
					$audit_action[] 	= AUDIT_UPDATE;
					$prev_step_det 		= $this->workflow_stage_tasks->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, 
						$main_where
					);

					$prev_detail[]  	= $prev_step_det;

					$this->workflow_stage_tasks->update_stage_task( $arr, $main_where );

					$curr_step_det 		= $this->workflow_stage_tasks->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, 
						$main_where
					);

					$curr_detail[] 				= $curr_step_det;

					if( !EMPTY( $o_approval_type ) )
					{
						$upd_arr 				= array(
							'workflow_task_id'	=> $main_where['workflow_task_id'],
							'code'				=> 'APPROVAL_TYPE',
							'value'				=> $o_approval_type
						);

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS;
						$audit_action[] 	= AUDIT_UPDATE;
						$prev_detail[] 		= $this->workflow_stage_tasks->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS, 
							$main_where
						);

						$this->workflow_stage_tasks->insert_task_other_details( $upd_arr );

						$curr_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS, 
							$main_where
						);
					}

					if( $prev_step_det[0]['sequence_no'] != $curr_step_det[0]['sequence_no'] )
					{
						if( $curr_step_det[0]['sequence_no'] < $prev_step_det[0]['sequence_no'] )
						{
							$task_adjust[$cnt]['step_id'] 		= $step_id_per;
							$task_adjust[$cnt]['sequence_no'] 	= $curr_step_det[0]['sequence_no'];
						}
					}

				}
				else
				{
					$o_role_per 				= ( ISSET( $clean_params['actor'][$v.'_sequence'] ) ) ? $clean_params['actor'][$v.'_sequence'] : array();
					$o_append 					= ( ISSET( $clean_params['appendable'][$v.'_sequence'] ) ) ? $clean_params['appendable'][$v.'_sequence'] : array();
					$o_act 						= ( ISSET( $clean_params['action'][$v.'_sequence'] ) ) ? $clean_params['action'][$v.'_sequence'] : array();
					$o_disp_status 				= ( ISSET( $clean_params['display_status'][$v.'_sequence'] ) ) ? $clean_params['display_status'][$v.'_sequence'] : array(); 
					$o_proc 				= ( ISSET( $clean_params['process_stop_flag'][$v.'_sequence'] ) ) ? $clean_params['process_stop_flag'][$v.'_sequence'] : array(); 

					if( ISSET( $clean_params['approval_type'][$stage_id] ) )
					{
						$o_approval_type 			= ( ISSET( $clean_params['approval_type'][$stage_id][$k] ) ) ? $clean_params['approval_type'][$stage_id][$k] : array(); 
					}
					else
					{
						$o_approval_type 			= ( ISSET( $clean_params['approval_type'][$k] ) ) ? $clean_params['approval_type'][$k] : array(); 	
					}


					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$step_id_per 				= $this->workflow_stage_tasks->insert_stage_task( $arr );

					$main_where 				= array(
						'workflow_task_id'		=> $step_id_per
					);

					$curr_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS, 
						$main_where
					);

					if( !EMPTY( $o_approval_type ) )
					{
						
						$upd_arr 				= array(
							'workflow_task_id'	=> $main_where['workflow_task_id'],
							'code'				=> 'APPROVAL_TYPE',
							'value'				=> $o_approval_type
						);

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS;
						$audit_action[] 	= AUDIT_INSERT;
						$prev_detail[] 		= array();

						$this->workflow_stage_tasks->insert_task_other_details( $upd_arr );

						$curr_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS, 
							$main_where
						);
					}

				}

				if( !EMPTY( $step_id_per ) )
				{
					$multi_arr 		= array(
						'actor'		=> $o_role_per,
						'appendable'=> $o_append,
						'action' 	=> $o_act,
						'display_status' => $o_disp_status,
						'process_stop_flag' => $o_proc
					);

					$multi_par 		= $this->set_filter( $multi_arr )
										->filter_string('actor', TRUE)
										->filter_number('appendable', TRUE)
										->filter_number('action', TRUE, array('explode' => TRUE))
										->filter_string('display_status')
										->filter();

					$role_val 		= $this->_process_validate_roles( $multi_par, $step_id_per );
					$append_val 	= $this->_process_validate_appendable( $multi_par, $step_id_per );
					$action_val 	= $this->_process_validate_action( $multi_par, $step_id_per );
					
					$roles_audit 	= $this->_save_task_roles( $role_val, $step_id_per, $main_where, $act_step );

					if( !EMPTY( $roles_audit['audit_schema'] ) )
					{
						$audit_schema 		= array_merge( $audit_schema, $roles_audit['audit_schema'] );
						$audit_table 		= array_merge( $audit_table, $roles_audit['audit_table'] );
						$audit_action 		= array_merge( $audit_action, $roles_audit['audit_action'] );
						$prev_detail 		= array_merge( $prev_detail, $roles_audit['prev_detail'] );
						$curr_detail 		= array_merge( $curr_detail, $roles_audit['curr_detail'] );
					}

					$append_audit 	= $this->_save_appendables( $append_val, $step_id_per, $main_where, $act_step );

					if( !EMPTY( $append_audit['audit_schema'] ) )
					{
						$audit_schema 		= array_merge( $audit_schema, $append_audit['audit_schema'] );
						$audit_table 		= array_merge( $audit_table, $append_audit['audit_table'] );
						$audit_action 		= array_merge( $audit_action, $append_audit['audit_action'] );
						$prev_detail 		= array_merge( $prev_detail, $append_audit['prev_detail'] );
						$curr_detail 		= array_merge( $curr_detail, $append_audit['curr_detail'] );
					}
					
					$actions_audit 	= $this->_save_task_actions( $action_val, $step_id_per, $main_where, $act_step );

					if( !EMPTY( $actions_audit['audit_schema'] ) )
					{
						$audit_schema 		= array_merge( $audit_schema, $actions_audit['audit_schema'] );
						$audit_table 		= array_merge( $audit_table, $actions_audit['audit_table'] );
						$audit_action 		= array_merge( $audit_action, $actions_audit['audit_action'] );
						$prev_detail 		= array_merge( $prev_detail, $actions_audit['prev_detail'] );
						$curr_detail 		= array_merge( $curr_detail, $actions_audit['curr_detail'] );
					}
				}

				$cnt++;
			}

			if( !EMPTY( $task_adjust ) )
			{

				$adjust_preq_audit 			= $this->_adjust_prerequisites_task( $task_adjust, $stage_id, $workflow_id );

				if( !EMPTY( $adjust_preq_audit['audit_schema'] ) )
				{
					$audit_schema 		= array_merge( $audit_schema, $adjust_preq_audit['audit_schema'] );
					$audit_table 		= array_merge( $audit_table, $adjust_preq_audit['audit_table'] );
					$audit_action 		= array_merge( $audit_action, $adjust_preq_audit['audit_action'] );
					$prev_detail 		= array_merge( $prev_detail, $adjust_preq_audit['prev_detail'] );
					$curr_detail 		= array_merge( $curr_detail, $adjust_preq_audit['curr_detail'] );
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

	private function _save_task_roles( array $role_val, $step_id_per, array $main_where, $act_step )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			if( !EMPTY( $role_val ) )
			{
				if( $act_step == ACTION_EDIT )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_ROLES;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->workflow_task_roles->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_ROLES, 
						$main_where
					);

					$this->workflow_task_roles->delete_stage_task_roles( $main_where );

					$curr_detail[] 				= array();
				}

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_ROLES;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$this->workflow_task_roles->insert_stage_task_roles( $role_val );

				$curr_detail[] 				= $this->workflow_task_roles->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_ROLES, 
					$main_where
				);
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

	private function _save_appendables( array $append_val, $step_id_per, array $main_where, $act_step )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			if( $act_step == ACTION_EDIT )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->workflow_task_appendable->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE, 
					$main_where
				);

				$this->workflow_task_appendable->delete_stage_task_appendable( $main_where );

				$curr_detail[] 				= array();
			}

			if( !EMPTY( $append_val ) )
			{

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$this->workflow_task_appendable->insert_stage_task_appendable( $append_val );

				$curr_detail[] 				= $this->workflow_task_appendable->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE, 
					$main_where
				);
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

	private function _save_task_actions( array $actions_val, $step_id, array $main_where, $action_step )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			if( !EMPTY( $actions_val ) )
			{
				if( $action_step == ACTION_EDIT )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->workflow_task_actions->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS, 
						$main_where
					);

					$this->workflow_task_actions->delete_task_actions( $main_where );

					$curr_detail[] 				= array();
				}

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$this->workflow_task_actions->insert_task_actions( $actions_val );

				$curr_detail[] 				= $this->workflow_task_actions->get_details_for_audit( SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS, 
					$main_where
				);
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

	private function _process_validate_roles( array $roles, $task_id )
	{
		$required 				= array();
		$constraints 			= array();
		$arr 					= array();

		$required['actor']		= 'Actor';

		$constraints['actor']	= array(
				'name'			=> 'Actor',
				'data_type'		=> 'db_value',
				'field'			=> ' COUNT( role_code ) as check_role ',
				'check_field'	=> 'check_role',
				'where' 		=> 'role_code',
				'table'			=> DB_CORE.'.'.SYSAD_Model::CORE_TABLE_ROLES
		);

		$this->check_required_fields( $roles, $required );

		$this->validate_inputs( $roles, $constraints );

		foreach( $roles['actor'] as $k => $r )
		{
			$arr[$k]['workflow_task_id']	= $task_id;
			$arr[$k]['actor_role_code']		= $r;
		}

		return $arr;

	}

	private function _process_validate_appendable( array $appendable, $task_id )
	{
		$required 				= array();
		$constraints 			= array();
		$arr 					= array();

		if( !EMPTY( $appendable['appendable'] ) )
		{
			$constraints['appendable']	= array(
					'name'			=> 'Appendable Workflow',
					'data_type'		=> 'db_value',
					'field'			=> ' COUNT( workflow_id ) as check_wf ',
					'check_field'	=> 'check_wf',
					'where' 		=> 'workflow_id',
					'table'			=> DB_CORE.'.'.SYSAD_Model::CORE_WORKFLOWS
			);

		}

		if( !EMPTY( $required ) )
		{
			$this->check_required_fields( $appendable, $required );
		}

		if( !EMPTY( $constraints ) )
		{
			$this->validate_inputs( $appendable, $constraints );
		}

		if( !EMPTY( $appendable['appendable'] ) )
		{

			foreach( $appendable['appendable'] as $k => $r )
			{
				$arr[$k]['workflow_task_id']	= $task_id;
				$arr[$k]['workflow_id']			= $r;
			}
		}

		return $arr;

	}

	private function _process_validate_action( array $action_val, $task_id )
	{
		$required 				= array();
		$constraints 			= array();
		$arr 					= array();

		$required['action']				= 'Action';
		$required['display_status']		= 'Display status';

		$constraints['action']	= array(
				'name'			=> 'Action',
				'data_type'		=> 'db_value',
				'field'			=> ' COUNT( action_id ) as check_act ',
				'check_field'	=> 'check_act',
				'where' 		=> 'action_id',
				'table'			=> DB_CORE.'.'.SYSAD_Model::CORE_PARAM_TASK_ACTIONS
		);

		$constraints['display_status']	= array(
				'name'			=> 'Display status',
				'data_type'		=> 'string',
				'max_len'		=> '100'
		);

		$this->check_required_fields( $action_val, $required );

		$this->validate_inputs( $action_val, $constraints );

		foreach( $action_val['action'] as $k => $r )
		{

			$disp_name 		= ( ISSET( $action_val['display_status'][$k] ) ) ? $action_val['display_status'][$k] : '';
			$o_proc 		= ( ISSET( $action_val['process_stop_flag'][$k] ) AND !EMPTY( $action_val['process_stop_flag'][$k] ) ) ? ENUM_YES : ENUM_NO;

			$arr[$k]['workflow_task_id']	= $task_id;
			$arr[$k]['task_action_id']		= $r;
			$arr[$k]['process_stop_flag']	= $o_proc;
			$arr[$k]['display_status']		= $disp_name;
		}

		return $arr;
	}

	public function load_actions_table()
	{
		$html 			= "";

		$resources 		= array();
		$data 			= array();

		$orig_params 	= get_params();
		$actions 		= array();

		$stage_id 		= NULL;
		$step_id 		= NULL;

		$task_actions 	= array();

		$action 		= NULL;

		try
		{
			// $this->redirect_off_system($this->module);

			$params 	= $this->set_filter( $orig_params )
						->filter_number('step_id', TRUE)
						->filter_number('workflow_main', TRUE)
						->filter_number('stage_id', TRUE)
						->filter();

			if( !EMPTY( $params['workflow_main'] ) )
			{
				check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );

				$action 	= $params['workflow_action'];
			}

			if( !EMPTY( $params['step_id'] ) )
			{
				check_salt( $params['step_id'], $params['step_salt'], $params['step_token'] );

				$step_id = $params['step_id'];

				$task_actions = $this->workflow_task_actions->get_task_actions( $params['step_id'] );
			}

			if( !EMPTY( $params['stage_id'] ) )
			{
				check_salt( $params['stage_id'], $params['stage_salt'], $params['stage_token'] );

				$stage_id = $params['stage_id'];
			}

			$actions 			= $this->param_task_actions->get_param_task_actions();

			$data['actions']	= $actions;
			$data['step_id']	= $step_id;
			$data['stage_id']	= $stage_id;
			$data['task_actions']= $task_actions;
			$data['action'] 	= $action;

			$html 	.= $this->load->view('tables/actions', $data, TRUE);
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
				$empt_msg 		= sprintf( $this->lang->line('workflow_empty'), 'step.' );

				throw new Exception( $empt_msg );
			}

			check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );

			$check_name = 'stages_flag';
			$check 		= get_setting( WORKFLOW_FLAG, $check_name );

			if( !EMPTY( $check ) )
			{
				$check_has_stage 	= $this->workflow_stages->check_has_stage( $params['workflow_main'] );

				if( EMPTY( $check_has_stage ) OR EMPTY( $check_has_stage['check_has_stage'] ) )
				{
					$stage_msg 		= sprintf( $this->lang->line('workflow_stage_empty'), 'step.' );

					throw new Exception( $stage_msg );

				}
			}

			$workflow_id 		= $params['workflow_main'];

			$main_where 		= array(
				'workflow_id'	=> $workflow_id
			);

			SYSAD_Model::beginTransaction();

			$val 				= $this->_validate_save( $params, $orig_params, $workflow_id, $check, $action );

			if( !EMPTY( $val['audit_schema'] ) )
			{

				$audit_schema 				= array_merge( $audit_schema, $val['audit_schema'] );
				$audit_table 				= array_merge( $audit_table, $val['audit_table'] );
				$audit_action 				= array_merge( $audit_action, $val['audit_action'] );
				$prev_detail 				= array_merge( $prev_detail, $val['prev_detail'] );
				$curr_detail 				= array_merge( $curr_detail, $val['curr_detail'] );

				$audit_name 				= 'Workflow Step.';

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

	public function delete_action()
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
									->filter_number('action_id', TRUE)
									->filter_number('step_id', TRUE)
									->filter();

			check_salt( $params['action_id'], $params['action_salt'], $params['action_token'] );
			check_salt( $params['step_id'], $params['step_salt'], $params['step_token'] );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'task_action_id'	=> $params['action_id'],
				'workflow_task_id'	=> $params['step_id'],
			);

			SYSAD_Model::beginTransaction();

			$prev_act 				= $this->workflow_task_actions->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS,
				$main_where
			);

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $prev_act;

			$this->workflow_task_actions->delete_task_actions( $main_where );

			$curr_detail[] 				= array();

			$audit_activity				= sprintf($this->lang->line( 'audit_trail_delete'), 'Action '.$prev_act[0]['display_status'] );

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
			'function'				=> 'Steps.load_actions_table_del(self);'
		);

		echo json_encode( $response );
	}

	public function delete_step()
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
									->filter_number('step_id', TRUE)
									->filter();

			check_salt( $params['step_id'], $params['step_salt'], $params['step_token'] );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'workflow_task_id'	=> $params['step_id']
			);

			$pd_where 			= array(
				'pre_workflow_task_id'	=> $params['step_id']
			);
			
			SYSAD_Model::beginTransaction();

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
				$main_where
			);

			$this->workflow_task_predecessors->delete_task_predecessor( $main_where );

			$curr_detail[] 				= array();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $this->workflow_task_actions->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_TASK_ACTIONS,
				$main_where
			);

			$this->workflow_task_actions->delete_task_actions( $main_where );

			$curr_detail[] 				= array();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $this->workflow_task_appendable->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_TASK_APPENDABLE,
				$main_where
			);

			$this->workflow_task_appendable->delete_stage_task_appendable( $main_where );

			$curr_detail[] 				= array();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_ROLES;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $this->workflow_task_roles->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_TASK_ROLES,
				$main_where
			);

			$this->workflow_task_roles->delete_stage_task_roles( $main_where );

			$curr_detail[] 				= array();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_TASK_OTHER_DETAILS,
				$main_where
			);

			$this->workflow_stage_tasks->delete_task_other_details( $main_where );

			$curr_detail[] 				= array();

			$prev_step 					= $this->workflow_stage_tasks->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS,
				$main_where
			);

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $prev_step;

			$this->workflow_stage_tasks->delete_stage_task( $main_where );

			$curr_detail[] 				= array();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS;
			$audit_action[] 			= AUDIT_UPDATE;
			$prev_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS,
				$main_where
			);

			$this->workflow_stage_tasks->update_sequence( $prev_step[0]['workflow_stage_id'], $prev_step[0]['sequence_no'] );

			$curr_detail[] 				= $this->workflow_stage_tasks->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOW_STAGE_TASKS,
				$main_where
			);

			$audit_activity				= sprintf($this->lang->line( 'audit_trail_delete'), $prev_step[0]['task_name'] );

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
			'function'				=> 'Steps.load_table();'
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
									->filter_number( 'workflow_step', TRUE )
									->filter_number('workflow_main', TRUE)
									->filter_number('sequence')
									->filter();

			check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );
			check_salt( $params['workflow_step'], $params['workflow_step_salt'], $params['workflow_step_token'] );

			$stage_det 			= $this->workflow_stage_tasks->get_specific_step( $params['workflow_step'] ); 

			if( !EMPTY( $stage_det ) )
			{
				if( $stage_det['sequence_no'] != $params['sequence'] )
				{
					if( $params['sequence'] < $stage_det['sequence_no'] )
					{
						$check_for_prerequisite 	= $this->workflow_stage_tasks->check_for_prerequisite( $params['workflow_step'] );
						
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