<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Roles_model extends SYSAD_Model 
{
	
	private $roles;
	private $system_roles;
	private $user_roles;
	private $systems;
	
	public function __construct()
	{	
		parent::__construct();
		
		$this->roles 		= parent::CORE_TABLE_ROLES;
		$this->system_roles = parent::CORE_TABLE_SYSTEM_ROLES;
		$this->user_roles 	= parent::CORE_TABLE_USER_ROLES;
		$this->systems 		= parent::CORE_TABLE_SYSTEMS;
	}
                
	public function get_role_details($role_code)
	{
		$result 	= array();

		try
		{
			$fields = array("role_code", "role_name", "default_system", "maintainer_flag", "default_role_sign_up_flag");
			$where 	= array("role_code" => $role_code);
			
			$result = $this->select_data($fields, $this->roles, FALSE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}	
	
	public function get_roles()
	{
		$result 	= array();

		try
		{
			$fields 	= array("role_code", "role_name", "default_role_sign_up_flag");
			$order_by 	= array("role_name" => "ASC");
			
			$result 	= $this->select_data($fields, $this->roles, TRUE, NULL, $order_by);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	
		return $result;		
	}
		
	public function get_system_roles($role_code)
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query 	= "
				SELECT 	a.system_code, b.system_name
				FROM 	%s a 
				JOIN 	%s b ON a.system_code = b.system_code
				WHERE 	a.role_code = ?
";

			$val[] 	= $role_code;

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_SYSTEM_ROLES, SYSAD_Model::CORE_TABLE_SYSTEMS );

			$result	= $this->query( $query, $val);

			/*$fields = array("*");
			$where 	= array("role_code" => $role_code);
			
			$result = $this->select_data($fields, $this->system_roles, TRUE, $where);*/
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;	
	}	
	
	public function get_user_roles($user_id, $main_role_flag = 0)
	{
		$result 		= array();

		try
		{			
			$fields 	= array("role_code");
			$where 		= array("user_id" => $user_id, 'main_role_flag' => $main_role_flag);
			$order_by 	= array("role_code" => "ASC");
			
			$result 	= $this->select_data($fields, $this->user_roles, TRUE, $where, $order_by);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	public function get_users_count($id)
	{
		$result 	= array();

		try
		{	
			$fields = array("COUNT(*) cnt");
			$where	= array("role_code" => $id);
				
			$result = $this->select_data($fields, $this->user_roles, FALSE, $where);
	
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
		
	public function get_role_list($aColumns, $bColumns, $params)
	{
		$stmt 		= array();

		try
		{
			$cColumns = array("A-role_code", "A-role_name", "C-system_name", "IF(A.built_in_flag = 1, '".ENUM_YES."', '".ENUM_NO."') as status", "IF(A.modified_date IS NULL, A.created_date, A.modified_date) convert_to date");
			
			$fields = str_replace(" , ", " ", implode(", ", $aColumns));
			
			$sWhere = $this->filtering($cColumns, $params, FALSE);
			$sOrder = $this->ordering($bColumns, $params);
			$sLimit = $this->paging($params);
			
			$filter_str = $sWhere["search_str"];
			$filter_params = $sWhere["search_params"];

			$sub_val 		= array();
					
			$query = <<<EOS
				SELECT SQL_CALC_FOUND_ROWS $fields 
				FROM $this->roles A
				LEFT JOIN $this->system_roles B
				ON A.role_code = B.role_code
				LEFT JOIN $this->systems C
				ON B.system_code = C.system_code
				AND C.on_off_flag = ?
				$filter_str
				GROUP BY A.role_code
	        	$sOrder
	        	$sLimit
EOS;

			$sub_val[] = 1;

			$filter_params = array_merge( $sub_val, $filter_params );

			$stmt = $this->query($query, $filter_params);
		
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $stmt;
	}	
	
	public function filtered_length($aColumns, $bColumns, $params)
	{
		$stmt 		= array();

		try
		{
			$this->get_role_list($aColumns, $bColumns, $params);
	
			$query = <<<EOS
				SELECT FOUND_ROWS() cnt
EOS;
	
			$stmt = $this->query($query, NULL, TRUE, FALSE);
	
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $stmt;
	}	
	
	public function total_length()
	{
		$result 	= array();

		try
		{
			$fields = array("COUNT(role_code) cnt");
				
			$result = $this->select_data($fields, $this->roles, FALSE);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	public function insert_role($params)
	{
		$role_code 	= 0;

		try
		{
			$val = array();
			$role_code = filter_var($params['role_code'], FILTER_SANITIZE_STRING);
			
			$val["role_code"] = $role_code;
			$val["role_name"] = filter_var($params['role_name'], FILTER_SANITIZE_STRING);
			$val["created_by"] = $this->session->user_id;
			$val["created_date"] = date('Y-m-d H:i:s');

			if( ISSET( $params['default_system'] ) AND !EMPTY( $params['default_system'] ) )
			{
				$val['default_system']	= $params['default_system'];
			}

			if( ISSET( $params['maintainer_flag'] ) AND !EMPTY( $params['maintainer_flag'] ) )
			{
				$val['maintainer_flag'] 	= LOGGED_IN_FLAG_YES;
			}
			else
			{
				$val['maintainer_flag'] 	= LOGGED_IN_FLAG_NO;	
			}

			if( ISSET( $params['default_role_sign_up_flag'] ) AND !EMPTY( $params['default_role_sign_up_flag'] ) )
			{
				$val['default_role_sign_up_flag'] 	= ENUM_YES;
			}
			else
			{
				$val['default_role_sign_up_flag'] 	= ENUM_NO;	
			}
			
			$this->insert_data($this->roles, $val);
			
			if(!EMPTY($role_code) && !EMPTY($params["system_role"]))
				$this->_insert_system_roles($params["system_role"], $role_code);
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $role_code;
	}
	
	public function update_role($params)
	{
		try
		{
			$val = array();
			$val["role_name"] = filter_var($params['role_name'], FILTER_SANITIZE_STRING);
			$val["modified_by"]	= $this->session->user_id;

			if( ISSET( $params['default_system'] ) AND !EMPTY( $params['default_system'] ) )
			{
				$val['default_system']	= $params['default_system'];
			}
			else
			{
				$val['default_system']	= NULL;	
			}

			if( ISSET( $params['maintainer_flag'] ) AND !EMPTY( $params['maintainer_flag'] ) )
			{
				$val['maintainer_flag'] 	= LOGGED_IN_FLAG_YES;
			}
			else
			{
				$val['maintainer_flag'] 	= LOGGED_IN_FLAG_NO;	
			}

			if( ISSET( $params['default_role_sign_up_flag'] ) AND !EMPTY( $params['default_role_sign_up_flag'] ) )
			{
				$val['default_role_sign_up_flag'] 	= ENUM_YES;
			}
			else
			{
				$val['default_role_sign_up_flag'] 	= ENUM_NO;	
			}
			
			$where = array();
			$where["role_code"] = filter_var($params["id"], FILTER_SANITIZE_STRING);
			
			$this->update_data($this->roles, $val, $where);
			
			if(!EMPTY($params["id"]) && !EMPTY($params["system_role"])){
				$this->delete_data($this->system_roles, $where);
				$this->_insert_system_roles($params["system_role"], $params["id"]);
			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	public function delete_role($id)
	{
		try
		{
			$this->delete_data($this->roles, array('role_code' => $id));				
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	private function _insert_system_roles($system_roles, $role_code)
	{
		try
		{
			foreach ($system_roles as $system_role):
			
				$params	= array();
				$params["system_code"] = filter_var($system_role, FILTER_SANITIZE_STRING);
				$params["role_code"] = filter_var($role_code, FILTER_SANITIZE_STRING);
				
				$this->insert_data($this->system_roles, $params);
			endforeach;
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
}