<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| List of GENERIC FUNCTION FOR AUTH FACTORS
|--------------------------------------------------------------------------
*/

/**
 * Use This helper function to check if key 'enable_multi_auth_factor' is enable or not empty.
 * As of now general authentication factor is hidden in account settings this is just a ready made 
 * function.
 * 
 * 
 * @return boolean
 */
function check_multi_auth_enable()
{
	$multi_auth_enable 	= 0;

	try
	{
		$multi_auth_enable = (int)	get_setting( AUTH_FACTOR, 'enable_multi_auth_factor' );
	}
	catch( PDOException $e )
	{
		throw $e;
	}

	return $multi_auth_enable;
}

/**
 * Use This helper function to get the authentication factor by general or by section
 * 
 * @param  $auth_section -- NULL. default general auth factor setting. (AUTH_SECTION_ACCOUNT, *AUTH_SECTION_LOGIN,AUTH_SECTION_PASSWORD)
 * @return array
 */
function get_setting_authentication_factor($auth_section = NULL)
{
	$authentication_factor = array();

	try
	{
		

		if( !EMPTY( $auth_section ) )
		{
			$get_auth_section_map 	= get_auth_section_map($auth_section);
			$authentication_factor_str = get_setting( AUTH_FACTOR, $get_auth_section_map['factors'] );
		}
		else
		{
			$authentication_factor_str = get_setting( AUTH_FACTOR, 'authentication_factor' );
		}

		$authentication_factor_str 		= trim($authentication_factor_str);

		if(  !EMPTY( $authentication_factor_str ) )
		{
			$authentication_factor = explode(',', $authentication_factor_str);
		}

		if( $auth_section == AUTH_SECTION_PASSWORD )
		{
			$authentication_factor[] = AUTHENTICATION_FACTOR_EMAIL;
		}
	}	
	catch( PDOException $e )
	{
		throw $e;
	}

	return $authentication_factor;
}

/**
 * Use This helper function to check if authentication factor is selected in general authentication factor setting
 * 
 * 
 * @param  $authentication_factor -- required. ( AUTHENTICATION_FACTOR_EMAIL, AUTHENTICATION_FACTOR_SMS )
 * @return boolean
 */
function check_multi_auth_factor($authentication_factor)
{
	$check = FALSE;

	try
	{
		$check_multi_auth_enable = check_multi_auth_enable();

		if( !EMPTY( $check_multi_auth_enable ) )
		{
			$get_setting_authentication_factor = get_setting_authentication_factor();

			if( !EMPTY( $get_setting_authentication_factor ) ) 
			{
				$check = in_array( $authentication_factor, $get_setting_authentication_factor );
			}
		}
		else
		{
			$check = TRUE;
		}
	}	
	catch( PDOException $e )
	{
		throw $e;
	}

	return $check;	
}

/**
 * Use This helper function to check if authenticated user has already verified the authentication factor set in general authentication factor.
 * 
 * 
 * @param  $user_id -- required. ( id of the user )
 * @param  $table -- required. ( either SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
 * @return array
 */
function check_user_multi_auth($user_id, $table)
{
	$check 	= array();

	try
	{
		$CI =& get_instance();
		$CI->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'uam');

		$users_auth = $CI->uam->get_user_multi_auth($user_id, NULL, $table, TRUE);

		if( !EMPTY( $users_auth ) )
		{
			foreach( $users_auth as $uath )
			{
				$check_multi_auth_factor = check_multi_auth_factor($uath['authentication_factor_id']);

				if( !EMPTY( $check_multi_auth_factor ) )
				{
					$check 	= array(
						'authentication_factor_id'	=> base64_url_encode($uath['authentication_factor_id']),
						'user_id'					=> base64_url_encode($user_id)
					);

					return $check;
				}
			}
		}
	}
	catch( PDOException $e )
	{
		throw $e;
	}

	return $check;	
}

/**
 * Use This helper function to check if authenticated user has already verified the authentication factor set based in authentication factor section either in (AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD).
 * 
 * 
 * @param  $user_id -- required. ( id of the user )
 * @param  $table -- required. ( either SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
 * @param  $auth_section -- required. ( either AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
 * @return array
 */
