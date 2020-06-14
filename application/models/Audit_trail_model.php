<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Audit_trail_model extends SYSAD_Model {
	
	private $audit_trail;
	private $audit_trail_detail;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->audit_trail = SYSAD_Model::CORE_TABLE_AUDIT_TRAIL;
		$this->audit_trail_detail = SYSAD_Model::CORE_TABLE_AUDIT_TRAIL_DETAIL;
	}
                
	public function insert_audit_trail($params = array())
    {
        try
        {
            $user_id            	= ($this->session->has_userdata('user_id') === TRUE)? $this->session->user_id : ANONYMOUS_ID;
            if( ISSET( $params['user_id'] ) )
            {
            	$user_id 			= $params['user_id'];
            }
            $ip_address            	= (ISSET($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'localhost');
            $user_agent            	= $this->input->user_agent();
            $user_agent            	= (ISSET($user_agent) ? $user_agent: 'test');

            $val                	= array();
            $val["user_id"]        	= $user_id;
            $val["module_code"] 	= filter_var($params["module"], FILTER_SANITIZE_STRING);
            $val["activity"]     	= filter_var($params["activity"], FILTER_SANITIZE_STRING);
            $val["ip_address"]     	= $ip_address;
            $val["user_agent"]     	= $user_agent;
            
            $id = $this->insert_data($this->audit_trail, $val, TRUE);

            return $id;

        }
        catch(PDOException $e)
        {
            throw $e;
        }
    }
		
	public function insert_audit_trail_detail($params)
	{
		try
		{
			for($i=0; $i<count($params); $i++){
				$this->insert_data($this->audit_trail_detail, $params[$i]);
			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
			
}
