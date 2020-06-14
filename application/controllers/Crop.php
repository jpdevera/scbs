<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Crop extends SYSAD_Controller 
{
	private $module_js;

	public function __construct()
	{
		parent::__construct();

		$this->module_js = HMVC_FOLDER."/crop";
	}

	public function modal()
	{
		$data  		= array();
		$resources 	= array();

		try
		{
			$params 				= get_params();

			$data['file'] 			= $params['file'];
			$data['file_name']		= $params['file_name'];

			$resources['load_css'] 	= array(CSS_CROPPER);
			$resources['load_js'] 	= array(JS_CROPPER, $this->module_js);
			$resources['loaded_init'] = array(
				'Crop.init();',
				'Crop.crop();'
			);

			$this->load->view("modals/crop", $data);
			$this->load_resources->get_resource($resources);
		}	
		catch(PDOException $e)
		{
			// LOG ERROR
			$this->rlog_error($e);
			
			// SHOW ERROR PAGE
			$data['heading'] = 'ERROR';
			$data['message'] = $this->get_user_message($e);
			
			$this->load->view(ERROR_MODAL, $data);
			$this->load_resources->get_resource($resources);
		}
		catch(Exception $e)
		{
			// LOG ERROR
			$this->rlog_error($e);
			
			// SHOW ERROR PAGE
			$data['message'] = $e->getMessage();
			
			$this->load->view(ERROR_MODAL, $data);
			$this->load_resources->get_resource($resources);
		}
	}

	public function modal_webcam()
	{
		$data  		= array();
		$resources 	= array();

		try
		{
			$params 				= get_params();

			$resources['load_js'] 	= array(JS_JPEGCAMERA, $this->module_js);
			$resources['loaded_init'] = array(
				'Crop.webcam("'.$params['upload_id'].'", "'.$params['path'].'", '.$params['default_check'].');',
				// 'Crop.crop();'
			);

			$data['params']	= $params;

			$this->load->view("modals/webcam", $data);
			$this->load_resources->get_resource($resources);
		}
		catch(PDOException $e)
		{
			// LOG ERROR
			$this->rlog_error($e);
			
			// SHOW ERROR PAGE
			$data['heading'] = 'ERROR';
			$data['message'] = $this->get_user_message($e);
			
			$this->load->view(ERROR_MODAL, $data);
			$this->load_resources->get_resource($resources);
		}
		catch(Exception $e)
		{
			// LOG ERROR
			$this->rlog_error($e);
			
			// SHOW ERROR PAGE
			$data['message'] = $e->getMessage();
			
			$this->load->view(ERROR_MODAL, $data);
			$this->load_resources->get_resource($resources);
		}
	}

	public function crop()
	{
		$params 	= get_params();

		$flag 		= 0;
		$msg 		= "";
		$status		= ERROR;

		try
		{
			$path 	= generate_real_path( $params['file_path'] );
			
			if( file_exists( $path ) )
			{
				$path_info 	= pathinfo( $path );

				if( !EMPTY( $path_info ) AND ISSET( $params['cropped_image'] ) )
				{
					if( EMPTY( $params['cropped_image']['errors'] ) )
					{
						unlink( $path );

						move_uploaded_file($params['cropped_image']['tmp_name'], $path_info['dirname'].DS.$path_info['basename']);

						$flag 	= 1;
						$msg 	= $this->lang->line('data_cropped');
						$status	= SUCCESS;
					}
				}
			}
		}
		catch(PDOException $e)
		{
			// IDMRIS_Model::rollback();
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			// IDMRIS_Model::rollback();
			$msg = $this->rlog_error($e, TRUE);
		}

		$response 	= array(
			'flag' 	=> $flag,
			'msg'	=> $msg,
			'status'=> $status
		);

		echo json_encode( $response );
	}

	public function upload_photo()
	{
		$params 	= get_params();

		$flag 		= 0;
		$msg 		= "";
		$status		= ERROR;

		$file_name 	= "";

		try
		{
			if( ISSET( $params['webcam_photo'] ) AND !EMPTY( $params['webcam_photo'] ) )
			{
				$unique_id 		= date('Ymd').'_'.uniqid();

				$path 			= str_replace(array('/', '\\'), array(DS, DS), FCPATH.$params['path']);
				$file_name 		= 'webcam_photo_'.$unique_id.'.png';

				move_uploaded_file($params['webcam_photo']['tmp_name'], $path.$file_name);
				
				$flag 			= 1;
				$msg 			= $this->lang->line('data_saved');
				$status			= SUCCESS;
			}
		}
		catch(PDOException $e)
		{
			// IDMRIS_Model::rollback();
			$msg = $this->get_user_message($e);
		}
		catch(Exception $e)
		{
			// IDMRIS_Model::rollback();
			$msg = $this->rlog_error($e, TRUE);
		}


		$response 	= array(
			'flag' 	=> $flag,
			'msg'	=> $msg,
			'status'=> $status,
			'file_name'	=> $file_name
		);

		echo json_encode( $response );
	}
}