<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notifications extends Base_Controller 
{
	private $module;
	private $table_id;
	private $path;
	private $module_js;

	public function __construct()
	{
		$this->table_id		= 'notifications_table';
		$this->path 		= 'Notifications/get_notifications_list';

		$this->module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/notifications/notifications";

		parent::__construct();
	}

	public function index( $system )
	{
		$data 		= array();
		$resources 	= array();

		try
		{
			$resources['load_css'] 	= array(CSS_DATATABLE_MATERIAL, CSS_SELECTIZE);
			$resources['load_js'] 	= array(JS_DATATABLE, JS_DATATABLE_MATERIAL, $this->module_js);
			$resources['datatable'] = array(
				'table_id'			=> $this->table_id, 'path' => $this->path,
				'succ_callback'		=> 'Notifications.message_text();',
				'length_change'		=> FALSE,
				'display_length'	=> '25',
				'sort'				=> FALSE,
				'with_search'		=> FALSE,
				'info'				=> FALSE
			);

			//$this->template->load('notifications', $data, $resources, $system);
			
			$this->load->view('notifications', $data);
			$this->load_resources->get_resource($resources);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg  	= $this->rlog_error($e);	

			$this->error_index( $msg );
		}
	}

	public function get_notifications()
	{
		$html 		= '';
		$unread 	= 0;

		$orig_params 	= get_params();

		try
		{
			$params 	= $this->set_filter( $orig_params )
								->filter_number('page')
								->filter();


			$details 	= $this->template->get_notifications_scroll($params['page']);

			$html 		= $details['html'];
			$unread 	= $details['unread'];
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		$response 		= array(
			'html' 		=> $html,
			'unread' 	=> $unread
		);

		echo json_encode( $response );
	}

	public function get_notifications_list()
	{
		$flag 				= 0;
		$msg 				= "";
		$row 				= array();
		$output 			= array();
		$resources 			= array();

		try
		{
			$params 					= get_params();

			$filter 		= array(
				'a-notification', 'a-notification_date'
			);

			$ordering 		= array(
				'a.notification', 'a.notification_date'
			);

			$user_id 		= $this->session->user_id;
			$user_roles 	= $this->session->user_roles;
			$user_orgs 		= $this->session->current_org_code;

			$result 		= $this->notifications_model->get_notifications( $user_id, $user_roles, $user_orgs, 0, 10, $params, $filter, $ordering);

			$output['sEcho'] 				= ( ISSET( $params['sEcho'] ) ) ? $params['sEcho'] : 0;
			$output['iTotalRecords'] 		= count($result['aaData']);
			$output['aaData'] 				= array();
			$output['iTotalDisplayRecords']	= $result['filtered_length']['filtered_length'];

			if(!EMPTY($result))
			{
				$cnt 			= 0;

				$count_adata 	= count($result['aaData']);

				$html 			= '<ul class="collection dt-notif">';
				$previous_dt	= '';
				
				foreach( $result['aaData'] as $r )
				{
					$cnt++;
					$date = date_format( date_create( $r['notification_date'] ), 'F d, Y' );
					$label = $r['notification'];
					$class = EMPTY($r['read_date']) ? 'item-unread' : 'item-read';
					
					$notification_date = ($date == date('F d, Y')) ? findTimeAgo($r['notification_date']) : date_format( date_create( $r['notification_date'] ), 'h:ia' );
				/*	if( EMPTY( $r['read_date'] ) )
					{
						$label 		= '<b class="black-text text-darken-2">'.$r['notification'].'</b>';
						$class_lbs 	= 'notification-span-dt';
					}*/

					$msg   		= $r['notification'];
					
					if($previous_dt!== $date)
					{
						$date_txt =  ($date == date('F d, Y')) ? "Today" : $date;
						$html.= '<li class="collection-header">'.$date_txt.'</li>';
					}
					
					$html 		.= '<li class="collection-item avatar '.$class.'">';
					$html 		.= $msg;
					$html 		.= '<span class="mute">'.$notification_date.'</span>';
					$html 		.= '</li>';

					/*$html 		.= '<li class="collection-item avatar">';
					$html 		.= '<i class="material-icons circle light-blue darken-2">swap_horiz</i>';

					$html 		.= '<span class="title '.$class_lbs.'" data-id="'.$r['notification_id'].'" onclick="Notifications.update_notify(event)">';
					$html 		.= $label;
					$html 		.= '</span>';

					$html 		.= '<p><span class="mute font-xs">'.$notification_date.'</span></p>';

					$html 		.= '</li>';*/
					
					$previous_dt= $date;
				}

				$html 			.= '</ul>';

				$output['iTotalRecords'] 	= $cnt;

				$row[] 		= array(
					$html
				);
			}

			$flag 							= 1;
		}
		catch(PDOException $e)
		{
			
			$this->rlog_error( $e );

			$msg 			= $this->get_user_message( $e );
		}
		catch(Exception $e)
		{
		
			$this->rlog_error( $e );

			$msg 			= $e->getMessage();
		}
		
		$output['aaData'] 	= $row;
		$output['flag']		= $flag;
		$output['msg']		= $msg;
		
		echo json_encode($output);
	}

	public function update_notification()
	{
		$flag 	= 0;
		$msg 	= "";

		$status 	= ERROR;

		try
		{
			$params 					= get_params();

			SYSAD_Model::beginTransaction();

			$this->notifications_model->update_notification( $params['user_id'], $params['user_roles'], $params['org_code'] );

			SYSAD_Model::commit();

			$status 	= SUCCESS;

			$flag 		= 1;
			$msg 		= $this->lang->line('data_updated');
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			$msg = $this->rlog_error($e, TRUE);
		}	

		$response 		= array(
			'flag' 		=> $flag,
			'msg' 		=> $msg
		);

		echo json_encode( $response );
	}

}