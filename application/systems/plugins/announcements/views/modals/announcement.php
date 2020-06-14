<?php 
	
	$description 	= '';

	if( !EMPTY( $details ) )
	{
		$description 	= ( ISSET( $details['description'] ) AND !EMPTY( $details['description'] ) ) ? html_entity_decode( $details['description'] ) : '';

	}

	if( !EMPTY( $orig_params ) ) :
?>
	<?php 
		foreach( $orig_params as $name => $val ) :
	?>
	<input type="hidden" id="<?php echo $name ?>_inp" name="<?php echo $name ?>" value="<?php echo $val ?>">
	<?php 
		endforeach;
	?>
<?php 
	endif;
?>

<div>
	<div class="form-float-label">
		<div class="row" >
			<div class="col s12">
				<div class="input-field">
					<label class="active required">Announcement</label>
					<textarea <?php echo $disabled ?> <?php echo $client_side['announcement'] ?> data-parsley-trigger="change" name="announcement" id="write_announcement_textarea" class="materialize-textarea" placeholder = "Write your announcement here"><?php echo $description ?></textarea>
				</div>
			</div>
		</div>
	</div>
</div>