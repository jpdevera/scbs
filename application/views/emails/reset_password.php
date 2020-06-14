<?php 
$base_email = base64_url_encode($email);
$base_salt = base64_url_encode($salt);
?>
<div style="width:85%; margin:0 auto;">
  <div style="background:#333333; padding:20px 30px; text-align:center;"><img src="<?php echo $logo ?>" height="40" alt="logo" /></div>
  <div style="background:#f6f6f6; padding:30px;">
	<div style="font-size:20px; color: #222; margin:10px 0; font-weight:300; text-align:center;">
		<?php echo $email_subject ?>
	</div>
	
	<p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0 20px; line-height:22px; text-align:center; color: #222;">
	  The <?php echo $system_name ?> has received a request<br/>to reset the password for your account, <?php echo $email ?>.
	</p>
	<div style="border:1px solid #E3DEB5; background:#FBF7DC; border-radius:2px; padding:15px; word-break:keep-all; margin-top:20px;">
	  <p style="font-size:14px; font-family:'Open Sans', arial;margin:0 0 20px; line-height:25px;">
	    To reset your password, click on the link below (or copy and paste the URL into your browser).<br/>
		<a style="display:block; width:100%; word-break:break-all;" href="<?php echo base_url() ?>forgot_password/reset/<?php echo $base_email ?>/<?php echo $base_salt ?>/"><?php echo base_url() ?>forgot_password/reset/<?php echo $base_email ?>/<?php echo $base_salt ?></a>
	  </p>
	  <p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0 0; line-height:25px;">
	    If this was a mistake, please ignore this email. Your password won't be changed until you access the link above and create a new one.
	  </p>
    </div>
	<p style="margin-top:20px; font-size:14px; font-family:'Open Sans', arial; line-height:25px;">Sincerely,<br/>The <?php echo $system_name ?> Team</p>	
	<p style="font-size:13px; color:#999; text-shadow: 0 0 2px #fff; font-family:'Open Sans', arial;margin:30px 0 0; line-height:25px;">
	  This email is sent automatically by the system, please do not reply directly. 
	</p>
  </div>
</div>
