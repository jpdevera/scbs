<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use AJD_validation\Contracts\Abstract_common;

class Statements extends SYSAD_Controller 
{
	const MAXFILESIZE 			= '89128960 ';

	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	private $table_id 			= 'table_statements';
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

		$this->module 			= MODULE_STATEMENTS;

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
		
		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_MAINTENANCE."/statements";

		$this->path 				= CORE_MAINTENANCE.'/Statements'.'/get_list/';	

		$this->data_table_opt_gen 	= array(
			'table_id'	 		=> $this->table_id,
			'path' 				=> $this->path,
			'advanced_filter' 	=> true,
			'with_search'		=> true
		);

		$this->load->model(CORE_MAINTENANCE.'/Statements_model', 'sm');

		$this->fields 				= array(
			'a.statement_title', 'a.statement_id', 'c.statement_module_type', 'd.statement_type',
			"
				CASE
					WHEN a.statement_type_id = ".STATEMENT_TYPE_TEXT." THEN a.statement
					WHEN a.statement_type_id = ".STATEMENT_TYPE_LINK." THEN a.statement_link
					WHEN a.statement_type_id = ".STATEMENT_TYPE_FILE." THEN GROUP_CONCAT(b.orig_file_name SEPARATOR '<br/>')
				END as statements
			",
			'a.built_in'
		);

		$this->filter 				= array(
			'a.statement_title convert_to statement_title', 
			"
				CASE
					WHEN a.statement_type_id = ".STATEMENT_TYPE_TEXT." THEN a.statement
					WHEN a.statement_type_id = ".STATEMENT_TYPE_LINK." THEN a.statement_link
					WHEN a.statement_type_id = ".STATEMENT_TYPE_FILE." THEN b.orig_file_name
				END convert_to statements
			",
			'c.statement_module_type convert_to statement_module_type',
			'd.statement_type convert_to statement_type',
		);

		$this->order				= array(
			'a.statement_title', 'statements', 'c.statement_module_type', 'd.statement_type'
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
				'modal_statements' => array (
					'title' 		=> "Statement",
					'size'			=> "md",
					'module' 		=> CORE_MAINTENANCE,
					'method'		=> 'modal_statements',
					'controller'	=> __CLASS__,
					'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),
					'post' 			=> true
				),

			);

