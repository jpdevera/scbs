<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sign_up extends SYSAD_Controller 
{
	
	private $controller;
	
	private $module_js;
	private $path;
	private $table_id;

	private $approve_per;

	private $status_drop 	= array();

	private $dt_opt 		= array();

	protected $fields 				= array();
	protected $filter 				= array();
	protected $order 				= array();

	private $dt_role = array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->controller 	= strtolower(__CLASS__);
		$this->module_code 	= MODULE_SIGN_UP_APPROVAL;
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_DASHBOARD."/".$this->controller;
		$this->path 		= CORE_DASHBOARD."/".$this->controller."/get_user_list/0";
		$this->table_id 	= "user_approval_table";
		
		$this->load->model('Sign_up_model', 'sign_up');
		$this->load->model(CORE_DASHBOARD.'/Dashboard_model', 'dshm');
		$this->load->model(CORE_USER_MANAGEMENT . '/users_model', 'users', TRUE);
		$this->load->model(CORE_USER_MANAGEMENT . '/roles_model', 'roles', TRUE);
		$this->load->model(CORE_USER_MANAGEMENT.'/Organizations_model', 'orgs');

		$this->approve_per 	= $this->permission->check_permission($this->module_code 	, ACTION_APPROVE);

		// APPROVED => "Approved", DISAPPROVED => "Disapproved",

		$this->status_drop 	= array(
			PENDING => "Pending", INCOMPLETE => 'Incomplete'
		);

		$this->dt_opt 	= array(
			'table_id' 			=> $this->table_id, 
			'path' 				=> $this->path, 
			'advanced_filter'	=> true,
			'with_search'		=> true,
			'post_data' 		=> array(
				'status_sign_up'=> '0'
			),
			'search_func' 		=> 'Sign_up.search_func(search_params);',
			'custom_option_callback'	=> 'Sign_up.custom_option_callback(options, default_setting);',
			/*'extra_bulk_custom_button'=> array(
				'approve'	=> array(
					'text' 	=> "Approve"
				),
				'reject' 		=> array(
					'text'	=> 'Reject'
				),
				'resend_email'	=> array(
					'text'	=> 'Resend Email'
				),
			),*/
			'no_export' 	=> true,
			'no_colvis'		=> true,
			'no_bulk_delte'	=> true,
			'add_multi_del'		=> array(
				'check_callback'	=> 'Sign_up.check_callback(self, default_setting, tb);',
				'custom_button_func'=> 'Sign_up.custom_button_func(rows, default_setting, table_obj, tb);'
			)

		);

		$this->fields 				= array(
			'a.role_code', 'a.role_name'
		);

		$this->filter 				= array(
			'a.role_code convert_to role_code', 'a.role_name convert_to role_name'
		);

		$this->order				= array(
			'a.role_code', 'a.role_name'
		);

		$this->dt_role 	= array(
			'table_id' 			=> 'table_role_user_add', 
			'path' 				=> CORE_DASHBOARD."/".$this->controller."/get_user_role_add_users_list/", 
			'advanced_filter'	=> true,
			'with_search'		=> true,
			'no_buttons'		=> true
		);
	}

	public function index()
	{
		try
		{
			
			$this->redirect_module_permission($this->module_code);

			$data 			= array();
			$resources 		= array();

			$roles 			= $this->roles->get_roles();

			$data['roles'] 			= $roles;
			$data['statistics'] 	= $this->users->get_user_status_count();
			$data['statistics_temp'] 	= $this->users->get_user_status_count_temp();

			$role_json 				= json_encode( $roles );

			$data['role_json'] 		= $role_json;

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATATABLE_BUTTONS);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_BUTTON_EXPORT_EXTENSION, $this->module_js);
			$resources['load_materialize_modal'] = array (
				'modal_user_details'=> array (
					'title' 		=> "User Details",
					'size' 			=> "lg",
					'module' 		=> CORE_DASHBOARD,
					'controller' 	=> __CLASS__,
					'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),
					'post' 			=> true
				)
			);

			$datatable_options 		= $this->dt_opt;

			$datatable_disappr 		= $datatable_options;
			$datatable_appr 		= $datatable_options;
			$datatable_inc 			= $datatable_options;

			$datatable_disappr['post_data']	= array(
				'status_sign_up'		=> DISAPPROVED
			);

			$datatable_appr['post_data']	= array(
				'status_sign_up'		=> APPROVED
			);

			$datatable_inc['post_data']	= array(
				'status_sign_up'		=> INCOMPLETE
			);

			$resources['datatable'] = array( $datatable_options, $this->dt_role );

			$json_datatable_options 		= json_encode( $datatable_options );
			$json_datatable_appr_options 	= json_encode( $datatable_appr );
			$json_datatable_disappr_options = json_encode( $datatable_disappr );
			$json_datatable_inc_options 	= json_encode($datatable_inc);
			
			$resources['loaded_init'] = array(
				'materialize_select_init();',
				"Sign_up.initObj('".$json_datatable_options."');",
				"refresh_datatable('".$json_datatable_options."','#ctr_pending');",
				"refresh_datatable('".$json_datatable_disappr_options."','#ctr_disapproved');",
				"refresh_datatable('".$json_datatable_appr_options."','#ctr_approved');",
				"refresh_datatable('".$json_datatable_inc_options."','#ctr_incomplete');"
			);

			$data['status_arr'] 	= $this->status_drop;

			$resources['load_delete']	= array(
				'user'	=> array(
					'delete_cntrl' 		=> 'Sign_up',
					'delete_method'		=> 'delete_incomplete_user',
					'delete_module'		=> CORE_DASHBOARD
				)
			);

			$this->template->load('sign_up', $data, $resources);
		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			$this->error_index( $msg );
		}
	}

	public function get_user_role_add_users_list()
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();
		$extra_data 			= array();

		$resources 				= array();

		$data_arr 				= array();

		try
		{
			$params 			= get_params();

			/*$order 				= array(
				'a.sort_order', 'a.sort_order', 'a.column_1', 'a.column_2', 'a.column_3'
			);*/

			$result 			= $this->dshm->get_user_role_add_users_list( $this->fields, $this->filter, $this->order, $params );

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

					$actions 			= '';

					$actions 			.= "<div class='table-actions'>";

					$actions .= "</div>";

					$counter++;


					if($cnt_result == $counter)
					{
						// $resources['preload_modal'] = array("modal_sample");
						// $resources['loaded_init'] = array("selectize_init();");
						// $actions .= $this->load_resources->get_resource($resources, TRUE);
					}

					$rows[] 		 	= array(
						$r['role_code'],
						$r['role_name'],
						$actions
					);
				}

				$output['iTotalRecords'] = $counter;
			}

			$flag 				= 1;
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
		// $output['extra_data'] 	= $extra_data;

		echo json_encode($output);
	}

	private function _filter_sign_up( array $orig_params )
 	{
 		$par 			= $this->set_filter( $orig_params )
 							->filter_number('id', TRUE)
							->filter_number('user_id', TRUE)
							;

		$params 		= $par->filter();
		
		return $params;
 	}

	public function get_user_list($filter_status = NULL)
	{
		
		// Variables needed for the datatable 		
		$total_records	= $display_records = $flag = 0;
		$table_data		= array();
		$msg			= $this->lang->line('err_page_500_heading');
		
		try 
		{
			$params	= get_params();

			$account_creator = get_setting(ACCOUNT, "account_creator");

			if( ISSET( $params['search_status_sign_up'] ) )
			{
				$filter_status 	= $params['search_status_sign_up'];
			}

			if( ISSET( $params['status_sign_up'] ) )
			{
				$filter_status 	= $params['status_sign_up'];
			}
			
			if( ! is_int($filter_status) )
			{
				$filter_status_dec = $this->decrypt($filter_status);

				if( !EMPTY( $filter_status_dec ) )
				{
					$filter_status = $filter_status_dec;
				}
			}
			
			$total_records		= $this->users->get_user_list($filter_status, NULL, $account_creator);

			$records_info 		= $this->users->get_user_list($filter_status, $params, $account_creator);
			$records			= $records_info['records'];
			$display_records	= $records_info['display_records'];
			
			for($itr = 0; $itr < $display_records; $itr++)
			{
				$record = $records[$itr];
				$avatar = $this->_construct_avatar($record);
				$status	= $record["sys_param_code"];
								
				$account_status = ($status === STATUS_APPROVED) ? '<div class="mute m-t-xs"><em>-- Unauthenticated --</em></div>' : '';
				
				// Construct table actions
				$encrypt_id	= $this->encrypt($record["user_id"]);				
				$salt		= gen_salt();
				$actions	= '';				
			
				$del_json_obj = json_encode(array(
					'user_id' 	=> $encrypt_id,
					'status_per'=> $record['sys_param_value']
					/*'salt'		=> $salt,
					'token' 	=> in_salt($encrypt_id . '/' . $this->security_action_del, $salt),
					'action' 	=> $this->security_action_del*/
				));

				$delete_class = '';
				$resend_email_class = '';
				$delete_inc_class 	= '';

				$post_view_json = json_encode( array(
					'id'	=> base64_url_encode( $record['user_id'] ),
					'action' => ACTION_EDIT
				) );

				$post_view_only_json = json_encode( array(
					'id'	=> base64_url_encode( $record['user_id'] ),
					'action' => ACTION_VIEW
				) );

				$post_delete_json = json_encode( array(
					'user_id'	=> base64_url_encode($record['user_id']),
				) );
					
				switch($status)
				{
					case STATUS_APPROVED:
					case STATUS_DISAPPROVED:

						$resend_email_class = 'resend_email_class';
						$actions.= "<div><a href='javascript:;' class='tooltipped' data-tooltip='Resend Email' data-position='bottom' data-delay='50' onclick=\"Sign_up.resendEmail('".$encrypt_id."','".base64_url_encode($status)."','".$record['email']."')\"><i class='material-icons'>markunread</i></a></div>";
					break;
					
					case STATUS_PENDING:					
						$resend_email_class = 'approve_reject_class';
						if($this->approve_per)
						{	
							$actions .= "";
							$actions .= "<a href='#modal_user_details' data-modal_post='".$post_view_json."' class='modal_user_details_trigger tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_user_details_init('', this, 'View User')\"><i class='grey-text material-icons'>visibility</i></a>";
							/*$actions.= "<a href='javascript:;' class='waves-effect waves-light approve tooltipped popmodal-dropdown' data-id-selector='approve_id' data-ondocumentclick-close='false' data-ondocumentclick-close-prevent='e' data-id='".$encrypt_id."' data-placement='rightCenter' data-showclose-but='false' data-popmodal-bind='#approve_content' data-tooltip='Approve' data-position='bottom' data-delay='50'><i class='material-icons'>done</i></a>";
							$actions.= "<a href='javascript:;' class='waves-effect waves-light disapprove tooltipped popmodal-dropdown' data-id-selector='reject_id' data-id='".$encrypt_id."' data-ondocumentclick-close='false' data-ondocumentclick-close-prevent='e'  data-placement='rightCenter' data-showclose-but='false' data-popmodal-bind='#reject_content' data-tooltip='Disapprove' data-position='bottom' data-delay='50'><i class='material-icons'>clear</i></a>";*/
						}
					break;
					case STATUS_INCOMPLETE :
						$resend_email_class = 'delete_inc_class';
						if($this->approve_per)
						{	
							$actions .= "";
							$actions .= "<a href='#modal_user_details' data-modal_post='".$post_view_only_json."' class='modal_user_details_trigger tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_user_details_init('', this, 'View User')\"><i class='grey-text material-icons'>visibility</i></a>";

							$delete_action = 'content_user_delete("Incomplete User", "", "", this)';

							$actions .= "<a href='javascript:;' data-delete_post='".$post_delete_json."' onclick='".$delete_action."' class='tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
							/*$actions.= "<a href='javascript:;' class='waves-effect waves-light approve tooltipped popmodal-dropdown' data-id-selector='approve_id' data-ondocumentclick-close='false' data-ondocumentclick-close-prevent='e' data-id='".$encrypt_id."' data-placement='rightCenter' data-showclose-but='false' data-popmodal-bind='#approve_content' data-tooltip='Approve' data-position='bottom' data-delay='50'><i class='material-icons'>done</i></a>";
							$actions.= "<a href='javascript:;' class='waves-effect waves-light disapprove tooltipped popmodal-dropdown' data-id-selector='reject_id' data-id='".$encrypt_id."' data-ondocumentclick-close='false' data-ondocumentclick-close-prevent='e'  data-placement='rightCenter' data-showclose-but='false' data-popmodal-bind='#reject_content' data-tooltip='Disapprove' data-position='bottom' data-delay='50'><i class='material-icons'>clear</i></a>";*/
						}
					break;
				}
				
				if( ($itr + 1) == $display_records)
				{
					$resources['load_js'] 		= array(JS_POP_MODAL, $this->module_js);
					$resources['preload_modal'] = array("modal_user_details");
					$resources['loaded_doc_init'] 	= array(
						"selectize_init();",
						"Sign_up.initTable();"
					);
					$actions.= $this->load_resources->get_resource($resources, TRUE);
				}
				
					
				$table_data[] = array(
					$avatar . "<input type='hidden' data-disabled='".$delete_class."' class='dt_details ".$resend_email_class."' data-delete_post='".$del_json_obj."'>" . $record["fname"] . ' ' . $record["lname"],
					'<span class="font-semibold">' . $record['org_name'] . '</span>',
					'<em>' . $record['job_title'] . '</em>',					
					$record["email"],
					'<div class="center-align">' . $record['sys_param_name'] . $account_status . '</div>',
					'<div class="table-actions">' . $actions . '</div>'
				);
			}
			
			$flag	= 1;
			$msg	= "";
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
			
		}
		
		echo json_encode(
			array(
				'aaData'				=> $table_data,
				'sEcho'					=> intval($params['sEcho']),
				'iTotalRecords'			=> $total_records,
				'iTotalDisplayRecords'	=> $display_records,
				'flag'					=> $flag,
				'msg'					=> $msg
			)
		);
	}
	
		private function _construct_avatar($record)
	{
		try 
		{
			$photo_path = '';
			$img_src	= base_url().PATH_IMAGES . "avatar.jpg";
			if( ! empty($record['photo']) )
			{
				$root_path  = $this->get_root_path();
				$photo_path = $root_path . PATH_USER_UPLOADS . $record['photo'];
				$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);
				
				if( file_exists( $photo_path ) )
				{
					
					$check_upl = $this->check_custom_path();
					
					if( ! empty($check_upl) )
					{
						$img_src = output_image($record['photo'], PATH_USER_UPLOADS);
					}
					else
					{
						$img_src = base_url() . PATH_USER_UPLOADS . $record['photo'];
					}
				}				
			}
			
			$contact_flag = ($record['contact_flag'] == 1) ? "<i class='material-icons small'>fiber_manual_record</i>" : "";
			
			if( ! empty($photo_path) )
			{
				$img = '<img class="avatar" width="20" height="20" src="'.$img_src.'" /> ' . $contact_flag;
			}
			else
			{
				$img = '<img class="avatar default-avatar" data-name="'.$record['fname'].'" /> ' . $contact_flag;
			}
			
			return '<span class="table-avatar-wrapper">' . $img . '</span>';

		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	public function modal()
	{
		
		$orig_params 	= get_params();
		try
		{
			$params 	= $this->_filter_sign_up($orig_params);
			
			$data 	= array();

			if( is_array($params['id']) )
			{
				$multiple 	= TRUE;
			}
			else
			{
				$multiple 	= FALSE;
			}

			$dis_appr_reject = '';

			if( ISSET( $params['action'] ) AND $params['action'] == ACTION_VIEW )
			{
				$dis_appr_reject = 'disabled';
			}

			$all_orgs = $this->orgs->get_orgs_all();


			$resources['load_js'] 	= array(JS_LABELAUTY, JS_SELECTIZE, $this->module_js);
			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_SELECTIZE);
			$resources['loaded_init']	= array('Sign_up.modal_init();', 'Sign_up.save_modal();');

			$user_id 			= $params['id'];

			$account_creator 	= get_setting(ACCOUNT, "account_creator");
				
			$data["user"] 		= $this->users->get_user_details($params['id'], $account_creator);
			$data['multiple'] 	= $multiple;
			$data['all_orgs']	= $all_orgs;
			$data['dis_appr_reject'] = $dis_appr_reject;

			$roles 			= $this->roles->get_roles();

			$data['roles'] 			= $roles;

			$role_json 				= json_encode( $roles );

			$data['role_json'] 		= $role_json;


			$main_orgs_det 					= $this->auth_model->get_user_organizations_temp($user_id, LOGGED_IN_FLAG_YES);

			$other_orgs_det 				= $this->auth_model->get_user_organizations_temp($user_id, LOGGED_IN_FLAG_NO);

			$main_orgs 	= array();
			$other_orgs = array();

			if( !EMPTY( $main_orgs_det ) )
			{
				$main_orgs 					= array_column($main_orgs_det, 'org_code');
			}

			if( !EMPTY( $other_orgs_det ) )
			{
				$other_orgs 				= array_column($other_orgs_det, 'org_code');
			}

			$data['main_orgs']				= $main_orgs;
			$data['other_orgs']				= $other_orgs;

			$data['orig_params'] 			= $orig_params;
			$data['params'] 				= $params;

			$this->load->view("modals/dashboard_user_details", $data);
			$this->load_resources->get_resource($resources);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error( $e, TRUE );

			$this->error_modal( $msg );
		}	
	}

	private function _validate_update_user($params, $action = NULL)
	{
		$required 		= array();
		$constraints	= array();

		if( ISSET( $params['main_role'] ) )
		{

			$required['main_role']	= 'Main Role';

			if( !EMPTY( $params['role'] ) AND !EMPTY( $params['role'][0] ) )
			{
				if( in_array( $params['main_role'][0], $params['role'] ) )
				{
					throw new Exception('There may be a duplicate role in both main role and other roles.');
				}
			}
		}

		$this->check_required_fields( $params, $required );

		$this->validate_inputs( $params, $constraints );
	}
		
	public function update_user_status()
	{
		try
		{
			// $this->redirect_off_system($this->module);

			$status = ERROR;
			$params	= get_params();

			$params = $this->set_filter($params)
						->filter_number('user_id', TRUE)
						->filter();

			$this->_validate_update_user( $params );

			$permission 		= $this->approve_per;
			$per_msg 			= $this->lang->line( 'err_unauthorized_approve_disapprove_user' );

			$account_creator = get_setting(ACCOUNT, "account_creator");

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}

			$table_us 	= SYSAD_Model::CORE_TABLE_USERS;

			if( !EMPTY( $account_creator ) AND $account_creator == VISITOR  )
			{
				$table_us = SYSAD_Model::CORE_TABLE_TEMP_USERS;
			}

			SYSAD_Model::beginTransaction();

			if( ISSET( $params['id'] ) )
			{
	
				// GET SECURITY VARIABLES
				$decode_id	= $this->decrypt($params['id']);
				$id			= filter_var($decode_id, FILTER_SANITIZE_NUMBER_INT);
				// BEGIN TRANSACTION			
				
				$audit_table[]	= $table_us;
				$audit_schema[]	= DB_CORE;
				$audit_action[]	= AUDIT_UPDATE;
									
				$params['user_id'] 	= $decode_id;
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
				$prev_detail[] 		= $this->users->get_specific_user($id, FALSE, TRUE, $account_creator);
				
				$this->users->update_status($params, $account_creator);

				$curr_detail[] 	= $this->users->get_specific_user($id, FALSE, TRUE, $account_creator);

				$activity = "%s user account has been approved";
				$activity = sprintf($activity, $curr_detail[0][0]['fname'] . ' ' . $curr_detail[0][0]['lname']);				
				
				// LOG AUDIT TRAIL
				$this->audit_trail->log_audit_trail(
					$activity, 
					$this->module_code, 
					$prev_detail, 
					$curr_detail, 
					$audit_action, 
					$audit_table,
					$audit_schema
				);
			}
			else 
			{
				if( ISSET( $params['user_id'] ) )
				{
					$main_where = array();

					if( is_array( $params['user_id'] ) )
					{
						$user_ids 	= array('IN', $params['user_id']);
						$main_where['user_id'] = $user_ids;

						$id = $params['user_id'];

						foreach( $params['user_id'] as $user_id )
						{
							$upd_val = $params;

							$audit_table[]	= $table_us;
							$audit_schema[]	= DB_CORE;
							$audit_action[]	= AUDIT_UPDATE;
												
							$upd_val['user_id'] 	= $user_id;

							if( ISSET( $upd_val['reject_reason'] ) AND !EMPTY( $upd_val['reject_reason'] ) )
							{
								$upd_val['reason'] = $upd_val['reject_reason'];
							}

							// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD

							$prev_detail[] 		= $this->users->get_specific_user($user_id, FALSE, TRUE, $account_creator);
							
							$this->users->update_status($upd_val,$account_creator);

							$curr_detail[] 	= $this->users->get_specific_user($user_id, FALSE, TRUE, $account_creator);

							$activity = "%s user account has been approved";
							$activity = sprintf($activity, $curr_detail[0][0]['fname'] . ' ' . $curr_detail[0][0]['lname']);				
							
							// LOG AUDIT TRAIL
							$this->audit_trail->log_audit_trail(
								$activity, 
								$this->module_code, 
								$prev_detail, 
								$curr_detail, 
								$audit_action, 
								$audit_table,
								$audit_schema
							);
						}

					}
				}
			}

			$msg 			= $this->lang->line('data_updated');
			
			// GET THE DETAIL AFTER UPDATING THE RECORD
			
			
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
			
			$mail_flag = $this->_send_email_check_bulk($id, $params['status_id']);
									
			SYSAD_Model::commit();
			
			// $mail_flag = $this->_send_email($id, $params['status_id']);
			
			
			$status = SUCCESS;
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();
		
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
				
			$msg = $this->rlog_error($e, TRUE);
		}
		
		$statistics = $this->users->get_user_status_count();
		$statistics_temp = $this->users->get_user_status_count_temp();
		
		$info = array(
			"status" 	=> $status,
			"msg" 		=> $msg,
			"id" 		=> $id,
			"pending" 	=> $statistics_temp['pending_count'],
			"approved" 	=> $statistics_temp['approved_count'],
			"disapproved" 	=> $statistics['disapproved_count'],
			'incomplete'	=> $statistics_temp['incomplete_count'],
			"mail" 			=> $mail_flag,
			"datatable_options"	=> array('table_id' => $this->table_id, 'path' => $this->path, 'advanced_filter' => true),
			'datatable_id'		=> $this->table_id
		);
	
		echo json_encode($info);
	}

	public function resend_approval_email()
	{
		try
		{
			// $this->redirect_off_system($this->module);
			
			$status 		= ERROR;
			$params			= get_params();

			$account_creator = get_setting(ACCOUNT, "account_creator");

			if( ISSET( $params['user_id'] ) AND !EMPTY( $params['user_id'] ) )
			{
				$params = $this->set_filter($params)
						->filter_number('user_id', TRUE)
						->filter();

				$id = $params['user_id'];

				foreach( $params['user_id'] as $key => $user_id )
				{
					$stat_code 		= DISAPPROVED;

					if( ISSET( $params['status_per'][$key] ) AND !EMPTY( $params['status_per'][$key] ) )
					{
						$stat_code  = $params['status_per'][$key];
					}

					$mail_flag 		= $this->_send_email($user_id, $stat_code);	
				}


				$msg			= "Email was successfully resent.";
			}
			else
			{
				if( ISSET( $params['id'] ) AND !EMPTY( $params['id'] ) )
				{

					$decode_id		= base64_url_decode($params['id']);
					$id				= filter_var($decode_id, FILTER_SANITIZE_NUMBER_INT);
					$status_id		= base64_url_decode($params['status_id']);
					$status_code	= ($status_id == STATUS_APPROVED) ? APPROVED : DISAPPROVED;
					
					$user_detail 	= $this->users->get_user_details($id, $account_creator);
				}
				
				$mail_flag 		= $this->_send_email($id, $status_code);

				$msg			= "Email was successfully resent to <strong>" . $user_detail['email'] . "</strong>";
			}
			$status 		= SUCCESS;
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();
	
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
	
			$msg = $this->rlog_error($e, TRUE);
		}
	
		$info = array(
			"status" 	=> $status,
			"msg" 		=> $msg,
			"id" 		=> $id,
			"mail" 		=> $mail_flag
		);
	
		echo json_encode($info);
	}

	private function _send_email_check_bulk($id, $status)
	{
		try
		{
			if( is_array( $id ) ) 
			{
				foreach( $id as $k => $i_d )
				{
					$this->_send_email($i_d, $status);		
				}
			}
			else
			{
				return $this->_send_email($id, $status);
			}
		}
		catch( PDOException $e )
		{
			echo $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			echo $this->rlog_error( $e, TRUE );
		}	
	}
	
	private function _send_email($id, $status)
	{	
		try
		{
			$account_creator = get_setting(ACCOUNT, "account_creator");
			$user_detail 	= $this->users->get_user_details($id, $account_creator);
			
			$flag 			= 0;
			$email_data 	= array();
			$template_data 	= array();
	
			$system_title 	= get_setting(GENERAL, "system_title");

			$sys_logo 				 	= get_setting(GENERAL, "system_logo");
			$system_logo_src 			= base_url() . PATH_IMAGES . "logo_white.png";

			if( !EMPTY( $sys_logo ) )
			{
				$root_path 				= $this->get_root_path();
				$sys_logo_path 			= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
				$sys_logo_path 			= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

				if( file_exists( $sys_logo_path ) )
				{
					$system_logo_src 	= base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
					$system_logo_src 	= @getimagesize($system_logo_src) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
				}
			}
				
			// required parameters for the email template library
			$email_data["from_email"] 	= get_setting(GENERAL, "system_email");
			$email_data["from_name"] 	= $system_title;
			$email_data["to_email"] 	= array($user_detail['email']);
			$email_data["subject"] 		= ($status == APPROVED) ? 'Activate your Account' : 'Registration Denied';
			
			// additional set of data that will be used by a specific template
			$template_data["email"] 		= $user_detail['email'];
			$template_data["password"] 		= base64_url_encode($user_detail['password']);
			$template_data["reason"] 		= $user_detail['reason'];
			$template_data["name"] 			= $user_detail['fname'] . ' ' . $user_detail['lname'];
			$template_data["status"] 		= $status;
			$template_data["system_name"] 	= $system_title;
			$template_data["id"] 			= $id;
			$template_data['sign_up_login'] = TRUE;
			$template_data['logo']			= $system_logo_src;
				
			$template_data['logo']			= '
				<div style="background:#333333; padding:20px 30px; text-align:center;"><img src="'.$template_data['logo'].'" height="40" alt="logo" /></div>
';

			$template_data['link']			= '
				<a style="word-wrap:break-word" href="'.base_url().'" title="Activate Account" target="_blank">'.base_url().'</a>
';

			if( $status == DISAPPROVED )
			{
				$this->email_template->send_email_template_html($email_data, STATEMENT_CODE_EMAIL_REJECT_USER, $template_data);
			}
			else
			{
				$this->email_template->send_email_template_html($email_data, STATEMENT_CODE_EMAIL_APPROVE_USER, $template_data);
			}
			//$flag = 1;
			$flag = $this->email->print_debugger();

			$errors 						= $this->email_template->get_email_errors();

			if( !EMPTY( $errors ) )
			{
				$str 						= var_export( $errors, TRUE );

				throw new Exception($str);

				RLog::error( "Email Error" ."\n" . $str . "\n" );
			}
			else
			{
				if( $status == APPROVED )
				{
					$this->load->library('Authentication_factors');

					$prev_detail 			= array();
					$curr_detail 			= array();
					$audit_table 			= array();
					$audit_action 			= array();
					$audit_schema 			= array();
					$audit_activity 		= '';

					$audit_details 		= $this->authentication_factors->insert_temp_user_to_main($id, $account_creator);

					if( !EMPTY( $audit_details['audit_table'] ) )
					{
						$upd_where 	= array(
							'user_id'	=> $audit_details['user_id']
						);

						$upd_val 	= array(
							'status' => STATUS_ACTIVE
						);
						$this->users->update_helper(SYSAD_Model::CORE_TABLE_USERS, $upd_val, $upd_where);

						$audit_schema 				= array_merge( $audit_schema, $audit_details['audit_schema'] );
						$audit_table 				= array_merge( $audit_table, $audit_details['audit_table'] );
						$audit_action 				= array_merge( $audit_action, $audit_details['audit_action'] );
						$prev_detail 				= array_merge( $prev_detail, $audit_details['prev_detail'] );
						$curr_detail 				= array_merge( $curr_detail, $audit_details['curr_detail'] );

						if( !EMPTY( $audit_schema ) )
						{
							$audit_activity = $audit_details['audit_activity'];

							$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
						}
					}
				}
				else if( $status == DISAPPROVED )
				{
					$prev_detail 			= array();
					$curr_detail 			= array();
					$audit_table 			= array();
					$audit_action 			= array();
					$audit_schema 			= array();
					$audit_activity 		= '';

					// SYSAD_Model::beginTransaction();

					$temp_user_where = array(
						'user_id'	=> $id
					);

					$prev_temp_user_role = $this->users->get_details_for_audit(
						SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES,
						$temp_user_where
					);

					if( !EMPTY( $prev_temp_user_role ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES,
														$temp_user_where
													 );

						$this->users->delete_helper( SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES, $temp_user_where );

						$curr_detail[] 		= array();
					}

					$prev_user_org 		= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS,
													$temp_user_where
												 );

					$prev_user_secu 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS,
													$temp_user_where
												 );

					$prev_user_auth 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH,
													$temp_user_where
												 );

					$prev_user_aggr 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS,
													$temp_user_where
												 );

					if( !EMPTY( $prev_user_aggr ) )
					{

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $prev_user_aggr;

						$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS, $temp_user_where);

						$curr_detail[] 		= array();
					}

					if( !EMPTY( $prev_user_org ) )
					{

						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $prev_user_org;

						$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $temp_user_where);

						$curr_detail[] 		= array();
					}

					if( !EMPTY( $prev_user_auth ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $prev_user_auth;

						$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, $temp_user_where);

						$curr_detail[] 		= array();	
					}

					if( !EMPTY( $prev_user_secu ) )
					{
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $prev_user_secu;

						$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS, $temp_user_where);

						$curr_detail[] 		= array();	
					}

					$prev_us 			= $this->users->get_user_details($id, $account_creator);

					if( ISSET( $prev_us['org_code'] ) AND !EMPTY( $prev_us['org_code'] ) )
					{
						$main_org_where 	= array(
							'org_code'	=> $prev_us['org_code']
						);
						$audit_schema[] 	= DB_CORE;
						$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS;
						$audit_action[] 	= AUDIT_DELETE;
						$prev_detail[]  	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS,
														$main_org_where
													 );

						$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $main_org_where);

						$curr_detail[] 		= array();
					}

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USERS;
					$audit_action[] 	= AUDIT_DELETE;
					
					$prev_detail[]  	= array( $prev_us );

					$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USERS, $temp_user_where);

					$curr_detail[] 		= array();

					if( !EMPTY( $audit_schema ) )
					{
						$audit_name 	= 'User '.$prev_us['fname'].' '.$prev_us['lname'];

						$audit_activity = sprintf( $this->lang->line('audit_trail_delete'), $audit_name);

						$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
					}

					// SYSAD_Model::commit();
				}
			}
			
			return $flag;
		}
		catch( PDOException $e )
		{
			// SYSAD_Model::rollback();
			echo $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			// SYSAD_Model::rollback();
			echo $this->rlog_error( $e, TRUE );
		}	
	
	}

	public function delete_incomplete_user()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();

		$orig_params 			= get_params();

		// $delete_per 			= $this->delete_per;

		$msg 					= '';
		$flag 					= 0;
		$status 				= ERROR;

		$main_where 			= array();

		$statistics_json 		= '';

		try
		{
			$params 			= $this->_filter_sign_up( $orig_params );

			$account_creator 	= get_setting(ACCOUNT, "account_creator");

			$arr 				= FALSE;

			$main_where 		= array();

			if( ISSET( $params['user_id'] ) )
			{
				if( is_array( $params['user_id'] ) )
				{
					$main_where['user_id'] = array( 'IN', $params['user_id'] );
					$arr 		= TRUE;
				}
				else
				{
					$main_where['user_id'] = $params['user_id'];
				}
			}

			SYSAD_Model::beginTransaction();

			if( !EMPTY( $main_where ) )
			{
				$prev_temp_user_role = $this->users->get_details_for_audit(
					SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES,
					$main_where
				);

				if( !EMPTY( $prev_temp_user_role ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES,
													$main_where
												 );

					$this->users->delete_helper( SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES, $main_where );

					$curr_detail[] 		= array();
				}

				$prev_user_org 		= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS,
												$main_where
											 );

				$prev_user_secu 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS,
												$main_where
											 );

				$prev_user_auth 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH,
												$main_where
											 );

				$prev_user_aggr 	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS,
													$main_where
												 );

				if( !EMPTY( $prev_user_aggr ) )
				{

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $prev_user_aggr;

					$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_AGREEMENTS, $main_where);

					$curr_detail[] 		= array();
				}

				if( !EMPTY( $prev_user_org ) )
				{

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $prev_user_org;

					$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $main_where);

					$curr_detail[] 		= array();
				}

				if( !EMPTY( $prev_user_auth ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $prev_user_auth;

					$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH, $main_where);

					$curr_detail[] 		= array();	
				}

				if( !EMPTY( $prev_user_secu ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $prev_user_secu;

					$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS, $main_where);

					$curr_detail[] 		= array();	
				}


				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_GROUPS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $prev_user_secu;

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USER_GROUPS, $main_where);

				$curr_detail[] 		= array();	

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $prev_user_secu;

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS, $main_where);

				$curr_detail[] 		= array();	

				$prev_u 			= array();

				$prev_us 			= $this->users->get_user_details($params['user_id'], $account_creator);

				$org_codes 			= array();

				if( $arr )
				{
					$prev_u 		= $prev_us;

					$user_det 		= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_USERS, $main_where );

					$org_codes 		= array_column($user_det, 'org_code');
				}
				else
				{
					$prev_u 		= array( $prev_us );

					$org_codes 		= array_column($prev_u, 'org_code');
				}

				
				if( ISSET( $org_codes ) AND !EMPTY( $org_codes ) )
				{
					$main_org_where 	= array(
						'org_code'	=> array('IN', $org_codes)
					);
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[]  	= $this->users->get_details_for_audit( SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS,
													$main_org_where
												 );
				
					$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_ORGANIZATIONS, $main_org_where);

					$curr_detail[] 		= array();
				}

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_TEMP_USERS;
				$audit_action[] 	= AUDIT_DELETE;
				
				$prev_detail[]  	= $prev_u;

				$this->users->delete_helper(SYSAD_Model::CORE_TABLE_TEMP_USERS, $main_where);

				$curr_detail[] 		= array();

				if( !EMPTY( $audit_schema ) )
				{
					$audit_name 	= 'Temp User(s)';

					$audit_activity = sprintf( $this->lang->line('audit_trail_delete'), $audit_name);

					$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
				}	
			}

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_deleted' );

			$statistics_temp = $this->users->get_user_status_count_temp();
			$statistics_json = json_encode($statistics_temp);	

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
			'reload' 				=> 'datatable',
			'datatable_id' 			=> $this->table_id,
			'extra_reload' 	=> 'function',
			'extra_function' => 'Sign_up.extra_function('.$statistics_json.');'
		);

		echo json_encode( $response );
	}
}
