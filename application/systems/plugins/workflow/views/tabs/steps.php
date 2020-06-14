<?php 
	$disable 			= "";

	if( $action == ACTION_VIEW )
	{
		$disable 		= "disabled";
	}
?>
<fieldset class="wcontent" data-page="3">

	<div class="p-md form-basic left-align">
		<div class="row m-b-n">
			<div class="title-content"><?php echo $work_tab_details['steps']['name'] ?></div>
			<div class="fs-subtitle m-t-xs"><?php echo $work_tab_details['steps']['description'] ?></div>
		</div>

		<div class="steps-container">

		</div>
		
	</div>
	<input type="button" name="previous" class="previous action-button" value="Previous" />
	<?php 
		if( EMPTY( $disable ) ) :
	?>
	<input type="button" name="save-wizard" data-action="Steps.save(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Save" />
	<?php 
		endif;
	?>
	<input type="button" name="next" data-disable="<?php echo $disable ?>" data-action="Steps.save(animate_next, next_fs, current_fs, self);" class="next action-button" value="Next" />
</fieldset>