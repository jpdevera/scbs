<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Systems extends SYSAD_Controller 
{
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('systems_model', 'systems');
	}
	
	public function set_system($system_code)
	{
		try
		{
			$this->load->model(CORE_SYSTEMS.'/Systems_application_model', 'sys_sys_mod');

			$check_sys_dir 		= $this->sys_sys_mod->check_system_redirection( $system_code );

			$img_src 			= "";

			if( !EMPTY( $check_sys_dir ) )
			{
				$root_path 		= $this->get_root_path();
				
	
				if( !EMPTY( $check_sys_dir["logo"] ) )
				{

					$photo_path = $root_path.PATH_SYSTEMS_UPLOADS.$check_sys_dir["logo"];
					$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);
	
					if( file_exists( $photo_path ) )
					{						
						$img_src = output_image($check_sys_dir["logo"], PATH_SYSTEMS_UPLOADS);
					}
				}
			}

			$this->session->set_userdata('current_system_logo', $img_src);
			
			$this->session->set_userdata("current_system", $system_code);

			$link 				= $this->session->redirect_page;

			$roles 				= $this->session->user_roles;

			$landing_pages 		= array();

			if( !EMPTY( $system_code ) )
			{
				$landing_pages 	= $this->auth_model->get_landing_pages( array( $system_code ) );
			}
			
			// SETS THE LANDING PAGE AFTER LOGIN
			if(!EMPTY($landing_pages))
			{
				if( !EMPTY( $landing_pages[0]['link'] ) )
				{

					$landing_details = $this->_process_landing_page( $landing_pages );

					$has_access 	= $landing_details['has_access'];
					$link 			= $landing_details['link_p'];

					if( EMPTY( $has_access ) )
					{
						$next_link 	= $this->_next_landing_page( $roles, $system_code );
						$link 		= $next_link['link'];
					}
					
				}
			}
			else
			{
				$next_link 	= $this->_next_landing_page( $roles, $system_code );
				$link 		= $next_link['link'];
			}
			
			// $sys = $this->systems->get_systems($system_code);

			// $link 	= $this->session->redirect_page;
			
			echo json_encode(array("link" => $link));
		}
		catch(PDOException $e)
		{
			$this->get_user_message($e);
		}	
		catch( Exception $e )
		{
			$this->rlog_error($e, TRUE);	
		}
	}

	private function _process_landing_page( array $landing_pages )
	{
		$has_access = FALSE;
		$link_p 	= "";

		try
		{
			if( count( $landing_pages ) > 1 )
			{
				foreach( $landing_pages as $l_p )
				{
					$has_access 	= $this->permission->check_permission($l_p['module_code']);

					if( !EMPTY( $has_access ) )
					{
						$link_p 	= $l_p['link'];

						break;
					}
				}
			}
			else
			{
				$has_access 	= $this->permission->check_permission($landing_pages[0]['module_code']);

				if( !EMPTY( $has_access ) )
				{
					$link_p 	= $landing_pages[0]['link'];
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

		return array(
			'has_access'	=> $has_access,
			'link_p'		=> $link_p
		);
	}

	private function _next_landing_page( array $roles, $system_code )
	{
		$link 					= NULL;

		try
		{
			$this->load->model(CORE_COMMON.'/Systems_model', 'sysm');

			$next_landing_page 	= $this->auth_model->get_modules_for_landing_page( $roles, $system_code );

			$has_access_sub_arr = array();

			$main_link 			= NULL;

			if( !EMPTY( $next_landing_page ) )
			{
				foreach( $next_landing_page as $page )
				{
					$modules 								= $this->auth_model->get_modules_by_link($page['link'], $page['system_code']);

					$sys_details 							= $this->sysm->get_systems($page['system_code']);

					if( !EMPTY( $sys_details ) AND !EMPTY( $sys_details['shared_module'] ) )
					{
						$this->session->set_userdata('current_system', $page['system_code']);
					}

					foreach( $modules as $mod )
					{

						$has_access_sub 					= $this->permission->check_permission($mod['module_code']);

						$has_access_sub_arr[ $mod['link'] ][] 	= $has_access_sub;
					}
				}

				if( !EMPTY( $has_access_sub_arr ) )
				{
					foreach( $has_access_sub_arr as $link => $permissions )
					{
						if( !in_array(0, $permissions ) )
						{
							$main_link 	= $link;

							break;
						}
					}
				}
				
				if( !EMPTY( $main_link ) )
				{
					$link 	= $main_link;
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
			'link'	=> $link
		);
	}
}