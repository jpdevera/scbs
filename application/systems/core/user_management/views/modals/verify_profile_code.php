<?php 
		if( !EMPTY( $orig_params ) ) :
	?>
		<?php 
			foreach( $orig_params as $name => $val ) :
		?>
		<input name="<?php echo $name ?>" id="<?php echo $name ?>_inp" value="<?php echo $val ?>" type="hidden" />
	<?php 
		endforeach;
	?>
<?php 
	endif;
?>


<div class="form-basic p-md p-t-lg">
	<div class="row" >
		<div class="col s12">
			<div class="input-field">
				<label class="active required">New Email</label>
				<input type="text" data-parsley-trigger="keyup" <?php echo $client_side['new_email'] ?> name="new_email"  data-parsley-group="new_value" id="new_email"
				placeholder="Enter new email address" />
			</div>
		</div>
	</div>
	<div class="row" >
		<div class="col s5">
			<div class="input-field">
				<label class="active required">Code</label> 
				<input type="text" name="auth_code" id="auth_code" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup"
				placeholder="Enter code" />
			</div>
		</div>
	</div>
</div>
