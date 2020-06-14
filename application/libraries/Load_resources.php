<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Load_resources 
{
	protected $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	
	/**
	 * $resources - js and css files that will be needed by a particular page.
	 */
	
	public function get_resource($resources, $print = FALSE)
	{	
		try 
		{

			foreach( $resources as $name => $value )
			{
				$real_v 	= array();
			
				if( is_array( $value ) )
				{
					foreach( $value as $key => $val )
					{
						if( is_array( $val ) )
						{
							foreach( $val as $k => $v )
							{
								$check_serial 		= $this->CI->template->is_serialized($v);
								
								if( $check_serial )
								{
									$uns_v 			= unserialize($v);
									$real_v[$key] 	= array_merge( $real_v[$key], $uns_v );
								}
								else
								{
									if( is_numeric( $k ) )
									{
										$real_v[$key][]		= $v;
									}
									else
									{
										$real_v[$key][$k]	= $v;
									}
								}
							}
						}
						else
						{
							$check_serial_s 	= $this->CI->template->is_serialized($val);
						
							if( !$check_serial_s )
							{
								$real_v[$key]	= $val;
							}
							else
							{
								$uns_v_s 		= unserialize($val);
								
								if( is_array( $uns_v_s ) )
								{
									foreach( $uns_v_s as $v_s )
									{
										$real_v[] = $v_s;
									}
								}
								else
								{
									$real_v[] 	= $uns_v_s;
								}
								
							}
						}
						
					}
					
					$resources[$name] = $real_v;
				}
				else
				{
					$resources[$name] = $value;
				}

			}
			
			if($print){
				return $this->CI->load->view('footer', $resources, $print);
			} else {
				$this->CI->load->view('footer', $resources);
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}							
	}	
	
}