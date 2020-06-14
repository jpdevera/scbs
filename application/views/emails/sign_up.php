<div style="width:90%; margin:0 auto;">
	<div style="padding:20px 30px; text-align:center;">
		<img src="<?php echo $logo; ?>" height="40" alt="logo" />
	</div>
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td width="35%" style="
			background:url('<?php echo base_url() . PATH_IMAGES ?>welcome_email_bg.jpg') no-repeat; 
			background-size: auto 100%;
		">&nbsp;</td>
		<td width="65%" style="vertical-align:top; padding: 30px 20px 20px; background: #2c2d2d; color: #fff;">
			<p style="font-size:15px; font-family: verdana; line-height:22px; text-transform:uppercase; margin:0">
				Thanks for<br/>Signing Up with
			</p>
			<p style="font-size:20px; font-family:verdana; text-transform:uppercase; margin:5px 0; font-weight:bold; color: #ae4b52;">
				<?php echo $system_name ?>
			</p>
			<p style="font-size: 14px; margin: 30px 0px 40px; line-height: 20px; font-family: verdana;">
				Thank you for registering to the <?php echo $system_name ?>! Your registration awaits approval by the site administrator. 
				Once approved or denied, we will notify you again through email.
			</p>
			<p style="font-size:11px; font-family:verdana;margin:10px 0 5px; color: #8c8c8c;">
				* Some providers may mark our emails as spam, so make sure to check your spam or junk folder.
			</p>
		</td>
	</tr>
	</table>
	<p style="font-size:10px; color:#999; text-shadow: 0 0 2px #fff; font-family:verdana;margin:10px 0 0; text-transform: uppercase; text-align:center; font-weight:bold; ">
		This email is sent automatically by the system, please do not reply directly. 
	</p>
</div>
