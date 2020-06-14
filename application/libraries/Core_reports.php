<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Core_reports
{
	const PDF_OUTPUT_INLINE		= 'I';
	const PDF_OUTPUT_DOWNLOAD 	= 'D';
	const PDF_OUTPUT_LOCAL_FILE	= 'F';
	const PDF_OUTPUT_STRING		= 'S';

	protected $CI;

	public $auto_print 			= NULL;

	protected $ms_file 			= array(
		'word' 		=> 'doc',
		'wordx'		=> 'docx',
		'excel' 	=> 'xls',
		'excelx' 	=> 'xlsx'
	);

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function pdf($filename, $html, $portrait = TRUE, $output = Core_reports::PDF_OUTPUT_STRING, $header = NULL, $footer = NULL, $margin_left = 10, $margin_right = 10, $margin_top = 10, $margin_bottom = 10, $margin_header=10, $margin_footer=10, array $add_page = array())
	{
		try
		{
			ini_set('memory_limit', '1024M');
	
			$this->CI->load->library('pdf');
		
			// Create PDF object
			$paper = (!$portrait) ? "A4-L" : "A4";
			
			$pdf = $this->CI->pdf->create("en-GB-x",$paper,"","",$margin_left,$margin_right,$margin_top,$margin_bottom,$margin_header,$margin_footer, ((!$portrait) ? '-L' : ''));
			
			if(ISSET($header) && ! EMPTY($header) OR ISSET($footer) && ! EMPTY($footer))
			{
				$pdf->SetHTMLHeader($header);
				$pdf->SetHTMLFooter($footer);	
			}
			
			if (is_array($html) AND ! EMPTY($html))
			{
				for ($i = 0; $i < count($html); $i++)
				{
					if ( ! EMPTY($html[$i]))
					{
						if ($i > 0)
						{
							$pdf->AddPage();						
						}
						$pdf->WriteHTML($html[$i]);
					}
				}
			}
			else
			{
				$pdf->WriteHTML($html);
			}

			if( ISSET( $this->auto_print ) )
			{
				$pdf->setJS('this.print()');
			}
			
			$pdf->debug = true;
			
			if($output == 'F')
			{
				$pdf->Output($filename, $output);
				return $filename;
			}
			else				
				return $pdf->Output($filename, $output);
		}
		catch(Exception $e)
		{
			throw $e;
		}
		
	}

	public function set_report_header($colspan = 0, $project_name = PROJECT_NAME)
	{
		try
		{
			if($colspan)
			{
				$header = '
				<table width="100%">
					<thead>
						<tr>
							<td colspan="'.$colspan.'"><font size="3"><b>'.$project_name.'</b></font></td>
						</tr>
					</thead>
				</table>';
			}
			else
			{
				$header = '<font size="3"><b>'.$project_name.'</b></font>';
			}

			return $header;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	public function convert_html_to_ms( $buffer, $file_name, $mode = 'excel', $sheet_name = 'Sheet 1' )
	{
	
		$data 			= '';
		$default_name 	= array(
				'word' 		=> 'WORD-',
				'wordx' 	=> 'WORDX-',
				'excel' 	=> 'EXCEL-',
				'excelx' 	=> 'EXCELX-'
		);
	
		$content_header = array(
				'word' 		=> 'application/word',
				'excel' 	=> 'application/excel',
				'wordx' 	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'excelx' 	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
		);
	
		$extension 				= ( ISSET( $this->ms_file[ $mode ] ) ) ? $this->ms_file[ $mode ] : $this->ms['excel'];
	
		if( EMPTY( $file_name ) )
		{
	
			$prepend_name 		= ( ISSET( $default_name[ $mode ] ) ) ? $default_name[ $mode ] : $default_name['excel'];
	
			$filename 			= $prepend_name.date('d-m-Y-hh-ii').'.'.$extension;
		}
		else
		{
			$filename 			= $file_name.'.'.$extension;
		}
	
		$header 				= ( ISSET( $content_header[ $mode ] ) ) ? $content_header[ $mode ] : $content_header['excel'];
	
		if( $mode == 'excel' OR $mode == 'excelx' )
		{
	
			$data 				   .= '<html xmlns:x="urn:schemas-microsoft-com:office:excel">
				<head>
					<meta http-equiv="Content-type" content="text/html;charset=utf-8" />
					 <xml>
				        <x:ExcelWorkbook>
				            <x:ExcelWorksheets>
				                <x:ExcelWorksheet>
				                    <x:Name>'.$sheet_name.'</x:Name>
				                    <x:WorksheetOptions>
				                        <x:Print>
				                            <x:ValidPrinterInfo/>
				                        </x:Print>
				                    </x:WorksheetOptions>
				                </x:ExcelWorksheet>
				            </x:ExcelWorksheets>
				        </x:ExcelWorkbook>
				    </xml>
				</head>
		
			<body>';
	
		}

		if( is_array( $buffer ) )
		{
			foreach( $buffer as $b_f )
			{
				$data .= $b_f;
			}
		}
		else
		{
			$data  	.= $buffer;
		}

		$data   .= '</body></html>';
	
		header( 'Expires: 0' );
		header( 'Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
	
		if( is_array( $header ) )
		{
			foreach ( $header as $head )
			{
				header( 'Content-type: '.$head );
			}
		}
		else
		{
			header( 'Content-type: '.$header );
		}
	
		header( 'Content-length: '.strlen( $data ) );
		header( 'Content-Disposition: attachment; filename='.$filename );
	
		return $data;
	
	}

	public function set_report_footer($project_name = PROJECT_NAME)
	{
		try	
		{
			$footer = '<hr>';
			$footer .= '<table width="100%">';
			$footer .= '<tr>';
			$footer .= '<td align="left"><font size="2"><b>Run Time : </b>'. date('m/d/Y g:i:s a') .'</font></td>';
			$footer .= '<td align="left"><font size="2"><b>Generated By : </b>'.$project_name.'</font></td>';
			$footer .= '<td align="right"><font size="2">Page {PAGENO} of {nb}<font size="2"></td>';
			$footer .= '</tr></table>';

			return $footer;
		}
		catch(Exception $e)
		{
			throw $e;
		}	
	}

	public function generate_report($view, $filename, $file_type, $report_name = NULL, $data = array(), $sheet_name = NULL, $colspan = 0, $portrait = TRUE, $report_type = REPORT_DEFAULT, $set_report_header = TRUE )
	{
		$html = "";
		if($set_report_header)
			$html.= $this->set_report_header($colspan, $report_name);
		
		$html.= $this->CI->load->view($view, $data, TRUE);
		$footer = $this->set_report_footer();
		$filename = $filename . '_' . date('m') . date('d') . date('Y');
		switch($file_type){
			case EXPORT_PDF:
				ob_end_clean();
				$filename = $filename . ".pdf";
				$this->pdf($filename, $html, $portrait, 'I', NULL, $footer);
			break;
			case EXPORT_EXCEL:
				$this->convert_excel($html, $filename, $sheet_name);
			break;
			case EXPORT_DOCUMENT:
				$this->convert_word($html, $filename);
			break;
		}
	}

	public function convert_excel( $buffer, $file_name, $sheet_name )
	{
		echo $this->convert_html_to_ms( $buffer, $file_name, 'excel', $sheet_name );
	}

	public function convert_word( $buffer, $file_name )
	{
		echo $this->convert_html_to_ms( $buffer, $file_name, 'word' );	
	}

	public function convert_excel_multi_sheets( array $buffers, $filename )
	{
		if( !EMPTY( $buffers ) )
		{
			$this->CI->load->library('Excel');

			$php_excel 				= new PHPExcel();
			$system_title 			= get_setting(GENERAL, "system_title");

			$php_excel->getProperties()
			  	->setCreator($system_title)
		   		->setTitle($filename);

			$reader 				= PHPExcel_IOFactory::createReader('HTML');

			$cnt 					= 0;

			$has_chart 				= FALSE;

			if( ISSET( $buffers['excel_chart'] ) ) 
			{
				$has_chart 			= TRUE;
			}

			$excel_objs 			= array();

			foreach( $buffers as $sheet_name => $buffer )
			{					
				if( $sheet_name != 'excel_chart' )
				{
					$var = 'obj_'.$sheet_name;

					$tmp_file 			=  tempnam(sys_get_temp_dir(), 'html');
					file_put_contents($tmp_file, $buffer['buffer']);
					
					$styles_callback 	= array();

					if( ISSET($buffer['styles_callback']) AND !EMPTY( $buffer['styles_callback'] ) )
					{
						$styles_callback = $buffer['styles_callback'];
					}

					if( $cnt == 0 )
					{
						$php_excel->setActiveSheetIndex(0);
						$php_excel 			= $reader->load($tmp_file);
						$php_excel->getActiveSheet()->setTitle($sheet_name);
						$this->style_php_excel($php_excel, $styles_callback, $buffer['buffer']);

						$excel_objs[$sheet_name]['excel']		= $php_excel;
						$excel_objs[$sheet_name]['worksheet']	= $php_excel->getActiveSheet();
					}
					else
					{
						$sub_excel_obj 		= new PHPExcel();
						$sub_excel_obj->setActiveSheetIndex(0);
				
						$sub_excel_obj 		= $reader->load($tmp_file);
						
						$sub_excel_obj->getActiveSheet()->setTitle($sheet_name);

						$excel_objs[$sheet_name]['excel']		= $sub_excel_obj;
						$excel_objs[$sheet_name]['worksheet']	= $sub_excel_obj->getActiveSheet();
						
						// 
						if( ISSET( $buffer['index'] ) )
						{
							$this->style_php_excel($sub_excel_obj, $styles_callback, $buffer['buffer']);
							$php_excel->addExternalSheet($sub_excel_obj->getActiveSheet(), $buffer['index']);
						}
						else
						{
							$this->style_php_excel($sub_excel_obj, $styles_callback, $buffer['buffer']);
							$php_excel->addExternalSheet($sub_excel_obj->getActiveSheet());
						}

						if( ISSET( $buffer['active'] ) AND !EMPTY( $buffer['active'] ) 
							AND ISSET( $buffer['index'] )
						)
						{
							$php_excel->setActiveSheetIndex($buffer['index']);
						}
					}

					unlink($tmp_file);
				}
				else
				{
					$args 			= array(
						$php_excel
					);	

					foreach( $buffer as $chart_name => $chart_callback )
					{
						$args[] 	= $reader;
						$args[] 	= $chart_name;
						$args[] 	= $excel_objs;

						if( ISSET( $chart_callback['args'] ) )
						{
							$args 	= array_merge( $args, $chart_callback['args'] );
						}

						if( ISSET( $chart_callback['obj'] ) )
						{
							$chart_cb_obj 	= $chart_callback['obj'];

							$chart_method 	= ( ISSET( $chart_callback['method'] ) ) ? $chart_callback['method'] : 'excel_chart';	

							call_user_func_array(array($chart_cb_obj,$chart_method), $args);
						}
					}
				}

				$cnt++;
			}

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // header for .xlxs file
			header('Content-Disposition: attachment;filename='.$filename); // specify the download file name
			header('Cache-Control: max-age=0');

            $writer 	= PHPExcel_IOFactory::createWriter($php_excel, 'Excel2007');

            if( $has_chart )
            {
            	$writer->setIncludeCharts(TRUE);
            }

            ob_end_clean();
            
            $writer->save('php://output');
		}
	}
	
	public function style_php_excel( PHPExcel $php_excel, array $styles_callback = array(), $buffer = NULL )
	{
		$highest_col 		= $php_excel->getActiveSheet()->getHighestColumn();
		$columns 			= range('A', $php_excel->getActiveSheet()->getHighestColumn());

		foreach ( $columns as $column_key ) 
		{
			$php_excel->getActiveSheet()->getColumnDimension($column_key)->setAutoSize(false);
			$php_excel->getActiveSheet()->getColumnDimension($column_key)->setWidth("30");
		}

		$highest_row = $php_excel->getActiveSheet()->getHighestRow();

		$rows 	= range(1, $highest_row);
		
		foreach( $rows as $row_key ) 
		{
		 	$php_excel->getActiveSheet()->getRowDimension($row_key)->setRowHeight("20");

		 	if( $row_key % 2 == 0 )   
		 	{
		 		$color 	= 'F6F6F6';
		 	}
		 	else
		 	{
		 		$color 	= 'FEFEFE';
		 	}

		 	$php_excel->getActiveSheet()
 			->getStyle('A'.$row_key.':'.$highest_col.$row_key)
		    ->getFill()
		    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
		    ->getStartColor()
		    ->setARGB($color);
	     	

            $php_excel->getActiveSheet()
            ->getStyle('A'.$row_key.':'.$highest_col.$row_key)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
            ->getColor()
            ->setARGB('DAE3E5');
		}

		if( !EMPTY( $styles_callback ) )
		{
			$args 		= array();
			$args[] 	= $buffer;
			$args[] 	= $php_excel;
			$args[] 	= $highest_col;
			$args[] 	= $rows;
			$args[] 	= $highest_row;

			if( ISSET( $styles_callback['args'] ) AND !EMPTY( $styles_callback['args'] ) )
			{
				$args 	= array_merge( $args, $styles_callback['args'] );
			}

			call_user_func_array(array( $styles_callback['obj'], $styles_callback['method'] ), $args);
		}
	}
}