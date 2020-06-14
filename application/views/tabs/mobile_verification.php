<?php 
	$mobile_authentication_factor_id = "";

	if( !EMPTY( $mob_ver_data ) )
	{
		$mobile_authentication_factor_id = base64_url_encode($mob_ver_data['authentication_factor_id']);
	}
?>
<div class="p-md form-basic left-align">
	<input type="hidden" id="mobile_authentication_factor_id" name="authentication_factor_id" value="<?php echo $mobile_authentication_factor_id ?>">
	<input type="hidden" id="temp_flag" name="temp_flag" value="1">
	<div class="row m-b-n">
		<div class="title-content font-normal">Mobile No. Verification</div>
		<div class="fs-subtitle m-t-sm" id="mobile_ver_title"><?php echo $mob_form_label ?></div>
	</div>
	<div class="row m-b-n m-t-md">
		<div class="col s6 m-t-md">
			<div class="input-field">
				<?php 
					if( EMPTY( $disable_mob_ver ) ) :
				?>
				<div id="mobile_inps">
					<label class="label active required" for="mobile_verification_code">Verfication Code</label>
					<div>
						<input type="text" name="mobile_verification_code" <?php echo $client_vc['mobile_verification_code'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="" id="mobile_verification_code">
					</div>
				</div>
				<?php 
					endif;
				?>
			</div>
		</div>
		<div class="col s3">
			<div class="input-field p-t-md m-b-xs">
				<a href="#" id="resend_mob_btn" class="<?php echo $hide_resend_mob ?>">Resend Code</a>
			</div>
		</div>
	</div>
</div>
<input type="button" name="previous" class="previous action-button" value="Previous" />
<?php 
	if( EMPTY( $disable_mob_ver ) ) :
?>
<!-- <input type="button" name="save-wizard" data-disable="<?php echo $disable_mob_ver ?>" data-action="SignUp.move_verification(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Save" /> -->
<?php 
	endif;
?>
<input type="button" name="next" id="process_save" style="width: 300px !important;" data-disable="<?php echo $disable_mob_ver ?>" data-action="SignUp.move_verification(animate_next, next_fs, current_fs, self);" class="next action-button" value="Set-up your <?php echo $system_title ?> account" />