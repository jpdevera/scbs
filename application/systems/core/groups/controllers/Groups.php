<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Groups extends SYSAD_Controller 
{

	private $module;
	private $table_id;
	private $path;
	private $module_js;

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	private $table_columns 		= array(
		'a.group_id', 'a.group_name', 'a.group_color', 'a.active_flag', 'a.group_description'
	);

	private $table_filter 		= array(
		'a-group_name', 'a-group_description', 'a-group_color'
	);

	private $table_order 		= array(
		'a.group_name', 'a.group_description', 'a.group_color'
	);

	private $dt_options 	= array();
	private $date_now;

	public function __construct()
	{
		parent::__construct();
		
		$this->module = MODULE_GROUPS;

		$this->load->model('Groups_model', 'groups');
		$this->load->model('User_groups_model', 'user_groups');
		$this->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'users');

		$this->table_columns[] 	= 'IF( a.active_flag = "'.ENUM_YES.'", "Active", "Inactive" ) as status';
		$this->table_filter[] 	= 'active_flag as status';
		$this->table_filter[] 	= 'IF( a.active_flag = "'.ENUM_YES.'", "Active", "Inactive" ) as status_str';
		$this->table_order[] 	= 'status';

		$this->dt_options 	= array(
			'table_id'		=> 'groups_main_table',
			'path'			=> CORE_GROUPS.'/Groups/get_groups_list',
			'advanced_filter' 	=> true,
			'with_search'		=> true,
			'post_data'			=> array(
				'status_link'	=> ENUM_YES
			)
		);

		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_GROUPS."/groups";

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );

		$this->date_now 		= date('Y-m-d H:i:s');

		$this->table_columns[]  = "
			IFNULL( (
				SELECT COUNT(user_id)
				FROM 	".SYSAD_Model::CORE_TABLE_USER_GROUPS." suba 
				WHERE 	suba.group_id = a.group_id
			), 0 ) as no_member
";

		$this->table_filter[] = "
			IFNULL( (
				SELECT COUNT(user_id)
				FROM 	".SYSAD_Model::CORE_TABLE_USER_GROUPS." suba 
				WHERE 	suba.group_id = a.group_id
			), 0 ) convert_to member_no
