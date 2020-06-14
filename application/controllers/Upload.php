<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Upload extends SYSAD_Controller
{
	protected $allowed_types 	= '';
	protected $file_type 		= '';
	protected $mimes 			= array();

	private $date_now;
	
	public function __construct()
	{
		parent::__construct();

		$this->date_now 	= date('Y-m-d H:i:s');
		
		$this->mimes 		= get_mimes();

		$this->config->load('custom_mimes');

		$this->mimes['mkv']	= array('video/webm', 'video/x-matroska');
		$this->mimes['ico']	= array('image/x-icon');

		$custom_mimes 		= $this->config->item('custom_mimes');

		$this->mimes 		= array_merge( $this->mimes, $custom_mimes );
		
		$this->set_allowed_types($this->mimes);
		
		$this->load->model('Common_validate_model', 'cvm');
		$this->load->model('Upload_model', 'uplm');
		
	}
	
	protected function set_allowed_types($types)
	{
		$this->allowed_types = (is_array($types) OR $types === '*')
			? $types
			: explode('|', $types);
	}

	public function get_extension($filename, $file_ext_tolower = FALSE)
	{
		$x = explode('.', $filename);

		if (count($x) === 1)
		{
		    return '';
		}

		$ext = ($file_ext_tolower) ? strtolower(end($x)) : end($x);
		return '.'.$ext;
	}

	public function is_allowed_filetype(array $params, $fileName, $tmp_name,  $ignore_mime = FALSE)
	{
		$extension 	= $this->get_extension($fileName);

		$this->_file_mime_type($params['file']);

		if ($this->allowed_types === '*')
		{
			return TRUE;
		}

		if( EMPTY($this->allowed_types) OR !is_array($this->allowed_types) )
		{
			// $this->set_error('upload_no_file_types');
			return FALSE;
		}

		$ext = strtolower(ltrim($extension, '.'));
		
		if ( ! in_array($ext, array_keys( $this->allowed_types ), TRUE))
		{
			return FALSE;
		}

		// Images get some additional checks
		
			
		if (in_array($ext, array('gif', 'jpg', 'jpeg', 'jpe', 'png'), TRUE) && @getimagesize($tmp_name) === FALSE)
		{
			return FALSE;
		}

		if ($ignore_mime === TRUE)
		{
			return TRUE;
		}

		$mimes 	= $this->mimes;

		if (ISSET($mimes[$ext]))
		{
			return is_array($mimes[$ext])
				? in_array($this->file_type, $mimes[$ext], TRUE)
				: ($mimes[$ext] === $this->file_type);
		}

		return FALSE;
	}

	protected function _file_mime_type($file)
	{
		$regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';

		if (function_exists('finfo_file'))
		{
			$finfo = @finfo_open(FILEINFO_MIME);
			if (is_resource($finfo)) 
			{
				$mime = @finfo_file($finfo, $file['tmp_name']);
				finfo_close($finfo);

				if (is_string($mime) && preg_match($regexp, $mime, $matches))
				{
					$this->file_type = $matches[1];
					return;
				}
			}
		}

		if (DIRECTORY_SEPARATOR !== '\\')
		{
			$cmd = function_exists('escapeshellarg')
				? 'file --brief --mime '.escapeshellarg($file['tmp_name']).' 2>&1'
				: 'file --brief --mime '.$file['tmp_name'].' 2>&1';

			if (function_usable('exec'))
			{
				$mime = @exec($cmd, $mime, $return_status);
				if ($return_status === 0 && is_string($mime) && preg_match($regexp, $mime, $matches))
				{
					$this->file_type = $matches[1];
					return;
				}
			}

			if ( ! ini_get('safe_mode') && function_usable('shell_exec'))
			{
				$mime = @shell_exec($cmd);
				if (strlen($mime) > 0)
				{
					$mime = explode("\n", trim($mime));
					if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
					{
						$this->file_type = $matches[1];
						return;
					}
				}
			}

			if (function_usable('popen'))
			{
				$proc = @popen($cmd, 'r');
				if (is_resource($proc))
				{
					$mime = @fread($proc, 512);
					@pclose($proc);
					if ($mime !== FALSE)
					{
						$mime = explode("\n", trim($mime));
						if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
						{
							$this->file_type = $matches[1];
							return;
						}
					}
				}
			}
		}

		if (function_exists('mime_content_type'))
		{
			$this->file_type = @mime_content_type($file['tmp_name']);
			if (strlen($this->file_type) > 0) 
			{
				return;
			}
		}

		$this->file_type = $file['type'];
	}
	
	private function _filter( array $orig_params, array $par_keys )
	{
		$par 			= $this->set_filter( $orig_params );
		
		foreach( $par_keys as $key )
		{
			$par->filter_string( $key, TRUE );
		}
		
		$params 		= $par->filter();
		
		return $params;
	}

	public function upload_file_method($source, $output_dir, $newfilename, $upload_type)
	{
		$image_ext 	= array('png','jpeg','jpg');
		$exten 		= pathinfo($newfilename, PATHINFO_EXTENSION);

		$enable_image_compression	= get_setting(MEDIA_SETTINGS, "enable_image_compression");

		if(in_array(strtolower($exten),$image_ext) AND !EMPTY($enable_image_compression))
		{
			$info 	= getimagesize($source);

			$image_quality 	= get_setting(MEDIA_SETTINGS, "image_quality_compression");

			if( EMPTY( $image_quality ) ) 
			{
				$image_quality = 0;
			}

			if ($info['mime'] == 'image/jpeg') 
			{
				$image = imagecreatefromjpeg($source);
			}
			elseif ($info['mime'] == 'image/gif') 
			{
				$image = imagecreatefromgif($source);	
			}
			elseif ($info['mime'] == 'image/png') 
			{
				$image = imagecreatefrompng($source);
			}

			imagejpeg($image, $output_dir.$newfilename, $image_quality);

			if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
			{
				$blob_file = file_get_contents($output_dir.$newfilename);

				if( file_exists( $output_dir.$newfilename ) )
				{
					unlink( $output_dir.$newfilename );
				}

				return $blob_file;
			}

		}
		else
		{
			if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
			{
				$blob_file 	= file_get_contents($source);

				return $blob_file;
			}
			else
			{
				move_uploaded_file($source, $output_dir.$newfilename);	
			}
		}
	}

	
	public function index()
	{
		try
		{
			$params		= get_params();
			$output_dir = $params['dir'];

			$upload_type = get_media_upload_type();
			
			$root_path 	= $this->get_root_path();
			
			$output_dir = $root_path.$output_dir;
			$output_dir = str_replace(array('/', '\\'), array(DS, DS), $output_dir);

			if( !EMPTY( $upload_type ) )
			{
				if( $upload_type == MEDIA_UPLOAD_TYPE_DIR )
				{
					if(!is_dir($output_dir))
					{
						mkdir($output_dir,0777,TRUE);
					}		
				}
				else if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
				{

				}
				else
				{

				}
			}
			else
			{
				if(!is_dir($output_dir))
				{
					mkdir($output_dir,0777,TRUE);
				}
			}
			
			$salt 	= gen_salt();

			SYSAD_Model::beginTransaction();
			
			if(ISSET($params["file"]))
			{
				$ret 	= array();
				
				$error 	= $params["file"]["error"];
				//You need to handle both cases
				//If any browser does not support serializing of multiple files using FormData()
				
				$unique_id 	= uniqid();
				
				if(!is_array($params["file"]["name"])) //single file
				{
					$fileName 	= str_replace(" ","",$params["file"]["name"]);
					$allowed 	= $this->is_allowed_filetype($params, $fileName, $params["file"]["tmp_name"] );
					
					$extension 	= pathinfo($fileName, PATHINFO_EXTENSION);
					$fileName 	= pathinfo($fileName, PATHINFO_FILENAME);
					$fileName 	= preg_replace('/[^A-Za-z0-9]/u','', strip_tags($fileName));
					$newfilename 	= str_replace(array("/", "\\", "."), array("","",""),crypt($fileName, $salt)).date('Ymd').$unique_id.'.'.$extension;
					// $newfilename	= crypt($newfilename, $salt);
					if( !$allowed )
					{
						throw new Exception('Invalid File');
					}

					$ins_val 	= array();

					$exten 			= pathinfo($newfilename, PATHINFO_EXTENSION);

					if($params['file']['error'] == 0) 
					{
						
						// $blob_file 	= file_get_contents($params['file']['tmp_name']);
						$ins_val = array(
							'sys_file_name' 	=> $newfilename,
							'orignal_file_name'	=> $params['file']['name'],
							'mime'				=> $params['file']['type'],
							'size'				=> intval($params['file']['size']),
							// 'data' 				=> $blob_file,
							'desired_path'		=> $output_dir,
							'extension' 		=> $exten,
							'uploaded_by' 		=> $this->session->user_id,
							'uploaded_date'		=> $this->date_now
						);

					}

					if( !EMPTY( $upload_type ) )
					{
						if( $upload_type == MEDIA_UPLOAD_TYPE_DIR )
						{
							$this->upload_file_method($params["file"]["tmp_name"], $output_dir, $newfilename, $upload_type);
							// move_uploaded_file($params["file"]["tmp_name"],$output_dir.$newfilename);	
						}
						else if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
						{
							if($params['file']['error'] == 0) 
							{
								$blob_file 		= $this->upload_file_method($params["file"]["tmp_name"], $output_dir, $newfilename, $upload_type);
								$ins_val['data'] = $blob_file;
								/*$blob_file 	= file_get_contents($params['file']['tmp_name']);
								$ins_val['data'] = $blob_file;*/
							}
						}
						else
						{
							$this->upload_file_method($params["file"]["tmp_name"], $output_dir, $newfilename, $upload_type);
							// move_uploaded_file($params["file"]["tmp_name"],$output_dir.$newfilename);
						}
					}
					else
					{
						$this->upload_file_method($params["file"]["tmp_name"], $output_dir, $newfilename, $upload_type);
						// move_uploaded_file($params["file"]["tmp_name"],$output_dir.$newfilename);
					}

					if( !EMPTY( $ins_val ) )
					{
						if($params['file']['error'] == 0) 
						{
							$file_id 	= $this->uplm->insert_helper(SYSAD_Model::CORE_TABLE_FILE_DB_STORAGE,  $ins_val);
						}
					}
					
					$ret[] 			= $newfilename;
				}
				else  //Multiple files, file[]
				{
					
					$fileCount 	= count($params["file"]["name"]);
					
					for($i=0; $i < $fileCount; $i++)
					{
						$fileName 		= str_replace(" ","_",$params["file"]["name"][$i]);
						$allowed 		= $this->is_allowed_filetype($params, $fileName, $params["file"]["tmp_name"][$i] );
						$gen_file 		= strip_tags(crypt( $fileName, $salt ));
						$gen_file 		= str_replace(array("/", "\\", "."), array("","",""), $gen_file);
						$newfilename = preg_replace('/[^A-Za-z0-9 _.]/u','', $gen_file);
						/*$newfilename 	= preg_replace('/[^A-Za-z0-9 _.]/u','', strip_tags(crypt( $fileName, $salt ) ));*/
						// $newfilename 	= crypt($newfilename, $salt);
						
						
						if( !$allowed )
						{
							throw new Exception('Invalid File');
						}

						$ins_val 	= array();

						if($params['file']['error'][$i] == 0) 
						{
							// $blob_file 	= file_get_contents($params['file']['tmp_name'][$i]);
							$ins_val = array(
								'sys_file_name' 	=> $newfilename,
								'orignal_file_name'	=> $params['file']['name'][$i],
								'mime'				=> $params['file']['type'][$i],
								'size'				=> intval($params['file']['size'][$i]),
								'data' 				=> $blob_file,
								'desired_path'		=> $output_dir,
								'extension' 		=> pathinfo($newfilename, PATHINFO_EXTENSION),
								'uploaded_by' 		=> $this->session->user_id,
								'uploaded_date'		=> $this->date_now
							);
						}

						if( !EMPTY( $upload_type ) )
						{
							if( $upload_type == MEDIA_UPLOAD_TYPE_DIR )
							{
								$this->upload_file_method($params["file"]["tmp_name"][$i], $output_dir, $newfilename, $upload_type);
								// move_uploaded_file($params["file"]["tmp_name"][$i],$output_dir.$newfilename);
							}
							else if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
							{
								if($params['file']['error'][$i] == 0) 
								{
									/*$blob_file 	= file_get_contents($params['file']['tmp_name'][$i]);
									$ins_val['data'] = $blob_file;*/

									$blob_file 		= $this->upload_file_method($params["file"]["tmp_name"], $output_dir, $newfilename, $upload_type);
									$ins_val['data'] = $blob_file;
								}
							}
							else
							{
								$this->upload_file_method($params["file"]["tmp_name"][$i], $output_dir, $newfilename, $upload_type);
								// move_uploaded_file($params["file"]["tmp_name"][$i],$output_dir.$newfilename);
							}
						}
						else
						{
							$this->upload_file_method($params["file"]["tmp_name"][$i], $output_dir, $newfilename, $upload_type);
							// move_uploaded_file($params["file"]["tmp_name"][$i],$output_dir.$newfilename);
						}
						
						
						if( !EMPTY( $ins_val ) )
						{
							if($params['file']['error'][$i] == 0) 
							{
								$file_id 	= $this->uplm->insert_helper(SYSAD_Model::CORE_TABLE_FILE_DB_STORAGE,  $ins_val);
							}
						}
						
						$ret[] 	= $newfilename;
					}
				}
				
				echo json_encode($ret);
			}

			SYSAD_Model::commit();

		}
		catch( PDOException $e )
		{
			SYSAD_Model::rollback();
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
			
			// echo json_encode($msg);
		}
		catch (Exception $e)
		{
			SYSAD_Model::rollback();
			
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
			
			// echo json_encode($msg);
		}
	}
	
	public function existing_files()
	{
		try
		{
			$params		= get_params();
			$output_dir = $params['dir'];
			$root_path 	= $this->get_root_path();

			$upload_type = get_media_upload_type();
			
			$output_dir = $root_path.$output_dir;
			$output_dir = str_replace(array('/', '\\'), array(DS, DS), $output_dir);

			if( !EMPTY( $upload_type ) )
			{
				if( $upload_type == MEDIA_UPLOAD_TYPE_DIR )
				{
					$files 		= scandir($output_dir);
				}
				else if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
				{
					$file_det 		= $this->uplm->get_file_by_desired_path($output_dir);
					if( !EMPTY( $file_det ) )
					{
						$files 		= array_column($file_det, 'sys_file_name');
					}
					else
					{
						$files 		= array();
					}
				}
				else
				{
					$files 		= scandir($output_dir);
				}
			}
			else
			{
				$files 		= scandir($output_dir);
			}
			
			$db_file 	= ( ISSET( $params['file'] ) ) ? $params['file'] : NULL;
			
			$ret 		= array();
			
			$multi_file = FALSE;
			
			if( ISSET( $params['max_file'] ) AND intval( $params['max_file'] ) > 1 )
			{
				$multi_file = TRUE;
				
				foreach( $params['file'] as $f )
				{
					$db_file_arr = explode('|', $f);
					
					if( !EMPTY( $db_file_arr ) )
					{
						$db_file = array();
						
						foreach( $db_file_arr as $db_file_e )
						{
							$db_file_d 	= explode('=', $db_file_e);
							
							$db_file[]  = $db_file_d[0];
						}
					}
				}
			}
			
			if(ISSET($db_file))
			{
				foreach($files as $file)
				{
					if($file == "." || $file == "..")
					{
						continue;
					}	
						if( is_array( $db_file ) )
						{
							$multi_file = TRUE;
							
							if( in_array( $file , $db_file ) )
							{
								$key 	= array_search($file, $db_file);
								
								$key 	= ( int )$key;
								
								$ret[$key]=$file;
							}
						}
						else
						{
							if($file == $db_file)
								$ret[]=$file;
						}
				}
			}
			
			if( $multi_file )
			{
				ksort( $ret );
				
				$ret 	= flattened_array( $ret );
			}
			
			echo json_encode($ret);
		}
		catch(Exception $e)
		{
			echo $this->rlog_error($e, TRUE);
		}
	}
	
	public function delete($params = array())
	{
		$msg 				= '';
		
		try
		{
			$params			= (!EMPTY($params))? $params : get_params();
			$output_dir 	= $params['dir'];

			$upload_type = get_media_upload_type();
			
			$root_path 	= $this->get_root_path();
			$output_dir = $root_path.$output_dir;
			$output_dir = str_replace(array('/', '\\'), array(DS, DS), $output_dir);
			
			$module_schema 	= DB_CORE;
			$method 		= 'delete_attachments';
			$audit_method 	= ACTION_DELETE;
			
			if(isset($params["op"]) && $params["op"] == "delete" && isset($params['name']))
			{
				
				if(ISSET($params['delete_path']) AND !EMPTY( $params['delete_path'] ))
				{
					$type_obj 		= Modules::load( $params['delete_path'] );
					
					if( !EMPTY( $type_obj ) AND ISSET( $params['delete_path_method'] ) )
					{
						call_user_func_array( array( $type_obj, $params['delete_path_method'] ), array( $params ) );
						
					}
				}
				else
				{
					if( ISSET( $params['module_table'] ) AND !EMPTY( $params['module_table'] ) )
					{
						
					}
				}
				
				$fileName = $params['name'];
				$fileName = str_replace("..",".",$fileName); //required. if somebody is trying parent folder files
				$filePath = $output_dir. $fileName;
				$file_exists = FALSE;

				$this->uplm->beginTransaction();

				$main_where 	= array(
					'sys_file_name'	=> $fileName,
					'desired_path'	=> $output_dir
				);

				$this->uplm->delete_helper( SYSAD_Model::CORE_TABLE_FILE_DB_STORAGE, $main_where);

				if (file_exists($filePath))
				{
					$file_exists = TRUE;
					unlink($filePath);
				}	

				$this->uplm->commit();

				if( $file_exists )
				{
					if(!ISSET($params["no_echo"]))
					{
						echo "Deleted File ".$fileName."<br>";
					}
				}
			}
		}
		catch( PDOException $e )
		{
			$this->uplm->rollback();
			
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
		}
		catch (Exception $e)
		{
			$this->uplm->rollback();
			
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
		}
		
		echo $msg;
	}
	
	public function download($file_arg = NULL, $path_arg = NULL)
	{
		
		$flag 				= 0;
		
		try
		{
			$params 		= get_params(TRUE, TRUE);

			if( !EMPTY( $file_arg ) )
			{
				$params['file'] 	= $file_arg;
			}
			
			if( ISSET( $params['file'] ) )
			{
				$root_path 	= $this->get_root_path();
				
				$path_dir 	= NULL;
				$path_fold 	= NULL;

				if( !EMPTY( $path_arg ) )
				{
					$params['path'] = $path_arg;
				}
				
				if( ISSET( $params['dir'] ) )
				{
					$dir_arr 	= explode( '|', $params['dir'] );
					
					$dir_path 	= implode( '/', $dir_arr);
					
					$path_dir 	= $root_path.PATH_UPLOADS.$dir_path.'/';
					$path_fold 	= PATH_UPLOADS.$dir_path.'/';
				}
				else if( $params['path'] )
				{
					$path_dir 	= $root_path.$params['path'];
					$path_fold 	= $params['path'];
				}
				
				
				$path_dir 	= str_replace(array('\\','/'), array(DS,DS), $path_dir);
				
				$path 		= $path_dir.$params['file'];
				$path 		= str_replace(array('\\','/'), array(DS,DS), $path);

				$file_det  	= $this->uplm->get_file_by_sys_file_name($params['file']);

				$upload_type = get_media_upload_type();
				$file_exists = FALSE;
				$file_type   = TRUE;

				$raw_data  		= NULL;
				$db_mime 		= NULL;
				$orig_file_name = NULL;
				$ext 			= NULL;
				$path_inf 		= NULL;

				if( ISSET( $params['orig_file'] ) )
				{
					$orig_file_name = $params['orig_file'];
				}
				else
				{
					if( !EMPTY( $file_det ) )
					{
						$orig_file_name = $file_det['orignal_file_name'];
					}
				}

				if( !EMPTY( $upload_type ) )
				{
					if( $upload_type == MEDIA_UPLOAD_TYPE_DIR )
					{
						$file_exists = file_exists($path);
						$file_type 	 = TRUE;
					}
					else if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
					{
						$file_type 	= FALSE;

						if( !EMPTY( $file_det ) )
						{
							$file_exists = TRUE;
							$db_mime 	= $file_det['mime'];
							$raw_data	= $file_det['data'];
						}
					}
					else
					{
						$file_exists = file_exists($path);
						$file_type 	 = TRUE;
					}
				}
				else
				{
					$file_exists = file_exists($path);
					$file_type 	 = TRUE;			
				}
				
				if( $file_exists )
				{
					if( $file_type )
					{
						$ext 		= pathinfo( $path, PATHINFO_EXTENSION );
						$path_inf 	= pathinfo( $path );
					}
					else
					{
						if( !EMPTY( $file_det ) )
						{
							$ext 	= $file_det['extension'];
						}
					}

					$img_ext_arr 	= array('gif','png','jpeg','jpg');
					$txt_ext_arr 	= array('txt');
					
					if( ISSET( $params['js_viewer'] ) AND !EMPTY( $params['js_viewer'] ) )
					{
						if( $file_type )
						{
							$file_get 	= file_get_contents( $path );

							echo base64_encode($file_get);
						}
						else
						{
							if( !EMPTY( $raw_data ) )
							{
								echo base64_encode($raw_data);		
							}
							
						}
					}
					else
					{
						if( strtolower( $ext ) == 'pdf'
								AND ( !ISSET( $params['pdf_no'] ) AND EMPTY( $params['pdf_no'] ) )
								)
						{

							if( $file_type )
							{
								$pdf 	= file_get_contents( $path );
								
								header("Content-type: application/pdf");
								header("Content-Disposition: inline; filename=".$params['file']."");
								
								readfile( $path );
							}
							else
							{
								header("Content-type: application/pdf");
								header("Content-Disposition: inline; filename=".$orig_file_name."");

								if( !EMPTY( $raw_data ) )
								{
									echo $raw_data;
								}
								
								// echo $raw_data
							}
							
						}
						else if( in_array( strtolower( $ext ), $txt_ext_arr ) )
						{
							if( $file_type )
							{
								$text 	= file_get_contents( $path );

								header("Content-type: text/plain");
								header("Content-Disposition: inline; filename=".$orig_file_name."");

								echo $text;
							}
							else
							{
								if( !EMPTY( $raw_data ) )
								{

									header("Content-type: text/plain");
									header("Content-Disposition: inline; filename=".$orig_file_name."");

									echo $raw_data;
								}
							}
						}
						else if( in_array( strtolower( $ext ), $img_ext_arr ) )
						{
							$img_path 	= output_image( $params['file'], $path_fold );
							// $imginfo 	= getimagesize($img_path);
							
							$this->load->view('imageviewer', array('img_path' => $img_path));
							
						}
						else
						{
							$this->load->helper('download');

							if( $file_type )
							{	
								force_download( $path, NULL, NULL, $orig_file_name );
							}
							else
							{
								if( !EMPTY( $raw_data ) )
								{
									force_download( $orig_file_name, $raw_data, $db_mime );
								}
							}
						}
					}
					
					$flag 		= 1;
				}
				else
				{
					throw new Exception('File not found.');
				}
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
			
			$this->error_index( $msg );
		}
		catch(Exception $e)
		{
			$msg  	= $this->rlog_error($e, TRUE);
			
			$this->error_index( $msg );
		}
		
		if( !$flag )
		{
			// redirect(base_url().'Errors/index/402/');
		}
		
	}
	
	public function force_download($file_arg = NULL, $path_arg = NULL)
	{
		
		$flag 				= 0;
		
		try
		{
			$params 		= get_params(TRUE, TRUE);

			if( !EMPTY( $file_arg ) )
			{
				$params['file']	= $file_arg;
			}
			
			if( ISSET( $params['file'] ) )
			{
				$root_path 	= $this->get_root_path();
				
				$path_dir 	= NULL;
				$path_fold 	= NULL;

				$upload_type = get_media_upload_type();

				if( !EMPTY( $path_arg ) )
				{
					$params['path'] = $path_arg;
				}
				
				if( ISSET( $params['dir'] ) )
				{
					$dir_arr 	= explode( '|', $params['dir'] );
					
					$dir_path 	= implode( '/', $dir_arr);
					
					$path_dir 	= $root_path.PATH_UPLOADS.$dir_path.'/';
					$path_fold 	= PATH_UPLOADS.$dir_path.'/';
				}
				else if( $params['path'] )
				{
					$path_dir 	= $root_path.$params['path'];
					$path_fold 	= $params['path'];
				}
				
				
				$path_dir 	= str_replace(array('\\','/'), array(DS,DS), $path_dir);
				
				$path 		= $path_dir.$params['file'];
				$path 		= str_replace(array('\\','/'), array(DS,DS), $path);

				$file_det  = $this->uplm->get_file_by_sys_file_name($params['file']);

				$file_type  = TRUE;

				$raw_data  = NULL;
				$db_mime 	= NULL;
				$orig_file_name = NULL;

				if( ISSET( $params['orig_file'] ) )
				{
					$orig_file_name = $params['orig_file'];
				}
				else
				{
					if( !EMPTY( $file_det ) )
					{
						$orig_file_name = $file_det['orignal_file_name'];
					}
				}

				if( !EMPTY( $upload_type ) )
				{
					if( $upload_type == MEDIA_UPLOAD_TYPE_DIR )
					{
						$file_exists = file_exists($path);
						$file_type 	 = TRUE;
					}
					else if( $upload_type == MEDIA_UPLOAD_TYPE_DB )
					{
						$file_type 	= FALSE;

						if( !EMPTY( $file_det ) )
						{
							$file_exists = TRUE;
							$db_mime 	= $file_det['mime'];
							$raw_data	= $file_det['data'];
						}
					}
					else
					{
						$file_exists = file_exists($path);
						$file_type 	 = TRUE;
					}
				}
				else
				{
					$file_exists = file_exists($path);
					$file_type 	 = TRUE;
					
				}

				if( $file_exists )
				{
					$this->load->helper('download');
					
					if( $file_type )	
					{
						$ext 		= pathinfo( $path, PATHINFO_EXTENSION );
						
						$img_ext_arr 	= array('gif','png','jpeg','jpg');
						
						force_download( $path, NULL, NULL, $orig_file_name );
						
						$flag 		= 1;
					}	
					else
					{
						if( !EMPTY( $raw_data ) )
						{
						
							force_download( $orig_file_name, $raw_data, $db_mime );
						}
					}			
				}
				else
				{
					throw new Exception('File not found.');
				}
			}
		}
		catch( PDOException $e )
		{
			$msg 	= $this->get_user_message($e);
			
			$this->error_index( $msg );
		}
		catch(Exception $e)
		{
			$msg  	= $this->rlog_error($e, TRUE);
			
			$this->error_index( $msg );
		}
		
		if( !$flag )
		{
			// redirect(base_url().'Errors/index/402/');
		}
		
	}
	
	public function upload_ckeditor()
	{
		
		$params				= get_params(TRUE, TRUE);
		$output_dir 		= PATH_CKEDITOR_UPLOADS;
		$ret 				= array();
		$ret_filename 		= "";
		$url 				= "";
		$error 				= "";
		$flag 				= 0;
		//$cmd = 'icacls '.SERVER_UPLOAD_FOLDER.' /grant "Everyone":(OI)(CI)F';
		
		try
		{
			
			$uploadimgerrors1 = $this->lang->line('err_ck_upload1');
			$uploadimgerrors2 = $this->lang->line('err_ck_upload2');
			$uploadimgerrors3 = $this->lang->line('err_ck_upload3');
			$uploadimgerrors4 = $this->lang->line('err_ck_upload4');
			$uploadimgerrors5 = $this->lang->line('err_ck_upload5');
			$uploadimgerrors6 = $this->lang->line('err_ck_upload6');
			$uploadimgerrors7 = $this->lang->line('err_ck_upload7');
			$uploadimgerrors8 = $this->lang->line('err_ck_upload8');
			
			if( !EMPTY( $output_dir ) )
			{
				$root_path 		= $this->get_root_path();
				
				$output_dir 	= $root_path.PATH_CKEDITOR_UPLOADS;
				$output_dir 	= str_replace( array('/', '\\'), array( DS, DS ), $output_dir );
			}
			
			if( !is_dir( $output_dir ) )
			{
				mkdir( $output_dir, 0777, TRUE );
			}
			
			
			if( ISSET( $params["upload"] ) )
			{
				
				$file_type 		= pathinfo( $params['upload']['name'], PATHINFO_EXTENSION );
				
				$valid_file_type= explode(',', IMAGE_EXTENSIONS);
				
				if( !in_array( strtolower( $file_type ), $valid_file_type ) )
				{
					echo '
					 	<script src="'.base_url().PATH_JS.'jquery-2.1.1.min.js"></script>
						<script src="'.base_url().PATH_JS.'ckeditor/plugins/imageuploader/dist/sweetalert.min.js"></script>
    					<link rel="stylesheet" type="text/css" href="'.base_url().PATH_JS.'ckeditor/plugins/imageuploader/dist/sweetalert.css">
			            <script>
			            $( function () {
							 swal({
				              title: "Error!",
				              text: "'.$uploadimgerrors8.'",
				              type: "error",
				              closeOnConfirm: false
				            },
				            function(){
				              history.back();
				            });
			            });
			            </script>
			        ';
					exit();
				}
				
				$check 			= getimagesize( $params["upload"]["tmp_name"] );
				
				if($check !== false)
				{
					$flag 		= 1;
				}
				else
				{
					echo "<script>alert('".$uploadimgerrors1."');</script>";
					$flag 		= 0;
				}
				
				$error 			= ( ISSET( $params["upload"]["error"] ) AND !EMPTY( $params["upload"]["error"] ) ) ? $params["upload"]["error"] :  "";
				
				
				//You need to handle both cases
				//If any browser does not support serializing of multiple files using FormData()
				
				if( !is_array( $params["upload"]["name"] ) ) //single file
				{
					$fileName 	= str_replace(" ","",$params["upload"]["name"]);
					$extension 	= pathinfo($fileName, PATHINFO_EXTENSION);
					$extension 	= strtolower( $extension );
					$fileName 	= pathinfo($fileName, PATHINFO_FILENAME);
					$fileName 	= preg_replace('/[^A-Za-z0-9]/u','', strip_tags($fileName));
					
					$upload_id 	= 0;
					
					/*$upload_id 	= $this->upload->insert_attachments( $this->upload->tbl_uploads,
					 array(
					 'filename'	=> $fileName,
					 'extension'	=> $extension,
					 'dir'		=> $output_dir,
					 'created_by'=> $this->session->user_id,
					 'created_date' => date( 'Y-m-d H:i:s' )
					 )
					 );*/
					
					$unique_id 	= uniqid();
					
					if( !EMPTY( $upload_id ) )
					{
						$unique_id 		= str_pad($upload_id, 4, '0', STR_PAD_LEFT);
					}
					
					if( !EMPTY( $params['asset_type'] ) )
					{
						$newfilename 	= strtoupper( $params['asset_type'] ).'_'.date('Ymd').'_'.$unique_id.'.'.$extension;
					}
					else
					{
						$newfilename 	= $fileName.'_'.date('Ymd').'_'.$unique_id.'.'.$extension;
					}
					
					$ret[] 				= $newfilename;
					$ret_filename 		= $newfilename;
					$url 				= base_url().PATH_FILE_UPLOADS.$newfilename;
					
					move_uploaded_file( $params["upload"]["tmp_name"], $output_dir.$newfilename );
					$flag 				= 1;
					//exec($cmd);
				}
				else  //Multiple files, file[]
				{
					$fileCount 			= count( $params["upload"]["name"] );
					
					for( $i=0; $i < $fileCount; $i++ )
					{
						$fileName 		= str_replace(" ","_",$params["upload"]["name"][$i]);
						$newfilename 	= preg_replace('/[^A-Za-z0-9 _.]/u','', strip_tags($fileName));
						
						move_uploaded_file( $params["upload"]["tmp_name"][$i], $output_dir.$newfilename );
						
						$flag 				= 1;
						
						if( ISSET( $cmd ) )
						{
							exec($cmd);
						}
						
						$ret[] 			= $newfilename;
					}
					
					$ret_filename 		= implode(",", $ret);
				}
			}
		}
		catch( PDOException $e )
		{
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
		}
		catch (Exception $e)
		{
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
		}
		
		$response 					= array(
				'uploaded'				=> $flag,
				'fileName'				=> $ret_filename,
				'url'					=> $url
		);
		
		if( !EMPTY( $error ) )
		{
			$response['error']		= array(
					'message'			=> $error
			);
		}
		
		if( $flag == 1 )
		{
			if( ISSET( $params['CKEditorFuncNum'] ) )
			{
				$CKEditorFuncNum 	= $params['CKEditorFuncNum'];
				
				echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$ret_filename', '');</script>";
			}
		}
		else
		{
			echo "<script>alert('".$uploadimgerrors6." ".$ret_filename." ".$uploadimgerrors7."');</script>";
		}
		
		if( !ISSET( $params['CKEditorFuncNum'] ) )
		{
			echo '<script>history.back();</script>';
		}
		
		// echo json_encode( $response );
	}
	
	public function delete_ckeditor()
	{
		$params				= get_params(TRUE, TRUE);
		$output_dir 		= PATH_CKEDITOR_UPLOADS;
		$ret 				= array();
		$ret_filename 		= "";
		$url 				= "";
		$error 				= "";
		$flag 				= 0;
		
		try
		{
			
			if( !EMPTY( $output_dir ) )
			{
				$root_path 		= $this->get_root_path();
				
				$output_dir 	= $root_path.PATH_CKEDITOR_UPLOADS;
				$output_dir 	= str_replace( array('/', '\\'), array( DS, DS ), $output_dir );
			}
			
			$filename 			= filter_var( $params['img'], FILTER_SANITIZE_STRING);
			
			$file_path 			= $output_dir.$filename;
			
			if( file_exists( $file_path ) )
			{
				if( !unlink( $file_path ) )
				{
					throw new Exception('Cannot delete file');
				}
				else
				{
					header('Location: ' . $_SERVER['HTTP_REFERER']);
					$flag 		= 1;
				}
			}
			
		}
		catch( PDOException $e )
		{
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
		}
		catch (Exception $e)
		{
			RLog::error( $e->getMessage() . "\n" . $e->getTraceAsString() );
			
			$msg 					= $e->getMessage();
		}
		
		if( $flag == 0 )
		{
			echo '
	            <script>
	            swal({
	              title: "Error",
	              text: "'.$msg.'",
	              type: "error",
	              closeOnConfirm: false
	            },
	            function(){
	              history.back();
	            });
	            </script>
	        ';
		}
		
	}

	public function delete_multi_dt()
	{
		$prev_detail 			= array();
		$curr_detail 			= array();
		$audit_table			= array();
		$audit_schema 			= array();
		$audit_action 			= array();

		$orig_params 			= get_params();

		// $delete_per 			= $this->delete_per;

		$msg 					= '';
		$flag 					= 0;
		$status 				= ERROR;

		$main_where 			= array();
		$new_params 			= array();

		try
		{
			$orig_params 			= get_params();
			unset( $orig_params['CSRFToken'] );
			$par_keys 				= array_keys( $orig_params );

			$tables 				= $orig_params['tables'];
			$extra_data 			= $orig_params['extra_data'];
			unset( $orig_params['tables'] );
			unset( $orig_params['extra_data'] );

			if( ISSET( $orig_params['new_params'] ) )
			{
				$new_params 		= $orig_params['new_params'];
				unset($orig_params['new_params']);
			}

			$params 				= $this->_filter( $orig_params, $par_keys );

			$real_par 				= $params;

			foreach( $params as $columns => $val )
			{
				$main_where[$columns] 	= array( 'IN', $val );
			}

			SYSAD_Model::beginTransaction();

			if( !EMPTY( $tables ) )
			{
			
				foreach( $tables as $table )
				{
					$real_table 			= $extra_data['schema'].'.'.$table;
					
					$audit_schema[] 	= $extra_data['schema'];
					$audit_table[] 	 	= $real_table;
					$audit_action[] 	= AUDIT_DELETE;
					$prev_detail[] 		= $this->cvm->get_details_for_audit( $real_table,
											$main_where
										 );

					$this->cvm->delete_helper( $real_table, $main_where );

					$curr_detail[] 		= array();
				}

				$audit_name 		= $extra_data['module'].'.';

				$audit_activity 			= sprintf( $this->lang->line('audit_trail_delete'), $audit_name);

				$this->audit_trail->log_audit_trail( $audit_activity, $extra_data['module'], $prev_detail, $curr_detail, $audit_action, $audit_table, $audit_schema );
			}

			SYSAD_Model::commit();

			$status 				= SUCCESS;
			$flag 					= 1;
			$msg 					= $this->lang->line( 'data_deleted' );
		}
		catch( PDOException $e )
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $this->get_user_message( $e );
		}
		catch (Exception $e) 
		{

			SYSAD_Model::rollback();

			$this->rlog_error( $e );

			$msg 					= $e->getMessage();
		}

		$response 					= array(
			'msg' 					=> $msg,
			'flag' 					=> $flag,
			'status'				=> $status,
			'datatable_new_params' 	=> $new_params
		);

		echo json_encode( $response );	
	}
}