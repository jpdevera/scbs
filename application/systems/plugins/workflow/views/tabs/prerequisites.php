<?php 
	$disable 			= "";

	if( $action == ACTION_VIEW )
	{
		$disable 		= "disabled";
	}
?>
<fieldset class="wcontent" data-page="4">

	<div class="p-md form-basic left-align">
		<div class="row m-b-n">
			<div class="title-content"><?php echo $work_tab_details['prerequisites']['name'] ?></div>
			<div class="fs-subtitle m-t-xs"><?php echo $work_tab_details['prerequisites']['description'] ?></div>
		</div>

		<div class="prerequisites-container">

		</div>
		
	</div>
	<input type="button" name="previous" class="previous action-button" value="Previous" />
	<?php 
		if( EMPTY( $disable ) ) :
	?>
	<input type="button" name="save-wizard" data-action="Prerequisites.save(animate_next, next_fs, current_fs);" class="save-wizard action-button" value="Save" />
	<?php 
		endif;
	?>
</fieldset>