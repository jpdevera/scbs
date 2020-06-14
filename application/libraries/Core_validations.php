<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/AJD_validation/AJD_ciServiceProvider.php';

use AJD_validation\Contracts\Base_extension;

class Core_validations extends Base_extension
{
	protected $CI;
	public static $validation_library;

	public function __construct()
	{
		$this->CI =& get_instance();

		// $this->CI->load->library('AJD_validation/AJD_ciServiceProvider');

		if( EMPTY( static::$validation_library ) )
		{
			static::$validation_library 	= new AJD_ciServiceProvider;
		}
	}

	public function getName()
	{
		return 'Core_validations';
	}

	public function check_required_fields($params, $fields)
	{
		try 
		{
			$str 				= '';
			$group_count 		= array();
			$group_container 	= array();
			$concat_str			= '';
			
			foreach($fields AS $key => $val)
			{
				$name 			= (is_array($val)) ? $val[0] : $val;
				$group 			= (is_array($val) && !EMPTY($val[1])) ? $val[1] : NULL;
				
				if(is_array($params[$key]))
				{
					foreach($params[$key] AS $k => $v)
					{
						$k 		= (is_numeric($k)) ? $k+1 : $k;
						if(!EMPTY($group))
						{
							$concat_str[$group][$k]	.= EMPTY($v) ? '<b>' . $name . '</b> in row ' . $k . ' is required.<br>' : '';
							$group_container[$group][$k][] = EMPTY($v) ?  NULL : $v;
							$group_count[$group][$k] = !EMPTY($group_count[$group][$k]) ? $group_count[$group][$k] : 0;
							$group_count[$group][$k] = $group_count[$group][$k] + 1;
						}
						$filtered_group	= (!EMPTY($group) && !EMPTY($group_container[$group][$k])) ? count(array_filter($group_container[$group][$k])) : NULL;
						if((EMPTY($v) && EMPTY($group)))
						{
							$str .= '<b>' . $name . '</b> in row ' . $k . ' is required.<br>';
						}
						elseif(!EMPTY($filtered_group) && $group_count[$group][$k] != $filtered_group)
						{
							$str .= $concat_str[$group][$k];
							unset($concat_str[$group][$k]);
						}
						
						// IF STILL AN ARRAY ASSUME $k AS TABLE NAME IN CLIENT SIDE
						if(is_array($v))
						{
							foreach($v AS $key2 => $val2)
							{
								$key2 = (is_numeric($key2)) ? $key2+1 : $key2;
								if(!EMPTY($group))
								{
									$concat_str[$group][$k][$key2] .= EMPTY($val2) ? ' <b>' . $name . '</b> in ' . $k . ' table, row ' . $key2 . ' is required.<br>' : '';
									$group_container[$group][$k][$key2][] = EMPTY($val2) ?  NULL : $val2;
									$group_count[$group][$k][$key2]	= !EMPTY($group_count[$group][$k]) ? $group_count[$group][$key2] : 0;
									$group_count[$group][$k][$key2]	= $group_count[$group][$k][$key2] + 1;
								}
								
								$filtered_group	= (!EMPTY($group) && !EMPTY($group_container[$group][$k][$key2])) ? 
								count(array_filter($group_container[$group][$k][$key2])) : NULL;
								
								if(EMPTY($val2) && EMPTY($group))
								{
									$str .= ' <b>' . $name . '</b> in ' . $k . ' table, row ' . $key2 . ' is required.<br>';
								}
								elseif(!EMPTY($filtered_group) && $group_count[$group] != $filtered_group)
								{
									$str .= $concat_str[$group][$k][$key2];
									unset($concat_str[$group][$k][$key2]);
								}
							}
						}
					}
				}
				else
				{
					if(!EMPTY($group))
					{
						$concat_str[$group]	.= EMPTY($v) ? '<b>' . $name . '</b> is required.<br>' : '';
						$group_container[$group][] = EMPTY($params[$key]) ?  NULL : $params[$key];
						$group_count[$group] = !EMPTY($group_count[$group]) ? $group_count[$group] : 0;
						$group_count[$group] = $group_count[$group] + 1;
					}
					
					$filtered_group	= !EMPTY($group_container[$group]) ? count(array_filter($group_container[$group])) : NULL;

					if(EMPTY($params[$key]) && EMPTY($group))
					{
						$str .= '<b>' . $name . '</b> is required.<br>';
					}
					elseif(!EMPTY($filtered_group) && $group_count[$group] != $filtered_group)
					{
						$str .= $concat_str[$group];
						unset($concat_str[$group]);
					}
				}
			}
			if(!EMPTY($str))
			{
				throw new Exception($str);
			}
				
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
			$valid_inputs = array();
			
			FOREACH($arr_inputs AS $key => $value)
			{
				$field_validation = !EMPTY($arr_validations[$key]) ? $arr_validations[$key] : '';
				
				if(!ISSET($field_validation) OR EMPTY($field_validation))
				{
					continue;
				}
				
				IF(!ISSET($field_validation['data_type']) OR EMPTY($field_validation['data_type']))
				{
					throw new Exception($this->CI->lang->line('err_invalid_data'));
				}
				
				if(EMPTY($field_validation['name']))
				{
					throw new Exception('Validation incomplete for ' . $key . ' parameter name missing');
				}
				
				SWITCH (strtolower($field_validation['data_type']))
				{
					CASE 'string':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_string($v, $field_validation);
							}
						}
						else 
						{
							$valid_inputs[$key] = $this->_validate_string($value, $field_validation);
						}
						break;
						
					CASE 'password':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_string($v, $field_validation, TRUE);
							}
						}
						else 
						{
							$valid_inputs[$key] = $this->_validate_string($value, $field_validation, TRUE);
						}
						break;
						
