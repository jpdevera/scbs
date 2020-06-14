
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Core_file_helper
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function upload_attachment($config, $input_name)
	{
		try 
		{
			$this->CI->load->library('upload', $config);
			// RLog::info(json_encode($config['upload_path']) . ' line 935 ' . json_encode(!is_dir($config['upload_path'])));
			
			if (!is_dir($config['upload_path']))
				mkdir($config['upload_path'], 0777, true);
	
			if (!$this->CI->upload->do_upload($input_name))
				throw new Exception($this->CI->upload->display_errors());
				
			$upload_data = $this->upload->data();
	
			return $upload_data;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	
	}

	public function unlink_attachment( $path )
	{
		if( EMPTY( $path ) )
		{
			throw new Exception ('No path given');
		}

		if( is_dir( $path ) === true ) 
		{
			$files 				= array_diff( scandir( $path ), array( '.', '..' ) );

			foreach( $files as $file ) 
			{
				if( is_dir( $path . $file ) ) 
				{
					$sub_files 	= array_diff( scandir( $path . $file ), array( '.', '..' ) );

					foreach( $sub_files as $sub_file ) 
					{
						if( is_dir( $path . $file .'/'. $sub_file ) ) 
						{
							$this->unlink_attachment( $path . $file .'/'. $sub_file );
						}

						if( is_file( $path . $file .'/'. $sub_file ) ) 
						{
							$this->unlink_attachment( $path . $file .'/'. $sub_file );
						}

					}

					$this->unlink_attachment( $path . $file );

				} 
				else 
				{
					if( file_exists( $path .'/'. $file ) ) 
					{
						if( !unlink( $path .'/'. $file ) )
						{
	           	 			throw new Exception('Cannot delete file');
						}
					}

				}

			}

			$del_fol  =	rmdir( $path );

			return $del_fol;

		} 
		else if( is_file($path) === true ) 
		{
			if( file_exists( $path ) ) 
			{
				return unlink( $path );
			}
			else 
			{
				throw new Exception('Cannot delete file');
			}

		} 
		else 
		{
			throw new Exception( 'Path is invalid.' );
		}
	}

	public function create_zip($files = array(), $destination = '', $overwrite = false)
	{
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($destination) && !$overwrite) {
			return false;
		}
	
	
		//vars
		$valid_files = array();
		
		//if files were passed in...
		if(is_array($files)) {
			
			//cycle through each file
			
			foreach($files as $file) {
				
				//make sure the file exists
				if(file_exists($file)) {
						
					$valid_files[] = $file;
				
				}
			}
		}
		
		//if we have good files...
		if(count($valid_files)) {
			
			//create the archive
			$zip = new ZipArchive();

			$create_overwrite = ( file_exists( $destination ) && $overwrite ) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE;

			if($zip->open($destination, $create_overwrite ) !== true) {
	
				return false;
			}
			
			//add the files	
			foreach($valid_files as $file) {
	
	
	
				$zip->addFile($file,basename($file));
					
			}
			$zip->close();
				
			//check to make sure the file exists
			return file_exists($destination);
		}
		else
		{
			return false;
		}
	}

	public function unzip_file($dir, $extract_to)
	{
	
		if(!is_dir($extract_to))
		{
			mkdir($extract_to,DIR_READ_MODE,TRUE);
		}
	
		$zip = new ZipArchive;
		if ($zip->open($dir) === TRUE)
		{
			$zip->extractTo($extract_to);
			$zip->close();
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}