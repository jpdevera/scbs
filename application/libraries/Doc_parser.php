<?php 
/**
 * This class is use to parse different types of documents.
 * 
 * @author asiagate
 */
class Doc_parser
{
	protected $CI; // Instance of CodeIgniter

	const CHUNK_SIZE 	= 1000;

	protected $main_model;

	public function __construct()
	{
		$this->CI =& get_instance(); // Instance of CodeIgniter

		$this->CI->load->library('Excel');
	}

	public function parse( $path, $sheet_name = NULL, $get_blank = FALSE, $custom = FALSE, $format_val = TRUE )
	{
		$parse 							= array();

		try
		{
			$input_file_type 			= PHPExcel_IOFactory::identify( $path );

			$reader 					= PHPExcel_IOFactory::createReader($input_file_type);

			if( strtoupper( $input_file_type ) == 'CSV' )
			{
		   		$reader->setDelimiter(';');
	        	$reader->setEnclosure('');
			}
			
			$spread_sheet_info 			= $reader->listWorksheetInfo($path);
			
			$chunk_size 				= self::CHUNK_SIZE;

			if( !EMPTY( $spread_sheet_info ) )
			{
				foreach( $spread_sheet_info as $worksheet )
				{
					if( !EMPTY( $sheet_name ) )
					{
						if( is_array( $sheet_name ) )
						{
							if( !in_array( $worksheet['worksheetName'], $sheet_name ) )
							{
								continue;
							}
						}
						else
						{
							if( $worksheet['worksheetName'] != $sheet_name )
							{
								continue;
							}
						}
					}

					$total_rows 	= $worksheet['totalRows'];

					$chunk_filter 	= new PHPExcel_ChunkReadFilter();

					$work_name 		= $worksheet['worksheetName'];
					
					$parse 			= $this->read_chunk_worksheets( $total_rows, $work_name, $path, $reader, $parse, $get_blank, $custom, $format_val );
				}
				
			}

			/*echo '<pre>';
			print_r($parse);*/

			// Rlog::info(var_export($parse, TRUE));			
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}

		return $parse;
	}

	public function read_chunk_worksheets( $total_rows, $work_name, $path, PHPExcel_Reader_IReader $reader, array $parse, $get_blank = FALSE,  $custom = FALSE, $format_val = TRUE )
	{

		try
		{
			$chunk_size 	= self::CHUNK_SIZE;

			$chunk_filter 	= new PHPExcel_ChunkReadFilter();

			$reader->setReadFilter($chunk_filter);
  			$reader->setReadDataOnly(true);
  			$reader->setLoadSheetsOnly($work_name);

			$start_row 		= 0;

  			for( ; $start_row <= $total_rows; $start_row += $chunk_size )
  			{
  				$chunk_filter->setRows( $start_row, $chunk_size );

  				$cache_method 		= PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
			 	$cache_settings 	= array( ' memoryCacheSize '  => '1000MB');

	    		PHPExcel_Settings::setCacheStorageMethod($cache_method, $cache_settings);

			 	$cache_method_objs 	= PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
	    		PHPExcel_Settings::setCacheStorageMethod($cache_method_objs);

	   		 	$cache_method_objf 	= PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

	   		 	$set_cache_stor_met = PHPExcel_Settings::setCacheStorageMethod($cache_method_objf);

	   		 	if ( !$set_cache_stor_met ) 
	   		 	{
			        die($set_cache_stor_met . " caching method is not available");
			    }

			    $php_excel 			= $reader->load($path);

			    $worksheet_obj 		= $php_excel->getActiveSheet();

			    $parsed 			= $this->row_cell_parser( $worksheet_obj, $work_name, $parse, $get_blank, $custom, $format_val );

			    $parse['by_row']		= $parsed['by_row'];
			    $parse['by_first_row']	= (ISSET($parsed['by_first_row'])) ? $parsed['by_first_row'] : array();
			    $parse[$work_name] 		= $worksheet_obj;
			    $parse[$work_name.'_php_excel'] 	= $php_excel;
			    $parse['main_php_excel'] 			= $php_excel;

			    if( !$custom )
		 		{

				    unset($worksheet_obj);

			     	$php_excel->disconnectWorksheets();
	     			unset($php_excel);
	     		}
  			}

  			unset($chunk_filter);
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}

		return $parse;
	}

