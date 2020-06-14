<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Base_Controller extends MX_Controller
{
	const PDF_OUTPUT_INLINE		= 'I';
	const PDF_OUTPUT_DOWNLOAD 	= 'D';
	const PDF_OUTPUT_LOCAL_FILE	= 'F';
	const PDF_OUTPUT_STRING		= 'S';
	
	const MYSQL_ERR_PREFIX 		= 'mysql_err_';
	const MYSQL_ERR_DEFAULT 	= 'mysql_err_default';
	
	const HASH_CODE 			= '%$';
	
	// THIS WILL HOLD THE SYSTEM NAME OF THE EXTENDING CLASS
	protected static $system 	= SYSTEM_DEFAULT;
	public $core_v;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->_construct_rlog();
	}
	
	private function _construct_rlog()
	{
		$system 		= static::$system;
		
		// Getting values from the configuration
		$level 			= $this->config->item('rlog_level');
		$enable			= $this->config->item('rlog_enable');
		$error_handler 	= $this->config->item('rlog_error_handler');
		$location 		= realpath(APPPATH) . DS . 'logs' . DS . $system;
		
		// Setting up RLog
		RLog::location($location);
		RLog::level($level);
		RLog::enable($enable);
		RLog::setErrorHandler($error_handler);

		// Libraries
		$this->load->library('Core_validations');
		$this->load->library('Core_filters');
		$this->load->library('Core_reports');
		$this->load->library('Core_file_helper');
		
		$this->core_v 	= Core_validations::$validation_library;

		$this->core_v->setGlobalVar( $this );

		$this->core_v->registerExtension($this->core_validations);
		$this->core_v->registerExtension($this->core_filters);
		
		$this->core_v->addDbConnection(DB_CORE, $this->load->database(DB_CORE)->db);

		$path 		= APPPATH.'/libraries/custom_rules/';
		$path 		= str_replace(array('/', '\\'), array(DS, DS), $path);

		$this->core_v->addRuleDirectory($path)
			->addRuleNamespace('');
	}
	
	/**
	 * 
	 * @param unknown $pdo_ex - instance of the PDOException
	 * @param unknown $field_names - use to convert field name to a user-friendly message.
	 */
	protected function get_user_message($pdo_ex, $arr_field_names = array(), $custom_message_per_code = array()) 
	{
		try
		{
			$this->lang->load( 'mysql_err_lang', 'language/english' );

			$msg = $pdo_ex->getMessage();
			
			$this->rlog_error($pdo_ex);
			
			// reference --> http://php.net/manual/en/pdostatement.errorinfo.php
			$code = self::MYSQL_ERR_PREFIX . $pdo_ex->errorInfo[1];
			$method = $this->router->fetch_method();
			$check_meth = preg_match('/delete/', $method);

			if( !EMPTY( $check_meth ) )
			{
				$code .= '_'.ACTION_DELETE;
			}

			$title = "";
			$err_msg = $this->lang->line($code);
			
			foreach ($arr_field_names AS $field_name => $field_title)
			{
				// check if $msg contains $field_name
				if(strpos($msg, $field_name)) 
				{
					$title = $field_title;
					break;
				}
			}
			
			if(!ISSET($err_msg) OR EMPTY($err_msg))
				$err_msg = $this->lang->line( self::MYSQL_ERR_DEFAULT );

			if( !EMPTY( $custom_message_per_code ) )
			{
				if( ISSET( $custom_message_per_code[ $pdo_ex->errorInfo[1] ] ) )
				{
					$err_msg 	= $custom_message_per_code[ $pdo_ex->errorInfo[1] ];
				}
			}
			
			return $title . $err_msg;
		}	
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	protected function encrypt($value)
	{
		return base64_url_encode($value);
	}
	
	protected function decrypt($value)
	{
		return base64_url_decode($value);
	}
	
	protected function generate_report($view, $filename, $file_type, $report_name = NULL, $data = array(), $sheet_name = NULL, $colspan = 0, $portrait = TRUE, $report_type = REPORT_DEFAULT, $set_report_header = TRUE )
	{
		$this->core_reports->generate_report( $view, $filename, $file_type, $report_name, $data, $sheet_name, $colspan, $portrait, $report_type, $set_report_header );
	}
	
	protected function pdf($filename, $html, $portrait = TRUE, $output = SYSAD_Controller::PDF_OUTPUT_STRING, $header = NULL, $footer = NULL, $margin_left = 10, $margin_right = 10, $margin_top = 10, $margin_bottom = 10, $margin_header=10, $margin_footer=10)
	{
		try
		{
			return $this->core_reports->pdf($filename, $html, $portrait, $output, $header, $footer, $margin_left, $margin_right, $margin_top, $margin_bottom, $margin_header, $margin_footer );
		}
		catch(Exception $e)
		{
			throw $e;
		}
		
	}
	
	protected function set_report_header($colspan = 0, $project_name = PROJECT_NAME)
	{
		try
		{
			$header 	= $this->core_reports->set_report_header($colspan, $project_name);

			return $header;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	public function convert_excel_multi_sheets( array $buffers, $filename )
	{
		$this->core_reports->convert_excel_multi_sheets( $buffers, $filename );
	}

	public function style_php_excel( PHPExcel $php_excel, array $styles_callback = array(), $buffer = NULL )
	{
		$this->core_reports->style_php_excel( $php_excel, $styles_callback, $buffer );
	}
	
	public function convert_excel( $buffer, $file_name, $sheet_name )
	{
		$this->core_reports->convert_excel( $buffer, $file_name, $sheet_name );
	}

	public function convert_word( $buffer, $file_name )
	{
		$this->core_reports->convert_word( $buffer, $file_name );	
	}

	protected function set_report_footer($project_name = PROJECT_NAME)
	{
		try	
		{
			$footer 	= $this->core_reports->set_report_footer($project_name);

			return $footer;
		}
		catch(Exception $e)
		{
			throw $e;
		}	
	}
	
	protected function upload_attachment($config, $input_name)
	{
		try 
		{
				
			$upload_data = $this->core_file_helper->upload_attachment($config, $input_name);
	
			return $upload_data;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	
	}
	
	protected function check_required_fields($params, $fields)
	{
		try 
		{
			$this->core_validations->check_required_fields( $params, $fields );
		} 
		catch (Exception $e) 
		{
			throw $e;
		}
	}		
	
	public function validate_inputs($arr_inputs, $arr_validations)
	{
		try
		{
			$validate_inputs 	= $this->core_validations->validate_inputs($arr_inputs, $arr_validations);

			return $validate_inputs;
		}
		catch(Exception $e)
		{
			throw $e;
		}

	}
	
	protected function rlog_error($exception, $return_message = FALSE)
	{
		IF($return_message)
			$message = $exception->getMessage();
		ELSE
			$message = $exception->getLine() . ': ' . $exception->getMessage(). ': '. $exception->getTraceAsString();;
	
		RLog::error($message);
	
		IF($return_message)
			return $message;
			
	}
	
	protected function rlog_info($msg)
	{
		RLog::info($msg);
	}
		
	protected function rlog_debug($msg)
	{
		RLog::debug($msg);
	}

	protected function redirect_module_permission( $module_id, $action = NULL )
	{
		$check 						= $this->permission->check_permission( $module_id, $action );

		if( !$check )
		{
			redirect(base_url() . 'errors/index/'.ERROR_CODE_401.'/?message=&system='.static::$system , 'location');
		}
	}	

	protected function redirect_off_system( $module_code, $show_404 = TRUE )
	{
		$check 						= $this->check_system->check_system( $module_code );
	
		if( !$check )
		{
			if( $show_404 )
			{
				show_404();
			}
			else
			{
				redirect(base_url() . 'errors/index/'.ERROR_CODE_401.'/?message=&system='.static::$system , 'location');
			}
		}
	}	

	protected function redirect_off_system_modal( $module_code )
	{
		$check 						= $this->check_system->check_system( $module_code );

		if( !$check )
		{
			redirect(base_url() . 'errors/modal/'.ERROR_CODE_401.'/?message=&system='.static::$system , 'location');
		}
	}	

	protected function error_page( $msg, $error_type = ERROR_TYPE_INDEX, $error_code = ERROR_CODE_500, $tab = FALSE )
	{
		redirect(base_url() . 'errors/'.$error_type.'/'.$error_code.'/?message='.base64_url_encode($msg).'&system='.static::$system.'&tab='.$tab, 'location');
	}

	protected function error_index( $msg, $error_code = ERROR_CODE_500, $tab = FALSE )
	{
		$this->error_page( $msg, ERROR_TYPE_INDEX, $error_code, $tab );
	}

	protected function error_modal( $msg, $error_code = ERROR_CODE_500, $tab = FALSE )
	{
		$this->error_page( $msg, ERROR_TYPE_MODAL, $error_code );
	}

	protected function error_index_tab( $msg, $error_code = ERROR_CODE_500 )
	{
		$this->error_page( $msg, ERROR_TYPE_INDEX, $error_code, TRUE );
	}

	protected function error_modal_tab( $msg, $error_code = ERROR_CODE_500 )
	{
		$this->error_page( $msg, ERROR_TYPE_MODAL, $error_code, TRUE );
	}
	/**
	 * Use this helper function to replace the all {replace} in a string usually use for notification
	 * so that it will be more readable to the devs what is the whole message. 
	 * i.e. $document_year = 2017, $document_type_name = 'Test' $org_name = 'Test' 
	 * 		$message_details = array( 'document_year' => 2017, 'document_type_name' => 'test', 'org_name' => 'Test' )
	 * i.e. $message = {document_year} {document_type_name} of {org_name}.
	 * The  result will be 2017 test of Test.
	 *
	 * @param  $message_details - required. key value of what will be the value of the replacements
	 * @param  $message - required. Message
	 * @return string
	 */
	public function construct_message(array $message_details, $message)
	{
		$message_str 			= '';

		try
		{
			$message_str 		= construct_message( $message_details, $message );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e ) 
		{
			throw $e;
		}
		
		return $message_str;
	}

	public function set_filter( array $values )
	{
		$this->core_filters->set_filter( $values );

		return $this;
	}

	public function filter_string( $key, $decode = FALSE, $options = array() )
	{
		$this->core_filters->filter_type( 'string', $key, $decode, $options );

		return $this;
		
	}

	public function filter_number( $key, $decode = FALSE, $options = array() )
	{
		$this->core_filters->filter_type( 'number', $key, $decode, $options );

		return $this;
		
	}

	public function filter_float( $key, $decode = FALSE, $options = array() )
	{
		$this->core_filters->filter_type( 'float', $key, $decode, $options );

		return $this;
		
	}

	public function filter_email( $key, $decode = FALSE, $options = array() )
	{
		$this->core_filters->filter_type( 'email', $key, $decode, $options );

		return $this;
		
	}

	public function filter_url( $key, $decode = FALSE, $options = array() )
	{
		$this->core_filters->filter_type( 'url', $key, $decode, $options );

		return $this;
		
	}

	public function filter_date( $key, $decode = FALSE, $options = array() )
	{
		$this->core_filters->filter_type( 'date', $key, $decode, $options );

		return $this;
	}

	public function filter()
	{
		return $this->core_filters->filter();
	}

	protected function get_constants( $pattern, $json = FALSE )
	{
		$constants 			 		= array();

		$constants_user 		  = get_defined_constants( TRUE );

		foreach( $constants_user['user'] as $key => $value ) 
		{
			if( !preg_match( '/'.$pattern.'/' , $key ) ) continue;

			$constants[ $key ]		= $value;
		}

		if( $json )
		{
			$constants 				= !EMPTY( $constants ) ? json_encode( $constants ) : array();
		}

		return $constants;
	}

	public function generate_salt_token_arr( $id )
	{
		$arr 		= array();

		if( EMPTY( $id ) )
		{
			return;
		}

		$args 		= func_get_args();
		$cnt_arg 	= count( $args );

		if( is_array( $id ) )
		{
			if( ISSET( $id[1] ) )
			{
				$salt 	= $id[1];
				
			}
			else
			{
				$salt 	= gen_salt();
			}
			
			if( ISSET( $id[0] ) )
			{
				$id 	= $id[0];
			}
			else
			{
				$id 	= 0;
			}
		}
		else 
		{
			$salt 		= gen_salt();
		}

		$id_dec 		= base64_url_decode( $id );

		if( !EMPTY( $id_dec ) AND $id_dec != 0 )
		{
			$id_enc 		= $id;
			
			$id 			= base64_url_decode($id);
		}
		else
		{
			if( $id_dec === FALSE )
			{
				$id_enc 		= base64_url_encode( $id );	
			}
			else if( $id_dec == 0 )
			{
				$id_enc 		= base64_url_encode( $id_dec );
			}
			else
			{
				$id_enc 		= $id;
			}
		}

		$id_salt 		= $salt;
		$id_token 		= in_salt( $id, $id_salt );

		$id_concat 		= $id;

		$id_token_concat = "";

		$arr 			= array(
			'id' 		=> $id,
			'id_enc'	=> $id_enc,
			'salt'		=> $id_salt,
			'token'		=> $id_token
		);

		if( $cnt_arg > 1 )
		{	
			unset( $args[0] );

			$id_concat 		.= '/';

			foreach( $args as $k => $a )
			{
				$id_concat .= $a.'/';

				$arr['sub_id_'.$k]	= $a;
			}

			$id_concat 			= rtrim($id_concat, '/');

			$id_token_concat 	= in_salt( $id_concat, $id_salt );

			$arr['token_concat']	= $id_token_concat;
			
		}

		return $arr;
	}

	public function unlink_attachment( $path )
	{
		$this->core_file_helper->unlink_attachment( $path );
	}

	public function stringify_array( array $arr, $start_delimiter = '', $end_delimeter = '</br>' )
	{

		$str 		= "";

		if( !EMPTY( $arr ) ) 
		{

			foreach( $arr as $key => $value ) 
			{

				if( !EMPTY( $start_delimiter ) ) 
				{

					$str 	.= $start_delimiter.$value.$end_delimeter;

				} 
				else 
				{

					$str 	.= $value.$end_delimeter;

				}

			}

		}

		return $str;

	}

	public function create_zip($files = array(), $destination = '', $overwrite = false)
	{
		return $this->core_file_helper->create_zip( $files, $destination, $overwrite );
	}

	public function unzip_file($dir, $extract_to)
	{
		return $this->core_file_helper->unzip_file( $dir, $extract_to );
	}

	/**
	 * Use this helper function to get the correct base path or root path where the uploaded file will be stored
	 *
	 * @return string
	 */
	public function get_root_path()
	{
		$path 	= FCPATH;

		try
		{
			$path 	= get_root_path();
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $path;
	}

	public function check_custom_path()
	{
		$checked_upload_path 	= false;

		try
		{
			$checked_upload_path = check_custom_path();
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $checked_upload_path;
	}
	
}