<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Media extends SYSAD_Controller 
{
	public function __construct()
	{
		parent::__construct();
	}

	private function _filter( array $params )
	{
		$arr 				= $this->set_filter( $params )
								->filter_string( 'path' )
								->filter_string( 'file' );

		$arr 				= $arr->filter();

		return $arr;
	}

	private function _validate( array $params )
	{
		$required 			= array();
		$constraints 		= array();

		$required['path'] 	= 'Path';
		$required['file'] 	= 'File';

		$this->check_required_fields( $params, $required );
	}

	public function output_image()
	{
		$path 				= "";

		$orig_params  		= get_params();

		try
		{
			$params 		= $this->_filter( $orig_params );

			$this->_validate( $params );

			$path 			= output_image( $params['file'], $params['path'] );
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		echo $path;
	}
}