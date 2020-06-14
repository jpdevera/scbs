<div class="p-lg">
	<input type="hidden" name="id" value="<?php echo $id ?>">
	<input type="hidden" name="key" value="<?php echo $key ?>">
	
	<div class="center-align m-t-md">
	  <div class="password-header center-align">
		<span><i class="material-icons">lock_open</i></span>
		Reset Password
	  </div>
	  <div class="password-box">Please enter and confirm your new password to access your account.</div>
	</div>
	
	<div class="m-b-md m-t-md">
	  <div class="row m-n">
        <div class="col s2">&nbsp;</div>
        <div class="col s8">
			<div class="input-field m-n">
				<i class="material-icons prefix">lock_outline</i>
				<input type="password" class="m-b-n" name="password" id="password" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-pass="true" placeholder="New Password"/>
			</div>	  
		</div>			  
        <div class="col s2">&nbsp;</div>
      </div>
	  <div class="row m-n">
        <div class="col s2">&nbsp;</div>
        <div class="col s8">
			<div class="input-field m-n">
				<i class="material-icons prefix">lock_outline</i>
				<input type="password" class="m-b-n" name="password2" id="password2" data-parsley-required="true" data-parsley-minlength="8" data-parsley-equalto="#password" data-parsley-equalto-message="Confirm New Password and New Password do not match"  data-parsley-trigger="keyup" placeholder="Confirm New Password"/>
			</div>			  
			<div class="center-align m-t-lg">
				<button type="submit" name="reset_password_btn" id="reset_password_btn" class="modal-action modal-close waves-effect waves-light btn" data-btn-action="Updating Password">Update Password</button>
			</div>		  
		</div>			  
        <div class="col s2">&nbsp;</div>
      </div>	
	</div>
</div>