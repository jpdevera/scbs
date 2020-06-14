<form id="forgot_password_form" class="p-lg">
	<input type="hidden" name="msg_post_as" id="msg_post_as" value="Send Message"/>
	<input type="hidden" name="msg_recipient[]" id="msg_recipient" value=""/>
	<div class="center-align m-t-md">
	  <div class="password-header center-align">
		<span><i class="material-icons">vpn_key</i></span>
		Forgot your password?
	  </div>
	  <div class="password-box">Enter your email address and we'll send you a link to reset your password.</div>
	</div>
	<div class="m-b-md m-t-md">
	  <div class="row m-n">
        <div class="col s2">&nbsp;</div>
        <div class="col s8">
			<div class="input-field m-n">
				<i class="material-icons prefix">email</i>
				<input type="text" class="m-b-n" name="email" id="email" data-parsley-required="true" data-parsley-maxlength="255" data-parsley-type="email" data-parsley-trigger="keyup" placeholder="E-mail address"/>
			</div>			  
			<div class="center-align m-t-lg">
				<button type="submit" class="waves-effect waves-light btn" id="forgot_password_btn" name="forgot_password_btn" data-btn-action="<?php echo BTN_EMAILING ?>">Submit</button>
				<a href="javascript:;" onclick='$("#modal_forgot_pw").modal("close");' class="modal-action modal-close m-t-md inline">&larr; Back to account login.</a>
			</div>		  
		</div>			  
        <div class="col s2">&nbsp;</div>
      </div>	
	</div>
	
</form>