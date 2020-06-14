<?php   
  $base_email = base64_url_encode($email);
  $base_id = base64_url_encode($id);
  $url = 'auth/verify/' . $base_id . '/' .$base_email;
?>
<div style="width:85%; margin:0 auto;">
  <div style="background:#f2c65d; padding:20px 30px;"><img src="<?php echo base_url() . PATH_IMAGES ?>logo_new2.png" height="40" alt="logo" /></div>
  <div style="background:#f6f6f6; padding:30px;">
	<p style="font-size:14px; font-family:'Open Sans', arial; margin-bottom:20px;">Hi <?php echo $name ?>,</p>
	
	<?php if($status == DISAPPROVED){ ?>
		<p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0 20px; line-height:25px;">
		  We regret to inform you that your account on <?php echo $system_name ?> has been denied. Please refer to the below comments for details.
		  
		  <div style="background:rgba(255, 255, 255, 0.3); border-left:8px solid #ccc; padding:15px; margin-top:20px;">
			 <p style="font-style:italic; font-size:14px; font-family:'Open Sans', arial; margin:0 0 20px; line-height:25px;"><?php echo $reason; ?></p>
		  </div>
		</p>
	<?php }else{ ?>
		<p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0 20px; line-height:25px;">
		Your registration to the <?php echo $system_name ?> has been approved.
		</p>
		<div style="border:1px solid #E3DEB5; background:#FBF7DC; border-radius:2px; padding:15px; word-break:keep-all; margin-top:20px;">

		  <?php 
			if( ISSET( $sign_up_login ) AND !EMPTY($sign_up_login) ) :
		  ?>	
		  <p style="font-size:14px; font-family:'Open Sans', arial;margin:0 0 20px; line-height:25px;">
			Please click on the link below to login.<br/>

			<a style="word-wrap:break-word" href="<?php echo base_url() ?>" title="Activate Account" target="_blank"><?php echo base_url() ?></a>
		  </p>
		  <?php 
		  	else :
		  ?>
		  <p style="font-size:14px; font-family:'Open Sans', arial;margin:0 0 20px; line-height:25px;">
			Please click on the authentication link below to activate your account.<br/>

			<a style="word-wrap:break-word" href="<?php echo base_url() . $url ?>" title="Activate Account" target="_blank"><?php echo base_url() . $url ?></a>
		  </p>
		  <?php 
		  	endif;
		  ?>
		  <!--p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px; word-break:keep-all;">
		    <strong>Email</strong>&nbsp;&nbsp;&nbsp;</?php echo $email ?><br/>
 		    <strong>Password</strong>&nbsp;&nbsp;&nbsp;</?php echo $raw_password ?>
		  </p-->
		  <p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0 0; line-height:25px;">
			If clicking on the link doesn't work, copy and paste the URL  into the browser's address bar.
		  </p>	  
		</div>
	<?php } ?>
	<p style="margin-top:20px; font-size:14px; font-family:'Open Sans', arial; line-height:25px;">Sincerely,<br/>The <?php echo $system_name ?> Team</p>	
	<p style="font-size:13px; color:#999; text-shadow: 0 0 2px #fff; font-family:'Open Sans', arial;margin:30px 0 0; line-height:25px;">
	  This email is sent automatically by the system, please do not reply directly. 
	</p>
  </div>
</div>
