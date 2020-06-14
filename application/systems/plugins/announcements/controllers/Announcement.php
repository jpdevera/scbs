<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Announcement extends SYSAD_Controller 
{
	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	private $table_id 			= 'table_announcements';
	private $path 				= '';
	private $date_now;

	private $module_js;

	protected $fields 				= array();
	protected $filter 				= array();
	protected $order 				= array();

	public $common_modal 			= array();

	public function __construct()
	{
		parent::__construct();

		$this->date_now 		= date('Y-m-d H:i:s');

		$this->controller 		= strtolower(__CLASS__);

		$this->module 			= MODULE_ANNOUNCEMENTS;

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
		
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_ANNOUNCEMENTS."/announcement";

		$this->path 				= CORE_ANNOUNCEMENTS.'/Announcement'.'/get_list/';	

		$this->data_table_opt_gen 	= array(
			'table_id'	 		=> $this->table_id,
			'path' 				=> $this->path,
			'advanced_filter' 	=> true,
			'with_search'		=> true
		);

		$this->load->model(CORE_ANNOUNCEMENTS.'/Announcements_model', 'am');

		$this->fields 				= array(
			'a.announcement_id', 'a.description'
		);

		$this->filter 				= array(
			'a.description convert_to announcement'
		);

		$this->order				= array(
			'a.description'
		);

	}

	public function index($param = NULL)
	{
		$data = $resources = array();

		$this->redirect_module_permission( $this->module );

		try
		{
			$resources['load_css'] 		= array( CSS_LABELAUTY, CSS_SELECTIZE, CSS_DATATABLE_MATERIAL );
			$resources['load_js'] 		= array( JS_DATATABLE, JS_DATATABLE_MATERIAL, $this->module_js );

			$table_options 				= $this->data_table_opt_gen;

			$resources['datatable']		= $table_options;

			$resources['loaded_init'] 	= array(
				"refresh_new_datatable_params('".$this->table_id."');",
			);

			$resources['load_materialize_modal'] = array(
				'modal_announcement' => array (
					'title' 		=> "Announcement",
					'size'			=> "md",
					'module' 		=> CORE_ANNOUNCEMENTS,
					'method'		=> 'modal_announcement',
					'controller'	=> __CLASS__,
					'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),
					'post' 			=> true
				),

			);

			$resources['load_delete']	= array(
				'announcement'	=> array(
					'delete_cntrl' 		=> 'Announcement',
					'delete_method'		=> 'delete',
					'delete_module'		=> CORE_ANNOUNCEMENTS
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

		$data['add_per'] 			= $this->add_per;
		$data['table_id']			= $this->table_id;

		$this->template->load('announcement', $data, $resources);

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
			$params 			= get_params();

			$result 			= $this->am->get_list( $this->fields, $this->filter, $this->order, $params );

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

					$salt 		= gen_salt();

					$id_detail 	= array(
						$r['announcement_id'],
						$salt
					);

					$token_edit 	= $this->generate_salt_token_arr( $id_detail, ACTION_EDIT, $this->module );

					$token_view 	= $this->generate_salt_token_arr( $id_detail, ACTION_VIEW, $this->module );

					$token_delete 	= $this->generate_salt_token_arr( $id_detail, ACTION_DELETE, $this->module );

					$post_edit_json = json_encode( array(
						'announcement_id'	=> base64_url_encode($r['announcement_id']),
						'salt'			=> $salt,
						'token'			=> $token_edit['token_concat'],
						'action'		=> ACTION_EDIT,
						'module'		=> base64_url_encode($this->module)
					) );

					$post_view_json = json_encode( array(
						'announcement_id'	=> base64_url_encode($r['announcement_id']),
						'salt'			=> $salt,
						'token'			=> $token_view['token_concat'],
						'action'		=> ACTION_VIEW,
						'module'		=> base64_url_encode($this->module)
					) );

					$post_delete_json = json_encode( array(
						'announcement_id'	=> base64_url_encode($r['announcement_id']),
						'salt'			=> $salt,
						'token'			=> $token_delete['token_concat'],
						'action'		=> ACTION_DELETE,
						'module'		=> base64_url_encode($this->module)
					) );

					$actions 			= '';

					$actions 			.= "<div class='table-actions'>";

					if( $this->view_per )
					{
						$actions .= "<a href='#modal_announcement' data-modal_post='".$post_view_json."' class='modal_announcement_trigger tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_announcement_init('', this, 'View Announcement')\"><i class='grey-text material-icons'>visibility</i></a>";
					}

					if( $this->edit_per )
					{
						$actions .= "<a href='#modal_announcement' data-modal_post='".$post_edit_json."' class='modal_sample_trigger tooltipped' data-tooltip='Edit' data-position='bottom' data-delay='50' onclick=\"modal_announcement_init('', this)\"><i class='grey-text material-icons'>edit</i></a>";
					}


					if( $this->delete_per )
					{
						$delete_action = 'content_announcement_delete("Announcement", "", "", this)';

						$actions .= "<a href='javascript:;' data-delete_post='".$post_delete_json."' onclick='".$delete_action."' class='tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
					}

					$actions .= "</div>";

					$counter++;


					if($cnt_result == $counter)
					{
						$resources['preload_modal'] = array("modal_announcement");
						// $resources['loaded_init'] = array("selectize_init();");
						$actions .= $this->load_resources->get_resource($resources, TRUE);
					}

					$rows[] 		 	= array(
						html_entity_decode( $r['description'] ),
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


	private function _filter( array $orig_params )
	{
		$par 			= $this->set_filter( $orig_params )
							->filter_number('announcement_id', TRUE)
							->filter_string('module', TRUE)
							;

		$params 		= $par->filter();
		
		return $params;

	}

	public function modal_announcement()
	{
		$data 					= array();
		$resources 				= array();	

		$disabled 				= '';

		$details 				= array();

		try
		{
			$orig_params 		= get_params();
			
			$params 			= $this->_filter( $orig_params );

			$client_side 		= $this->_validate( array(), TRUE );

			if( ISSET( $params['announcement_id'] ) AND !EMPTY( $params['announcement_id'] ) )
			{
				check_salt($params['announcement_id'], $params['salt'], $params['token'], $params['action'], $params['module']);

				$details 			= $this->am->get_specific_announcement($params['announcement_id']);
			}


			$resources['load_css'] 	= array(CSS_LABELAUTY);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_EDITOR, $this->module_js);

			$resources['loaded_init']	= array( $client_side['customJS'], 'Announcement.modal_init();');

			$data['disabled'] 			= $disabled;
			$data['orig_params'] 	= $orig_params;
			$data['params']			= $params;
			$data['details']		= $details;
			$data['client_side']	= $client_side;

			$this->load->view("modals/announcement",$data);
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


	public function save()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= ( ISSET( $orig_params['announcement_id'] ) AND !EMPTY( $orig_params['announcement_id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['announcement_id'] ) AND !EMPTY( $orig_params['announcement_id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$ins_val 				= array();

		$add  					= FALSE;

		$main_where 			= array();

		$announcement_id 		= NULL;

		try
		{
			$params 		 	= $this->_filter( $orig_params );

			$permission 		= ( !$update ) ? $this->add_per : $this->edit_per;
			$per_msg 			= ( !$update ) ? $this->lang->line( 'err_unauthorized_add' ) : $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}

			$post 				= $this->input->post() ? $this->input->post() : array();

			$ins_val['description']	= htmlentities( $post['announcement'] );

			$this->_validate( $params );

			SYSAD_Model::beginTransaction();

			if( !$update )
			{
				$ins_val['created_by']		= $this->session->user_id;
				$ins_val['created_date']	= $this->date_now;

				$add 				= TRUE;

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$announcement_id 			= $this->am->insert_announcement( $ins_val );

				$main_where 				= array(
					'announcement_id'		=> $announcement_id
				);

				$curr_detail[] 				= $this->am->get_details_for_audit( SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS,
													$main_where
												 );

				$users 				= $this->am->get_users();

				$us_val 		= $this->process_user_ann( $users, $announcement_id );

				if( !EMPTY( $us_val ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();				

					$this->am->insert_user_announcement( $us_val );

					$curr_detail[] 		= $this->am->get_details_for_audit( SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS,
													$main_where
												 );
				}
			}
			else
			{
				$ins_val['modified_by']		= $this->session->user_id;
				$ins_val['modified_date']	= $this->date_now;

				check_salt( $params['announcement_id'], $params['salt'], $params['token'], $params['action'], $this->module );

				$announcement_id 			= $params['announcement_id'];

				$main_where 				= array(
					'announcement_id'		=> $announcement_id
				);

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS;
				$audit_action[] 	= AUDIT_UPDATE;
				$prev_detail[]  	= $this->am->get_details_for_audit( SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS,
													$main_where
												 );

				$this->am->update_announcement( $ins_val, $main_where );

				$curr_detail[] 		= $this->am->get_details_for_audit( SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS,
													$main_where
												 );
			}

			$audit_name 				= 'Announcement';

			$audit_activity 			= ( !$update ) ? sprintf( $this->lang->line('audit_trail_add'), $audit_name) : sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );


			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					=$this->lang->line( 'data_saved' );

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
			'status' 				=> $status,
			'datatable_options'		=> $this->data_table_opt_gen,
			'datatable_id'			=> $this->table_id
		);

		echo json_encode( $response );

	}

	protected function process_user_ann( array $users, $announcement_id )
	{
		$val 	= array();

		try
		{
			if( !EMPTY( $users ) )
			{
				foreach( $users as $key => $us )
				{
					$val[$key]['user_id']			= $us['user_id'];
					$val[$key]['announcement_id']	= $announcement_id;
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $val;
	}

	private function _validate( array $params = array(), $forJs = FALSE )
    {
    	try
    	{
    		$v 						= $this->core_v;

    		$v 	
    			->required(NULL, '#client_announcement')
    			->maxlength(60000, '#client_announcement')->sometimes('sometimes', NULL, $forJs)
    			->check('announcement', $params);

    		if( $forJs )
			{
				return $v->getClientSide();
			}
			else
			{
				$v->assert(FALSE);
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
    }

    public function delete()
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
			$params 			= $this->_filter( $orig_params );

			check_salt( $params['announcement_id'], $params['salt'], $params['token'], $params['action'], $this->module );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 					= array(
				'announcement_id'			=> $params['announcement_id']		
			);

			SYSAD_Model::beginTransaction();

			$prev_us_ann_data 					= $this->am->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS, $main_where
			);

			if( !EMPTY( $prev_us_ann_data ) )
			{
				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $prev_us_ann_data;

				$this->am->delete_user_announcement( $main_where );

				$curr_detail[] 				= array();
			}

			$audit_schema[]				= DB_CORE;
			$audit_table[] 				= SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS;
			$audit_action[] 			= AUDIT_DELETE;
			$prev_detail[] 				= $this->am->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS, $main_where
			);

			$this->am->delete_announcement( $main_where );

			$curr_detail[] 				= array();

			if( !EMPTY( $audit_schema ) )
			{

				$audit_name 				= 'Announcement';

				$this->audit_trail->log_audit_trail( $audit_name, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
			}

			SYSAD_Model::commit();


			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_deleted' );

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
			'datatable_id' 			=> $this->table_id
		);

		echo json_encode( $response );

    }

}