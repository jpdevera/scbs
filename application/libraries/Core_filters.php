<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use AJD_validation\Contracts\Base_extension;

class Core_filters extends Base_extension
{
	protected $CI;
	protected $filter_values 	= array();

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function set_filter( array $values )
	{
		$this->filter_values 	= $values;

		return $this;
	}

	public function filter_type( $type, $key, $decode = FALSE, $options = array() )
	{
		$filter_type 			= array(
			'string' 			=> array(
				FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES
			),
			'number' 			=> array( FILTER_SANITIZE_NUMBER_INT ),
			'float'				=> array( 
				FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION
			),
			'email' 			=> array( FILTER_SANITIZE_EMAIL ),
			'url' 				=> array( FILTER_SANITIZE_URL ),
			'date' 				=> true
		);

		$not_filter_var 		= array( 'date' );

		if( ISSET( $this->filter_values[ $key ] ) )
		{
			if( !is_array( $this->filter_values[ $key ] ) )  
			{
				$check_what 		= ( EMPTY( $this->filter_values[ $key ] ) AND $decode ) ? FALSE : TRUE;

				$value 				= ( $decode ) ? base64_url_decode( $this->filter_values[ $key ] ) : $this->filter_values[ $key ];
			}
			else
			{
				$check_what 	= FALSE;

				$value 			= $this->filter_values[ $key ];
			}

			$arguments 			= array( $value, FILTER_SANITIZE_STRING );

			if( ISSET( $filter_type[ $type ] ) )
			{
				if( in_array( strtolower( $type ), $not_filter_var ) )
				{
					switch( strtolower( $type ) )
					{
						case 'date':

							$format 		= ISSET( $options['date_format'] ) ? $options['date_format'] : 'Y-m-d';

							if( is_array( $this->filter_values[ $key ] ) )
							{
								foreach( $this->filter_values[ $key ] as $cnt => $a )
								{
									$this->filter_values[ $key ][ $cnt ] = date_format( date_create( $a ), $format );
								}
							}
							else
							{
								$this->filter_values[ $key ] = date_format( date_create( $value ), $format );
							}

							

						break;
					}
				}
				else 
				{

					if( is_array( $value ) )
					{
						foreach( $value as $cnt => $a )
						{
							$check_what_multi 	= ( EMPTY( $a ) AND $decode ) ? FALSE : TRUE;

							if( ISSET( $options['explode'] ) AND !EMPTY( $options['explode'] )  )
							{
								$a_arr 			= explode('|', $a);
								$a 				= ( $decode ) ? base64_url_decode( $a_arr[0] ) : $a_arr[0];
							}
							else
							{
								$a 				= ( $decode ) ? base64_url_decode( $a ) : $a;
							}

							$arguments 		= $filter_type[ $type ];

							array_unshift( $arguments, $a );
						
							$this->filter_values[ $key ][ $cnt ] = call_user_func_array( 'filter_var', $arguments );

							if( EMPTY( $a ) AND $decode AND $check_what_multi )
							{
								$this->filter_values[ $key ][ $cnt ] = "error";
							}
						}
						
					}
					else
					{
						if( ISSET( $options['explode'] ) AND !EMPTY( $options['explode'] )  )
						{
							$val_arr 		= explode('|', $value);
							$a 				= ( $decode ) ? base64_url_decode( $val_arr[0] ) : $val_arr[0];
						}

						$arguments 		= $filter_type[ $type ];

						array_unshift( $arguments, $value );

						$this->filter_values[ $key ] = call_user_func_array( 'filter_var', $arguments );
						
						if( EMPTY( $value ) AND $decode AND $check_what )
						{
							$this->filter_values[ $key ] = "error";
						}
					}

				}

			}

		}

		return $this;
	}

	public function filter_string( $key, $decode = FALSE, $options = array() )
	{
		$this->filter_type( 'string', $key, $decode, $options );

		return $this;
		
	}

	public function filter_number( $key, $decode = FALSE, $options = array() )
	{
		$this->filter_type( 'number', $key, $decode, $options );

		return $this;
		
	}

	public function filter_float( $key, $decode = FALSE, $options = array() )
	{
		$this->filter_type( 'float', $key, $decode, $options );

		return $this;
		
	}

	public function filter_email( $key, $decode = FALSE, $options = array() )
	{
		$this->filter_type( 'email', $key, $decode, $options );

		return $this;
		
	}

	public function filter_url( $key, $decode = FALSE, $options = array() )
	{
		$this->filter_type( 'url', $key, $decode, $options );

		return $this;
		
	}

	public function filter_date( $key, $decode = FALSE, $options = array() )
	{
		$this->filter_type( 'date', $key, $decode, $options );

		return $this;
	}

	public function filter()
	{
		return $this->filter_values;
	}

	public function getName()
	{
		return 'Core_filters';
	}

	public function getFilters()
	{
		return array(
			'core_string_filter',
			'core_number_filter',
			'core_float_filter',
			'core_email_filter',
			'core_url_filter',
			'core_date_filter',
			'core_decode_filter',
			'core_encode_filter'
		);
	}

	public function core_decode_filter( $field, $value, $satisfier )
	{
		$value 	= base64_url_decode( $value );

		return $value;
	}

	public function core_encode_filter( $field, $value, $satisfier )
	{
		$value 	= base64_url_encode( $value );

		return $value;
	}

	public function core_string_filter( $field, $value, $satisfier )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );

		return $value;
	}
	
	public function core_number_filter( $field, $value, $satisfier )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_NUMBER_INT );

		return $value;
	}

	public function core_float_filter( $field, $value, $satisfier )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

		return $value;
	}

	public function core_email_filter( $field, $value, $satisfier )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_EMAIL );

		return $value;
	}

	public function core_url_filter( $field, $value, $satisfier )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_URL );

		return $value;
	}

	public function core_date_filter( $field, $value, $satisfier )
	{
		$format 		= ISSET( $satisfier['date_format'] ) ? $satisfier['date_format'] : 'Y-m-d';

		$value 			= date_format( date_create( $value ), $format );

		return $value;
	}
}