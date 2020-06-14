<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notify 
{

	protected $date_now;
	
	public function __construct()
	{
		$this->date_now 			= date('Y-m-d H:i:s');

		$this->CI =& get_instance();
		$this->CI->load->model('notifications_model');
	}
	
	/**
	 * $notification - notification message.
	 * $notify_who - 2 dimensional array consisting the following indexes 'notify_users', 'notify_orgs', 'notify_roles'.
	 * 				second array is array('-1') if all.
	 */
	
	public function insert_notification($notification, $notify_who)
	{
		try
		{
			$params = array("notification" => $notification);
			
			foreach ($notify_who as $k => $v):
				if(!EMPTY($notify_who[$k]))
					$params[$k] = $notify_who[$k];
			endforeach;
				
			$this->CI->notifications_model->insert_notification($params);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
		
	}

	public function insert_notification_helper_multi_user( $system = SYSTEM_CORE, $message, array $val )
	{
		$id 		= array();

		try
		{
			if( ISSET( $val['notify_users'] ) AND !EMPTY( $val['notify_users'] ) )
			{
				foreach( $val['notify_users'] as $users )
				{
					$val['notify_users']	= $users;

					$ins_ids 				= $this->insert_notification_helper( $system, $message, $val );
					
					$id 					= array_merge( $id, $ins_ids );
				}
			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}

		return $id;
	}

	public function insert_notification_helper( $system = SYSTEM_CORE, $message, array $val )
	{
		$id 		= array();
		
		try
		{
			$model 	= array(
				SYSTEM_CORE 	=> $this->CI->notifications_model
			);
			
			if( ISSET( $model[ $system ] ) )
			{
				$icon_color 	= 'light-blue darken-2';
				$icon 			= 'swap_horiz';
				$link 			= '#';

				if( ISSET( $val['icon_color'] ) AND !EMPTY( $val['icon_color'] ) )
				{
					$icon_color = $val['icon_color'];

					unset( $val['icon_color'] );
				}

				if( ISSET( $val['icon'] ) AND !EMPTY( $val['icon'] ) )
				{
					$icon 		= $val['icon'];

					unset( $val['icon'] );
				}

				if( ISSET( $val['link'] ) AND !EMPTY( $val['link'] ) )
				{
					$link 		= $val['link'];
				}

				$icons_str 		= "<i class='material-icons circle ".$icon_color."'>".$icon."</i>";

				$mess_str 		= $icons_str;

				if( $link != '#' )
				{
					$mess_str 		.= "
						<p><a target='_blank' style='color:white !important; cursor: pointer !important;' data-timestamp='".$this->date_now."' href='".$link."'>".$message."</a></p>
";
				}
				else
				{
					$mess_str 		.= "
						<p><a style='color:white !important; cursor: pointer !important;' data-timestamp='".$this->date_now."' href='#'>".$message."</a></p>
";
				}

				if( ISSET( $val['custom_message'] ) )  
				{
					$mess_str 		= $val['custom_message'];
				}

				$toast 				= $mess_str;

				if( ISSET( $val['custom_message_toast'] ) )  
				{
					$toast 			= $val['custom_message_toast'];
				}

				$val 	= array(
					'notification'		=> $mess_str,
					'notification_html'	=> $toast,
					'notify_users'	=> $val['notify_users'],
					'notified_by'		=> $val['from_user'],
					'notification_date'	=> $this->date_now,
					'module_code'		=> $val['module_code']
				);

				$mod 	= $model[ $system ];

				$id[]  	= $mod->insert_notification_helper($val);

			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}

		return $id;
	}
	
}