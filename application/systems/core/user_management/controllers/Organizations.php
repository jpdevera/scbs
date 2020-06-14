<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Organizations extends SYSAD_Controller 
{

	const MAXFILESIZE 			= '89128960 ';
	const DEFAULT_ORG_LEVEL 	= 1;

	public $hierarchy_msg_map 	= array(); // Msg mag for hierarchy
	
	private $module;
	private $table_id;
	private $path;

	private $view_per 			= FALSE;
	private $edit_per 			= FALSE;
	private $add_per 			= FALSE;
	private $delete_per 		= FALSE;
	private $download_per 		= FALSE;
	private $import_per 		= FALSE;

	private $date_now;
	private $dt_options 		= array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->module 					= MODULE_ORGANIZATION;
		$this->table_id 				= 'organizations_table';
		$this->path 					= CORE_USER_MANAGEMENT.'/organizations/get_organization_list';
		
		$this->load->model('organizations_model', 'orgs');

		$this->hierarchy_msg_map 		= array(
			Organizations_model::DESCENDANTS 		=> '',
			Organizations_model::ANCESTORS 			=> '',
			Organizations_model::SIBLINGS 			=> '',
		); // Msg mag for hierarchy;

		$this->date_now 		= date('Y-m-d H:i:s');

		$this->view_per 		= $this->permission->check_permission( $this->module, ACTION_VIEW );
		$this->edit_per 		= $this->permission->check_permission( $this->module, ACTION_EDIT );
		$this->add_per 			= $this->permission->check_permission( $this->module, ACTION_ADD );
		$this->delete_per 		= $this->permission->check_permission( $this->module, ACTION_DELETE );
		$this->download_per 	= $this->permission->check_permission( $this->module, ACTION_DOWNLOAD );
		$this->import_per 		= $this->permission->check_permission( $this->module, ACTION_IMPORT );

		$this->dt_options 	= array(
			'table_id' 	=> $this->table_id, 
			'path'	 	=> $this->path, 
			'advanced_filter'	=> true, 
			'with_search' => true
		);
	}
	
	public function index()
	{	
		$data 		= array();
		$resources 	= array();
		$module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_USER_MANAGEMENT."/organizations";

		try
		{
			// $this->redirect_off_system($this->module);
			$this->redirect_module_permission($this->module);
			
			$resources['load_css'] 	= array(CSS_DATATABLE_MATERIAL, CSS_SELECTIZE);
			$resources['load_js'] 	= array(JS_DATATABLE, JS_DATATABLE_MATERIAL, $module_js);

			$datatable_options 		= $this->dt_options;
			$resources['datatable'] = $datatable_options;

			$resources['load_materialize_modal'] 	= array (
			    'modal_organizations' 				=> array (
					'title' 		=> "Create an organization",
					'size' 			=> "md lg-h",
					'module' 		=> CORE_USER_MANAGEMENT,
					'controller' 	=> __CLASS__
			    ),
			    'modal_org_import' 					=> array(
			    	'title' 		=> "Import Organizations",
			    	'size' 			=> "sm",
					'module' 		=> CORE_USER_MANAGEMENT,
					'method'		=> 'modal_org_import',
					'controller'	=> __CLASS__,
					'custom_button'	=> array(
						BTN_SAVE => array("type" => "button", "action" => BTN_SAVING)
					),
					'post' 			=> true
			    ),
			    'modal_error_import'	=> array(
			    	'title'			=> 'Import Error',
			    	'size' 			=> "full",
			    	'module' 		=> CORE_USER_MANAGEMENT,
					'method'		=> 'modal_error_import',
					'controller'	=> __CLASS__,
					'post' 			=> true,
					'permission' 	=> false
			    )
			);

			$data['add_per'] 		  = $this->add_per;
			$data['import_per']		  = $this->import_per;
			$data['download_per']	  = $this->download_per;

			$json_datatable_options   = json_encode( $datatable_options );

			$resources['loaded_init'] = array(
				'Organizations.init_obj();',
				'materialize_select_init();',
				"refresh_datatable('".$json_datatable_options."')"
			);
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);	

			$this->error_index( $msg );
		}
		
		$this->template->load('organizations', $data, $resources);
	}

	public function get_organization_list($sector_code=NULL)
	{
		$rows 					= array();
		$flag 					= 0;
		$msg 					= '';
		$output  				= array();

		$resources 				= array();

		try
		{
			// $this->redirect_off_system($this->module);
			
			$params 			= get_params();
				
			$aColumns 			= array('A.org_code', 'A.org_parent', 'A.name', 'A.website', 'A.email', 'GROUP_CONCAT(b.org_code) as parent_orgs', 'GROUP_CONCAT(d.name SEPARATOR "<br/>") as parent_name', 'IF(A.status = "Y", "Active", "Inactive") as status');
			$bColumns 			= array('name', 'parent_name', 'email', 'status');
				
			$organizations 		= $this->orgs->get_org_list($aColumns, $bColumns, $params, $sector_code);
			$iFilteredTotal 	= $this->orgs->filtered_length($aColumns, $bColumns, $params);
			$iTotal 			= $this->orgs->total_length();

			$output['aaData']				= array();
			$output['sEcho'] 				= intval($params['sEcho']);
			$output['iTotalRecords'] 		= $iTotal["cnt"];
			$output['iTotalDisplayRecords']	= $iFilteredTotal["cnt"];
			
			$keys 				= array_keys($organizations);
			$last_key 			= array_pop($keys);	

			$edit_per 			= $this->permission->check_permission($this->module, ACTION_EDIT);
			$delete_per 		= $this->permission->check_permission($this->module, ACTION_DELETE);

			if( !EMPTY( $organizations ) )
			{
			
				foreach($organizations as $key => $val)
				{
					$actions 		= '';
					$id  			= base64_url_encode($val['org_code']);
					$salt 			= gen_salt();
					$token 			= in_salt($val['org_code'], $salt);
							 
					$del_action 	= 'content_delete(\'Organization\', \''.$id.'\');';	
					$url 			= $id.'/'.$salt.'/'.$token;
					$actions 		.= '<div class="table-actions">';

					if( $this->view_per )
					{
						$view_url 	= $url.'/'.ACTION_VIEW.'/';
						$actions 	.= '<a href="#modal_organizations" class="modal_organizations_trigger tooltipped" data-tooltip="Edit" data-position="bottom" data-delay="50" onclick="modal_organizations_init(\''.$view_url.'\');" ><i class="material-icons">visibility</i></a>';
					}
					
					if( $edit_per )
					{
						$actions 	.= '<a href="#modal_organizations" class="modal_organizations_trigger tooltipped" data-tooltip="Edit" data-position="bottom" data-delay="50" onclick="modal_organizations_init(\''.$url.'\');" ><i class="material-icons">mode_edit</i></a>';
					}
					
					if( $delete_per )
					{
						$actions 	.= '<a href="javascript:;" onclick="'.$del_action.'" class="tooltipped" data-tooltip="Delete" data-position="bottom" data-delay="50"><i class="material-icons">delete</i></a>';
					}
					
					$actions 		.= '</div>';
					
					if($last_key == $key)
					{
						$resources['preload_modal'] = array("modal_organizations");
						$resources['loaded_init'] 	= array("selectize_init();");
						$actions 					.= $this->load_resources->get_resource($resources, TRUE);
					}

					/*$parent_search 	= array(
						'c.group_type_name', 'd.name'
					);

					$get_parents 	= $this->get_parents($val['org_code'], NULL, $parent_search, $params);

					$parent_str 	= '';

					if( !EMPTY( $get_parents ) )
					{
						$desc_arr 		= $this->process_descendants_arr($get_parents, 'group_type_name', 'parent_name');

						if( !EMPTY( $desc_arr ) )
						{
							foreach( $desc_arr as $grp => $desc )
							{
								$parent_str .= '<b>'.$grp.'</b>
								<br/>
									&nbsp;&nbsp;'.implode(', ', $desc).'
								<br/>
								<br/>
	';
							}
						}
					}*/
					
					$rows[] = array(
						$val['name'],
						$val['parent_name'],
						// $val['website'],
						$val['email'],
						$val['status'],
						$actions
					);
				}
			}

			$flag 	= 1;
		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);
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

	public function get_lazy_parent_orgs()
	{
		$options 		= array();
		$orig_params 	= get_params();

		try
		{
			$params 	= $orig_params;

			$index 		= 0;
			$keyword 	= '';

			if( ISSET( $params['index'] ) )
			{
				$index 	= $params['index'];
			}

			if( ISSET( $params['keyword'] ) )
			{
				$keyword = $params['keyword'];
			}

			$org_code 			= ( ISSET( $params['org_sel'] ) AND !EMPTY( $params['org_sel'] ) ) ? base64_url_decode($params['org_sel']) : NULL;

			$sel_val 			= ( ISSET( $params['sel_val'] ) ) ? $params['sel_val'] : array();
			$acts 				= $this->orgs->get_parent_orgs( $index, $keyword, $org_code );
			$acts 				= $this->process_parents($acts);

			if( !EMPTY( $acts ) )
			{
				foreach( $acts as $gm )
				{
					$id_gs 			= base64_url_encode($gm['org_code']);

					if( !EMPTY( $sel_val ) AND in_array($gm['tag_id'], $sel_val) )
					{
						continue;
					}
					
					$options[] 		= array(
		    			'text'		=> $gm['name'],
		    			'value' 	=> $id_gs,
		    			'org_parent_names'	=> $gm['org_parent_names']
		    		);
				}
			}

		}
		catch(PDOException $e)
		{			
			$msg = $this->get_user_message($e);

			// $this->error_modal($msg);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			// $this->error_modal($msg);
		}

		echo json_encode($options);
	}

	protected function process_parents(array $orgs = array())
	{

		try
		{
			if( !EMPTY( $orgs ) )
			{
				foreach( $orgs as $key => $o )
				{
					$name 		= '';

					if( !EMPTY( $o['org_parents'] ) )
					{
						$org_par_arr 	= explode(',', $o['org_parents']);

						foreach( $org_par_arr as $o_arr )
						{
							$name 	= $this->_process_parent_name($o_arr).'<br/>';
						}
					}
					$name 	= rtrim($name, ', </br>');

					$orgs[$key]['org_parent_names'] = $name;
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $orgs;
	}

	protected function _process_parent_name($org_code, $name = '')
	{

		try
		{
			$details 	= $this->orgs->get_orgs_details_with_parents($org_code);

			if( !EMPTY( $details['org_parents'] ) )
			{
				$org_par_arr 	= explode(',', $details['org_parents']);

				$name 		= $details['name'].', ';
				
				foreach( $org_par_arr as $o )
				{
					$name 	.= $this->_process_parent_name($o, $name);
				}
			}
			else
			{

				$name 	= $details['name'].', ';
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

		return $name;
	}
	
	public function modal($id=NULL, $salt=NULL, $token=NULL, $action = NULL)
	{
		$disabled 		= '';
		$disable_upl 	= false;
		$org_types 		= array();

		try
		{
			// $this->redirect_off_system($this->module);

			$sel_par_org = array();

			$data 			= array();
			$resources 		= array();
			$org_code 		= 0;

			$parent_org_dr  = array();

			$org_parents 	= array();
			
			if(!EMPTY($id) && !EMPTY($salt) && !EMPTY($token))
			{
				
				$org_code 		= base64_url_decode($id);
				
				// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
				check_salt($org_code, $salt, $token);
				
				$org_details 			= $this->orgs->get_org_details($org_code);
				
				$data['id'] 			= $id;
				$data['salt'] 			= $salt;
				$data['token'] 			= $token;
				$data['org_details'] 	= $org_details;

				$org_parents 			= $this->get_parents( $org_code );

				$sel_par_org_det 		= $this->orgs->get_selected_parent_organizations($org_code);
				
				if( !EMPTY( $sel_par_org_det ) )
				{
					$sel_par_org 		= array_column($sel_par_org_det, 'org_parent');
				}

			}

			if( $action == ACTION_VIEW )
			{
				$disabled 		= 'disabled';
				$disable_upl	= true;
			}
			
			$parent_org_dr 				= $this->orgs->get_parent_orgs(0, '', $org_code, $sel_par_org);			

			if( !EMPTY( $sel_par_org ) )
			{
				$tags_a = array_column($parent_org_dr, 'org_code');
				foreach( $sel_par_org as $t )
				{
					if( !in_array($t, $tags_a) )
					{
						$tags_d_det 	= $this->orgs->get_orgs_details_with_parents_many(array($t));
						// $tt_arr 		= array($tags_d_det);

						$parent_org_dr 			= array_merge($parent_org_dr, $tags_d_det);
					}
				}
			}

			$parent_org_dr 				= $this->process_parents($parent_org_dr);

			
			$data['other_orgs'] 		= $this->orgs->get_other_orgs($org_code);
			$data['org_parents'] 		= $org_parents;
			$data['parent_org_dr']		= $parent_org_dr;
			$data['sel_par_org'] 		= $sel_par_org;
			
			$resources['load_css'] 		= array(CSS_SELECTIZE, CSS_LABELAUTY);
			$resources['load_js'] 		= array(JS_SELECTIZE, JS_LAZY_SELECTIZE, JS_LABELAUTY, 'add_row');
			$resources['loaded_init'] 	= array(
				'Organizations.init_modal();',
				'Organizations.save();'
			);
			$resources['selectize'] 	= array (
				'selectize-orgs' 		=> array(
					'type' 				=> 'default'
				)
			);

			$resources['load_delete']	= array(
				'org_parent'			=> array(
					'delete_cntrl'		=> 'Organizations',
					'delete_method'		=> 'delete_org_parent',
					'delete_module'		=> CORE_USER_MANAGEMENT
				)
			);

			$resources['upload'] 		= array(
				'org_logo'				=> array(
					'path'					=> PATH_ORGANIZATION_UPLOADS,
					'allowed_types' 		=> IMAGE_EXTENSIONS,
					'show_preview'			=> 1,
					'default_img_preview'	=> 'image_preview.png',
					'max_file' 				=> 1,
					'multiple' 				=> 0,
					'successCallback'		=> "Organizations.successCallback(files,data,xhr,pd);",
					'auto_submit'			=> false,
					// 'max_file_size'			=> '13107200',
					'multiple_obj'			=> true,
					'show_download'			=> true,
					'drag_drop' 			=> false,
					'dont_delete_in_server' => true,
					'disable' 				=> $disable_upl
				)
			);

			$get_org_types 		= $this->orgs->get_org_types();

			$data['disabled_mod']	= $disabled;
			$data['org_types']		= $get_org_types;

			$this->load->view("modals/organizations", $data);
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
	
	/**
	 * Use This helper function to generate the query string for insert and values
	 * for org_paths table
	 *
	 *
	 * @param  $details -- required. detail of the organization, its parent and its org_paths 
	 * @param  $par_detail -- optional. additional value 
	 * 						if the following key or details is existing then it will override the details
	 * @param  $action -- required. default ACTION_ADD. what action
	 * @param  $check_lev_var -- required. default FALSE. if organization level is 1
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function process_org_path( array $details, array $par_detail = array(), $action = ACTION_ADD, $check_lev_var = FALSE )
	{
		$val 		= array();
		$str 		= '';

		if( !EMPTY( $details ) )
		{
			foreach( $details as $det )
			{
				$str 	.= '(?,?,?,?,?),';

				if( !EMPTY( $par_detail['org_code'] ) AND ISSET( $par_detail['org_code'] ) )
				{
					$val[] 	= $par_detail['org_code'];
				}
				else
				{
					$val[] 	= $det['org_code'];
				}

				if( !EMPTY( $par_detail['org_parent'] ) AND ISSET( $par_detail['org_parent'] ) )
				{
					$val[] 	= $par_detail['org_parent'];
				}
				else
				{
					$val[] 	= $det['org_parent'];
				}

				if( !EMPTY( $par_detail['group_type'] ) AND ISSET( $par_detail['group_type'] ) )
				{
					$val[] 	= $par_detail['group_type'];
				}
				else
				{
					$val[] 	= $det['group_type'];
				}

				if( $action != ACTION_ADD OR !EMPTY( $check_lev_var ) )
				{
					$val[] 	= ( $det['org_level'] - 1 );
				}
				else
				{
					$val[] 	= ( $det['org_level'] + 1 );
				}

				if( !EMPTY( $par_detail['org_root'] ) AND ISSET( $par_detail['org_root'] ) )
				{
					$val[] 	= $par_detail['org_root'];
				}
				else
				{
					$val[] 	= $det['org_root'];
				}
			}

			$str 		= rtrim( $str, ',' );
		}

		return array(
			'str'		=> $str,
			'val'		=> $val
		);
	}
	
	/**
	 * Use This helper function to insert the org paths per sub organization of the main organization selected
	 *
	 *
	 * @param  $details -- required. detail of the organization, its parent and its org_paths
	 * @param  $par_detail -- optional. additional value
	 * 						if the following key or details is existing then it will override the details
	 * @param  $action -- required. default ACTION_ADD. what action
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function do_process_insert_sub_org( array $details, array $audit_where, array $par_detail = array(), $action = ACTION_ADD )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{

			if( !EMPTY( $details ) )
			{
				foreach( $details as $det )
				{
					$str 		= '(?,?,?,?,?)';
					$val 		= array();

					if( !EMPTY( $par_detail['org_code'] ) AND ISSET( $par_detail['org_code'] ) )
					{
						$val[] 		= $par_detail['org_code'];
						$org_code 	= $par_detail['org_code'];
					}
					else
					{
						$val[] 		= $det['org_code'];
						$org_code 	= $det['org_code'];
					}

					if( !EMPTY( $par_detail['org_parent'] ) AND ISSET( $par_detail['org_parent'] ) )
					{
						$val[] 			= $par_detail['org_parent'];
						$par_org_code 	= $par_detail['org_parent'];
					}
					else
					{
						$val[] 			= $det['org_parent'];
						$par_org_code 	= $det['org_parent'];
					}

					if( !EMPTY( $par_detail['group_type'] ) AND ISSET( $par_detail['group_type'] ) )
					{
						$val[] 			= $par_detail['group_type'];
						$group_type 	= $par_detail['group_type'];
					}
					else
					{
						$val[] 			= $det['group_type'];
						$group_type 	= $det['group_type'];
					}

					if( $action != ACTION_ADD )
					{
						$val[] 		= ( $det['org_level'] - 1 );
						$org_level 	= ( $det['org_level'] - 1 );
					}
					else
					{
						$val[] 		= ( $det['org_level'] + 1 );
						$org_level 	= ( $det['org_level'] + 1 );
					}

					if( !EMPTY( $par_detail['org_root'] ) AND ISSET( $par_detail['org_root'] ) )
					{
						$val[] 		= $par_detail['org_root'];
						$org_root 	= $par_detail['org_root'];
					}
					else
					{
						$val[] 		= $det['org_root'];
						$org_root 	= $det['org_root'];
					}

					if( !EMPTY( $val ) )
					{
						$check_where 		= array(
							'org_code'		=> $org_code,
							'org_parent'	=> $par_org_code,
							'group_type'	=> $group_type,
							'org_level'		=> $org_level,
							'org_root'		=> $org_root
						);

						$check_exists 		= $this->orgs->check_org_path_exisits( $check_where );

						if( !EMPTY( $check_exists ) AND !EMPTY( $check_exists['check_exists'] ) )
						{
							continue;
						}

						$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORG_PATHS;
						$audit_schema[]		= DB_CORE;

						$prev_detail[] 		= array();
						$audit_action[] 	= AUDIT_INSERT;

						$this->orgs->insert_org_paths( $str, $val );

						$curr_detail[] 		= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORG_PATHS, $audit_where );
					}
				}
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

		return array(
			'audit_schema'			=> $audit_schema,
			'audit_table' 			=> $audit_table,
			'audit_action' 			=> $audit_action,
			'prev_detail'			=> $prev_detail,
			'curr_detail' 			=> $curr_detail
		);
	}
	
	/**
	 * Use This helper function to insert the org paths of the org root then update the its child organization
	 * org path usually used when the org root will change per group type 
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $action -- required. default ACTION_ADD. what action
	 * @param  $org_code_for_del -- optional. default value NULL
	 * 						if the following details is not empty then it will override the org_code parameter
	 * @param  $group_type -- optional. default value NULL. what is the group type of the main organization selected
	 * @param  $check_lev_var -- optional. default value FALSE. if the main organization selected is level 1
	 * @param  $org_det -- optional. default value array. detail main organization selected 						
	 * 
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function process_org_root_del_ins( $org_code, $action = ACTION_ADD, $org_code_for_del = NULL, $group_type = NULL, $check_lev_var = FALSE, $org_det = array() )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			$orig_org_code 		= $org_code;

			if( !EMPTY( $check_lev_var ) )
			{
				if( !EMPTY( $org_det ) )
				{
					$org_code 	= $org_det['org_root'];
				}
			}

			$root_childrens 	= $this->orgs->get_root_children( $org_code, $group_type );

			if( !EMPTY( $root_childrens ) )
			{
				$root_where 	= array(
					'org_root'	=> $org_code
				);

				if( $action == ACTION_DELETE )
				{
					$root_code['org_root']	= $org_code_for_del;
				}
				else
				{
					$org_code_where 				= array();
					$org_code_where['a.org_code'] 	= $org_code;

					$root_code 				= $this->orgs->get_org_path_details_helper( $org_code_where );
				}

				if( !EMPTY( $check_lev_var ) )
				{
					if( !EMPTY( $org_det ) )
					{
						$root_code['org_root'] 		= $orig_org_code;
					}

				}

				$root_str 				= '';
				$root_val 				= array();

				if( !EMPTY( $root_code ) )
				{
					$audit_action[]		= AUDIT_DELETE;
					$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORG_PATHS;
					$audit_schema[]		= DB_CORE;

					$prev_detail[] 		= $this->orgs->get_org_root_audit( $org_code, $group_type );
					
					$this->orgs->delete_root_helper( $org_code, $group_type );
					
					$curr_detail[] 		= array();

					$root_ins_det 		= $this->process_org_path( $root_childrens, array( 'org_root' => $root_code['org_root']  ), $action, $check_lev_var );

					$root_str 			= $root_ins_det['str'];
					$root_val 			= $root_ins_det['val'];
					
					if( !EMPTY( $root_str ) )
					{
						$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORG_PATHS;
						$audit_schema[]		= DB_CORE;

						$prev_detail[] 		= array();
						$audit_action[] 	= AUDIT_INSERT;

						$this->orgs->insert_org_paths( $root_str, $root_val );

						$curr_detail[] 		= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORG_PATHS, $root_where );
					}
				}
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

		return array(
			'audit_schema'			=> $audit_schema,
			'audit_table' 			=> $audit_table,
			'audit_action' 			=> $audit_action,
			'prev_detail'			=> $prev_detail,
			'curr_detail' 			=> $curr_detail
		);
	}

	/**
	 * Use This helper function to update the org paths of the child org of the main organization selected.
	 * Usually used when adding a parent to the main organization selected 
	 *
	 *
	 * @param  $params -- required. form fields
	 * @param  $org_code -- required. organization code
	 * @param  $action -- required. default ACTION_ADD. what action
	 * @param  $group_type -- optional. default value NULL. what is the group type of the main organization selected
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function process_sub_org_del_ins( array $params, $org_code, $action = ACTION_ADD, $group_type = NULL )
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			$not_parent_str 	= '';
			$group_str 			= '';
			$root_str 			= '';

			$not_par_val 		= array();
			$group_val 			= array();
			$root_val 			= array();

			if( ISSET( $params['org_parents'] ) AND !EMPTY( $params['org_parents'] ) 
				AND ISSET( $params['org_parents'][0] ) AND !EMPTY( $params['org_parents'][0] )
			)
			{
				foreach( $params['org_parents'] as $key => $par_org_code )
				{
					$clean_par 			= filter_var( base64_url_decode( $par_org_code ), FILTER_SANITIZE_STRING );

					$not_parent_str 	.= '?,';
					$not_par_val[] 		= $clean_par;

					$per_org_det 		= $this->orgs->get_root_org_code( $clean_par, $group_type );
					

					if( !EMPTY( $per_org_det ) )
					{
						foreach( $per_org_det as $s_k => $det )
						{
							$group_str 	.= '?,';
							$root_str 	.= '?,';

							$group_val[] = $det['group_type'];
							$root_val[]  = $det['org_root'];
						}
					}
					else
					{
						$root_str 	.= '?,';
						$root_val[]  = $clean_par;

						if( !EMPTY(  $group_type ) )
						{
							if( is_array( $group_type ) )
							{
								$count_as_type  = count( $group_type );

								$group_str 		.= str_repeat( '?,', $count_as_type );

								$group_val 		= array_merge( $group_val, $group_type );
							}
							else
							{
								$group_str 	.= '?,';
								$group_val[] = $group_type;
							}
						}
					}
				}

				$not_parent_str 		= rtrim( $not_parent_str, ',' );
				$group_str 				= rtrim( $group_str, ',' );
				$root_str 				= rtrim( $root_str, ',' );

			}

			if( !EMPTY( $not_parent_str ) AND !EMPTY( $group_str ) AND !EMPTY( $root_str ) )
			{
				$descendants 			= $this->orgs->get_descendants_update( $org_code, $not_parent_str, $not_par_val, $root_str, $root_val, $group_str, $group_val );
				
				if( !EMPTY( $descendants ) )
				{

					foreach( $descendants as $d_k => $desc )
					{
						$desc_str  				= '';
						$desc_val  				= array();

						$desc_where 			= array(
							'org_code'		=> $desc['org_code'],
							'group_type'	=> $desc['group_type']
						);

						$org_parent_det 	= $this->orgs->check_parent_org_has_parent( $desc['org_parent'], $desc['group_type'] );
						// var_dump($org_parent_det);
						
						$audit_action[]		= AUDIT_DELETE;
						$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORG_PATHS;
						$audit_schema[]		= DB_CORE;

						$prev_detail[] 		= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORG_PATHS, $desc_where );

						$this->orgs->delete_helper( SYSAD_Model::CORE_TABLE_ORG_PATHS, $desc_where );

						$curr_detail[] 		= array();
						
						if( !EMPTY( $org_parent_det ) )
						{
							/*$ins_sub_org_det 	= $this->process_org_path( $org_parent_det, $desc );
							$desc_str 		 	= $ins_sub_org_det['str'];
							$desc_val 			= $ins_sub_org_det['val'];*/

							$ins_sub_org_real 	= $this->do_process_insert_sub_org( $org_parent_det, $desc_where, $desc, $action );

							$audit_schema 		= array_merge( $audit_schema, $ins_sub_org_real['audit_schema'] );
							$audit_table 		= array_merge( $audit_table, $ins_sub_org_real['audit_table'] );
							$audit_action 		= array_merge( $audit_action, $ins_sub_org_real['audit_action'] );
							$prev_detail 		= array_merge( $prev_detail, $ins_sub_org_real['prev_detail'] );
							$curr_detail 		= array_merge( $curr_detail, $ins_sub_org_real['curr_detail'] );
						}

					/*	if( !EMPTY( $desc_str ) )
						{
							$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORG_PATHS;
							$audit_schema[]		= DB_CORE;

							$prev_detail[] 		= array();
							$audit_action[] 	= AUDIT_INSERT;

							$this->orgs->insert_org_paths( $desc_str, $desc_val );

							$curr_detail[] 		= $this->orgs->select_helper(
								array('*'), SYSAD_Model::CORE_TABLE_ORG_PATHS, $desc_where, TRUE
							);
						}*/
					}
				}
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

		return array(
			'audit_schema'			=> $audit_schema,
			'audit_table' 			=> $audit_table,
			'audit_action' 			=> $audit_action,
			'prev_detail'			=> $prev_detail,
			'curr_detail' 			=> $curr_detail
		);
	}

	protected function process_parent_organizations($org_code, array $params = array())
	{
		$val 	= array();

		try
		{	
			if( ISSET( $params['parent_organizations'] ) AND !EMPTY( $params['parent_organizations'] ) 
				AND ISSET( $params['parent_organizations'][0] ) AND !EMPTY( $params['parent_organizations'][0] )
			)
			{
				foreach( $params['parent_organizations'] as $key => $par_org )
				{
					$val[$key]['org_code']		= $org_code;
					$val[$key]['org_parent']	= $par_org;
				}
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

		return $val;
	}
	
	public function process()
	{
		$org_code 	= NULL;
		$org_dec 	= NULL;

		$security_detail 	= array();

		$path 				= NULL;
		$sess_org_code 	 	= NULL;

		try
		{
			// $this->redirect_off_system($this->module);

			$status 	= ERROR;
			$params 	= get_params();

			$params 	= $this->set_filter($params)
							->filter_string('parent_organizations', TRUE)
							->filter_number('organization_type', TRUE)
							->filter();
			
			// SERVER VALIDATION
			$this->_validate($params);
			
			// GET SECURITY VARIABLES
			$id			= $params['id'];
			$salt 		= $params['salt'];
			$token 		= $params['token'];			
			
			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();
			// print_r($params);
			$check_lev 	= array();
			
			if(!EMPTY($id) && !EMPTY($salt) && !EMPTY($token))
			{
				$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
				$audit_schema[]		= DB_CORE;

				$audit_action[] 	= AUDIT_UPDATE;
				
				$org_code 			= base64_url_decode($id);
					
				// CHECK IF THE SECURITY VARIABLES WERE CORRUPTED OR INTENTIONALLY EDITED BY THE USER
				check_salt($org_code, $salt, $token);
				
				// GET THE DETAIL FIRST BEFORE UPDATING THE RECORD
				$prev_org_upd 		= $this->orgs->get_org_details($org_code);
				$prev_detail[] 		= array($prev_org_upd);
				
				$this->orgs->update_org($params, $org_code);

				$msg = $this->lang->line('data_updated');
				
				// GET THE DETAIL AFTER UPDATING THE RECORD
				$curr_org_det 		= $this->orgs->get_org_details($org_code);
				$curr_detail[] 		= array($curr_org_det);

				if( EMPTY( $params['org_logo'] ) )
				{
					$main_where 		= array(
						'org_code'		=> $org_code
					);

					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
					$audit_action[] 	= AUDIT_UPDATE;
					$prev_detail[]  	= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
						$main_where
					);

					$upd_val['modified_by'] 	= $this->session->user_id;
					$upd_val['modified_date'] 	= $this->date_now;
					$upd_val['logo'] 			= NULL;
					$upd_val['logo_orig_name'] 	= NULL;

					$path 				= base_url().PATH_IMAGES.DEFAULT_ORG_LOGO;
					

					$this->orgs->update_helper( SYSAD_Model::CORE_TABLE_ORGANIZATIONS, $upd_val, $main_where );

					$curr_detail[] 		= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
						$main_where
					);
				}
				else
				{
					if( !EMPTY( $curr_org_det['logo'] ) )
					{
						$root_path 			= $this->get_root_path();
						$org_pic_path 		= $root_path. PATH_ORGANIZATION_UPLOADS . $curr_org_det['logo'];
						$org_pic_path 		= str_replace(array('\\','/'), array(DS,DS), $org_pic_path);
						
						if( file_exists( $org_pic_path ) )
						{
							$org_pic_src	= output_image( $curr_org_det['logo'], PATH_ORGANIZATION_UPLOADS );
							$path 	 	 	= $org_pic_src;
						}
					}
				}

				$sess_org_code 		= $this->session->org_code;
				$org_dec 			= $org_code;

				if( $curr_org_det['system_owner'] == ENUM_NO )
				{
					$sys_logo 		 = get_setting(GENERAL, "system_logo");

					if( !EMPTY( $sys_logo ) )
					{
						$root_path 			= $this->get_root_path();

						$sys_logo_path 		= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
						$sys_logo_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

						if( file_exists( $sys_logo_path ) )
						{

							$system_logo_src = output_image( $system_logo, PATH_SETTINGS_UPLOADS );
							$system_logo_src = @getimagesize($sys_logo_path) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";

							$path 			= $system_logo_src;
						}
						else
						{
							$path 			= base_url() . PATH_IMAGES . "logo_white.png";
						}
					}
				}

				$activity 				= "updated the details of organization ( %s ).";
				$activity 				= sprintf($activity, $params['org_name']);
			}
			else
			{
				$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
				$audit_schema[]		= DB_CORE;

				$prev_detail[] 		= array();
				$audit_action[] 	= AUDIT_INSERT;
				
				$org_code 			= $this->orgs->insert_org($params);
				$msg 				= $this->lang->line('data_saved');
				
				// GET THE DETAIL AFTER INSERTING THE RECORD
				$curr_detail[] 		= array($this->orgs->get_org_details($org_code));

				$activity 				= "created a new organization ( %s ).";
				$activity 				= sprintf($activity, $params['org_name']);
			}

			$security_detail 		= $this->generate_salt_token_arr( $org_code );

			if( !EMPTY( $org_code ) )
			{
				$par_org_val 	= $this->process_parent_organizations($org_code, $params);

				$par_org_where 	= array(
					'org_code'	=> $org_code
				);


				$audit_schema[] 	= DB_CORE;
				$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS;
				$audit_action[] 	= AUDIT_DELETE;
				$prev_detail[]  	= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS,
												$par_org_where
											 );

				$this->orgs->delete_helper(SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS, $par_org_where);

				$curr_detail[] 		= array();


				if( !EMPTY( $par_org_val ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->orgs->insert_helper(SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS, $par_org_val);

					$curr_detail[] 		= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS,
													$par_org_where
												 );
				}
			}
			
			// LOG AUDIT TRAIL
			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module, 
				$prev_detail, 
				$curr_detail, 
				$audit_action, 
				$audit_table,
				$audit_schema
			);
			
			SYSAD_Model::commit();
			$status 	= SUCCESS;
			
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();
			$msg 		= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			$msg 		= $this->rlog_error($e, TRUE);
		}

		$response 		= array(
			'status' 	=> $status,
			'msg' 		=> $msg,
			'datatable_options' => $this->dt_options,
			'sess_org_code'	=> $sess_org_code,
			'path' 			=> $path,
			'org_dec' 		=> $org_dec
		);

		if( !EMPTY( $security_detail ) )
		{
			$response['org_code'] 		= $security_detail['id_enc'];
			$response['org_salt'] 		= $security_detail['salt'];
			$response['org_token'] 		= $security_detail['token'];
		}
		
		echo json_encode( $response );
	}
	
	public function delete_organization()
	{
		try
		{
			// $this->redirect_off_system($this->module);

			$status 		= ERROR;
			$params 		= get_params();
			$org_code 		= base64_url_decode($params['param_1']);

			// BEGIN TRANSACTION
			SYSAD_Model::beginTransaction();

			$par_where 			= array();

			$par_where['org_code']	= $org_code;

			$audit_schema[] 	= DB_CORE;
			$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS;
			$audit_action[] 	= AUDIT_DELETE;
			$prev_detail[]  	= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS,
											$par_where
										 );

			$this->orgs->delete_helper(SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS, $par_where);

			$curr_detail[] 		= array();
			
			$audit_action[]		= AUDIT_DELETE;
			$audit_table[] 		= SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
			$audit_schema[]		= DB_CORE;
			
			// GET THE DETAIL FIRST BEFORE DELETING THE RECORD
			$prev_or 			= $this->orgs->get_org_details($org_code);
			$prev_detail[] 		= array($prev_or);
			
			$this->orgs->delete_org($org_code);


			$msg 				= $this->lang->line('data_deleted');
			
			$curr_detail[] 		= array();
			// ACTIVITY TO BE LOGGED ON THE AUDIT TRAIL
			$activity 			= "deleted an organization ( %s ).";
			$activity 			= sprintf($activity, $prev_or['short_name']);
				
			// LOG AUDIT TRAIL
			$this->audit_trail->log_audit_trail(
				$activity, 
				$this->module, 
				$prev_detail, 
				$curr_detail, 
				$audit_action, 
				$audit_table,
				$audit_schema
			);

			$root_path = $this->get_root_path();
			$path_dir = $root_path. PATH_ORGANIZATION_UPLOADS. $prev_or['logo'];
			$path_dir = str_replace(array('\\','/'), array(DS,DS), $path_dir);

			if( !EMPTY( $prev_or['logo'] ) )
			{
				if( !EMPTY( $path_dir ) )
				{
					if( file_exists($path_dir) )
					{
						$this->unlink_attachment( $path_dir );
					}
				}
			}
				
			SYSAD_Model::commit();
			$status 	= SUCCESS;
			
		}
		catch(PDOException $e)
		{
			SYSAD_Model::rollback();
			$msg 		= $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			SYSAD_Model::rollback();
			$msg 		= $this->rlog_error($e, TRUE);
		}

		$response 		= array(
			'status' 	=> $status,
			'msg' 		=> $msg,
			'reload' 	=> 'datatable',
			'datatable_options' 	=> $this->dt_options
		);
	
		echo json_encode( $response );
	}
	
	
	
	
	/**
	 * Use This helper function to get the parent of the main organization selected
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. default NULL. group type of the organization
	 * @param  $search -- optional. default value array. search parameters usually used in datatable
	 * @param  $params -- optional. default value array. search value of the parameters usually used in datatable
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_parents( $org_code, $group_type = NULL, array $search = array(), array $params = array() )
	{
		$result 		= array();

		try
		{
			$result 	= $this->orgs->get_parents( $org_code, $group_type, $search, $params );
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
	
	/**
	 * Use This helper function to get the paths of the main organization selected
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. default NULL. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_paths( $org_code, $group_type = NULL )
	{
		$result 		= array();

		try
		{
			$result 	= $this->orgs->get_paths( $org_code, $group_type );
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

	/**
	 * Use This helper function to get the roots of the main organization selected
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $group_type -- optional. default NULL. group type of the organization
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function get_roots( $org_code, $group_type = NULL )
	{
		$result 		= array();

		try
		{
			$result 	= $this->orgs->get_roots( $org_code, $group_type );
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

	/**
	 * Use This helper function to process the descendants of the main org selected
	 *
	 *
	 * @param  $descendants -- required. all the child of the main org selected.
	 * @param  $group_name -- optional. default NULL. group name
	 * @param  $org_name -- optional. default NULL. organization name
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function process_descendants_arr( array $descendants, $group_name = NULL, $org_name = NULL )
	{
		$descendants_arr 	= array();

		$org_key 			= 'org_code';
		$group_key 			= 'group_type';

		if( !EMPTY( $group_name ) )
		{
			$group_key 		= $group_name;
		}

		if( !EMPTY( $org_name ) )
		{
			$org_key 		= $org_name;
		}

		foreach( $descendants as $descendant )
		{
			$descendants_arr[ $descendant[ $group_key ] ][] 	= $descendant[ $org_key ];
		}

		return $descendants_arr;
	}
	
	/**
	 * Use This helper function to check if the main org selected is a descendant
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $parent_org -- required. its parent organization code
	 * @param  $group_type -- required. group type of the organization
	 * @param  $return -- optional. Default value TRUE. if False it will just throw an exception message
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function is_descendant( $org_code, $parent_org, $group_type, $return = TRUE )
	{
		$flag 				= '';
		$msg 				= TRUE;

		try
		{
			$check 			= $this->base_hierarchy_check( $org_code, $parent_org, $group_type, $return );

			$flag 			= ( !$check['flag'] );
			$msg 			= $check['msg'];
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return array(
			'flag'	=> $flag
		);
	}
	
	/**
	 * Use This helper function to check if the main org selected is an ancestor
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $parent_org -- required. its parent organization code
	 * @param  $group_type -- required. group type of the organization
	 * @param  $return -- optional. Default value TRUE. if False it will just throw an exception message
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function is_ancestor( $org_code, $parent_org, $group_type, $return = TRUE )
	{
		$flag 				= '';
		$msg 				= TRUE;

		try
		{
			$check 			= $this->base_hierarchy_check( $org_code, $parent_org, $group_type, $return, Organizations_model::ANCESTORS );

			$flag 			= ( !$check['flag'] );
			$msg 			= $check['msg'];
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return array(
			'flag'	=> $flag
		);
	}

	/**
	 * Use This helper function to check if the main org selected is a sibling
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $parent_org -- required. its parent organization code
	 * @param  $group_type -- required. group type of the organization
	 * @param  $return -- optional. Default value TRUE. if False it will just throw an exception message
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function is_sibling( $org_code, $parent_org, $group_type, $return = TRUE )
	{
		$flag 				= '';
		$msg 				= TRUE;

		try
		{
			$check 			= $this->base_hierarchy_check( $org_code, $parent_org, $group_type, $return, Organizations_model::SIBLINGS );

			$flag 			= ( !$check['flag'] );
			$msg 			= $check['msg'];
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return array(
			'flag'	=> $flag
		);
	}
	

	/**
	 * Use This helper function to check if the main org selected is a descendant or ancestor or sibling
	 *
	 *
	 * @param  $org_code -- required. organization code
	 * @param  $parent_org -- required. its parent organization code
	 * @param  $group_type -- required. group type of the organization
	 * @param  $return -- optional. Default value TRUE. if False it will just throw an exception message
	 * @param  $type -- optional. Default value Descendants.
	 * @throws PDOException
	 * @throws Exception
	 * @return array
	 */
	public function base_hierarchy_check( $org_code, $parent_org, $group_type, $return = FALSE, $type = Organizations_model::DESCENDANTS )
	{
		$msg 				= '';
		$flag 				= TRUE;

		try
		{
			$child_orgs 	= $this->orgs->get_descendants($org_code, $type);

			$desc_arr 		= $this->process_descendants_arr( $child_orgs );

			if( is_array( $parent_org ) AND is_array( $group_type ) )
			{
				foreach( $parent_org as $key => $par_org )
				{
					$clean_group 	= filter_var( base64_url_decode( $group_type[ $key ] ), FILTER_SANITIZE_STRING );
					$clean_par 		= filter_var( base64_url_decode( $par_org ), FILTER_SANITIZE_STRING );

					$real_par 		= ( !EMPTY( $clean_par ) ) ? $clean_par : $par_org;
					$real_group 	= ( !EMPTY( $clean_group ) ) ? $clean_group : $group_type[ $key ];
					
					if( !EMPTY( $desc_arr ) AND ISSET( $desc_arr[ $real_group ] ) AND !EMPTY( $desc_arr[ $real_group ] ) )
					{
						$valid_desc_arr 	= $desc_arr[ $real_group ];

						if( in_array( $real_par, $valid_desc_arr ) )
						{
							// $msg 			= 'Sorry, but this organization is already your child.';

							if( ISSET( $this->hierarchy_msg_map[ $type ] ) )
							{
								$msg 		= sprintf( $this->hierarchy_msg_map[ $type ], $real_par );
							}
							
							$flag 			= FALSE;

							if( !$return )
							{
								throw new Exception( $msg );
							}

							break;
						}
					}
				}					
			}
			else
			{
				$clean_group 	= filter_var( base64_url_decode( $parent_org ), FILTER_SANITIZE_STRING );
				$clean_par 		= filter_var( base64_url_decode( $group_type ), FILTER_SANITIZE_STRING );

				$real_par 		= ( !EMPTY( $clean_par ) ) ? $clean_par : $parent_org;
				$real_group 	= ( !EMPTY( $clean_group ) ) ? $clean_group : $group_type;

				if( !EMPTY( $desc_arr ) AND ISSET( $desc_arr[ $real_group ] ) AND !EMPTY( $desc_arr[ $real_group ] ) )
				{
					$valid_desc_arr 	= $desc_arr[ $real_group ];

					if( in_array( $real_par, $valid_desc_arr ) )
					{
						// $msg 			= 'Sorry, but this organization is already your child.';

						if( ISSET( $this->hierarchy_msg_map[ $type ] ) )
						{
							$msg 		= sprintf( $this->hierarchy_msg_map[ $type ], $real_par );
						}

						$flag 			= FALSE;

						if( !$return )
						{
							throw new Exception( $msg );
						}
					}
				}
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

		return array(
			'msg'	=> $msg,
			'flag'	=> $flag
		);
	}
	
	private function _validate($params)
	{
		$required 		= array();
		$constraints 	= array();

		try
		{

			$required['org_name']				= 'Organization Name';

			if( ISSET( $params['has_parent'] ) )
			{
				$required['org_group_type'] 	= 'Group Type';
				$required['org_parents'] 		= 'Parent';
			}

			$constraints['org_name']			= array(
			 	'data_type'     => 'string',
	            'max_len'       => '255',
	            'name'          => 'Organization Name'
			);

			if( ! ISSET($params['org_name'] ) OR EMPTY( $params['org_name'] ) )
			{
				$required['org_name']	= 'Organization name';
			}

			if( !EMPTY( $params['id'] ) )
			{
				$id 			= filter_var( base64_url_decode( $params['id'] ), FILTER_SANITIZE_STRING );

				if( ISSET( $params['org_parents'] ) AND !EMPTY( $params['org_parents'] ) 
					AND ISSET( $params['org_parents'][0] ) AND !EMPTY( $params['org_parents'][0] )
				)
				{
					$this->is_descendant( $id, $params['org_parents'], $params['org_group_type'], FALSE );
				}
			}
			
			if(EMPTY($params['id']) AND EMPTY($params['salt']) AND EMPTY($params['token']))
			{

				if( ! ISSET( $params['org_code'] ) OR EMPTY($params['org_code'] ) )
				{
					$required['org_code']	= 'Organization code';
				}
				
				$org_details = $this->orgs->get_org_details($params['org_code']); 

				if(! EMPTY($org_details))
					throw new Exception(sprintf($this->lang->line('duplicate_data'), 'organization code')); 
				
			}

			$this->check_required_fields( $params, $required );

			if( ISSET( $params['email'] ) AND !EMPTY( $params['email'] ) )
			{
				$constraints['email']	= array(
					'data_type'     => 'email',
		            'name'          => 'Email'
				);
			}

			if( ISSET( $params['website'] ) AND !EMPTY( $params['website'] ) )
			{
				$constraints['website']	= array(
					'data_type'     => 'url',
		            'name'          => 'Website'
				);
			}

			$this->validate_inputs( $params, $constraints );
			

			if( ISSET( $params['has_parent'] ) )
			{
				if( ISSET( $params['org_parents'] ) AND !EMPTY( $params['org_parents'] ) 
					AND ISSET( $params['org_parents'][0] ) AND !EMPTY( $params['org_parents'][0] )
				)
				{
					foreach( $params['org_parents'] as $key => $org_par )
					{
						$clean_par 		= filter_var( base64_url_decode( $org_par ), FILTER_SANITIZE_STRING );
						$clean_group 	= filter_var( base64_url_decode( $params['org_group_type'][ $key ] ), FILTER_SANITIZE_STRING );

						$org_where 				= array();
						$org_where['org_code']	= $clean_par;

						$group_where 				= array();
						$group_where['group_type']	= $clean_group;

						$check 		= $this->orgs->check_valid_org_code_helper( $org_where );

						$check_grp 		= $this->orgs->check_valid_group_type_helper( $group_where );

						if( EMPTY( $check ) OR EMPTY( $check['check_org'] ) )
						{
							$invalid_org_msg 			= sprintf( $this->lang->line('invalid_multi'), 'Org', ( $key + 1 ) );

							throw new Exception($invalid_org_msg);
						}

						if( EMPTY( $check_grp ) OR EMPTY( $check_grp['check_grp'] ) )
						{
							$invalid_group_type_msg 	= sprintf( $this->lang->line('invalid_multi'), 'Group Type', ( $key + 1 ) );

							throw new Exception($invalid_group_type_msg);
						}
					}
				}
			}

			$v = $this->core_v;

			$v
			 	->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ORGANIZATIONS.'|primary_id=org_code')
			 		->sometimes()
			 	->check('parent_organizations', $params);

			 $v
			 	->exists(DB_CORE.'|table='.SYSAD_Model::CORE_ORGANIZATION_TYPES.'|primary_id=organization_type_id')
			 		->sometimes()
			 	->check('organization_type', $params);

			$v->assert(FALSE);
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

	public function get_org_parents_table()
	{
		$flag 		= 0;

		$html 		= '';

		$org_code 	= 0;
		$params 	= get_params();
		$par_org_details 	= array();

		try
		{
			$data  			= array();
			$resources 		= array();

			$org_code_orig 	= NULL;
			$org_salt 		= NULL;
			$org_token 		= NULL;

			if( !EMPTY( $params['org_code'] ) )
			{
				$org_code 				= filter_var( base64_url_decode( $params['org_code'] ), FILTER_SANITIZE_STRING );

				$par_org_details 		= $this->orgs->get_root_paths_per_org_code( $org_code );

				check_salt( $org_code, $params['salt'], $params['token'] );

				$org_code_orig 			= $params['org_code'];
				$org_salt 				= $params['salt'];
				$org_token 				= $params['token'];
			}

			$resources['loaded_init']	= array('selectize_init();');

			$org_group_types 			= array();

			$org_group_types 			= $this->orgs->get_org_group_types();
			$other_orgs 				= $this->orgs->get_other_orgs( $org_code );

			$data['other_orgs'] 		= $other_orgs;
			$data['org_group_types']	= $org_group_types;
			$data['par_org_details']	= $par_org_details;
			$data['org_code']			= $org_code_orig;
			$data['org_salt']			= $org_salt;
			$data['org_token'] 			= $org_token;

			$html 			= $this->load->view('tables/org_parents_table', $data, TRUE);
			$html 		   .= $this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			$msg 	= $this->get_user_message( $e );
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);
		}

		echo $html;
	}

	public function update_logo()
	{
		$msg 					= "";
		$flag  					= 0;

		$orig_params 			= get_params();

		$audit_action 			= AUDIT_UPDATE;
		$update 				= TRUE;
		$action 				= ACTION_EDIT;

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$status 				= ERROR;

		$update 				= ( ISSET( $orig_params['id'] ) AND !EMPTY( $orig_params['id'] ) ) ? TRUE : FALSE;
		$action 				= ( ISSET( $orig_params['id'] ) AND !EMPTY( $orig_params['id'] ) ) ? ACTION_EDIT : ACTION_ADD;

		$main_where 			= array();

		$path 					= NULL;
		$org_code 				= NULL;
		$sess_org_code 			= NULL;

		try
		{
			// $this->redirect_off_system($this->module);
			
			$params 			= $this->set_filter( $orig_params )
									->filter_string('id', TRUE)
									->filter();

			check_salt($params['id'], $params['salt'], $params['token']);

			$permission 		= $this->edit_per;
			$per_msg 			= $this->lang->line( 'err_unauthorized_edit' );

			if( !$permission )
			{
				throw new Exception( $per_msg );
			}	

			$main_where 		= array(
				'org_code'		=> $params['id']
			);

			SYSAD_Model::beginTransaction();

			$audit_schema[] 	= DB_CORE;
			$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
			$audit_action[] 	= AUDIT_UPDATE;
			$prev_detail[]  	= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
				$main_where
			);

			$upd_val['modified_by'] 	= $this->session->user_id;
			$upd_val['modified_date'] 	= $this->date_now;
			$upd_val['logo'] 			= ( !EMPTY( $params['org_logo'] ) ) ? $params['org_logo'] : NULL;
			$upd_val['logo_orig_name'] 	= ( !EMPTY( $params['org_logo_orig_filename'] ) ) ? $params['org_logo_orig_filename'] : NULL;

			$this->orgs->update_helper( SYSAD_Model::CORE_TABLE_ORGANIZATIONS, $upd_val, $main_where );

			$curr_detail[] 		= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
				$main_where
			);

			$audit_name 				= 'Organization Logo.';

			$audit_activity 			= sprintf($this->lang->line('audit_trail_update'), $audit_name);

			$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );

			if( !EMPTY( $params['org_logo'] ) )
			{
				$root_path 			= $this->get_root_path();
				$org_pic_path 		= $root_path. PATH_ORGANIZATION_UPLOADS . $params['org_logo'];
				$org_pic_path 		= str_replace(array('\\','/'), array(DS,DS), $org_pic_path);
				
				if( file_exists( $org_pic_path ) )
				{
					$org_pic_src	= output_image($params['org_logo'], PATH_ORGANIZATION_UPLOADS);
					$path 	 	 	= $org_pic_src;
				}
			}
			else
			{
				$path 				= base_url().PATH_IMAGES.DEFAULT_ORG_LOGO;
			}

			$org_code 				= $params['id'];
			$sess_org_code 			= $this->session->org_code;

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_saved' );
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
			'status'				=> $status,
			'table_id' 				=> $this->table_id,
			'org_code' 				=> $org_code,
			'sess_org_code'			=> $sess_org_code,
			'path' 					=> $path
		);

		echo json_encode( $response );
	}

	public function download_template($extra = NULL)
	{
		try
		{
			$per_msg 			= $this->lang->line( 'err_unauthorized_download' );

			if( !$this->download_per )
			{
				throw new Exception( $per_msg );
			}

			$extra_arr 	= array();

			if( !EMPTY( $extra ) )
			{
				$extra_json 	= json_decode(base64_url_decode($extra));
				$extra_arr 		= (array) $extra_json;

				foreach( $extra_json as $k => $ex )
				{
					$extra_arr[$k] = (array) $ex;
				}
				
			}

			$this->load->library('Import_template');

			$php_excel 				= new PHPExcel();

			$system_title 			= get_setting(GENERAL, "system_title");

			$model_obj 				= $this->itm;

			$php_excel->getProperties()
			   ->setCreator($system_title)
			   ->setTitle('Organization Import Template')
			   ->setDescription('A facility to upload multiple organization through excel')
			   ->setSubject('Organization Import Template')
			   ->setKeywords('Organization Import Template')
			   ->setCategory('upload');


			// $this->import_template->column_with_dropdown[] = 'org_code';
			$this->import_template->column_with_dropdown[] = 'org_parent';
			$this->import_template->column_with_dropdown[] = 'system_owner';
			$this->import_template->column_with_dropdown[] = 'status';

			$references 		= new \PHPExcel_Worksheet($php_excel, REFERENCE_SHEET);
	    	
	    	$this->import_template->set_sheet_protection_password( $references, TEMPLATE_PASSWORD );

	    	$php_excel->addSheet($references);

	    	$this->init_reference( $references, array('extension_code' => 'ename') );


			$organizations 				= $this->itm->get_columns_table_import( $model_obj::CORE_TABLE_ORGANIZATIONS, array(
					'org_parent',
					'location_code',
					'created_by', 
					'created_date',
					'modified_by',
					'modified_date',
					'logo',
					'logo_orig_name',
					'fax'
				) 

			);

			$organization_parent 	= $this->itm->get_columns_table_import( $model_obj::CORE_TABLE_ORGANIZATION_PARENTS, array('path') );

			$filename 				= 'Organization_import_template.xlsx';

	    	$loc_worksheet_obj_arr 		= array();

	    	$this->import_template->sheet_input[] 	= ORGANIZATION_SHEET;
	    	// $this->import_template->sheet_input[] 	= ORGANIZATION_PARENT_SHEET;

	    	$organization_sheet 		= $this->import_template->write_to_worksheet( $php_excel, ORGANIZATION_SHEET, $organizations, FALSE, array(), array(
	    			'org_parent'
	    		), TRUE, 
	    		array(
	    			'org_code',
	    			'short_name',
	    			'name',
	    			'system_owner',
	    			'status'
	    		),
	    		array(),
	    		DB_CORE,
	    		$extra_arr
	    	);

	    	// $php_excel->addNamedRange(new PHPExcel_NamedRange('ORG_CODE_LIST', $organization_sheet->getSheetByName(ORGANIZATION_SHEET), 'A2:A'.TEMPLATE_DR_NUM_DV));
/*
	    	$organization_parent_sheet 	= $this->import_template->write_to_worksheet( $php_excel, ORGANIZATION_PARENT_SHEET, $organization_parent, FALSE, array( 
	    			// OPERATION_COLUMN,
	    			'org_code',
	    			'org_parent',
	    			// 'date_surrendered_old'
	    		)
	    	);
*/
	    	$php_excel->removeSheetByIndex(0);

	    	if( !EMPTY( $this->import_template->sheet_columns ) )
	    	{
	    		foreach( $this->import_template->sheet_columns as $sheet_name => $cols )
	    		{
	    			$sheet_obj 		= $php_excel->getSheetByName($sheet_name);

	    			$this->import_template->set_sheet_protection_password($sheet_obj);

	    			$end_col 		= end( $cols );
	    			
	    			$sheet_obj->getStyle( $cols[0].'2:'.$end_col.TEMPLATE_DR_NUM_DV )
	    				->getProtection()
	    				->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
	    		}
	    	}

	    	$php_excel->setActiveSheetIndexByName(ORGANIZATION_SHEET);
			// $writer = \PHPExcel_IOFactory::createWriter($surrenderer_intervention_sheet, 'Excel2007');
			$writer = \PHPExcel_IOFactory::createWriter($organization_sheet, 'Excel2007');
			// Excel5

			ob_end_clean();
			header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			
			$writer->save('php://output');
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);

			$this->error_index( $msg );
		}
		catch( Exception $e )
		{
			$msg 	= $this->rlog_error($e, TRUE);	

			$this->error_index( $msg );
		}
	}

	public function init_reference( PHPExcel_Worksheet $surrenderer_references, array $change_names = array() )
	{
		try
		{
			$this->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'ousm');

			$active_flag 	= $this->import_template->get_active_flag('status', 'status_name', TRUE);
			$sys_owner 		= $this->import_template->get_active_flag('system_owner', 'system_owner_status');
			$orgs 			= $this->ousm->get_orgs_parent_import();

	    	if( !EMPTY( $active_flag ) )
	    	{
	    		$active_flag_sheet 			= $this->import_template->write_append( $surrenderer_references, $active_flag, array(), $change_names );
	    	}
	    	else
	    	{
	    		$active_flag_sheet 			= 0;
	    	}

	    	if( !EMPTY( $sys_owner ) )
	    	{
	    		$sys_owner_sheet 			= $this->import_template->write_append( $surrenderer_references, $sys_owner, $active_flag_sheet, $change_names );
	    	}
	    	else
	    	{
	    		$sys_owner_sheet 			= $active_flag_sheet;
	    	}

	    	if( !EMPTY( $orgs ) )
	    	{
	    		$org_sheet 			= $this->import_template->write_append( $surrenderer_references, $orgs, $sys_owner_sheet, $change_names );
	    	}
	    	else
	    	{
	    		$org_sheet 			= $sys_owner_sheet;
	    	}
	    	
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch(Exception $e)
		{
			$this->rlog_error($e);
			throw $e;
		}
	}

	public function modal_org_import($module = NULL)
	{
		$data 					= array();
		$resources 				= array();	

		$disabled 				= '';

		try
		{

			if( IS_NULL( $module ) )
			{
				$module = $this->module;
			}
			else
			{
				$module = base64_url_decode($module);
			}

			$module_js 	= HMVC_FOLDER."/".SYSTEM_CORE."/".CORE_USER_MANAGEMENT."/organizations";
			$resources['load_css'] 	= array();
			$resources['load_js'] 	= array($module_js);
			$resources['loaded_init']	= array('Organizations.import("'.$module.'");');

			$path_upl 	= PATH_ORGANIZATION_IMPORTS;

			if( $module == MODULE_USER )
			{
				$path_upl = PATH_USER_IMPORTS;
			}
			
			$resources['upload']	= array(
				'org_import'			=> array(
					'path' 				=> $path_upl, 
					'allowed_types' 	=> 'xlsx', 
					// 'show_progress' => 1,
					'show_preview'			=> 1,
					'drag_drop' 			=> true,
					'default_img_preview'	=> 'image_preview.png',
					'max_file' 				=> 1,
					'multiple' 				=> 1,
					'successCallback'		=> "Organizations.successImportCallback(files,data,xhr,pd, '".$module."');",
					// 'max_file_size'			=> '13107200',
					'auto_submit'			=> false,
					'max_file_size'			=> self::MAXFILESIZE,
					'multiple_obj'			=> true,
					// 'show_download'			=> true,
					/*'delete_path'			=> CORE_MAINTENANCE.'/Statements',
					'delete_path_method'	=> 'delete_uploads',
					'delete_form'			=> '#form_modal_statements',
					'disable'				=> $dis_upl,
					'deleteCallback' 		=> "refresh_ajax_datatable('".$this->table_id."');"*/
				)
			);

			$this->load->view("modals/org_import",$data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{			 
			$msg = $this->get_user_message($e);

			$this->error_modal($msg);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			$this->error_modal($msg);
		}	
	}

	private function _validate_import( array $params )
	{
		try
		{
			$v 	= $this->core_v;

			$v 
				->required()
				->check('org_import|Import File', $params);

			$v->assert(FALSE);
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

	public function modal_error_import()
	{
		$data 					= array();
		$resources 				= array();	

		try
		{
			$orig_params 		= $this->input->post(NULL, FALSE);
			$params 			= $orig_params;

			$err_arr_url_enc 	= base64_url_encode(json_encode($params['err_arr']));

			$path_upl 			= base_url().CORE_USER_MANAGEMENT.'/Organizations/download_template/'.$err_arr_url_enc;

			if( $params['module'] == MODULE_USER )
			{
				$path_upl 			= base_url().CORE_USER_MANAGEMENT.'/Users/download_template/'.$err_arr_url_enc;				
			}
			
			$data['params'] 		= $params;
			$data['orig_params'] 	= $orig_params;
			$data['path_upl'] 		= $path_upl;
			/*echo '<pre>';
			print_r($params);*/
			$this->load->view("modals/error_import",$data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{			 
			$msg = $this->get_user_message($e);

			$this->error_modal($msg);
		}
		catch(Exception $e)
		{
			$msg = $this->rlog_error($e, TRUE);

			$this->error_modal($msg);
		}	

	}

	public function import()
	{

		$msg 					= "";
		$flag  					= 0;
		$status 				= ERROR;

		$orig_params 			= get_params();

		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();
		$audit_activity 		= '';

		$params 			= $orig_params;
		$path 				= NULL;
		$header 			= array();
		$err_arr 			= array();
		$upl_arr 			= array();

		try
		{
			$per_msg 			= $this->lang->line( 'err_unauthorized_import' );

			if( !$this->import_per )
			{
				throw new Exception( $per_msg );
			}
			

			$this->_validate_import( $params );

			$this->load->library('Import_template');
			$this->load->library('Doc_parser');

			if( ISSET( $params['org_import'] ) AND !EMPTY( $params['org_import'] ) )
			{
				$path 			= FCPATH.PATH_ORGANIZATION_IMPORTS.$params['org_import'];
				$path 			= str_replace(array('/', '\\'), array(DS, DS), $path);

				$model_obj 		= $this->orgs;

				if( file_exists( $path ) )
				{
					$data 		= $this->doc_parser->parse($path, ORGANIZATION_SHEET, TRUE);

					$organizations 				= $this->itm->get_columns_table_import( $model_obj::CORE_TABLE_ORGANIZATIONS);

					if( ISSET( $data['by_row'][ORGANIZATION_SHEET][1]['formatted_value'] ) )
					{
						$header = $data['by_row'][ORGANIZATION_SHEET][1]['formatted_value'];
					}

					if( !EMPTY( $organizations ) )
					{
						$organizations[] = 'org_parent';
						if( !EMPTY( $header ) )
						{
							foreach( $header as $h )
							{
								if( !in_array($h, $organizations, TRUE) )
								{
									throw new Exception('Invalid File Format for header ('.$h.').');
								}
							}
						}
						else
						{
							throw new Exception('Invalid File Format.');
						}
					}

					if( !ISSET($data['by_first_row']) OR EMPTY( $data['by_first_row'] ) )
					{
						throw new Exception('File was empty.');
					}

					if( ISSET( $data['by_row'][ORGANIZATION_SHEET] ) )
					{
						if( ISSET( $data['by_row'][ORGANIZATION_SHEET][1] ) )
						{
							unset($data['by_row'][ORGANIZATION_SHEET][1]);
						}

						$process_data = $this->process_data($data['by_row'][ORGANIZATION_SHEET], $header);

						$ins_arr 	  = $process_data['ins_arr'];
						$err_arr 	  = $process_data['err_arr'];
						$parent_org_arr = $process_data['parent_org_arr'];
						$succ_rows 	= $process_data['succ_rows'];
						$upl_arr 	= $process_data['upl_arr'];

						SYSAD_Model::beginTransaction();

						if( !EMPTY( $ins_arr ) )
						{
							$org_codes 			= array_column($ins_arr, 'org_code');

							$main_where 		= array(
								'org_code'		=> array( 'IN', $org_codes )
							);

							$audit_schema[] 	= DB_CORE;
							$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
							$audit_action[] 	= AUDIT_INSERT;
							$prev_detail[]  	= array();

							$this->orgs->insert_helper( SYSAD_Model::CORE_TABLE_ORGANIZATIONS, $ins_arr );

							$curr_detail[] 				= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATIONS,
													$main_where
												 );

							if( !EMPTY( $parent_org_arr ) )
							{
								$audit_schema[] 	= DB_CORE;
								$audit_table[] 	 	= SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS;
								$audit_action[] 	= AUDIT_INSERT;
								$prev_detail[]  	= array();

								$this->orgs->insert_helper( SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS, $parent_org_arr );

								$curr_detail[] 				= $this->orgs->get_details_for_audit( SYSAD_Model::CORE_TABLE_ORGANIZATION_PARENTS,
													$main_where
												 );
							}

							$audit_name 				= 'Organization Imported';

							$audit_activity 			= sprintf( $this->lang->line('audit_trail_add'), $audit_name);

							$this->audit_trail->log_audit_trail( $audit_activity, $this->module, $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
						}

						SYSAD_Model::commit();
						
						if( !EMPTY( $err_arr ) )
						{
							if( !EMPTY( $succ_rows ) ) 
							{
								$succ_rows_str = implode(', ', $succ_rows);
								throw new Import_exception('Row(s) '.$succ_rows_str.' was imported but, there are some errors found.');
							}
							else
							{
								throw new Import_exception('There are some errors found.');
							}
						}
					}
				}
				else
				{
					throw new Exception("File doesn't exists.");
				}
			}

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					=$this->lang->line( 'data_imported' );
		}
		catch( PDOException $e )
		{
			if( !EMPTY( $path ) )    
			{
				if( file_exists( $path ) )
				{
					unlink($path);
				}
			}

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{
			if( !EMPTY( $path ) )    
			{
				if( file_exists( $path ) )
				{
					unlink($path);
				}
			}

			if( $e instanceof Import_exception )
			{
				$flag 	= 2;
			}
			else
			{
				SYSAD_Model::rollback();
			}

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}
		catch( Import_exception $e )
		{
			if( !EMPTY( $path ) )    
			{
				if( file_exists( $path ) )
				{
					unlink($path);
				}
			}
			// SYSAD_Model::rollback();
			$msg 	= $this->rlog_error($e, TRUE);
			$flag 	= 2;
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status' 				=> $status,
			'err_arr' 				=> $err_arr,
			'upl_arr' 				=> $upl_arr,
			'header'  				=> $header,
			'datatable_options'		=> $this->dt_options,
			'datatable_id'			=> $this->table_id
		);

		echo json_encode( $response );
	}

	protected function process_data(array $data = array(), array $header = array())
	{
		$ins_arr 			= array();
		$err_arr 			= array();
		$parent_org_arr 	= array();
		$succ_rows 			= array();
		$upl_arr 			= array();

		try
		{
			if( !EMPTY( $data ) )
			{
				$k 	= 0;
				$e 	= 0;
				foreach( $data as $row_k => $d )
				{
					$arr 		= array();
					$err_msg 	= '';

					foreach( $d['formatted_value'] as $key => $v )
					{
						$real_val 			= explode(TEMPLATE_DR_MARK, $v);

						$arr[$header[$key]] = $real_val[0];
					}

					if( !EMPTY( $arr ) )
					{

						$validate = $this->core_v;

						$validate 
							->required()
							->maxlength(25)
								->sometimes()
							->Notexists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ORGANIZATIONS.'|primary_id=org_code')
								->sometimes()
							->check('org_code', $arr);

						$validate 
							->required()
							->maxlength(50)
								->sometimes()
							->check('short_name', $arr);

						$validate 
							->required()
							->maxlength(100)
								->sometimes()
							->check('name', $arr);

						$validate 
							->maxlength(100)
								->sometimes()
							->url(array('verybasic'))
								->sometimes()
							->check('website', $arr);

						$validate 
							->maxlength(100)
								->sometimes()
							->email()
								->sometimes()
							->check('email', $arr);

						$validate 
							->phone()
								->sometimes()
							->check('phone', $arr);

						$validate 
							->required()
							->in(array(ENUM_YES, ENUM_NO))
								->sometimes()
							->check('system_owner', $arr);

						$validate 
							->required()
							->in(array(ENUM_YES, ENUM_NO))
								->sometimes()
							->check('status', $arr);

						$validate 
							->maxlength(25)
								->sometimes()
							->exists(DB_CORE.'|table='.SYSAD_Model::CORE_TABLE_ORGANIZATIONS.'|primary_id=org_code')
								->sometimes()
							->check('org_parent', $arr);

						if( $validate->validation_fails() )
						{
							if( !EMPTY( $validate->errors()->all() ) )
							{
								$validate->errors()->start_delimiter = '<li>';
								$validate->errors()->end_delimiter   = '</li>';
								$err_msg = $validate->errors()->toStringErr(array(), FALSE);

								$validate->resetMessage();
							}
						}

						if( !EMPTY( $err_msg ) )
						{
							foreach( $d['formatted_value'] as $key => $v )
							{
								$err_arr[$e][$header[$key]]	= $v;
							}

							$err_arr[$e]['row_index'] 	= ( $row_k - 1 );
							$err_arr[$e]['error_msg'] 	= $err_msg;

							$e++;
						}
						else
						{
							$succ_rows[] 	= ( $row_k - 1 );
							foreach( $d['formatted_value'] as $key => $v )
							{
								$real_val 					= explode(TEMPLATE_DR_MARK, $v);
								$org_code 					= NULL;

								$upl_arr[$k][$header[$key]]	= $v;

								if( $header[$key] == 'org_parent' )
								{
									if( !EMPTY( $real_val[0] ) )
									{
										$parent_org_arr[$k][$header[$key]] 	= $real_val[0];
										$parent_org_arr[$k]['org_code'] 	= $d['formatted_value'][0];
									}
								}
								else
								{
									$ins_arr[$k][$header[$key]] = $real_val[0];
									$ins_arr[$k]['created_by'] 	= $this->session->user_id;
									$ins_arr[$k]['created_date'] 	= date('Y-m-d H:i:s');
								}
							}

							$upl_arr[$k]['row_index'] 	= ( $row_k - 1 );
							$upl_arr[$k]['msg'] 		= 'Detail(s) uploaded.';
							
							$k++;
						}

					}
					
				}
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

		return array(
			'header'	=> $header,
			'ins_arr'   => $ins_arr,
			'parent_org_arr' => $parent_org_arr,
			'err_arr'	=> $err_arr,
			'succ_rows' => $succ_rows,
			'upl_arr' 	=> $upl_arr
		);
	}
}

