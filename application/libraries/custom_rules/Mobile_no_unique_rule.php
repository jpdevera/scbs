<?php 

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\AJD_validation as v;

class Mobile_no_unique_rule extends Abstract_rule
{
	protected $CI;

	// public $blacklistedEmail;

	public function __construct() 
	{
		$this->CI 		=& get_instance();

		$this->CI->load->model(CORE_USER_MANAGEMENT.'/Users_model', 'umm');
	}

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL, $origValues = NULL )
	{
		$check 	= FALSE;

		try
		{
			$user_id = NULL;
			$temp 	= FALSE;

			if( ISSET( $satisfier[0] ) )
			{
				$user_id = $satisfier[0];
			}

			if( ISSET( $satisfier[1] ) )
			{
				$temp = $satisfier[1];
			}

			$mobile_no 	= $this->CI->umm->mobile_no_check($value, $user_id, $temp);

			if( !EMPTY( $mobile_no ) AND !EMPTY( $mobile_no['check_mobile_no'] ) )
			{
				$check = FALSE;
			}
			else
			{
				$check 	= TRUE;
			}

		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $check;

	}

	public function validate( $value )
	{
		$check 	= FALSE;

		try
		{
			$check 			= $this->run( $value );

			if( is_array( $check ) )
			{
				return $check['check'];
			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $check;
	}

}