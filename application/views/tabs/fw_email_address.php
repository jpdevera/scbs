<div class="p-md form-basic left-align">
	<div class="row m-b-n">
		<div class="title-content font-normal">Email Address</div>
		<div class="fs-subtitle m-t-sm">Please provide your registered email address for account recovery.</div>
	</div>
	<div class="row m-b-n m-t-md">
		<div class="col s12 m-t-md black-text">
			<!-- <div class="input-field"> -->
				<label class="label active required" for="email">Email Address <span style="color:#CF5866">*</span></label>
				<!-- <div> -->
					<input type="text" name="email" <?php echo $client_val_email['email'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="" id="email" data-parsley-existsemail="true">
				<!-- </div> -->
			<!-- </div> -->
		</div>
	</div>
</div>
<input type="button" name="next" id="process_save" data-action="ForgotPw.move_email(animate_next, next_fs, current_fs, self);" class="next action-button" value="Next" />