			$resources['load_delete']	= array(
				'statements'	=> array(
					'delete_cntrl' 		=> 'Statements',
					'delete_method'		=> 'delete',
					'delete_module'		=> CORE_MAINTENANCE
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

		$this->template->load('statements', $data, $resources);

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

			$result 			= $this->sm->get_list( $this->fields, $this->filter, $this->order, $params );

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
						$r['statement_id'],
						$salt
					);

					$token_edit 	= $this->generate_salt_token_arr( $id_detail, ACTION_EDIT, $this->module );

					$token_view 	= $this->generate_salt_token_arr( $id_detail, ACTION_VIEW, $this->module );

					$token_delete 	= $this->generate_salt_token_arr( $id_detail, ACTION_DELETE, $this->module );

					$post_edit_json = json_encode( array(
						'statement_id'	=> base64_url_encode($r['statement_id']),
						'salt'			=> $salt,
						'token'			=> $token_edit['token_concat'],
						'action'		=> ACTION_EDIT,
						'module'		=> base64_url_encode($this->module)
					) );

					$post_view_json = json_encode( array(
						'statement_id'	=> base64_url_encode($r['statement_id']),
						'salt'			=> $salt,
						'token'			=> $token_view['token_concat'],
						'action'		=> ACTION_VIEW,
						'module'		=> base64_url_encode($this->module)
					) );

					$post_delete_json = json_encode( array(
						'statement_id'	=> base64_url_encode($r['statement_id']),
						'salt'			=> $salt,
						'token'			=> $token_delete['token_concat'],
						'action'		=> ACTION_DELETE,
						'module'		=> base64_url_encode($this->module)
					) );

					$actions 			= '';

					$actions 			.= "<div class='table-actions'>";

					if( $this->view_per )
					{
						$actions .= "<a href='#modal_statements' data-modal_post='".$post_view_json."' class='modal_statements_trigger tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_statements_init('', this, 'View Statement')\"><i class='grey-text material-icons'>visibility</i></a>";
					}

					if( $this->edit_per )
					{
						$actions .= "<a href='#modal_statements' data-modal_post='".$post_edit_json."' class='modal_statements_trigger tooltipped' data-tooltip='Edit' data-position='bottom' data-delay='50' onclick=\"modal_statements_init('', this)\"><i class='grey-text material-icons'>edit</i></a>";
					}

					$delete_class	= '';
					$delete_tooltip	= 'Delete';

					if( $this->delete_per )
					{
						$delete_action = 'content_statements_delete("Statement", "", "", this)';

						if( ! EMPTY($r['built_in']) AND $r['built_in'] == ENUM_YES )
						{
							$delete_action	= '';
							$delete_class	= 'disabled';
							$delete_tooltip	= 'You are not allowed to delete this record';
						}

						$actions .= "<a href='javascript:;' data-delete_post='".$post_delete_json."' onclick='".$delete_action."' class='tooltipped ".$delete_class." ' data-tooltip='".$delete_tooltip."' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
					}

					$actions .= "</div>";

					$counter++;


					if($cnt_result == $counter)
					{
						$resources['preload_modal'] = array("modal_statements");
						// $resources['loaded_init'] = array("selectize_init();");
						$actions .= $this->load_resources->get_resource($resources, TRUE);
					}

					$rows[] 		 	= array(
						$r['statement_title'],
						html_entity_decode( $r['statements'] ),
						$r['statement_module_type'],
						$r['statement_type'],
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
							->filter_number('statement_id', TRUE)
							->filter_string('module', TRUE)
							->filter_number('statement_module_type')
							->filter_number('statement_type')
							->filter_url('statement_link')
							->filter_string('statement_subject')
							;

		$params 		= $par->filter();
		
		return $params;

	}

	private function _validate( array $params = array(), $forJs = FALSE )
    {
    	try
    	{
    		$v 						= $this->core_v;

    		$statement_id 			= ( ISSET( $params['statement_id'] ) ) ? $params['statement_id'] : NULL;

    		$v 	
    			->required(NULL, '#client_statement_code')
    			->maxlength(50, '#client_statement_code')->sometimes('sometimes', NULL, $forJs)
    			->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_STATEMENTS.'|primary_id=statement_code|exclude_id=statement_id|exclude_value='.$statement_id)
    			->check('statement_code', $params);

    		$v 	
    			->required(NULL, '#client_statement_title')
    			->maxlength(255, '#client_statement_title')->sometimes('sometimes', NULL, $forJs)
    			->check('statement_title', $params);

    		$v 
    			->required(NULL, '#client_statement_module_type')
    			->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_STATEMENT_MODULE_TYPES.'|primary_id=statement_module_type_id')
    			->check('statement_module_type', $params);

    		$v 
    			->required(NULL, '#client_statement_type')
    			->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_STATEMENT_TYPES.'|primary_id=statement_type_id')
    			->check('statement_type', $params);

    		$v 
    			->required()
    				->sometimes(function() use(&$params)
    				{
    					return ( ISSET($params['statement_type']) AND $params['statement_type'] == STATEMENT_TYPE_TEXT );
    				})
    			->maxlength(60000, '#client_statement')->sometimes('sometimes', NULL, $forJs)
    			->check('statement', $params);

    		$v 
    			->required()
    				->sometimes(function() use(&$params)
    				{
    					return ( ISSET($params['statement_module_type']) AND $params['statement_module_type'] == STATEMENT_MODULE_EMAIL_TEMPLATE );
    				})
    			->maxlength(60000, '#client_statement_subject')->sometimes('sometimes', NULL, $forJs)
    			->check('statement_subject', $params);

    		$v 
    			->required()
    				->sometimes(function() use(&$params)
    				{
    					return ( ISSET($params['statement_type']) AND $params['statement_type'] == STATEMENT_TYPE_LINK );
    				})
    			->maxlength(255, '#client_statement_link')->sometimes('sometimes', NULL, $forJs)
    			->url(array(Abstract_common::URL_VERY_BASIC))->sometimes('sometimes', NULL, $forJs)
    			->check('statement_link', $params);

    		if( !EMPTY( $statement_id ) )
    		{
    			$details 			= $this->sm->get_specific_statement($params['statement_id']);

    			if( !EMPTY( $details ) AND !EMPTY( $details['statement_tokens'] ) )
    			{
    				$statement_tokens 	= explode(',', $details['statement_tokens']);

    				if( ISSET( $params['statement'] ) AND !EMPTY( $params['statement'] ) )
    				{
    					$check_match 	= preg_match_all('/\{(.*?)\}/', $params['statement'], $match);

    					if( ISSET( $match[1] ) )
						{
							foreach( $match[1] as $key => $place_name )
							{
								if( !in_array($place_name, $statement_tokens, true) )
								{
									throw new Exception('Invalid token ('.$place_name.').');
								}
							}
						}

    				}
    			}
    		}

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

