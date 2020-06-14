<?php 
	$disable 	= '';

	if( $action == ACTION_VIEW )
	{
		$disable = 'disabled';
	}
?>
<?php 
	$action_name 			= "action";
	$display_status_name 	= "display_status";
	$proc_name 				= 'process_stop_flag';
	$save_data_class 		= '';
	$sg_id 					= 0;

	if( !EMPTY( $stage_id ) )
	{
		$action_name 			.= '['.$stage_id.']';
		$display_status_name 	.= '['.$stage_id.']';
		$proc_name 				.= '['.$stage_id.']';
		$sg_id 					= $stage_id;
	}
	else
	{
		/*$action_name 			.= '[]';
		$display_status_name 	.= '[]';*/
	}

	$st_id 						= 0;

	if( !EMPTY( $step_id ) )
	{
		$action_name 			.= '['.$step_id.']';	
		$display_status_name 	.= '['.$step_id.']';
		$proc_name 				.= '['.$step_id.']';
		$save_data_class 		= 'saved_data';
		$st_id 					= $step_id;
	}
	else
	{
		$action_name 			.= '[1_sequence]';
		$display_status_name 	.= '[1_sequence]';
	}
	
	$action_name 			.= '[]';
	$display_status_name 	.= '[]';
	$proc_name 				.= '[]';

	$a_main_id 				= $sg_id.'_'.$st_id;
?>

<?php 
	if( !EMPTY( $task_actions ) ) :
?>
<?php 
		foreach( $task_actions as $key_a => $t_a ) :

			$ta_id 	= $t_a['task_action_id'];

			$ta_id_enc 		= base64_url_encode( $ta_id );
			$ta_id_salt 	= gen_salt();
			$ta_id_token 	= in_salt( $ta_id, $ta_id_salt );

			$st_id 			= base64_url_encode( $t_a['workflow_task_id'] );
			$st_salt 		= gen_salt();
			$st_token 		= in_salt( $t_a['workflow_task_id'], $st_salt );

			$act_obj 		= array(
				'action_id'		=> $ta_id_enc,
				'action_salt'	=> $ta_id_salt,
				'action_token'	=> $ta_id_token,
				'step_id'		=> $st_id,
				'step_salt'		=> $st_salt,
				'step_token' 	=> $st_token
			);

			$act_json 	= json_encode( $act_obj );

			$check_pr_stop = ( $t_a['process_stop_flag'] == ENUM_YES ) ? 'checked' : '';
?>
<tr>
	<td>
		<div>
			<select <?php echo $disable ?> data-url-encode="true" name="<?php echo $action_name ?>" data-parsley-group="fieldset-3" data-parsley-required="true" data-parsley-trigger="change" id="action_id" class="selectize column-1 sub_multiple <?php echo $save_data_class ?>">
				<option value="">Please Select</option>
				<?php 
					if( !EMPTY( $actions ) ) :
				?>
					<?php 
						foreach( $actions as $act ) :

							$sel_ta 	= ( $act['action_id'] == $ta_id ) ? 'selected' : '';

							$id_act 	= base64_url_encode( $act['action_id'] );
					?>
					<option value="<?php echo $id_act ?>|<?php echo $act['action_id'] ?>" <?php echo $sel_ta ?>><?php echo $act['action_name'] ?></option>
					<?php 
						endforeach;
					?>
				<?php 
					endif;
				?>
			</select>
		</div>
	</td>
	<td>
		<input <?php echo $disable ?> type="text" data-parsley-group="fieldset-3" name="<?php echo $display_status_name ?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-maxlength="100" value="<?php echo $t_a['display_status'] ?>" class="sub_multiple <?php echo $save_data_class ?>" id="display_status">
	</td>
	<td>
		<div class="input-field m-n">
			<input type="hidden" class="act_checkbox_hidden" name="<?php echo $proc_name ?>" value="" />
			<input type="checkbox" <?php echo $disable ?> <?php echo $check_pr_stop ?> class="labelauty process_stop" id="process_stop_<?php echo $a_main_id ?>_<?php echo $key_a ?>" name="process_stop[]" data-labelauty="No|Yes" />
		</div>
	</td>
	<td>
		<div class="table-actions center">
			<?php 
				if( EMPTY( $disable ) ) :
			?>
			<a href='javascript:;' class='delete tooltipped' data-tooltip='Delete' onclick="content_workflow_action_delete('Action', '', undefined, this)" data-delete_post='<?php echo $act_json ?>' data-position='bottom' data-delay='50'></a>
			<?php 
				endif;
			?>
		</div>
	</td>
</tr>
<?php 
		endforeach;
?>
<?php 
	else :
?>
<tr>
	<td>
		<div>
			<select <?php echo $disable ?> data-url-encode="true" name="<?php echo $action_name ?>" data-parsley-group="fieldset-3" data-parsley-required="true" data-parsley-trigger="change" id="action_id" class="column-1 selectize sub_multiple <?php echo $save_data_class ?>">
				<option value="">Please Select</option>
				<?php 
					if( !EMPTY( $actions ) ) :
				?>
					<?php 
						foreach( $actions as $act ) :

							$id_act 	= base64_url_encode( $act['action_id'] );
					?>
					<option value="<?php echo $id_act ?>|<?php echo $act['action_id'] ?>"><?php echo $act['action_name'] ?></option>
					<?php 
						endforeach;
					?>
				<?php 
					endif;
				?>
			</select>
		</div>
	</td>
	<td>
		<input <?php echo $disable ?> type="text" data-parsley-group="fieldset-3" name="<?php echo $display_status_name ?>" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-maxlength="100" class="sub_multiple <?php echo $save_data_class ?>" id="display_status">
	</td>
	<td>
		<div class="input-field m-n">
			<input type="hidden" class="act_checkbox_hidden" name="<?php echo $proc_name ?>" value="" />
			<input type="checkbox" <?php echo $disable ?> class="labelauty process_stop" id="process_stop_<?php echo $a_main_id ?>" name="process_stop[]" data-labelauty="No|Yes" />
		</div>
	</td>
	<td>
		<div class="table-actions center">
			<?php 
				if( EMPTY( $disable ) ) :
			?>
			<a href='javascript:;' class='delete tooltipped' data-tooltip='Delete' data-position='bottom' data-delay='50'></a>
			<?php 
				endif;
			?>
		</div>
	</td>
</tr>
<?php 
	endif;
?>