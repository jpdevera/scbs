<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main_queue extends SYSAD_Controller 
{
	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	private $table_id 			= 'table_queues';
	private $path 				= '';
	private $date_now;

	private $module_js;

	protected $fields 				= array();
	protected $filter 				= array();
	protected $order 				= array();

	public $common_modal 			= array();

	private $perm_email_q 			= FALSE;
	private $perm_sms_q 			= FALSE;

	public $data_table_opt_gen 		= array();

	public function __construct()
	{
		parent::__construct();

		$this->date_now 		= date('Y-m-d H:i:s');

		$this->controller 		= strtolower(__CLASS__);

		$this->module 			= MODULE_QUEUES;

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
		$this->perm_email_q 	= $this->permission->check_permission( MODULE_EMAIL_QUEUE );
		$this->perm_sms_q 		= $this->permission->check_permission( MODULE_SMS_QUEUE );

		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_QUEUES."/main_queue";

		$this->path 				= CORE_QUEUES.'/Main_queue'.'/get_list/';	

		$this->data_table_opt_gen 	= array(
			'table_id'	 		=> $this->table_id,
			'path' 				=> $this->path,
			'advanced_filter' 	=> true,
			'with_search'		=> true,
			'no_export' 	=> true,
			'no_colvis'		=> true,
			'no_bulk_delte'	=> true,
			'custom_option_callback'	=> 'Main_queue.custom_option_callback(options, default_setting);',
			'add_multi_del'		=> array(
				'check_callback'	=> 'Main_queue.check_callback(self, default_setting, tb);',
				'custom_button_func'=> 'Main_queue.custom_button_func(rows, default_setting, table_obj, tb);'
			)
		);

		$this->load->model(CORE_QUEUES.'/Main_queue_model', 'mqm');

		$aes_decrypt_from_fname = 'CAST( '. aes_crypt('b.fname', FALSE, FALSE).' AS char(100) )';
		$aes_decrypt_from_lname = 'CAST( '. aes_crypt('b.lname', FALSE, FALSE). ' AS char(100) )';	

		$aes_decrypt_to_fname = 'CAST( '. aes_crypt('c.fname', FALSE, FALSE).' AS char(100) )';
		$aes_decrypt_to_lname = 'CAST( '. aes_crypt('c.lname', FALSE, FALSE). ' AS char(100) )';	

		$this->fields 				= array(
			'CONCAT( '.$aes_decrypt_from_fname.'," ",'.$aes_decrypt_from_lname.' ) as from_user',
			'CONCAT( '.$aes_decrypt_to_fname.'," ",'.$aes_decrypt_to_lname.' ) as to_user',
			'a.message',
			'DATE_FORMAT( a.created_date, "%m/%d/%Y %h:%i" ) as created_date_format',
			'IF( a.sent_flag = "'.ENUM_NO.'", "No", "Yes" ) as sent_flag',
		);

		$this->filter 				= array(
			'CONCAT( '.$aes_decrypt_from_fname.'," ",'.$aes_decrypt_from_lname.' ) convert_to from_user',
			'CONCAT( '.$aes_decrypt_to_fname.'," ",'.$aes_decrypt_to_lname.' ) convert_to to_user',
			'a.message convert_to message',
			'DATE_FORMAT( a.created_date, "%m/%d/%Y %h:%i" ) convert_to created_date_format',
			'IF( a.sent_flag = "'.ENUM_NO.'", "No", "Yes" ) convert_to sent_flag',
		);

		$this->order				= array(
			'from_user', 'to_user', 'a.message', 'created_date_format', 'sent_flag'
		);
	}

	public function index($param = NULL)
	{
		$data = $resources = array();

		$this->redirect_module_permission( $this->module );

		try
		{
			$resources['load_css'] 		= array( CSS_LABELAUTY, CSS_SELECTIZE, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATATABLE_BUTTONS );
			$resources['load_js'] 		= array( JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_BUTTON_EXPORT_EXTENSION, $this->module_js );

			$resources['loaded_init'] 	= array(
				"refresh_new_datatable_params('".$this->table_id."');",
				'Main_queue.form_init();',
			);

			$resources['load_materialize_modal'] = array(
				'modal_message' => array (
					'title' 		=> "Message",
					'size'			=> "md",
					'module' 		=> CORE_QUEUES,
					'method'		=> 'modal_message',
					'controller'	=> __CLASS__,
					/*'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),*/
					'permission' 	=> FALSE,
					'post' 			=> true
				),

			);

			$resources['load_delete']	= array(
				'queue'	=> array(
					'delete_cntrl' 		=> 'Main_queue',
					'delete_method'		=> 'delete',
					'delete_module'		=> CORE_QUEUES
				)
			);
		}
		catch( PDOException $e )
		{	
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );

		}
		catch( Exception $e )
		{
			$msg  	= $this->rlog_error($e);	

			$this->error_index( $msg );

		}

		$data['perm_email_q'] 			= $this->perm_email_q;
		$data['perm_sms_q'] 			= $this->perm_sms_q;
		$data['table_id']				= $this->table_id;

		$this->template->load('queues', $data, $resources);

	}

	public function get_list()
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();

		$resources 				= array();

		try
		{
			$orig_params 		= get_params();
			$params 			= $this->set_filter($orig_params)
									->filter_string('module', TRUE)
									->filter();

			$table 				= NULL;

			if( $params['module'] == MODULE_EMAIL_QUEUE )
			{
				$table 			= SYSAD_Model::CORE_TABLE_EMAIL_NOTIFICATION_QUEUES;

				$this->fields[] = 'email_notification_queue_id as notif_id';
				$this->fields[] = 'email_data as send_data';
			}
			else if( $params['module'] == MODULE_SMS_QUEUE )
			{
				$table 			= SYSAD_Model::CORE_TABLE_SMS_NOTIFICATION_QUEUES;	
				$this->fields[] = 'sms_notification_queue_id as notif_id';
				$this->fields[] = 'sms_data as send_data';
			}

			$result 			= $this->mqm->get_list( $table, $this->fields, $this->filter, $this->order, $params );

			$cnt_result 		= count($result['aaData']);

			$counter 			= 0;

			$output['sEcho'] 				= $params['sEcho'];
			$output['iTotalRecords'] 		= $cnt_result;
			$output['iTotalDisplayRecords'] = $result['filtered_length']['filtered_length'];
			$output['aaData']				= array();

			$delete_per 	= $this->permission->check_permission( $params['module'], ACTION_DELETE );
			$view_per 		= $this->permission->check_permission( $params['module'], ACTION_VIEW );

			if(! EMPTY($result))
			{
				foreach($result['aaData'] as $r)
				{

					$salt 		= gen_salt();

					$id_detail 	= array(
						$r['notif_id'],
						$salt
					);

					$token_edit 	= $this->generate_salt_token_arr( $id_detail, ACTION_EDIT, $params['module'] );

					$token_view 	= $this->generate_salt_token_arr( $id_detail, ACTION_VIEW, $params['module'] );

					$token_delete 	= $this->generate_salt_token_arr( $id_detail, ACTION_DELETE, $params['module'] );

					$post_edit_json = json_encode( array(
						'notif_id'	=> base64_url_encode($r['notif_id']),
						'salt'			=> $salt,
						'token'			=> $token_edit['token_concat'],
						'action'		=> ACTION_EDIT,
						'module'		=> base64_url_encode($params['module'])
					) );

					$post_view_json = json_encode( array(
						'notif_id'	=> base64_url_encode($r['notif_id']),
						'salt'			=> $salt,
						'token'			=> $token_view['token_concat'],
						'action'		=> ACTION_VIEW,
						'module'		=> base64_url_encode($params['module'])
					) );

					$post_delete_json = json_encode( array(
						'notif_id'	=> base64_url_encode($r['notif_id']),
						'salt'			=> $salt,
						'token'			=> $token_delete['token_concat'],
						'action'		=> ACTION_DELETE,
						'module'		=> base64_url_encode($params['module'])
					) );

					$post_resend_json = json_encode( array(
						'notif_id'	=> base64_url_encode($r['notif_id']),
						'salt'			=> $salt,
						'token'			=> $token_delete['token_concat'],
						'action'		=> ACTION_DELETE,
						'module'		=> base64_url_encode($params['module']),
						'resend' 		=> true
					) );

					$post_view_message_json 	= json_encode(
						array(
							'message' => base64_url_encode( $r['message'] ),
							'module'		=> base64_url_encode($params['module'])
						)
					);

					$delete_class = '';

					$del_json_obj = json_encode(array(
						'notif_id' 	=> base64_url_encode($r['notif_id']),
						'module'	=> base64_url_encode($params['module'])
						/*'salt'		=> $salt,
						'token' 	=> in_salt($encrypt_id . '/' . $this->security_action_del, $salt),
						'action' 	=> $this->security_action_del*/
					));

					$actions 			= '';

					$actions 			.= "<div class='table-actions'>";

					if( $view_per )
					{
						/*$actions .= "<a href='#modal_announcement' data-modal_post='".$post_view_json."' class='modal_announcement_trigger tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_announcement_init('', this, 'View Announcement')\"><i class='grey-text material-icons'>visibility</i></a>";*/
					}

					$cust_message 	= json_encode( array(
						'h4' 	=> 'Are you sure you want to resend this message ?',
						'p' 	=> 'This action will resend this message to recipient.'
					) );

					$resend_action = 'content_queue_delete("Queue", "", "", this, undefined,'.$cust_message.')';


					$actions.= "<a href='javascript:;' data-delete_post='".$post_resend_json."' onclick='".$resend_action."' class='tooltipped' data-tooltip='Resend' data-position='bottom' data-delay='50'><i class='material-icons'>markunread</i></a>";



					if( $delete_per )
					{
						$delete_action = 'content_queue_delete("Queue", "", "", this, undefined, )';

						$actions .= "<a href='javascript:;' data-delete_post='".$post_delete_json."' onclick='".$delete_action."' class='tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
					}

					$actions .= "</div>";

					$counter++;


					if($cnt_result == $counter)
					{
						$resources['preload_modal'] = array("modal_message");
						// $resources['loaded_init'] = array("selectize_init();");
						$actions .= $this->load_resources->get_resource($resources, TRUE);
					}

					$link_message 		= "<a href='#modal_message' data-modal_post='".$post_view_message_json."' class='modal_message_trigger tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_message_init('', this, 'View Message')\">".limit_string( htmlentities($r['message']) )."</a>";

					$rows[] 		 	= array(
						$r['from_user'] . "<input type='hidden' data-disabled='".$delete_class."' class='dt_details' data-delete_post='".$del_json_obj."'>",
						$r['to_user'],
						$link_message,
						$r['created_date_format'],
						$r['sent_flag'],
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

		echo json_encode($output);
	}

	public function modal_message()
	{
		$data 					= array();
		$resources 				= array();	

		$disabled 				= '';

		$details 				= array();

		try
		{
			$post = $this->input->post(NULL, FALSE) ? $this->input->post(NULL, FALSE) : array();
			$get  = $this->input->get(NULL, FALSE) ? $this->input->get(NULL, FALSE) : array();

			$orig_params 		= array_merge($get, $post);
			
			$params 			= $this->set_filter( $orig_params )
									->filter();

			$data['params'] 	= $params;
			$data['orig_params']= $orig_params;

			$this->load->view("modals/message",$data);
			$this->load_resources->get_resource($resources);

		}
		catch(PDOException $e)
		{			 
			$msg = $this->get_user_message($e);

			$this->error_modal($msg);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			$this->error_modal($msg);
		}	
	}

	public function delete()
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

		try
		{
			$params 			= $this->set_filter($orig_params)
									->filter_string('module', TRUE)
									->filter_number('notif_id', TRUE)
									->filter();


			$table 				= NULL;
			$primary_key 		= NULL;

			if( !is_array( $params['module'] ) )
			{
				$module 		= $params['module'];

				if( $params['module'] == MODULE_EMAIL_QUEUE )
				{
					$table 			= SYSAD_Model::CORE_TABLE_EMAIL_NOTIFICATION_QUEUES;

					$primary_key 	= 'email_notification_queue_id';

					$this->data_table_opt_gen['post_data'] = array('module' => base64_url_encode(MODULE_EMAIL_QUEUE));
				}
				else if( $params['module'] == MODULE_SMS_QUEUE )
				{
					$table 			= SYSAD_Model::CORE_TABLE_SMS_NOTIFICATION_QUEUES;	
					$primary_key 	= 'sms_notification_queue_id';
					$this->data_table_opt_gen['post_data'] = array('module' => base64_url_encode(MODULE_SMS_QUEUE));
				}
			}
			else 
			{
				$module 	= $params['module'][0];

				if( in_array(MODULE_EMAIL_QUEUE, $params['module']) )
				{
					$table 			= SYSAD_Model::CORE_TABLE_EMAIL_NOTIFICATION_QUEUES;

					$primary_key 	= 'email_notification_queue_id';

					$this->data_table_opt_gen['post_data'] = array('module' => base64_url_encode(MODULE_EMAIL_QUEUE));
				}
				else if( in_array(MODULE_SMS_QUEUE, $params['module']) )
				{
					$table 			= SYSAD_Model::CORE_TABLE_SMS_NOTIFICATION_QUEUES;	
					$primary_key 	= 'sms_notification_queue_id';
					$this->data_table_opt_gen['post_data'] = array('module' => base64_url_encode(MODULE_SMS_QUEUE));
				}	
			}

			$delete_per 			= $this->permission->check_permission($module, ACTION_DELETE);

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}


			$main_where 	= array();

			if( is_array( $params['notif_id'] ) )
			{
				$main_where[$primary_key] = array('IN', $params['notif_id']);
			}
			else
			{
				$main_where[$primary_key] = $params['notif_id'];	
			}
			
			SYSAD_Model::beginTransaction();

			if( ISSET( $params['resend'] ) AND !EMPTY( $params['resend'] ) )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= $table;
				$audit_action[] 	= AUDIT_UPDATE;
				
				$prev_detail[]  	=  $this->mqm->get_details_for_audit( $table,
											$main_where
										 );

				$upd_val 			= array(
					'sent_flag' 	=> ENUM_NO
				);

				$this->mqm->update_helper($table, $upd_val, $main_where);

				$curr_detail[] 		=  $this->mqm->get_details_for_audit( $table,
											$main_where
										 );

				$msg 					= 'Message was return to queue.';
			}
			else
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= $table;
				$audit_action[] 	= AUDIT_DELETE;
				
				$prev_detail[]  	=  $this->mqm->get_details_for_audit( $table,
											$main_where
										 );

				$this->mqm->delete_helper($table, $main_where);

				$curr_detail[] 		= array();

				$msg 					= $this->lang->line( 'data_deleted' );
			}

			if( !EMPTY( $audit_schema ) )
			{
				$audit_name 	= $module;

				$audit_activity = sprintf( $this->lang->line('audit_trail_delete'), $audit_name);

				$this->audit_trail->log_audit_trail( $audit_activity, $module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
			}

			SYSAD_Model::commit();


			$status 				= SUCCESS;
			$flag 					= 1;
			
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
			'datatable_options' 	=> $this->data_table_opt_gen,
		);

		echo json_encode( $response );
	}
}