<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Param_task_actions_model extends SYSAD_Model 
{
	
	public function __construct()
	{	
		parent::__construct();
	}

	public function get_param_task_actions()
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.action_id, a.action_name
				FROM 	%s a
				WHERE 	a.active_flag = ?
";

			$val[] 	= ENUM_YES;
			$query 	= sprintf( $query, SYSAD_Model::CORE_PARAM_TASK_ACTIONS );

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
}