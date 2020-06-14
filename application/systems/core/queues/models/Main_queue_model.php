<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main_queue_model extends SYSAD_Model 
{
	
	private $email_notification_queues;
	private $sms_notification_queues;
                
	public function __construct()
	{
		parent::__construct();
		
		$this->email_notification_queues 	= parent::CORE_TABLE_EMAIL_NOTIFICATION_QUEUES;
		$this->sms_notification_queues 		= parent::CORE_TABLE_SMS_NOTIFICATION_QUEUES;
	}

	public function get_list( $table, array $columns, array $filter, array $order_arr, array $params )
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
                	ON 	a.from_user = b.user_id
                JOIN 	%s c 
                	ON 	a.to_user 	= c.user_id
               	WHERE 	1 = 1
				$add_where
				$filter_str
				$order
				$limit	
";
			$replacements 	= array(
				$table,
				SYSAD_Model::CORE_TABLE_USERS,
				SYSAD_Model::CORE_TABLE_USERS
			);

			$query 			= preg_replace_callback('/[\%]s/', function() use (&$replacements)
			{
				 return array_shift($replacements);
			}, $query);

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

	public function delete_helper($table, array $where)
	{
		try
		{
			$this->delete_data( $table, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function update_helper($table, array $val, array $where)
	{
		try
		{
			$this->update_data( $table, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
}