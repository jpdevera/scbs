<?php 
	$cols 	= 's6';

	if( $required_mobile )
	{
		$cols = 's4';
	}

	$auth_fac_email 	= base64_url_encode(AUTHENTICATION_FACTOR_EMAIL);
	$auth_fac_mobile 	= base64_url_encode(AUTHENTICATION_FACTOR_SMS);
?>
<div class="row m-b-n">
	<!-- <div class="title-content font-normal" id="email_help_text">Choose the mode of delivery for the verification code.</div> -->
	<div class="fs-subtitle m-t-sm hide" id="email_help_text">Choose the means of verification.</div>
</div>
<div class="tabs-wrapper full">
	<div>
		<ul class="tabs row">
			<li class="tab col <?php echo $cols ?>"><a class="" id="security_question_link" href="#tab_security_question" onclick="load_index('tab_security_question', 'Forgot_password/verification/0/'+$('#user_id_inp').val()+'/<?php echo $data_page_key ?>/1')">Security Question</a></li>
			<li class="tab col <?php echo $cols ?>"><a class="" id="email_link" href="#tab_email" onclick="load_index('tab_email', 'Forgot_password/verification/<?php echo $auth_fac_email ?>/'+$('#user_id_inp').val()+'/<?php echo $data_page_key ?>')">Thru Email</a></li>
			<?php 
				if( $required_mobile ) :
			?>
			<li class="tab col s4"><a href="#tab_mobile" id="mobile_link" onclick="load_index('tab_mobile', 'Forgot_password/verification/<?php echo $auth_fac_mobile ?>/'+$('#user_id_inp').val()+'/<?php echo $data_page_key ?>')">Thru Mobile</a></li>
			<?php 
				endif;
			?>
		</ul>
	</div>
</div>

<div id="tab_security_question" class="tab-content col s12"></div>
<div id="tab_email" class="tab-content col s12"></div>
<?php 
	if( $required_mobile ) :
?>
<div id="tab_mobile" class="tab-content col s12"></div>
<?php 
	endif;
?>

<input type="button" name="next" id="process_save" data-action="ForgotPw.move_verify(animate_next, next_fs, current_fs, self);" class="next action-button" value="Next" />