<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customer_information extends CBS_Controller
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

			$resources['load_css']	= array(CSS_SELECTIZE ,CSS_LABELAUTY, CSS_DATETIMEPICKER, CSS_UPLOAD);
			$resources['load_js']	= array(JS_SELECTIZE, JS_LABELAUTY,JS_DATETIMEPICKER, JS_UPLOAD, $this->module_js);
			$resources['loaded_init'] = array(
				"Customer_information.init();",
				"Customer_information.save();"
			);

			// Pass all reference tables
			$references = $this->get_reference_tables();
			foreach ($references as $key => $value) 
			{
				$data[$key] = $value;
			}

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

	private function get_reference_tables()
	{
		$data = [];
		try 
		{
			$data['titles'] = $this->model->select_data(
				array('*'), 
				CBS_Model::CBS_TABLE_CONFIG_TITLES,
				TRUE
			);

			$data['religions'] = $this->model->select_data(
				array("*"), 
				CBS_Model::CBS_TABLE_CONFIG_RELIGIONS,
				TRUE
			);

			$data['blood_types'] = $this->model->select_data(
				array('*'), 
				CBS_Model::CBS_TABLE_CONFIG_BLOOD_TYPES,
				TRUE
			);

			$data['civil_status'] = $this->model->select_data(
				array('*'), 
				CBS_Model::CBS_TABLE_CONFIG_CIVIL_STATUS,
				TRUE
			);

			$data['regions'] = $this->model->select_data(
				array('region_code, abbreviation region_name'), 
				CBS_Model::CBS_TABLE_LOCATION_REGIONS,
				TRUE
			);

		} 
		catch (PDOException $e) 
		{
			echo "<pre>";
			print_r($e);
			$msg = $this->get_user_message($e);
		} 
		catch (Exception $e) 
		{
			$msg = $this->rlog_error($e, TRUE);
		}

		return $data;
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

			$fields 	= $this->_construct_data($validated_data, TRUE);
			$dec_fields = $this->_construct_data($validated_data, FALSE);

			CBS_Model::beginTransaction();
			switch($params['security_action'])
			{
				case $this->security_action_add:
					if( !empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));

					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));

					// Insert Customer
					$customer_id = $this->model->insert_data(CBS_Model::CBS_TABLE_CUSTOMERS, $fields['customer'], TRUE);
					if( $customer_id )
					{
						// Insert Contacts
						foreach ($fields['contacts'] as $contact) 
						{
							$contact['customer_id'] = $customer_id;
				            $this->model->insert_data(CBS_Model::CBS_TABLE_CUSTOMER_CONTACTS, $contact);
			          	}

		          	  	// Insert Address
			         	$fields['res_address']['customer_id'] = $customer_id;
			          	$fields['res_address']['address_type_id'] = 1;
			          	$this->model->insert_data(CBS_Model::CBS_TABLE_CUSTOMER_ADDRESSES, $fields['res_address']);

			          	if (isset($validated_data['per_region'])) {
			            	$fields['per_address']['customer_id'] = $customer_id;
			            	$fields['per_address']['address_type_id'] = PERMANENT_ADDRESS;
			           		$this->model->insert_data(CBS_Model::CBS_TABLE_CUSTOMER_ADDRESSES, $fields['per_address']);
			          	}
					}

					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_CBS;
					$audit_table[] = CBS_Model::CBS_TABLE_GL_TYPES;
					$prev_detail[] = array();
					$curr_detail[] = array($fields['customer']);
					$activity	   = "Customer {$customer_id} has been added in the system.";
				break;

				case $this->security_action_edit:
					if( empty($id) )
						throw new Exception($this->lang->line('err_unauthorized_access'));
						
					if($this->permission_edit === FALSE)
						throw new Exception($this->lang->line('err_unauthorized_add'));


					// Audit trail
					$msg = $this->lang->line('data_saved');
					$audit_action[] = AUDIT_INSERT;
					$audit_schema[] = DB_CBS;
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

	public function process_modal($hash_id, $salt, $token, $security_action)
	{
		try
		{

			$data 		= $resources = array();
			$security 	= "";
			$id 		= $this->decrypt($hash_id);

			// Load resources
			$resources['load_css']	= array(CSS_SELECTIZE ,CSS_LABELAUTY);
			$resources['load_js']	= array(JS_SELECTIZE, JS_LABELAUTY, $this->module_js);

			// Loaded Init
			$resources['loaded_init'] = array(
				"Type.add();",
				"Type.edit();"
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

					$data['types'] = $this->model->select_data(array("*"), CBS_Model::CBS_TABLE_GL_TYPES, FALSE, array('type_id'=>$id));
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
				print_r($e);
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
			$audit_schema[]	= DB_CBS;
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


	public function _validate_form(&$params) {
		try 
		{
			$this->validate_security($params);

			$fields = array();
			$fields['height'] = 'Height';
			$fields['weight'] = 'Weight';
			$fields['mobile_no'] = 'Mobile No.';
			$fields['last_name'] = 'Last Name';
			$fields['first_name'] = 'First Name';
			$fields['birth_date'] = 'Birth Date';
			// $fields['religion_id'] = 'Religion';
			$fields['birth_place'] = 'Birth Place';
			$fields['blood_type_id'] = 'Blood Type';
			$fields['civil_status_id'] = 'Civil Status';
			$fields['citizenship_type'] = 'Citizenship Type';

			$fields['res_region'] = 'Residential Region';
			if ($params['res_citymuni'] != '0|00') {
				$fields['res_barangay'] = 'Residential Barangay';
			}
			$fields['res_province'] = 'Residential Province';
			$fields['res_citymuni'] = 'Residential City / Municipality';

			if (isset($params['per_region'])) {
				$fields['per_region'] = 'Permanent Region';
				if ($params['per_citymuni'] != '0|00') {
					$fields['per_barangay'] = 'Permanent Barangay';
				}
				$fields['per_province'] = 'Permanent Province';
				$fields['per_citymuni'] = 'Permanent City / Municipality';
			}

			$this->check_required_fields($params, $fields);
			return $this->_validate_inputs($params);

		} catch (Exception $e) {
			throw $e;
		}
	}



	public function _validate_inputs($params) {
		try {
			$validation = array();

			$validation['last_name'] = array (
				'name' => 'Last Name',
				'data_type' => 'string',
				'min_len' => 2,
				'max_len' => 50
			);

			$validation['first_name'] = array (
				'name' => 'First Name',
				'data_type' => 'string',
				'min_len' => 2,
				'max_len' => 50
			);

			$validation['middle_name'] = array (
				'name' => 'Middle Name',
				'data_type' => 'string'
			);

			$validation['ext_name'] = array(
				'name' => 'Extension Name',
				'data_type' => 'string',
				'max_len' => 50
			);

			$validation['maiden_name'] = array(
				'name' => 'Maiden Name',
				'data_type' => 'string',
				'max_len' => 225
			);

			$validation['birth_date'] = array (
				'name' => 'Birth Date',
				'data_type' => 'date',
				'max_date' => date('Y-m-d', strtotime('NOW')),
				'compare' => date('F d, Y', strtotime('NOW'))
			);

			$validation['birth_place'] = array (
				'name' => 'Birth Place',
				'data_type' => 'string',
				'min_len' => 2,
				'max_len' => 100
			);

			$validation['sex_code'] = array (
				'name' => 'Sex',
				'data_type' => 'enum',
				'allowed_values' => array('M', 'F')
			);

			$validation['civil_status_id'] = array (
				'name' => 'Civil Status',
				'data_type' => 'string'
			);

			$validation['religion_id'] = array (
				'name' => 'Religion',
				'data_type' => 'string'
			);

			$validation['height'] = array (
				'name' => 'Height',
				'data_type' => 'string',
				'min_len' => 1,
				'max_len' => 5
			);

			$validation['weight'] = array (
				'name' => 'Weight',
				'data_type' => 'string',
				'min_len' => 1,
				'max_len' => 7
			);

			$validation['blood_type_id'] = array (
				'name' => 'Blood Type',
				'data_type' => 'string',
				'min_len' => 1,
				'max_len' => 4
			);

			$validation['citizenship_type'] = array (
				'name' => 'Citizenship Type',
				'data_type' => 'enum',
				'allowed_values' => array('F', 'D')
			);

			$validation['dual_citizen_type'] = array(
				'name' => 'Dual Citizenship Type',
				'data_type' => 'enum',
				'allowed_types' => array('B', 'N')
			);

			$validation['dual_country_id'] = array(
				'name' => 'Country',
				'data_type' => 'string',
			);

			$validation['mobile_no'] = array(
				'name' => 'Mobile No.',
				'data_type' => 'string',
				'min_len' => 2,
				'max_len' => 100
			);

			$validation['tel_no'] = array(
				'name' => 'Telephone No.',
				'data_type' => 'string',
				'max_len' => 100
			);

			$validation['email'] = array(
				'name' => 'Primary Email Address',
				'data_type' => (!empty($params['email']) ? 'email' : 'string')
			);

			$validation['alternate'] = array(
				'name' => 'Alternate Email Address',
				'data_type' => (!empty($params['alternate']) ? 'email' : 'string')
			);

			$validation['res_house_no'] = array(
				'name' => 'Residential House / Block / Lot No.',
				'data_type' => 'string'
			);

			$validation['res_street'] = array(
				'name' => 'Residential Street',
				'data_type' => 'string'
			);

			$validation['res_subdivision'] = array(
				'name' => 'Residential Subdivision / Village',
				'data_type' => 'string'
			);

			$validation['res_zipcode'] = array(
				'name' => 'Residential ZIP Code',
				'data_type' => 'string'
			);

			$validation['res_region'] = array(
				'name' => 'Residential Region',
				'data_type' => 'string'
			);

			$validation['res_province'] = array(
				'name' => 'Residential Province',
				'data_type' => 'string'
			);

			$validation['res_citymuni'] = array(
				'name' => 'Residential City / Municipality',
				'data_type' => 'string'
			);

			$validation['res_barangay'] = array(
				'name' => 'Residential Barangay',
				'data_type' => 'string'
			);

			if (isset($params['per_region'])) {
				$validation['per_house_no'] = array(
					'name' => 'Permanent House / Block / Lot No.',
					'data_type' => 'string'
				);

				$validation['per_street'] = array(
					'name' => 'Permanent Street',
					'data_type' => 'string'
				);

				$validation['per_subdivision'] = array(
					'name' => 'Permanent Subdivision / Village',
					'data_type' => 'string'
				);

				$validation['per_zipcode'] = array(
					'name' => 'Permanent ZIP Code',
					'data_type' => 'string'
				);

				$validation['per_region'] = array(
					'name' => 'Permanent Region',
					'data_type' => 'string'
				);

				$validation['per_province'] = array(
					'name' => 'Permanent Province',
					'data_type' => 'string'
				);

				$validation['per_citymuni'] = array(
					'name' => 'Permanent City / Municipality',
					'data_type' => 'string'
				);

				$validation['per_barangay'] = array(
					'name' => 'Permanent Barangay',
					'data_type' => 'string'
				);
			}

			if ($params['alternate'] === $params['email'])
				throw new Exception('Invalid alternate email');

			return $this->validate_inputs($params, $validation);
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function _construct_data($validated_data, $encrypt = TRUE) {
		try{
			$fields = array();

			if (empty($validated_data['dual_country_id']))
				$validated_data['dual_country_id'] = NULL;

			if (!empty($validated_data['dual_country_id']))
				$validated_data['citizenship_type'] = 'D';

			if (!isset($validated_data['dual_citizen_type']))
				$validated_data['dual_citizen_type'] = NULL;

			if ($validated_data['sex_code'] === 'M')
				$validated_data['maiden_name'] = NULL;

			if (empty($validated_data['religion_id']))
				$validated_data['religion_id'] = NULL;

			$fields['customer']['last_name'] = array(strtoupper($validated_data['last_name']), 'ENCRYPT');
			$fields['customer']['first_name'] = array(strtoupper($validated_data['first_name']), 'ENCRYPT');
			$fields['customer']['middle_name'] = array(strtoupper($validated_data['middle_name']), 'ENCRYPT');
			$fields['customer']['ext_name'] = array(strtoupper($validated_data['ext_name']), 'ENCRYPT');
			$fields['customer']['maiden_name'] = array($validated_data['maiden_name'], 'ENCRYPT');
			$fields['customer']['birth_date'] = array(date('Y-m-d', strtotime($validated_data['birth_date'])), 'ENCRYPT');
			$fields['customer']['birth_place'] = array($validated_data['birth_place'], 'ENCRYPT');
			$fields['customer']['sex_code'] = $validated_data['sex_code'];
			$fields['customer']['civil_status_id'] = $validated_data['civil_status_id'];
			$fields['customer']['religion_id'] = $validated_data['religion_id'];
			$fields['customer']['height'] = $validated_data['height'];
			$fields['customer']['weight'] = $validated_data['weight'];
			$fields['customer']['blood_type_id'] = $validated_data['blood_type_id'];
			$fields['customer']['dual_country_id'] = $validated_data['dual_country_id'];
			$fields['customer']['citizenship_type'] = $validated_data['citizenship_type'];
			$fields['customer']['dual_citizen_type'] = $validated_data['dual_citizen_type'];


			if ($encrypt === FALSE) {
				$fields['customer']['ext_name'] = $validated_data['ext_name'];
				$fields['customer']['last_name'] = $validated_data['last_name'];
				$fields['customer']['birth_date'] = $validated_data['birth_date'];
				$fields['customer']['first_name'] = $validated_data['first_name'];
				$fields['customer']['birth_date'] = date('Y-m-d', strtotime($validated_data['birth_date']));
				$fields['customer']['middle_name'] = $validated_data['middle_name'];
				$fields['customer']['birth_place'] = $validated_data['birth_place'];
				$fields['customer']['maiden_name'] = $validated_data['maiden_name'];
			}

			$fields['contacts']['mobile_no'] = array(
				'contact_value' => array($validated_data['mobile_no'], 'ENCRYPT'),
				'contact_type_id' => 1
			);

			$fields['contacts']['tel_no'] = array(
				'contact_value' => array($validated_data['tel_no'], 'ENCRYPT'),
				'contact_type_id' => 2
			);

			$fields['contacts']['email'] = array(
				'contact_value' => array($validated_data['email'], 'ENCRYPT'),
				'contact_type_id' => 3
			);

			$fields['contacts']['alternate'] = array(
				'contact_value' => array($validated_data['alternate'], 'ENCRYPT'),
				'contact_type_id' => 4
			);

			$res_citymuni = explode('|', $validated_data['res_citymuni']);
			$fields['res_address'] = array(
				'street' => array($validated_data['res_street'], 'ENCRYPT'),
				'house_no' => array($validated_data['res_house_no'], 'ENCRYPT'),
				'region_code' => $validated_data['res_region'],
				'subdivision' => array($validated_data['res_subdivision'], 'ENCRYPT'),
				'province_code' => $validated_data['res_province'],
				'barangay_code' => (!EMPTY($validated_data['res_barangay'])) ? $validated_data['res_barangay'] : NULL,
				'postal_number' => $validated_data['res_zipcode'],
				'citymuni_code' => $res_citymuni[1],
				'district_code' => $res_citymuni[0]
			);

			if (isset($validated_data['per_region'])) 
			{
				$per_citymuni = explode('|', $validated_data['per_citymuni']);
				$fields['per_address'] = array(
					'street' => array($validated_data['per_street'], 'ENCRYPT'),
					'house_no' => array($validated_data['per_house_no'], 'ENCRYPT'),
					'region_code' => $validated_data['per_region'],
					'subdivision' => array($validated_data['per_subdivision'], 'ENCRYPT'),
					'province_code' => $validated_data['per_province'],
					'barangay_code' => (!EMPTY($validated_data['per_barangay'])) ? $validated_data['per_barangay'] : NULL,
					'postal_number' => $validated_data['per_zipcode'],
					'citymuni_code' => $per_citymuni[1],
					'district_code' => $per_citymuni[0]
				);
			}
			return $fields;

		} catch (Exception $e) 
		{
			throw $e;
		}

	}

	public function get_province() {
		try 
		{
			$msg = '';
			$params = get_params();

			$where = array('region_code' => $params['region_code']);
			$order = array('province_code'=>'ASC');
			$data  = $this->model->select_data(
				array('province_code as value', 'province_name as text'), 
				CBS_Model::CBS_TABLE_LOCATION_PROVINCES, 
				TRUE, 
				$where,
				$order
			);

			echo json_encode(array(
				'msg' => $msg,
				'data' => $data
			));
		} catch (PDOException $e) {
			$msg = $this->get_user_message($e);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);
		}
	}

	public function get_citymuni() {
		try 
		{
			$msg = '';
			$params = get_params();

			$where  = array('region_code' => $params['region_code'], 'province_code' => $params['province_code']);
			$order  = array('province_code' => 'ASC');
			$data 	= $this->model->select_data(
				array('CONCAT_WS("|", district_code, citymuni_code) as value', 'citymuni_name as text'), 
				CBS_Model::CBS_TABLE_LOCATION_CITYMUNI, 
				TRUE, 
				$where,
				$order
			);
			echo json_encode(array(
				'msg' => $msg,
				'data' => $data
			));
		} catch (PDOException $e) {
			print_r($e);
			$msg = $this->get_user_message($e);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);
		}
	}

	public function get_barangay() {
		try 
		{
			$msg = '';
			$params = get_params();
			$citymuni_code = explode('|', $params['citymuni_code']);

			$where = array(
				'region_code' 	=> $params['region_code'],
				'province_code' => $params['province_code'],
				'citymuni_code' => (!empty($params['citymuni_code']) ? $citymuni_code[1] : ''),
				'district_code' => (!empty($params['citymuni_code']) ? $citymuni_code[0] : '')
			);
			$order = array('province_code' => 'ASC');
			$data  = $this->model->select_data(
				array('barangay_code as value', 'barangay_name as text'), 
				CBS_Model::CBS_TABLE_LOCATION_BARANGAYS, 
				TRUE, 
				$where,
				$order
			);

			echo json_encode(array(
				'msg' => $msg,
				'data' => $data
			));

		} catch (PDOException $e) {
			$msg = $this->get_user_message($e);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);
		}
	}

	
}