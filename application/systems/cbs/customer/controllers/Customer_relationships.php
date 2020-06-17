<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customer_relationships extends CBS_Controller
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

		$this->table_options = array(
			'table_id'    => 'tbl_data_list',
			'path'        => $this->path,
			'modal'		  =>'#modal_customer',
			'advanced_filter' => TRUE
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

	public function index($customer_id)
	{
		try {
			$data 			= array();
			$resources 		= array();

			$resources['load_css']	= array(CSS_SELECTIZE ,CSS_LABELAUTY);
			$resources['load_js']	= array(JS_SELECTIZE, JS_LABELAUTY, JS_ADD_ROW, $this->module_js);
			$resources['loaded_init'] = array(
				"Customer_relationships.init();",
				"Customer_relationships.addRow();",
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
					'controller' => strtolower(__CLASS__),
					'modal_footer' => TRUE,
					'custom_button' => array(
						'Select'	=> array('type' => 'button', 'action' => 'Saving', 'id' => 'btn-select')
					)
		        )
			);

			// Pass all reference tables
			$data['relationship_types'] = $this->model->select_data(
				array('*'), 
				CBS_Model::CBS_TABLE_CONFIG_RELATIONSHIP_TYPES,
				TRUE
			);

			$encrypt_id       = $this->encrypt(0);
			$salt             = gen_salt();
			$token            = in_salt($encrypt_id . '/' . $this->security_action_add, $salt);
			$data['security'] 		= $encrypt_id . '/' . $salt . '/' . $token . '/' . $this->security_action_add;

			$this->load->view('tabs/'.$this->controller, $data);
			$this->load_resources->get_resource($resources);
		} catch (PDOException $e) {
			$msg = $this->get_user_message($e);
			$this->error_index($m);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);

			$this->error_index($msg);
		}
	}

	public function process_modal($row_index)
	{
		try 
		{

			$data = $resources = array();

			// Load all initial javascript functions
			$resources['load_css']  = array( CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATETIMEPICKER );
			$resources['load_js'] 	= array( JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_SELECTIZE, JS_DATETIMEPICKER );
			$resources['loaded_init'] = array();
			$resources['datatable'] = $this->table_options;
			$data['row_index'] = $row_index;

			$this->load->view('modals/customer', $data);
			$this->load_resources->get_resource($resources);

		} catch (Exception $e) {

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

				//Hidden fields
				$hidden = $record[$primary_key].'@@'.$record['first_name'].' '.$record['last_name'];
				$actions 	.= "<input type='hidden' value='$hidden' name='customer' id='customer'> ";

				$table_data[] = array(
					$avatar.$record["title_name"],
					$record['first_name'],
					$record['last_name'],
					$record['birth_date'],
					$actions
					/*"<div class='table-actions'>" . $actions . "</div>"*/
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
	
}