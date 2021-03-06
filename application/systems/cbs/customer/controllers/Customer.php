<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends CBS_Controller
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
		$this->module_code  = MODULE_CBS_CUSTOMER;
		$this->module_folder= FOLDER_CUSTOMER;
		$this->controller 	= strtolower(__CLASS__);
		$this->path 		= $this->module_folder.'/'.$this->controller.'/get_data_list';
		$this->module_js    = HMVC_FOLDER .'/'. SYSTEM_CBS .'/'.  $this->module_folder.'/'.$this->controller;
		$this->module_js_relationships    = HMVC_FOLDER .'/'. SYSTEM_CBS .'/'.  $this->module_folder.'/customer_relationships';

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
		$this->load->model('customer_model', 'model');
	}

	public function index()
	{
		try {
			$data 			= array();
			$resources 		= array();

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATATABLE_BUTTONS);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_BUTTON_EXPORT_EXTENSION, $this->module_js);

			//Load modal
			$resources['load_materialize_modal'] = array(
				'modal_add' => array(
					'size' 	=> 'sm-w sm-h',
					'title' => 'Create Customer',
					'modal_style' => 'modal-icon',
					'modal_header_icon' => 'library_add',
					'module' => $this->module_folder,
					'method' => 'process_modal',
					'controller' => strtolower(__CLASS__),
					'modal_footer' => TRUE
		        ),
		        'modal_edit' => array(
					'size' 		=> 'sm-w sm-h',
					'title'		=> 'Edit Customer',
					'modal_style'=> 'modal-icon',
					'modal_header_icon' => 'edit',
					'module'		=> $this->module_folder,
					'method' => 'process_modal',
					'controller' 	=> strtolower(__CLASS__)
				),
			);

			$resources['loaded_init'] = array(
				"Customer.init();"
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
			$primary_key 		= 'customer_id';

			foreach($records as $record)
			{
				$avatar = $this->_construct_avatar($record);
				$hash_id	= $this->encrypt($record[$primary_key]);
				$salt		= gen_salt();
				$actions	= "";
				if($this->permission_view){}

				if( $this->permission_edit )
				{

					$token	= in_salt($hash_id . '/' . $this->security_action_edit, $salt);
					$url	= $hash_id . '/' . $salt . '/' . $token . '/' . $this->security_action_edit;

					$actions.= "<a href='#' onclick=\"content_form('".$url."','customer/form')\"><i class='grey-text material-icons tooltipped' data-position='bottom' data-delay='50' data-tooltip='Edit'>mode_edit</i></a>";
				}

				if( $this->permission_delete )
				{
					$token	  = in_salt($hash_id . '/' . $this->security_action_delete, $salt);
					$url   	  = $hash_id . '/' . $salt . '/' . $token . '/' . $this->security_action_delete;

					$onclick = 'content_delete("customer", "'.$url.'")';			
							
					$actions.= "<a href='javascript:;' onclick='".$onclick."' class='tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>";
				}


				$table_data[] = array(
					$avatar.$record["title_name"],
					$record['first_name'],
					$record['last_name'],
					$record['birth_date'],
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

 	private function _construct_avatar($record)
	{
		try 
		{
			$photo_path = base_url() . PATH_UPLOAD_CUSTOMER;
			$img_src 	= $photo_path.'avatar.jpg';
			if( isset($record['photo']) AND !empty($record['photo']) )
			{
				$img_src = $photo_path.$record['photo'];
			}
			$img = '<img class="avatar" width="20" height="20" src="'.$img_src.'" data-name="" /> ';
			
			return '<span class="table-avatar-wrapper">' . $img .'</span>';

		}
		catch(Exception $e)
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

			$resources['load_css']	= array(CSS_SELECTIZE ,CSS_LABELAUTY, CSS_DATETIMEPICKER, CSS_UPLOAD);
			$resources['load_js']	= array(JS_SELECTIZE, JS_LABELAUTY,JS_DATETIMEPICKER, JS_UPLOAD, JS_ADD_ROW, $this->module_js, $this->module_js_relationships);
			$resources['loaded_init'] = array(
				'Customer.init();',
				'Customer_relationships.init();',
				'Customer_relationships.addRow();'
			);

			//Load modal
			$resources['load_materialize_modal'] = array(
				'modal_customer' => array(
					'size' 	=> 'lg',
					'title' => 'Search Customer',
					'modal_style' => 'modal-icon',
					'modal_header_icon' => 'search',
					'module' => $this->module_folder,
					'method' => 'process_modal',
					'controller' => 'customer_relationships',
					'modal_footer' => TRUE,
					'custom_button' => array(
						'Select'	=> array('type' => 'button', 'action' => 'Saving', 'id' => 'btn-select')
					)
		        )
			);

			$resources['upload'] = array(
	        'user_photo' => array(
	          'id' => 'user_photo',
	          'path' => PATH_UPLOAD_BRANCH,
	          'multiple' => 0,
	          'max_file' => 1,
	          'drag_drop' => 0,
	          'show_preview' => 0,
	          'allowed_types' => 'jpg,jpeg,png'
	          // 'deleteCallback' => 'Profile.deleteCallback('. $arr .');',
	          // 'successCallback' => 'Profile.successCallback('. $arr .', data);'
	        )
	      );

			switch($security_action)
			{
				case $this->security_action_add:
					if( ! empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_add === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$data['title'] = 'Create Customer';
				break;

				case $this->security_action_edit:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$data['title']    = 'Edit Customer';
				break;

				case $this->security_action_view:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_add'));

					if($this->permission_view === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					$data['title'] = 'View Customer';
				break;

			}	
			
			$salt			 = gen_salt();
			$token			 = in_salt($hash_id . '/' . $security_action, $salt);
			$security		 = $hash_id . '/' . $salt . '/' . $token . '/' . $security_action;
			$data['security']= $security;
			$data['customer_id']  = $hash_id;

		} catch (PDOException $e) {
			$msg = $this->get_user_message($e);
			$this->error_index($m);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);

			$this->error_index($msg);
		}

		// Pass all reference tables
		$data['relationship_types'] = $this->model->select_data(
			array('*'), 
			CBS_Model::CBS_TABLE_CONFIG_RELATIONSHIP_TYPES,
			TRUE
		);

		// Get all tabs
		$tabs   = ['business', 'codes', 'employment', 'ids', 'information', 'relationships'];
		foreach ($tabs as $key => $value) 
		{
			$tab_name = $this->controller.'_'.$value;
			$data[$tab_name]   = $this->load->view('tabs/'.$tab_name, $data, TRUE);	
		}

		// print_r($data);
		// die();

		// Load form
		$this->template->load('forms/'.$this->controller, $data, $resources);	

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
					$fields['type_code'] = $validated_data['type_code'];
				    $fields['type_name'] = $validated_data['type_name'];
				    $fields['position']  = $validated_data['position'];
				    $fields['active_flag']  = $validated_data['active_flag'];
					$type_id = $this->model->insert_data(CBS_Model::CBS_TABLE_GL_TYPES, $fields, TRUE);

					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_SCBS;
					$audit_table[] = CBS_Model::CBS_TABLE_GL_TYPES;
					$prev_detail[] = array();
					$curr_detail[] = array($fields);
					$activity	   = "GL Type {$id} has been added in the system.";
				break;

				case $this->security_action_edit:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));
						
					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					// Previous Records
					$where 	  = array('type_id'=>$id);
					$previous = $this->model->select_data(array('*'), CBS_Model::CBS_TABLE_GL_TYPES, FALSE, $where);

					// update gl types
					$fields   = [];
					$fields['type_code'] = $validated_data['type_code'];
				    $fields['type_name'] = $validated_data['type_name'];
				    $fields['position']  = $validated_data['position'];
				    $fields['active_flag']= $validated_data['active_flag'];
					$type_id = $this->model->update_data(CBS_Model::CBS_TABLE_GL_TYPES, $fields, $where);

					// Audit trail
					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_SCBS;
					$audit_table[] = CBS_Model::CBS_TABLE_GL_TYPES;
					$prev_detail[] = array($previous);
					$curr_detail[] = array($fields);
					$activity	   = "GL Type {$id} has been updated in the system.";
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
			$where 		= array('type_id' => $this->decrypt($hash_id));
			$record     = $this->model->select_data(array('*'), CBS_Model::CBS_TABLE_GL_TYPES, FALSE, $where);
			$type_id    = isset($record['type_id']) ? $record['type_id'] : 0;

			switch($security_action)
			{
				case $this->security_action_delete:
					if( empty($type_id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));

					if($this->permission_delete === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_delete'));

					$this->model->delete_data(CBS_Model::CBS_TABLE_GL_TYPES, array('type_id' => $type_id));

					$msg  = $this->lang->line('data_deleted');
				break;

				default:
				throw new Exception($this->lang->line('err_unauthorized_access'));
				break;
			}

			$audit_action[]	= AUDIT_DELETE;
			$audit_table[]	= CBS_Model::CBS_TABLE_GL_TYPES;
			$audit_schema[]	= DB_SCBS;
			$prev_detail[]	= array($record);
			$curr_detail[]	= array();
			$activity		= $record["type_name"] . " has been deleted in the system.";
			
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

	private function insert_decrypt(){
		// AES_ENCRYPT('.$field.', UNHEX(SHA2("'.SECURITY_PASSPHRASE.'", 512)))
	}

	
}
