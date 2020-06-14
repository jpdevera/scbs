<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Site_settings_model extends SYSAD_Model {
	
	private $site_settings;
	
	public function __construct()
	{	
		parent::__construct();
		
		$this->site_settings = parent::CORE_TABLE_SITE_SETTINGS;
	}

	public function get_roles()
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
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_ROLES );

			$result 	= $this->query( $query, $val, TRUE );
		}
		catch (PDOException $e)
		{
			throw $e;
		}

		return $result;
	}
                
	public function get_site_settings($setting_location = NULL, $setting_type = NULL, $setting_name = NULL)
	{
		try
		{	
			$where = array();
			
			$fields = array("*");
			$multiple = TRUE;
			
			if(!IS_NULL($setting_location))
				$where['setting_location'] = $setting_location;
			
			if(!IS_NULL($setting_type))
				$where['setting_type'] = $setting_type;
			
			if(!IS_NULL($setting_name)){
				$where['setting_name'] = $setting_name;
				$multiple = FALSE;
			}
			
			return $this->select_data($fields, $this->site_settings, $multiple, $where);
			
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
	
	public function update_settings($setting_type, $params, $field)
	{
		try
		{
			$val = array();
			$where = array();
			
			switch($field){
				case 'system_logo':
				  $value = (@getimagesize(base_url(). PATH_SETTINGS_UPLOADS . $params[$field])) ? $params[$field] : "";
				break;
				
				case 'password_expiry':
				  $value = ISSET($params['password_expiry']) ? $params[$field] : 0;
				break;

				case 'encryption':
				  $value = ISSET($params['encryption']) ? 1 : 0;
				break;

				case 'enable_image_compression':
				  $value = ISSET($params['enable_image_compression']) ? 1 : 0;
				break;

				case 'enable_ip_blacklist':
				  $value = ISSET($params['enable_ip_blacklist']) ? 1 : 0;
				break;

				case 'change_password_initial_login':
					$value = ISSET($params['change_password_initial_login']) ? 1 : 0;
				break;

				case 'constraint_repeating_characters':
					$value = ISSET($params['constraint_repeating_characters']) ? 1 : 0;
				break;

				case 'log_in_deactivation':
					$value = ISSET($params['log_in_deactivation']) ? 1 : 0;
				break;

				case 'log_in_deactivation_duration':
					$value = EMPTY($params['log_in_deactivation_duration']) ? 0 : $params['log_in_deactivation_duration'];
				break;

				case 'auto_log_inactivity':
					$value = ISSET($params['auto_log_inactivity']) ? 1 : 0;
				break;

				case 'auto_log_inactivity_duration':
					$value = EMPTY($params['auto_log_inactivity_duration']) ? 0 : $params['auto_log_inactivity_duration'];
				break;

				case 'apply_username_constraints':
					$value = ISSET($params['apply_username_constraints']) ? 1 : 0;
				break;

				case 'constraint_pass_diff_username':
					$value = ISSET($params['constraint_pass_diff_username']) ? 1 : 0;
				break;

				case USERNAME_MIN_LENGTH :
					$value = EMPTY($params[USERNAME_MIN_LENGTH]) ? 0 : $params[USERNAME_MIN_LENGTH];
				break;

				case USERNAME_MAX_LENGTH :
					$value = EMPTY($params[USERNAME_MAX_LENGTH]) ? 0 : $params[USERNAME_MAX_LENGTH];
				break;

				case USERNAME_DIGIT :
					$value = EMPTY($params[USERNAME_DIGIT]) ? 0 : $params[USERNAME_DIGIT];
				break;

				case 'username_case_sensitivity' :
					$value = ISSET($params['username_case_sensitivity']) ? 1 : 0;
				break;

				case 'maintenance_mode' :
					$value = ISSET($params['maintenance_mode']) ? 1 : 0;
				break;
				
				case 'password_duration':
				case 'password_reminder':
				  $value = ISSET($params['password_expiry']) ? $params[$field] : "";
				break;

				case 'single_session':
				  $value = ISSET($params['single_session']) ? 1 : 0;
				break;

				case 'sess_expiration_warning' :
					$value = ISSET($params['sess_expiration_warning']) ? 1 : 0;
				break;
				
				case 'password_creator':
					$value = ISSET($params['password_creator']) ? $params[$field] : 0;
					break;
				case 'stages_flag' :
					$value = ISSET($params['stages_flag']) ? 1 : 0;
				break;
				case 'process_flag' :
					$value = 1;
				break;
				case 'steps_flag' :
					$value = 1;
				break;
				case 'prerequisites_flag' :
					$value = 1;
				break;

				case 'stages' :
					$value = ( ISSET($params['stages'] ) AND !EMPTY( $params['stages'] ) ) ? $params[$field] : NULL;
				break;
				case 'process' :
					$value = ( ISSET($params['process']) AND !EMPTY( $params['process'] ) ) ? $params[$field] : NULL;
				break;
				case 'steps' :
					$value = ( ISSET($params['steps'] ) AND !EMPTY($params['steps']) ) ? $params[$field] : NULL;
				break;
				case 'prerequisites' :
					$value = ( ISSET($params['prerequisites']) AND !EMPTY($params['steps']) ) ? $params[$field] : NULL;
				break;

				case 'stages_description' :
					$value = ( ISSET($params['stages_description'] ) AND !EMPTY($params['stages_description']) ) ? $params[$field] : NULL;
				break;
				case 'process_description' :
					$value = ( ISSET($params['process_description']) AND !EMPTY($params['process_description'] ) ) ? $params[$field] : NULL;
				break;
				case 'steps_description' :
					$value = ( ISSET($params['steps_description']) AND !EMPTY($params['steps_description'] ) ) ? $params[$field] : NULL;
				break;
				case 'prerequisites_description' :
					$value = ( ISSET($params['prerequisites_description']) AND !EMPTY($params['prerequisites_description'] ) ) ? $params[$field] : NULL;
				break;

				case 'show_title_on_login' :
					$value = ISSET($params['show_title_on_login']) ? 1 : 0;
				break;
				case 'show_tagline_on_login' :
					$value = ISSET($params['show_tagline_on_login']) ? 1 : 0;
				break;
				case 'change_upload_path' :
					$value = ISSET($params['change_upload_path']) ? 1 : 0;
				break;

				case 'new_upload_path' :
					$value = ( ISSET($params['new_upload_path']) AND !EMPTY($params['new_upload_path']) ) ? $params[$field] : NULL;
				break;

				case 'has_agreement_text':
					$value = ISSET($params['has_agreement_text']) ? $params['has_agreement_text'] : NULL;

					if( !ISSET( $params['dpa_enable'] ) )
					{
						$value = '';
					}
				break;

				case 'file_upload_type':
					$value = ISSET($params['file_upload_type']) ? $params['file_upload_type'] : '';

					if( !ISSET( $params['change_upload_path'] ) )
					{
						$value = '';
					}
				break;

				case 'dpa_email_enable' : 
					$value = ISSET($params['dpa_email_enable']) ? 1 : 0;
				break;

				case 'email_domain':

					$value 	= EMPTY($params['email_domain']) ? '' : $params['email_domain'];

					if( !ISSET( $params['dpa_email_enable'] ) )
					{	
						$value = '';
					}

				break;

				case 'dpa_enable' : 
					$value = ISSET($params['dpa_enable']) ? 1 : 0;
				break;

				case 'agremment_text':
					// $post 	= $this->input->post();

					$value 	= EMPTY($params['agremment_text']) ? '' : implode(',', $params['agremment_text']);

					if( !ISSET( $params['has_agreement_text'] ) )
					{
						$value = '';
					}

				break;

				case 'role_override':
					// $post 	= $this->input->post();
					$value 	= ( EMPTY($params['role_override']) OR EMPTY( $params['role_override'][0] ) ) ? '' : implode(',', $params['role_override']);

				break;

				case 'ip_blacklist':
					// $post 	= $this->input->post();

					$value 	= EMPTY($params['ip_blacklist']) ? '' : $params['ip_blacklist'];

					$with_ip_blacklist 	= get_sys_param_val('IP_ADDRESS', 'IP_BLACKLIST');
					$ch_with_ip_blacklist 	= ( !EMPTY( $with_ip_blacklist ) AND !EMPTY( $with_ip_blacklist['sys_param_value'] ) ) ? TRUE : FALSE;

					if( !ISSET( $params['enable_ip_blacklist'] ) )
					{
						$value = '';
					}

					if( EMPTY( $ch_with_ip_blacklist ) )
					{
						$value 	= '';
					}

				break;

				case 'login_api':
					// $post 	= $this->input->post();

					$value 	= EMPTY($params['login_api']) ? '' : implode(',', $params['login_api']);

					$login_sys_param 	= get_sys_param_val('LOGIN', 'LOGIN_WITH');

					$ch_login_sys_param 	= ( !EMPTY( $login_sys_param ) AND !EMPTY( $login_sys_param['sys_param_value'] ) ) ? TRUE : FALSE;

					if( EMPTY( $ch_login_sys_param ) )
					{
						$value 	= '';
					}

					/*if( !ISSET( $params['has_agreement_text'] ) )
					{
						$value = '';
					}*/

				break;

				case 'term_conditions' :
					$value = ISSET($params['term_conditions']) ? 1 : 0;
				break;

				case 'term_condition_value':
					// $post 	= $this->input->post();

					$value 	= EMPTY($params['term_condition_value']) ? '' : implode(',', $params['term_condition_value']);

					if( !ISSET( $params['term_conditions'] ) )
					{
						$value = '';
					}

				break;

				case 'not_req_sec_question' :
					$value = ISSET($params['not_req_sec_question']) ? 1 : 0;
				break;

				case 'self_user_logout' :
					$value = ISSET($params['self_user_logout']) ? 1 : 0;

					if( !ISSET( $params['single_session'] ) )
					{
						$value = 0;
					}
				break;

				case 'notification_cron' :
					$value = ISSET($params['notification_cron']) ? 1 : 0;
				break;

				case 'device_location_auth' :
					$value = ISSET($params['device_location_auth']) ? 1 : 0;
				break;

				/*case 'agreement_uploads':

					$value 	= EMPTY($params['agreement_uploads']) ? '' : $params['agreement_uploads'];

					if( !ISSET( $params['has_agreement_text'] ) )
					{	
						$value = '';
					}

				break;*/

				/*case 'enable_multi_auth_factor' : 
					$value = ISSET($params['enable_multi_auth_factor']) ? 1 : 0;
				break;

				case 'authentication_factor':
				
					$value 	= EMPTY($params['authentication_factor']) ? '' : implode( ',', $params['authentication_factor'] );

					if( !ISSET( $params['authentication_factor'] ) )
					{	
						$value = '';
					}

				break;

				case 'auth_code_decay':
				
					$value 	= EMPTY($params['auth_code_decay']) ? 1 : $params['auth_code_decay'];

					if( !ISSET( $params['authentication_factor'] ) )
					{	
						$value = '';
					}

				break;*/

				case 'auth_login_factor':
				
					$value 	= EMPTY($params['auth_login_factor']) ? '' : implode( ',', $params['auth_login_factor'] );

				break;

				case 'auth_login_code_decay':
				
					$value 	= EMPTY($params['auth_login_code_decay']) ? 1 : $params['auth_login_code_decay'];

					if( !ISSET( $params['auth_login_factor'] ) )
					{	
						$value = 0;
					}

				break;

				case 'auth_password_factor':
				
					$value 	= EMPTY($params['auth_password_factor']) ? '' : implode( ',', $params['auth_password_factor'] );

				break;

				case 'auth_password_code_decay':
				
					$value 	= EMPTY($params['auth_password_code_decay']) ? 1 : $params['auth_password_code_decay'];

					/*if( !ISSET( $params['auth_password_factor'] ) )
					{	
						$value = 0;
					}*/

				break;

				case 'auth_account_factor':

					if( ISSET($params['account_creator']) AND 
						( $params['account_creator'] == VISITOR OR $params['account_creator'] == VISITOR_NOT_APPROVAL )

					)
					{
						$value 	= EMPTY($params['auth_account_factor']) ? '' : implode( ',', $params['auth_account_factor'] );
					}
					else
					{
						$value 	= '';
					}

				break;

				case 'auth_account_code_decay':


					if( ISSET($params['account_creator']) AND 
						( $params['account_creator'] == VISITOR OR $params['account_creator'] == VISITOR_NOT_APPROVAL )

					)
					{
				
						$value 	= EMPTY($params['auth_account_code_decay']) ? 1 : $params['auth_account_code_decay'];

						if( !ISSET( $params['auth_account_factor'] ) )
						{	
							$value = 0;
						}
					}
					else
					{
						$value 	= 0;
					}
				break;

				case 'sms_api' :

					$value 	= EMPTY($params['sms_api']) ? '' : implode( ',', $params['sms_api'] );

				break;
				
				default:
					if( ISSET( $params[$field] ) )
					{
				  		$value = $params[$field];				
				  	}
				  	else
				  	{
				  		$value = '';
				  	}
			}

			$val['setting_value'] = filter_var($value, FILTER_SANITIZE_STRING);
			$where['setting_name'] = $field;
			$where['setting_type'] = $setting_type;
			
			$this->update_data($this->site_settings, $val, $where);
			
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

	public function get_statements($statement_module_type_id)
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
				WHERE 	a.statement_module_type_id = ?
";
			$query 		= sprintf( $query, SYSAD_Model::CORE_TABLE_STATEMENTS );

			$val[] 		= $statement_module_type_id;

			$result 	= $this->query($query, $val, TRUE);

		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;	
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

	public function get_all_varbinary_column(array $add_schemas = array())
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$path 		= APPPATH.'config'.DS.'database.php';
			$path 		= str_replace(array('/', '\\'), array(DS, DS), $path);

			require $path;

			$schem 	= array_keys($db);

			$schemas 		= array_merge($schem, $add_schemas);

			if( !EMPTY( $schemas ) )
			{
				$count			= count( $schemas );
				// starts
				$placeholder 	= str_repeat( '?,', $count );
				$placeholder 	= rtrim( $placeholder, ',' );
				
				// ends

				$add_where          .= " AND TABLE_SCHEMA IN ( $placeholder ) ";
				$extra_val 			= array_merge($extra_val, $schemas);
			}

			$query 		= "
				SELECT
					TABLE_SCHEMA,
				    TABLE_NAME,
				    COLUMN_NAME
				FROM
				    INFORMATION_SCHEMA.COLUMNS
				WHERE
				    DATA_TYPE = 'varbinary' 
				    $add_where
";
			$val 		= array_merge($val, $extra_val);


			$result 	= $this->query($query, $val, TRUE);

			
			$result 	= $this->process_columns_table($result);
			

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

	public function process_columns_table(array $result)
	{
		$res_arr 			= array();
		try
		{
			if( !EMPTY( $result ) )
			{
				foreach( $result as $res )
				{
					$res_arr[$res['TABLE_SCHEMA'].'.'.$res['TABLE_NAME']][]	= $res['COLUMN_NAME'];
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

		return $res_arr;
	}

	public function update_encrypt_decrypt(array $details)
	{
		$add_where 		= "";
		$extra_val 		= array();

		$val 			= array();
		$result 		= array();

		$sub_query 		= "";
		$sub_val 		= array();

		try
		{
			$query 		= '';
			if( !EMPTY( $details ) )
			{
				$dpa_encryption 				= get_setting(DPA_SETTING, 'encryption');

				foreach( $details as $table => $columns )
				{
					$fields 	= '';

					foreach( $columns as $column )
					{
						if( !EMPTY( $dpa_encryption ) )
						{

							$fields .= "
								".$column." = IF( 
									AES_DECRYPT(".$column.", UNHEX(SHA2('".SECURITY_PASSPHRASE."', 512))) IS NULL, 
									AES_ENCRYPT(".$column.", UNHEX(SHA2('".SECURITY_PASSPHRASE."', 512))),
									".$column."
								), ";
						}
						else
						{
							$fields .= "


								".$column." = IF( 
									AES_DECRYPT(".$column.", UNHEX(SHA2('".SECURITY_PASSPHRASE."', 512))) IS NULL, 
									".$column.",
									AES_DECRYPT(".$column.", UNHEX(SHA2('".SECURITY_PASSPHRASE."', 512)))
								), ";
						}
					}

					$fields = rtrim( $fields, ', ' );

					$query 	= " 
						UPDATE 	$table
						SET 	$fields;
					";

					$val 		= array_merge($val, $extra_val);

					$result 	= $this->query($query, $val, FALSE);
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

		return $result;
	}
}