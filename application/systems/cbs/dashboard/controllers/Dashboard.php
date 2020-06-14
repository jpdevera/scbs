<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends SYSAD_Controller
{
	private $controller;
	private $module_js;
	private $path;
	private $table_id;

	public function __construct()
	{
		parent::__construct();

		$this->controller 	= strtolower(__CLASS__);
		$this->table_id 	= "user_approval_table";

		$this->dt_opt 	= array(
			'table_id' 			=> $this->table_id,
			'path' 				=> $this->path,
			'advanced_filter'	=> true,
			'with_search'		=> true,
			'post_data' 		=> array(
				'status_sign_up' => '0'
			)

		);
	}

	public function index()
	{
		try {
			$data 			= array();
			$resources 		= array();

			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE, CSS_DATATABLE_BUTTONS);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_DATATABLE, JS_DATATABLE_MATERIAL, JS_BUTTON_EXPORT_EXTENSION, $this->module_js);
			$this->template->load('dashboard', $data, $resources);
		} catch (PDOException $e) {
			$msg = $this->get_user_message($e);
			$this->error_index($m);
		} catch (Exception $e) {
			$msg = $this->rlog_error($e, TRUE);

			$this->error_index($msg);
		}
	}
}
