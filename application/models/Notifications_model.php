<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notifications_model extends SYSAD_Model {
	
	private $notifications;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->notifications = parent::CORE_TABLE_NOTIFICATIONS;
	}
    
	public function insert_notification($params)
	{
		try
		{
			$val = array(
				"notification" 		=> $params["notification"],
				"notified_by" 		=> $this->session->user_id,
				"notification_date" => date("Y-m-d H:i:s")
			);
			
			if(ISSET($params["notify_users"]))
			{
				$notify_users 			= implode(",",$params["notify_users"]);
				$val["notify_users"] 	= $notify_users;
			}	
				
			if(ISSET($params["notify_orgs"]))
			{
				$notify_orgs 		= implode(",",$params["notify_orgs"]);
				$val["notify_orgs"] = $notify_orgs;
			}	
				
			if(ISSET($params["notify_roles"]))
			{
				$notify_roles 			= implode(",",$params["notify_roles"]);
				$val["notify_roles"] 	= $notify_roles;
			}
			
			$this->insert_data($this->notifications, $val);
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	public function get_notifications($user_id, $user_roles, $user_orgs, $page = 0, $limit = 10, array $params = array(), array $filter = array(), array $ordering = array() )
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$order 			= "";
		$filter_str 	= "";
		$filter_params	= array();

		try
		{
			if( !EMPTY( $params ) AND !EMPTY( $filter ) )
			{
				$where 				= $this->filtering( $filter, $params, TRUE );

				$filter_str 		= $where['search_str'];

				$filter_params 		= $where['search_params'];
			}

			if( !EMPTY( $params ) AND !EMPTY( $ordering ) )
			{
				//$order 	= $this->ordering( $ordering, $params );
				$order 		= " ORDER BY a.notification_date DESC ";
				$limit 		= $this->paging($params);
			}
			else
			{
				if( !EMPTY( $page ) )
				{
					$page 	= $page * $limit;
				}

				$limit 		= " LIMIT $page, $limit ";

				$order 		= " ORDER BY a.notification_date DESC ";
			}

			if( EMPTY( $user_id ) AND EMPTY( $user_orgs ) AND EMPTY( $user_roles ) )
			{
				return array();
			}

			if( !EMPTY( $user_id ) )
			{
				$add_where 		.= ' ( ';

				if( is_array( $user_id ) )
				{
					foreach( $user_id as $u_id )
					{
						$add_where 		.= " FIND_IN_SET(?, a.notify_users) OR ";
						$extra_val[]	= $u_id;
					}

					$add_where 	= ' ( '.rtrim( $add_where, ' OR ' ).' ) ';

					$add_where 	.= ' OR ';
					$add_where  .= ' a.notify_users IN ( '.rtrim(str_repeat('?,', count($user_id)), ',').' ) ';
					$extra_val 	= array_merge( $extra_val, $user_id );
				}
				else
				{
					$add_where 		.= " FIND_IN_SET(?, a.notify_users ) ";
					$extra_val[]	= $user_id;

					$add_where 	.= ' OR ';
					$add_where  .= ' a.notify_users = ? ';

					$extra_val[] = $user_id;
				}

				$add_where 	.= ' ) OR ';
			}

			if( !EMPTY( $user_roles ) )
			{
				$add_where 		.= ' ( ';

				if( is_array( $user_roles ) )
				{
					foreach( $user_roles as $u_r )
					{
						$add_where 		.= " FIND_IN_SET(?, a.notify_roles) OR ";
						$extra_val[]	= $u_r;
					}

					$add_where 	= ' ( '.rtrim( $add_where, ' OR ' ).' ) ';

					$add_where 	.= ' OR ';
					$add_where  .= ' a.notify_roles IN ( '.rtrim(str_repeat('?,', count($user_roles)), ',').' ) ';
					$extra_val 	= array_merge( $extra_val, $user_roles );
				}
				else
				{
					$add_where 		.= " FIND_IN_SET(?, a.notify_roles ) ";
					$extra_val[]	= $user_roles;

					$add_where 	.= ' OR ';
					$add_where  .= ' a.notify_roles = ? ';

					$extra_val[] = $user_roles;
				}

				$add_where 	.= ' ) OR ';
			}

			if( !EMPTY( $user_orgs ) )
			{
				$add_where 		.= ' ( ';

				if( is_array( $user_orgs ) )
				{
					foreach( $user_orgs as $u_o )
					{
						$add_where 		.= " FIND_IN_SET(?, a.notify_orgs) OR ";
						$extra_val[]	= $u_o;
					}

					$add_where 	= ' ( '.rtrim( $add_where, ' OR ' ).' ) ';

					$add_where 	.= ' OR ';
					$add_where  .= ' a.notify_orgs IN ( '.rtrim(str_repeat('?,', count($user_orgs)), ',').' ) ';
					$extra_val 	= array_merge( $extra_val, $user_orgs );
				}
				else
				{
					$add_where 		.= " FIND_IN_SET(?, a.notify_orgs ) ";
					$extra_val[]	= $user_orgs;

					$add_where 	.= ' OR ';
					$add_where  .= ' a.notify_orgs = ? ';

					$extra_val[] = $user_orgs;
				}

				$add_where 	.= ' ) OR ';
			}


			$add_where 	= rtrim( $add_where, ' OR ' );

			$query 		= "

				SELECT	SQL_CALC_FOUND_ROWS a.*
				FROM 	%s a
				WHERE 	( 
					$add_where
				)
				$filter_str
				$order
				$limit
";

			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_NOTIFICATIONS );
			$val 		= array_merge( $val, $extra_val );
			$val 		= array_merge( $val, $filter_params );

			$result['aaData'] 	= $this->query( $query, $val);

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

	public function insert_notification_helper( array $val )
	{
		$id 	= NULL;

		try
		{
			$id = $this->insert_data($this->notifications, $val, TRUE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $id;
	}

	public function get_notifications_socket()
	{
		$result 		= array();
		$val 			= array();

		try
		{
			$query 		= "
				SELECT 	a.notification_id,
						a.notification,
						a.notification_html,
						a.notify_users, a.notify_orgs, a.notify_roles,
						a.notified_by, a.notification_date, a.read_date, a.module_code,
						a.displayed_socket_flag
				FROM 	%s a
				WHERE 	a.displayed_socket_flag = ?
				ORDER 	BY a.notification_date DESC
				LIMIT 	1
";

			$query 		= sprintf( $query, $this->notifications );

			$val[] 		= ENUM_NO;

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $result;
	}

	public function update_displayed_flag( $notification_id )
	{
		$val 			= array();

		try
		{
			$query 		= "
				UPDATE 	%s
				SET 	displayed_socket_flag = ?
				WHERE 	notification_id = ?
";
			$val[] 		= ENUM_YES;
			$val[] 		= $notification_id;

			$query 		= sprintf( $query, $this->notifications );

			$this->query( $query, $val, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}

	public function update_notification( $user_id, $user_roles, $user_orgs )
	{
		$val 			= array();
		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			if( !EMPTY( $user_id ) )
			{
				$add_where 		.= ' ( ';

				if( is_array( $user_id ) )
				{
					foreach( $user_id as $u_id )
					{
						$add_where 		.= " FIND_IN_SET(?, notify_users) OR ";
						$extra_val[]	= $u_id;
					}

					$add_where 	= ' ( '.rtrim( $add_where, ' OR ' ).' ) ';

					$add_where 	.= ' OR ';
					$add_where  .= ' notify_users IN ( '.rtrim(str_repeat('?,', count($user_id)), ',').' ) ';
					$extra_val 	= array_merge( $extra_val, $user_id );
				}
				else
				{
					$add_where 		.= " FIND_IN_SET(?, notify_users ) ";
					$extra_val[]	= $user_id;

					$add_where 	.= ' OR ';
					$add_where  .= ' notify_users = ? ';

					$extra_val[] = $user_id;
				}

				$add_where 	.= ' ) OR ';
			}

			if( !EMPTY( $user_roles ) )
			{
				$add_where 		.= ' ( ';

				if( is_array( $user_roles ) )
				{
					foreach( $user_roles as $u_r )
					{
						$add_where 		.= " FIND_IN_SET(?, notify_roles) OR ";
						$extra_val[]	= $u_r;
					}

					$add_where 	= ' ( '.rtrim( $add_where, ' OR ' ).' ) ';

					$add_where 	.= ' OR ';
					$add_where  .= ' notify_roles IN ( '.rtrim(str_repeat('?,', count($user_roles)), ',').' ) ';
					$extra_val 	= array_merge( $extra_val, $user_roles );
				}
				else
				{
					$add_where 		.= " FIND_IN_SET(?, notify_roles ) ";
					$extra_val[]	= $user_roles;

					$add_where 	.= ' OR ';
					$add_where  .= ' notify_roles = ? ';

					$extra_val[] = $user_roles;
				}

				$add_where 	.= ' ) OR ';
			}

			if( !EMPTY( $user_orgs ) )
			{
				$add_where 		.= ' ( ';

				if( is_array( $user_orgs ) )
				{
					foreach( $user_orgs as $u_o )
					{
						$add_where 		.= " FIND_IN_SET(?, notify_orgs) OR ";
						$extra_val[]	= $u_o;
					}

					$add_where 	= ' ( '.rtrim( $add_where, ' OR ' ).' ) ';

					$add_where 	.= ' OR ';
					$add_where  .= ' notify_orgs IN ( '.rtrim(str_repeat('?,', count($user_orgs)), ',').' ) ';
					$extra_val 	= array_merge( $extra_val, $user_orgs );
				}
				else
				{
					$add_where 		.= " FIND_IN_SET(?, notify_orgs ) ";
					$extra_val[]	= $user_orgs;

					$add_where 	.= ' OR ';
					$add_where  .= ' notify_orgs = ? ';

					$extra_val[] = $user_orgs;
				}

				$add_where 	.= ' ) OR ';
			}

			$add_where 	= rtrim( $add_where, ' OR ' );

			$query 		= "
				UPDATE 	%s
				SET 	read_date = ?
				WHERE 
				( 
					$add_where
				)
";
			$val[] 		= date('Y-m-d H:i:s');
			$val 		= array_merge( $val, $extra_val );

			$query 		= sprintf( $query, $this->notifications );
			
			$this->query( $query, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}
}
