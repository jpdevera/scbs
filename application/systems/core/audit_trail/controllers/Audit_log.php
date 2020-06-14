<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Audit_log extends SYSAD_Controller 
{
	
	private $module;

	protected $valid_report_format 	= array(
		REPORT_TYPE_PDF,
		REPORT_TYPE_EXCEL
	);

	private $dt_options 	= array();

	private $table_id;
	private $path;

	private $archive_per 	= FALSE;
	
	public function __construct()
	{
		parent::__construct();

		$this->table_id 				= 'audit_log_table';
		$this->path 					= CORE_AUDIT_TRAIL.'/Audit_log/get_audit_log';
		
		$this->module = MODULE_AUDIT_TRAIL;
		
		$this->load->model('audit_log_model', 'audit_log', TRUE);
		$this->load->model(CORE_COMMON.'/systems_model', 'systems', TRUE);

		$this->dt_options 	= array(
			'table_id' 	=> $this->table_id, 
			'path'	 	=> $this->path, 
			'advanced_filter'	=> true, 
			'with_search' => true,
			'order' 			=> 3, 
			'sort_order' 		=> 'desc'
		);

		$this->archive_per 	= $this->permission->check_permission($this->module, ACTION_ARCHIVE);
	}
	
	public function index()
	{	
		$checked_arc = '';
		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);

			$arc 	= $this->session->archived_sess;

			if( !EMPTY( $arc ) )
			{
				$checked_arc 	= 'checked';
			}

			$data 				= array();
			$resources 			= array();
			$module_js 			= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_AUDIT_TRAIL."/audit_log";
			
			$data['systems'] 	= $this->systems->get_systems();

			$id 						= base64_url_encode($this->module);
			$salt 						= gen_salt();
			$token                      = in_salt($this->module, $salt);

			$data["id"]						= $id;
			$data["token"]					= $token;
			$data["salt"]					= $salt;
			$data['checked_arc'] 			= $checked_arc;
			
			$resources['load_css'] 	= array(CSS_LABELAUTY, CSS_DATETIMEPICKER, CSS_DATATABLE_MATERIAL, CSS_SELECTIZE);
			$resources['load_js'] 	= array(JS_LABELAUTY, JS_DATETIMEPICKER, JS_DATATABLE, JS_DATATABLE_MATERIAL, $module_js);

			$datatable_options 		= $this->dt_options;

			$json_datatable_options = json_encode( $datatable_options );
		
			$resources['datatable'] = $datatable_options;
			$resources['loaded_init'] = array(
				"Auditlog.initForm('".$json_datatable_options."');",
				"refresh_datatable('".$json_datatable_options."');",
				'materialize_select_init();'
			);
			$resources['load_materialize_modal'] = array (
				'modal_auditlog' 		=> array (
					'title' 			=> "View audit log details",
					'module' 			=> CORE_AUDIT_TRAIL,
					'controller'		=> __CLASS__,
					'permission'		=> false
				)
			);

			$data['archive_per'] 	 = $this->archive_per;
			
			$this->template->load('audit_log', $data, $resources);
		}
		catch(PDOException $e)
		{			
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);

			$this->error_index( $msg );
		}
	}
	
	public function get_audit_log($system_code = NULL)
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();

		try
		{
			// $this->redirect_off_system($this->module);
			$params 	= get_params();
			$resources 	= array();
			
			if(!IS_NULL($system_code))
			{
				$params["system_code"] = $system_code;
			}
			
			$cnt 		= 0;

			$fname 		= aes_crypt('B.fname', FALSE, FALSE);
			$lname 		= aes_crypt('B.lname', FALSE, FALSE);

			$arc 	= $this->session->archived_sess;

			$params['checked_arc']	= $arc;
		
			$aColumns 	= array("A.*", "B.photo", "DATE_FORMAT(activity_date,'%m/%d/%Y %T') activity_date", "IF(A.user_id = 0, ".$fname." ,CONCAT(".$fname.",' ',".$lname.")) name", "C.module_name","CAST(".$fname." as char(100) ) as fname" );
			$bColumns 	= array("name", "module_name", "activity", "A.activity_date", "ip_address");
		
			$audit_log 		= $this->audit_log->get_audit_log_list($aColumns, $bColumns, $params);
			$iTotal 		= $this->audit_log->total_length();
			$iFilteredTotal = $this->audit_log->filtered_length($aColumns, $bColumns, $params);
		
			$output['aaData']				= array();
			$output['sEcho'] 				= intval($params['sEcho']);
			$output['iTotalRecords'] 		= $iTotal["cnt"];
			$output['iTotalDisplayRecords']	= $iFilteredTotal["cnt"];

			$view_per 			= $this->permission->check_permission($this->module, ACTION_VIEW);

			$audit_cnt 			= count($audit_log);

			$root_path 			= $this->get_root_path();
		
			foreach($audit_log as $arow)
			{
				$cnt++;
				$row 			= array();
				$actions 		= '<div class="table-actions">';
			
				$audit_log_id 	= $arow["audit_trail_id"];
				$id 			= base64_url_encode($audit_log_id);
				$salt 			= gen_salt();
				$token 			= in_salt($audit_log_id, $salt);			
				$url 			= $id."/".$salt."/".$token;

				$img_src 		= base_url().PATH_IMAGES . "avatar.jpg";

				$photo_path 	= "";

				if( !EMPTY( $arow['photo'] ) )
				{
					$photo_path = $root_path.PATH_USER_UPLOADS.$arow['photo'];
					$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);

					if( file_exists( $photo_path ) )
					{
						$img_src = output_image($arow['photo'] ,PATH_USER_UPLOADS);
					}
					else
					{
						$photo_path = "";
					}
				}

				$avatar = '<span class="table-avatar-wrapper">';

				if( !EMPTY( $photo_path ) )
				{
					$avatar 	.= '<img class="avatar" width="20" height="20" src="'.$img_src.'" /> ';
				}
				else
				{
					$avatar 	.= '<img class="avatar default-avatar" data-name="'.$arow["fname"].'" class="demo" /> ';
				}

				$avatar.='</span>';
				
				if( $view_per )
				{
					$actions .= "<a href='#modal_auditlog' class='modal_auditlog_trigger tooltipped' data-tooltip='View' data-position='bottom' data-delay='50' onclick=\"modal_auditlog_init('".$url."')\"><i class='material-icons'>search</i></a>";
				}
				
				$actions .= '</div>';
				
				if($cnt == $audit_cnt)
				{
					$resources['preload_modal'] = array("modal_auditlog");
					$resources['loaded_init'] 	= array("selectize_init();");
					$actions .= $this->load_resources->get_resource($resources, TRUE);
				}

				$rows[] = array(
					$avatar.$arow["name"],
					$arow["module_name"],
					$arow["activity"],
					$arow["activity_date"],
					$arow["ip_address"],
					$actions
				);	
				
			}
			
			$flag 	= 1;
		}	
		catch(PDOException $e)
		{
			$msg =  $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);
		}

		$output['aaData']			= $rows;
		$output['flag']				= $flag;
		$output['msg']				= $msg;
			
		echo json_encode($output);
	}
	
	public function modal($id = NULL, $salt = NULL, $token = NULL){
		
		try
		{	
			// $this->redirect_off_system($this->module);

			$data 	= array();
			
			if(!IS_NULL($id))
			{
				$id = base64_url_decode($id);
				
				// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
				check_salt($id, $salt, $token);

				$arc 	= $this->session->archived_sess;
			
				$data["audit_trail_id"] 	= $id;
				$data["audit_trail"] 		= $this->audit_log->get_audit_log($id, $arc);
				$data["audit_trail_detail"] = $this->audit_log->get_audit_log_details($id, $arc);
			}

			$data['obj']				= $this;

			$resources['loaded_init']	= array('Auditlog.initModal();');
			
			$this->load->view("modals/audit_log", $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg = $this->get_user_message($e);

			$this->error_modal( $msg );
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			$this->error_modal( $msg );
		}	
	}

	public function validate_print()
	{
		$flag 					= 0;

		$report 				= NULL;
		$main 					= NULL;
		$salt 					= NULL;
		$token 					= NULL;
		$action 				= NULL;

		$msg 					= '';

		try
		{
			$orig_params 		= get_params();

			$params				= $this->set_filter( $orig_params )
									// ->filter_number( 'main', TRUE )
									->filter_number( 'report', TRUE )
									->filter();
									
			// check_salt( $params['main'], $params['salt'], $params['token'], $params['action'] );

			if( !in_array( $params['report'], $this->valid_report_format ) )
			{
				throw new Exception( 'Invalid report type.' );
			}

			if( ISSET( $params['date_from'] ) AND !EMPTY( $params['date_from'] ) 
				AND ISSET( $params['date_to'] ) AND !EMPTY( $params['date_to'] )
			)
			{
				$start_date 	= date_create( $params['date_from'] );
				$end_date 	 	= date_create( $params['date_to'] );

				$date_diff 		= date_diff( $start_date, $end_date );

				$date_form 		= $date_diff->format("%R%a days");

				if( $date_form < 0 )
				{
					throw new Exception( 'Start date should be earlier or the same as the End Date.' );
				}
			}

			$report 			= $params['report'];
			/*$main 				= $params['main'];
			$salt 				= $params['salt'];
			$token 				= $params['token'];
			$action 			= $params['action'];*/

			$flag 				= 1;

		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
			// $msg 	= base64_url_encode($msg);
			$this->rlog_error( $e );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);
			// $msg 	= base64_url_encode($msg);
			$this->rlog_error( $e );
		}

		$response 				= array(
			'flag'				=> $flag,
			'msg'				=> $msg
		);

		if( $flag )
		{
			$response['report']	= $report;
			/*$response['main']	= $main;
			$response['salt']	= $salt;
			$response['token']	= $token;
			$response['action']	= $action;*/
		}

		echo json_encode( $response );

	}

	public function print_form()
	{
		$orig_params 		= get_params();
		$data 				= array();
		$styles 			= "";

		try
		{
			// $this->redirect_off_system($this->module);
			
			$par 				= $this->set_filter( $orig_params )
									// ->filter_number( 'main', TRUE )
									->filter_number( 'report' );
									

			if( ISSET( $orig_params['date_from'] ) AND !EMPTY( $orig_params['date_from'] ) )
			{
				$par->filter_date('date_from');
			}

			if( ISSET( $orig_params['date_to'] ) AND !EMPTY( $orig_params['date_to'] ) )
			{
				$par->filter_date('date_to');
			}

			$params 			= $par->filter();

			$type 				= $params['report'];

			$user_id 			= filter_var( $this->session->user_id, FILTER_SANITIZE_NUMBER_INT );

			$params['user_id']	= $user_id;

			$arc 	= $this->session->archived_sess;

			$params['checked_arc']	= $arc;

			$fname 		= aes_crypt('B.fname', FALSE, FALSE);
			$lname 		= aes_crypt('B.lname', FALSE, FALSE);

			$aColumns 	= array("A.*", "B.photo", "DATE_FORMAT(activity_date,'%m/%d/%Y %T') activity_date", "IF(A.user_id = 0, ".$fname." ,CONCAT(".$fname.",' ',".$lname.")) name", "C.module_name","CAST(".$fname." as char(100) ) as fname" );
			$bColumns 	= array("name", "module_name", "activity", "activity_date", "ip_address");
		
			$audit_log 			= $this->audit_log->get_audit_log_list($aColumns, $bColumns, $params);

			$styles 			= $this->load->view( 'styles/styles', array(), TRUE );
			
			$data['form_list']	= $audit_log;

			$data['styles']		= $styles;

			$data['is_excel']	= ( strtolower( $type ) == REPORT_TYPE_EXCEL ) ? TRUE : FALSE;

			$view 			    = $this->load->view('reports/audit_log', $data, TRUE);
			$file_name 			= 'AUDIT_TRAIL_'.date('F').'_'.date('d').'_'.date('Y');

			$activity 			= "downloaded the audit trail logs";

			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module 
			);
			
			switch( strtolower( $type ) )
			{
				case REPORT_TYPE_EXCEL:
					$this->generate_excel( $view, $file_name );
				break;
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch(Exception $e)
		{
			$msg 	= $this->rlog_error($e, TRUE);

			$this->error_index( $msg );
		} 
	}

	protected function generate_excel( $html, $file_name )
	{
		$this->convert_excel( $html, $file_name, 'Audit Trail' );
	}

	private function _filter( array $orig_params )
	{
		$par 			= $this->set_filter( $orig_params )
							->filter_date('date_archived_from')
							->filter_date('date_archived_to')
							;

		$params 		= $par->filter();
		
		return $params;

	}

	private function _validate( array $params = array(), $forJs = FALSE )
    {
    	try
    	{
    		$v 						= $this->core_v;

    		$v 	
    			->required()
    			->date('Y-m-d')
    			->check('date_archived_from', $params);

    		$v 	
    			->required()
    			->date('Y-m-d')
    			->check('date_archived_to', $params);

    		$start_date 	= date_create( $params[ 'date_archived_from' ] );
			$end_date 	 	= date_create( $params[ 'date_archived_to' ] );
			
			$date_diff 		= date_diff( $start_date, $end_date );

			$date_form 		= $date_diff->format("%R%a days");

			if( $date_form < 0 )
			{
				throw new Exception( 'Date Archived from'.' should be earlier or the same as the '.'Date Archived to' );
			}

    		if( $forJs )
			{
				return $v->getClientSide();
			}
			else
			{
				$v->assert(FALSE);
			}

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

	public function archive()
	{
		$status 				= ERROR;
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		try
		{
			$params 		 	= $this->_filter( $orig_params );

			$permission 		= $this->archive_per;
			$per_msg 			= $this->lang->line( 'err_unauthorized_archive' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}

			$this->_validate( $params );
			
			SYSAD_Model::beginTransaction();

			$this->audit_log->insert_select_audit_trail($params['date_archived_from'], $params['date_archived_to']);

			$this->audit_log->insert_select_audit_trail_detail($params['date_archived_from'], $params['date_archived_to']);


			$this->audit_log->delete_select_audit_trail_detail($params['date_archived_from'], $params['date_archived_to']);

			$this->audit_log->delete_select_audit_trail($params['date_archived_from'], $params['date_archived_to']);

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= 'Records successfully archived';

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
			'status' 				=> $status,
			'datatable_options'		=> $this->dt_options,
			// 'datatable_id'			=> $this->table_id
		);

		echo json_encode( $response );
	}

	public function set_archived()
	{
		$params = get_params();

		if( ISSET($params['archived']) AND !EMPTY( $params['archived'] ) )
		{
			$this->session->set_userdata('archived_sess', 1);
			echo json_encode(array('status' => SUCCESS));
		}
		else
		{
			$this->session->unset_userdata('archived_sess');
			echo json_encode(array('status' => SUCCESS));
		}
	}
}