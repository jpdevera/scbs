<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manage_workflow extends SYSAD_Controller 
{
	
	private $module;
	private $dt_options 	= array();

	private $setting_per 	= FALSE;
	private $process_js;
	private $stage_js;
	private $steps_js;
	private $prerequisites_js;
	private $workflow_js;

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	private $table_columns 		= array(
		'a.workflow_id', 'a.workflow_name', 'a.description', 'a.appendable_flag', 'a.active_flag',
		'IFNULL(b.count_stages, 0) as count_stages'
	);

	private $table_filter 		= array(
		'a-workflow_name', 'a-description', 'IFNULL(b.count_stages, 0) as count_stages'
	);

	private $table_order 		= array(
		'a.workflow_name', 'a.description', 'count_stages'
	);
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_WORKFLOW;

		$this->table_columns[] 	= 'IF( active_flag = "'.ENUM_YES.'", "Active", "Inactive" ) as status';
		$this->table_filter[] 	= 'active_flag as status';
		$this->table_filter[] 	= 'IF( active_flag = "'.ENUM_YES.'", "Active", "Inactive" ) as status_str';
		$this->table_order[] 	= 'status';
		
		$this->load->model('workflow_model', 'workflow');
		$this->load->model('Workflow_stages_model', 'workflow_stages');
		$this->load->model('Workflow_stage_tasks_model', 'workflow_stage_tasks');
		$this->load->model('Workflow_task_roles_model', 'workflow_task_roles');
		$this->load->model('Workflow_task_appendable_model', 'workflow_task_appendable');
		$this->load->model('Workflow_task_actions_model', 'workflow_task_actions');
		$this->load->model('Workflow_task_predecessors_model', 'workflow_task_predecessors');

		$this->dt_options 	= array(
			'table_id'		=> 'workflow_table',
			'path'			=> CORE_WORKFLOW.'/manage_workflow/get_workflows',
			'advanced_filter' 	=> true,
			'with_search'		=> true,
			'post_data'			=> array(
				'status_link'	=> ENUM_YES
			),
			'search_func'		=> 'Workflow.search_func(search_params);'
		);

		$this->setting_per 	= $this->permission->check_permission( $this->module, ACTION_SETTINGS );

		$this->process_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/process";
		$this->stages_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/stages";
		$this->steps_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/steps";
		$this->workflow_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/workflow";
		$this->prerequisites_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/prerequisites";

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
	}
	
	public function index()
	{
		$data 			= array();
		$resources 		= array();

		try
		{

			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);			

			$workflow_setting 		= base64_url_encode(WORKFLOW_SETTING);
			$workflow_setting_salt 	= gen_salt();
			$workflow_setting_token = in_salt(WORKFLOW_SETTING, $workflow_setting_salt);
			$workflow_setting_url 	= base_url().CORE_WORKFLOW.'/Workflow_settings/index/'.$workflow_setting.'/'.$workflow_setting_salt.'/'.$workflow_setting_token;

			$workflow_active_cnt 	= 0;
			$workflow_inactive_cnt 	= 0;
			$workflow_append_cnt 	= 0;

			$workflow_act_det 		= $this->workflow->get_workflow_status_cnt(ENUM_YES);
			$workflow_inact_det 	= $this->workflow->get_workflow_status_cnt(ENUM_NO);
			$workflow_app_det 		= $this->workflow->get_appendable_workflows_cnt();

			if( !EMPTY( $workflow_act_det ) )
			{
				$workflow_active_cnt 		= $workflow_act_det['workflow_status_cnt'];
			}

			if( !EMPTY( $workflow_inact_det ) )
			{
				$workflow_inactive_cnt 		= $workflow_inact_det['workflow_status_cnt'];
			}

			if( !EMPTY( $workflow_app_det ) )
			{
				$workflow_append_cnt 		= $workflow_app_det['workflow_appendable_cnt'];
			}

			$data['workflow_setting'] 		= $workflow_setting;
			$data['workflow_setting_salt']	= $workflow_setting_salt;
			$data['workflow_setting_token'] = $workflow_setting_token;
			$data['workflow_setting_url']	= $workflow_setting_url;
			$data['workflow_active_cnt']  	= $workflow_active_cnt;
			$data['workflow_inactive_cnt']  = $workflow_inactive_cnt;
			$data['workflow_append_cnt']  	= $workflow_append_cnt;

			$data['setting_per']			= $this->setting_per;
			$data['add_per']				= $this->add_per;

			$datatable_act 			= $this->dt_options;
			$datatable_inact  		= $this->dt_options;
			$datatable_append  		= $this->dt_options;

			$datatable_act['post_data']		= array(
				'status_link'		=> ENUM_YES
			);

			$datatable_inact['post_data']	= array(
				'status_link'		=> ENUM_NO
			);

			$datatable_append['post_data'] 	= array(
				'append_link'		=> ENUM_YES
			);

			$json_datatable_act_options			= json_encode( $datatable_act );
			$json_datatable_inact_options 		= json_encode( $datatable_inact );
			$json_datatable_append_options 		= json_encode( $datatable_append );

			$resources['load_css'] 	= array(CSS_DATATABLE_MATERIAL, CSS_SELECTIZE);
			$resources['load_js'] 	= array(JS_NUMBER, JS_DATATABLE, JS_DATATABLE_MATERIAL, $this->workflow_js);
			$resources['loaded_init']	= array(
				'materialize_select_init();',
				'Workflow.init_page();',
				"refresh_datatable('".$json_datatable_act_options."','#link_active_btn');",
				"refresh_datatable('".$json_datatable_inact_options."','#link_inactive_btn');",
				"refresh_datatable('".$json_datatable_append_options."','#link_append_btn');"
			);

			$resources['datatable'] 	= $this->dt_options;
			$resources['decimal_places'] = 0;

			$resources['load_delete']	= array(
				'workflow'				=> array(
					'delete_cntrl' 		=> 'Manage_workflow',
					'delete_method'		=> 'delete_workflow',
					'delete_module'		=> CORE_WORKFLOW
				)
			);
			
			$this->template->load('workflow_list', $data, $resources);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg  	= $this->rlog_error($e, TRUE);	

			$this->error_index( $msg );
		}
	}

	public function create_new( $action = NULL, $main_id = NULL, $salt = NULL, $token = NULL )
	{
		$data 			= array();
		$resources		= array();

		$workflow_details 				= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);
			

			$resources['load_css']		= array( CSS_LABELAUTY, CSS_SELECTIZE, CSS_WIZARD, CSS_DRAGULA );
			$resources['load_js']		= array( JS_LABELAUTY, JS_SELECTIZE, JS_NUMBER, JS_WIZARD, JS_ADD_ROW, JS_DRAGULA_SCROLL, $this->workflow_js, $this->process_js, $this->stages_js, $this->steps_js, $this->prerequisites_js );
			$resources['loaded_init']	= array( 'Wizard.init();', 'Stages.table();', 'Steps.table();', 'Prerequisites.table();' );

			$extra 						= array(
				'order'					=> array( 'sort_order' => 'ASC' )
			);

			$resources['load_delete']	= array(
				'workflow_stage'		=> array(
					'delete_cntrl' 		=> 'Stages',
					'delete_method'		=> 'delete_stage',
					'delete_module'		=> CORE_WORKFLOW
				),
				'workflow_action'		=> array(
					'delete_cntrl'		=> 'Steps',
					'delete_method'		=> 'delete_action',
					'delete_module'		=> CORE_WORKFLOW
				),
				'workflow_step'			=> array(
					'delete_cntrl' 		=> 'Steps',
					'delete_method'		=> 'delete_step',
					'delete_module'		=> CORE_WORKFLOW
				)
			);

			$work_settings_flag 		= array();

			$workflow_settings 			= get_settings( WORKFLOW_TAB, $extra );

			$list 						= '';

			$count_setting 				= 0;

			$work_tab_details 			= array();

			if( !EMPTY( $workflow_settings ) )
			{
				foreach( $workflow_settings as $w_k => $w_s )
				{
					$desc_name 	= $w_s['setting_name'].'_description';
					$check_name = $w_s['setting_name'].'_flag';
					$check 		= get_setting( WORKFLOW_FLAG, $check_name );

					$active 	= '';

					$desc 		= get_setting( WORKFLOW_DESCRIPTION, $desc_name );

					if( $w_k == 0 )
					{
						$active = 'active';
					}

					if( !EMPTY( $check ) )
					{
						$list 	.= '<li class="'.$active.'">'.$w_s['setting_value'].'</li>';

						$count_setting++;
					}

					$work_settings_flag[ $check_name ] = $check;
					$work_tab_details[ $w_s['setting_name'] ] = array(
						'name'	=> $w_s['setting_value'],
						'description' => $desc
					);
				}
			}

			$class_step 				= trim( convertNumberToWord( $count_setting ) ).'-step';

			$workflow_url 				= base_url().CORE_WORKFLOW.'/Manage_workflow';

			if( !EMPTY( $main_id ) )
			{
				$decode_id 				= base64_url_decode( $main_id );

				check_salt( $decode_id, $salt, $token, $action );

				$workflow_details 		= $this->workflow->get_workflow_specific( $decode_id );

			}

			$data['workflow_url'] 		= $workflow_url;
			$data['list'] 				= $list;
			$data['class_step'] 		= $class_step;
			$data['work_settings_flag']	= $work_settings_flag;
			$data['work_tab_details'] 	= $work_tab_details;
			$data['workflow_details']	= $workflow_details;
			$data['action']				= $action;
			$data['main_id']			= $main_id;
			$data['salt']				= $salt;
			$data['token']				= $token;

			$pass_data 					= $data;

			$data['pass_data'] 			= $pass_data;

			$this->template->load('forms/workflow', $data, $resources);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg  	= $this->rlog_error($e, TRUE);	

			$this->error_index( $msg );
		}
	}
	
	/*public function create()
	{
		try
		{
			$process_id = $this->uri->segment(4, 0);
			
			$data = array("process_id" => $process_id);
			
			$this->template->load('workflow', $data);
		}
		catch(Exception $e)
		{
			echo $this->rlog_error($e, TRUE);
		}	
	}*/

	public function get_workflows()
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();

		$resources 				= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$orig_params 		= get_params();

			$flag 				= 1;

			$params 			= $this->set_filter( $orig_params )
									->filter();

			$result 				= array();

			$columns 				= $this->table_columns;
			$filter 				= $this->table_filter;
			$order 					= $this->table_order;

			$result 				= $this->workflow->get_workflow_list( $columns, $filter, $order, $params );

			$cnt_result 		= count($result['aaData']);

			$counter 			= 0;

			$output['sEcho'] 				= $params['sEcho'];
			$output['iTotalRecords'] 		= $cnt_result;
			$output['iTotalDisplayRecords'] = $result['filtered_length']['filtered_length'];
			$output['aaData']				= array();

			if(! EMPTY($result))
			{
				foreach($result['aaData'] as $r)
				{
					$id_enc 		= base64_url_encode( $r['workflow_id'] );
					$salt 			= gen_salt();
					$token_view 	= in_salt( $r['workflow_id'].'/'.ACTION_VIEW, $salt );
					$token_edit 	= in_salt( $r['workflow_id'].'/'.ACTION_EDIT, $salt );
					$token_delete 	= in_salt( $r['workflow_id'].'/'.ACTION_DELETE, $salt );

					$actions 			= "<div class='table-actions'>";

					$main_url 			= base_url().CORE_WORKFLOW.'/Manage_workflow/create_new/';

					if( $this->view_per )
					{
						$view_url 	= $main_url.ACTION_VIEW.'/'.$id_enc.'/'.$salt.'/'.$token_view;

						$actions .= "<a href='".$view_url."' class='tooltipped' data-tooltip='View' data-position='bottom' data-delay='50'><i class='material-icons'>remove_red_eye</i></a>";
					}

					if( $this->edit_per )
					{
						$edit_url 	= $main_url.ACTION_EDIT.'/'.$id_enc.'/'.$salt.'/'.$token_edit;

						$actions .= "<a href='".$edit_url."' class='tooltipped' data-tooltip='Edit' data-position='bottom' data-delay='50'><i class='material-icons'>mode_edit</i></a>";
					}

					if( $this->delete_per )
					{
						// data-delete_post=\''.$post_data.'\'
						$url_delete 		=  ACTION_DELETE.'/'.$id_enc.'/'.$salt.'/'.$token_delete;
						$delete_action 		= "content_workflow_delete('Workflow', '".$url_delete."', '', this)";

						$actions 			.= '<a class="cursor-pointer tooltipped" data-delete_post="" onclick="'.$delete_action.'" data-tooltip="Delete" data-position="bottom" data-delay="50"><i class="material-icons">delete</i></a>';
					}

					$actions .= "</div>";

					$counter++;

					/*if($counter == $cnt_result)
					{
						$resources['preload_modal'] = array("modal_gender_issues");
						$resources['loaded_init'] 	= array("dropdown_button_init('dropdown-button-action');");
						$actions .= $this->load_resources->get_resource($resources, TRUE);
					}*/

					$rows[] 		 	= array(
						$r['workflow_name'],
						$r['description'],
						$r['count_stages'],
						$r['status'],
						$actions
					);
				}

				$output['iTotalRecords'] = $counter;
			}

		}
		catch(PDOException $e)
		{
			$this->rlog_error($e);
				
			$flag	= 0;
			$msg	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$this->rlog_error($e);
				
			$flag	= 0;
			$msg	= $e->getMessage();
		}
	
		$output['aaData'] 	= $rows;
		$output['flag']		= $flag;
		$output['msg']		= $msg;

		echo json_encode($output);
	}
	
	/*public function get_workflows()
	{
		try
		{
			$params = get_params();
			
			$center = array("process_id", "num_stages");
			
			$aColumns = array("A.process_id", "A.name", "A.description", "A.num_stages", "CONCAT(B.fname, ' ', B.lname) creator");
			$bColumns = array("process_id", "name", "description", "num_stages", "creator");
		
			$workflows = $this->workflow->get_workflows($aColumns, $bColumns, $params);
			$iTotal = $this->workflow->total_length();
			$iFilteredTotal = $this->workflow->filtered_length($aColumns, $bColumns, $params);
		
			$output = array(
				"sEcho" => intval($_POST['sEcho']),
				"iTotalRecords" => $iTotal["cnt"],
				"iTotalDisplayRecords" => $iFilteredTotal["cnt"],
				"aaData" => array()
			);
			
			foreach ($workflows as $aRow):
				$row = array();
				$action = "<div class='table-actions center-align'>";
			
				$process_id = $aRow["process_id"];
				$id = base64_url_encode($process_id);
				$salt = gen_salt();
				$token = in_salt($process_id, $salt);
				$url = base_url().PROJECT_CORE."/manage_workflow/create/".$id."/".$salt."/".$token;
				$delete_action = 'content_delete("delete_workflow_process","'.$id.'")';
				
				for ($i=0; $i<count($bColumns); $i++)
				{
					if(in_array($bColumns[$i], $center)) { 
						$row[] = "<div class='center-align'>".$aRow[ $bColumns[$i] ]."</div>";
					} else {
						$row[] = $aRow[ $bColumns[$i] ];
					}	
				}
							
				if($this->permission->check_permission($this->module, ACTION_EDIT))
					$action .= "<a href='".$url."' title='Edit' class='edit'></a>";
				
				if($this->permission->check_permission($this->module, ACTION_DELETE))
					$action .= "<a href='javascript:;' onclick='".$delete_action."' title='Delete' class='delete' ></a>";
				
				$action .= "</div>";
				$row[] = $action;
					
				$output['aaData'][] = $row;
			endforeach;
		
			echo json_encode( $output );
		}
		catch(PDOException $e)
		{			
			echo $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			echo $this->rlog_error($e, TRUE);
		}
	}*/

	public function process_stage_task( array $stage_task )
	{
		$arr 	= array();

		if( !EMPTY( $stage_task ) )
		{
			foreach( $stage_task as $key => $s_t )
			{
				$arr[ $s_t['workflow_stage_id'] ]['stage_name'] 	= $s_t['stage_name'];
				$arr[ $s_t['workflow_stage_id'] ]['sequence_no'] 	= $s_t['sequence_no'];

				if( !EMPTY( $s_t['workflow_task_id'] ) )
				{
					$arr[ $s_t['workflow_stage_id'] ]['tasks'][] 	= array(
						'workflow_task_id'	=> $s_t['workflow_task_id'],
						'task_name'			=> $s_t['task_name'],
						'task_sequence'		=> $s_t['task_sequence'],
						'tat_in_days'		=> $s_t['tat_in_days'],
						'version_flag'		=> $s_t['version_flag'],
						'get_flag'			=> $s_t['get_flag'],
						'actor_role_codes'	=> $s_t['actor_role_codes'],
						'append_wf'			=> $s_t['append_wf'],
						'task_action_ids'	=> $s_t['task_action_ids'],
						'display_statuses'	=> $s_t['display_statuses'],
						'approval_type'		=> $s_t['approval_type']
					);
				}
				else
				{

					$arr[ $s_t['workflow_stage_id'] ]['tasks'] 			= array();
				}
			}
		}


		return $arr;
	}

	public function delete_workflow()
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

		$workflow_active_cnt 	= 0;
		$workflow_inactive_cnt 	= 0;
		$workflow_append_cnt 	= 0;

		try
		{
			// $this->redirect_off_system($this->module);
			
			$params 			= $orig_params;

			$details  			= explode('/', $params['param_1']);
			$action 			= $details[0];
			$workflow_id 		= base64_url_decode( $details[1] );
			$workflow_salt 		= $details[2];
			$workflow_token 	= $details[3];

			check_salt( $workflow_id, $workflow_salt, $workflow_token, $action );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'workflow_id'	=> $workflow_id
			);

			SYSAD_Model::beginTransaction();

			$workflow_stages 			= $this->workflow_stages->get_workflow_stages( $workflow_id );

			if( !EMPTY( $workflow_stages ) )
			{
				$stages_id 					= array_column($workflow_stages, 'workflow_stage_id');

				$workflow_task 				= $this->workflow_stage_tasks->get_workflow_stage_task_ids( $workflow_id );

				if( !EMPTY( $workflow_task ) )
				{
					$task_id 				= array_column($workflow_task, 'workflow_task_id');

					$sub_where 				= array(  
						'workflow_task_id'	=> array( 'IN', $task_id )
					);

					$pd_where 			= array(
						'pre_workflow_task_id'	=> array( 'IN', $task_id )
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

				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_WORKFLOW_STAGES;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $this->workflow_stages->get_details_for_audit(
					SYSAD_Model::CORE_WORKFLOW_STAGES,
					$main_where
				);

				$this->workflow_stages->delete_stage( $main_where );

				$curr_detail[] 				= array();
				
			}

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_WORKFLOWS;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_work 					= $this->workflow->get_details_for_audit(
				SYSAD_Model::CORE_WORKFLOWS,
				$main_where
			);

			$prev_detail[] 				= $prev_work;

			$this->workflow->delete_workflow( $main_where );

			$workflow_act_det 		= $this->workflow->get_workflow_status_cnt(ENUM_YES);
			$workflow_inact_det 	= $this->workflow->get_workflow_status_cnt(ENUM_NO);
			$workflow_app_det 		= $this->workflow->get_appendable_workflows_cnt();

			if( !EMPTY( $workflow_act_det ) )
			{
				$workflow_active_cnt 		= $workflow_act_det['workflow_status_cnt'];
			}

			if( !EMPTY( $workflow_inact_det ) )
			{
				$workflow_inactive_cnt 		= $workflow_inact_det['workflow_status_cnt'];
			}

			if( !EMPTY( $workflow_app_det ) )
			{
				$workflow_append_cnt 		= $workflow_app_det['workflow_appendable_cnt'];
			}

			$curr_detail[] 				= array();

			$audit_activity				= sprintf($this->lang->line( 'audit_trail_delete'), $prev_work[0]['workflow_name'] );

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
			"reload" 				=> 'datatable',
			"status" 				=> $status,
			"datatable_options" 	=> $this->dt_options,
			'extra_reload'			=> 'function',
			'extra_function' 		=> 'Workflow.delete_callback("'.$workflow_active_cnt.'", "'.$workflow_inactive_cnt.'", "'.$workflow_append_cnt.'", datatable_options);'
		);

		echo json_encode( $response );
	}
}