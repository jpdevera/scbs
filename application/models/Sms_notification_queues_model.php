<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sms_notification_queues_model extends SYSAD_Model 
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_sms_notification_queue(array $where = array(), $single = FALSE)
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			$limit_str 	= "";

			if( $single )
			{
				$limit_str 	= " LIMIT 1 ";
			}
			else 
			{
				$limit_str 	= " LIMIT 30 ";	
			}

			if( !EMPTY( $where ) )
			{
				list($add_where, $extra_val)	= $this->_construct_where_statement($where);

				$add_where 	= " AND ".$add_where;
			}

			$fields			= array('b.mobile_no', 'b.email', 'b.fname', 'b.lname', 'b.mname');
			$decrypt_fields = aes_crypt($fields, FALSE);

			$query 		= "
				SELECT 	a.*,
						$decrypt_fields
				FROM 	%s a 
				JOIN 	%s b 
					ON 	a.to_user = b.user_id
				WHERE 	1 = 1
				$add_where
				ORDER 	BY a.sms_notification_queue_id
				$limit_str
";
			$query 		= sprintf( $query, 
				SYSAD_Model::CORE_TABLE_SMS_NOTIFICATION_QUEUES,
				SYSAD_Model::CORE_TABLE_USERS
			);

			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val, TRUE, !$single );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function insert_sms_queues( array $val )
	{
		$id 		= NULL;

		try
		{
			$id 	= $this->insert_data( SYSAD_Model::CORE_TABLE_SMS_NOTIFICATION_QUEUES, $val, TRUE, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function update_sms_queues( array $val, array $where )
	{
		try
		{
			$this->update_data( SYSAD_Model::CORE_TABLE_SMS_NOTIFICATION_QUEUES, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function delete_sms_queues( array $where )
	{
		try
		{
			$this->delete_data( SYSAD_Model::CORE_TABLE_SMS_NOTIFICATION_QUEUES, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
}