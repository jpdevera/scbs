 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This class is use to check if the module's system code is on or off
 * Can be placed in index,form,modal,save of the module's controller function
 * 
 * @author asiagate
 */
class Check_system 
{
	protected $CI; //  Codeigniter instane
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model(CORE_SYSTEMS.'/Systems_application_model', 'sys_app_mod');
	}


	/**
	 * Use This helper function to check if the module's system code is on or off
	 * 
	 * 
	 * @param  $module_code -- required. Module Code of the screen
	 * @throws PDOException
	 * @throws Exception
	 * @return boolean
	 */
	public function check_system($module_code)
	{
		$check 				= FALSE;

		try
		{
			$check_system 	= $this->CI->sys_app_mod->check_system( $module_code );
			
			if( !EMPTY( $check_system ) )
			{
				if( !EMPTY( $check_system['check_system'] ) )
				{
					$check 	= TRUE;
				}
			}
			else
			{
				$check 		= FALSE;
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $check;
	}

	/**
	 * Use This helper function to check if the module's directory system code is on or off
	 * 
	 * 
	 * @param  $directory -- required. system Directory of current module
	 * @throws PDOException
	 * @throws Exception
	 * @return boolean
	 */
	public function check_system_directory($directory)
	{
		$check 				= FALSE;

		try
		{
			$check_system 	= $this->CI->sys_app_mod->check_system_redirection( $module_code );
			
			if( !EMPTY( $check_system ) )
			{
				if( !EMPTY( $check_system['check_system_redirection'] ) )
				{
					$check 	= TRUE;
				}
			}
			else
			{
				$check 		= FALSE;
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $check;
	}

}