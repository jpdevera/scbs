
<div style="width:85%; margin:0 auto;">
  <div style="background:#333333; padding:20px 30px; text-align:center;"><img src="<?php echo $logo ?>" height="40" alt="logo" /></div>
  <div style="background:#f6f6f6; padding:30px;">
	<div style="font-size:20px; color: #222; margin:10px 0; font-weight:300; text-align:center;">
		<div class="m-b-xs">Hi <span style="font-weight:bold"><?php echo $name ?></span>!</div>
	</div>
	<p style="font-size:14px; font-family:'Open Sans', arial;margin:0 0 20px; line-height:22px; text-align:center; color: #333">
	  A device or location we haven't seen before is requesting access to your account.<br/>
	</p>
	<div style="border:1px solid #E3DEB5; background:#FBF7DC; border-radius:2px; padding:15px; word-break:keep-all; margin-top:20px;text-align: center;">
	  <p style="font-size:14px; font-family:'Open Sans', arial;margin:10px 0; line-height:25px; word-break:keep-all;text-align: center;">

	  	<strong>IP Adress</strong>&nbsp;&nbsp;&nbsp;<?php echo $ip_address ?><br/>
		<strong>Platform</strong>&nbsp;&nbsp;&nbsp;<?php echo $platform ?><br/>
		<strong>Browser</strong>&nbsp;&nbsp;&nbsp;<?php echo $browser ?><br/>
		<strong>Location</strong>&nbsp;&nbsp;&nbsp;<?php echo $location ?><br/>
		<!-- <strong>Password</strong>&nbsp;&nbsp;&nbsp;<?php echo $raw_password ?> -->

	  	<a class="btn waves-effect green lighten-2" style="border: none;
			  border-radius: 2px;
			  display: inline-block;
			  height: 36px;
			  line-height: 36px;
			  padding: 0 2rem;
			  text-transform: uppercase;
			  vertical-align: middle;
			  -webkit-tap-highlight-color: transparent;
			  background: #1E90FF;
			  color : #fff;
			  text-decoration: none;" 
			  href="<?php echo $auth_link ?>">
			I authorize</a>
	  	
	  </p>
	  
	</div>
	<p style="margin-top:20px; font-size:14px; font-family:'Open Sans', arial; line-height:25px;">Sincerely,<br/>The <?php echo $system_name ?> Team</p>	
	<p style="font-size:13px; color:#999; text-shadow: 0 0 2px #fff; font-family:'Open Sans', arial;margin:30px 0 0; line-height:25px;">
	  This email is sent automatically by the system, please do not reply directly. 
	</p>
  </div>
</div>
