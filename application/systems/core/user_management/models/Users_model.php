<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users_model extends SYSAD_Model 
{
	
	private $users;
	private $sys_param;
	private $user_roles;
	private $organizations;
	private $user_history;
	private $temp_users;
	
	public function __construct()
	{	
		parent::__construct();
		
		$this->users 			= parent::CORE_TABLE_USERS;
		$this->sys_param 		= parent::CORE_TABLE_SYS_PARAM;
		$this->user_roles 		= parent::CORE_TABLE_USER_ROLES;
		$this->roles	 		= parent::CORE_TABLE_ROLES;
		$this->organizations 	= parent::CORE_TABLE_ORGANIZATIONS;
		$this->user_history 	= parent::CORE_TABLE_USER_HISTORY;
		$this->temp_users 		= parent::CORE_TABLE_TEMP_USERS;
	}
	
	
	public function get_user_list($filter_status = NULL, $params = NULL, $account_creator = NULL)
	{
		try 
		{
			
		
			
			// Initialize variables
			$where	= $group = $order = $limit = "";
			$val	= array();
			
			$signup = FALSE;
			// Default validation
			$val[] = SYS_PARAM_STATUS;
			$val[] = ANONYMOUS_ID;
				
			if( $filter_status === NULL)
			{
			
				$where.= " AND B.sys_param_value NOT IN (?,?,?,?) ";
				$val[] = PENDING;
				$val[] = APPROVED;
				$val[] = DISAPPROVED;
				$val[] = DELETED;
			}
			else
			{
				switch($filter_status)
				{
					case '0':
						$where.= " AND B.sys_param_value IN (?,?,?) ";
						$val[] = PENDING;
						$val[] = APPROVED;
						$val[] = DISAPPROVED;
						
						$signup	= TRUE;
					break;
					
					case PENDING:
					case APPROVED:
					case DISAPPROVED:
					case INCOMPLETE:
						$where.= " AND B.sys_param_value = ? ";
						$val[] = $filter_status;
						$signup	= TRUE;
					break;
					case STATUS_ACTIVE :
						$where.= " AND B.sys_param_value = ? ";
						$val[] = ACTIVE;
					break;
					case STATUS_INACTIVE :
						$where.= " AND B.sys_param_value = ? ";
						$val[] = INACTIVE;
					break;
					case STATUS_BLOCKED :
						$where.= " AND B.sys_param_value = ? ";
						$val[] = BLOCKED;
					break;
					
					default:
						$where.= " AND B.sys_param_value IN (?,?) ";
						$val[] = ACTIVE;
						$val[] = BLOCKED;
						
						$signup	= FALSE;
					break;
				
				}			
			}

			
			// If params variable is null, get the total count of records
			if($params === NULL)
			{
				$fields	= 'COUNT(DISTINCT(A.user_id)) total';
			}
			else
			{
				$fields			= array('A.nickname', 'A.username', 'A.fname','A.lname', 'A.email', 'A.job_title');
				$decrypt_fields = aes_crypt($fields, FALSE);

				$aes_decrypt_fname = 'CAST( '. aes_crypt('A.fname', FALSE, FALSE).' AS char(100) )';
				$aes_decrypt_lname = 'CAST( '. aes_crypt('A.lname', FALSE, FALSE). ' AS char(100) )';	

				$aes_decrypt_infname = 'CAST( '. aes_crypt('H.fname', FALSE, FALSE).' AS char(100) )';
				$aes_decrypt_inlname = 'CAST( '. aes_crypt('H.lname', FALSE, FALSE). ' AS char(100) )';	

				$aes_decrypt_blfname = 'CAST( '. aes_crypt('I.fname', FALSE, FALSE).' AS char(100) )';
				$aes_decrypt_bllname = 'CAST( '. aes_crypt('I.lname', FALSE, FALSE). ' AS char(100) )';	

				$aes_decrypt_clfname = 'CAST( '. aes_crypt('L.fname', FALSE, FALSE).' AS char(100) )';
				$aes_decrypt_cllname = 'CAST( '. aes_crypt('L.lname', FALSE, FALSE). ' AS char(100) )';	

				$reason_field 	= '';

				switch( $filter_status )
				{
					case STATUS_INACTIVE :
					case STATUS_EXPIRED :

						$reason_field 	= "
							IF( A.inactivated_by IS NOT NULL, CONCAT(A.reason,' - ',DECRYPT(H.fname),' ',DECRYPT(H.lname),'(',A.inactivated_date,')'), CONCAT(A.reason,' (',A.inactivated_date,')') ) as reason_remarks,
";
					break;

					case STATUS_BLOCKED :

						$reason_field 	= "
							IF( A.blocked_by IS NOT NULL, CONCAT(A.reason,' - ', DECRYPT(I.fname),' ',DECRYPT(I.lname),'(',A.blocked_date,')'), CONCAT(A.reason,' (',A.blocked_date,')') ) as reason_remarks,
";
					break;
				}


				$fields = '
					SQL_CALC_FOUND_ROWS
						A.user_id,
						DECRYPT(A.nickname) nickname,
						DECRYPT(A.username) username,
						DECRYPT(A.fname) fname,
						DECRYPT(A.lname) lname, 
						DECRYPT(A.email) email, 
						DECRYPT(A.job_title) job_title,						
						A.last_logged_in_date,						
						A.photo,
						A.built_in_flag,
						A.contact_flag,
						A.logged_in_flag,
						CONCAT( DECRYPT(L.fname)," ", DECRYPT(L.lname) ," <br/>", DATE_FORMAT( A.created_date, "%m/%d/%Y %h:%i" ) )
						 as date_created,
						CONCAT( DECRYPT(A.fname)," ",DECRYPT(A.lname) ) as merge_name,
						GROUP_CONCAT(DISTINCT G.name SEPARATOR "<br>") as organization_name,
						B.sys_param_code,
						B.sys_param_value,
						B.sys_param_name,
						C.name org_name,
						'.$reason_field.'
						GROUP_CONCAT(DISTINCT E.role_name SEPARATOR ",<br>") roles
				';
				
				
				$filters = $this->_construct_filters($filter_status, $signup, $params);
				
				// if the query has already a WHERE keyword, set to TRUE otherwise FALSE
				$filter	= $this->filtering($filters['filters'], $params, TRUE);
				$order	= $this->ordering($filters['orders'], $params);
				$limit	= $this->paging($params);
				
				$where	= $where . $filter["search_str"];
				$val	= array_merge($val,$filter["search_params"]);
				$group	= 'GROUP BY A.user_id';
			}

			$table_us 		= $this->users;
			$table_us_rol 	= SYSAD_Model::CORE_TABLE_USER_ROLES;
			$table_us_org 	= SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS;
			$add_left_j = '';
			
			if( !EMPTY( $account_creator ) AND ( $account_creator == VISITOR_NOT_APPROVAL OR $account_creator == VISITOR ) AND ( $filter_status === '0' OR $filter_status === '4' OR $filter_status == '11' ) )
			{
				$table_us = $this->temp_users;
				$table_us_rol 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES;
				$table_us_org 	= SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS;
				$add_left_j = ' LEFT ';
			}
			
			$query = '
				SELECT 
				' . $fields . '

				FROM ' . $table_us . ' A
 				
				JOIN ' . parent::CORE_TABLE_SYS_PARAM . '  B ON A.status = B.sys_param_code  					
					AND B.sys_param_type = ?

				LEFT JOIN ' . parent::CORE_TABLE_ORGANIZATIONS. ' C ON A.org_code = C.org_code 
					
				LEFT JOIN ' . $table_us_rol . ' D ON A.user_id = D.user_id

				LEFT JOIN ' . parent::CORE_TABLE_ROLES . ' E ON D.role_code = E.role_code

				LEFT JOIN ' .$table_us_org. ' F ON A.user_id = F.user_id

				LEFT JOIN ' .SYSAD_Model::CORE_TABLE_ORGANIZATIONS. ' G ON F.org_code = G.org_code

				LEFT JOIN '. $table_us .' H ON A.inactivated_by = H.user_id

				LEFT JOIN '. $table_us .' I ON A.blocked_by = I.user_id

				LEFT JOIN '. $table_us .' L ON A.created_by = L.user_id
  
				WHERE A.user_id != ? 
				' . $where . '
				' . $group . '
				' . $order . '
				' . $limit . '  
			';
			
			// If params variable is null, get the total records				
			if(empty($params))
			{
				$total = $this->query($query, $val, TRUE, FALSE);
				
				return $total['total'];
			}
			else
			{			
				return array(
					'records'			=> $this->query($query, $val),
					'display_records'	=> $this->_get_display_records()
				);
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
	
	private function _construct_filters($filter_status = NULL, $signup,  &$params)
	{
		if($signup)
		{
			
			$decrypt 	= array('A.fname', 'A.lname');
			$decrypt	= aes_crypt($decrypt, FALSE, FALSE, FALSE);
			$full_name	= 'CONCAT( '.$decrypt[0].' , " ",  '.$decrypt[1].' )';
			
			if( isset($params['full_name']) )
				$params[$full_name]	= $params['full_name'];
			
			if( isset($params['agency']) )
				$params['C.name']	= $params['agency'];
			
			if( isset($params['job_title']) )
				$params['A.job_title']	= $params['job_title'];
			
			if( isset($params['email']) )
				$params['A.email']	= $params['email'];
			
			if( isset($params['status']) )
				$params['B.sys_param_value']	= $params['status'];
			
			
			$filters	= array(
				$full_name,
				'C.name',
				array('A.job_title', 'DECRYPT'),
				array('A.email', 'DECRYPT'),
				'B.sys_param_value'
			);

			$orders 	= array(
				$full_name,
				'C.name',
				array('A.job_title', 'DECRYPT'),
				array('A.email', 'DECRYPT'),
				'B.sys_param_value'
			);
			
		}
		else 
		{
			if( isset($params['username']) )
			$params['A.username']	= $params['username'];
		
			if( isset($params['first_name']) )
				$params['A.fname']	= $params['first_name'];
			
			if( isset($params['last_name']) )
				$params['A.lname']	= $params['last_name'];
			
			if( isset($params['email']) )
				$params['A.email']	= $params['email'];
			
			if( isset($params['roles']) )
				$params['E.role_name']	= $params['roles'];
			
			$filter_col = 'E.role_name';
			
			if( ! empty($filter_status))
			{
				if($filter_status == STATUS_INACTIVE)						
					$filter_col = 'A.last_logged_in_date';						
			}				
			else 
			{
				$filter_col = 'E.role_name';
			}

			$aes_decrypt_fname = 'CAST( '. aes_crypt('A.fname', FALSE, FALSE).' AS char(100) )';
			$aes_decrypt_lname = 'CAST( '. aes_crypt('A.lname', FALSE, FALSE). ' AS char(100) )';

			$aes_decrypt_infname = 'CAST( '. aes_crypt('H.fname', FALSE, FALSE).' AS char(100) )';
			$aes_decrypt_inlname = 'CAST( '. aes_crypt('H.lname', FALSE, FALSE). ' AS char(100) )';	

			$aes_decrypt_blfname = 'CAST( '. aes_crypt('I.fname', FALSE, FALSE).' AS char(100) )';
			$aes_decrypt_bllname = 'CAST( '. aes_crypt('I.lname', FALSE, FALSE). ' AS char(100) )';	

			$reason_field 		= '';
			$reason_order 		= '';

			switch( $filter_status )
			{
				case STATUS_INACTIVE :
					case STATUS_EXPIRED :

						$reason_field 	= "
							IF( A.inactivated_by IS NOT NULL, CONCAT(A.reason,' - ',".$aes_decrypt_infname.",' ',".$aes_decrypt_inlname.",'(',A.inactivated_date,')'), CONCAT(A.reason,' (',A.inactivated_date,')') ) convert_to date_created
";
						$reason_order 	= "
							reason_remarks
";
					break;

					case STATUS_BLOCKED :

						$reason_field 	= "
							IF( A.blocked_by IS NOT NULL, CONCAT(A.reason,' - ',".$aes_decrypt_blfname.",' ',".$aes_decrypt_bllname.",'(',A.blocked_date,')'), CONCAT(A.reason,' (',A.blocked_date,')') ) convert_to date_created
";
						$reason_order 	= "
							reason_remarks
";
				break;

				default :

					$reason_field = 'CONCAT( '.$aes_decrypt_fname.'," ",'.$aes_decrypt_lname.'," ", DATE_FORMAT( A.created_date, "%m/%d/%Y %h:%i" ) ) convert_to date_created';

					$reason_order 	= 'date_created';

				break;
			}
		
			$filters	= array(
				array('A.username', 'DECRYPT'),
				'CONCAT( '.$aes_decrypt_fname.'," ",'.$aes_decrypt_lname.' ) convert_to merge_name',
				'G.name convert_to organization_name',
				array('A.email', 'DECRYPT'),
				$filter_col,
				$reason_field
			);


			$orders = array(
				'',
				array('A.username', 'DECRYPT'),
				'CONCAT( '.$aes_decrypt_fname.'," ",'.$aes_decrypt_lname.' )',
				'G.name',
				array('A.email', 'DECRYPT'),
				$filter_col,
				$reason_order
			);
		}
		
		return array(
			'filters' => $filters,
			'orders' => $orders
		);
	}
	
	private function _get_display_records()
	{
		try
		{
			$query = "SELECT FOUND_ROWS() cnt";
			
			$count = $this->query($query, NULL, TRUE, FALSE);
			
			return $count['cnt'];
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}
	
                
	public function get_user_details($user_id, $account_creator = NULL)
	{

		$add_where 	= "";
		$extra_val 	= array();

		$val 		= array();

		try
		{
			
			$fields 	= array(
				'A.fname', 'A.lname', 'A.email', 'A.mname', 'A.ext_name', 
				'A.username', 'A.nickname', 'A.job_title', 'A.contact_no', 'A.mobile_no',
				'A.facebook_email', 'A.google_email'
			);
			
			$decrypt	= aes_crypt($fields, FALSE);
			$multiple 	= FALSE;

			$table_us 	= $this->users;
			$table_roles = SYSAD_Model::CORE_TABLE_USER_ROLES;
			$table_orgs = SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
			$add_left_j = '';
			$add_left_j_orgs = ' LEFT ';

			if( !EMPTY( $account_creator ) AND ( $account_creator == VISITOR_NOT_APPROVAL OR $account_creator == VISITOR ) )
			{
				$table_us = $this->temp_users;
				$add_left_j = ' LEFT ';
				$add_left_j_orgs = ' LEFT ';
				$table_roles = SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES;
				$table_orgs = SYSAD_Model::CORE_TABLE_ORGANIZATIONS;
			}

			if( !EMPTY( $user_id ) )
			{
				if( is_array( $user_id ) )
				{
					$count 				= count( $user_id );
					$placeholder 		= str_repeat('?,', $count);
					$placeholder 		= rtrim($placeholder, ',');

					$add_where .= " AND A.user_id NOT IN ( $placeholder ) ";
					$extra_val	= array_merge( $extra_val, $user_id );

					$multiple 	= TRUE;
				}
				else
				{
					$add_where .= " AND A.user_id = ? ";
					$extra_val[] = $user_id;
				}
			}
			else
			{
				if( $user_id == 0 )
				{
					$add_where .= " AND A.user_id = ? ";
					$extra_val[] = $user_id;
				}
			}
			
			$query = 
			'
				SELECT
					A.user_id,
					A.location_code,
					A.org_code,
					A.password,
					A.salt,
					A.reset_salt,
					A.gender,
					A.photo,
					A.logged_in_flag,
					A.mail_flag,
					A.contact_flag,
					A.status,
					A.reason,
					A.attempts,
					A.initial_flag,
					A.pw_email_flag,
					A.last_logged_in_date,
					A.built_in_flag,
					A.consent_form_sys_filename, 
					A.consent_form_orig_filename,
					A.created_by,
					A.expire_dpa_date,
					A.receive_email_flag,
					A.receive_sms_flag,
					A.temporary_account_flag,
					A.temporary_account_expiration_date,
					A.sign_up_api,
					A.product_subscription_notif_flag,
					' . $decrypt . ',
					GROUP_CONCAT(B.role_code SEPARATOR ",") roles, 
					GROUP_CONCAT(D.role_name SEPARATOR ",") role_names,
					C.name org_name
										
				FROM ' . $table_us . ' A
				
				'.$add_left_j.'JOIN ' . $table_roles . ' B ON A.user_id = B.user_id
				
				'.$add_left_j_orgs.'JOIN ' .$table_orgs . ' C ON A.org_code = C.org_code

				'.$add_left_j.'JOIN '. parent::CORE_TABLE_ROLES . ' D ON B.role_code = D.role_code 
				
				WHERE 1 = 1
					'.$add_where.'
			';

			$val 	= array_merge($val, $extra_val);
			
			return $this->query($query, $val, TRUE, $multiple);
			
			
		}	
		catch (PDOException $e)
		{
			throw $e;
		}
	}
	
	public function filtered_length($aColumns, $bColumns, $params)
	{
		$stmt 		= array();

		try
		{
			$this->get_user_list($aColumns, $bColumns, $params);
			
			$query = <<<EOS
				SELECT FOUND_ROWS() cnt
EOS;
	
			$stmt = $this->query($query, NULL, TRUE, FALSE);
		
			
		}
		catch (PDOException $e)
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
			$where 	= array();
			
			$fields = array("COUNT(user_id) cnt");
			
			$where["status"]["!="] 	= 'STATUS_INACTIVE';
			$where["user_id"]["!="] = ANONYMOUS_ID;
			
			$result = $this->select_data($fields, $this->users, FALSE, $where);
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	public function get_specific_user($id, $email = FALSE, $multiple=TRUE, $account_creator = NULL)
	{
		$result 	= array();

		try
		{
			
			$fields 	= array(
				'fname', 'lname', 'email', 'mname', 'ext_name', 
				'username', 'nickname', 'job_title', 'contact_no', 'mobile_no'
			);
			
			$decrypt	= aes_crypt($fields, FALSE, TRUE, FALSE);
	
			$fields 	= array(
				'user_id',
				'location_code',
				'org_code',
				'password',
				'salt',
				'reset_salt',
				'gender',
				'photo',
				'logged_in_flag',
				'mail_flag',
				'contact_flag',
				'status',
				'reason',
				'attempts',
				'initial_flag',
				'pw_email_flag',
				'last_logged_in_date',
				'built_in_flag',
				'receive_email_flag',
				'receive_sms_flag'
			);

			$table_us 	= $this->users;
			$add_left_j = '';

			if( !EMPTY( $account_creator ) AND ( $account_creator == VISITOR_NOT_APPROVAL OR $account_creator == VISITOR ) )
			{
				$table_us = $this->temp_users;
				$add_left_j = ' LEFT ';
			}

			$fields = array_merge($fields, $decrypt);
			
			if( $email )
			{
				$where 	= array(aes_crypt("email",FALSE, FALSE) => $id);
			}
			else
			{
				$where 	= array("user_id" => $id);
			}
				
			return $this->select_data($fields, $table_us, $multiple, $where);
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		
		
	}
	
	public function insert_user($params, $account_creator_t = NULL)
	{
		$user_id 			= 0;

		try
		{		
			$val 			= array();
			
			$salt 			= gen_salt(TRUE);
			
			$status 		= ISSET($params["status"]) ? filter_var($params["status"], FILTER_SANITIZE_NUMBER_INT) : INACTIVE;

			$account_creator = get_setting(ACCOUNT, "account_creator");


			$sp_status 		= get_sys_param_code(SYS_PARAM_STATUS, $status);

			if( ISSET( $params['gender'] ) AND !EMPTY( $params['gender'] ) )
			{
			
				$sp_gender 		= get_sys_param_code(SYS_PARAM_GENDER, filter_var($params['gender'], FILTER_SANITIZE_STRING));
				$val["gender"] 		= $sp_gender["sys_param_code"];
				
			}

			$val["lname"] 		= array(filter_var($params['lname'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			$val["fname"] 		= array(filter_var($params['fname'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			if( ISSET( $params['mname'] ) )
			{
				$val["mname"] 		= array(filter_var($params['mname'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}
			$val["nickname"] 	= ISSET($params['nickname']) ? array(filter_var($params['nickname'], FILTER_SANITIZE_STRING), 'ENCRYPT') : NULL;
			
			$val["contact_no"] 	= ISSET($params['contact_no']) ? array(filter_var($params['contact_no'], FILTER_SANITIZE_NUMBER_INT), 'ENCRYPT') : NULL;
			$val["mobile_no"] 	= ISSET($params['mobile_no']) ? array(filter_var($params['mobile_no'], FILTER_SANITIZE_NUMBER_INT), 'ENCRYPT') : NULL;
			$val["email"] 		= array(filter_var($params['email'], FILTER_SANITIZE_STRING), 'ENCRYPT');

			if( ISSET( $params['org'] ) )
			{
				$val["org_code"] 	= filter_var($params["org"], FILTER_SANITIZE_STRING);
			}

			if( ISSET( $params['job_title'] ) )
			{

				$val["job_title"] 	= array(filter_var($params['job_title'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}
			$val["photo"] 		= !EMPTY($params['image']) ? filter_var($params['image'], FILTER_SANITIZE_STRING) : NULL;
			$val["status"] 		= $sp_status["sys_param_code"];
			$val['facebook_email']	= NULL;
			$val['google_email']	= NULL;

			if( ISSET( $params['ext_name'] ) )
			{
				$val["ext_name"] 		= array($params['ext_name'], 'ENCRYPT');
			}


			if( ISSET( $params['facebook_email'] ) AND !EMPTY( $params['facebook_email'] ) )
			{
				$val["facebook_email"] 		= array(filter_var($params['facebook_email'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}

			if( ISSET( $params['google_email'] ) AND !EMPTY( $params['google_email'] ) )
			{
				$val["google_email"] 		= array(filter_var($params['google_email'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}

			$val['receive_sms_flag'] = ENUM_NO;	
			$val['receive_email_flag'] = ENUM_NO;
			$val['temporary_account_flag'] 				= ENUM_NO;
			$val['temporary_account_expiration_date'] 	= NULL;

			if( ISSET( $params['temp_account_flag'] ) )
			{
				$val['temporary_account_flag'] = ENUM_YES;

				if( ISSET($params['temp_expiration_date']) AND !EMPTY( $params['temp_expiration_date'] ) ) 
				{
					$val['temporary_account_expiration_date'] = $params['temp_expiration_date'];
				}
			}

			if( ISSET( $params['receive_email'] ) )
			{
				$val['receive_email_flag'] = ENUM_YES;
			}

			if( ISSET( $params['receive_sms'] ) )
			{
				$val['receive_sms_flag'] = ENUM_YES;	
			}

			$sess_id 			= NULL;

			if( $this->session->has_userdata('user_id') )
			{
				$sess_id 		= $this->session->user_id;
			}

			if( $status == INACTIVE )
			{
				$val['inactivated_by']		= $sess_id;
				$val['inactivated_date'] 	= date('Y-m-d H:i:s');
				$val['reason'] 				= 'Manually Deactivated';
			}
			else if( $status == BLOCKED )
			{
				$val['blocked_by']		= $sess_id;
				$val['blocked_date'] 	= date('Y-m-d H:i:s');
				$val['reason'] 			= 'Manually Blocked';
			}
			else
			{
				$val['blocked_by']		= NULL;
				$val['blocked_date'] 	= NULL;
				$val['reason'] 			= NULL;	
				$val['inactivated_by']		= NULL;
				$val['inactivated_date'] 	= NULL;
			}

			if( ISSET( $params['expire_dpa_date'] ) ) 
			{
				$val['expire_dpa_date']	= $params['expire_dpa_date'];
			}
			
			if(ISSET($params['contact_type']))
			{
				$val["contact_flag"] = filter_var($params['contact_type'], FILTER_SANITIZE_NUMBER_INT);
				
				if($params["contact_type"] == 0)
				{
					if( ISSET( $params['password'] ) )
					{

						if( $params['password_creation'] == SET_ADMINISTRATOR )
						{
						
							$clean_password 	= preg_replace('/\s+/', '', $params['password']);
							
							$val["password"] 	= in_salt($clean_password, $salt, TRUE);
							$val["salt"] 		= $salt;
						}
						
					}

					if( $params['password_creation'] == SET_SYSTEM_GENERATED 
						OR $params['password_creation'] == SET_ACCOUNT_OWNER
					)
					{
						$clean_password 	= $params['system_generated_password'];
						$val["password"] 	= in_salt($clean_password, $salt, TRUE);
						$val["salt"] 		= $salt;
					}
					
					if(ISSET($params["username"]))
					{
						$val["username"] 	= array(filter_var(preg_replace('/\s+/', '', $params['username'], FILTER_SANITIZE_STRING)), 'ENCRYPT');
					}
						
				}else{
					$val["password"] 		= NULL;
					$val["salt"] 			= NULL;
					$val["username"] 		= NULL;
				}
			}
			
			$val['initial_flag']	= INITIAL_YES;
			$val["created_by"] 		= $this->session->user_id;
			$val["created_date"] 	= date('Y-m-d H:i:s');
			
			if(ISSET($params["send_email"]))
			{
			  $val["mail_flag"] = filter_var($params["send_email"], FILTER_SANITIZE_NUMBER_INT);
			}

			$table_us 	= $this->users;

			if( !EMPTY( $account_creator_t ) AND 
				( $account_creator_t == VISITOR_NOT_APPROVAL OR 
					$account_creator_t == VISITOR
				)
			)
			{
				$table_us = $this->temp_users;
			}
			
			$user_id = $this->insert_data($table_us, $val, TRUE);

			if( EMPTY( $account_creator_t ) OR  
				( !EMPTY( $account_creator_t ) AND 
					( $account_creator_t != VISITOR_NOT_APPROVAL AND $account_creator_t != VISITOR )
				)
			)
			{
				if(!EMPTY($user_id))
				{
					$this->_insert_user_agreements($user_id);
				}
				
				if($params["contact_type"] == 0)
				{
					if(!EMPTY($user_id) && !EMPTY($params["main_role"]))
					{
						$this->_insert_user_roles($params["main_role"], $user_id, TRUE);
					}
					
					if(!EMPTY($user_id) && !EMPTY($params["role"]))
					{
						$this->_insert_user_roles($params["role"], $user_id);
					}

					if(!EMPTY($user_id) && ISSET($params["org"]) && !EMPTY($params["org"]))
					{
						$this->_insert_user_organizations(array($params["org"]), $user_id, TRUE);
					}

					if(!EMPTY($user_id) AND 
						ISSET($params['other_orgs']) AND !EMPTY($params["other_orgs"])
						AND ISSET($params['other_orgs'][0]) AND !EMPTY($params["other_orgs"][0])
					)
					{
						$this->_insert_user_organizations($params["other_orgs"], $user_id);
					}
				}
			}
			else
			{
				if(!ISSET($params["contact_type"]) OR $params["contact_type"] == 0)
				{

					if(!EMPTY($user_id) && !EMPTY($params["org"]))
					{
						$this->_insert_user_organizations_temp(array($params["org"]), $user_id, TRUE);
					}

					if(!EMPTY($user_id) AND 
						ISSET($params['other_orgs']) AND !EMPTY($params["other_orgs"])
						AND ISSET($params['other_orgs'][0]) AND !EMPTY($params["other_orgs"][0])
					)
					{
						$this->_insert_user_organizations_temp($params["other_orgs"], $user_id);
					}
				}
			}
			
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $user_id;
	}	

	public function _insert_user_agreements($user_id)
	{
		try
		{
			$params["user_id"] 					= $user_id;

			$this->insert_data(SYSAD_Model::CORE_TABLE_USER_AGREEMENTS, $params);
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{			
			throw $e;
		}	
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

	public function insert_helper($table, array $val)
	{
		$id = NULL;
		try
		{
			$id = $this->insert_data( $table, $val, TRUE, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $id;
	}

	public function get_user_multi_auth($user_id, $authentication_factor_id = NULL, $table = SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, $not_authenticated = FALSE)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			if( !EMPTY( $authentication_factor_id ) )
			{
				$add_where 	.= " AND a.authentication_factor_id = ? ";
				$extra_val[] = $authentication_factor_id; 
			}

			if( !EMPTY( $not_authenticated ) )
			{
				$add_where 	.= " AND a.authenticated_date IS NULL ";
			}

			$query 		= "
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	a.user_id = ?
					$add_where
";
			$query 		= sprintf( $query,
				$table
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

	public function get_authentication_factors()
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
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	1 = 1
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTORS
			);

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}			


	/**
	 * Use This helper function to save in either table ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH ) based in account setting general auth  factor
	 * 
	 * 
	 * @param  $user_id -- required. id of the user
	 * @param  $table -- required. ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
	 * @throws PDOException
	 * @throws Exception
	 * @return array - audit trail
	 */
	public function save_multi_auth_helper($user_id, $table)
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			$auth_factors 		= $this->get_authentication_factors();

			if( !EMPTY( $auth_factors ) )
			{
				$ins_val 		= array();
				$k = 0;
				foreach($auth_factors as $key => $auth)
				{
					$check_multi_auth_factor = check_multi_auth_factor($auth['authentication_factor_id']);

					$get_user_multi_auth 	= $this->get_user_multi_auth($user_id, $auth['authentication_factor_id'], $table);

					if( !EMPTY( $check_multi_auth_factor ) AND EMPTY( $get_user_multi_auth ) )
					{
						$ins_val[$k]['user_id'] = $user_id;
						$ins_val[$k]['authentication_factor_id'] = $auth['authentication_factor_id'];

						$k++;
					}
				}

				$main_where 	= array(
					'user_id'	=> $user_id
				);

				if( !EMPTY( $ins_val ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= $table;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->insert_helper($table, $ins_val);

					$curr_detail[] 	= $this->get_details_for_audit( $table, $main_where );
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
			'prev_detail'	=> $prev_detail,
			'curr_detail'	=> $curr_detail,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'audit_schema'	=> $audit_action
		);
	}

	/**
	 * Use This helper function to save in either table ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH ) based in account setting section auth factor ( AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
	 * 
	 * 
	 * @param  $user_id -- required. id of the user
	 * @param  $table -- required. ( SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
	 * @param  $auth_section -- required. ( AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
	 * @throws PDOException
	 * @throws Exception
	 * @return array - audit trail
	 */
	public function save_multi_auth_section_helper($user_id, $table, $auth_section)
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table 			= array();
		$audit_action 			= array();
		$audit_schema 			= array();

		try
		{
			$auth_factors 		= $this->get_authentication_sections_factors($auth_section);

			if( !EMPTY( $auth_factors ) )
			{
				$ins_val 		= array();
				$kk = 0;
				foreach($auth_factors as $key => $auth)
				{
					$check_multi_auth_factor = check_multi_auth_factor_section($auth['authentication_factor_id'], $auth_section);
					
					$get_user_multi_auth 	= $this->get_user_multi_auth($user_id, $auth['authentication_factor_id'], $table);

					if( !EMPTY( $check_multi_auth_factor ) AND EMPTY( $get_user_multi_auth ) )
					{
						$ins_val[$kk]['user_id'] = $user_id;
						$ins_val[$kk]['authentication_factor_id'] = $auth['authentication_factor_id'];

						$kk++;
					}
				}

				$main_where 	= array(
					'user_id'	=> $user_id
				);
				
				if( !EMPTY( $ins_val ) )
				{
					$audit_schema[] 	= DB_CORE;
					$audit_table[] 	 	= $table;
					$audit_action[] 	= AUDIT_INSERT;
					$prev_detail[]  	= array();

					$this->insert_helper($table, $ins_val);

					$curr_detail[] 	= $this->get_details_for_audit( $table, $main_where );
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
			'prev_detail'	=> $prev_detail,
			'curr_detail'	=> $curr_detail,
			'audit_table' 	=> $audit_table,
			'audit_action' 	=> $audit_action,
			'audit_schema'	=> $audit_schema
		);
	}
	
	public function update_user($params, $account_creator_t = NULL)
	{
		try
		{
			$val 		= array();
			$where 		= array();
			
			$user_id 	= filter_var($params["user_id"], FILTER_SANITIZE_NUMBER_INT);
			
			$status 		= ISSET($params["status"]) ? filter_var($params["status"], FILTER_SANITIZE_NUMBER_INT) : INACTIVE;
			$sp_status 		= get_sys_param_code(SYS_PARAM_STATUS, $status);

			if( ISSET( $params['gender'] ) AND !EMPTY( $params['gender'] ) )
			{
			
				$sp_gender 		= get_sys_param_code(SYS_PARAM_GENDER, filter_var($params['gender'], FILTER_SANITIZE_STRING));
				$val["gender"] 			= $sp_gender["sys_param_code"];
			}
			
			$val["lname"] 			= array(filter_var($params['lname'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			$val["fname"] 			= array(filter_var($params['fname'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			if( ISSET( $params['mname'] ) )
			{
				$val["mname"] 			= array(filter_var($params['mname'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}
			if( ISSET( $params['nickname'] ) )
			{
				$val["nickname"] 		= array(filter_var($params['nickname'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}
			
			$val["contact_no"] 		= array(filter_var($params['contact_no'], FILTER_SANITIZE_NUMBER_INT), 'ENCRYPT');
			$val["mobile_no"] 		= array(filter_var($params['mobile_no'], FILTER_SANITIZE_NUMBER_INT), 'ENCRYPT');
			if( ISSET( $params['email'] ) )
			{
				$val["email"] 			= array(filter_var($params['email'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}
			if( ISSET( $params['job_title'] ) )
			{
				$val["job_title"] 		= array(filter_var($params['job_title'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}
			$val["photo"] 			= !EMPTY($params['image']) ? filter_var($params['image'], FILTER_SANITIZE_STRING) : NULL;
			$val["contact_flag"] 	= filter_var($params['contact_type'], FILTER_SANITIZE_NUMBER_INT);
			$val["status"] 			= $sp_status["sys_param_code"];
			$val["modified_by"]		= $this->session->userdata("user_id");

			$val['facebook_email']	= NULL;
			$val['google_email']	= NULL;

			if( ISSET( $params['ext_name'] ) )
			{
				$val["ext_name"] 		= array($params['ext_name'], 'ENCRYPT');
			}

			if( ISSET( $params['facebook_email'] ) AND !EMPTY( $params['facebook_email'] ) )
			{
				$val["facebook_email"] 		= array(filter_var($params['facebook_email'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}

			if( ISSET( $params['google_email'] ) AND !EMPTY( $params['google_email'] ) )
			{
				$val["google_email"] 		= array(filter_var($params['google_email'], FILTER_SANITIZE_STRING), 'ENCRYPT');
			}

			$val['receive_sms_flag'] = ENUM_NO;	
			$val['receive_email_flag'] = ENUM_NO;

			$val['temporary_account_flag'] 				= ENUM_NO;
			$val['temporary_account_expiration_date'] 	= NULL;

			if( ISSET( $params['temp_account_flag'] ) )
			{
				$val['temporary_account_flag'] = ENUM_YES;

				if( ISSET($params['temp_expiration_date']) AND !EMPTY( $params['temp_expiration_date'] ) ) 
				{
					$val['temporary_account_expiration_date'] = $params['temp_expiration_date'];
				}
			}

			if( ISSET( $params['receive_email'] ) )
			{
				$val['receive_email_flag'] = ENUM_YES;
			}

			if( ISSET( $params['receive_sms'] ) )
			{
				$val['receive_sms_flag'] = ENUM_YES;	
			}

			if( $sp_status['sys_param_code'] == STATUS_ACTIVE )
			{
				$val['attempts'] 	= 0;
				$val['soft_attempts'] = 0;
			}

			$sess_id 			= NULL;

			if( $this->session->has_userdata('user_id') )
			{
				$sess_id 		= $this->session->user_id;
			}

			if( $status == INACTIVE )
			{
				$val['inactivated_by']		= $sess_id;
				$val['inactivated_date'] 	= date('Y-m-d H:i:s');
				$val['reason'] 				= 'Manually Deactivated';
			}
			else if( $status == BLOCKED )
			{
				$val['blocked_by']		= $sess_id;
				$val['blocked_date'] 	= date('Y-m-d H:i:s');
				$val['reason'] 			= 'Manually Blocked';
			}
			else
			{
				$val['blocked_by']		= NULL;
				$val['blocked_date'] 	= NULL;
				$val['reason'] 			= NULL;	
				$val['inactivated_by']		= NULL;
				$val['inactivated_date'] 	= NULL;
			}
			
			if(ISSET($params["org"]))
				$val["org_code"] 	= filter_var($params["org"], FILTER_SANITIZE_STRING);
			
			if($params["contact_type"] == 0)
			{
				if( ISSET( $params['password'] ) && !EMPTY( $params['password'] ) )
				{
					if( $params['password_creation'] == SET_ADMINISTRATOR )
					{
						$clean_password 	= preg_replace('/\s+/', '', $params['password']);
						$salt 				= gen_salt(TRUE);
						$val["password"] 	= in_salt($clean_password, $salt, TRUE);
						$val["salt"] 		= $salt;
					}
				}
				
				if(ISSET($params["username"]))
				{
					$val["username"] 	= array(filter_var(preg_replace('/\s+/', '', $params['username'], FILTER_SANITIZE_STRING)), 'ENCRYPT');
				}
				
			}else{
				$val["password"] 		= NULL;
				$val["salt"] 			= NULL;
				$val["username"] 		= NULL;
			}
			
			$where["user_id"] 		= $user_id;

			$table_us 	= $this->users;

			if( !EMPTY( $account_creator_t ) AND 
				( $account_creator_t == VISITOR_NOT_APPROVAL OR $account_creator_t == VISITOR )
			)
			{
				$table_us = $this->temp_users;
			}
				
			$this->update_data($table_us, $val, $where);

			if( EMPTY( $account_creator_t ) OR  
				( !EMPTY( $account_creator_t ) 
					AND ( $account_creator_t != VISITOR_NOT_APPROVAL AND $account_creator_t != VISITOR )
				)
			)
			{
			
				if($params["contact_type"] == 0)
				{
					if(ISSET($params["main_role"]) && !EMPTY($params["main_role"]))
					{
						$this->delete_data($this->user_roles, array('user_id' => $user_id, 'main_role_flag' => 1));
						$this->_insert_user_roles($params["main_role"], $user_id, TRUE);
					}
					
					$this->delete_data($this->user_roles, array('user_id' => $user_id, 'main_role_flag' => 0));
					
					if( ISSET( $params['role'] ) AND !EMPTY( $params['role'] ) )
					{
						$this->_insert_user_roles($params["role"], $user_id);
					}

					if(!EMPTY($user_id) && ISSET($params["org"]) && !EMPTY($params["org"]))
					{
						$this->delete_data(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, array('user_id' => $user_id, 'main_org_flag' => 1));
						$this->_insert_user_organizations(array($params["org"]), $user_id, TRUE);
					}

					$this->delete_data(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, array('user_id' => $user_id, 'main_org_flag' => 0));

					if(!EMPTY($user_id) AND 
						ISSET($params['other_orgs']) AND !EMPTY($params["other_orgs"])
						AND ISSET($params['other_orgs'][0]) AND !EMPTY($params["other_orgs"][0])
					)
					{
						$this->_insert_user_organizations($params["other_orgs"], $user_id);
					}
				}
			}
			else
			{
				if($params["contact_type"] == 0)
				{
					if(!EMPTY($user_id) && !EMPTY($params["org"]))
					{
						$this->delete_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, array('user_id' => $user_id, 'main_org_flag' => 1));
						$this->_insert_user_organizations_temp(array($params["org"]), $user_id, TRUE);
					}

					$this->delete_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, array('user_id' => $user_id, 'main_org_flag' => 0));

					if(!EMPTY($user_id) AND 
						ISSET($params['other_orgs']) AND !EMPTY($params["other_orgs"])
						AND ISSET($params['other_orgs'][0]) AND !EMPTY($params["other_orgs"][0])
					)
					{
						$this->_insert_user_organizations_temp($params["other_orgs"], $user_id);
					}
				}
			}
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}
	
	public function update_user_password($params)
	{
		try
		{
			$val 		= array();
			$where 		= array();
			
			$user_id 			= filter_var($params["user_id"], FILTER_SANITIZE_NUMBER_INT);
			$clean_password 	= preg_replace('/\s+/', '', $params['password']);
			$salt 				= gen_salt(TRUE);
			
			$val["password"] 	= in_salt($clean_password, $salt, TRUE);
			$val["salt"] 		= $salt;
			
			$where["user_id"] 		= $user_id;
			
			$this->update_data($this->users, $val, $where);
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}

	public function _insert_user_organizations($orgs, $user_id, $main_org = FALSE, $signup = FALSE)
	{
		try
		{
			foreach ($orgs as $k_r => $role):
				$params					= array();
				$params["user_id"] 		= filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
				$params["org_code"] 	= filter_var($role, FILTER_SANITIZE_STRING);

				if( $main_org )
				{
					$params['main_org_flag'] 	= 1;
				}
				else
				{
					if( $signup )
					{
						if( $k_r == 0 )
						{
							$params['main_org_flag'] 	= 1;			
						}
					}
				}

				$this->insert_data(SYSAD_Model::CORE_TABLE_USER_ORGANIZATIONS, $params);
			endforeach;
				
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
	}

	public function _insert_user_organizations_temp($orgs, $user_id, $main_org = FALSE, $signup = FALSE)
	{
		try
		{
			foreach ($orgs as $k_r => $role):
				$params					= array();
				$params["user_id"] 		= filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
				$params["org_code"] 	= filter_var($role, FILTER_SANITIZE_STRING);

				if( $main_org )
				{
					$params['main_org_flag'] 	= 1;
				}
				else
				{
					if( $signup )
					{
						if( $k_r == 0 )
						{
							$params['main_org_flag'] 	= 1;			
						}
					}
				}

				$this->insert_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ORGANIZATIONS, $params);
			endforeach;
				
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
	}
		
	public function _insert_user_roles($roles, $user_id, $main_role = FALSE, $signup = FALSE)
	{
		try
		{
			foreach ($roles as $k_r => $role):
				$params					= array();
				$params["user_id"] 		= filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
				$params["role_code"] 	= filter_var($role, FILTER_SANITIZE_STRING);

				if( $main_role )
				{
					$params['main_role_flag'] 	= 1;
				}
				else
				{
					if( $signup )
					{
						if( $k_r == 0 )
						{
							$params['main_role_flag'] 	= 1;			
						}
					}
				}

				$this->insert_data($this->user_roles, $params);
			endforeach;
				
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		
	}

	public function _insert_user_role_temp($roles, $user_id, $main_role = FALSE, $signup = FALSE)
	{
		try
		{
			foreach ($roles as $k_r => $role):
				$params					= array();
				$params["user_id"] 		= filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
				$params["role_code"] 	= filter_var($role, FILTER_SANITIZE_STRING);

				if( $main_role )
				{
					$params['main_role_flag'] 	= 1;
				}
				else
				{
					if( $signup )
					{
						if( $k_r == 0 )
						{
							$params['main_role_flag'] 	= 1;			
						}
					}
				}

				$this->insert_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES, $params);
			endforeach;
				
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		
	}

	public function delete_helper($table, array $where)
	{
		try
		{
			$this->delete_data( $table, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
	}
	
	public function update_status($params, $account_creator = NULL)
	{
		try
		{
			$val 				= array();
			$where 				= array();
			
			$sp_status 			= get_sys_param_code(SYS_PARAM_STATUS, $params['status_id']);
			
			if(!EMPTY($params["password"]))
			{
				$params['set_own_password_flag']	= ENUM_YES;

				$clean_password 	= preg_replace('/\s+/', '', $params['password']);
				$salt 				= gen_salt(TRUE);
				$val["password"] 	= in_salt($clean_password, $salt, TRUE);
				$val["salt"] 		= $salt;
			}

			/*if( ISSET($params['set_own_password_flag']) AND !EMPTY($params['set_own_password_flag']) )
			{
				$val['set_own_password_flag'] = ENUM_YES;
			}*/
			
			if(ISSET($params["username"]))
				$val["username"] 	= array(filter_var(preg_replace('/\s+/', '', $params['username'], FILTER_SANITIZE_STRING)), 'ENCRYPT');

			
			$val["status"] 			= $sp_status["sys_param_code"];
			$val["reason"] 			= ISSET($params['reason']) ? $params['reason'] : NULL;
			$val["modified_by"]		= $this->session->user_id;
			$val["initial_flag"] 	= INITIAL_NO;
			$val["created_date"]	= date('Y-m-d', strtotime('NOW'));

			if( $sp_status["sys_param_code"] == STATUS_ACTIVE )
			{
				$val['attempts']	= 0;
				$val['soft_attempts'] = 0;
			}

			if( $params['status_id'] == ACTIVE )
			{
				$val['blocked_by']		= NULL;
				$val['blocked_date'] 	= NULL;
				$val['reason'] 			= NULL;	
				$val['inactivated_by']		= NULL;
				$val['inactivated_date'] 	= NULL;
			}
			
			$where['user_id'] 		= $params['user_id'];

			if( ISSET( $params['reject_reason'] ) )
			{
				$val['reason']		= $params['reject_reason'];
			}

			$table_us = $this->users;

			if( !EMPTY( $account_creator ) 
				AND ( $account_creator == VISITOR_NOT_APPROVAL OR $account_creator == VISITOR )
			)
			{
				$val["modified_by"]		= NULL;
				$table_us = $this->temp_users;
			}
			
			$this->update_data($table_us, $val, $where);

			if( EMPTY( $account_creator ) OR 
				( !EMPTY( $account_creator ) 
					AND ( $account_creator != VISITOR_NOT_APPROVAL AND $account_creator != VISITOR )
				) 

			)
			{
				if(ISSET($params["main_role"]) && !EMPTY($params["main_role"]))
				{
					$this->delete_data($this->user_roles, array('user_id' => $params['user_id'], 'main_role_flag' => 1));
					$this->_insert_user_roles($params["main_role"], $params['user_id'], TRUE);
				}
				
				if(ISSET($params["role"]) && !EMPTY($params["role"]))
				{
					if( !ISSET( $params['main_role'] ) OR EMPTY( $params['main_role'] ) )
					{
						$this->delete_data($this->user_roles, array('user_id' => $params['user_id'], 'main_role_flag' => 0));
					}

					$this->_insert_user_roles($params["role"], $params['user_id']);
				}
			}
			else
			{
				if(ISSET($params["main_role"]) && !EMPTY($params["main_role"]))
				{
					$this->delete_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES, array('user_id' => $params['user_id'], 'main_role_flag' => 1));
					$this->_insert_user_role_temp($params["main_role"], $params['user_id'], TRUE);
				}
				
				if(ISSET($params["role"]) && !EMPTY($params["role"]))
				{
					if( !ISSET( $params['main_role'] ) OR EMPTY( $params['main_role'] ) )
					{
						$this->delete_data(SYSAD_Model::CORE_TABLE_TEMP_USER_ROLES, array('user_id' => $params['user_id'], 'main_role_flag' => 0));
					}

					$this->_insert_user_role_temp($params["role"], $params['user_id']);
				}	
			}
		}
		catch (PDOException $e)
		{
			throw $e;
		}
	}

	public function deactivate_user($user_id, $status)
	{
		try
		{
			$inactive	= $status;

			$count 		= count($user_id);

			$in_val 	= '';

			for($i = 0; $i < $count; $i++)
			{
				$in_val .= '?,';
			}

			$in_val 	= rtrim($in_val, ',');

			$val 		= array(
				$inactive,
				date('Y-m-d H:i:s'),
				'Expired Account'
			);

			$val 		= array_merge($val, $user_id);

			$query = <<<EOS
				UPDATE
					$this->users
				SET
					status = ?,
					inactivated_date = ?,
					reason 	= ?
				WHERE user_id IN ($in_val)
EOS;
			$this->query($query, $val );
				
		}
		catch(PDOException $var)
		{
			throw $var;
		}
	}

	public function get_user_for_deactivate_last_logged_in($interval, $status)
	{
		$result 	= array();

		try
		{

			$val 	= array();
			$val[]	= $status;


			$query = <<<EOS
				SELECT
					a.*,GROUP_CONCAT(DISTINCT b.role_code) as role_codes
				FROM
					$this->users a
				JOIN $this->user_roles b 
					ON a.user_id = b.user_id
				WHERE 
					a.last_logged_in_date <= (NOW() - INTERVAL $interval DAY)
				AND
					a.status = ?
				GROUP BY a.user_id
EOS;
	
			$result 	= $this->query( $query, $val );
		}
		catch(PDOException $var)
		{
			throw $var;
		}

		return $result;
	}
		
	public function check_email_exist($email, $id = 0)
	{
		$stmt 		= array();

		try
		{
			$disapproved 	= get_sys_param_code(SYS_PARAM_STATUS, DISAPPROVED);
			$deleted 		= get_sys_param_code(SYS_PARAM_STATUS, DELETED);
			
			$where 			= "";
			$val 			= array();
			
			$val[] 			= $email;
			$val[] 			= $disapproved["sys_param_code"];
			$val[] 			= $deleted["sys_param_code"];
			
			
			$email_str		= aes_crypt('email', FALSE, FALSE);
			
			if($id)
			{
				$where.=" AND user_id = ? 
				  OR (SELECT IF(
					EXISTS(SELECT ".$email_str." FROM $this->users
						WHERE ".$email_str." = ? AND status NOT IN(?,?)), 0, 1))";
				$val[] = $id;
				$val[] = $email;
				$val[] = $disapproved["sys_param_code"];
				$val[] = $deleted["sys_param_code"];
			}
			
			$query = <<<EOS
				SELECT IF(
				EXISTS(
				  SELECT $email_str FROM $this->users
				  WHERE $email_str = ? AND status NOT IN(?,?) 
				  $where
				), 1, 0) email_exist
EOS;
			$stmt = $this->query($query, $val, TRUE, FALSE);
			
		}
		catch (PDOException $e)
		{
			throw $e;
		}

		return $stmt;
	}
	
	public function validate_current_password($id)
	{
		$result 	= array();

		try
		{
			$fields = array("password");
			$where 	= array("user_id" => $id);
			
			$result = $this->select_data($fields, $this->users, FALSE, $where);
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	public function get_user_account_history($user_id , $limit = 0)
	{
		$stmt 		= array();

		try
		{
			$query 	= "";
			$val 	= array(
				$user_id,
				$user_id
			);
			
			$limit_by 	= ($limit === 0) ? "" : " LIMIT " .$limit;
			
			$query 		= <<<EOS
				SELECT * FROM $this->users WHERE user_id = ?
				UNION
				SELECT * FROM $this->user_history WHERE user_id = ? ORDER BY modified_date DESC
				$limit_by
EOS;
			$stmt = $this->query($query, $val);
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $stmt;
	}
	
	public function check_password_history($user_id, $password)
	{
		try
		{
			$x 				= get_setting(PASSWORD_CONSTRAINTS, PASS_CONS_HISTORY);
			$user_history 	= $this->get_user_account_history($user_id, $x);
			
			foreach ($user_history as $user_info)
			{
				// ENCRYPT THE PASSWORD
				$hashed_password = base64_url_decode($user_info["password"]);
				
				if($password == $hashed_password)
				{
					throw new Exception('Password must not match any of the user\'s previous ' .$x . ' passwords.');
				}
			}
			
		} 
		catch(PDOException $e)
		{
			throw $e;
		}
	}
	
	public function get_user_salt()
	{
		$result 	= array();

		try
		{
			$fields = array("salt");
			$where 	= array("user_id" => $this->session->user_id);
	
			$result	= $this->select_data($fields, $this->users, FALSE, $where);
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $result;
	}
	
	public function update_password_status($duration)
	{
		try
		{
			if(intval($duration) <= 0) return TRUE;
			
			$expired 	= get_sys_param_code(SYS_PARAM_STATUS, EXPIRED);
			$active 	= get_sys_param_code(SYS_PARAM_STATUS, ACTIVE);
			
			$query 		= <<<EOS
				UPDATE $this->users SET status = ? 
				WHERE status = ? AND (DATE_ADD(DATE(modified_date), INTERVAL ? DAY) > DATE(NOW()));
EOS;
			$this->query($query, array($expired["sys_param_code"], $active["sys_param_code"], $duration), FALSE);
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
	}
	
	public function get_accounts_to_remind($duration)
	{
		$stmt 		= array();

		try
		{
			if(intval($duration) <= 0) return TRUE;
			
			$active = get_sys_param_code(SYS_PARAM_STATUS, ACTIVE);
			
			$val 	= array(
				$active["sys_param_code"], 
				$duration
			);

			$aes_mname 	= 'CAST( '.aes_crypt('a.mname', FALSE, FALSE).' AS char(200) ) ';
			$aes_lname 	= 'CAST( '.aes_crypt('a.lname', FALSE, FALSE).' AS char(200) )';
			$aes_fname 	= 'CAST(' .aes_crypt('a.fname', FALSE, FALSE). ' AS char(200) )';
			$aes_email 	= 'CAST(' .aes_crypt('a.email', FALSE, FALSE). ' AS char(200) )';
			$aes_username = 'CAST(' .aes_crypt('a.username', FALSE, FALSE). ' AS char(200) )';
			
			$query = <<<EOS
				SELECT a.user_id, $aes_username as username, DATEDIFF(DATE(NOW()),DATE(modified_date)) as remaining, CONCAT($aes_fname, ' ',substr($aes_mname, 1,1) , '. ', $aes_lname) as full_name, gender, $aes_email as email,
					GROUP_CONCAT(DISTINCT b.role_code) as role_codes
				FROM $this->users a 
				JOIN $this->user_roles b 
				 ON a.user_id = b.user_id
				WHERE status = ? 
				AND (DATEDIFF(DATE(NOW()),DATE(modified_date)) <= ?)
				GROUP BY a.user_id
EOS;

// 
			$stmt = $this->query($query, $val);
			
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $stmt;
	}
	
	/*public function get_settings_arr($setting_type)
	{
		try
		{
			$this->load->model('settings_model');
			$result = $this->settings_model->get_settings_value($setting_type);
			$return = array();
			
			foreach ($result as $row)
				$return[$row['setting_name']] = $row['setting_value'];
			
			return $return;
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{			
			throw $e;
		}
	}*/
	
	public function get_email_params()
	{
		$return 	= array();

		try
		{
			
			$query 	= <<<EOS
				SELECT * FROM $this->sys_param WHERE sys_param_type = ?
EOS;
			$result = $this->query($query, array(SYS_PARAM_SMTP));
			
			foreach ($result as $row)
				$return[$row['sys_param_name']] = $row['sys_param_value'];
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $return;
	}

	public function update_email_pw_flag($user_id)
	{
		try
		{
			$email_yes	= 1;

			$count 		= count($user_id);

			$in_val 	= '';

			for($i = 0; $i < $count; $i++)
			{
				$in_val .= '?,';
			}

			$in_val = rtrim($in_val, ',');


			$query 	= <<<EOS
				UPDATE
					$this->users
				SET
					pw_email_flag = $email_yes
				WHERE user_id IN ($in_val)
EOS;
			$this->query( $query, $user_id, FALSE );
		}
		catch(PDOException $var)
		{
			throw $var;
		}
	}

	public function update_last_logged_in($username)
	{
		try
		{
			$query = <<<EOS
				UPDATE
					users
				SET
					last_logged_in_date = NOW()
				WHERE username = ?
EOS;
	
			$this->query( $query, array($username), FALSE );
		}
		catch(PDOException $var)
		{
			throw $var;
		}
	}

	public function get_expired_dpa_user()
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
				SELECT 	a.*
				FROM 	%s a 
				WHERE 	NOW() >= expire_dpa_date
					AND built_in_flag = 0
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_USERS
			);

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;

	}

	public function get_settings_arr($setting_type)
	{
		$result 	= array();

		try
		{
			$this->load->model('settings_model');

			$result = $this->settings_model->get_settings_value($setting_type);
			$return = array();
			
			foreach ($result as $row)
				$return[$row['setting_name']] = $row['setting_value'];
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		
		return $return;
	}

/*	public function get_email_params()
	{
		try
		{
			$return = array();
			
			$query = <<<EOS
				SELECT * FROM $this->sys_param WHERE sys_param_type = ?
EOS;
			$result = $this->query($query, array(SYS_PARAM_SMTP));
			
			foreach ($result as $row)
				$return[$row['sys_param_name']] = $row['sys_param_value'];
				
			return $return;
		} 
		catch (PDOException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{			
			throw $e;
		}
	}*/

	public function get_admin_email()
	{
		$result 	= array();

		try
		{
			$active = 'STATUS_ACTIVE';

			$sql 	= <<<EOS
				SELECT
					email
				FROM
					$this->users
				WHERE
					status = ?
EOS;

			$val 	= array( $active );

			$result = $this->query( $sql, $val);
			
		}
		catch (PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_user_status_count()
	{
		$stmt 	= array();	

		try
		{
			$join = $where = "";
			$val = array();
			$val[] = STATUS_ACTIVE;
			$val[] = STATUS_PENDING;
			$val[] = STATUS_APPROVED;
			$val[] = STATUS_DISAPPROVED;
			$val[] = STATUS_BLOCKED;
			$val[] = STATUS_INACTIVE;
			$val[] = STATUS_DELETED;
			$val[] = STATUS_DPA_PENDING;
			$val[] = STATUS_INCOMPLETE;
			$val[] = ANONYMOUS_ID;
			
			$query = <<<EOS
				SELECT 
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) active_count, 
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) pending_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) approved_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) disapproved_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) blocked_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) inactive_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) deleted_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) dpa_pending_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) incomplete_count
				FROM $this->users A
				WHERE A.user_id != ?
EOS;
			$stmt = $this->query($query, $val, TRUE, FALSE);
			
		}
		catch(PDOException $e)
		{
			$this->rlog_error($e);
			
			throw $e;
		}


		return $stmt;
	}

	public function get_user_status_count_temp()
	{
		$stmt 	= array();	

		try
		{
			$join = $where = "";
			$val = array();
			$val[] = STATUS_ACTIVE;
			$val[] = STATUS_PENDING;
			$val[] = STATUS_APPROVED;
			$val[] = STATUS_DISAPPROVED;
			$val[] = STATUS_BLOCKED;
			$val[] = STATUS_INACTIVE;
			$val[] = STATUS_DELETED;
			$val[] = STATUS_DPA_PENDING;
			$val[] = STATUS_INCOMPLETE;
			$val[] = ANONYMOUS_ID;
			
			$query = <<<EOS
				SELECT 
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) active_count, 
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) pending_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) approved_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) disapproved_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) blocked_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) inactive_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) deleted_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) dpa_pending_count,
				IFNULL( SUM(CASE WHEN A.status = ? THEN 1 ELSE 0 END), 0 ) incomplete_count
				FROM $this->temp_users A
				WHERE A.user_id != ?
EOS;
			$stmt = $this->query($query, $val, TRUE, FALSE);
			
		}
		catch(PDOException $e)
		{
			$this->rlog_error($e);
			
			throw $e;
		}


		return $stmt;
	}

	public function get_active_users($user_id = NULL)
	{
		$result 						= array();

		try
		{
			$where 						= array();
			
			
			$username 	= aes_crypt('username', FALSE);
			$fname		= aes_crypt('fname', FALSE, FALSE);
			$lname		= aes_crypt('lname', FALSE, FALSE);
			
			$fields 					= array("user_id, ".$username." , CONCAT(".$fname.", ' ', ".$lname.") name, photo");
			$where["logged_in_flag"] 	= LOGGED_IN_FLAG_YES;

			if( !EMPTY( $user_id ) )
			{
				$where['user_id']["!="]	= $user_id;
			}

			$result 					= $this->select_data($fields, $this->users, TRUE, $where);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_all_users_active( $user_id = NULL )
	{
		$result 		= array();
		$val 			= array();

		$add_where 		= "";
		$extra_val 		= array();

		try
		{
			if( !EMPTY( $user_id ) )
			{
				if( is_array( $user_id ) )
				{
					$count 				= count( $user_id );
					$placeholder 		= str_repeat('?,', $count);
					$placeholder 		= rtrim($placeholder, ',');

					$add_where .= " AND a.user_id NOT IN ( $placeholder ) ";
					$extra_val	= array_merge( $extra_val, $user_id );
				}
				else
				{
					$add_where 	.= " AND a.user_id != ? ";
					$extra_val[] = $user_id;
				}
			}

			$aes_mname 	= 'CAST( '.aes_crypt('a.mname', FALSE, FALSE).' AS char(200) ) ';
			$aes_lname 	= 'CAST( '.aes_crypt('a.lname', FALSE, FALSE).' AS char(200) )';
			$aes_fname 	= 'CAST(' .aes_crypt('a.fname', FALSE, FALSE). ' AS char(200) )';

			$query 		= "
				SELECT 	a.user_id, 
						IF( IFNULL(".$aes_mname.", '') != '',  
							CONCAT( ".$aes_fname.",' ',".$aes_mname.",' ',".$aes_lname." ),
							CONCAT( ".$aes_fname.",' ',".$aes_lname." )
						) as fullname
				FROM 	%s a
				WHERE 	a.status IN (?)
				AND 	a.user_id != 0
				$add_where
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_USERS );

			$val[] 		= STATUS_ACTIVE;

			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val );
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_authentication_sections_factors($section)
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
				SELECT 	b.*, a.authentication_factor_section
				FROM 	%s a 
				JOIN 	%s b 
				 	ON 	a.authentication_factor_id = b.authentication_factor_id
				WHERE 	a.authentication_factor_section = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTOR_SECTIONS,
				SYSAD_Model::CORE_TABLE_AUTHENTICATION_FACTORS
			);

			$val[] 		= $section;

			$val 		= array_merge( $val, $extra_val );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_security_questions_temp($user_id = NULL)
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
				SELECT 	a.*, b.answer
				FROM 	%s a 
				LEFT 	JOIN %s b 
					ON 	a.security_question_id = b.security_question_id
					AND b.user_id = ?
				WHERE 	1 = 1
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_SECURITY_QUESTIONS,
				SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS
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

	public function get_security_questions_with_answer($user_id )
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
				SELECT 	a.answer, b.*
				FROM 	%s a 
				LEFT 	JOIN %s b 
					ON 	a.security_question_id = b.security_question_id
				WHERE 	1 = 1
					AND a.user_id = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_USER_SECURITY_ANSWERS,
				SYSAD_Model::CORE_TABLE_SECURITY_QUESTIONS
				
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

	public function get_security_answer_temp($user_id )
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
				SELECT 	a.*
				FROM 	%s a
				WHERE 	1 = 1
					AND a.user_id = ?
					$add_where
";
			$query 		= sprintf( $query,
				SYSAD_Model::CORE_TABLE_TEMP_USER_SECURITY_ANSWERS
				
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

	public function get_temp_users_for_deactivation()
	{
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$table 		= SYSAD_Model::CORE_TABLE_USERS;

			$query 		= "
				SELECT  a.user_id
				FROM 	%s a
				WHERE 	a.status = ?
					AND a.temporary_account_flag = ?
				    AND a.temporary_account_expiration_date <= NOW()
";
			$query 		= sprintf( $query,
				$table
			);

			$val[] 		= STATUS_ACTIVE;
			$val[] 		= ENUM_YES;

			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val, TRUE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function update_temp_users_for_deactivation()
	{
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$table 		= SYSAD_Model::CORE_TABLE_USERS;

			$query 		= "
				UPDATE 	%s a
				SET 	a.status = ?,
						a.modified_date = ?,
						a.inactivated_date = ?,
						a.reason = ?
				WHERE 	a.status = ?
					AND a.temporary_account_flag = ?
				    AND a.temporary_account_expiration_date <= NOW()
";
			$query 		= sprintf( $query,
				$table
			);

			$val[] 		= STATUS_INACTIVE;
			$val[] 		= date('Y-m-d H:i:s');
			$val[] 		= date('Y-m-d H:i:s');
			$val[] 		= 'Expired Temporary Account';
			$val[] 		= STATUS_ACTIVE;
			$val[] 		= ENUM_YES;

			$val 		= array_merge( $val, $extra_val );
			
			$result 	= $this->query( $query, $val, FALSE );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function mobile_no_check( $mobile_no, $user_id = NULL, $temp = FALSE )
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();
	
		try
		{
			$table 	= SYSAD_Model::CORE_TABLE_USERS;

			if( $temp )
			{
				$table = SYSAD_Model::CORE_TABLE_TEMP_USERS;
			}

			if( !EMPTY( $user_id ) )
			{
				$add_where .= " AND user_id != ? ";
				$extra_val[] = $user_id;
			}

			$query 		= "
				SELECT  COUNT(user_id) as check_mobile_no
				FROM 	%s
				WHERE 	IF( LENGTH(CAST(AES_DECRYPT(mobile_no, UNHEX(SHA2('".SECURITY_PASSPHRASE."', 512))) as char(100))) = 10, 
						CONCAT('0',CAST(AES_DECRYPT(mobile_no, UNHEX(SHA2('".SECURITY_PASSPHRASE."', 512))) as char(100)) ),
			            CAST(AES_DECRYPT(mobile_no, UNHEX(SHA2('".SECURITY_PASSPHRASE."', 512))) as char(100))
			        ) = ?
			       $add_where
";
			$val[] 	= $mobile_no;

			$val 	= array_merge($val, $extra_val);

			$query 	= sprintf( $query, $table );

			$result = $this->query( $query, $val, TRUE, FALSE );
		}	
		catch( PDOException $e )	
		{
			throw $e;
		}

		return $result;
	}

	public function check_username( $username, $user_id = NULL )
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$aes_username = '' .aes_crypt('a.username', FALSE, FALSE). '';

			if( !EMPTY( $user_id ) )
			{
				$add_where .= " AND a.user_id != ? ";
				$extra_val[] = $user_id;
			}

			$query 		= "
				SELECT 	a.*, MAX(a.user_id), a.user_id + 1 as inc_user_id
				FROM 	%s a 
				WHERE 	$aes_username = ?
					$add_where
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_USERS );

			$val[] 	= $username;
			$val 	= array_merge($val, $extra_val);
			
			$result 	= $this->query( $query, $val, TRUE, FALSE );
		}
		catch (PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_roles_import( array $where = array() )
	{
		$result 	= array();

		try
		{
			$fields 	= array("role_code", "role_name", "CONCAT(role_code,'".TEMPLATE_DR_MARK."',role_name) as ".TEMPLATE_DR_NAME."");
			$order_by 	= array("role_name" => "ASC");
			
			$result 	= $this->select_data($fields, parent::CORE_TABLE_ROLES, TRUE, $where, $order_by);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_orgs_import( array $where = array() )
	{
		$result 	= array();

		try
		{
			$fields 	= array("org_code", "name", "CONCAT(org_code,'".TEMPLATE_DR_MARK."',name) as ".TEMPLATE_DR_NAME."");
			$order_by 	= array("name" => "ASC");

			$def_where 	= array(
				'status' => ENUM_YES
			);

			$def_where 	= array_merge($def_where, $where);
			
			$result 	= $this->select_data($fields, parent::CORE_TABLE_ORGANIZATIONS, TRUE, $def_where, $order_by);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}

	public function get_orgs_parent_import( array $where = array() )
	{
		$result 	= array();

		try
		{
			$fields 	= array("org_code as org_parent", "name", "CONCAT(org_code,'".TEMPLATE_DR_MARK."',name) as ".TEMPLATE_DR_NAME."");
			$order_by 	= array("name" => "ASC");

			$def_where 	= array(
				'status' => ENUM_YES
			);

			$def_where 	= array_merge($def_where, $where);
			
			$result 	= $this->select_data($fields, parent::CORE_TABLE_ORGANIZATIONS, TRUE, $def_where, $order_by);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}
}
