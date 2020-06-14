<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Template {
	
	var $template_data = array();

	private $date_now;

	public function __construct()
	{
		$this->date_now = date('Y-m-d H:i:s');

		$this->CI =& get_instance();
		$this->CI->load->model('Notifications_model', 'nm');

		$this->CI->load->model(CORE_ANNOUNCEMENTS.'/Announcements_model', 'am_notif');
	}

	/*
	 * @param	string	$value	Value to test for serialized form
	 * @param	mixed	$result	Result of unserialize() of the $value
	 * @return	boolean	True if $value is serialized data, otherwise false
	 */
	public function is_serialized($value, &$result = null)
	{
		// Bit of a give away this one
		if (!is_string($value))
		{
			return false;
		}
		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;')
		{
			$result = false;
			return true;
		}
		$length	= strlen($value);
		$end	= '';
		if( ISSET($value[0]) )
		{
			switch ($value[0])
			{
				case 's':
					if ($value[$length - 2] !== '"')
					{
						return false;
					}
				case 'b':
				case 'i':
				case 'd':
					// This looks odd but it is quicker than isset()ing
					$end .= ';';
				case 'a':
				case 'O':
					$end .= '}';
					if ($value[1] !== ':')
					{
						return false;
					}
					switch ($value[2])
					{
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
						case 8:
						case 9:
						break;
						default:
							return false;
					}
				case 'N':
					$end .= ';';
					if ($value[$length - 1] !== $end[0])
					{
						return false;
					}
				break;
				default:
					return false;
			}
		}
		if (($result = @unserialize($value)) === false)
		{
			$result = null;
			return false;
		}
		return true;
	}
	
	private function _set($name, $value)
	{
		if( $name == 'resources' )
		{
			$real_v 	= array();

			if( is_array( $value ) )
			{

				foreach( $value as $key => $val )
				{
					if( is_array( $val ) )
					{
						foreach( $val as $k => $v )
						{
							$check_serial 		= $this->is_serialized($v);

							if( $check_serial )
							{
								$uns_v 			= unserialize($v);
								$real_v[$key] 	= array_merge( $real_v[$key], $uns_v );
							}
							else
							{
								if( is_numeric( $k ) )
								{
									$real_v[$key][]		= $v;
								}
								else
								{
									$real_v[$key][$k]	= $v;
								}
							}
						}
					}
					else
					{
						$check_serial_s 	= $this->is_serialized($val);

						if( $check_serial_s )
						{
							$uns_v_s 		= unserialize($val);
							$real_v 		= $uns_v_s;
						}
						else
						{
							$real_v[$key]	= $val;
						}
					}
					
				}

				$this->template_data[$name] = $real_v;
			}
			else
			{
				$this->template_data[$name] = $value;
			}
			
		}
		else
		{
			$this->template_data[$name] = $value;
		}
	}
	
	public function load($view = '' , $data = array(), $resources = array(), $system = SYSTEM_CORE, $override_system_folder = NULL, $print = FALSE)
	{   
		try 
		{
			$user_id 	= $this->CI->session->user_id;
			$user_roles = $this->CI->session->user_roles;
			$user_orgs 	= $this->CI->session->org_code;

			$contents 	= $this->CI->load->view($view, $data, TRUE);
			$this->_set('contents', $contents);			

			$notifications = $this->CI->nm->get_notifications($user_id, $user_roles, $user_orgs);

			$announcement_det 		= $this->CI->am_notif->get_all_user_announcements_not_read($user_id, NULL);

			if( !EMPTY( $announcement_det ) )
			{
				$this->_construct_announcements_list($announcement_det);
			}

			if( !EMPTY( $notifications ) )
			{
				$this->_construct_notification_list( $notifications ); 
			}

			if(!EMPTY($resources))
			{
				$this->_set('resources', $resources);			
			}

			$this->CI->load->model(CORE_USER_MANAGEMENT.'/Organizations_model', 'org_temp_mod', TRUE);

			$org_detail 	= $this->CI->org_temp_mod->get_system_owner();

			if( !EMPTY( $org_detail ) )
			{
				$this->template_data['org_sys_owner']	 		= $org_detail['system_owner'];
				$this->template_data['org_template_logo']		= $org_detail['logo'];
				
			}

			$current_system 	= $this->CI->session->current_system;
			$curr_system 		= SYSAD;

			if( !EMPTY( $current_system ) )
			{
				$curr_system 	= $current_system;
			}

			if( !EMPTY( $override_system_folder ) )
			{
				$curr_system 	= $override_system_folder;
			}

			$this->template_data['curr_system']		= $curr_system;
			$this->template_data['ROOT_PATH'] 		= get_root_path();

			if( $print )
			{
				echo $this->CI->load->view('template_'.$system, $this->template_data, $print);
			}
			else
			{
				$this->CI->load->view('template_'.$system, $this->template_data);
			}
		}
		catch( PDOException $e )
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}
		catch(Exception $e)
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}	
	}	

	private function _construct_notification_list( array $notifications )
	{
		$html    = '';
		$unread  = 0;

		try
		{
			/* For notifications */
			
			if( ! EMPTY($notifications['aaData']))
			{
				foreach($notifications['aaData'] as $val)
				{
					//COUNT UNREAD NOTIFICATIONS
					if( EMPTY($val['read_date'] ) ) 
					{
						$unread++;
					}
					
					$msg   		= $val['notification'];
					$time  		= findTimeAgo($val['notification_date']);
					$date  		= $val['notification_date'];
					
					$class = 'title';

					$extra  = '';

					if( EMPTY( $link ) )
					{
						$href  = '';
					}

					$html .= '<li data-notif="'.$val['notification_id'].'" class="collection-item avatar" onclick="Socket_notification.read_notification(this);">';
					$html .= $msg;
					$html .= '<span class="mute font-xs">'.$time.'</span>';
					$html .= '</li>';
				}
			}
			
			$this->template_data['notif_list']	 = $html;
			$this->template_data['unread_notif'] = $unread;
		}
		catch( PDOException $e )
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}
		catch(Exception $e)
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}	

		return array(
			'html' 		=> $html,
			'unread' 	=> $unread
		);

	}

	private function _construct_announcements_list( array $announcements )
	{
		$html    = '';
		$unread  = 0;

		try
		{
			/* For notifications */
			
			if( ! EMPTY($announcements))
			{
				$icons_str 		= "<i class='material-icons circle blue'>announcement</i>";

				foreach($announcements as $val)
				{
					//COUNT UNREAD NOTIFICATIONS
					if( !ISSET( $val['read_flag'] ) OR $val['read_flag'] == ENUM_NO ) 
					{
						$unread++;
					}
					
					$msg   		= html_entity_decode( $val['description'] );

					if( !EMPTY( $val['created_date'] ) )
					{
						$time  		= findTimeAgo($val['created_date']);
					}
					else
					{
						$time  		= findTimeAgo($val['modified_date']);	
					}

					$mess_str 		= "
						<p><a style='color:white !important; cursor: pointer !important;' data-timestamp='".$this->date_now."' href='#'>".$msg."</a></p>
";

					// $date  		= $val['notification_date'];
					
					$class = 'title';

					$extra  = '';

					if( EMPTY( $link ) )
					{
						$href  = '';
					}

					$html .= '<li data-announcement="'.$val['announcement_id'].'" class="collection-item avatar">';

					$html .= $icons_str;


					$html .= $mess_str;
					$html .= '<span class="mute font-xs">'.$time.'</span>';
					$html .= '</li>';
				}
			}
			
			$this->template_data['announcements_list']	 = $html;
			$this->template_data['unread_announcements'] = $unread;
		}
		catch( PDOException $e )
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}
		catch(Exception $e)
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}	

		return array(
			'html' 		=> $html,
			'unread' 	=> $unread
		);

	}

	public function get_notifications_scroll( $page = 0 )
	{
		$html    = '';
		$unread  = 0;

		try
		{
			$user_id 	= $this->CI->session->user_id;
			$user_roles = $this->CI->session->user_roles;
			$user_orgs 	= $this->CI->session->current_org_code;

			$notifications = $this->CI->nm->get_notifications($user_id, $user_roles, $user_orgs, $page);

			$details 		= $this->_construct_notification_list($notifications);

			$html 			= $details['html'];
			$unread 		= $details['unread'];
		}
		catch( PDOException $e )
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}
		catch(Exception $e)
		{
			$message = $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();;
			
			RLog::error($message);

			throw $e;
		}

		return array(
			'html' 		=> $html,
			'unread' 	=> $unread
		);
	}
		
}