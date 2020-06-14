<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class System_modules extends SYSAD_Controller 
{	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('system_modules_model', 'system_modules', TRUE);
	}
	
	public function toggle_fields($module = NULL)
	{
		try
		{
			$params = get_params();
			$module = $params['module_code'];
			$output = array();
			
			$fields = $this->system_modules->get_fields($module);
			
			foreach($fields as $row):
				$output[] = $row['field_name'];		
			endforeach;
			
			//print_r($output);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
			
			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);
			
			$this->error_index( $msg );
		}
		
		echo json_encode($output);
		
	}
}