	public function modal_statements()
	{
		$data 					= array();
		$resources 				= array();	

		$disabled 				= '';

		$details 				= array();
		$statement_module_type 	= array();
		$statement_type 		= array();

		$dis_upl 				= false;

		try
		{
			$orig_params 		= get_params();
			
			$params 			= $this->_filter( $orig_params );

			$client_side 		= $this->_validate( array(), TRUE );

			if( ISSET( $params['statement_id'] ) AND !EMPTY( $params['statement_id'] ) )
			{
				check_salt($params['statement_id'], $params['salt'], $params['token'], $params['action'], $params['module']);

				$details 			= $this->sm->get_specific_statement($params['statement_id']);
			}
			else
			{
				$orig_params 		= array(
					'statement_id'	=> '',
					'salt'			=> '',
					'token' 		=> '',
					'action'		=> '',
					'module'		=> ''
				);

				$params 			= $orig_params;
			}

			if( ISSET($params['action']) AND $params['action'] == ACTION_VIEW )
			{
				$disabled 		= 'disabled';
				$dis_upl 		= true;
			}

			$statement_module_type 	= $this->sm->get_statement_module_types();
			$statement_type 		= $this->sm->get_statement_types();

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_SELECTIZE);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_SELECTIZE, JS_EDITOR, $this->module_js);

			$resources['upload']	= array(
				'statements_file'			=> array(
					'path' 				=> PATH_STATEMENTS, 
					'allowed_types' 	=> 'pdf', 
					// 'show_progress' => 1,
					'show_preview'			=> 1,
					'drag_drop' 			=> false,
					'default_img_preview'	=> 'image_preview.png',
					'max_file' 				=> 5,
					'multiple' 				=> 1,
					'successCallback'		=> "Statements.successCallback(files,data,xhr,pd);",
					// 'max_file_size'			=> '13107200',
					'auto_submit'			=> false,
					'max_file_size'			=> self::MAXFILESIZE,
					'multiple_obj'			=> true,
					'show_download'			=> true,
					'delete_path'			=> CORE_MAINTENANCE.'/Statements',
					'delete_path_method'	=> 'delete_uploads',
					'delete_form'			=> '#form_modal_statements',
					'disable'				=> $dis_upl,
					'deleteCallback' 		=> "refresh_ajax_datatable('".$this->table_id."');"
				)
			);
			
			$resources['loaded_init']	= array( $client_side['customJS'], 'Statements.modal_init();', 'Statements.save();');

			$data['disabled'] 			= $disabled;
			$data['orig_params'] 	= $orig_params;
			$data['params']			= $params;
			$data['details']		= $details;
			$data['client_side']	= $client_side;
			$data['statement_module_type']	= $statement_module_type;
			$data['statement_type']	= $statement_type;

			$this->load->view("modals/statement",$data);
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

