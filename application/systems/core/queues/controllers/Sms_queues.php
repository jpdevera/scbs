<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sms_queues extends SYSAD_Controller 
{
	protected $view_per 		= FALSE;
	protected $edit_per 		= FALSE;
	protected $add_per 			= FALSE;
	protected $delete_per 		= FALSE;

	protected $dt_options 		= array();

	public function __construct()
	{
		parent::__construct();

		$this->date_now 		= date('Y-m-d H:i:s');

		$this->controller 		= strtolower(__CLASS__);

		$this->module 			= MODULE_SMS_QUEUE;

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );

		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_QUEUES."/main_queue";

		$this->load->module(CORE_QUEUES.'/Main_queue');

		$this->dt_options 	= $this->main_queue->data_table_opt_gen;

		$this->dt_options['post_data'] = array(
			'module'	=> base64_url_encode($this->module)
		);
	}

	public function index()
	{
		$data = $resources = array();

		$this->redirect_module_permission( $this->module );

		try
		{
			$table_options 				= $this->dt_options;

			$resources['datatable']		= $table_options;

			$resources['loaded_init'] 	= array(
				"refresh_new_datatable_params('".$this->dt_options['table_id']."');",
				// 'Main_queue.form_init();',
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

		$data['table_id']				= $this->dt_options['table_id'];
		$data['pass_data'] 				= $data;

		$this->load->view('tabs/common_queues', $data);
		$this->load_resources->get_resource($resources);
	}
}