<?php 
	$disable 	= '';

	if( $action == ACTION_VIEW )
	{
		$disable = 'disabled';
	}
?>
<div class="stages-container">
<input type="hidden" value="<?php echo $stage_cnt ?>" id="stage_cnt">
<?php 
	if( !EMPTY( $workflow_stages ) ) :
?>
	<?php 
		foreach( $workflow_stages as $st_k => $w_s ) :

			$checked_skip 	= "";
			$hid_skip_val 	= "";

			$stage_id 		= base64_url_encode( $w_s['workflow_stage_id'] );
			$stage_salt 	= gen_salt();
			$stage_token 	= in_salt( $w_s['workflow_stage_id'], $stage_salt );
			$stage_url 		= $stage_id.'/'.$stage_salt.'/'.$stage_token;
			$stage_obj 		= array(
				'stage_id'		=> $stage_id,
				'stage_salt'	=> $stage_salt,
				'stage_token'	=> $stage_token
			);

			$stage_json 	= json_encode( $stage_obj );

			$skip_fl 		= ENUM_NO;

			$skip_fl  		= ( ISSET( $w_s['skip_flag'] ) ) ? $w_s['skip_flag'] : ENUM_NO;

			if( $skip_fl == ENUM_YES )
			{
				$checked_skip = "checked";
				$hid_skip_val = "1";
			}

	?>
	<div id="stages-div" class="row m-b-n ul-collapsible stages-div" data-sequence="<?php echo $w_s['sequence_no'] ?>">
		<ul class="collapsible m-b-n" data-collapsible="expandable">
			<li>
			 	<div class="collapsible-header title-content-14 active">
			 		<?php 
	 					if( EMPTY( $disable ) ) :
	 				?>
			 		<i class="material-icons handle">drag_handle</i>
			 		<?php 
			 			endif;
			 		?>
			 			<div class="header-text">
			 				Stage <?php echo $w_s['sequence_no'] ?>
			 				<?php 
			 					if( EMPTY( $disable ) ) :
			 				?>
			 				<a class="pull-right font-lg grey-text text-lighten-2 delete_row" onclick="content_workflow_stage_delete('Stage', '<?php echo $stage_url ?>', undefined, this, undefined, undefined, event ); return false;" data-delete_post='<?php echo $stage_json ?>' href="javascript:;">&#10006;</a>
			 				<?php 
			 					endif;
			 				?>
			 			</div>
			 	</div>
				<div class="collapsible-body form-basic">
					<div class="row">
						<div class="col s8">
							<div class="input-field">
								<label for="stage_name" class="active required">Stage Name</label>
								<div>
									<input type="hidden" class="stage_sequence" name="stage_sequence[]" value="<?php echo $w_s['sequence_no'] ?>" id="stage_sequence">
									<input type="hidden" class="workflow_stage_inp" name="workflow_stage[]" value="<?php echo $stage_id ?>">
									<input type="hidden" class="workflow_stage_salt_inp" name="workflow_stage_salt[]" value="<?php echo $stage_salt ?>">
									<input type="hidden" class="workflow_stage_token_inp" name="workflow_stage_token[]" value="<?php echo $stage_token ?>">
									<input type="text" <?php echo $disable ?> name="stage_name[]" data-parsley-maxlength="100" value="<?php echo $w_s['stage_name'] ?>" data-parsley-group="fieldset-2" data-parsley-required="true" data-parsley-trigger="keyup" id="stage_name">
								</div>
							</div>
						</div>
						<div class="col s4">
							<div class="input-field">
								<!-- <label class="label active" for="is_skippable_id_<?php echo $st_k ?>">Is skippable?</label> -->
								<input type="hidden" class="skippable_hid" name="skippable_hid[]" value="<?php echo $hid_skip_val ?>">
			 					<input type="checkbox" <?php echo $disable ?> class="labelauty skippable_check" name="is_skippable[]" id="is_skippable_id_<?php echo $st_k ?>" value="" data-labelauty="Non-skippable|Skippable" <?php echo $checked_skip ?> />
							</div>
						</div>
					</div>

				</div>
			</li>
		</ul>
	</div>
	<?php 
		endforeach;
	?>

<?php 
	else :
?>
<div id="stages-div" class="row m-b-n ul-collapsible stages-div" data-sequence="1">
	<ul class="collapsible m-b-n" data-collapsible="expandable">
		<li>
		 	<div class="collapsible-header title-content-14 active">
		 		<?php 
 					if( EMPTY( $disable ) ) :
 				?>
		 		<i class="material-icons handle">drag_handle</i>
		 		<?php 
		 			endif;
		 		?>
		 			<div class="header-text">
		 				Stage 1
		 				<?php 
		 					if( EMPTY( $disable ) ) :
		 				?>
		 				<a class="pull-right font-lg grey-text text-lighten-2 delete_row" href="javascript:;">&#10006;</a>
		 				<?php 
		 					endif;
		 				?>
		 			</div>
		 	</div>
			<div class="collapsible-body form-basic">
				<div class="row">
					<div class="col s8">
						<div class="input-field">
							<label for="stage_name" class="active required">Stage Name</label>
							<div>
								<input type="hidden" class="stage_sequence" name="stage_sequence[]" value="1" id="stage_sequence">
								<input type="hidden" class="workflow_stage_inp" name="workflow_stage[]" value="">
								<input type="hidden" class="workflow_stage_salt_inp" name="workflow_stage_salt[]" value="">
								<input type="hidden" class="workflow_stage_token_inp" name="workflow_stage_token[]" value="">
								<input type="text" <?php echo $disable ?> name="stage_name[]" data-parsley-maxlength="100" data-parsley-group="fieldset-2" data-parsley-required="true" data-parsley-trigger="keyup" id="stage_name">
							</div>
						</div>
					</div>
					<div class="col s4">
						<div class="input-field">
							<!-- <label class="label active" for="is_skippable_id">Is skippable?</label> -->
							<input type="hidden" class="skippable_hid" name="skippable_hid[]" value="">
		 					<input type="checkbox" <?php echo $disable ?> class="labelauty skippable_check" name="is_skippable[]" id="is_skippable_id" value="" data-labelauty="Non-skippable|Skippable" />
						</div>
					</div>
				</div>

			</div>
		</li>
	</ul>
</div>
<?php 
	endif;
?>
</div>