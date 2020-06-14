<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notification_cron extends SYSAD_Controller
{
	protected static $INIT_FLAG		= false; // indicator if this class is already loaded or not
	protected static $EMAIL_FLAG 	= false; // indicator if send_email() is currently sending email
	protected static $SMS_FLAG 		= false; // indicator if send_sms() is currently sending SMS

	public function __construct() 
	{
		// if INIT_FLAG is TRUE, it means this class
		// is already loaded
		if ( !EMPTY( static::$INIT_FLAG ) )
		{
			return;
		}

		static::$INIT_FLAG = true;

		parent::__construct();

		$this->load->library('Notification_queues');
		$this->load->model(CORE_SETTINGS.'/Site_settings_model', 'ssm');
		$this->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'usm');
	}

	public function delete_expired_dpa_user()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		try
		{
			$expired_user = $this->usm->get_expired_dpa_user();
			
			if( !EMPTY( $expired_user ) )
			{
				$ex_us_id = array_column($expired_user, 'user_id');

				$main_where = array();

				$main_where['user_id']	= array( 'IN', $ex_us_id );

				SYSAD_Model::beginTransaction();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ROLES;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_ROLES, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_ROLES, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_AGREEMENTS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_ANNOUNCEMENTS, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_GROUPS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_GROUPS, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_GROUPS, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_AUDIT_TRAIL;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_AUDIT_TRAIL, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_AUDIT_TRAIL, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_HISTORY;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_HISTORY, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_HISTORY, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $main_where);

				$curr_detail[] 		= array();

				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_USERS;
				$audit_action[] 	= AUDIT_DELETE;
				// $prev_detail[]  	= array();

				// $curr_det_u 	= $this->usm->get_user_details( $user_id );
				$prev_detail[]  	= $this->usm->get_details_for_audit(SYSAD_Model::CORE_TABLE_USERS, $main_where);

				$this->usm->delete_helper(SYSAD_Model::CORE_TABLE_USERS, $main_where);

				$curr_detail[] 	= array();

				if( !EMPTY( $audit_schema ) )
				{
					$audit_name 	= 'Users expired link';

					$audit_activity = sprintf( $this->lang->line('audit_trail_delete'), $audit_name);

					$this->audit_trail->log_audit_trail( $audit_activity, MODULE_USER, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
				}				

				SYSAD_Model::commit();
			}
		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();
			$msg 	= $this->get_user_message($e);
		}
		catch( Exception $e )
		{
			SYSAD_Model::rollback();
			$msg 	= $this->rlog_error($e, TRUE);
		}
	}

	public function run_every_one_sec()
	{
		try
		{
			$this->load->library('Ci_react');

			$loop   	= React\EventLoop\Factory::create();
			
			$loop->addPeriodicTimer(1, function()
			{
				$this->cron_one();
				$this->delete_expired_dpa_user();
			});

			$loop->run();
		}
		catch( PDOException $e )
		{
			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}
		catch( Exception $e )
		{
			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}	
	}

	public function cron_one()
	{
		try
		{
			$notification_cron 				 	= get_setting(NOTIFICATION_CRON, "notification_cron");
			
			if( !EMPTY( $notification_cron ) )
			{
				return;
			}

			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 1), 'notification_cron');

			$this->send_email(TRUE);
			$this->send_sms(TRUE);

			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
		}
		catch( PDOException $e )
		{
			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}
		catch( Exception $e )
		{
			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}	
	}

	public function cron()
	{
		try
		{
			$notification_cron 				 	= get_setting(NOTIFICATION_CRON, "notification_cron");

			if( !EMPTY( $notification_cron ) )
			{
				return;
			}

			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 1), 'notification_cron');

			$this->send_email();
			$this->send_sms();
			$this->delete_expired_dpa_user();

			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
			
		}
		catch( PDOException $e )
		{
			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}
		catch( Exception $e )
		{
			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}	
	}

	/**
	 * send_email()
	 * 		sends an e-mail message to user
	 * 
	 * 
	 */
	public function send_email( $single = FALSE )
	{
		// we need to make sure that only one email is 
		// being process by the server
		if ( !EMPTY( static::$EMAIL_FLAG ) )
		{
			// return;
		}

		try
		{
			static::$EMAIL_FLAG = true;

			if( $single )
			{
				$this->notification_queues->start_email_queue_single();
			}
			else
			{
				$this->notification_queues->start_email_queue_multi();
			}
			static::$EMAIL_FLAG = false;
		}
		catch( PDOException $e )
		{
			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
			static::$EMAIL_FLAG = true;

			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}
		catch( Exception $e )
		{
			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
			static::$EMAIL_FLAG = true;

			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}
	}

	/**
	 * send_sms() 
	 * 		sends an sms to user
	 * 
	 * NOTE: this function is called every 1 second.
	 */
	public function send_sms( $single = FALSE )
	{
		// we need to make sure that only one sms is
		// being process by the server
		if ( !EMPTY( static::$SMS_FLAG ) )
		{
			// return;
		}

		try
		{
		
			static::$SMS_FLAG = true;
			if( $single )
			{
				$this->notification_queues->start_sms_queue_single();
			}
			else
			{
				$this->notification_queues->start_sms_queue_multi();
			}
			
			static::$SMS_FLAG = false;
		}
		catch( PDOException $e )
		{
			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
			static::$SMS_FLAG = true;

			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}
		catch( Exception $e )
		{
			$this->ssm->update_settings(NOTIFICATION_CRON, array('notification_cron' => 0), 'notification_cron');
			static::$SMS_FLAG = true;

			$msg 	= $e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString();

			RLog::error($msg);
		}
	}
}