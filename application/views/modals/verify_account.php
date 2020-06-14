<div class="p-lg">
	<input type="hidden" name="user_id" value="<?php echo $id ?>"/>
	<input type="hidden" name="status_id" value="<?php echo ACTIVE ?>"/>
	<input class="none" type="password" />
	<div class="center-align m-t-md">
	  <div class="password-header center-align">
		<span><i class="material-icons">person</i></span>
		Welcome to <?php echo get_setting(GENERAL, "system_title") ?>!
	  </div>
	  <div class="password-box">Create a username and password for your account to start accessing the system.</div>
	</div>
	<div class="m-b-md m-t-md">
	  <div class="row m-n">
        <div class="col s2">&nbsp;</div>
        <div class="col s8">
			<div class="input-field m-n">
				<i class="material-icons prefix">person_outline</i>
				<input type="text" class="m-b-n" data-parsley-username="true" name="username" id="username" data-parsley-required="true" data-parsley-trigger="keyup" placeholder="Username" value=""/>
			</div>	  
		</div>			  
        <div class="col s2">&nbsp;</div>
      </div>	
	  <div class="row m-n">
        <div class="col s2">&nbsp;</div>
        <div class="col s8">
			<div class="input-field m-n">
				<i class="material-icons prefix">lock_outline</i>
				<input type="password" class="m-b-n" name="password" id="password" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-pass="#username" placeholder="Password" value=""/>
			</div>	  
		</div>			  
        <div class="col s2">&nbsp;</div>
      </div>	
	  <div class="row m-n">
        <div class="col s2">&nbsp;</div>
        <div class="col s8">
			<div class="input-field m-n">
				<i class="material-icons prefix">lock_outline</i>
				<input type="password" class="m-b-n" name="confirm_password" id="confirm_password" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-equalto-message="Passwords don't match." data-parsley-equalto="#password" placeholder="Confirm Password" value=""/>
			</div>			  
			<div class="center-align m-t-lg">
				<button type="submit" class="waves-effect waves-light btn" id="continue_btn" name="continue_btn" data-btn-action="Getting Started">Get Started &rarr;</button>
			</div>		  
		</div>			  
        <div class="col s2">&nbsp;</div>
      </div>	
	</div>
	
</div>