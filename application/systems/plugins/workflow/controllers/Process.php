<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Process extends SYSAD_Controller 
{
	
	private $module;
	private $dt_options 	= array();

	private $process_js;

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_WORKFLOW;

		$this->process_js 	= HMVC_FOLDER."/".SYSTEM_PLUGIN."/".CORE_WORKFLOW."/process";

		$this->load->model('Workflow_model', 'workflow_mod');

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
	}

	private function _validate( array $params, array $orig_params, $action = NULL )
	{
		$required 				= array();
		$constraints 			= array();

		$arr 					= array();

		$description 			= NULL;

		$required['process_name']		= 'Process Name';

		$constraints['process_name']	= array(
			'name'			=> 'Process Name',
			'data_type'		=> 'string',
			'max_len'		=> '100'
		);

		if( ISSET( $params['process_description'] ) AND !EMPTY(  $params['process_description'] ) )
		{
			$constraints['process_description']	= array(
				'name'			=> 'Process Description',
				'data_type'		=> 'string',
				'max_len'		=> '255'
			);

			$description 		= $params['process_description'];
		}

		$this->check_required_fields( $params, $required );

		$this->validate_inputs( $params, $constraints );

		$arr['workflow_name']	= $params['process_name'];
		$arr['description']		= $description;
		$arr['appendable_flag']	= ( ISSET( $params['is_appendable'] ) ) ? ENUM_YES : ENUM_NO;
		$arr['active_flag']		= ( ISSET( $params['active_flag'] ) ) ? ENUM_YES : ENUM_NO;
		

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

		$workflow_id 			= NULL;
		$workflow_main 			= NULL;
		$workflow_salt 			= NULL;
		$workflow_token 		= NULL;
		$workflow_action 		= NULL;

		$main_where 			= array();

		$check 					= FALSE;

		try
		{
			// $this->redirect_off_system($this->module);
			
			$params 			= $this->set_filter( $orig_params )
									->filter_number( 'workflow_main', TRUE )
									->filter_string( 'process_name' )
									->filter_string( 'process_description' )
									->filter();

			$check_name = 'stages_flag';
			$check 		= get_setting( WORKFLOW_FLAG, $check_name );

			$check 		= !EMPTY( $check );

			$permission 		= ( !$update ) ? $this->add_per : $this->edit_per;
			$per_msg 			= ( !$update ) ? $this->lang->line( 'err_unauthorized_add' ) : $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}

			$val 				= $this->_validate( $params, $orig_params, $action );

			SYSAD_Model::beginTransaction();
			
			if( !$update )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOWS;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$workflow_id 		= $this->workflow_mod->insert_workflows( $val );

				$main_where 		= array(
					'workflow_id'	=> $workflow_id
				);

				$curr_detail[] 	 	= $this->workflow_mod->get_details_for_audit( SYSAD_Model::CORE_WORKFLOWS,
					$main_where
				);

			}
			else
			{
				check_salt( $params['workflow_main'], $params['workflow_salt'], $params['workflow_token'], $params['workflow_action'] );

				$workflow_id 		= $params['workflow_main'];

				$main_where 		= array(
					'workflow_id'	=> $workflow_id
				);

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_WORKFLOWS;
				$audit_action[] 	= AUDIT_UPDATE;
				$prev_detail[]  	= $this->workflow_mod->get_details_for_audit( SYSAD_Model::CORE_WORKFLOWS, 
					$main_where
				);

				$this->workflow_mod->update_workflow( $val, $main_where );

				$curr_detail[] 		= $this->workflow_mod->get_details_for_audit( SYSAD_Model::CORE_WORKFLOWS, 
					$main_where
				);
			}

			$audit_name 				= 'Workflow'.' ('.$params['process_name'].').';

			$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );

			$workflow_action 		= ACTION_EDIT;
			$workflow_main 			= base64_url_encode( $workflow_id );
			$workflow_salt 			= gen_salt();
			$workflow_token 		= in_salt( $workflow_id.'/'.$workflow_action, $workflow_salt );

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
			'workflow_main' 		=> $workflow_main,
			'workflow_salt'			=> $workflow_salt,
			'workflow_token'		=> $workflow_token,
			'workflow_action'		=> $workflow_action,
			'check'					=> $check
		);

		echo json_encode( $response );	
	}

}