<?php 
	$read_us 	= '';
	
	$usern = $username_gen;

	if( $set_auto_username )
	{
		$read_us = 'readonly="readonly"';
	}
	
?>
<div class="p-md form-basic left-align">
	<input class="none" type="password" />
	<div class="row m-b-n">
		<div class="title-content font-normal">Account Detail</div>
		<div class="fs-subtitle m-t-sm">One step away - set username and password.</div>
	</div>
	<div class="row m-b-n m-t-md">
		<div class="col s6 m-t-md">
			<div class="input-field">
				<label class="label active required" for="username">Username</label>
				<div>
					<input type="text" <?php echo $read_us ?> name="username" data-parsley-username="true" <?php echo $client_acc['username'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $usern ?>" id="username">
				</div>
			</div>
		</div>
	</div>
	<div class="row m-b-n m-t-md">
		<div class="col s6 m-t-md">
			<div class="input-field">
				<label class="label active required" for="password">Password</label>
				<div>
					<input type="password" name="password" <?php echo $client_acc['password'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-pass="#username" data-parsley-trigger="keyup" value="" id="password">
				</div>
			</div>
		</div>
		<div class="col s6 m-t-md">
			<div class="input-field">
				<label class="label active required" for="confirm_password">Confirm Password</label>
				<div>
					<input type="password" name="confirm_password" <?php echo $client_acc['confirm_password'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" data-parsley-equalto-message="Passwords don't match." data-parsley-equalto="#password" value="" id="confirm_password">
				</div>
			</div>
		</div>
	</div>
</div>
<input type="button" name="previous" class="previous action-button" value="Previous" />
<input type="button" name="save-wizard" style="width: 300px !important;" data-action="SignUp.move_acc_details(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Set-up your <?php echo $system_title ?> account" />