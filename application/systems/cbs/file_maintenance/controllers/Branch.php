<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Branch extends CBS_Controller
{
	private $module_code;
	private $module_folder;
	private $controller;
	private $module_js;

	private $path;
	private $table_id;

	public function __construct()
	{
		parent::__construct();
		$this->module_code  = MODULE_FILE_MAINTENANCE_BRANCH;
		$this->module_folder= FOLDER_FILE_MAINTENANCE;
		$this->controller 	= strtolower(__CLASS__);
		$this->path 		= $this->module_folder.'/'.$this->controller.'/get_data_list';
		$this->module_js    = HMVC_FOLDER .'/'. SYSTEM_CBS .'/'.  $this->module_folder.'/'.$this->controller;

		// init datatable
		$this->table_options 	= array(
			'table_id' 			=> 'tbl_data_list', 
			'path' 				=> $this->path, 
			'advanced_filter'	=> true,
			'with_search'		=> true,
			'no_export' 	=> true,
			'no_colvis'		=> true,
			'no_bulk_delte'	=> true,
		);

		try 
		{
		 	$encrypt_module = $this->encrypt($this->module_code);	

        	// check module permissions
			$this->permission_module = $this->permission->check_permission($this->module_code, ACTION_VIEW);
			$this->permission_delete = $this->permission->check_permission($this->module_code, ACTION_DELETE);
			$this->permission_view = $this->permission->check_permission($this->module_code, ACTION_VIEW);
			$this->permission_edit = $this->permission->check_permission($this->module_code, ACTION_EDIT);
			$this->permission_add = $this->permission->check_permission($this->module_code, ACTION_ADD);

			$this->is_construct_error = FALSE;
			$this->construct_error_msg = NULL;
				
		}
      	catch (PDOException $e)
      	{
           	$this->is_construct_error	= TRUE;
			$this->construct_error_msg	= $this->get_user_message($e);
      	}
      	catch (Exception $e)
      	{
           	$this->is_construct_error	= TRUE;
			$this->construct_error_msg	= $e->getMessage();
      	}

		// set security variables
		$this->security_action_delete = $encrypt_module . $this->encrypt(ACTION_DELETE);
		$this->security_action_view = $encrypt_module . $this->encrypt(ACTION_VIEW);
		$this->security_action_edit = $encrypt_module . $this->encrypt(ACTION_EDIT);
		$this->security_action_add = $encrypt_module . $this->encrypt(ACTION_ADD);

		// Load Model
		$this->load->model('branch_model', 'model');
	}

	public function index()
	{
		try {
			$data 			= array();
			$resources 		= array();

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATATABLE_BUTTONS, CSS_CALENDAR);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_BUTTON_EXPORT_EXTENSION, JS_CALENDAR, $this->module_js);

			//Load modal
			$resources['load_materialize_modal'] = array(
				'modal_add' => array(
					'size' 	=> 'md-w lg-h',
					'title' => 'Create Branch',
					'modal_style' => 'modal-icon',
					'modal_header_icon' => 'library_add',
					'module' => $this->module_folder,
					'method' => 'process_modal',
					'controller' => strtolower(__CLASS__),
					'modal_footer' => TRUE
		        ),
		        'modal_edit' => array(
					'size' 		=> 'md-w lg-h',
					'title'		=> 'Edit Branch',
					'modal_style'=> 'modal-icon',
					'modal_header_icon' => 'edit',
					'module'		=> $this->module_folder,
					'method' => 'process_modal',
					'controller' 	=> strtolower(__CLASS__)
				),
			 	'modal_view' => array(
					'size' 		=> 'md-w lg-h',
					'title'		=> 'View Branch',
					'modal_style'=> 'modal-icon',
					'modal_header_icon' => 'remove_red_eye',
					'module'		=> $this->module_folder,
					'method' => 'process_modal',
					'controller' 	=> strtolower(__CLASS__),
					'modal_footer' => FALSE
				)
			);

			$resources['loaded_init'] = array(
				"Branch.init();",
				"Branch.remove();"
			);

			$encrypt_id       = $this->encrypt(0);
			$salt             = gen_salt();
			$token            = in_salt($encrypt_id . '/' . $this->security_action_add, $salt);
			$data['security'] 		= $encrypt_id . '/' . $salt . '/' . $token . '/' . $this->security_action_add;

			$resources['datatable'] = $this->table_options;


			$this->template->load($this->controller, $data, $resources);
		} catch (PDOException $e) {
			$msg = $this->get_user_message($e);
			$this->error_index($m);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);

			$this->error_index($msg);
		}
	}

	public function get_data_list()
	{

		$flag 				= 0;
		$total_records		= 0;
		$display_records 	= 0;
		$table_data 		= array();
		
		try 
		{			
			
			$params	= get_params();

			$total_records		= $this->model->get_data_list(NULL);
			$records_info 		= $this->model->get_data_list($params);
			
			$records			= $records_info['records'];
			$display_records	= $records_info['display_records'];
			$primary_key 		= 'branch_id';

			foreach($records as $record)
			{
				$hash_id	= $this->encrypt($record[$primary_key]);
				$salt		= gen_salt();
				$actions	= "";
				if( $this->permission_view )
				{

					$token	  = in_salt($hash_id . '/' . $this->security_action_view, $salt);
					$url   	  = $hash_id . '/' . $salt . '/' . $token . '/' . $this->security_action_view;
					

					$actions.= "<a href='#modal_view' onclick=\"modal_view_init('".$url."','','View Branch')\"><i class='grey-text material-icons tooltipped' data-position='bottom' data-delay='50' data-tooltip='Edit'>remove_red_eye</i></a>";
				}

				if( $this->permission_edit )
				{

					$token	= in_salt($hash_id . '/' . $this->security_action_edit, $salt);
					$url	= $hash_id . '/' . $salt . '/' . $token . '/' . $this->security_action_edit;

					$actions.= "<a href='#' onclick=\"content_form('".$url."','file_maintenance/branch/form')\"><i class='grey-text material-icons tooltipped' data-position='bottom' data-delay='50' data-tooltip='Edit'>mode_edit</i></a>";
				}

				if( $this->permission_delete )
				{
					$token	  = in_salt($hash_id . '/' . $this->security_action_delete, $salt);
					$url   	  = $hash_id . '/' . $salt . '/' . $token . '/' . $this->security_action_delete;

					$onclick = 'content_delete("holiday", "'.$url.'")';			
							
					$actions.= "<a href='javascript:;' onclick='".$onclick."' class='tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
				}


				$table_data[] = array(
					$record['brn_code'],
					$record['brn_name'],
					$record['institution_name'],
					$record['system_date'],
					"<div class='table-actions'>" . $actions . "</div>"
				);
			}				
			$flag	= 1;
			$msg	= "";
		}
		catch(PDOException $e)
		{
			 $msg = $e->getMessage();
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

 	public function process_action() 
	{
		try 
		{
			$messenger    = [];
			$status       = ERROR;
			$params       = get_params();
			$msg          = '';

			$validated_data = $this->_validate_form($params);
			$branch_id     = $params['decrypt_id'];
			$fields = $this->_construct_data($validated_data);

			CBS_Model::beginTransaction();
			switch($params['security_action'])
			{
				case $this->security_action_add:
					// insert branch
					$branch_id = $this->model->insert_data(CBS_Model::CBS_TABLE_BRANCHES, $fields['branches'], TRUE);
					if( $branch_id )
					{
						$fields['setups']['branch_id'] = $branch_id;
						$this->model->insert_data(CBS_Model::CBS_TABLE_BRANCH_SETUPS, $fields['setups'], TRUE);
						
						foreach ($fields['holidays'] as $key => $value)
						{
							$fields=[];
							$fields['branch_id']  = $branch_id;
							$fields['holiday_id'] = $value;
							$this->model->insert_data(CBS_Model::CBS_TABLE_BRANCH_HOLIDAYS, $fields, TRUE);
						}
						
					}

					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_CBS;
					$audit_table[] = CBS_Model::CBS_TABLE_BRANCHES;
					$prev_detail[] = array();
					$curr_detail[] = array($fields);
					$activity	   = "Branch {$branch_id} has been added in the system.";
				break;

				case $this->security_action_edit:
					if( empty($branch_id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));
						
					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					// update branch and get Previous Records
					$where 	  = array('branch_id'=>$branch_id);
					$previous = $this->model->select_data(array('*'), CBS_Model::CBS_TABLE_BRANCHES, FALSE, $where);

					if( $branch_id )
					{
						$this->model->update_data(CBS_Model::CBS_TABLE_BRANCHES, $fields['branches'], $where);
						$this->model->update_data(CBS_Model::CBS_TABLE_BRANCH_SETUPS, $fields['setups'], $where);

						$this->model->delete_data(CBS_Model::CBS_TABLE_BRANCH_HOLIDAYS, $where);
						foreach ($fields['holidays'] as $key => $value)
						{
							$fields=[];
							$fields['branch_id']  = $branch_id;
							$fields['holiday_id'] = $value;
							$this->model->insert_data(CBS_Model::CBS_TABLE_BRANCH_HOLIDAYS, $fields, TRUE);
						}
						
					}

					// Audit trail
					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_CBS;
					$audit_table[] = CBS_Model::CBS_TABLE_BRANCHES;
					$prev_detail[] = array($previous);
					$curr_detail[] = array($fields);
					$activity	   = "Branch {$branch_id} has been updated in the system.";
				break;

				default:
					throw new Exception($this->lang->line('err_unauthorized_access'));
				break;
			}

			// $this->audit_trail->log_audit_trail($activity, $this->module_code, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema);

			CBS_Model::commit();
			$status = SUCCESS;
		} 
		catch (PDOException $e) 
		{
			print_r($e);
			$msg = $this->get_user_message($e);
			CBS_Model::rollback();
		} 
		catch (Exception $e) 
		{
				print_r($e);
			$msg = $this->rlog_error($e, TRUE);
			CBS_Model::rollback();
		}

		echo json_encode(
			array(
				'status'	=> $status,
				'msg'		=> $msg,
				'datatable'	=> $this->table_options,
				'messenger'=>$messenger
			)
		);
	}

	private function _construct_data($validated_data)
	{
		try{
			
			$all_fields = [];
			if( !empty($validated_data) )
			{	
				$fields = [];
				$fields['brn_code']  			= $validated_data['brn_code'];
				$fields['brn_name']  			= $validated_data['brn_name'];
				$fields['institution_name']     = $validated_data['institution_name'];
				$fields['previous_system_date'] = $validated_data['previous_system_date'];
				$fields['system_date']   		= $validated_data['system_date'];
				$fields['file_name']     		= $validated_data['file_name'];
				$all_fields['branches'] 		= $fields;

				$fields = [];
				$fields['passbook_length'] 		= $validated_data['passbook_length'];
				$fields['passbook_row_length'] 	= $validated_data['passbook_row_length'];
				$fields['check_length'] 		= $validated_data['check_length'];
				$fields['no_of_booklet'] 		= $validated_data['no_of_booklet'];
				$fields['no_check_per_booklet'] = $validated_data['no_check_per_booklet'];
				$fields['transaction_serial'] 	= $validated_data['transaction_serial'];
				$fields['bsp_code'] 			= $validated_data['bsp_code'];
				$fields['frp_option'] 			= $validated_data['frp_option'];
				$fields['frp_path'] 			= $validated_data['frp_path'];
				$fields['backup_path'] 			= $validated_data['backup_path'];
				$fields['database_name'] 		= $validated_data['database_name'];
				$all_fields['setups']	    	= $fields;

				$fields = [];
				$all_fields['holidays']	    	= $validated_data['holiday_id'];

			}
			return $all_fields;
		}catch(Exception $e)
		{
			throw $e;
		}
	}

	public function form($hash_id, $salt, $token, $security_action)
	{
		try {
			$data 			= array();
			$resources 		= array();

			$data 		= $resources = array();
			$security 	= "";
			$id 		= $this->decrypt($hash_id);

			// | Load resources
			$resources['load_css']	= array(CSS_SELECTIZE ,CSS_LABELAUTY, CSS_DATETIMEPICKER, CSS_UPLOAD);
			$resources['load_js']	= array(JS_SELECTIZE, JS_LABELAUTY,JS_DATETIMEPICKER, JS_UPLOAD, $this->module_js);

			// | Loaded Init
			$resources['loaded_init'] = array(
				"Branch.init();",
				"Branch.save();"
			);

			switch($security_action)
			{
				case $this->security_action_add:
					if( ! empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_add === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$data['title'] = 'Create Branch';
				break;

				case $this->security_action_edit:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					// | Pass Data
					$holidays 		  = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_BRANCH_HOLIDAYS, TRUE, array('branch_id'=>$id));
					$data['holiday_id'] = array_column($holidays, 'holiday_id');
					$data['branch']	  = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_BRANCHES, FALSE, array('branch_id'=>$id));
					$data['setup'] 	  = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_BRANCH_SETUPS, FALSE, array('branch_id'=>$id));
					$data['title']    = 'Edit Branch';
				break;

				case $this->security_action_view:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_view === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$data['branch'] = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_BRANCHES, FALSE, array('branch_id'=>$id));
					$data['title'] = 'View Branch';
				break;

			}
			$data['holidays'] = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, TRUE);
			
			// | Upload file
			$max_file_size = 25;
	      	$data['allowed_types'] = LOGO_ALLOWED_TYPES;
	      	$data['max_file_size'] = (string) MAX_FILE_SIZE . ' MB';
	      	$data['supporting_documents'] = array();

	      	$resources['upload'] = array(
		        'attachment_document' => array(
		          'id' => 'attachment_document',
		          'path' => PATH_UPLOAD_BRANCH,
		          'max_file' => 1,
		          'multiple' => 0,
		          'drag_drop' => 1,
		          'multiple_obj' => true,
		          'show_preview' => 0,
		          'max_file_size' => (MAX_FILE_SIZE * 1024 * 1024),
		          'allowed_types' => $data['allowed_types'],
		          'show_progress' => 0,
		          'show_download' => 1
		        )
	      	);

	      	// | Load Tabs
			$data['tab_branch_info']  =  $this->load->view('tabs/branch_info', $data, TRUE);
			$data['tab_branch_setup'] =  $this->load->view('tabs/branch_setup', $data, TRUE);

			// | START: Regenerate security variables
			$salt			 = gen_salt();
			$token			 = in_salt($hash_id . '/' . $security_action, $salt);
			$security		 = $hash_id . '/' . $salt . '/' . $token . '/' . $security_action;
			$data['security']= $security;
			
			// | Load form
			$this->template->load('forms/'.$this->controller, $data, $resources);
		} catch (PDOException $e) {
			$msg = $this->get_user_message($e);
			$this->error_index($m);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);

			$this->error_index($msg);
		}

	}

	public function process_modal($hash_id, $salt, $token, $security_action)
	{
		try
		{

			$data 		= $resources = array();
			$security 	= "";
			$id 		= $this->decrypt($hash_id);

			// Load resources
			$resources['load_css']	= array(CSS_SELECTIZE ,CSS_LABELAUTY, CSS_DATETIMEPICKER, CSS_UPLOAD);
			$resources['load_js']	= array(JS_SELECTIZE, JS_LABELAUTY,JS_DATETIMEPICKER, JS_UPLOAD, $this->module_js);

			// Loaded Init
			$resources['loaded_init'] = array(
				"Branch.init();",
				"Branch.edit();"
			);

			switch($security_action)
			{
				case $this->security_action_add:
					if( ! empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_add === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$modal 			  = $this->controller;
				break;

				case $this->security_action_edit:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$data['branch'] = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_BRANCHES, FALSE, array('branch_id'=>$id));
					$modal 		   = $this->controller;
				break;

				case $this->security_action_view:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_view === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$data['branch'] = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_BRANCHES, FALSE, array('branch_id'=>$id));
					$modal 				= $this->controller.'_view';
				break;

				}

				// START: Regenerate security variables
				$salt		= gen_salt();
				$token		= in_salt($hash_id . '/' . $security_action, $salt);
				$security	= $hash_id . '/' . $salt . '/' . $token . '/' . $security_action;
				// END: Construct security variables
			}
			catch(PDOException $e)
			{
				$data['exception']	= $e;
				$data['message']	= $this->get_user_message($e);
			}
			catch(Exception $e)
			{
				print_r($e);
				$data['exception']	= $e;
				$data['message']	= $this->rlog_error($e, TRUE);
			}

		  	$max_file_size = 25;
	      	$data['allowed_types'] = LOGO_ALLOWED_TYPES;
	      	$data['max_file_size'] = (string) MAX_FILE_SIZE . ' MB';
	      	$data['supporting_documents'] = array();

	      	$resources['upload'] = array(
		        'attachment_document' => array(
		          'id' => 'attachment_document',
		          'path' => PATH_UPLOAD_BRANCH,
		          'max_file' => 1,
		          'multiple' => 0,
		          'drag_drop' => 1,
		          'multiple_obj' => true,
		          'show_preview' => 0,
		          'max_file_size' => (MAX_FILE_SIZE * 1024 * 1024),
		          'allowed_types' => $data['allowed_types'],
		          'show_progress' => 0,
		          'show_download' => 1
		        )
	      	);

			$data['security']			= $security;

			$this->load->view('modals/'.$modal, $data);
			$this->load_resources->get_resource($resources);
	}

	public function process_delete()
	{
		try
		{

			$params 		 = get_params();
			$params['security']	= $params['param_1'];

			$security 	 	 = explode("/",$params['param_1']);
			$hash_id 		 = $security[0];
			$salt     		 = $security[1];
			$token    		 = $security[2];
			$security_action = $security[3];

			CBS_Model::beginTransaction();
			$where 		= array('branch_id' => $this->decrypt($hash_id));
			$record     = $this->model->select_data(array('*'), CBS_Model::CBS_TABLE_BRANCHES, FALSE, $where);
			$branch_id    = isset($record['branch_id']) ? $record['branch_id'] : 0;

			switch($security_action)
			{
				case $this->security_action_delete:
					if( empty($branch_id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));

					if($this->permission_delete === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_delete'));

					$this->model->delete_data(CBS_Model::CBS_TABLE_BRANCHES, array('branch_id' => $branch_id));

					$msg  = $this->lang->line('data_deleted');
				break;

				default:
				throw new Exception($this->lang->line('err_unauthorized_access'));
				break;
			}

			$audit_action[]	= AUDIT_DELETE;
			$audit_table[]	= CBS_Model::CBS_TABLE_BRANCHES;
			$audit_schema[]	= DB_CBS;
			$prev_detail[]	= array($record);
			$curr_detail[]	= array();
			$activity		= $record["branch_id"] . " has been deleted in the system.";
			
			$this->audit_trail->log_audit_trail($activity, $this->module_code, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema);

			CBS_Model::commit();
			$msg 	= $this->lang->line('data_deleted');
			$status = SUCCESS;

		}
		catch(PDOException $e)
		{
			CBS_Model::rollback();
			$this->rlog_error($e);
			$status = ERROR;
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			CBS_Model::rollback();
			$this->rlog_error($e);
			$status = ERROR;
			$msg = $e->getMessage();
		}

		$info = array(
			"status"			=> $status,
			"msg"				=> $msg,
			"reload"			=> 'datatable',
			"datatable_options" => $this->table_options

		);

		echo json_encode($info);
	}


	private function _validate_form(&$params)
	{
		try
		{	
			// Check security variables
			$this->validate_security($params);
			$fields              = array();	

			// branches		
			$fields['brn_code']  			= "Branch Code";
			$fields['brn_name']  			= 'Branch Name';
			$fields['institution_name']     = 'Institution Name';
			$fields['previous_system_date'] = 'Previous System Date';
			$fields['system_date']   		= 'System Date';
			$fields['file_name']     		= 'Branch Logo';

			// branch_setups
			$fields['passbook_length']     = 'Passbook Length';
			$fields['passbook_row_length'] = 'Passbook Row Length';
			$fields['check_length']        = 'Check Length';
			$fields['no_of_booklet']       = 'No. of Booklet';
			$fields['no_check_per_booklet']= 'No. of Check per Booklet';
			$fields['transaction_serial']  = 'Transaction Serial';
			$fields['bsp_code']     	   = 'BSP Code';
			$fields['frp_option']     	   = 'FRP Option';
			if( $params['frp_option'] === ENUM_YES ){
				$fields['frp_path']= 'FRP Path';	
			}
			$fields['holiday_id'] = 'Branch Logo';
			
			//not required
			/*$fields['backup_path']  = 'Backup Path';
			$fields['database_name']= 'Database Name';*/

			$this->check_required_fields($params, $fields);
			
			return $this->_validate_inputs($params);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}


	private function _validate_inputs($params)
	{
		try
		{
			$validation	= array();

			/*branches*/
			$validation['brn_code'] = array(
				'data_type' => 'string',
				'name'		=> 'Branch Code',
				'min' 		=> 1,
				'max'		=> 3
			);

			$validation['brn_name'] = array(
				'data_type' => 'string',
				'name'		=> 'Branch Name',
				'min' 		=> 5,
				'max'		=> 45
			);

			$validation['institution_name'] = array(
				'data_type' => 'string',
				'name'		=> 'Institution Name',
				'min' 		=> 5,
				'max'		=> 255
			);

			$validation['previous_system_date'] = array(
				'data_type' => 'date',
				'name'		=> 'Previous System Date'
			);

			$validation['system_date'] = array(
				'data_type' => 'date',
				'name'		=> 'System Date'
			);

			$validation['file_name'] = array(
				'data_type' => 'string',
				'name'		=> 'Branch Logo'
			);

			$validation['sys_file_name'] = array(
				'data_type' => 'string',
				'name'		=> 'Branch Logo'
			);

			/*branch_setup*/
			$validation['passbook_length'] = array(
				'data_type' => 'string',
				'name'		=> 'Passbook Length',
				'min' 		=> 1,
				'max'		=> 3
			);

			$validation['passbook_row_length'] = array(
				'data_type' => 'string',
				'name'		=> 'Passbook Row Length',
				'min' 		=> 1,
				'max'		=> 3
			);

			$validation['check_length'] = array(
				'data_type' => 'string',
				'name'		=> 'Check Length',
				'min' 		=> 1,
				'max'		=> 3
			);

			$validation['no_of_booklet'] = array(
				'data_type' => 'string',
				'name'		=> 'No. of Booklet',
				'min' 		=> 1,
				'max'		=> 3
			);

			$validation['no_check_per_booklet'] = array(
				'data_type' => 'string',
				'name'		=> 'No. of Check per Booklet',
				'min' 		=> 1,
				'max'		=> 3
			);

			$validation['transaction_serial'] = array(
				'data_type' => 'string',
				'name'		=> 'No. of Check per Booklet',
				'min' 		=> 1,
				'max'		=> 10
			);

			$validation['bsp_code'] = array(
				'data_type' => 'string',
				'name'		=> 'BSP Code',
				'min' 		=> 3,
				'max'		=> 30
			);

			$validation['frp_option'] = array(
				'data_type' => 'enum',
				'name'		=> 'FRP Option',
				'allowed_types' => ['Y', 'N']
			);

			$validation['frp_path'] = array(
				'data_type' => 'enum',
				'name'		=> 'FRP Path'
			);

			$validation['backup_path'] = array(
				'data_type' => 'enum',
				'name'		=> 'Backup Path'
			);

			$validation['database_name'] = array(
				'data_type' => 'enum',
				'name'		=> 'Database Name'
			);

			$validation['database_name'] = array(
				'data_type' => 'enum',
				'name'		=> 'Database Name'
			);

			$validation['holiday_id'] = array(
				'data_type' => 'enum',
				'name'		=> 'Holidays'
			);


			return $this->validate_inputs($params, $validation);
		}
		catch ( Exception $e )
		{
			throw $e;
		}
	}
	
}
