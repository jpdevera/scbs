<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permission {
	
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	
	/**
	 * $module_code - Code of the module being accessed
	 * 
	 * $button_action - Actions that a user can use depending on its access level
	 *  
	 * $redirect - redirect to unauthorized access error page if necessary
	 *     
	 */

	public function check_own_permission($module_code, $current_user, $user_to_check, $button_action = NULL, $redirect = FALSE)
	{
		try
		{
			return $this->check_permission($module_code, $button_action, $redirect, $current_user, $user_to_check);
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
	
	public function check_permission($module_code, $button_action = NULL, $redirect = FALSE, $current_user = NULL, $user_to_check = NULL){
	
		try
		{

			if( !$this->CI->load->is_model_loaded('permissions_model') )
			{
				$this->CI->load->model('permissions_model');
			}

			$permissions = $this->CI->permissions_model->get_permission_access($module_code, $button_action);
			
			$has_access	= FALSE;
			
			if(!EMPTY($permissions))
			{
				$user_roles = $this->CI->session->userdata('user_roles');
		
				if(!EMPTY($user_roles)){
		
					foreach($permissions as $permission):
						$role_code = $permission['role_code'];
							
						if(in_array($role_code, $user_roles))
							$has_access	= TRUE;
					endforeach;
				}
			}
		
			if($has_access){
				if(!$redirect)
					if( !EMPTY( $current_user ) AND !EMPTY( $user_to_check ) )
					{
						if( $current_user == $user_to_check )
						{
							return TRUE;
						}
						else
						{
							return FALSE;
						}
					}
					else
					{
						return TRUE;
					}
			} else {
				if($redirect){
					redirect(base_url() . 'unauthorized' , 'location');
				} else {
					return FALSE;
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
	}

	public function get_scope($module_code) 
	{
		$scope 			= NULL;

		try
		{
			$scope_system 		= get_sys_param_code(SYS_PARAM_SCOPES, SCOPE_SYSTEM);
			$scope_region 		= get_sys_param_code(SYS_PARAM_SCOPES, SCOPE_REGION);
			$scope_agency 		= get_sys_param_code(SYS_PARAM_SCOPES, SCOPE_AGENCY);
			$scope_direct 		= get_sys_param_code(SYS_PARAM_SCOPES, SCOPE_DIRECT_NODES);
			$scope_region_own 	= get_sys_param_code(SYS_PARAM_SCOPES, SCOPE_OWN_AND_REGION);
			$scope_direct_own 	= get_sys_param_code(SYS_PARAM_SCOPES, SCOPE_OWN_AND_DR);

			$scope_system = $scope_system['sys_param_code'];
			$scope_region = $scope_region['sys_param_code'];
			$scope_agency = $scope_agency['sys_param_code'];
			$scope_direct = $scope_direct['sys_param_code'];
			$scope_region_own = $scope_region_own['sys_param_code'];
			$scope_direct_own = $scope_direct_own['sys_param_code'];

			$user_roles 	= $this->CI->session->userdata('user_roles');

			if( ! ISSET($user_roles) || EMPTY($user_roles))
			{
				return NULL;
			}

			$priority_heirarchy = array(
				$scope_system,
				$scope_region_own,
				$scope_region,
				$scope_direct_own,
				$scope_direct,
				$scope_agency
			);

			$priority_heirarchy_val = array(
				SCOPE_SYSTEM,
				SCOPE_OWN_AND_REGION,
				SCOPE_REGION,
				SCOPE_OWN_AND_DR,
				SCOPE_DIRECT_NODES,
				SCOPE_AGENCY,
			);

			$curr_scope 		= count( $priority_heirarchy );

			$curr_scope_he 		= array();

			if($user_roles)
			{
				foreach ($user_roles as $user_role) 
				{
					$result		= $this->CI->permissions_model->get_scope($module_code, $user_role);

					if(ISSET( $result ) AND ISSET( $result['scope'] ) )
					{
						if( !EMPTY( $result['scope'] ) )
						{
							$heirarchy 		= array_search($result['scope'], $priority_heirarchy);
							
							$curr_scope_he[$heirarchy] = $priority_heirarchy_val[$heirarchy];

							/*if($heirarchy < $curr_scope)
							{
								$curr_scope = $heirarchy;

								break;
							}*/
						}
					}
				}

				if( !EMPTY( $curr_scope_he ) )
				{
					$min_key 	= min(array_keys($curr_scope_he));

					$curr_scope = $curr_scope_he[$min_key];
				}

				/*$curr_scope = $priority_heirarchy[$curr_scope];
				
				if($curr_scope == $scope_system )
					$curr_scope = SCOPE_SYSTEM;

				if($curr_scope == $scope_region)
					$curr_scope = SCOPE_REGION;

				if($curr_scope == $scope_agency)
					$curr_scope = SCOPE_AGENCY;

				if($curr_scope == $scope_direct)
					$curr_scope = SCOPE_DIRECT_NODES;*/
			}
			else
			{
				$curr_scope = null;
			}

			$scope 	= $curr_scope;
		}	
		catch(PDOException $e) 
		{
			throw $e;
		} 
		catch(Exception $e) 
		{
			throw $e;
		}

		return $scope;
	}

	public function get_scope_organizations( $module_code ) 
	{
		try
		{
			$scope 		= $this->get_scope($module_code);

			$user_id	= $this->CI->session->user_id;

			$user_orgs  = $this->CI->session->user_orgs;
			
			if( $scope == SCOPE_AGENCY )
			{
				return $user_orgs;
			}		
			else if( $scope == SCOPE_REGION )
			{
				$adv_orgs 		= $this->get_child_orgs($user_orgs, $scope);

				$adv_orgs 		= array_unique($adv_orgs);

				return ( !EMPTY( $adv_orgs ) ) ? $adv_orgs : FALSE;
			}
			else if( $scope == SCOPE_OWN_AND_REGION )
			{
				$adv_orgs 		= $this->get_child_orgs($user_orgs, $scope, TRUE);

				$adv_orgs 		= array_unique($adv_orgs);

				return ( !EMPTY( $adv_orgs ) ) ? $adv_orgs : FALSE;
			}
			else if( $scope == SCOPE_OWN_AND_DR )
			{
				$adv_orgs 		= $this->get_child_orgs($user_orgs, $scope, TRUE, TRUE);

				$adv_orgs 		= array_unique($adv_orgs);

				return ( !EMPTY( $adv_orgs ) ) ? $adv_orgs : FALSE;
			}
			else if( $scope == SCOPE_DIRECT_NODES )
			{
				$adv_orgs 		= $this->get_child_orgs($user_orgs, $scope, FALSE, TRUE);

				$adv_orgs 		= array_unique($adv_orgs);

				return ( !EMPTY( $adv_orgs ) ) ? $adv_orgs : FALSE;
			}
			else if( $scope == SCOPE_SYSTEM )
			{
				return array();
			}
			else 
			{
				return FALSE;
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

	public function get_child_orgs(array $user_orgs, $scope, $include_own = FALSE, $direct_node = FALSE)
	{
		$user_org_pass 	= array();

		try
		{
			if( !EMPTY( $user_orgs ) )
			{
				if( $include_own )
				{
					$user_org_pass = array_merge($user_org_pass, $user_orgs);						
				}

				$child_adv 		= $this->CI->permissions_model->get_child_orgs_drop_many($user_orgs);

				if( !EMPTY( $child_adv ) )
				{
					$child_adv_arr 		= array_column($child_adv, 'org_code');

					$user_org_pass 		= array_merge( $user_org_pass, $child_adv_arr );

					$sub_arr 			= $this->get_child_orgs($child_adv_arr, $scope, $include_own, $direct_node);

					if( !EMPTY( $direct_node ) )
					{
						return $user_org_pass;	
					}

					$user_org_pass 	= array_merge( $user_org_pass, $sub_arr );
				}
				else
				{
					$user_org_pass = array_merge($user_org_pass, $user_orgs);							
				}

				
				/*foreach( $user_orgs as $adv_org )
				{
					$child_adv 	= $this->CI->permissions_model->get_child_orgs_drop($adv_org);

					if( $include_own )
					{
						$user_org_pass[] = $adv_org;						
					}

					if( !EMPTY( $child_adv ) )
					{
						$child_adv_arr 		= array_column($child_adv, 'org_code');

						$user_org_pass 		= array_merge( $user_org_pass, $child_adv_arr );

						if( !EMPTY( $direct_node ) )
						{	
							continue;	
						}

						$sub_arr 		= $this->get_child_orgs($child_adv_arr, $scope, $include_own, $direct_node);
						
						$user_org_pass 	= array_merge( $user_org_pass, $sub_arr );
					}
				}*/
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

		return $user_org_pass;
	}

	public function get_orgs_by_scope( $module_code )
	{
		$orgs 		= array();

		try
		{
			$this->CI->load->model(CORE_USER_MANAGEMENT.'/Organizations_model', 'org_mod');

			$scope 	= $this->get_scope($module_code);

			$current_org_code 	= $this->CI->session->org_code;

			$org_codes 			= ( !is_array( $current_org_code ) ) ? array( $current_org_code ) : $current_org_code;

			if( !EMPTY( $scope ) )
			{
				if( $scope == SCOPE_REGION
					OR $scope == SCOPE_DIRECT_NODES
			 	)
				{
					foreach( $org_codes as $o_c )
					{
						$check_root 	= FALSE;

						$check_root_det = $this->CI->org_mod->check_root($o_c);

						if( !EMPTY( $check_root_det ) AND !EMPTY( $check_root_det['check_root'] ) )
						{
							$check_root = TRUE;
						}

						$org_childs 	= $this->CI->org_mod->get_descendants($o_c, Organizations_model::DESCENDANTS, NULL, $check_root);

						if( !EMPTY( $org_childs ) )
						{
							$org_c 		= array_column($org_childs, 'org_code');

							if( $scope == SCOPE_REGION )
							{
								$orgs 	= array_merge( $orgs, $org_c );
							}
							else if( $scope == SCOPE_DIRECT_NODES )
							{
								$orgs[] = $org_c[0];
							}
						}

						if( $check_root )
						{
							$orgs[] 	= $o_c;
						}
					}

					$orgs 				= array_unique( $orgs );

					if( EMPTY( $orgs ) )
					{
						$orgs 			= $org_codes;
					}
				}
				else if( $scope == SCOPE_AGENCY )
				{
					$orgs			= $org_codes;
				}
				else if( $scope == SCOPE_SYSTEM )
				{
					$orgs 			= array();
				}
			}
			else
			{
				$orgs 				= FALSE;
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

		return $orgs;
	}
	
}