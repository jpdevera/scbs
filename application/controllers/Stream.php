<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stream extends SYSAD_Controller 
{
	public function __construct()
	{
		parent::__construct();

		// $this->load->model('Notifications_model', 'nm');

		$this->load->model(CORE_ANNOUNCEMENTS.'/Announcements_model', 'am');
	}

	public function get_announcements()
	{
		/*header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');

		$msg 						= 'success';
		$check 						= 0;
		$announcement 				= array();

		try
		{
			$user_id 				= $this->session->user_id;

			$announcement_det 		= $this->am->get_all_user_announcements_not_read($user_id);

			if( !EMPTY( $announcement_det ) )
			{
				$announcement 		= $announcement_det;

				foreach( $announcement_det as $key => $ann )
				{
					$announcement[$key]['description_decode']	= html_entity_decode( $ann['description'] );
				}
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);
		}

		$data 			= array(
			'check'		=> $check,
			'announcements'	=> $announcement,
			'user_id'	=> $this->session->user_id,
			'msg'		=> $msg
		);


		echo "event: announcement\n";
		echo 'data: ' . json_encode($data) . "\n\n";
		echo PHP_EOL;
		ob_flush();
		flush();*/
	}

	public function update_user_announcement()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$add  					= FALSE;

		$main_where 			= array();

		$upd_val 				= array();

		try
		{
			$params 		 	= $orig_params;

			SYSAD_Model::beginTransaction();

			$upd_val['read_flag'] = ENUM_YES;

			$main_where['user_id']			= $params['user_id'];
			$main_where['announcement_id'] 	= $params['announcement_id'];

			$this->am->update_user_announcements( $upd_val, $main_where );


			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					=$this->lang->line( 'data_saved' );
		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status' 				=> $status
		);

		echo json_encode( $response );
	}

	/*public function logout()
	{
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');

		$msg 						= 'success';
		$check 						= 0;

		try
		{
			$check_logout_users 	= $this->auth_model->check_logout_users($this->session->user_id);

			if( !EMPTY( $check_logout_users ) AND !EMPTY( $check_logout_users['check_logout_users'] ) )
			{
				$check 				= 1;
			}

		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);
		}

		
		$data 			= array(
			'check'		=> $check,
			'user_id'	=> $this->session->user_id,
			'msg'		=> $msg
		);

		echo 'data: ' . json_encode($data) . "\n\n";
		echo PHP_EOL;
		ob_flush();
		flush();

	}

	public function notification()
	{
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');

		$msg 						= 'success';
		$notifications_arr 			= array();

		try
		{
			$user_id 	= $this->session->user_id;
			$user_roles = $this->session->user_roles;
			$user_orgs 	= $this->session->org_code;

			$params 	= array(
				'listed_flag'				=> array( ENUM_NO, array('=', 'OR', '(') ),
				'displayed_socket_flag'		=> array( ENUM_NO, array('=', ')') )
			);

			$notifications = $this->nm->get_notifications($user_id, $user_roles, $user_orgs, 0, 10, $params);

			if( ISSET( $notifications['aaData'] ) )
			{
				$notifications_arr = $notifications['aaData'];

				if( !EMPTY( $notifications_arr ) )
				{
					$notification_ids 	= array_column($notifications_arr, 'notification_id');

					$upd_val 			= array(
						'listed_flag'	=> ENUM_YES,
						'displayed_socket_flag'	=> ENUM_YES
					);

					$upd_where 			= array(
						'notification_id'	=> array( 'IN', $notification_ids )
					);

					$this->nm->update_helper( SYSAD_Model::CORE_TABLE_NOTIFICATIONS, $upd_val, $upd_where );
				}
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);
		}

		$data 			= array(
			'msg'		=> $msg,
			'notifications'	=> $notifications_arr
		);

		echo 'data: ' . json_encode($data) . "\n\n";
		echo PHP_EOL;
		ob_flush();
		flush();
	}

	public function todo()
	{
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');

		$msg 				= 'success';
		$todo_arr 			= array();

		try
		{
			$user_id 	= $this->session->user_id;
			$user_roles = $this->session->user_roles;
			$user_orgs 	= $this->session->org_code;

			$params 	= array(
				'listed_flag'				=> array( ENUM_NO, array('=', 'OR', '(') ),
				'displayed_socket_flag'		=> array( ENUM_NO, array('=', ')') )
			);

			$todos 		= $this->nm->get_to_do_list($user_id, $user_roles, $user_orgs, 0, 10, $params);

			if( ISSET( $todos['aaData'] ) )
			{
				$todo_arr 	= $todos['aaData'];

				if( !EMPTY( $todo_arr ) )
				{
					$to_do_ids 	= array_column($todo_arr, 'to_do_id');

					$upd_val 			= array(
						'listed_flag'	=> ENUM_YES,
						'displayed_socket_flag'	=> ENUM_YES
					);

					$upd_where 			= array(
						'to_do_id'	=> array( 'IN', $to_do_ids )
					);

					$this->nm->update_helper( SYSAD_Model::CORE_TABLE_TO_DOS, $upd_val, $upd_where );
				}
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);
		}

		$data 			= array(
			'msg'		=> $msg,
			'todos'	=> $todo_arr
		);

		echo 'data: ' . json_encode($data) . "\n\n";
		echo PHP_EOL;
		ob_flush();
		flush();
	}*/
}