					CASE 'digit':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_digits($v, $field_validation);
							}
						}
						else 
						{
							$valid_inputs[$key] = $this->_validate_digits($value, $field_validation);
						}
						break;
						
					CASE 'amount':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_amount($v, $field_validation);
							}
						}
						else 
						{
							$valid_inputs[$key] = $this->_validate_amount($value, $field_validation);
						}
						break;
						
					CASE 'date':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_date($v, $field_validation);
							}
						}
						else 
						{
							$valid_inputs[$key] = $this->_validate_date($value, $field_validation);
						}
						break;
						
					CASE 'enum':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_enum($v, $field_validation);
							}
						}
						else 
						{
							$valid_inputs[$key] = $this->_validate_enum($value, $field_validation);
						}
						break;
						
					CASE 'time':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_time($v, $field_validation);
							}
						}
						else 
						{
							$valid_inputs[$key] = $this->_validate_time($value, $field_validation);
						}
						break;
						
					CASE 'email':
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_email($v, $field_validation);
							}
						}
						else
						{
							$valid_inputs[$key] = $this->_validate_email($value, $field_validation);
						}
						break;

					CASE 'url' :

						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_url($v, $field_validation);
							}
						}
						else
						{
							$valid_inputs[$key] = $this->_validate_url($value, $field_validation);
						}

					break;

					case 'db_value' :

						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_db_value($v, $field_validation);
							}
						}
						else
						{
							$valid_inputs[$key] = $this->_validate_db_value($value, $field_validation);
						}

						break;

					case 'year' :
						if(is_array($value))
						{
							foreach ($value AS $k=>$v)
							{
								$valid_inputs[$key][$k] = $this->_validate_year($v, $field_validation);
							}
						}
						else
						{
							$valid_inputs[$key] = $this->_validate_year($value, $field_validation);
						}
						
						break;
						
					DEFAULT:
						$valid_inputs[$key] = $value;
				}
			}
			
			return $valid_inputs;
		}
		catch(Exception $e)
		{
			throw $e;
		}

	}
	
	private function _validate_email($input, $validation)
	{
		try
		{
			if(ISSET($input) && ! filter_var($input, FILTER_VALIDATE_EMAIL))
			{
				throw new Exception('Please enter a valid email address in ' . $validation['name']);
			}
			
			return $input;
		}
		catch (Exception $e)
		{
			throw $e;
		}
		
	}

	private function _validate_url( $input, $validation )
	{
		try
		{
			if( ISSET( $input ) AND !filter_var(  $input, FILTER_VALIDATE_URL ) )
			{
				throw new Exception('Please enter a valid website/url in ' . $validation['name']);
			}
		}
		catch( Exception $e )
		{
			throw $e;
		}
	}
	
	private function _validate_string($input, $validation, $password_flag = FALSE)
	{
		try
		{
			if(EMPTY($password_flag))
			{
				$input = trim($input);
			}
			if(ISSET($validation['min_len']) && strlen($input) < $validation['min_len'])
			{
				throw new Exception($this->CI->lang->line('err_min_len') . $validation['min_len'] . ' character/s for ' . $validation['name']);
			}
			if(ISSET($validation['max_len']) && strlen($input) > $validation['max_len'])
			{
				throw new Exception($this->CI->lang->line('err_max_len') . $validation['max_len'] . ' character/s for ' . $validation['name']);
			}
			if(ISSET($validation['accepted_chars']))
			{
				if(!preg_match($validation['accepted_chars'], $input))
				{
					throw new Exception($this->CI->lang->line('err_invalid_data') . ' for ' . $validation['name']);
				}
			}
			
			return filter_var($input, FILTER_SANITIZE_STRIPPED);
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	private function _validate_digits($input, $validation)
	{
		if(EMPTY($input))
		{
			return $input;
		}
	
		try
		{
			$input = str_replace(',','', $input);
			if(!is_numeric($input))
			{
				throw new Exception('Please enter a valid number in ' . $validation['name'] . '.');
			}
	
			if(ISSET($validation['min']))
			{
				if(!is_numeric($input))
				{
					throw new Exception("Minimum amount is not a valid number in " . $validation['name'] . '.');
				}
					
				if($input < $validation['min'])
				{
					throw new Exception('Please enter a value higher than ' . number_format($validation['min'], 2) . ' in ' . $validation['name'] . '.');
				}
			}
	
			if(ISSET($validation['max']))
			{
				if(!is_numeric($input))
				{
					throw new Exception("Maximum amount is not a valid number" . $validation['name']);
				}
					
				if($input > $validation['max'])
				{
					throw new Exception('Please enter a value lower than ' . number_format($validation['max'], 2) . ' in ' . $validation['name'] . '.');
				}
	
			}
				
			if(ISSET($validation['min_len']) && strlen($input) < $validation['min_len'])
			{
				throw new Exception($this->CI->lang->line('err_min_len') . $validation['min_len'] . ' character/s for ' . $validation['name']);
			}
			if(ISSET($validation['max_len']) && strlen($input) > $validation['max_len'])
			{
				throw new Exception($this->CI->lang->line('err_max_len') . $validation['max_len'] . ' character/s for ' . $validation['name']);
			}
						
			return $input;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	private function _validate_amount($input, $validation)
	{
		if(EMPTY($input))
		{
			return $input;
		}
	
		try
		{
			$input = trim(str_replace(',', '', $input));
			if(!is_numeric($input))
			{
				throw new Exception('Please enter a valid amount in ' . $validation['name'] . '.');
			}
			
			if(ISSET($validation['decimal']))
			{
				$input = round($input, $validation['decimal'], PHP_ROUND_HALF_UP);
			}
				
			if(!is_numeric($input))
			{
				throw new Exception('Please enter a valid number in ' . $validation['name'] . '.');
			}
				
			if(ISSET($validation['min']))
			{
				if(! is_numeric($input))
				{
					throw new Exception("Minimum amount is not a valid number for " . $validation['name'] . '.');
				}
	
				if($input > $validation['max'])
				{
					throw new Exception('Please enter a value higher than ' . number_format($validation['max'], 2) . ' in ' . $validation['name'] . '.');
				}
			}
				
			if(ISSET($validation['max']))
			{
				if(! is_numeric($input))
				{
					throw new Exception("Maximum amount is not a valid number for " . $validation['name'] . '.');
				}
	
				if($input > $validation['max'])
				{
					throw new Exception('Please enter a value lower than ' . number_format($validation['max'], 2) . ' in ' . $validation['name'] . '.');
				}
			}
				
			if(ISSET($validation['min_len']) && strlen($input) < $validation['min_len'])
			{
				throw new Exception($this->CI->lang->line('err_min_len') . $validation['min_len'] . ' character/s for ' . $validation['name']);
			}
			if(ISSET($validation['max_len']) && strlen($input) > $validation['max_len'])
			{
				throw new Exception($this->CI->lang->line('err_max_len') . $validation['max_len'] . ' character/s for ' . $validation['name']);
			}
			
			return $input;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	private function _validate_date($input, $validation)
	{
		if(EMPTY($input))
		{
			return $input;
		}
		try
		{
			$new_var = str_replace('.', '/', $input);
			$new_var = str_replace('-', '/', $input);
	
			$valid_date = date('Y-m-d H:i:s', strtotime($new_var));
				
			$input	= date('Y-m-d', strtotime($input));
			if($valid_date === FALSE)
			{
				throw new Exception('Please enter a valid date in' . $validation['name'] . '.');
			}
	
			if(ISSET($validation['min_date']))
			{
				$min_date = date('Y-m-d', strtotime($validation['min_date']));
				if(EMPTY($min_date))
				{
					throw new Exception("Minimum Date is invalid in " . $validation['name'] . '.');
				}
	
				// RLog::info('LINE 805 - - - -' . json_encode($validation));
				if(EMPTY($validation['min_date']) OR !EMPTY($validation['compare']))
				{
					// RLog::info('LINE 808 - - - -' . strtotime($input) . '<' . strtotime($min_date));
					if(strtotime($input) < strtotime($min_date))
					{
						$str = !EMPTY($validation['compare']) ?  'date of ' . $validation['compare']: 'minimum date('. format_date($validation['min_date']) . ')';
						throw new Exception('Entered date in ' . $validation['name'] . ' receded the ' . $str);
					}
				}
				else
				{
					if($validation['compare']	!= ENUM_NO)
					{
						if(strtotime($input) <= strtotime($min_date))
						{
							throw new Exception('Please enter a valid date range between '. format_date($validation['min_date']) . ' - ' . format_date($validation['max_date']) . ' in ' . $validation['name'] . '.');
						}
					}
				}
			}
	
			if(ISSET($validation['max_date']))
			{
				$max_date = date('Y-m-d', strtotime($validation['max_date']));
				if(EMPTY($max_date))
				{
					throw new Exception("Maximum Date is invalid in " . $validation['name'] . '.');
				}
	
				if(EMPTY($validation['min_date']) OR !EMPTY($validation['compare']))
				{
					if(strtotime($input) > strtotime($max_date) )
					{
						$str = !EMPTY($validation['compare']) ?  'date of ' . $validation['compare']: 'maximum date('. format_date($validation['max_date']) . ')';
						throw new Exception('Entered date in ' . $validation['name'] . ' exceeded the ' . $str);
					}
				}
				else
				{
					if($validation['compare']	!= ENUM_NO)
					{
						if(strtotime($input) >= strtotime($max_date) )
						{
							throw new Exception('Please enter a valid date range between '. format_date($validation['min_date']) . ' - ' . format_date($validation['max_date']) . ' in ' . $validation['name'] . '.');
						}
					}
				}
			}
	
			return $valid_date;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	private function _validate_enum($input, $validation)
	{
		if(EMPTY($input))
		{
			return $input;
		}
		try
		{
			$input = trim($input);
			if(ISSET($validation['allowed_values']) && ! in_array($input, $validation['allowed_values']))
			{
				throw new Exception($this->CI->lang->line('err_invalid_data') . ' in ' . $validation['name'] . '.');
			}
	
			return $input;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	private function _validate_time($input, $validation)
	{
		if(EMPTY($input))
		{
			return $input;
		}
		try
		{
			$valid_date = date_create($input);
			if($valid_date === FALSE)
			{
				throw new Exception('Please enter a valid time in ' . $validation['name'] . '.');
			}
	
			$format_pattern = (ISSET($validation['format']) && ! EMPTY($validation['format'])) ? $validation['format'] : 'H:i:s';
			$valid_date = date_format($valid_date, $format_pattern);
	
			return $valid_date;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}	

	private function _validate_db_value($input, $validation)
	{
		try
		{
			if(!ISSET( $validation['field'] ) OR EMPTY( $validation['field'] ) )
			{
				throw new Exception('There must be a select fields.');
			}

			if(!ISSET( $validation['where'] ) OR EMPTY( $validation['where'] ) )
			{
				throw new Exception('There must be a where filter.');	
			}

			$where 		= array(
				$validation['where'] => $input
			);

			if(!ISSET( $validation['table'] ) OR EMPTY( $validation['table'] ) )
			{
				throw new Exception('There must be a table.');	
			}

			if(!ISSET( $validation['check_field'] ) OR EMPTY( $validation['check_field'] ) )
			{
				throw new Exception('There must be a check_field.');	
			}

			$check 	= $this->CI->common_validate_model->validate_db_value( $validation['table'], array( $validation['field'] ), $where );

			if( EMPTY( $check ) OR EMPTY( $check[$validation['check_field']] ) )
			{
				throw new Exception('Invalid value for '.$validation['name']);
			}
			
			return $input;
		}
		catch (Exception $e)
		{
			throw $e;
		}
		
	}

	private function _validate_year($input, $validation)
	{
		try
		{
			if (is_null($input) OR empty($input))
			{
				return $input;
			}
			
			$input = trim($input);
			
			if (isset($validation['min_len']) AND strlen($input) < $validation['min_len'])
			{
				throw new Exception($this->CI->lang->line('err_min_len') . $validation['min_len'] . ' character/s for ' . $validation['name']);
			}
			if (isset($validation['max_len']) AND strlen($input) > $validation['max_len'])
			{
				throw new Exception($this->CI->lang->line('err_max_len') . $validation['max_len'] . ' character/s for ' . $validation['name']);
			}
			
			if (isset($validation['min']) AND ! empty($validation['min']) AND $input < $validation['min'])
			{
				throw new Exception('Please enter year later than ' . $validation['min'] . ' in ' . $validation['name']);
			}
			
			if (isset($validation['max']) AND ! empty($validation['max']) AND $input > $validation['max'])
			{
				throw new Exception('Please enter year earlier than ' . $validation['max'] . ' in ' . $validation['name']);
			}
			
			return $input;
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	public function getRules()
	{
		return array(
			'core_email_rule',
			'core_url_rule',
			'core_string_rule',
			'core_digit_rule',
			'core_amount_rule',
			'core_enum_rule',
			'core_time_rule',
			'core_db_value_rule',
			'core_year_rule'
		);
	}

	public function getRuleMessages()
	{
		return array(
			'core_email' 	=> 'The :field field is not a valid email address.',
			'core_url' 		=> 'The :field field is not a valid url.',
			'core_string' 	=> 'The :field field is not valid a string.',
			'core_digit' 	=> 'The :field field is not a valid digit.',
			'core_amount' 	=> 'The :field field is not a valid amount.',
			'core_enum' 	=> 'The :field field is not a valid enum.',
			'core_time' 	=> 'The :field field is not a valid time.',
			'core_db_value' => 'The :field field is invalid.',
			'core_year' 	=> 'The :field field is not a valid year.'
		);
	}

	public function core_year_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$val 	= $this->_validate_year( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_db_value_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_db_value( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}


	public function core_time_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}
			
			$satisfier['name'] 	= $field;

			$this->_validate_time( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_enum_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_enum( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_date_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_date( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_amount_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_amount( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_digit_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_digits( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_email_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_email( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_url_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_url( $value, $satisfier );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;
			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;
			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function core_string_rule( $value, $satisfier, $field )
	{
		$check 	= TRUE;
		$msg 	= "";
		$val 	= NULL;

		try
		{
			$password_flag 	= FALSE;

			if( !is_array( $satisfier ) )
			{
				$satisfier 		= array();
			}

			if( ISSET( $satisfier[0] ) )
			{
				$satisfier 		= $satisfier[0];
			}

			if( ISSET( $satisfier['password_flag'] ) )
			{
				$password_flag = $satisfier['password_flag'];
			}

			$satisfier['name'] 	= $field;

			$this->_validate_string( $value, $satisfier, $password_flag );
		}
		catch( Exception $e )
		{
			$check 	= FALSE;

			$msg 	= $e->getMessage();
		}
		catch( PDOException $e )
		{
			$check 	= FALSE;
			$msg 	= $e->getMessage();
		}

		return array(
			'check' 	=> $check,
			'msg' 		=> $msg,
			'val' 		=> $val
		);
	}

	public function runRules( $rule, $value, $satisfier, $field )
	{
		if( method_exists( $this , $rule ) )
		{
			return $this->{ $rule }( $value, $satisfier, $field );
		}
		else 
		{	
			return call_user_func_array( $rule , array( $value, $satisfier, $field ) );
		}
	}
}