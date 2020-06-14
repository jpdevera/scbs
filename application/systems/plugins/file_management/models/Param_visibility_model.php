<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Param_visibility_model extends SYSAD_Model 
{
	public function __construct()
	{
		parent::__construct();

	}

	public function get_visibility()
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT	a.visibility_id, a.visibility_name
				FROM 	%s a
";
			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_PARAM_VISIBILITY );

			$result = $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}