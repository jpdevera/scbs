<div style="width:85%; margin:0 auto;">
  <div style="background:#333333; padding:20px 30px; text-align:center;"><img src="<?php echo $logo ?>" height="40" alt="logo" /></div>
  <div style="background:#f6f6f6; padding:30px;">
	<div style="font-size:20px; color: #222; margin:10px 0; font-weight:300; text-align:center;">
		<div class="m-b-xs">Hi <span style="font-weight:bold"><?php echo $name ?></span>!</div>
		<?php echo $email_subject ?>
	</div>
	<p style="font-size:14px; font-family:'Open Sans', arial;margin:0 0 20px; line-height:22px; text-align:center; color: #333">
	  A user account with <strong>Username</strong>&nbsp;&nbsp;&nbsp;<?php echo $username ?> has been created for you by <?php echo $created_by ?>.<br/>Please click the link below to change your password.
	</p>
	<div style="border:1px solid #E3DEB5; background:#FBF7DC; border-radius:2px; padding:15px; word-break:keep-all; margin-top:20px;">
	  <p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px; word-break:keep-all;">
		<a style="word-wrap:break-word" href="<?php echo $change_password_url ?>" title="Change Password" target="_blank"><?php echo $change_password_url ?></a>
	  </p>
	</div>
	<p style="margin-top:20px; font-size:14px; font-family:'Open Sans', arial; line-height:25px;">Sincerely,<br/>The <?php echo $system_name ?> Team</p>	
	<p style="font-size:13px; color:#999; text-shadow: 0 0 2px #fff; font-family:'Open Sans', arial;margin:30px 0 0; line-height:25px;">
	  This email is sent automatically by the system, please do not reply directly. 
	</p>
  </div>
</div>
