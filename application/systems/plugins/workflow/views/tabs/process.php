<?php 
	$workflow_name 		= "";
	$workflow_desc 		= "";
	$appendable_fl 		= ENUM_NO;
	$active_fl 			= NULL;

	$check_appendable 	= "";
	$check_active 		= "checked";

	$disable 			= "";

	if( $action == ACTION_VIEW )
	{
		$disable 		= "disabled";
	}

	if( !EMPTY( $workflow_details ) )
	{
		$workflow_name 	= ( ISSET( $workflow_details['workflow_name'] ) ) ? $workflow_details['workflow_name'] : '';
		$workflow_desc 	= ( ISSET( $workflow_details['description'] ) ) ? $workflow_details['description'] : '';

		$appendable_fl  = ( ISSET( $workflow_details['appendable_flag'] ) ) ? $workflow_details['appendable_flag'] : ENUM_NO;
		$active_fl  	= ( ISSET( $workflow_details['active_flag'] ) ) ? $workflow_details['active_flag'] : ENUM_NO;
	}

	if( $appendable_fl == ENUM_YES )
	{
		$check_appendable = "checked";
	}


	if( $active_fl == ENUM_YES )
	{
		$check_active 	= "checked";
	}
	else if( $active_fl == ENUM_NO )
	{
		$check_active 	= "";
	}
?>

<fieldset class="wcontent" data-page="1">

	<div class="p-md form-basic left-align">
		<div class="row m-b-n">
			<div class="title-content"><?php echo $work_tab_details['process']['name'] ?></div>
			<div class="fs-subtitle m-t-xs"><?php echo $work_tab_details['process']['description'] ?></div>
		</div>
		<div class="row m-b-n">
			<div class="col s12 m-n p-n m-t-md">
				<div class="input-field">
					<label class="label active required" for="process_name">Name</label>
					<div>
						<input type="text" data-parsley-maxlength="100" name="process_name" data-parsley-required="true" data-parsley-group="fieldset-1" <?php echo $disable ?> data-parsley-trigger="keyup" value="<?php echo $workflow_name ?>" id="process_name">
					</div>
				</div>
			</div>
		</div>

		<div class="row m-b-n">
			<div class="col s12 m-n p-n p-t-md m-t-md">
				<div class="input-field">
					<label class="label active" for="process_name">Description</label>
					<div>
						<textarea class="materialize-textarea" data-parsley-maxlength="255" data-parsley-group="fieldset-1" data-parsley-trigger="keyup" <?php echo $disable ?> name="process_description"><?php echo $workflow_desc ?></textarea>	
					</div>
				</div>
			</div>
		</div>

		<div class="row p-b-sm">
			<div class="col s6 m-n p-n p-t-md m-t-md">
				<div class="input-field">
					<!-- <label class="label active" for="process_name">Is appendable?</label> -->
				 	<input type="checkbox" <?php echo $disable ?> <?php echo $check_appendable ?> class="labelauty" name="is_appendable" id="is_appendable_id" value="" data-labelauty="Cannot be attached to another process|Can be attached to another process" />
				</div>
			</div>
			<div class="col s4 m-n p-n p-t-md m-t-md">
				<div class="input-field">					
				 	<input type="checkbox" <?php echo $disable ?> <?php echo $check_active ?> class="labelauty" name="active_flag" id="active_flag_id" value="" data-labelauty="Inactive|Active" />
				</div>
			</div>
		</div>
	</div>
	<input type="button" name="cancel" onclick="Workflow.cancel();" class="cancel action-button" value="Cancel" />
	<?php 
		if( EMPTY( $disable ) ) :
	?>
	<input type="button" name="save-wizard" data-action="Process.save(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Save" />
	<?php 
		endif;
	?>
	<input type="button" name="next" data-disable="<?php echo $disable ?>" id="process_save" data-action="Process.save(animate_next, next_fs, current_fs, self);" class="next action-button" value="Next" />
</fieldset>