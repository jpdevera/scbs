<?php 
	$disable 			= "";

	if( $action == ACTION_VIEW )
	{
		$disable 		= "disabled";
	}
?>
<fieldset class="wcontent" data-page="2">

	<div class="p-md form-basic left-align">
		<div class="row m-b-n">
			<div class="title-content"><?php echo $work_tab_details['stages']['name'] ?></div>
			<div class="fs-subtitle m-t-xs"><?php echo $work_tab_details['stages']['description'] ?></div>
		</div>

		<div class="stages-container-load">

		</div>

		<div class="row m-n form-basic">
			<!-- <div class=""> -->
				<div class="col s8 p-n">
					<div class="left-align ">
						<?php 
							if( EMPTY( $disable ) ) :
						?>
						<button type="button" id="stages_add_btn" class="btn-rounded btn btn-secondary white  cyan-text accent-4"><i class="material-icons">library_add</i>Add a Stage</button>
						<?php 
							endif;
						?>
					</div>
				</div>
			<!-- 	<div class="col s4 p-n">
				<div class="input-field">
					<div class="right-align m-t-sm">
						<input class="search-box" id="search_box_mod" type="text" value="" placeholder="Search" />
					</div>
				</div>
			</div> -->
				
			<!-- </div> -->
		</div>
		
	</div>
	<input type="button" name="previous" class="previous action-button" value="Previous" />
	<?php 
		if( EMPTY( $disable ) ) :
	?>
	<input type="button" name="save-wizard" data-action="Stages.save(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Save" />
	<?php 
		endif;
	?>
	<input type="button" data-disable="<?php echo $disable ?>" name="next" data-action="Stages.save(animate_next, next_fs, current_fs, self);" class="next action-button" value="Next" />
</fieldset>