<div class="table-display ">
  <div class="p-md table-cell valign-top b-r" style="border-color:#e2e7e7!important; width:30%;">
    <div class="p-t-sm p-b-xs">
      <label class="label m-b-n-xs text-uppercase">Module</label>
	  <p><?php echo $audit_trail['module_name'] ?></p>
    </div>
    <div class="p-t-sm p-b-xs">
      <label class="label m-b-n-xs text-uppercase">Activity</label>
	  <p class="break-all"><?php echo $audit_trail['activity'] ?></p>
    </div>
    <div class="p-t-sm p-b-xs">
      <label class="label m-b-n-xs text-uppercase">Activity Date</label>
	  <p><?php echo $audit_trail['activity_date'] ?></p>
    </div>
    <div class="p-t-sm p-b-xs">
      <label class="label m-b-n-xs text-uppercase">User</label>
	  <p><?php echo $audit_trail['name'] ?></p>
	  <div class="font-xs text-muted">I.P. Address: <?php echo $audit_trail['ip_address'] ?></div>
    </div>
  </div>
  <div class="p-n table-cell valign-top" style=" width:70%;">
	<table class="table table-default">
	<thead>
	<tr>
	  <th width="30%">Fields</th>
	  <th width="35%">Previous Value</th>
	  <th width="35%">Current Value</th>
	</tr>
	</thead>
	</table>
    <div>
      <div class="scroll-pane" style="height:380px">
		<table class="table table-default font-sm" cellpadding="0" cellspacing="0" width="100%">
	    <tbody>
	      <?php foreach($audit_trail_detail as $row): 
	      	$field_arr = explode('.', $row['field']);

	      	$table 	= $field_arr[0];
	      	$column = $field_arr[1];
	      	$schema = $row['trail_schema'];

	      	$key_usage = $obj->audit_log->get_reference_table($table, $column, $schema);

	      	$prev_real_detail = $row['prev_detail'];
	      	$curr_real_detail = $row['curr_detail'];

	      	if( ( !EMPTY( $key_usage ) AND !EMPTY( $key_usage['REFERENCED_TABLE_NAME'] ) 
	      		AND !EMPTY( $key_usage['REFERENCED_COLUMN_NAME'] ) ) 
	      		OR ( 
	      			in_array($table, array(SYSAD_Model::CORE_TABLE_USERS)) AND in_array($column, array('user_id'))
	      		)
	      		AND !in_array($schema, array(AUDIT_INSERT))
	      	)
	      	{
	      		$ref_table 	= $key_usage['REFERENCED_TABLE_NAME'];
	      		$ref_col 	= $key_usage['REFERENCED_COLUMN_NAME'];

	      		if(in_array($table, array(SYSAD_Model::CORE_TABLE_USERS,SYSAD_Model::CORE_TABLE_ORGANIZATIONS, SYSAD_Model::CORE_TABLE_ROLES)) AND in_array($column, array('user_id', 'org_code', 'role_code')) )
	      		{
	      			$ref_table 	= $table;
	      			$ref_col 	= $column;
	      		}

	      		$name_key 	= '';

	      		if( in_array($ref_table, array(SYSAD_Model::CORE_TABLE_USERS, SYSAD_Model::CORE_TABLE_TEMP_USERS ) ) )
	      		{
	      			$fname_cast = 'CAST('.aes_crypt('fname', FALSE, FALSE).' AS char(100))';
					$lname_cast = 'CAST('.aes_crypt('lname', FALSE, FALSE).' AS char(100))';	

					$name_key 	= "CONCAT($fname_cast, ' ', $lname_cast)";
	      		}
	      		else
	      		{
	      			$name_db 	= $obj->audit_log->get_first_default_name($ref_table, $schema);

	      			if( !EMPTY( $name_db ) AND !EMPTY( $name_db['COLUMN_NAME'] ) )
	      			{
	      				$name_key = $name_db['COLUMN_NAME'];
	      			}
	      		}

	      		if( !EMPTY( $row['prev_detail'] ) )
	      		{
	      		/*	$prev_name_val 	= $obj->audit_log->get_name_val($ref_table, $ref_col, $schema, $row['prev_detail'], $name_key);

	      			if( ISSET( $prev_name_val['val_name'] ) AND !EMPTY( $prev_name_val['val_name'] ) )
	      			{
	      				$prev_real_detail = $prev_name_val['val_name'];
	      			}*/
	      		}

	      		if( !EMPTY( $row['curr_detail'] ) )
	      		{
	      			
	      			$curr_name_val 	= $obj->audit_log->get_name_val($ref_table, $ref_col, $schema, $row['curr_detail'], $name_key);

	      			if( ISSET( $curr_name_val['val_name'] ) AND !EMPTY( $curr_name_val['val_name'] ) )
	      			{
	      				$curr_real_detail = $curr_name_val['val_name'];
	      			}
	      		}
	      		
	      	}
	      ?>
		  <tr>
			<td width="30%"><?php echo $row['field'] ?></td>
			<td width="35%"><?php echo $prev_real_detail ?></td>
			<td width="35%"><?php echo $curr_real_detail ?></td>
		  </tr>
		  <?php endforeach; ?>
	    </tbody>
	    </table>
	  </div>
    </div>
  </div>
</div>