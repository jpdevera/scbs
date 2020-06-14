<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SYSAD_Controller extends Base_Controller
{
	protected static $system = SYSTEM_CORE;

	public function get_pass_error_msg()
	{
		try
		{

			$result = get_settings(PASSWORD_CONSTRAINTS);

			$pass_constraints = array();
			$err 			  = array();
			
			foreach ($result as $row)
				$pass_constraints[$row['setting_name']] = $row['setting_value'];
				$constraint_msg_uppercase	 = ""; $constraint_msg_digit = ""; $constraint_msg_repeat = "";

				$constraint_letter_msg = "";
				
				$character_text = ($pass_constraints[PASS_CONS_LENGTH] > 1) ? " characters " : " character";

				$letter_text 	= ($pass_constraints['constraint_letter'] > 1) ? " letters " : " letter";
				$spec_text 	= ($pass_constraints['constraint_special_character'] > 1) ? " special characters " : " special character";

				if(intval($pass_constraints[PASS_CONS_LENGTH]) !== 0)
				{
					$err[] = 'Password field must be composed of at least ' . $pass_constraints[PASS_CONS_LENGTH] . ' character(s). ';
				}

				if(!EMPTY($pass_constraints['constraint_letter']) || ($pass_constraints['constraint_letter'] != 0))
				{
					$err[] 		= 'Containing at least ' . $pass_constraints['constraint_letter'] .' '.$letter_text.'.';
				}


				if(!EMPTY($pass_constraints['constraint_special_character']) || ($pass_constraints['constraint_special_character'] != 0))
				{
					$err[] 		= 'Containing at least ' . $pass_constraints['constraint_special_character'] .' '.$spec_text.'.';
				}

				if(!EMPTY($pass_constraints[PASS_CONS_DIGIT]) || ($pass_constraints[PASS_CONS_DIGIT] != 0))
				{
					$digit_text = ($pass_constraints[PASS_CONS_DIGIT] > 1) ? " digits " : " digit ";
					$constraint_msg_digit = $pass_constraints[PASS_CONS_DIGIT] . $digit_text;

					$err[] 		= 'Containing at least ' . $pass_constraints[PASS_CONS_DIGIT] . ' digit(s).';
				}
				
				if(!EMPTY($pass_constraints[PASS_CONS_UPPERCASE]) || ($pass_constraints[PASS_CONS_UPPERCASE] != 0))
				{
					$concat = (!EMPTY($pass_constraints[PASS_CONS_DIGIT]) || ($pass_constraints[PASS_CONS_DIGIT] != 0)) ? " and " : "";
					$letter_text = ($pass_constraints[PASS_CONS_UPPERCASE] > 1) ? " letters " : " letter ";
					$constraint_msg_uppercase = $concat . $pass_constraints[PASS_CONS_UPPERCASE] . " uppercase " . $letter_text;

					$err[] 		= 'Containing at least ' . $pass_constraints[PASS_CONS_UPPERCASE] . ' uppercase letter(s).';
				}

				if(!EMPTY($pass_constraints['constraint_lowercase']) || ($pass_constraints['constraint_lowercase'] != 0))
				{
					$concat = (!EMPTY($pass_constraints[PASS_CONS_DIGIT]) || ($pass_constraints[PASS_CONS_DIGIT] != 0)) ? " and " : "";
					$letter_text = ($pass_constraints['constraint_lowercase'] > 1) ? " letters " : " letter ";
					$constraint_msg_uppercase = $concat . $pass_constraints['constraint_lowercase'] . " lowercase " . $letter_text;

					$err[] 		= 'Containing at least ' . $pass_constraints['constraint_lowercase'] . ' lowercase letter(s).';
				}

				if(!EMPTY($pass_constraints[PASS_CONS_REPEATING]) || ($pass_constraints[PASS_CONS_REPEATING] != 0))
				{
					$concat = (!EMPTY($pass_constraints[PASS_CONS_DIGIT]) || ($pass_constraints[PASS_CONS_DIGIT] != 0)) ? " and " : "";
					$constraint_msg_repeat 	= $concat . "Repeating characters is not allowed.";

					$err[] 		= "Repeating characters is not allowed.";
				}

				if( !EMPTY( $pass_constraints[PASS_CONS_DIFF_USER] ) )
				{
					$err[] 		= "Password must be different with username.";
				}

				$str_msg 	= 'Password length must be equal or more than ' . $pass_constraints[PASS_CONS_LENGTH] . $character_text . ' with at least ' . $constraint_msg_digit . $constraint_msg_uppercase . $constraint_msg_repeat;

			if( !EMPTY( $err ) )
			{
				return implode('<br /> - ',$err);
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

	public function validate_password($password, $username = NULL)
	{
		try
		{
			$msg = $this->get_pass_error_msg();
				
			$constraints = get_settings(PASSWORD_CONSTRAINTS);
			$pass_const = array();
				
			foreach ($constraints as $row)
				$pass_const[$row['setting_name']] = $row['setting_value'];
				
			$pass_length = $pass_const[PASS_CONS_LENGTH];
			$upper_length = $pass_const[PASS_CONS_UPPERCASE];
			$lower_length = $pass_const['constraint_lowercase'];
			$digit_length = $pass_const[PASS_CONS_DIGIT];
			$letter_length = $pass_const['constraint_letter'];
			$spec_length  = $pass_const['constraint_special_character'];

			$repeating 	= $pass_const[PASS_CONS_REPEATING];
			$pass_same 	= $pass_const[PASS_CONS_DIFF_USER];

			$check_repeating = FALSE;
			$check_pass_same = FALSE;
				
			$pass_count = strlen($password);
			$upper_count = strlen(preg_replace('/[^A-Z]+/', "", $password));
			$lower_count = strlen(preg_replace('/[^a-z]+/', "", $password));
			$digit_count = strlen(preg_replace('/[^0-9]+/', "", $password));
			$pass_rep 	= preg_match_all('/(.)\1+/', $password, $matches);
			$letter_count = strlen(preg_replace('/[^A-Za-z]+/', "", $password));
			$spec_count  = strlen(preg_replace('/[!@#$%^&*+]+/', '', $password));
			$check_rep 	= FALSE;


			if( !EMPTY( $matches ) AND ISSET( $matches[0] ) )
			{
				foreach( $matches[0] as $m )
				{
					if( strlen($m) >= 3 )
					{
						$check_rep = TRUE;
					}
				}
			}
						
			$check_repeating = ( !EMPTY( $repeating ) ) ? $check_rep : FALSE;

			if( !EMPTY( $pass_same ) AND !EMPTY( $username ) )
			{
				$check_pass_same 	= strtolower( $password ) == strtolower( $username );
			}
			
			if
			(
					$pass_count < intval($pass_length)
					||
					($lower_count < intval($lower_length) || intval($lower_length) <= 0 && $lower_length != '')
					||
					($upper_count < intval($upper_length) || intval($upper_length) <= 0 && $upper_length != '')
					||
					($letter_count < intval($letter_length) || intval($letter_length) <= 0 && $letter_length != '')
					||
					($spec_count < intval($spec_length) || intval($spec_length) <= 0 && $spec_length != '')
					||
					($digit_count < intval($digit_length) || intval($digit_length) <= 0 && $digit_length != '')
					||
					$check_repeating
					||
					$check_pass_same
			)
			{
				return $msg;
			}
			return TRUE;
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

	public function get_username_error_msg()
	{
		try
		{
			$result = get_settings(USERNAME_CONSTRAINTS);
			
			$user_constraints = array();
			$err 			  = array();

			foreach ($result as $row)
			{
				$user_constraints[$row['setting_name']] = $row['setting_value'];
			}

			$constraint_msg_uppercase	 = ""; $constraint_msg_digit = ""; $constraint_msg_repeat = "";

			if(!EMPTY($user_constraints[USERNAME_MIN_LENGTH]))
			{
				$err[] = 'Username field must be composed of at least ' . $user_constraints[USERNAME_MIN_LENGTH] . ' character(s). ';
			}

			if(!EMPTY($user_constraints[USERNAME_MAX_LENGTH]))
			{
				$err[] = 'Username field must have a maximum of ' . $user_constraints[USERNAME_MAX_LENGTH] . ' character(s). ';
			}

			if(!EMPTY($user_constraints['constraint_username_letter']) || ($user_constraints['constraint_username_letter'] != 0))
			{
				$err[] 		= 'Containing at least ' . $user_constraints['constraint_username_letter'] . ' letter(s).';
			}

			if(!EMPTY($user_constraints[USERNAME_DIGIT]) || ($user_constraints[USERNAME_DIGIT] != 0))
			{
				$err[] 		= 'Containing at least ' . $user_constraints[USERNAME_DIGIT] . ' digit(s).';
			}

			if( !EMPTY( $err ) )
			{
				return implode('<br /> - ',$err);
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

	public function validate_username($username)
	{
		try
		{
			$msg 			= $this->get_username_error_msg();

			$constraints 	= get_settings(USERNAME_CONSTRAINTS);
			$user_const 	= array();

			$apply_user_cons = get_setting( USERNAME, 'apply_username_constraints' );

			$username_count 	= strlen($username);
			$digit_count 		= strlen(preg_replace('/[^0-9]+/', "", $username));
			$letter_count 		= strlen(preg_replace('/[^A-Za-z]+/', "", $username));

			if( !EMPTY( $apply_user_cons ) )
			{

				foreach ($constraints as $row)
				{
					$user_const[$row['setting_name']] = $row['setting_value'];
				}

				$user_max_length = $user_const[USERNAME_MAX_LENGTH];
				$user_min_length = $user_const[USERNAME_MIN_LENGTH];
				$user_dig_length = $user_const[USERNAME_DIGIT];
				$user_lett_length = $user_const['constraint_username_letter'];

				$check_min_length = FALSE;
				$check_max_length = FALSE;
				$check_dig_count  = FALSE;
				$check_lett_count = FALSE;

				if( !EMPTY( $user_min_length ) )
				{
					$check_min_length = $username_count < intval($user_min_length);
				}

				if( !EMPTY( $user_max_length ) )
				{
					$check_max_length = $username_count > intval( $user_max_length );
				}

				if( !EMPTY( $user_dig_length ) )
				{
					$check_dig_count  = $digit_count < intval($user_dig_length) || intval($user_dig_length) <= 0;
				}

				if( !EMPTY( $user_lett_length ) )
				{
					$check_lett_count  = $letter_count < intval($user_lett_length) || intval($user_lett_length) <= 0;
				}

				if
				(
					$check_min_length || $check_max_length || $check_dig_count || $check_lett_count
				)
				{
					return $msg;
				}

			}

			return TRUE;
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
}