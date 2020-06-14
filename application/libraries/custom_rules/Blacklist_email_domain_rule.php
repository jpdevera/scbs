<?php 

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\AJD_validation as v;

class Blacklist_email_domain_rule extends Abstract_rule
{
	protected $CI;

	public $blacklistedEmail;

	public function __construct() 
	{
		$this->CI 		=& get_instance();
	}

	protected function getEmailSiteSettings()
	{
		$configs  	= array();

		try
		{
			$dpa_email_enable 				= get_setting( DPA_SETTING, 'dpa_email_enable' );
			$check_dpa_email_enable 		= ( !EMPTY( $dpa_email_enable ) ) ? TRUE : FALSE;
			$email_domain 					= get_setting( DPA_SETTING, 'email_domain' );

			$configs 	= array(
				'check_dpa_email_enable'	=> $check_dpa_email_enable,
				'dpa_email_enable'			=> $dpa_email_enable,
				'email_domain'				=> $email_domain
			);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $configs;
	}

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL, $origValues = NULL )
	{
		$check 	= FALSE;

		try
		{
			$configs 	= $this->getEmailSiteSettings();

			if( $configs['check_dpa_email_enable'] )
			{
				if( !EMPTY( $configs['email_domain'] ) )
				{
					$email_domain_add_at_arr = explode(',', $configs['email_domain']);

					$email_domain_add_at_arr = array_map(function($value)
					{
						return '@'.$value;
					}, $email_domain_add_at_arr);

					$email_domain_add_at_imp = implode('|', $email_domain_add_at_arr);
					$email_domain_add_at_imp_str = implode(',', $email_domain_add_at_arr);

					$validatorRegex 		= $this->getValidator()
												->regex($email_domain_add_at_imp)
												->validate($value);

					$this->blacklistedEmail = $email_domain_add_at_imp_str;

					$check = !$validatorRegex;

				}
				else
				{
					$check 	= TRUE;		
				}
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