<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Base_Model extends CI_Model {

	// THIS WILL HOLD THE DATABASE NAME OF THE EXTENDING CLASS
	protected static $dsn;
	
	// THIS WILL HOLD THE SYSTEM NAME OF THE EXTENDING CLASS
	protected static $system 	= SYSTEM_DEFAULT;
	
	// THIS WILL HOLD THE DATABASE CONNECTIONS
	private static $conn 		= array();
	
	public function __construct()
	{
		$this->_construct_rlog();
	}
	
	private function _construct_rlog()
	{	
		$dsn 			= static::$dsn;
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
	
	}
	
	/**
	 * THIS FUNCTION WILL LOAD OR RETURN EXISTING DATA CONNECTION
	 */
	protected static function get_connection()
	{	
		$dsn 						 		= static::$dsn;
		
		$CI =& get_instance();
		
		if( is_array( static::$dsn ) )
		{
			$dsn 						= key( static::$dsn );

			if( !in_array($dsn, array_keys(self::$conn)) )
			{
				self::$conn[ $dsn ] 		= $CI->load->database( static::$dsn[ $dsn ], TRUE );
			}
		}
		else 
		{
			if(!in_array(static::$dsn, array_keys(self::$conn)))
			{
				self::$conn[static::$dsn] 	= $CI->load->database(static::$dsn, TRUE);		
			}
		}

		return self::$conn[$dsn];
	}
	
	public static function beginTransaction()
	{		
		try				
		{
			$db = static::get_connection();
			
			if(!$db->inTransaction())
				$db->beginTransaction();
		}
		catch (PDOException $e)
		{
			throw $e;
		}		
	}
	
	public static function commit()
	{
		try
		{
			
			$db = static::get_connection();			
			
			if($db->inTransaction())
				$db->commit();
		}
		catch (PDOException $e)
		{
			throw $e;
		}

	}
	
	public static function rollback()
	{		
		try
		{
		
			$db = static::get_connection();
						
			if($db->inTransaction())
				$db->rollBack();
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{			
			throw $e;
		}
	}
	
	/* FOR COMPLEXED QUERIES USE THIS */
	protected function query($query, $val = NULL, $is_select = TRUE, $multiple = TRUE,  $return_id = FALSE)
	{	
		try 
		{					
		/*	RLog::info('FUNCTION query()');
			RLog::info('QUERY: ' . $query);
			RLog::info('VALUE: ' . var_export($val, TRUE));*/
					
			$db		= static::get_connection();						
			$stmt	= $db->prepare($query);
			$stmt->execute($val);
			
			if($is_select)
			{
				if($multiple === TRUE)
				{
					return $stmt->fetchAll(PDO::FETCH_ASSOC);
				}
				else
				{
					return $stmt->fetch(PDO::FETCH_ASSOC);
				}
			}
			else
			{
				if( $return_id )
				{
					$last_insert_id = $db->lastInsertId();

					return $last_insert_id;
				}
				else
				{
					return $stmt;
				}
			}
		}
		catch (PDOException $e)
		{
			throw $e;
		}		
	}
	
	/* CRUD (BASIC COMMON QUERIES FOR INSERT, UPDATE, DELETE AND VIEW) */
	protected function select_data($fields_arr, $table, $multiple, $where_arr = array(), $order_arr = array(), $group_arr = array())
	{
		try
		{				
			$values 	= array();
			$where 		= '';
			$order_by 	= '';
			$group_by 	= '';
			
			
			$fields 	= implode(',' , $fields_arr);
				
			if( ! empty($where_arr))
			{
				// Construct where condition
				list($where_str, $where_val)	= $this->_construct_where_statement($where_arr);
				
				$where	= ' WHERE ' . $where_str;
				$values	= array_merge($values, $where_val);
				
			}
	
			if( ! empty($order_arr))
			{
				$order_by_arr = array();					
				foreach ($order_arr as $a => $b)
					$order_by_arr[] = $a . ' ' . $b;
					
				$order_by = 'ORDER BY  '. implode(',',$order_by_arr);
			}
			
			if( ! empty($group_arr))
			{
				$group_by_arr = array(); 				
				foreach ($group_arr as $grp)
					$group_by_arr[] = $grp;				
				
				$group_by = 'GROUP BY  ' . implode(',', $group_by_arr);
			}
				
	
			$query = 
			'
				SELECT ' . $fields . '
				FROM ' . $table . ' ' .
				$where . ' ' .
				$group_by . ' ' .
				$order_by;

			
		/*	RLog::info('FUNCTION select_data()');
			RLog::info('QUERY: ' . $query);
			RLog::info('VALUE: ' . var_export($values, TRUE)); */
			
			return $this->query($query, $values, TRUE, $multiple);	
							
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}		
	
	/*protected function insert_data($table, $raw_fields, $return_id = FALSE, $on_dup_update = FALSE)
	{
		try
		{
			
			$column_str = $dup_fields_str = "";
			$marker_str = $values = $dup_fields = array();
			
			if( ! isset($raw_fields[0]))
				$raw_fields = array($raw_fields);
				
			foreach($raw_fields as $index => $field_arr)
			{
				$columns = $markers = array();
				
				foreach($field_arr as $column_name => $column_val)
				{
					$columns[]	= $column_name;
					
					if(is_array($column_val) AND ! empty($column_val))
					{
						
						$values[]	= $column_val[0];
						
						switch($column_val[1])
						{
							
							case 'ENCRYPT':
								$markers[]	= aes_crypt('?');
							break;
								
							default:
								throw new Exception('Invalid data format.');
							break;
						}
					}
					else
					{
						$markers[]	= '?';
						$values[]	= $column_val;
					}
					
					
					if($on_dup_update === TRUE)
					{
						$dup_fields[]	= $column_name . ' = VALUES('.$column_name.') ';
					}
					
				}
				
				if($index == 0)
				{
					$columns	= implode(',', $columns);
					$column_str	= '('.$columns.')';
					
					if($on_dup_update === TRUE)
					{
						$dup_fields_str = ' ON DUPLICATE KEY UPDATE ' . implode(',', $dup_fields);
					}
				}
				
				
				$markers		= implode(',', $markers);
				$marker_str[]	= '('.$markers.')';
			}
			
			$marker_str	= implode(',', $marker_str);

			$query = '
				INSERT INTO ' . $table . $column_str . ' 					
				VALUES ' .$marker_str . $dup_fields_str;
	
			return $this->query($query, $values, FALSE, FALSE, $return_id);	
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	
	}	*/

	protected function insert_data($table, $raw_fields, $return_id = FALSE, $on_dup_update = FALSE, $on_dup_field_id=NULL)
	{
		try
		{
			$column_str = $dup_fields_str = "";
			$marker_str = $values = $dup_fields = array();

	        if( ! isset($raw_fields[0]))
	            $raw_fields = array($raw_fields);

	        foreach($raw_fields as $index => $field_arr)
	        {
	            $columns = $markers = array();

	            foreach($field_arr as $column_name => $column_val)
	            {
	                $columns[]    = $column_name;

	                if(is_array($column_val) AND ! empty($column_val))
	                {                        
	                    $values[]    = $column_val[0];

	                    switch($column_val[1])
	                    {                            
	                        case 'ENCRYPT':
	                            $markers[]    = aes_crypt('?');
	                        break;

	                        default:
	                            throw new Exception('Invalid data format.');
	                        break;
	                    }
	                }
	                else
	                {
	                    $markers[]    = '?';
	                    $values[]    = $column_val;
	                }                    

	                if($on_dup_update === TRUE)
	                {
	                    $dup_fields[]    = $column_name . ' = VALUES('.$column_name.') ';
	                }                    
	            }

	            if($index == 0)
	            {
	                $columns    = implode(',', $columns);
	                $column_str    = '('.$columns.')';

	                if($on_dup_update === TRUE)
	                {
	                    $dup_fields_str = ' ON DUPLICATE KEY UPDATE ' . implode(',', $dup_fields);

	                    if(isset($on_dup_field_id))
	                    {
	                        $dup_fields_str .= ", $on_dup_field_id = LAST_INSERT_ID($on_dup_field_id) ";
	                    }
	                }
	            }                

	            $markers        = implode(',', $markers);
	            $marker_str[]    = '('.$markers.')';
	        }

	        $marker_str    = implode(',', $marker_str);

	        $query = '
	            INSERT INTO ' . $table . $column_str . '                     
	            VALUES ' .$marker_str . $dup_fields_str;
	           /*print_r($query);
	           print_r($values);*/
	        /*RLog::info('QUERY: ' . $query);
	        RLog::info('VALUE: ' . var_export($values, TRUE));    */

	        return $this->query($query, $values, FALSE, FALSE, $return_id);    
	    }
	    catch(PDOException $e)
	    {
	        throw $e;
	    }
	    catch(Exception $e)
	    {
	        throw $e;
	    }    
	}
	
	/*
	* Use of OR 
	* $where['OR'] = array('username' => $username, 'email' => $email)
	* WHERE (username = ? OR email = ?)
	*
	* $where['OR'][] = array('username' => $username, 'email' => $email)
	* $where['OR'][] = array('status' => 'Active', 'flag' => 'Y')
	* WHERE (username = ? OR email = ?) AND (status = ? OR flag = ?)
	*
	* Use of !=, <>, =>, <=
	* $where['username']['!='] = 'juan'
	* WHERE username != ?
	*
	* Use of IS NULL, IS NOT NULL
	* $where['username'] = 'IS NULL'
	* WHERE username IS NULL
	*
	* Use of IN, NOT IN
	* $values = array('juan', 'pedro')
	* $where['username'] = array( 'IN', $values )
	*  OR
	* $where['username']['IN'] = $values;
	*	
	* WHERE username IN ('juan', 'pedro')
	*/
	
	protected function _construct_where_statement($where_arr)
	{
		
		$operators	= array('=', '!=', '<>', '>', '<', '>=', '<=');
		$nulls		= array('IS NULL', 'IS NOT NULL');
		$ins		= array('IN', 'NOT IN');
		
		$fields		= array();
		$values		= array();
		
		
		foreach($where_arr as $key_arr => $val_arr)
		{	
		
			
			if($key_arr === 'OR')
			{					

				if( ! isset($val_arr[0]) )
				{
					$val_arr = array($val_arr);
				}
				
				foreach($val_arr as $val_arr)
				{
					$this->_construct_or($val_arr, $fields, $values);	
				}				
			}
			else
			{
				if(is_array($val_arr))
				{
					if( isset($val_arr[0]) )
					{
						$values = array_merge($values, $val_arr[1]);
						$this->_construct_in($key_arr, $val_arr[0], $val_arr[1], $fields);
					}
					else 
					{
						foreach($val_arr as $optr => $optr_val)
						{
							if( in_array($optr, $operators) )
							{
								$values[]	= $optr_val;
								$fields[]	= $key_arr . ' ' . $optr . ' ? ';							
							}
							elseif( in_array($optr, $ins) )
							{
								$values = array_merge($values, $optr_val);	
								$this->_construct_in($key_arr, $optr, $optr_val, $fields, $values);
							}
						}
					}
				}
				else 
				{
					if( in_array($val_arr, $nulls) AND ! empty($val_arr) )
					{						
						$fields[]	= $key_arr . ' ' . $val_arr;						
					}
					else
					{
						$fields[]	= $key_arr . ' = ? ';
						$values[]	= $val_arr;
					}
				}
			}
		
		}
		
		$field_str = implode(' AND ', $fields);
		
		return array($field_str, $values);
	}
	
	
	private function _construct_or($val, &$fields, &$values)
	{
		$field_arr = array();
		foreach($val as $field => $value)
		{
			$values[]		= $value;
			$field_arr[] 	= $field . ' = ? ';
		}
		
		$fields[] = '( ' . implode(' OR ', $field_arr) . ' )';
	}
	
	private function _construct_in($key_arr, $optr, $optr_val, &$fields)
	{
		$markers = [];
		foreach($optr_val as $optr_val)
		{
			$markers[] = '?';
		}
		$markers	= implode(',', $markers);
		$fields[]	= $key_arr . ' ' . $optr . ' (' . $markers . ') ';
	}
	
	protected function update_data($table, $raw_fields, $where_arr)
	{
		try
		{
			$marker_str = $values =  array();
			
			// Construct fields to be updated
			if( ! isset($raw_fields[0]))
				$raw_fields = array($raw_fields);
				
			foreach($raw_fields as $index => $field_arr)
			{
				$markers = array();
				
				foreach($field_arr as $column_name => $column_val)
				{
					if(is_array($column_val) AND ! empty($column_val))
					{						
						$values[]	= $column_val[0];
						
						switch($column_val[1])
						{
							
							case 'ENCRYPT':
								$markers[]	= $column_name . ' = ' .aes_crypt('?');
							break;
								
							default:
								throw new Exception('Invalid data format.');
							break;
						}
					}
					else
					{
						$markers[]	= $column_name . ' = ?';
						$values[]	= $column_val;
					}
				}
												
				$markers		= implode(',', $markers);				
			}
			
			
			// Construct where condition
			list($where_str, $where_val)	= $this->_construct_where_statement($where_arr);
			$values							= array_merge($values, $where_val);
			
			$query = '
				UPDATE ' . $table . ' 
				SET ' . $markers	. '			
				WHERE ' . $where_str  ;
				
		/*	RLog::info('FUNCTION update_data()');
			RLog::info('QUERY: ' . $query);
			RLog::info('VALUE: ' . var_export($values, TRUE));	*/
				
			$this->query($query, $values, FALSE);	
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	}

	protected function delete_data($table, $where_arr)
	{
		try
		{
			// Construct where condition
			list($where, $values)	= $this->_construct_where_statement($where_arr);
			
			$query = '  
				DELETE FROM ' . $table . ' 
				WHERE ' . $where ;

	
		/*	RLog::info('FUNCTION delete_data()');
			RLog::info('QUERY: ' . $query);
			RLog::info('VALUE: ' . var_export($values, TRUE));*/
			
			$this->query($query, $values, FALSE);	
	
		}
		catch(PDOException $e)
		{
			throw $e;
		}
	
	}
	/* CRUD */
	
	protected function insert_select_data($table, $fields_arr, $where_arr = array(), $return_id = FALSE)
	{
		try
		{
			// RLog::info('FUNCTION insert_select_data()');
			
			$db 	= static::get_connection();
			
			$val 	= array();
			$fields = "";
			$values = "";
			$where 	= "";
	
			foreach ($fields_arr as $k => $v):
				$fields .= $k.", ";
				$values .= $v.", ";
			endforeach;
				
			$fields = rtrim($fields, ", ");
			$values = rtrim($values, ", ");
				
			foreach ($where_arr as $a => $b):
				$val[] = $b;
				$where .= $a." = ? AND ";
			endforeach;
				
			$where = rtrim($where, " AND ");
	
			$query = <<<EOS
				INSERT INTO $table[0] ($fields)
				SELECT $values
				FROM $table[1]
				WHERE $where
EOS;
	
			/*RLog::info('QUERY ' . $query);
			RLog::info('VALUES ' . var_export($val, TRUE));*/
			
			$stmt = $db->prepare($query);
			$stmt->execute($val);
			
			if($return_id){
				$last_insert_id = $db->lastInsertId();
				
				// RLog::info('LAST INSERT ID ' . $last_insert_id);
				
				return $last_insert_id;
			}
	
		}
		catch(PDOException $e)
		{
			throw $e;
		}	
	}
	
	/** ********************
	 * 	FOR DATATABLE 
	 ***********************
	 * 
	 * @param $aColumns - columns to be converted into field name. 
	 * @param $params - parameters (post data) from datatable
	 * @param $has_where - indicator if this function will start with "WHERE" or "AND" statement
	 * @return 
	 * 		- returns where clause or additional to where clause
	 */
	protected function filtering($aColumns, $params, $has_where)
	{
		try
		{
			$sWhere_arr 	= array();
			$sWhere 		= "";
			$sHaving 		= "";
			$search_params 	= array();
			$is_concat  	= FALSE;
			
			// IF DATATABLE PASSED sSearch PARAMS THEN SEARCH ALL COLUMNS
			if (ISSET($params['sSearch']) && $params['sSearch'] != "")
			{
				$sWhere = ($has_where) ? " AND (" : "WHERE ("; // START WITH "WHERE" or "AND" ?
				$has_where = true; // MARK THIS TRUE SO THAT NEXT QUERY WILL USE "AND" 
				
				// COUNT COLUMNS THAT CAN BE FILTERED
				$cnt1 	= count($aColumns);

				// THIS LOOP WILL CREATE "[WHERE|AND] (fieldA = ? OR fieldB = ?)"
				for ($i = 0; $i < $cnt1; $i++)
				{
					$filter_column = $this->_filtering_get_column_name($aColumns[$i], $aColumns[$i]);
					
					
					
					$args 		= $filter_column['args'];
					
					if ( ! empty($filter_column['new_column']))
					{
						$column 	= $filter_column['new_column'];
					}
					else 
					{
						$column 	= $filter_column['column'];
					}
					
					if( EMPTY( $args ) )
					{
						$column			= str_replace("-", ".",$column);
					}

					if( !$column instanceof Closure )
					{
					/*if (ISSET($params['bSearchable_'.$i]) && $params['bSearchable_'.$i] == "true")
					{*/
				
						if(is_array($column))
						{
							$column = 'CAST(' . aes_crypt($column[0], FALSE, FALSE) . ' AS CHAR)';							
						}
				
				
						$sWhere .= "LOWER(".$column.") LIKE ? OR ";
						
						$search_params[] = "%".strtolower(filter_var($params['sSearch'], FILTER_SANITIZE_STRING))."%";
					// }
					}
					else
					{
						if(is_array($column))
							$column = 'CAST(' . aes_crypt($column[0], FALSE, FALSE) . ' AS CHAR)';
						
				
						
						$spec 		= call_user_func_array($column, array( $params['sSearch'], $sWhere ) );

						$sWhere 	.= ($has_where ? " AND " : "WHERE ");
						$sWhere 		.= $spec['where'];
						
						if( ISSET( $spec['val'] ) AND !EMPTY( $spec['val'] ) )
						{
							$search_params[] = "%".strtolower(filter_var($spec['val'], FILTER_SANITIZE_STRING))."%";
						}

						if( ISSET( $spec['specific_val'] ) AND !EMPTY( $spec['specific_val'] ) )
						{
							$search_params[] = $spec['specific_val'];
						}
					}
				}
				$sWhere = substr_replace( $sWhere, "", -3 );
				$sWhere .= ')';
				
			} // END OF : SEARCH ALL COLUMNS
			
			// SEARCH PER COLUMN 
			if( ISSET( $params['action'] ) AND !EMPTY( $params['action'] ) )
			{
				// THIS LOOP CREATES "[WHERE|AND] fieldA = ? AND fieldB = ?"
				foreach($aColumns as $aColumn => $special)
				{					
					$filter_column = $this->_filtering_get_column_name($aColumn, $special);
							
					$args 		= $filter_column['args'];
					$decrypt	= FALSE;
					
					if(is_array($filter_column['column']))
					{
						$column 	= $filter_column['column'][0];
						$decrypt	= TRUE;
					}
					else 
					{
						$column 	= $filter_column['column'];
					}
					
					$new_column = $filter_column['new_column'];
										
					
					if( isset( $params[$column] ) AND ! empty($params[$column]))
					{
						// CHECK IF COLUMN USES "GROUP_CONCAT" FUNCTION IN MYSQL
						if( empty( $args ) )
						{
							$new_column	= str_replace("-", ".",$column);
							if(strpos(strtolower($new_column), 'group_concat') !== false)
							{
								// IF COLUMN USES "GROUP_CONCAT", MOVE TO NEXT COLUMN
								continue; 
							}
						}
						
						if( $special instanceof Closure )
						{
							$spec 		= call_user_func_array($special, array( $params[$column] ) );
							
							$sWhere 	.= ($has_where ? " AND " : "WHERE ");
							$sWhere 	.= $spec['where'];
							$has_where	= true;
							
							if( ISSET( $spec['val'] ) AND !EMPTY( $spec['val'] ) )
							{
								$search_params[] = $spec['val'];
							}
						
						}
						else
						{
							// var_dump($dec)
							if($decrypt)								
								$new_column = 'CAST(' . aes_crypt($column, FALSE, FALSE) . ' AS CHAR)';
							
							$sWhere 	.= ($has_where ? " AND " : "WHERE ");
							$sWhere     .= " LOWER(".$new_column.") LIKE ? ";
							$has_where 	= true;
						}
						
							
						$is_concat = FALSE;

						if( !$special instanceof Closure )
						{
							$search_params[] = "%".strtolower(filter_var($params[$column], FILTER_SANITIZE_STRING))."%";
						}
					}
				} // END OF : FOR..LOOP (WHERE CLAUSE)
				
				// THIS LOOP CREATES "HAVING fieldA = ? AND fieldB = ?"
				foreach($aColumns as $aColumn => $special)
				{
					$filter_column = $this->_filtering_get_column_name($aColumn, $special);
					
					$args 		= $filter_column['args'];
					$column 	= $filter_column['column'];
					$new_column = $filter_column['new_column'];
					
					if (ISSET($params[$column]) AND ! EMPTY($params[$column]))
					{
						if (EMPTY($args))
						{
							$new_column	= str_replace("-", ".",$column);
							
							// CHECK IF COLUMN USES "GROUP_CONCAT" FUNCTION IN MYSQL
							if (strpos(strtolower($new_column), 'group_concat') !== false)
							{
								if ( ! empty($sHaving)) // IF COLUMN USES "GROUP_CONCAT", CREATE "HAVING" CLAUSE
								{
									$sHaving .= "AND ";
								}
								else
								{
									$sHaving .= "HAVING ";
								}
								$sHaving     .= "LOWER(".$new_column.") LIKE ? ";
								
								if( ! $special instanceof Closure )
								{
									$search_params[] = "%".strtolower(filter_var($params[$column], FILTER_SANITIZE_STRING))."%";
								}
							}
						}
					}
				} // END OF : FOR..LOOP (HAVING CLAUSE)
				
			} // END OF : SEARCH PER COLUMN
			
			$sWhere_arr["search_str"] 		= $sWhere;
			$sWhere_arr["search_having"] 	= $sHaving;
			$sWhere_arr["search_params"] 	= $search_params;
			return $sWhere_arr;
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}	
	}
	
	private function _filtering_get_column_name($aColumn, $special)
	{
		$args 		= array();		
		$column 	= $special;
		$new_column = '';
		
		if ( ! is_integer($aColumn))
		{
			$column = $aColumn;
		}

		if( is_array( $column ) )
		{
			if( isset( $column[1] ) AND $column[1] == 'DECRYPT' )
			{
				$column 	= $column;
			}
			else
			{
				$column 	= $column[0];
			}
		}

		$check_convert 	= preg_match('/convert_to/', $column, $convert );
		$match 			= array();
		
		if ( ! EMPTY($check_convert))
		{
			$matches 	= $check_convert;
			$match 		= $convert;
		}
		else
		{
			$matches = preg_match('/\bas\b/i', $column, $match);
		}
		
		if( ! EMPTY($matches))
		{
			if (ISSET($match[0]))
			{
				$args 		= explode($match[0] , $column);
				
				$column 	= trim($args[1]);
				$new_column = $args[0];
			}
		}
		
		return array(
				'args' 		=> $args,
				'column' 	=> $column,
				'new_column'=> $new_column
		);
	}
		
	protected function ordering($aColumns, $params)
	{
		try
		{
			
			$sOrder 	= "";
			if (ISSET($params['iSortCol_0']))
			{
				$sOrder = "ORDER BY  ";
			
				for ($i=0 ; $i<intval( $params['iSortingCols'] ) ; $i++)
				{
					if ($params[ 'bSortable_'.intval($params['iSortCol_'.$i]) ] == "true")
					{
						
						$col	= $aColumns[ intval( $params['iSortCol_'.$i] ) ];
						if(is_array($col))
						{
							$col = aes_crypt($col[0], FALSE, FALSE);
						}
						
						$sort	= $params['sSortDir_'.$i];
						
						$sOrder .= $col . ' ' . ( $sort === 'asc' ? 'asc' : 'desc') . ', ';	
						
						
					}
				}
				
				$sOrder = substr_replace( $sOrder, "", -2 );
				if ( $sOrder == "ORDER BY" )
					$sOrder = "";
			}
			
			return $sOrder;
		}	
		catch(Exception $e)
		{
			throw $e;
		}	
	}	
	
	protected function paging($params)
	{
		try
		{
			$sLimit 	= "";
			if (ISSET($params['iDisplayStart']) && $params['iDisplayLength'] != '-1')
			{
				$sLimit = "LIMIT ".intval( $params['iDisplayStart'] ).", ".
					intval( $params['iDisplayLength'] );
			}
		
			return $sLimit;
		}
		catch( PDOException $e )
		{
			throw $e;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	/* FOR DATATABLE */
	
	/* GET AUDIT TRAIL DETAIL */
	
	public function get_details_for_audit( $table, $where )
	{
		$result 	= array();

		try
		{
			$result = $this->select_data( array( '*' ), $table, TRUE, $where );
		}
		catch( PDOException $e )
		{
			throw $e;
		}

		return $result;
	}

	public function get_columns_table( $table, $schema = DB_CORE, $dont_include = array(), array $pass_fields = array(), $another_where = '', $another_where_val = array(), $join_str = '', $pdo_type_fetch = PDO::FETCH_COLUMN )
	{
		$result 		= array();
		$val 			= array();

		$extra_val 		= array();
		$extra_where 	= '';

		try 
		{
			$fields 	= array('COLUMN_NAME');

			$def_dont 	= array(
				'created_by',
				'created_date',
				'modified_by',
				'modified_date'
			);

			if( !EMPTY( $pass_fields ) )
			{
				$fields = $pass_fields;
			}

			if( !EMPTY( $dont_include ) )
			{
				$def_dont = $dont_include;
			}

			$columns 	= str_replace(" , ", " ", implode(", ", $fields));

			if( !EMPTY( $def_dont ) )
			{
				$count_def					= count( $def_dont );

				$placeholder_def 			= str_repeat( '?,', $count_def );
				$placeholder_def 			= rtrim( $placeholder_def, ',' );
				
				$extra_where        	   .= " AND COLUMN_NAME NOT IN ( $placeholder_def ) ";

				$extra_val 					= array_merge( $extra_val, $def_dont );
			}

			$query 		= "
				SELECT 	$columns
				FROM 	information_schema.columns 
				$join_str
				WHERE 	table_schema 	= ?
				AND 	table_name 		= ?
				$extra_where
				$another_where
				
";

			$val[] 		= $schema;
			$val[]		= $table;

			if( !EMPTY( $extra_val ) )
			{
				$val 	= array_merge( $val, $extra_val );
			}
			
			if( !EMPTY( $another_where_val ) )
			{
				$val 	= array_merge( $val, $another_where_val );
			}
			
			$stmt 		= $this->query( $query, $val );

			$result 	= $stmt->fetchAll($pdo_type_fetch);
		}
		catch(PDOException $e)
		{
			throw $e;
		}

		return $result;
	}
}