		$update 				= ( ISSET( $orig_params['statement_id'] ) AND !EMPTY( $orig_params['statement_id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['statement_id'] ) AND !EMPTY( $orig_params['statement_id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$ins_val 				= array();

		$add  					= FALSE;

		$main_where 			= array();

		$statement_id 			= NULL;
		$new_salt 				= NULL;
		$new_token 				= NULL;
		$statement_enc 			= NULL;
		$module_enc 			= NULL;

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

			$params['statement'] = $post['statement'];

			$ins_val['statement_code']				= $params['statement_code'];
			$ins_val['statement_title']				= $params['statement_title'];
			$ins_val['statement_module_type_id']	= $params['statement_module_type'];
			$ins_val['statement_type_id']			= $params['statement_type'];
			$ins_val['statement_subject'] 			= NULL;

			if( ISSET($params['statement_link']) AND !EMPTY( $params['statement_link'] ) )
			{
				$ins_val['statement_link'] 	= $params['statement_link'];
			}

			if( ISSET($params['statement_subject']) AND !EMPTY( $params['statement_subject'] ) )
			{
				$ins_val['statement_subject'] 	= $params['statement_subject'];
			}

			if( ISSET($post['statement']) AND !EMPTY( $post['statement'] ) )
			{
				$ins_val['statement']	= htmlentities( $post['statement'] );
			}

			switch( $params['statement_type'] )
			{
				case STATEMENT_TYPE_TEXT :
					$ins_val['statement_link'] 	= NULL;
				break;
				case STATEMENT_TYPE_LINK :
					$ins_val['statement'] 		= NULL;
				break;
				case STATEMENT_TYPE_FILE :
					$ins_val['statement'] 		= NULL;
					$ins_val['statement_link'] 	= NULL;
				break;
			}

			$this->_validate( $params );

			SYSAD_Model::beginTransaction();

			if( !$update )
			{
				$ins_val['created_by']		= $this->session->user_id;
				$ins_val['created_date']	= $this->date_now;

				$add 				= TRUE;

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_STATEMENTS;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$statement_id 			= $this->sm->insert_statements( $ins_val );

				$main_where 				= array(
					'statement_id'		=> $statement_id
				);

				$curr_detail[] 				= $this->sm->get_details_for_audit( SYSAD_Model::CORE_TABLE_STATEMENTS,
													$main_where
												 );
			}
			else
			{
				$ins_val['modified_by']		= $this->session->user_id;
				$ins_val['modified_date']	= $this->date_now;

				check_salt( $params['statement_id'], $params['salt'], $params['token'], $params['action'], $this->module );

				$statement_id 			= $params['statement_id'];

				$main_where 				= array(
					'statement_id'		=> $statement_id
				);

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ANNOUNCEMENTS;
				$audit_action[] 	= AUDIT_UPDATE;
				$prev_detail[]  	= $this->sm->get_details_for_audit( SYSAD_Model::CORE_TABLE_STATEMENTS,
													$main_where
												 );

				$this->sm->update_statements( $ins_val, $main_where );

				$curr_detail[] 		= $this->sm->get_details_for_audit( SYSAD_Model::CORE_TABLE_STATEMENTS,
													$main_where
												 );
			}

			$new_salt 	= gen_salt();

			$id_detail 	= array(
				$statement_id,
				$new_salt
			);

			$token_edit 	= $this->generate_salt_token_arr( $id_detail, ACTION_EDIT, $this->module );

			$new_token 		= $token_edit['token_concat'];
			$statement_enc  = base64_url_encode($statement_id);
			$module_enc 	= base64_url_encode($this->module);

			$audit_name 				= 'Statement';

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
			'datatable_id'			=> $this->table_id,
			'statement_id'			=> $statement_id,
			'statement_enc' 		=> $statement_enc,
			'new_salt'				=> $new_salt,
			'new_token'				=> $new_token,
			'module_enc'			=> $module_enc,
			'action'				=> ACTION_EDIT
		);

		echo json_encode( $response );

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

		$prev_file 				= array();

		try
		{
			$params 			= $this->_filter( $orig_params );

			check_salt( $params['statement_id'], $params['salt'], $params['token'], $params['action'], $this->module );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 					= array(
				'statement_id'			=> $params['statement_id']		
			);

			SYSAD_Model::beginTransaction();

			$prev_statement_upload 			= $this->sm->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS, $main_where
			);

			$prev_us_ann_data 					= $this->sm->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_STATEMENTS, $main_where
			);

			$prev_stat_tok_data 					= $this->sm->get_details_for_audit(
				SYSAD_Model::CORE_TABLE_STATEMENT_TOKENS, $main_where
			);

			if( !EMPTY( $prev_statement_upload ) )
			{
				$prev_file 				= array_column($prev_statement_upload, 'sys_file_name');

				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $prev_statement_upload;

				$this->sm->delete_statement_uploads( $main_where );

				$curr_detail[] 				= array();
			}

			if( !EMPTY( $prev_stat_tok_data ) )
			{
				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_TABLE_STATEMENT_TOKENS;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $prev_stat_tok_data;

				$this->sm->delete_helper( SYSAD_Model::CORE_TABLE_STATEMENT_TOKENS, $main_where );

				$curr_detail[] 				= array();
			}

			if( !EMPTY( $prev_us_ann_data ) )
			{
				$audit_schema[]				= DB_CORE;
				$audit_table[] 				= SYSAD_Model::CORE_TABLE_STATEMENTS;
				$audit_action[] 			= AUDIT_DELETE;
				$prev_detail[] 				= $prev_us_ann_data;

				$this->sm->delete_statements( $main_where );

				$curr_detail[] 				= array();
			}

			if( !EMPTY( $audit_schema ) )
			{

				$audit_name 				= 'Statement';

				$this->audit_trail->log_audit_trail( $audit_name, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

				if( !EMPTY( $prev_file ) )
				{
					$file_db_where 	= array(
						'sys_file_name'	=> array('IN', $prev_file)
					);

					$this->sm->delete_helper(SYSAD_Model::CORE_TABLE_FILE_DB_STORAGE, $file_db_where);
				}				
			}

			SYSAD_Model::commit();

			if( !EMPTY( $prev_file ) )
			{
				$path 		= PATH_STATEMENTS;

				foreach( $prev_file as $att )
				{
					$real_path 	= $path.$att;
					$real_path 	= str_replace(array('\\', '/'), array(DS, DS), $real_path);

					if( file_exists( $real_path ) )
					{
						$this->unlink_attachment( $real_path );
					}
				}
			}

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

    public function delete_uploads()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();

		$orig_params 			= get_params();

		$msg 					= '';
		$flag 					= 0;
		$status 				= ERROR;

		$main_where 			= array();

		try
		{
			$params 		 	= $this->_filter( $orig_params );

			check_salt( $params['statement_id'], $params['salt'], $params['token'], $params['action'], $this->module );

			$delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );

			if( !$delete_per )
			{
				throw new Exception( $this->lang->line( 'err_unauthorized_delete' ) );
			}

			$main_where 		= array(
				'sys_file_name'	=> $params['name']
			);

			SYSAD_Model::beginTransaction();

			$prev_file 			= $this->sm->get_details_for_audit( SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS, $main_where );

			if( !EMPTY( $prev_file ) )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 		= SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS;
				$audit_action[] 	= AUDIT_DELETE;

				$prev_detail[] 		= $prev_file;

				$this->sm->delete_statement_uploads( 
					$main_where
				);

				$curr_detail[] 		= array();

				$audit_name 				= 'Statement Uploads';

				$audit_activity 		= sprintf( $this->lang->line('audit_trail_delete'), $audit_name);

				$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
			}

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
			"status" 				=> $status
		);