function check_user_multi_auth_section($user_id, $table, $auth_section)
{
	$check 	= array();

	try
	{
		$CI =& get_instance();
		$CI->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'uam');

		$users_auth = $CI->uam->get_user_multi_auth($user_id, NULL, $table, TRUE);

		if( !EMPTY( $users_auth ) )
		{
			foreach( $users_auth as $uath )
			{
				$check_multi_auth_factor = check_multi_auth_factor_section($uath['authentication_factor_id'], $auth_section);
				
				if( !EMPTY( $check_multi_auth_factor ) )
				{
					$check 	= array(
						'authentication_factor_id'	=> base64_url_encode($uath['authentication_factor_id']),
						'user_id'					=> base64_url_encode($user_id)
					);

					return $check;
				}
			}
		}
	}
	catch( PDOException $e )
	{
		throw $e;
	}

	return $check;	
}

/**
 * Use This helper function to check if authenticated user has already verified the authentication factor set based in authentication section either in (AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD).
 * 
 * 
 * @param  $table -- required. ( either SYSAD_Model::CORE_TABLE_USER_MULTI_AUTH, SYSAD_Model::CORE_TABLE_TEMP_USER_MULTI_AUTH )
 * @param  $auth_section -- required. ( either AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
 * @return boolean
 */
function check_multi_auth_factor_section($authentication_factor, $auth_section)
{
	$check = FALSE;

	try
	{
		$get_setting_authentication_factor = get_setting_authentication_factor($auth_section);

		if( !EMPTY( $get_setting_authentication_factor ) ) 
		{
			$check = in_array( $authentication_factor, $get_setting_authentication_factor );
		}
		else
		{
			$check = TRUE;
		}
	}	
	catch( PDOException $e )
	{
		throw $e;
	}

	return $check;	
}

/**
 * Use This helper function to map the auth_factor in site settings based by auth section ( AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
 * 
 * 
 * @param  $auth_section -- required. ( AUTH_SECTION_ACCOUNT, AUTH_SECTION_LOGIN, AUTH_SECTION_PASSWORD )
 * @return array
 */
function get_auth_section_map($auth_section)
{
	$auth_sec_map 		= array(
		AUTH_SECTION_ACCOUNT	=> array(
			'factors'	=> 'auth_account_factor',
			'code' 		=> 'auth_account_code_decay'
		),
		AUTH_SECTION_LOGIN 		=> array(
			'factors'	=> 'auth_login_factor',
			'code' 		=> 'auth_login_code_decay'
		),
		AUTH_SECTION_PASSWORD 	=> array(
			'factors'	=> 'auth_password_factor',
			'code' 		=> 'auth_password_code_decay'
		)
	);

	return $auth_sec_map[$auth_section];
}

/**
 * Use This helper function to map the some common configs like the header txt in a label e.g. "Verify {$authentication_factor} or what sending method will be used based by (AUTHENTICATION_FACTOR_EMAIL, AUTHENTICATION_FACTOR_SMS)"
 * 
 * 
 * @param  $authentication_factor -- required. ( AUTHENTICATION_FACTOR_EMAIL, AUTHENTICATION_FACTOR_SMS )
 * @return array
 */
function auth_factor_config($authentication_factor)
{
	$auth_factor_arr = array(
		AUTHENTICATION_FACTOR_EMAIL => array(
			'header_txt'			=> 'Email',
			'send_function_method'		=> 'send_auth_email'
		),
		AUTHENTICATION_FACTOR_SMS => array(
			'header_txt' 			=> 'Mobile No.',
			'send_function_method'		=> 'send_auth_sms'
		)
	);

	if( !ISSET( $auth_factor_arr[$authentication_factor] ) )
	{
		return array(
			'header_txt'				=> '',
			'send_function_method'		=> ''
		);
	}

	return $auth_factor_arr[$authentication_factor];
}

/**
 * Use This helper function to generate the verification code change the length as needed.
 * 
 * 
 * @param  $length -- required. DEAFULT 5
 * @return array
 */
function generate_verify_code($length = 5)
{
	return generate_password($length);
}