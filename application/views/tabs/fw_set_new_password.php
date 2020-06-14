<div class="p-md form-basic left-align">
	<input class="none" type="password" />
	<div class="row m-b-n">
		<div class="title-content font-normal">Set Password</div>
		<div class="fs-subtitle m-t-sm">Set your new Password.</div>
	</div>
	
	<div class="row m-b-n m-t-md black-text">
		<div class="col s6 m-t-md">
			<!-- <div class="input-field"> -->
				<label class="label active required" for="password">Password</label>
				<div>
					<input type="password" name="password" <?php echo $client_acc['password'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-pass="#username" data-parsley-trigger="keyup" value="" id="password">
				</div>
			<!-- </div> -->
		</div>
		<div class="col s6 m-t-md">
			<!-- <div class="input-field"> -->
				<label class="label active required" for="confirm_password">Confirm Password</label>
				<div>
					<input type="password" name="confirm_password" <?php echo $client_acc['confirm_password'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" data-parsley-equalto-message="Passwords don't match." data-parsley-equalto="#password" value="" id="confirm_password">
				</div>
			<!-- </div> -->
		</div>
	</div>
</div>
<input type="button" name="save-wizard" data-action="ForgotPw.move_acc_details(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Save" />