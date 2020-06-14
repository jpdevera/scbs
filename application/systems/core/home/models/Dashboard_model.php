<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard_model extends SYSAD_Model 
{
                
	public function __construct()
	{	
		parent::__construct();
	}

	public function get_user_role_add_users_list( array $columns, array $filter, array $order_arr, array $params )
	{
		$val 					= array();
		$result 				= array();
		$filter_str 			= '';
		$filter_params			= '';

		$add_where 				= '';
		$extra_val 			 	= array();

		try
		{
			$fields 			= str_replace( " , " , " ", implode( ", ", $columns ) );

			$where 				= $this->filtering( $filter, $params, TRUE );

			$order 				= $this->ordering( $order_arr, $params );

			$limit 				= $this->paging($params);

			$filter_str 		= $where['search_str'];

			$filter_params 		= $where['search_params'];

			$query 		= "
				SELECT 	SQL_CALC_FOUND_ROWS $fields
				FROM 	%s a 
				JOIN 	%s b
					ON 	a.role_code = b.role_code
				JOIN 	%s c 
					ON 	b.module_action_id = c.module_action_id
				JOIN 	%s d 
					ON 	c.action = d.sys_param_code
				    AND d.sys_param_type = ?
				WHERE 	c.module_code IN(?)
					AND d.sys_param_value IN(?)
				$add_where
				$filter_str
				GROUP 	BY a.role_code
				$order
				$limit	
";
			$replacements 	= array(
				SYSAD_Model::CORE_TABLE_ROLES,
				SYSAD_Model::CORE_TABLE_MODULE_ACTION_ROLES,
				SYSAD_Model::CORE_TABLE_MODULE_ACTIONS,
				SYSAD_Model::CORE_TABLE_SYS_PARAM
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);
			
			$val[] 		= SYS_PARAM_ACTIONS;
			$val[] 		= MODULE_USER;
			$val[] 		= ACTION_ADD;

			$val 		= array_merge( $val, $extra_val );

			$val 		= array_merge( $val, $filter_params );
			
			$result['aaData'] 	= $this->query( $query, $val, TRUE );
			
			$query2 			= "
				SELECT 	FOUND_ROWS() filtered_length
";

			$result['filtered_length'] 	= $this->query( $query2, array(), TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_new_users()
	{
		try
		{
			$dec_fields = aes_crypt(array(
				'ctu.fname', 'ctu.lname'
			), FALSE);

			$fields = '
				'. $dec_fields .',
				ctu.photo, ctu.contact_flag, ctr.role_name
			';

			$query = '
				SELECT
					'. $fields .'
				FROM '. parent::CORE_TABLE_USERS .' ctu
				JOIN '. parent::CORE_TABLE_USER_ROLES .' ctur
					ON ctu.user_id = ctur.user_id AND main_role_flag = 1
				JOIN '. parent::CORE_TABLE_ROLES .' ctr
					ON ctur.role_code = ctr.role_code
				WHERE status = ? AND (ctu.created_date >= DATE_SUB(NOW(), INTERVAL 8 DAY))
			';

			$val = array(STATUS_ACTIVE);
			return $this->query($query, $val, TRUE, TRUE);
		}
		catch ( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_status()
	{
		try
		{
			$fields = 'sys_param_code, sys_param_name';

			$query = '
				SELECT
					'. $fields .'
				FROM '. parent::CORE_TABLE_SYS_PARAM .'
				WHERE sys_param_type = ?
			';

			$val = array(SYS_PARAM_STATUS);
			return $this->query($query, $val, TRUE, TRUE);
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}
	
}