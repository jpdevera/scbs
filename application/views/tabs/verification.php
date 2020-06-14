<div class="p-md form-basic left-align">
	<input type="hidden" id="email_authentication_factor_id" name="authentication_factor_id" value="<?php echo $auth_data['authentication_factor_id'] ?>">
	<div class="row m-b-n">
		<div class="title-content font-normal"><?php echo $configs['header_txt'] ?> Verification</div>
		<div class="fs-subtitle m-t-sm" id="email_ver_title">Get verification code thru <?php echo $configs['header_txt'] ?></div>
	</div>
	<div class="row m-b-n m-t-md">
		<div class="col s12 m-t-md black-text">
			<!-- <div class="input-field"> -->
				
				<div id="email_inps">
					<label class="label active required" for="auth_code">Verfication Code</label>
					<div>
						
						<input type="text" name="verification_code" <?php echo $client_vc['verification_code'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="" id="auth_code">
						
					</div>
				</div>

			<!-- </div> -->
		</div>
		<div class="col s3">
			<div class="input-field p-t-md m-b-xs">
				<a href="#" id="resend_btn" class="resend_btn">Resend Code</a>
			</div>
		</div>
	</div>
</div>