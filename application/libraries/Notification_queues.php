<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notification_queues
{
	protected $CI;
	protected $date_now;

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->date_now 			= date('Y-m-d H:i:s');

		$this->CI->load->model('Sms_notification_queues_model', 'sms_q_mod');
		$this->CI->load->model('Email_notification_queues_model', 'email_q_mod');

		$this->CI->load->library('Sms/Sms_api');
	}

	public function get_notifications_for_send( $notification_type = NULL, $single = FALSE )
	{
		$notification_queues 	= array();

		try
		{
			$where 				= array(
				'sent_flag' 	=> ENUM_NO
			);

			$sms 					= array();
			$emails 				= array();

			if( !EMPTY( $notification_type ) )
			{
				if( $notification_type == NOTIFICATION_TYPE_EMAIL )
				{
					$emails 		= $this->CI->email_q_mod->get_email_notification_queue( $where, $single );
				}
				else if( $notification_type == NOTIFICATION_TYPE_SMS )
				{
					$sms 			= $this->CI->sms_q_mod->get_sms_notification_queue( $where, $single );
				}
			}
			else
			{
				$sms 				= $this->CI->sms_q_mod->get_sms_notification_queue( $where, $single );
				$emails 			= $this->CI->email_q_mod->get_email_notification_queue( $where, $single );
			}

			$notification_queues_arr 		= array(
				NOTIFICATION_TYPE_EMAIL 	=> $emails,
				NOTIFICATION_TYPE_SMS 		=> $sms
			);

			if( !EMPTY( $notification_type ) )
			{
				if( ISSET( $notification_queues_arr[ $notification_type ] ) )
				{
					$notification_queues 	= $notification_queues_arr[ $notification_type ];
				}
			}
			else
			{
				$notification_queues 		= $notification_queues_arr;
			}
		}
		catch( PDOException $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch( Exception $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}	

		return $notification_queues;
	}

	public function default_notification_data()
	{
		$email_data 		= array();
		$sms_data 			= array();
		$template_data 		= array();

		try
		{
			$sys_logo 				 	= get_setting(GENERAL, "system_logo");
			$system_logo_src 			= base_url() . PATH_IMAGES . "logo_white.png";

			if( !EMPTY( $sys_logo ) )
			{
				$root_path 				= get_root_path();
				$sys_logo_path 			= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
				$sys_logo_path 			= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

				if( file_exists( $sys_logo_path ) )
				{
					$system_logo_src 	= base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
					$system_logo_src 	= @getimagesize($system_logo_src) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
				}
			}

			$system_title 	= get_setting(GENERAL, "system_title");
			$system_email 	= get_setting(GENERAL, "system_email");

			$email_data["from_email"] 	= $system_email;
			$email_data["from_name"] 	= $system_title;


			$template_data['logo']			= $system_logo_src;
			$template_data['system_name']	= $system_title;
		}
		catch( PDOException $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch( Exception $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}	

		return array( 
			'email_data' 	=> $email_data,
			'sms_data' 		=> $sms_data,
			'template_data'	=> $template_data

		);
	}

	public function process_multi_data( array $to_user, $message, $from_user, $notification_type, array $notif_data = array() )
	{
		$val 			= array();

		try
		{
			if( !EMPTY( $to_user ) )
			{
				$default_data 			= $this->default_notification_data();

				foreach( $to_user as $key => $user )
				{
					$val[$key]['to_user']		= $user;
						
					$val[$key]['from_user']		= $from_user;
					$val[$key]['sent_flag']		= ENUM_NO;
					$val[$key]['message']		= $message;
					$val[$key]["created_date"] 	= $this->date_now;

					if( $notification_type == NOTIFICATION_TYPE_EMAIL )
					{
						if( !EMPTY( $notif_data ) )
						{
							$notif_data 		= array_merge( $notif_data, $default_data['email_data'] );
						}
						else
						{
							$notif_data 		= $default_data['email_data'];
						}

						$val[$key]['email_data'] = json_encode( $notif_data );
					}

					if( $notification_type == NOTIFICATION_TYPE_SMS )
					{
						if( !EMPTY( $notif_data ) )
						{
							$notif_data 		= array_merge( $notif_data, $default_data['sms_data'] );
						}
						else
						{
							$notif_data 		= $default_data['sms_data'];
						}

						$val[$key]['sms_data'] = json_encode( $notif_data );
					}
				}
			}
		}
		catch( PDOException $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch( Exception $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}	

		return $val;
	}

	public function insert_email_to_multi_user(array $to_user, $message, $from_user, array $email_data = array() )
	{
		try
		{
			$val 			= $this->process_multi_data( $to_user, $message, $from_user, NOTIFICATION_TYPE_EMAIL, $email_data );

			if( !EMPTY( $val ) )
			{
				$this->CI->email_q_mod->insert_email_queues( $val );
			}
		}
		catch(PDOException $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function insert_sms_to_multi_user(array $to_user, $message, $from_user, array $sms_data = array() )
	{
		try
		{
			$val 			= $this->process_multi_data( $to_user, $message, $from_user, NOTIFICATION_TYPE_SMS, $sms_data );

			if( !EMPTY( $val ) )
			{
				$this->CI->sms_q_mod->insert_sms_queues( $val );
			}
		}
		catch(PDOException $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function insert_email_queues( $message, $from_user, $to_user, array $email_data = array() )
	{
		$val 						= array();

		try
		{
			$val['sent_flag']		= ENUM_NO;
				
			$val['message']			= $message;
			$val['from_user']		= $from_user;
			$val['to_user']			= $to_user;
			$val['created_date'] 	= $this->date_now;

			$default_data 			= $this->default_notification_data();

			if( !EMPTY( $email_data ) )
			{
				$email_data 		= array_merge( $email_data, $default_data['email_data'] );
			}
			else
			{
				$email_data 		= $default_data['email_data'];
			}

			$val['email_data'] 		= json_encode( $email_data );

			$this->CI->email_q_mod->insert_email_queues( $val );
		}
		catch( PDOException $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function insert_sms_queues( $message, $from_user, $to_user, array $sms_data = array() )
	{
		try
		{
			$val['sent_flag']		= ENUM_NO;
				
			$val['message']			= $message;
			$val['from_user']		= $from_user;
			$val['to_user']			= $to_user;
			$val['created_date'] 	= $this->date_now;

			$default_data 			= $this->default_notification_data();

			if( !EMPTY( $sms_data ) )
			{
				$sms_data 			= array_merge( $sms_data, $default_data );
			}
			else
			{
				$sms_data 			= $default_data;
			}

			$val['sms_data'] 		= json_encode( $sms_data );

			$this->CI->sms_q_mod->insert_sms_queues( $val );
		}
		catch( PDOException $e )
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	/**
	 * This function check if there is a e-mail message in queue then send it one by one
	 */
	public function start_email_queue_single()
	{
		try
		{
			$email_queue_details 	= $this->get_notifications_for_send(NOTIFICATION_TYPE_EMAIL, TRUE);

			if(!EMPTY($email_queue_details))
			{
				$this->_send_email($email_queue_details);
			}
		}
		catch(PDOException $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}
	
	/**
	 * This function check if there is a sms message in queue then send it one by one
	 */
	public function start_sms_queue_single()
	{
		try
		{
			$sms_queue_details 	= $this->get_notifications_for_send(NOTIFICATION_TYPE_SMS, TRUE);
			if(!EMPTY($sms_queue_details))
			{
				$this->_send_sms($sms_queue_details);
			}
		}
		catch(PDOException $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function start_email_queue_multi()
	{
		try
		{
			$email_queue_details 	= $this->get_notifications_for_send(NOTIFICATION_TYPE_EMAIL);

			if(!EMPTY($email_queue_details))
			{
				foreach( $email_queue_details as $email_queue_detail )
				{
					$this->_send_email($email_queue_detail);
				}
			}
		}
		catch(PDOException $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}
	

	public function start_sms_queue_multi()
	{
		try
		{
			$sms_queue_details 	= $this->get_notifications_for_send(NOTIFICATION_TYPE_SMS);
			if(!EMPTY($sms_queue_details))
			{
				foreach( $sms_queue_details as $sms_queue_details )
				{
					$this->_send_sms($sms_queue_details);
				}
			}
		}
		catch(PDOException $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	private function _send_email( array $email_to_send )
	{
		try
		{
			$default_data 	= $this->default_notification_data();

			$email_data 	= array();
			$template_data 	= $default_data['template_data'];

			if( !EMPTY( $email_to_send['email_data'] ) )
			{
				$email_data 	= (array) json_decode( $email_to_send['email_data'] );
			}

			if( ISSET( $email_data['email'] ) AND !EMPTY( $email_data['email'] ) )
			{
				if( is_array( $email_data['email'] ) )
				{
					$email_data['to_email']		= $email_data['email'];
				}
				else
				{
					$email_data['to_email']		= array( $email_data['email'] );
				}
			}
			else
			{
				$email_data['to_email']		= array( $email_to_send['email'] );
			}
			
			$template_data["message"]	= $email_to_send['message'];

			if( !EMPTY( $email_to_send['mname'] ) )
			{
				$fullname 				= $email_to_send['fname'].' '.$email_to_send['mname'].' '.$email_to_send['lname'];
			}
			else
			{
				$fullname 				= $email_to_send['fname'].' '.$email_to_send['lname'];
			}

			$template_data['fullname']	= $fullname;
			
			$flag 						= $this->CI->email_template->send_email_template($email_data, "emails/email_message", $template_data);

			$errors 					= $this->CI->email_template->get_email_errors();

			if(EMPTY( $errors ))
			{
				$this->update_email_queues( $email_to_send['email_notification_queue_id'] );
			}
			else
			{
				$this->update_email_queues( $email_to_send['email_notification_queue_id'], ENUM_NO );
			}

			if( !EMPTY( $errors ) )
			{
				RLog::info( "Email Error" ."\n" . var_export( $errors, TRUE ) . "\n" );
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	private function _send_sms( array $sms_to_send )
	{
		try
		{
			$sms_data 	= array();

			if( !EMPTY( $sms_to_send['sms_data'] ) )
			{
				$sms_data 	= (array) json_decode( $sms_to_send['sms_data'] );
			}

			$flag 	= $this->CI->sms_api->sendMessageToUser($sms_to_send['mobile_no'], $sms_to_send['message']);

			if($flag['check'] == 1)
			{
				$this->update_sms_queues( $sms_to_send['sms_notification_queue_id'] );
			}
			else
			{
				$this->update_sms_queues( $sms_to_send['sms_notification_queue_id'], ENUM_NO );
			}
			
		}
		catch(PDOException $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function update_email_queues( $email_notification_queue_id, $sent_flag = ENUM_YES )
	{
		try
		{
			$where 						= array(
				'email_notification_queue_id'	=> $email_notification_queue_id
			);

			$upd_val 					= array(
				'sent_flag'				=> $sent_flag
			);

			if( $sent_flag == ENUM_YES )
			{
				$upd_val['sent_date'] 	= $this->date_now;
				$upd_val['failed_flag'] = ENUM_NO;	
			}
			else
			{
				$upd_val['failed_flag'] = ENUM_YES;	
			}

			SYSAD_Model::beginTransaction();

			$this->CI->email_q_mod->update_email_queues( $upd_val, $where );	

			SYSAD_Model::commit();
		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}


	public function update_sms_queues( $sms_notification_queue_id, $sent_flag = ENUM_YES )
	{
		try
		{
			$where 						= array(
				'sms_notification_queue_id'	=> $sms_notification_queue_id
			);

			$upd_val 					= array(
				'sent_flag'				=> $sent_flag
			);

			if( $sent_flag == ENUM_YES )
			{
				$upd_val['sent_date'] 	= $this->date_now;
				$upd_val['failed_flag'] = ENUM_NO;	
			}
			else
			{
				$upd_val['failed_flag'] = ENUM_YES;	
			}

			SYSAD_Model::beginTransaction();

			$this->CI->sms_q_mod->update_sms_queues( $upd_val, $where );	

			SYSAD_Model::commit();
		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			Rlog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}
}