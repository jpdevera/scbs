<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Holidays extends CBS_Controller
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
		$this->module_code  = MODULE_FILE_MAINTENANCE_HOLIDAYS;
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
		$this->load->model('holidays_model', 'model');
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
					'size' 	=> 'sm-w sm-h',
					'title' => 'Create Holidays',
					'modal_style' => 'modal-icon',
					'modal_header_icon' => 'library_add',
					'module' => $this->module_folder,
					'method' => 'process_modal',
					'controller' => strtolower(__CLASS__),
					'modal_footer' => TRUE
		        ),
		        'modal_edit' => array(
					'size' 		=> 'sm-w sm-h',
					'title'		=> 'Edit Holidays',
					'modal_style'=> 'modal-icon',
					'modal_header_icon' => 'edit',
					'module'		=> $this->module_folder,
					'method' => 'process_modal',
					'controller' 	=> strtolower(__CLASS__)
				),
			);

			$resources['loaded_init'] = array(
				"Holidays.init();",
				"Holidays.remove();",
				'Holidays.calendar_js();',
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
			$primary_key 		= 'holiday_id';

			foreach($records as $record)
			{
				$hash_id	= $this->encrypt($record[$primary_key]);
				$salt		= gen_salt();
				$actions	= "";
				if($this->permission_view){}

				if( $this->permission_edit )
				{

					$token	  = in_salt($hash_id . '/' . $this->security_action_edit, $salt);
					$url   	  = $hash_id . '/' . $salt . '/' . $token . '/' . $this->security_action_edit;
					

					$actions.= "<a href='#modal_edit' onclick=\"modal_edit_init('".$url."','','Edit GL Sort Code')\"><i class='grey-text material-icons tooltipped' data-position='bottom' data-delay='50' data-tooltip='Edit'>mode_edit</i></a>";
				}

				if( $this->permission_delete )
				{
					$token	  = in_salt($hash_id . '/' . $this->security_action_delete, $salt);
					$url   	  = $hash_id . '/' . $salt . '/' . $token . '/' . $this->security_action_delete;

					$onclick = 'content_delete("holiday", "'.$url.'")';			
							
					$actions.= "<a href='javascript:;' onclick='".$onclick."' class='tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
				}


				$table_data[] = array(
					$record['holiday_title'],
					$record['holiday_desc'],
					$record['holiday_date'],
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
			$id = $params['decrypt_id'];

			CBS_Model::beginTransaction();
			switch($params['security_action'])
			{
				case $this->security_action_add:
					// insert gl type
					$fields   = [];
					$fields['holiday_title'] = $validated_data['holiday_title'];
				    $fields['holiday_desc'] = $validated_data['holiday_desc'];
				    $fields['holiday_date']  = $validated_data['holiday_date'];
				    $fields['recurring_flag']  = $validated_data['recurring_flag'];
					$holiday_id = $this->model->insert_data(CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, $fields, TRUE);

					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_SCBS;
					$audit_table[] = CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS;
					$prev_detail[] = array();
					$curr_detail[] = array($fields);
					$activity	   = "Holiday {$holiday_id} has been added in the system.";
				break;

				case $this->security_action_edit:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));
						
					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					// Previous Records
					$where 	  = array('holiday_id'=>$id);
					$previous = $this->model->select_data(array('*'), CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, FALSE, $where);

					// update gl types
					$fields   = [];
					$fields['holiday_title'] = $validated_data['holiday_title'];
				    $fields['holiday_desc'] = $validated_data['holiday_desc'];
				    $fields['holiday_date']  = $validated_data['holiday_date'];
				    $fields['recurring_flag']= $validated_data['recurring_flag'];
					$holiday_id = $this->model->update_data(CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, $fields, $where);

					// Audit trail
					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_SCBS;
					$audit_table[] = CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS;
					$prev_detail[] = array($previous);
					$curr_detail[] = array($fields);
					$activity	   = "Holiday {$id} has been updated in the system.";
				break;

				default:
					throw new Exception($this->lang->line('err_unauthorized_access'));
				break;
			}

			$this->audit_trail->log_audit_trail($activity, $this->module_code, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema);

			CBS_Model::commit();
			$status = SUCCESS;
		} 
		catch (PDOException $e) 
		{
			print_r($e);
			die();
			$msg = $this->get_user_message($e);
			CBS_Model::rollback();
		} 
		catch (Exception $e) 
		{
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

	public function process_modal($hash_id, $salt, $token, $security_action)
	{
		try
		{

			$data 		= $resources = array();
			$security 	= "";
			$id 		= $this->decrypt($hash_id);

			// Load resources
			$resources['load_css']	= array(CSS_SELECTIZE ,CSS_LABELAUTY, CSS_DATETIMEPICKER);
			$resources['load_js']	= array(JS_SELECTIZE, JS_LABELAUTY,JS_DATETIMEPICKER, $this->module_js);

			// Loaded Init
			$resources['loaded_init'] = array(
				"Holidays.add();",
				"Holidays.edit();"
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

					$data['holidays'] = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, FALSE, array('holiday_id'=>$id));
					$modal 		   = $this->controller;
				break;

				case $this->security_action_view:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_view === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$modal 				= $this->controller;
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
			$where 		= array('holiday_id' => $this->decrypt($hash_id));
			$record     = $this->model->select_data(array('*'), CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, FALSE, $where);
			$holiday_id    = isset($record['holiday_id']) ? $record['holiday_id'] : 0;

			switch($security_action)
			{
				case $this->security_action_delete:
					if( empty($holiday_id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));

					if($this->permission_delete === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_delete'));

					$this->model->delete_data(CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, array('holiday_id' => $holiday_id));

					$msg  = $this->lang->line('data_deleted');
				break;

				default:
				throw new Exception($this->lang->line('err_unauthorized_access'));
				break;
			}

			$audit_action[]	= AUDIT_DELETE;
			$audit_table[]	= CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS;
			$audit_schema[]	= DB_SCBS;
			$prev_detail[]	= array($record);
			$curr_detail[]	= array();
			$activity		= $record["holiday_title"] . " has been deleted in the system.";
			
			// $this->audit_trail->log_audit_trail($activity, $this->module_code, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema);

			CBS_Model::commit();
			$msg 	= $this->lang->line('data_deleted');
			$status = SUCCESS;

		}
		catch(PDOException $e)
		{
			print_r($e);
			CBS_Model::rollback();
			$this->rlog_error($e);
			$status = ERROR;
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			print_r($e);
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
			$fields['holiday_title'] = "Title";
			$fields['holiday_desc'] = 'Description';
			$fields['holiday_date'] = 'Date';
			$fields['recurring_flag']  = 'Recurring';
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
			$validation['holiday_title'] = array(
				'data_type' => 'string',
				'name'		=> 'Title',
				'min' 		=> 5,
				'max'		=> 45
			);

			$validation['holiday_desc'] = array(
				'data_type' => 'string',
				'name'		=> 'Description',
				'min' 		=> 5,
				'max'		=> 255
			);

			$validation['holiday_date'] = array(
				'data_type' => 'date',
				'name'		=> 'Date'
			);


			$validation['recurring_flag'] = array(
				'data_type' => 'enum',
				'name'		=> 'Recurring',
				'allowed_values' 	=> ['Y','N']
			);


			return $this->validate_inputs($params, $validation);
		}
		catch ( Exception $e )
		{
			throw $e;
		}
	}

	public function get_calendar_info()
	{
		try
		{
			$data = $this->model->select_data(array('*'), CBS_Model::CBS_TABLE_CONFIG_HOLIDAYS, TRUE);
			$events	  = array();

			foreach ($data as $key => $value)
			{
				$events[] 	= array(
					'id' 	=> $value['holiday_id'],
					'title' => html_entity_decode($value['holiday_title'], ENT_QUOTES, 'UTF-8'),
					'start' => $value['holiday_date'],
					// 'status' => $value['holiday_id'],
					// 'color' => '#2181e0',
					'color' => '#E02121'
				);
			}

		}
		catch(Exception $e){
			$message = $e->getMessage();
			RLog::error($message);
		}


		//echo json_encode($data);
		echo json_encode($events);
	}
	
}