		echo json_encode( $response );
	}

	protected function process_files(array $params)
	{

		$arr 	= array();

		$cnt 			= 0;

		if( ISSET( $params['statements_file'] ) AND !EMPTY( $params['statements_file'] ) )
		{
			foreach( $params['statements_file'] as $key => $file )
			{
				$origFile 							= '';

				if( ISSET( $params['statements_file_orig_filename'][$key] ) 
					AND !EMPTY( $params['statements_file_orig_filename'][$key] ) 
				)
				{
					$origFile 						= $params['statements_file_orig_filename'][$key];
				}


				$arr[$cnt]['statement_id']			= $params['statement_id'];
				$arr[$cnt]['sys_file_name']			= $file;
				$arr[$cnt]['orig_file_name']		= $origFile;
				$arr[$cnt]['created_by']			= $this->session->user_id;
				$arr[$cnt]['created_date']			= $this->date_now;
				$arr[$cnt]['modified_by']			= $this->session->user_id;
				$arr[$cnt]['modified_date']			= $this->date_now;

				$cnt++;
			}
		}

		return array( 
			'arr'	=> $arr
		);
	}

	public function save_uploads()
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

		try
		{
			$params 			= $this->_filter( $orig_params );

			check_salt( $params['statement_id'], $params['salt'], $params['token'], $params['action'], $params['module'] );

			SYSAD_Model::beginTransaction();

			$file_val 				= $this->process_files( $params );

			$main_where 		= array(
				'statement_id'		=> $params['statement_id']
			);

			$audit_schema[] 	= DB_CORE;
			$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS;
			$audit_action[] 	= AUDIT_DELETE;
			$prev_detail[]  	= $this->sm->get_details_for_audit( SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS,
									$main_where
								 );

			$this->sm->delete_statement_uploads( $main_where );

			$curr_detail[] 		= array();
			
			if( !EMPTY( $file_val['arr'] ) )
			{
				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS;
				$audit_action[] 	= AUDIT_INSERT;
				$prev_detail[]  	= array();

				$this->sm->insert_statement_uploads( $file_val['arr'] );

				$curr_detail[] 		= $this->sm->get_details_for_audit( SYSAD_Model::CORE_TABLE_STATEMENT_UPLOADS,
										$main_where
									 );

				if( !EMPTY( $curr_detail ) )
				{

					$audit_name 				= 'Statements Uploads';

					$audit_activity 			= sprintf( $this->lang->line('audit_trail_add'), $audit_name);

					$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
				}
			}

			SYSAD_Model::commit();

			$flag 					= 1;
			
			$status 				= SUCCESS;
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
			'datatable_id'			=> $this->table_id
		);

		echo json_encode( $response );
	}
}