";
		$this->table_order[] = "no_member";
	}

	public function index()
	{
		$data 			= array();
		$resources 		= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$data['add_per']	= $this->add_per;

			$resources['load_css'] 		= array(CSS_DATATABLE_MATERIAL, CSS_SELECTIZE);
			$resources['load_js'] 		= array(JS_DATATABLE, JS_DATATABLE_MATERIAL, $this->module_js);

			$resources['load_materialize_modal'] = array(
				'modal_groups' 		=> array (
					'title' 		=> 'Groups',
					'size' 			=> "md full-h",
					'module' 		=> CORE_GROUPS,
					'controller' 	=> 'Groups',
					'custom_button'	=> array(
						BTN_SAVE 	=> array("type" => "button", "action" => BTN_SAVING)
					)
				)
			);

			$resources['load_delete']	= array(
				'groups'				=> array(
					'delete_cntrl' 		=> 'Groups',
					'delete_method'		=> 'delete_groups',
					'delete_module'		=> CORE_GROUPS
				)
			);

			$datatable_act 			= $this->dt_options;
			$datatable_inact  		= $this->dt_options;

			$datatable_act['post_data']		= array(
				'status_link'		=> ENUM_YES
			);

			$datatable_inact['post_data']	= array(
				'status_link'		=> ENUM_NO
			);

			$json_datatable_act_options			= json_encode( $datatable_act );
			$json_datatable_inact_options 		= json_encode( $datatable_inact );

			$resources['loaded_init']	= array(
				'materialize_select_init();',
				'Groups.init_page();',
				"refresh_datatable('".$json_datatable_act_options."','#link_active_btn');",
				"refresh_datatable('".$json_datatable_inact_options."','#link_inactive_btn');",
			);

			$resources['datatable'] 	= $this->dt_options;

			$group_active_cnt 		= 0;
			$group_inactive_cnt 	= 0;

			$group_act_det 			= $this->groups->get_groups_status_cnt(ENUM_YES);
			$group_inact_det 		= $this->groups->get_groups_status_cnt(ENUM_NO);

			if( !EMPTY( $group_act_det ) )
			{
				$group_active_cnt 			= $group_act_det['group_status_cnt'];
			}

			if( !EMPTY( $group_inact_det ) )
			{
				$group_inactive_cnt 		= $group_inact_det['group_status_cnt'];
			}

			$data['group_active_cnt']  		= $group_active_cnt;
			$data['group_inactive_cnt']  	= $group_inactive_cnt;

			$this->template->load('groups_list', $data, $resources);
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

	public function get_groups_list()
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

			$result 				= $this->groups->get_groups_list( $columns, $filter, $order, $params );

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
					$id_enc 		= base64_url_encode( $r['group_id'] );
					$salt 			= gen_salt();
					$token_view 	= in_salt( $r['group_id'].'/'.ACTION_VIEW, $salt );
					$token_edit 	= in_salt( $r['group_id'].'/'.ACTION_EDIT, $salt );
					$token_delete 	= in_salt( $r['group_id'].'/'.ACTION_DELETE, $salt );

					$main_url 			= base_url().CORE_GROUPS.'/Groups/modal/';

					$actions 			= "<div class='table-actions'>";

					if( $this->view_per )
					{
						$url_view 			=  ACTION_VIEW.'/'.$id_enc.'/'.$salt.'/'.$token_view;
						$modal_action_view 	= "modal_groups_init('".$url_view."', this)";

						// 			$actions 			.= '<a class="tooltipped" data-tooltip="View" data-position="bottom" data-delay="50" href="#modal_groups" onclick="'.$modal_action_view.'" data-modal_post=\'\'><i class="material-icons">remove_red_eye</i></a>';
					}

					if( $this->edit_per )
					{
						$url_edit 			=  ACTION_EDIT.'/'.$id_enc.'/'.$salt.'/'.$token_edit;
						$modal_action_edit 	= "modal_groups_init('".$url_edit."', this)";

						$actions 			.= '<a class="tooltipped" data-tooltip="Edit" data-position="bottom" data-delay="50" href="#modal_groups" onclick="'.$modal_action_edit.'" data-modal_post=\'\'><i class="material-icons">edit</i></a>';
					}

					if( $this->delete_per )
					{
						// data-delete_post=\''.$post_data.'\'
						$url_delete 		=  ACTION_DELETE.'/'.$id_enc.'/'.$salt.'/'.$token_delete;
						$delete_action 		= "content_groups_delete('Group', '".$url_delete."', '', this)";

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

					$group_color = '<span class="legend circle m-r-xs" style="padding: 1px 10px 1px 5px !important;background-color:'. $r['group_color'] .'">&nbsp;</span>' . $r['group_color'];

					$rows[] 		 	= array(
						$group_color,
						$r['no_member'],
						$r['group_name'],
						$r['group_description'],						
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

	public function modal( $action = NULL, $id = NULL, $salt = NULL, $token = NULL )
	{
		$data 			= array();
		$resources		= array();

		$details 		= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$resources['load_css'] 	= array(
				CSS_COLORPICKER, CSS_LABELAUTY
			);

			$resources['load_js']	= array(
				JS_COLORPICKER, JS_LABELAUTY, JS_ADD_ROW
			);

			$resources['loaded_doc_init']	= array(
				'colorpicker_init();',
				'Groups.init_modal();'
			);

			if( !EMPTY( $id ) )
			{
				$id_dec 	= filter_var( base64_url_decode( $id ), FILTER_SANITIZE_NUMBER_INT );
				
				check_salt( $id_dec, $salt, $token, $action );

				$details 	= $this->groups->get_specific_group( $id_dec );
			}

			$data['details']	= $details;

			$data['group_id']		= $id;
			$data['group_salt']		= $salt;
			$data['group_token']	= $token;
			$data['group_action']	= $action;

			$this->load->view("modals/groups", $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	
	}

	public function load_table()
	{
		$orig_params 	= get_params();

		$data 			= array();
		$resources 		= array();

		$users 			= array();

		$details 		= array();

		$action 		= NULL;

		try
		{
			// $this->redirect_off_system($this->module);

			$params 	= $this->set_filter( $orig_params )
							->filter_number('group_id', TRUE)
							->filter();

			if( !EMPTY( $params['group_id'] ) )
			{
				check_salt( $params['group_id'], $params['group_salt'], $params['group_token'], $params['group_action'] );

				$details = $this->user_groups->get_user_groups( $params['group_id'] );

				$action  = $params['group_action'];
			}

			$users 		= $this->users->get_all_users_active();

			$data['users']		= $users;
			$data['details']	= $details;
			$data['action']		= $action;

			$html 		= $this->load->view('tables/group_users', $data, TRUE);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	

		echo $html;
	}

	private function _validate( array $params, array $orig_params, $action = NULL )
	{
		$arr 					= array();

		$required 				= array();
		$constraints 			= array();

		$arr['group_description']		= NULL;

		$required['group_name']			= 'Group Name';
		$constraints['group_name']		= array(
			'name'			=> 'Group Name',
			'data_type'		=> 'string',
			'max_len'		=> '100'
		);

		if( ISSET( $orig_params['group_color'] ) AND !EMPTY( $orig_params['group_color'] ) )
		{
			$constraints['group_color']		= array(
				'name'			=> 'Group Color',
				'data_type'		=> 'string',
				'max_len'		=> '45'
			);
		}

		if( ISSET( $orig_params['group_description'] ) AND !EMPTY( $orig_params['group_description'] ) )
		{
			$constraints['group_description']		= array(
				'name'			=> 'Group Description',
				'data_type'		=> 'string',
				'max_len'		=> '255'
			);

			$arr['group_description'] = $params['group_description'];
		}

		if( ISSET( $orig_params['group_users'] ) AND !EMPTY( $orig_params['group_users'] ) )
		{
			$constraints['group_users']		= array(
				'name'			=> 'Members',
				'data_type'		=> 'db_value',
				'field'			=> ' COUNT( user_id ) as check_user ',
				'check_field'	=> 'check_user',
				'where' 		=> 'user_id',
				'table'			=> DB_CORE.'.'.SYSAD_Model::CORE_TABLE_USERS
			);
		}

		$this->check_required_fields( $params, $required );

		$this->validate_inputs( $params, $constraints );

		$arr['group_name']			= $params['group_name'];
		$arr['group_color']			= $params['group_color'];
		$arr['active_flag'] 		= ( ISSET( $params['active_flag'] ) ) ? ENUM_YES : ENUM_NO;

		if( $action == ACTION_ADD )
		{
			$arr['created_by']		= $this->session->user_id;
			$arr['created_date']	= $this->date_now;
		}
		else
		{
			$arr['modified_by']		= $this->session->user_id;
			$arr['modified_date']	= $this->date_now;
		}

		return $arr;
	}

	private function _process_user_groups( array $params, $group_id )
	{
		$arr 		= array();

		try
		{
			if( ISSET( $params['group_users'] ) AND !EMPTY( $params['group_users'] ) 
				AND !EMPTY( $params['group_users'][0] )
			)
			{
				foreach( $params['group_users'] as $key => $user )
				{
					$admin 						= 0;

					if( ISSET( $params['group_admins'][$key] ) AND !EMPTY( $params['group_admins'][$key] ) )
					{
						$admin 					= 1;
					}
					else
					{
						$admin 					= 0;
					}

					$arr[$key]['user_id']		= $user;
					$arr[$key]['group_id']		= $group_id;
					$arr[$key]['admin_flag']	= $admin;
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

		$update 				= ( ISSET( $orig_params['group_id'] ) AND !EMPTY( $orig_params['group_id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['group_id'] ) AND !EMPTY( $orig_params['group_id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$main_where 			= array();
		$group_id 				= NULL;

		$group_active_cnt 		= 0;
		$group_inactive_cnt 	= 0;

		try
		{
			// $this->redirect_off_system($this->module);

			$params 			= $this->set_filter( $orig_params )
									->filter_string( 'group_name' )
									->filter_number( 'group_id', TRUE )
									->filter_string( 'group_color' )
									->filter_string( 'group_description' )
									->filter_number( 'group_users', TRUE )
									->filter();

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
				$audit_table[] 	 	= '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`';
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$group_id 			= $this->groups->insert_groups( $val );

				$main_where 		= array(
					'group_id'		=> $group_id
				);

				$curr_detail[] 	 	= $this->groups->get_details_for_audit( '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`',
					$main_where
				);
			}
			else
			{
				check_salt( $params['group_id'], $params['group_salt'], $params['group_token'], $params['group_action'] );

				$group_id 			= $params['group_id'];

				$main_where 		= array(
					'group_id'		=> $group_id
				);

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_GROUPS;
				$audit_action[] 	= AUDIT_UPDATE;
				$prev_detail[]  	= $this->groups->get_details_for_audit( '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`',
					$main_where
				);

				$this->groups->update_groups( $val, $main_where );

				$curr_detail[] 	 	= $this->groups->get_details_for_audit( '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`',
					$main_where
				);
			}

			if( !EMPTY( $group_id ) )
			{
				$user_groups_val 		= $this->_process_user_groups( $params, $group_id );
				
				if( !EMPTY( $user_groups_val ) )
				{
					if( $action == ACTION_EDIT )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_GROUPS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->user_groups->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_GROUPS,
												$main_where
											 );

						$this->user_groups->delete_user_group( $main_where );

						$curr_detail[] 		= array();
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_GROUPS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->user_groups->insert_user_group( $user_groups_val );

					$curr_detail[] 		= $this->user_groups->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_GROUPS,
											$main_where
										 );

				}
			}

			$audit_name 				= 'Group '.$params['group_name'].'.';

			$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			$group_act_det 			= $this->groups->get_groups_status_cnt(ENUM_YES);
			$group_inact_det 		= $this->groups->get_groups_status_cnt(ENUM_NO);

			if( !EMPTY( $group_act_det ) )
			{
				$group_active_cnt 			= $group_act_det['group_status_cnt'];
			}

			if( !EMPTY( $group_inact_det ) )
			{
				$group_inactive_cnt 		= $group_inact_det['group_status_cnt'];
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
			'status'				=> $status,
			'datatable_options' 	=> $this->dt_options,
			'group_active_cnt' 		=> $group_active_cnt,
			'group_inactive_cnt'	=> $group_inactive_cnt
		);

		echo json_encode( $response );	
	}


	public function delete_groups()
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

		$group_active_cnt 		= 0;
		$group_inactive_cnt 	= 0;

		try
		{
			// $this->redirect_off_system($this->module);
			
			$params 			= $orig_params;

			$details  			= explode('/', $params['param_1']);
			$action 			= $details[0];
			$group_id 			= base64_url_decode( $details[1] );
			$group_salt 		= $details[2];
			$group_token 		= $details[3];

			check_salt( $group_id, $group_salt, $group_token, $action );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'group_id'	=> $group_id
			);

			SYSAD_Model::beginTransaction();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_TABLE_USER_GROUPS;
			$audit_action[] 			= AUDIT_DELETE;

			$prev_detail[] 				= $this->user_groups->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_USER_GROUPS,
				$main_where
			);

			$this->user_groups->delete_user_group( $main_where );

			$curr_detail[] 				= array();

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= '`'.SYSAD_Model::CORE_TABLE_GROUPS.'`';
			$audit_action[] 			= AUDIT_DELETE;

			$prev_grp 					= $this->groups->get_details_for_audit(
				'`'.SYSAD_Model::CORE_TABLE_GROUPS.'`',
				$main_where
			);

			$prev_detail[] 				= $prev_grp;

			$this->groups->delete_group( $main_where );

			$curr_detail[] 				= array();

			$group_act_det 			= $this->groups->get_groups_status_cnt(ENUM_YES);
			$group_inact_det 		= $this->groups->get_groups_status_cnt(ENUM_NO);

			if( !EMPTY( $group_act_det ) )
			{
				$group_active_cnt 			= $group_act_det['group_status_cnt'];
			}

			if( !EMPTY( $group_inact_det ) )
			{
				$group_inactive_cnt 		= $group_inact_det['group_status_cnt'];
			}

			$audit_activity				= sprintf($this->lang->line( 'audit_trail_delete'), $prev_grp[0]['group_name'] );

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
			'extra_function' 		=> 'Groups.delete_callback("'.$group_active_cnt.'", "'.$group_inactive_cnt.'", datatable_options);'
		);

		echo json_encode( $response );
	}
}