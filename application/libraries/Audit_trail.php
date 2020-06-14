 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Audit_trail {
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('audit_trail_model');
	}
	
	/**
	 * $action - action made by the user, set of actions were placed in constant.php.
	 * $module - module in the system where the specific action/activity was made.
	 * $activity - list of activities will be referenced to param_activities table.
	 * $prev_detail = original value of a field before updating, this is saved to track what was changed if any.
	 * $curr_detail = current value of a field.
	 */
	
	public function log_audit_trail($activity, $module, $prev_detail = array(), $curr_detail = array(), $action_arr = array(), $table_arr = array(), $schema_arr = array(), $extra_params = array())
	{	
		try 
		{			
			if(EMPTY($activity))
				throw new Exception('Activity was not set for audit trail.');
			
			if(EMPTY($module))
				throw new Exception('Moodule was not set for audit trail.');
			
			$trail 				= $extra_params;			
			$trail["activity"] 	= $activity;
			$trail["module"] 	= $module;
			
			$id 				= $this->CI->audit_trail_model->insert_audit_trail($trail);
			$count_prev			= COUNT($prev_detail);
			$change_log 		= 0;

			if( !EMPTY( $prev_detail ) )
			{
			
				for($i = 0; $i < $count_prev; $i++)
				{
					$prev_data = $prev_detail[$i];
					$curr_data = $curr_detail[$i];
					$action = $action_arr[$i];
					$table = $table_arr[$i];
					$schema	= $schema_arr[$i];				

					switch($action)
					{
						case AUDIT_INSERT:
							
							foreach($curr_data as $curr_data)
							{
								foreach( $curr_data as $key => $val  )
								{
									$curr_val 	= $val;

									if(!EMPTY($curr_val))
									{
										$field = $table . "." . $key;
										$params[] = array(
											'audit_trail_id' => $id,
											'field'	=> $field,
											'prev_detail' => '',
											'curr_detail' => $curr_val,
											'action' => $action,
											'trail_schema' => $schema
										);
									}	
								}
								/*while(list($key, $curr_val) = each($curr_data))
								{
									if(!EMPTY($curr_val)){
										$field = $table . "." . $key;
										$params[] = array(
											'audit_trail_id' => $id,
											'field'	=> $field,
											'prev_detail' => '',
											'curr_detail' => $curr_val,
											'action' => $action,
											'trail_schema' => $schema
										);
									}	
								}	*/
							}
						break;
						
						case AUDIT_UPDATE:
							
							$index = 0;
							foreach($curr_data as $curr_data)
							{
								foreach( $curr_data as $key => $val  )
								{
									$change_log = 0;
									$prev_val = $prev_data[$index][$key];
									$curr_val = $val;

									$field = $table . "." . $key;
									
									// IF PREVIOUS VALUE IS NOT EQUAL TO CURRENT VALUE, LOG TO AUDIT TRAIL DETAIL
									if($prev_val != $curr_val)
									{
										$change_log = 1;
										$params[] = array(
											'audit_trail_id' => $id,
											'field'	=> $field,
											'prev_detail' => $prev_val,
											'curr_detail' => $curr_val,
											'action' => $action,
											'trail_schema' => $schema
										);
									}
								}
								
								/*while(list($key, $val) = each($curr_data))
								{
									$change_log = 0;
									$prev_val = $prev_data[$index][$key];
									$curr_val = $val;

									$field = $table . "." . $key;
									
									// IF PREVIOUS VALUE IS NOT EQUAL TO CURRENT VALUE, LOG TO AUDIT TRAIL DETAIL
									if($prev_val != $curr_val)
									{
										$change_log = 1;
										$params[] = array(
											'audit_trail_id' => $id,
											'field'	=> $field,
											'prev_detail' => $prev_val,
											'curr_detail' => $curr_val,
											'action' => $action,
											'trail_schema' => $schema
										);
									}
										
								}	*/
								
								$index++;
							}
						break;
						
						case AUDIT_DELETE:
							
							foreach($prev_data as $prev_data)
							{
								foreach( $prev_data as $key => $val )
								{
									$prev_val 	= $val;

									$field = $table . "." . $key;
									
									$params[] = array(
										'audit_trail_id' => $id,
										'field'	=> $field,
										'prev_detail' => $prev_val,
										'curr_detail' => '',
										'action' => $action,
										'trail_schema' => $schema
									);
								}
								/*while(list($key, $prev_val) = each($prev_data))
								{
									$field = $table . "." . $key;
									
									$params[] = array(
										'audit_trail_id' => $id,
										'field'	=> $field,
										'prev_detail' => $prev_val,
										'curr_detail' => '',
										'action' => $action,
										'trail_schema' => $schema
									);
								}*/
							}
						break;
					}
					
				}
				
				if(EMPTY($params) && ($action != AUDIT_UPDATE))
					throw new Exception('Parameters were not set in the audit trail.');
			}
			
			if(ISSET($action) AND $action == AUDIT_INSERT || $action == AUDIT_DELETE || ($action == AUDIT_UPDATE && !EMPTY($params)))
			{
				$this->CI->audit_trail_model->insert_audit_trail_detail($params);
			}
			
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
	
}