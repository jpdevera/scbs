<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends SYSAD_Model 
{
	
	private $users;
	private $user_roles;
	private $system_roles;
	private $tbl_modules;
	private $tbl_module_actions;
	private $tbl_module_action_roles;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->users 		= parent::CORE_TABLE_USERS;
		$this->user_roles 	= parent::CORE_TABLE_USER_ROLES;
		$this->system_roles	= parent::CORE_TABLE_SYSTEM_ROLES;
		$this->roles 		= parent::CORE_TABLE_ROLES;
		$this->tbl_modules 			= SYSAD_Model::CORE_TABLE_MODULES;
		$this->tbl_module_actions 	= SYSAD_Model::CORE_TABLE_MODULE_ACTIONS;
		$this->tbl_module_action_roles 	= SYSAD_Model::CORE_TABLE_MODULE_ACTION_ROLES;
	}
	
	public function get_active_user($search_term, $search_by = NULL, $aes_crypt_check = FALSE )
	{
		$result 	= array();

		try
		{
			$login_via = get_setting(LOGIN, "login_via");

			$username_case_sensitivity 		= get_setting( USERNAME, 'username_case_sensitivity' );

			$where 	= array();
			
			
			$fields = array(
				"username", 
				"email", 
				"fname",
				"lname",
				"job_title",
			);
			
			$decrypt = aes_crypt($fields, FALSE, FALSE, FALSE);
			
			$fields = array(
				"user_id", 				
				"password", 
				"salt", 
				"status", 
				$decrypt[0] . ' AS username' , 
				$decrypt[1] . ' AS email', 
				"CONCAT(".$decrypt[2] .", ' ', ".$decrypt[3] .") name", 
				"photo", 
				$decrypt[4] . ' AS job_title', 
				"location_code", 
				"org_code", 
				"attempts", 
				'initial_flag', 
				'logged_in_flag',
				'temporary_account_flag',
				'temporary_account_expiration_date',
				'soft_blocked',
				'soft_blocked_date',
				'soft_attempts'
			);
			
			if(IS_NULL($search_by))
			{
				switch($login_via)
				{
					case 'USERNAME_EMAIL':

						$username = aes_crypt(BY_USERNAME, FALSE, FALSE);
						$email_w 	= aes_crypt(BY_EMAIL, FALSE, FALSE);
						$mobile_w 	= aes_crypt('mobile_no', FALSE, FALSE);

						$by_username 		= $username;
						$by_email 			= $email_w;
						$by_mobile 			= $mobile_w;

						if( empty( $username_case_sensitivity ) )
						{
							$search_term 	= strtolower( $search_term );
							
							$by_username 	= "LOWER( CAST( ".$username." AS char(100) ) )";
							$by_email 		= "LOWER( CAST( ".$email_w." AS char(100) ) )";
							$by_mobile 		= "LOWER( CAST( ".$mobile_w." AS char(100) ) )";
							
						}	
						
						$where["OR"] = array($by_username => $search_term, $by_email => $search_term,  $by_mobile => $search_term);
						
					break;
					default:
						$search_by = strtolower($login_via);

						if( $aes_crypt_check )
						{
							$search_by = aes_crypt($search_by,FALSE,FALSE);
							$search_by = "CAST( ".$search_by." AS char(100) ) ";

							$where[$search_by] = $search_term;
						}
						else
						{
							$where[$search_by] = $search_term;
						}
					break;
				}	
			}
			else
			{
				if( $aes_crypt_check )
				{
					$search_by = aes_crypt($search_by,FALSE,FALSE);
					$search_by = "CAST( ".$search_by." AS char(100) ) ";
					$where[$search_by] = $search_term;
				}
				else
				{
					$where[$search_by] = $search_term;
				}
			}
			
			$where["salt"] 		= "IS NOT NULL";
			$where["password"] 	= "IS NOT NULL";
			
			$result 	= $this->select_data($fields, $this->users, FALSE, $where);
		}	
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;	
	}
	

	public function check_user_status($user_id, $sign_up = FALSE, $account_creator = NULL)
	{
		$result 	= array();

		try
		{	

			$table_us = $this->users;
			if( !EMPTY( $account_creator ) AND ( $account_creator == VISITOR_NOT_APPROVAL OR $account_creator == VISITOR ) )
			{
				$table_us = parent::CORE_TABLE_TEMP_USERS;
			}

			$fields = array("user_id");
			
			$where["user_id"] = $user_id;
			$where["status"] = STATUS_APPROVED;

			if( !$sign_up )
			{
				$where["salt"] = "IS NOT NULL";
				$where["password"] = "IS NOT NULL";
			}
				
			$result = $this->select_data($fields, $table_us, FALSE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;
			
	}

	public function get_active_user_for_reset($search_term, $search_by, $status = NULL)
	{
		$where 			= "";

		if( !EMPTY( $status ) )
		{
			$active 	= $status;
			$where 		= " AND status = $active ";
		}
		else 
		{
			$active 	= ACTIVE;
			$where 		= "";
		}
		
		$result 		= array();
		
		try
		{
			$username 		= 'CAST( '. aes_crypt('username', FALSE, FALSE).' AS char(100) ) username ';
			$fname 			= aes_crypt('fname', FALSE, FALSE);
			$lname 			= aes_crypt('lname', FALSE, FALSE);
			$email 		=  'CAST( '.aes_crypt('email', FALSE, FALSE).' AS char(100) ) email ';

			$query 	= <<<EOS
				SELECT user_id, $username, password, salt, CONCAT($fname, ' ', $lname) name, initial_flag, photo, $email
				FROM $this->users
				WHERE 1 = 1 
				$where
				AND $search_by = ?
				AND password IS NOT NULL
				AND salt IS NOT NULL			 
EOS;

			$result 	= $this->query( $query, array( $search_term ), TRUE, FALSE );
         	
		}	
		catch(PDOException $e)
		{
			throw $e;
		}	

		return $result;		
	}
	
	public function check_user_maintainer( $user_id )
	{
		try
		{
			$query 	= "
				SELECT 	b.maintainer_flag 
				FROM 	$this->user_roles a
				JOIN 	$this->roles b on a.role_code = b.role_code
				WHERE 	user_id = ?

";
			$val 		= array();
			$val[] 		= $user_id;

			$result 	= $this->query( $query, $val );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
	
	public function get_user_roles($user_id)
	{
		$result 	= array();
		$val 		= array();

		try
		{
			$query  = "
				SELECT 	a.role_code, b.default_system
				FROM 	%s a 
				JOIN 	%s b ON a.role_code = b.role_code
				WHERE 	a.user_id = ?
";

			$val[] 	= $user_id;

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_USER_ROLES, SYSAD_Model::CORE_TABLE_ROLES );

			$result = $this->query( $query, $val );
			
			/*$fields = array("role_code");
			$where 	= array("user_id" => $user_id);
			
			$result = $this->select_data($fields, $this->user_roles, TRUE, $where);*/
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;		
	}

	public function get_user_main_role($user_id)
	{
		$result = "";
		
		try
		{
			
			$fields = array("role_code");
			$where 	= array("user_id" => $user_id, "main_role_flag" => "1");
			
			$result = $this->select_data($fields, $this->user_roles, FALSE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result['role_code'];
	}
	
	public function get_user_system($roles)
	{
		$result 		= array();

		try
		{
			$filters 	= array();
			$cond 		= "a.role_code IN (";

			foreach($roles as $role) 
			{
				$cond .= "?, ";
				array_push($filters, $role);
			}

			$cond 		= trim($cond, ', ');
			$cond 		.= ")";

			$query 	= <<<EOS
				SELECT DISTINCT(a.system_code)
				FROM $this->system_roles a
				LEFT JOIN $this->tbl_modules b on a.system_code = b.system_code
                LEFT JOIN $this->tbl_module_actions c ON b.module_code = c.module_code
                LEFT JOIN $this->tbl_module_action_roles d 
               		ON 	c.module_action_id = d.module_action_id
                	AND a.role_code = d.role_code
					AND d.module_action_id IS NOT NULL
				WHERE $cond
				GROUP BY a.system_code
EOS;
	
			$result = $this->query($query, $filters);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $result;

	}

	public function get_landing_pages( array $systems )
	{
		$result 		= array();

		$val 			= array();
		$extra_val 		= array();

		$where 			= "";

		try
		{
			if( !EMPTY( $systems ) )
			{
				$count_systems				= count( $systems );

				$placeholder_systems 		= str_repeat( '?,', $count_systems );
				$placeholder_systems		= rtrim( $placeholder_systems, ',' );
				
				$where        	   		  	.= " AND a.system_code IN ( $placeholder_systems ) ";

				$extra_val 					= array_merge( $extra_val, $systems );
			}

			$query 		= "
				SELECT  a.module_code, a.link
				FROM 	%s a
				JOIN 	%s b ON a.system_code = b.system_code
				WHERE  	1 = 1
				AND 	a.landing_page_flag = 1
				AND 	IFNULL(a.link,'') != ''
				$where
				ORDER 	BY b.sort_order, a.sort_order
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_MODULES, SYSAD_Model::CORE_TABLE_SYSTEMS );
			
			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val );

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_modules_for_landing_page( array $role_code, $system = NULL, $link = NULL )
	{
		$result 		= array();

		$val 			= array();
		$extra_val 		= array();

		$where 			= "";

		try
		{
			if( !EMPTY( $role_code ) )
			{
				$count_roles			= count( $role_code );

				$placeholder_roles 		= str_repeat( '?,', $count_roles );
				$placeholder_roles		= rtrim( $placeholder_roles, ',' );
				
				$where        	   		  	.= " AND a.role_code IN ( $placeholder_roles ) ";

				$extra_val 					= array_merge( $extra_val, $role_code );
			}

			if( !EMPTY( $system ) )
			{
				if( is_array( $system ) )
				{
					$count_systems				= count( $system );

					$placeholder_systems 		= str_repeat( '?,', $count_systems );
					$placeholder_systems		= rtrim( $placeholder_systems, ',' );
					
					$where        	   		  	.= " AND c.system_code IN ( $placeholder_systems ) ";

					$extra_val 					= array_merge( $extra_val, $system );
				}
				else
				{
					$where        	   			.= " AND c.system_code = ? ";
					$extra_val[] 				= $system;
				}
			}

			if( !EMPTY( $link ) )
			{
				$where  		.= " AND c.link = ? ";
				$extra_val[] 	= $link;
			}
			else
			{
				$where 	.= " AND IFNULL(c.link,'') != '' ";
			}

			$query 		= "
			SELECT 	c.module_code, c.link, c.system_code
			FROM 	%s a 
			JOIN 	%s b ON a.module_action_id = b.module_action_id
			JOIN 	%s c ON b.module_code = c.module_code
			WHERE 	1 = 1
			$where
			GROUP 	BY c.module_code
			ORDER 	BY c.sort_order
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_MODULE_ACTION_ROLES, SYSAD_Model::CORE_TABLE_MODULE_ACTIONS, SYSAD_Model::CORE_TABLE_MODULES );
			
			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val);
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_modules_by_link( $link, $system_code = NULL )
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			if( !EMPTY( $system_code ) )
			{
				$add_where 	.= " AND system_code = ? ";
				$extra_val[] = $system_code;
			}

			$query 		= "
				SELECT 	module_code, link
				FROM 	%s	
				WHERE 	link = ?
					$add_where
				ORDER 	BY sort_order
";

			$val[] 		= $link;
			$val 		= array_merge($val, $extra_val);

			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_MODULES );

			$result = $this->query( $query, $val);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_reset_salt($email, $salt)
	{
		$cnt 			= 0;

		try{
			
			$where 		= array();
			
			$sys_param 	= get_sys_param_code(SYS_PARAM_STATUS, ACTIVE);

			$email_where = 'CAST( '.aes_crypt('email', FALSE, FALSE).' as char(100) ) ';
				
			$fields 	= array("COUNT(user_id) cnt");
			$where[$email_where] 		= $email;
			$where["reset_salt"] 	= $salt;
			$where["status"] 		= $sys_param["sys_param_code"];
				
			$result = $this->select_data($fields, $this->users, FALSE, $where);
			
			$cnt 	= $result["cnt"];
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		return $cnt;
	}

	public function update_helper($table, array $val, array $where)
	{
		try
		{
			$this->update_data( $table, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
	
	public function update_reset_salt($salt, $username)
	{
		try
		{
			$username_where 	= 'CAST( '.aes_crypt('username', FALSE, FALSE).' as char(100) ) ';

			$val 	= array("reset_salt" => $salt);
			$where 	= array($username_where => $username);
				
			$this->update_data($this->users, $val, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

	}	
	
	public function update_password($email, $password)
	{
		try
		{
			
			$email_where = 'CAST( '.aes_crypt('email', FALSE, FALSE).' as char(100) ) ';

			$val 	= array();
			$where 	= array($email_where => $email, "status" => STATUS_ACTIVE);
			
			// ENCRYPT THE PASSWORD
			$salt 		= gen_salt(TRUE);
			$password 	= in_salt($password, $salt, TRUE);
			
			$val["password"] 		= $password;
			$val["salt"] 			= $salt;
			$val["reset_salt"] 		= '';
			$val['initial_flag'] 	= INITIAL_NO;
			$val['attempts']		= 0;
			$val['soft_attempts'] 	= 0;
			
			$this->update_data($this->users, $val, $where);
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
	}
	
	public function update_status($username)
	{
	
		try
		{
			$active 	= get_sys_param_code(SYS_PARAM_STATUS, ACTIVE);
			$approved 	= get_sys_param_code(SYS_PARAM_STATUS, APPROVED);
			
			$val 		= array("status" => $active["sys_param_code"]);
			$where 		= array();

			$username_where 	= 'CAST( '.aes_crypt('username', FALSE, FALSE).' as char(100) ) ';

			$email_where = 'CAST( '.aes_crypt('email', FALSE, FALSE).' as char(100) ) ';
				
			$where["OR"] 		= array($username_where => $username, $email_where => $username);
			$where["status"] 	= $approved["sys_param_code"];
				
			$this->update_data($this->users, $val, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	
	}
	
	public function update_attempts($user_id , $attempts = NULL, $soft_attempts = NULL)
	{
		try 
		{
			$login_max 	= get_setting(LOGIN, 'login_attempts');
			$login_soft_max 	= get_setting(LOGIN, 'login_attempt_soft');


			$attempt_str 	= !is_null($attempts) ? "(attempts + 1)" : 0;
			$soft_attempt_str = !is_null($soft_attempts) ? "(soft_attempts + 1)" : 0;
			$date_now 		= date('Y-m-d H:i:s');
			$val 		= array($date_now, $user_id);
			$query 		= <<<EOS
				UPDATE $this->users set attempts = $attempt_str,
				soft_attempts = $soft_attempt_str,
				last_logged_in_attempt_date = ?
				WHERE user_id = ?;
EOS;
			$this->query($query, $val, FALSE);

			if(intval($login_soft_max) != 0 && ( !is_null($soft_attempts) and intval($login_soft_max) <= intval($soft_attempts) + 1))
			{
				if(intval($login_max) != 0 && ( !is_null($attempts) and intval($login_max) <= intval($attempts) + 1))
				{

				}
				else
				{
					$blocked 	= get_sys_param_code(SYS_PARAM_STATUS, BLOCKED);

					$seconds_to_add 		= 10;

					$login_attempt_soft_sec 	= get_setting(LOGIN, 'login_attempt_soft_sec');

					if( !EMPTY( $login_attempt_soft_sec ) AND is_numeric($login_attempt_soft_sec) )
					{
						$seconds_to_add 	= $login_attempt_soft_sec;
					}
						
					$time = new DateTime();
					$time->add(new DateInterval('PT' . $seconds_to_add . 'S'));

					$expired_date = $time->format('Y-m-d H:i:s');
					
					$val2 		= array(
						$blocked["sys_param_code"], 
						date('Y-m-d H:i:s'),
						'Reached Maximum Login Attempts (Soft Blocked)',
						ENUM_YES,
						$expired_date,
						$user_id

					);
					$query 		= <<<EOS
						UPDATE $this->users set status = ?,
						blocked_date = ?,
						reason = ?,
						soft_blocked = ?,
						soft_blocked_date = ?
						WHERE user_id = ?;
EOS;
					$this->query($query, $val2, FALSE);
				}
			}

			
			if(intval($login_max) != 0 && ( !is_null($attempts) and intval($login_max) <= intval($attempts) + 1))
			{
				$blocked 	= get_sys_param_code(SYS_PARAM_STATUS, BLOCKED);
				
				$val2 		= array(
					$blocked["sys_param_code"], 
					date('Y-m-d H:i:s'),
					'Reached Maximum Login Attempts',
					$user_id

				);
				$query 		= <<<EOS
					UPDATE $this->users set status = ?,
					blocked_date = ?,
					reason = ?
					WHERE user_id = ?;
EOS;
				$this->query($query, $val2, FALSE);
			}
			
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	public function get_user_by_id_reset_salt($id, $reset_salt, $initial_flag)
	{
		try
		{
			$and 		= "";

			if( !EMPTY( $initial_flag ) )
			{
				$and 	= " AND salt = ? ";
			}
			else 
			{
				$and 	= " AND reset_salt = ? ";
			}

			$username_where = aes_crypt('username', FALSE, FALSE);

			$username 		= 'CAST( '.aes_crypt('username', FALSE, FALSE).' as char(100) ) username';
			$email 		= 'CAST( '.aes_crypt('email', FALSE, FALSE).' as char(100) ) email';
			$fname 		= aes_crypt('fname', FALSE, 'fname');
			$lname 		= aes_crypt('lname', FALSE, 'lname');
			$fname_w 	= aes_crypt('fname', FALSE, FALSE);
			$lname_w 	= aes_crypt('lname', FALSE, FALSE);
			$job_title 	= aes_crypt('job_title', FALSE, 'job_title');
			
			$query = <<<EOS
				SELECT 	user_id, $username, $email, password, salt, status, CONCAT($fname_w, ' ', $lname_w) name, photo, $job_title, location_code, org_code,  		attempts, initial_flag
				FROM  	$this->users
				WHERE 	$username_where = ?
				$and
EOS;

			$val 	= array($id, $reset_salt);

			$result = $this->query($query, $val, TRUE, FALSE);
		
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	
	}

	public function update_log($user_id, $status)
	{
		try
		{
			$login_via 	= get_setting(LOGIN, "login_via");
		
			$val 		= array();
			$where 		= array();
			
			$val["logged_in_flag"] 	= $status;
			
			$where['user_id']		= $user_id;
			
			$this->update_data($this->users, $val, $where);
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
	
	public function validate_db_value( $table, $field, array $where )
	{
		$result 		= array();

		try
		{
			$result		= $this->select_data( $field, $table, FALSE, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}	

	public function get_systems_by_permission( $user_id )  
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			$query 		= "
				SELECT 	a.system_code,
						e.ci_directory
				FROM 	%s a 
				JOIN 	%s b 
				ON 		a.module_code = b.module_code
				JOIN 	%s c
				ON 		c.module_action_id = b.module_action_id
				JOIN 	%s d 
				ON 		c.role_code = d.role_code
				JOIN 	%s e 
				ON 		a.system_code = e.system_code
				WHERE 	d.user_id = ?
				GROUP 	BY a.system_code
";

			$query 		= sprintf( $query,  
				SYSAD_Model::CORE_TABLE_MODULES,
				SYSAD_Model::CORE_TABLE_MODULE_ACTIONS,
				SYSAD_Model::CORE_TABLE_MODULE_ACTION_ROLES,
				SYSAD_Model::CORE_TABLE_USER_ROLES,
				SYSAD_Model::CORE_TABLE_SYSTEMS
			);

			$val[] 		= $user_id;

			$result 	= $this->query( $query, $val);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_default_sign_up_role()
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= "
				SELECT 	a.role_code
				FROM 	%s a 
				WHERE 	a.default_role_sign_up_flag = ?			
";
			$query 	= sprintf( $query, SYSAD_Model::CORE_TABLE_ROLES );

			$val[] 	= ENUM_YES;

			$result = $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_user_agreement($user_id)
	{
		$result 		= array();
		$where 			= array();

		try
		{
			$fields 	= array('user_id','agreement_flag');
			$where['user_id'] = $user_id;

			$result 	= $this->select_data( $fields, SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, FALSE, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function update_user_agreement( $user_id )
	{
		$result 		= array();
		$where 			= array();
		$val 			= array();

		try
		{
			$where['user_id'] 		= $user_id;
			$val['agreement_flag']	= 1;
			$val['agreed_date']		= date('Y-m-d H:i:s');

			$result 	= $this->update_data( SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, $val, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}

	public function get_user_organizations($user_id, $main_org_flag = NULL)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			if( !IS_NULL( $main_org_flag ) )
			{
				$add_where 	.= " AND a.main_org_flag = ? ";
				$extra_val[] = $main_org_flag;
			}

			$query 		= "
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	a.user_id = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS
			);

			$val[] 		= $user_id;

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_user_organizations_temp($user_id, $main_org_flag = NULL)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			if( !IS_NULL( $main_org_flag ) )
			{
				$add_where 	.= " AND a.main_org_flag = ? ";
				$extra_val[] = $main_org_flag;
			}

			$query 		= "
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	a.user_id = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS
			);

			$val[] 		= $user_id;

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function count_user_device_location($user_id)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{

			$query 		= "
				SELECT 	COUNT(a.user_id) as check_user_device_location
				FROM 	%s a 
				WHERE 	a.user_id = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH
			);

			$val[] 		= $user_id;

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_user_device_location($user_id, $ip_address, $os, $details = FALSE)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{

			if( !$details )
			{
				$add_where .= " AND a.authorized = ? ";
				$extra_val[] = ENUM_YES;
			}

			$query 		= "
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	a.user_id = ?
					AND a.ip_address = ?
					AND a.os = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_USER_DEVICE_LOCATION_AUTH
			);

			$val[] 		= $user_id;
			$val[] 		= $ip_address;
			$val[] 		= $os;

			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}
}