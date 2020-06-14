<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import_template
{
	protected $CI;

	public $column_with_dropdown 	= array(
	);

	public $columns_list_reference 	= array();
	public $locations_reference 	= array();

	private $module;

	public $sheet_input 			= array(
		
	);

	public $sheet_columns 			= array();

	public $upper_columns 			= array(
		
	);

	public $required_table 			= array();

	public $location_region_sess;

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->library('Excel');

		$this->CI->load->model('Import_template_model', 'itm');
	}

	protected function add_special_name_range(array $data, array $ref_details, $column_key, $check_code, PHPExcel $php_excel, $sheet)
	{
		try
		{
			if( !EMPTY( $data ) )
			{
				if( ISSET( $ref_details[$column_key] ) )
				{
					$details_ref 	= $ref_details[$column_key];
					$first_col 		= $details_ref['col_row'];
					$last_col 		= $details_ref['end_row'];

					$prev_code 		= NULL;
					$real_arr 		= array();

					$real_row 		= 1;

					foreach( $data as $d )
					{
						if( ISSET( $d[$check_code] ) )
						{
							$real_arr[$d[$check_code]][$real_row] 	= 1;

							$real_row++;
						}
					}
					
					if( !EMPTY( $real_arr ) )
					{
						foreach( $real_arr as $code => $col )
						{
							$arr_keys 	= array_keys($col);

							$first 		= reset( $arr_keys ) - 1;
							$last 		= end( $arr_keys ) - 1;
							$cnt_l 		= count($col) - 1;

							$first_c 		= $first_col + $first ;
							$last_c 		= $first_c + $cnt_l;

							if( $last_c > $last_col )
							{
								$last_c 	= $last_col;
							}

							$real_code 		= preg_replace('/\s+/', '', $code);
							$real_code 		= preg_replace('/[\-]/', '', $real_code);
							$real_code 		= preg_replace('#\p{Pd}#u', '', $real_code);
							$real_code 		= preg_replace('/[\,]/', '', $real_code);
							
							$php_excel->addNamedRange(new PHPExcel_NamedRange('Alist'.$real_code, $php_excel->getSheetByName($sheet), 'C'.$first_c.':C'.$last_c));

						}
					}

					
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}

	public function set_sheet_protection_password( PHPExcel_Worksheet $sheet, $password = NULL )
	{
		try
		{
			$sheet->getProtection()->setSheet(true);
			$sheet->getProtection()->setSort(true);
			$sheet->getProtection()->setInsertRows(true);
			$sheet->getProtection()->setFormatCells(true);

			if( !EMPTY( $password ) )
			{
				$sheet->getProtection()->setPassword($password);
			}
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch( Exception $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
	}

	public function init_params()
	{
		try
		{
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch( Exception $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
	}

	public function init_reference( PHPExcel_Worksheet $surrenderer_references, array $change_names = array() )
	{
		try
		{
			
	    	
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch(Exception $e)
		{
			$this->rlog_error($e);
			throw $e;
		}
	}

	public function get_required( $table, $dont_include = array(), $schema = DB_CORE )
	{
		$get_required	 	= array();

		try
		{
			$def_dont 		= array(
				'created_by',
				'created_date',
				'modified_by',
				'modified_date'
			);

			if( !EMPTY( $dont_include ) )
			{
				$def_dont 	= $dont_include;
			}

			$get_required 	= $this->itm->get_columns_table_import(
				$table,
				$def_dont,
				$schema,
				array(),
				' AND IS_NULLABLE = ? ',
				array( 'NO' )
			);
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch( Exception $e )
		{
			$this->rlog_error($e);
			throw $e;
		}

		return $get_required;
	}

	public function set_data_validation( PHPExcel_Worksheet $sheet, $column, $col, $col_row, $str_col, $formula )
	{
		$objValidation 		= NULL;

		$data_map 			= array();
		$list 				= array();

		$obj_arr 			= array();

		try
		{			
			for( $j = 2; $j<= TEMPLATE_DR_NUM_DV; $j++  )
			{
				$objValidation 	= $sheet->getCell($str_col.$j)->getDataValidation();
				$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
				$objValidation->setAllowBlank(false);

				if( $column != 'surrenderer_id' OR $column != 'date_surrendered' )
				{
					$objValidation->setShowInputMessage(true);
					$objValidation->setShowErrorMessage(true);
					$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_STOP );
				}
				else
				{
					
				}

				if( $column == 'surrenderer_id' )
				{
					$sheet->getStyle( $str_col.$j )->getNumberFormat()->setFormatCode('00000');
					$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_WARNING );
					$objValidation->setShowErrorMessage(false);
				}

				if( $column == 'date_surrendered' )
				{
					$sheet->getStyle( $str_col.$j )->getNumberFormat()->setFormatCode('YYYY-mm-dd');
					$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_WARNING );
					$objValidation->setShowErrorMessage(false);
				}

				if( $column == 'assessment_result' )
				{
					$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_WARNING );
					$objValidation->setShowErrorMessage(false);
				}

				$objValidation->setShowDropDown(true);
				$objValidation->setErrorTitle('Invalid Value');
				$objValidation->setError('Please select a valid value');
				$objValidation->setPromptTitle('Pick from list');
				$objValidation->setPrompt('Please select a valid value');
				
				if(!EMPTY($formula))
				{
					/*$prov_col 		= 'Q';
					$city_col 		= 'R';
					$sub_mun_col 	= 'S';
					$brgy_col 		= 'T';

					if( $sheet->getTitle() == PARTICIPANT_SHEET )
					{
						$prov_col 		= 'E';
						$city_col 		= 'F';
						$sub_mun_col 	= 'G';
						$brgy_col 		= 'H';
					}*/

					if( $formula == TEMPLATE_PROVINCE_SHEET )
					{

						$real_formula 	= 'INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE('.$prov_col.$j.'," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–",""))';

						$objValidation->setFormula1($real_formula);
					}
					else if( $formula == TEMPLATE_MUNI_CITY_SHEET )
					{
						$real_formula 	= 'INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE('.$city_col.$j.'," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–",""))';

						$objValidation->setFormula1($real_formula);
					}
					else if( $formula == TEMPLATE_SUB_MUNICIPALTIES_SHEET )
					{
						$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_WARNING );
						$objValidation->setShowErrorMessage(false);

						$real_formula 	= 'INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(CONCATENATE('.$sub_mun_col.$j.',"List")," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–",""))';

						$objValidation->setFormula1($real_formula);
					}
					else if( $formula == TEMPLATE_BARANGGAYS_SHEET )
					{
						$real_formula 	= 'IF(OR( ISBLANK('.$brgy_col.$j.'), '.$brgy_col.$j.' = "#REF!" ), INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE('.$sub_mun_col.$j.'," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–","")), INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE('.$brgy_col.$j.'," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–","")) )';

						$objValidation->setFormula1($real_formula);
					}
					else if( $column == 'advocate_id' )
					{

						// INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE('.SURRENDERER_SHEET.'!AI," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–",""),"'.SURRENDERER_TEMPLATE_DR_MARK.'",""))
						// IF( OR( ISBLANK('.SURRENDERER_SHEET.'!AI'.$j.'), ISBLANK(A'.$j.') ), '.$formula.', INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(INDIRECT(ADDRESS(MATCH(A'.$j.','.SURRENDERER_SHEET.'!A2:A'.TEMPLATE_DR_NUM_DV.',0)+ROW('.SURRENDERER_SHEET.'!A2:A'.TEMPLATE_DR_NUM_DV.')-1,35,1,1,"'.SURRENDERER_SHEET.'"))," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–",""),"|+","")) )
						/*$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_WARNING );
						$objValidation->setShowErrorMessage(false);
						
						$real_formula 	= 'IF( ISBLANK(A'.$j.'), '.$formula.', INDIRECT(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(INDIRECT(ADDRESS(MATCH(A'.$j.','.SURRENDERER_SHEET.'!A2:A'.TEMPLATE_DR_NUM_DV.',0)+ROW('.SURRENDERER_SHEET.'!A2:A'.TEMPLATE_DR_NUM_DV.')-1,36,1,1,"'.SURRENDERER_SHEET.'"))," ",""),"(",""),")",""),".",""),"/",""),"\",""),"-",""),",",""),"&",""),"–",""),"|+","")) )';
						
						$objValidation->setFormula1($real_formula);*/
					}
					else
					{
						// $objValidation->setFormula1('"'.implode(',',$list).'"');
						$objValidation->setFormula1($formula);
					}
				}
				
			}
			/*print_r($sheet->getCell($str_col.'2'));
			// $objValidation 	= $sheet->getDataValidation();
			$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
			$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
			$objValidation->setAllowBlank(false);
			$objValidation->setShowInputMessage(true);
			$objValidation->setShowErrorMessage(true);
			$objValidation->setShowDropDown(true);
			$objValidation->setErrorTitle('Invalid Value');
			$objValidation->setError('Please select a valid value');
			$objValidation->setPromptTitle('Pick from list');
			$objValidation->setPrompt('Please select a valid value');
			
			if(!EMPTY($formula))
			{
				// $objValidation->setFormula1('"'.implode(',',$list).'"');
				$objValidation->setFormula1($formula);
			}

			$obj_arr[$column] 	= $objValidation;

			$sheet->setDataValidation($str_col.'2:'.$str_col.TEMPLATE_DR_NUM_DV, $objValidation);*/

			// $objValidation = $sheet;
			// else
			// 	$objValidation->setFormula1($list);
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch( Exception $e )
		{
			$this->rlog_error($e);
			throw $e;
		}

		return $objValidation;
	}

	public function set_data_upper_validation( PHPExcel_Worksheet $sheet, $column, $col, $col_row, $str_col )
	{
		$objValidation 		= NULL;

		$data_map 			= array();
		$list 				= array();

		$obj_arr 			= array();

		try
		{
			for( $j = 2; $j<= TEMPLATE_DR_NUM_DV; $j++  )
			{
				$objValidation 	= $sheet->getCell($str_col.$j)->getDataValidation();
				$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_CUSTOM );
				$objValidation->setAllowBlank(true);
				$objValidation->setShowInputMessage(true);
				$objValidation->setShowErrorMessage(true);

				$objValidation->setErrorTitle('Upper case value');
				$objValidation->setError('Please make sure the value is uppercase');
				$objValidation->setPromptTitle('Please make sure the value is uppercase');
				$objValidation->setPrompt('Please make sure the value is uppercase');
				$objValidation->setFormula1('AND(EXACT('.$str_col.$j.',UPPER('.$str_col.$j.')),ISTEXT('.$str_col.$j.'))');
			}
		}
		catch( PDOException $e )
		{
			$this->rlog_error($e);
			throw $e;
		}
		catch( Exception $e )
		{
			$this->rlog_error($e);
			throw $e;
		}

		return $objValidation;
	}

	public function write_to_worksheet( PHPExcel $excel, $title, array $data, $first = FALSE, array $add_prev = array(), array $add_next = array(), $no_temp = FALSE, $add_req_fields = array(), array $change_names = array(), $schema = DB_CORE, array $value_cell_arr = array() )
	{

		$required_fields 	= array();

		try
		{
			$model_obj 		= $this->CI->itm;


			$def_add_prev 	= array(
				// OPERATION_COLUMN,
				'surrenderer_id_temp'
			);

			if( $no_temp )
			{
				$def_add_prev 	= array();
			}

			if( ISSET( $this->required_table[ $title ] ) ) 
			{
				$required_fields = $this->get_required( $this->required_table[ $title ], array(), $schema );
				
			}

			if( !EMPTY( $add_req_fields ) )
			{
				$required_fields 		= array_merge( $required_fields, $add_req_fields );
			}

			$def_add_next 	= array();

			if( !EMPTY( $add_prev ) )
			{
				$def_add_prev 	= $add_prev;
			}

			if( !EMPTY( $add_next ) )
			{
				$def_add_next 	= $add_next;
			}


			if( $first == TRUE )
			{
				$worksheet = $excel->getSheet(0);
			}
			else
			{
				$worksheet = new \PHPExcel_Worksheet($excel, $title);
				$excel->addSheet($worksheet);
				
			}			

			$worksheet->setTitle($title);

			$real_arr 	= $this->fix_array( $data, $def_add_prev, $def_add_next );
			
			$this->write( $worksheet, $real_arr, NULL, $required_fields, $change_names, $value_cell_arr );
			
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}

		return $excel;
	}

	public function write( $worksheet, array $real_arr, $append_index = NULL, array $required_fields = array(), $change_names = array(), array $value_cell_arr = array() )
	{
		$ret_ind 		= 0;

		try
		{

			if( !EMPTY( $real_arr ) )
			{
				$col 	= 0;
				$col_row= ( !EMPTY( $append_index ) ) ? $append_index : 1;

				$ename 	= NULL;
				
				foreach( $real_arr as $column => $values )
				{
					if( $column == 'mname' )
					{
						$column 	= 'middle_name';
					}

					if( $column == 'address' )
					{
						$column 	= 'house_no,street,sitio,purok';
					}

					$work_obj_main 	= $worksheet->setCellValueByColumnAndRow( $col, $col_row , $column );

					$match 			= preg_match( '/date/i', $column );

					$all_str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);

					$worksheet->getStyle( $all_str_col )
						->getAlignment()
    					->setWrapText(true);

					if( ( !EMPTY( $required_fields ) AND in_array( $column, $required_fields ) 
						AND $column != 'surrenderer_id' ) OR $column == OPERATION_COLUMN
						OR $column == 'house_no,street,sitio,purok'
					)
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$worksheet->getStyle( $str_col.'1' )->applyFromArray(
				        array(
				            'fill' => array(
				                'type' => PHPExcel_Style_Fill::FILL_SOLID,
				                'color' => array('rgb' => '990000')
				            ),
				            'font'  => array(
				            	'color' => array('rgb' => 'FFFFFF'),
				            )
				        ) );
					}


					if( in_array( $worksheet->getTitle(), $this->sheet_input, TRUE ) )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$this->sheet_columns[ $worksheet->getTitle() ][] = $str_col;
						
					}

					if( $worksheet->getTitle() == USERS_SHEET )
					{
						if( $column == 'lname' )
						{
							$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
							$this->set_header_comment($str_col, $worksheet, 'Last Name');
							$this->set_field_comment($str_col, $worksheet, 'Last Name');
						}
						else if( $column == 'fname' )
						{
							$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
							$this->set_header_comment($str_col, $worksheet, 'First Name');
							$this->set_field_comment($str_col, $worksheet, 'First Name');
						}
						else if( $column == 'middle_name' )
						{
							$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
							$this->set_header_comment($str_col, $worksheet, 'Middle Initial');
							$this->set_field_comment($str_col, $worksheet, 'Middle Initial');

							$column 	= 'mname';
						}
						else if( $column == 'contact_no' )
						{
							$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
							$this->set_header_comment($str_col, $worksheet, 'Telephone No.');
							$this->set_field_comment($str_col, $worksheet, 'Telephone No.');
						}
						else if( $column == 'role_code' )
						{
							$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
							$this->set_header_comment($str_col, $worksheet, 'Main User Role.');
							$this->set_field_comment($str_col, $worksheet, 'Main User Roles.');
						}
						else if( $column == 'org_code' )
						{
							$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
							$this->set_header_comment($str_col, $worksheet, 'Main User Organization.');
							$this->set_field_comment($str_col, $worksheet, 'Main User Organization.');
						}
					}

					if( $column == 'monthly_income' )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$this->set_header_comment($str_col, $worksheet, 'Estimated Monthly Family Income');
						$this->set_field_comment($str_col, $worksheet, 'Estimated Monthly Family Income');

					}
					else if( $column == 'other_drug' )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$this->set_header_comment($str_col, $worksheet, 'Other drug used not in selection.');
						$this->set_field_comment($str_col, $worksheet, 'Please specify other drug if drugs_id is others.');						
					}
					else if( $column == 'drugs_id' )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$this->set_header_comment($str_col, $worksheet, 'Drug First Tried');
						$this->set_field_comment($str_col, $worksheet, 'Drug First Tried');						
					}
					else if( $column == 'drug_id' )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$this->set_header_comment($str_col, $worksheet, 'Type of Drug Used');
						$this->set_field_comment($str_col, $worksheet, 'Type of Drug Used');						
					}
					else if( $column == 'phone' )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$this->set_header_comment($str_col, $worksheet, 'Telephone No.');
						$this->set_field_comment($str_col, $worksheet, 'Telephone No.');						
					}

					// 
					if( $worksheet->getTitle() == REFERENCE_SHEET AND in_array( $column, $this->column_with_dropdown ) )
					{	

						$str_col 		= PHPExcel_Cell::stringFromColumnIndex($col);
						$all_val 		= count($values);

						if( $column == 'operation_code' )
						{
							$real_column 	= OPERATION_COLUMN;
						}
						else if( $column == 'risk_level_code' )
						{
							$real_column 	= 'risk_level';
						}
						else if( $column == 'assesment_result_code' )
						{
							$real_column 	= 'assessment_result';	
						}
						else if( $column == 'drug_source_id' )
						{
							$real_column 	= 'drugs_source_id';
						}
						else
						{
							$real_column 	= $column;
						}

						if( $real_column != 'nationality_id' )
						{
							$real_col_row 	= $col_row + 2;
						}
						else
						{
							$real_col_row 	= $col_row + 1;
						}

						$end_row 		= ( $real_col_row + $all_val ) - 1;

						// $this->columns_list_reference[ $col_drop ] 	= array();
						
						if( $real_column == 'drugs_id' )
						{
							$dr_real_col_row 	= $real_col_row + 1;
							$this->add_to_column_ref_list('drug_id', $dr_real_col_row, $end_row);
						}
						else if( $real_column == 'extension_code' )
						{
							$this->add_to_column_ref_list('mname', $real_col_row, $end_row);
							$this->add_to_column_ref_list('father_mname', $real_col_row, $end_row);
							$this->add_to_column_ref_list('mother_mname', $real_col_row, $end_row);
							$this->add_to_column_ref_list('spouse_mname', $real_col_row, $end_row);
						}
						else if( $real_column == 'active_flag_code' )
						{
							$this->add_to_column_ref_list('new_admission_flag', $real_col_row, $end_row);	
							$this->add_to_column_ref_list('primary_drug_flag', $real_col_row, $end_row);	
							$this->add_to_column_ref_list('new_admission_flag', $real_col_row, $end_row);	
							$this->add_to_column_ref_list('complete_flag', $real_col_row, $end_row);	
						}

						if( ISSET( $change_names[$real_column] ) )
						{
							$real_column 	= $change_names[$real_column];
						}
						
						$this->add_to_column_ref_list($real_column, $real_col_row, $end_row);
					}
					else
					{
						if( ISSET( $this->columns_list_reference[ $column ] ) ) 
						{
							$list_detail 	= $this->columns_list_reference[ $column ];
							
							$formula 		= REFERENCE_SHEET.'!$C$'.$list_detail['col_row'].':$C$'.$list_detail['end_row'].'';
							
							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
							
						}
						else if( $column == 'region'
						)
						{
							//ISSET( $this->locations_reference[ SURRENDERER_TEMPLATE_REGION_SHEET ] ) 
							//AND
							if( !EMPTY( $this->location_region_sess ) )
							{
								$formula 		= '=LOCLIST'.$this->location_region_sess;
							}
							else
							{
								//$list_detail 	= $this->locations_reference[ SURRENDERER_TEMPLATE_REGION_SHEET ];

								$formula 		= '=REGION_LIST';
							}

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}
						else if( $column == 'province' )
						{
							$formula 		= TEMPLATE_PROVINCE_SHEET;

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}
						else if( $column == 'city_muni' )
						{
							$formula 		= TEMPLATE_MUNI_CITY_SHEET;

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}
						else if( $column == 'submunicipality' )
						{
							$formula 		= TEMPLATE_SUB_MUNICIPALTIES_SHEET;

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}
						else if( $column == 'barangay' )
						{
							$formula 		= TEMPLATE_BARANGGAYS_SHEET;

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}
						/*else if(  $worksheet->getTitle() == ORGANIZATION_SHEET AND ( $column == 'org_parent' )
							
						)
						{
							$formula 		= '=ORG_CODE_LIST';

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}*/
						/*else if( $column == 'surrenderer_id_temp' 
							AND $worksheet->getTitle() != SURRENDERER_SHEET
						)
						{
							// $formula 		= 'SURRENDERER_ID_TEMP_LIST';
							$formula 		= 'OFFSET('.SURRENDERER_SHEET.'!$A$2,0,0,COUNTA('.SURRENDERER_SHEET.'!$A:$A),1)';

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}
						else if( $column == 'surrenderer_id' 
							AND $worksheet->getTitle() != ASSESSMENT_SHEET
						)
						{
							$formula 		= '=SURRENDERER_ID_LIST';

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}
						else if( $column == 'date_surrendered' 
							AND $worksheet->getTitle() != ASSESSMENT_SHEET
						)
						{
							// $formula 		= '=DATE_SURRENDERED_LIST';
							$formula 			= 'OFFSET('.ASSESSMENT_SHEET.'!$B$2,0,0,COUNTA('.ASSESSMENT_SHEET.'!$B:$B),1)';

							$this->set_column_validation( $worksheet, $column, $col, $col_row, $formula );
						}*/
					}

					$upper_col 	= $column;

					/*if( ( $worksheet->getTitle() == SURRENDERER_SHEET AND $upper_col == 'ename' )
						OR ( $worksheet->getTitle() == PARTICIPANT_SHEET AND $upper_col == 'ename' ) 
						OR ( $worksheet->getTitle() == ADVOCATE_SHEET AND $upper_col == 'ename' )  )
					{
						$upper_col = 'ename_a';
					}*/

					if( in_array( $upper_col, $this->upper_columns ) )
					{
						$str_col 			= PHPExcel_Cell::stringFromColumnIndex($col);
						$this->set_data_upper_validation( $worksheet, $column, $col, $col_row, $str_col );
					}
					
					if( !EMPTY( $match ) )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);

						$worksheet->getStyle( $str_col )->getNumberFormat()->setFormatCode('YYYY-mm-dd');

						$this->set_field_comment($str_col, $worksheet, 'Date format must be YYYY-mm-dd.');

						
					}
					else if( $column == 'monthly_income' )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						
						$worksheet->getStyle( $str_col )->getNumberFormat()->setFormatCode('#,##0.00');
					}
					else if( $column == 'surrenderer_id' )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$worksheet->getStyle( $str_col )->getNumberFormat()->setFormatCode('00000');
					}
					else if( $column == 'advocate_id'  )
					{
						$str_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
						$worksheet->getStyle( $str_col )->getNumberFormat()->setFormatCode('0000000000');	
					}

					$work_obj_main->getColumnDimensionByColumn($col)->setWidth('35');

					$ind 	= ( !EMPTY( $append_index ) ) ? $append_index : 0;

					foreach( $values as $index => $val )
					{

						$worksheet->setCellValueByColumnAndRow( $col, ( $ind + 2 ), $val );

						$ind++;

						$ret_ind = $ind;
						
					}

					if( !EMPTY( $value_cell_arr ) )
					{
						$val_arr = array_column($value_cell_arr, $column);

						if( !EMPTY( $val_arr ) )   
						{
							$str_a_col 	= PHPExcel_Cell::stringFromColumnIndex($col);
							foreach( $val_arr as $k_ar => $v )
							{
								$work_obj_main->setCellValue( $str_a_col.( $k_ar + 2 ), $v );
							}
						}
					}

					$col++;
					
				}
				
			}

		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}

		return $ret_ind + 3;
	}

	protected function set_field_comment($str_col, $worksheet, $comment)
	{
		for( $j = 2; $j<= TEMPLATE_DR_NUM_DV; $j++  )
		{
			$worksheet->getComment($str_col.$j)->getText()->createTextRun($comment);
		}
	}

	protected function set_header_comment($str_col, $worksheet, $comment)
	{
		$worksheet->getComment($str_col.'1')->getText()->createTextRun($comment);
	}

	public function set_column_validation( $worksheet, $column, $col, $col_row, $formula )
	{
		$str_col 		= PHPExcel_Cell::stringFromColumnIndex($col);
		
		$this->set_data_validation( $worksheet, $column, $col, $col_row, $str_col, $formula );
	}

	public function add_to_column_ref_list($real_column, $real_col_row, $end_row)
	{
		if( !ISSET( $this->columns_list_reference[$real_column]['col_row'] ) )
		{
			$this->columns_list_reference[ $real_column ]['col_row'] 	= $real_col_row;
		}

		if( !ISSET( $this->columns_list_reference[$real_column]['end_row'] ) )
		{
			$this->columns_list_reference[ $real_column ]['end_row'] 	= $end_row;
		}
	}

	public function write_append( PHPExcel_Worksheet $worksheet, array $data, $append_index = NULL, array $change_names = array() )
	{
		try
		{
			
			$real_arr 		= $this->fix_array( $data );

			$append_index 	= $this->write( $worksheet, $real_arr, $append_index, array(), $change_names );
		}
		catch(PDOException $e)
		{
			
			$this->rlog_error($e);
			throw $e;
			
		}
		catch(Exception $e)
		{
			
			$this->rlog_error($e);
			
			throw $e;
		}

		return $append_index;
	}

	public function fix_array( array $data, array $add_prev = array(), array $add_next = array() )
	{
		$arr 		= array();

		try
		{
			if( !EMPTY( $add_prev ) )
			{
				foreach( $add_prev as $index => $col )
				{
					$arr[ $col ][] = '';
				}
			}

			foreach( $data as $index => $values )
			{
				if( is_array( $values ) )
				{
					foreach( $values as $columns => $val )
					{
						$arr[$columns][] 	= $val;
					}
				}
				else
				{
					$arr[$values][] 		= '';
				}
			}

			if( !EMPTY( $add_next ) )
			{
				foreach( $add_next as $index => $col )
				{
					$arr[ $col ][] = '';
				}
			}
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}

		return $arr;
	}

	public function get_active_flag($code_name = 'active_flag_code', $code_display_name = 'active_flag_name', $add_blank = FALSE)
	{
		if( $add_blank ) 
		{
			$active_flag 	= array(
				array(
					$code_name 		=> '',
					$code_display_name 		=> '',
					TEMPLATE_DR_NAME  		=> ''
				),
				array(
					$code_name 		=> ENUM_YES,
					$code_display_name 		=> 'Yes',
					TEMPLATE_DR_NAME  		=> ENUM_YES.TEMPLATE_DR_MARK.'Yes'
				),
				array(
					$code_name 		=> ENUM_NO,
					$code_display_name 		=> 'NO',
					TEMPLATE_DR_NAME  		=> ENUM_NO.TEMPLATE_DR_MARK.'NO'
				)
			);
		}
		else
		{
			$active_flag 	= array(
				array(
					$code_name 		=> ENUM_YES,
					$code_display_name 		=> 'Yes',
					TEMPLATE_DR_NAME  		=> ENUM_YES.TEMPLATE_DR_MARK.'Yes'
				),
				array(
					$code_name 		=> ENUM_NO,
					$code_display_name 		=> 'NO',
					TEMPLATE_DR_NAME  		=> ENUM_NO.TEMPLATE_DR_MARK.'NO'
				)
			);
		}

		return $active_flag;
	}

	public function get_genders($code_name = 'gender', $code_display_name = 'gender_name')
	{
		$result 			= array();

		try
		{
			$male 			= get_sys_param_code('GENDER', MALE);
			$female 		= get_sys_param_code('GENDER', FEMALE);

			$result 		= array(
				array(
					$code_name	=> $male['sys_param_code'],
					$code_display_name		=> $male['sys_param_name'],
					TEMPLATE_DR_NAME  		=> $male['sys_param_code'].TEMPLATE_DR_MARK.$male['sys_param_name']
				),
				array(
					$code_name	=> $female['sys_param_code'],
					$code_display_name		=> $female['sys_param_name'],
					TEMPLATE_DR_NAME  		=> $female['sys_param_code'].TEMPLATE_DR_MARK.$female['sys_param_name']
				)
			);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}


	public function get_user_status($code_name = 'status', $code_display_name = 'status_namw')
	{
		$result 			= array();

		try
		{
			$active 			= get_sys_param_code('STATUS', ACTIVE);
			$inactive 			= get_sys_param_code('STATUS', INACTIVE);
			
			$result 		= array(
				array(
					$code_name	=> $active['sys_param_code'],
					$code_display_name		=> $active['sys_param_name'],
					TEMPLATE_DR_NAME  		=> $active['sys_param_code'].TEMPLATE_DR_MARK.$active['sys_param_name']
				),
				array(
					$code_name	=> $inactive['sys_param_code'],
					$code_display_name		=> $inactive['sys_param_name'],
					TEMPLATE_DR_NAME  		=> $inactive['sys_param_code'].TEMPLATE_DR_MARK.$inactive['sys_param_name']
				)
			);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}
}