	public function row_cell_parser( PHPExcel_Worksheet $worksheet_obj, $work_name, array $parse, $get_blank = FALSE, $custom = FALSE, $format_val_ch = TRUE )
	{

		try
		{
		 	$headers 			= array();

		 	$parse[$work_name] 	= $worksheet_obj;

		 	if( !$custom )
		 	{

			  	foreach ($worksheet_obj->getRowIterator() as $row) 
			    {
			    	$cell_Iterator 	= $row->getCellIterator();
					$cell_Iterator->setIterateOnlyExistingCells(false);

					$row_index 		= $row->getRowIndex();  							

					if( $row_index == 1 )
					{
						$header_column = 0;
						foreach ($cell_Iterator as $cell) 
				 		{
				 			$headers[$work_name][$cell->getColumn()] = $cell->getValue();

				 			$header_column++;
				 		}
					}

					if( $row->isRowEmpty() )
					{
						continue;
					}

					foreach ($cell_Iterator as $cell) 
			 		{
			 			$value 		= $cell->getValue();

			 			$method 	= ( $format_val_ch ) ? 'getFormattedValue' : 'getValue';

			 			$format_val = $cell->{$method}();		
			 			$calc_val   = $cell->getCalculatedValue();	 			
			 			$old_calc_val = $cell->getOldCalculatedValue();	 			

						if( $row_index != 1 )
						{
							foreach( $headers as $work_name => $heads )
							{
								foreach( $heads as $column => $head )
								{
									if( $cell->getColumn() == $column )
									{
										if( !$get_blank )
										{
											if( !EMPTY( $value ) )
											{
												$parse['by_first_row'][$work_name][$head]['raw_value'][] 		= $value;
												$parse['by_first_row'][$work_name][$head]['formatted_value'][] 	= $format_val;
												$parse['by_first_row'][$work_name][$head]['calc_val'][] 	= $calc_val;
												$parse['by_first_row'][$work_name][$head]['old_calc_val'][] 	= $old_calc_val;
											}
										}
										else
										{
											$parse['by_first_row'][$work_name][$head]['raw_value'][] 		= $value;
											$parse['by_first_row'][$work_name][$head]['formatted_value'][] 	= $format_val;
											$parse['by_first_row'][$work_name][$head]['calc_val'][] 	= $calc_val;
											$parse['by_first_row'][$work_name][$head]['old_calc_val'][] 	= $old_calc_val;
										}
									}
								}
							}
						}

						if( !$get_blank )
						{
							if( !EMPTY( $value ) )
							{
								$parse['by_row'][$work_name][$row_index]['raw_value'][] 		= $value;
								$parse['by_row'][$work_name][$row_index]['formatted_value'][] 	= $format_val;
								$parse['by_row'][$work_name][$row_index]['calc_val'][] 			= $calc_val;
								$parse['by_row'][$work_name][$row_index]['old_calc_val'][] 			= $old_calc_val;
							}
						}
						else
						{
							$parse['by_row'][$work_name][$row_index]['raw_value'][] 		= $value;
							$parse['by_row'][$work_name][$row_index]['formatted_value'][] 	= $format_val;
							$parse['by_row'][$work_name][$row_index]['calc_val'][] 			= $calc_val;
							$parse['by_row'][$work_name][$row_index]['old_calc_val'][] 			= $old_calc_val;
						}
			 		}
			    }
			}
			else
			{
				$parse['by_first_row'] 	= array();
				$parse['by_row'] 		= array();
			}
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}

		return $parse;
	}

	public function get_data_tosend( array $get_row_obj_meth )
	{
		$post_data 			= array();

		try
		{
			$get_row_data 	= call_user_func_array($get_row_obj_meth, array( $this ) );

			if( !EMPTY( $get_row_data ) )
			{
				$post_data 	= $get_row_data;
			}
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}

		return $post_data;
	}

	public function send_data_tosocket( array $get_row_obj_meth )
	{
		try
		{
			$curl 		= curl_init();

			$post_data 	= $this->get_data_tosend( $get_row_obj_meth );

			if( !EMPTY( $post_data ) )
			{

				curl_setopt($curl, CURLOPT_URL,NODEJS_STATIC_SERVER.'uploader');
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

			
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				$server_output = curl_exec($curl);

				curl_close($curl);
				print_r($server_output);
			}
			// die;
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function set_main_model( $model )
	{
		try
		{
			$this->main_model 	= $model;
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch( Exception $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function custom_insert_parse_data( array $get_obj_method, array $obj_method )
	{
		try
		{
			$parsed				= call_user_func_array( $get_obj_method, array( $this ) );

			if( !EMPTY( $parsed['parsed'] ) )
			{
				call_user_func_array( $obj_method, array( $parsed['parsed'], $parsed['upload_files'], $this ) );
			}
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
	}

	public function process_values( array $parsed, $sheet, array $add_values = array() )
	{
		$val 			= array();
		$query_str 		= '';

		try
		{
			if( !EMPTY( $parsed['by_row'] ) )
			{
				if( ISSET( $parsed['by_row'][ $sheet ] ) )
				{
					foreach( $parsed['by_row'][ $sheet ] as $key => $values )
					{
						if( $key == 1 )
						{
							continue;
						}

						$query_str 	.= ' ( ';

						if( !EMPTY( $add_values ) )
						{
							$values['formatted_value'] 		= array_merge( $values['formatted_value'], $add_values );
						}

						$placeholder 	= rtrim(str_repeat("?,", count($values['formatted_value'])), ",");

						$query_str 	.= $placeholder;

						$query_str 	.= ' ), ';

						$val 		= array_merge( $val, $values['formatted_value'] );
					}					

					$query_str 	= rtrim( $query_str, ', ' );
				}
			}
		}
		catch( PDOException $e )
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}
		catch(Exception $e)
		{
			RLog::error($e->getLine() . ': ' . $e->getMessage(). ': '. $e->getTraceAsString());
			throw $e;
		}

		return array(
			'val' 			=> $val,
			'query_str'		=> $query_str
		);
	}

}