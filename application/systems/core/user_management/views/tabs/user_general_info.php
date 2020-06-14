<?php 
$lname = "";
$fname = "";
$mname = "";
$nickname = "";
$female = "";
$male = "checked";
$job_title = "";
$contact_no = "";
$mobile_no = "";
$photo = "";

$receive_email = "checked";
$receive_sms = "checked";

if(ISSET($user)){
	$sp_status = get_sys_param_val(SYS_PARAM_STATUS, $user["status"]);
	$sp_gender = get_sys_param_val(SYS_PARAM_GENDER, $user["gender"]);
	
	$lname = (!EMPTY($user["lname"]))? $user["lname"] : "";
	$fname = (!EMPTY($user["fname"]))? $user["fname"] : "";
	$mname = (!EMPTY($user["mname"]))? $user["mname"] : "";
	
	$nickname = (!EMPTY($user["nickname"]))? $user["nickname"] : "";
	$female = ($sp_gender["sys_param_value"] == FEMALE)? "checked" : "";
	$male = ($sp_gender["sys_param_value"] == MALE)? "checked" : "";
	$job_title = (!EMPTY($user["job_title"]))? $user["job_title"] : "";
	$contact_no = (!EMPTY($user["contact_no"]))? $user["contact_no"] : "";
	$mobile_no = (!EMPTY($user["mobile_no"]))? $user["mobile_no"] : "";
	
	$photo = (!EMPTY($user["photo"]))? $user["photo"] : "";

	$receive_email = ($user["receive_email_flag"] == 'Y' ? "checked" : "");
	$receive_sms = ($user["receive_sms_flag"] == 'Y' ? "checked" : "");
}
?>
<input type="hidden" <?php echo $disabled_str ?> name="image" id="avatar" value="<?php echo $photo ?>"/>
<div class="panel m-b-lg white p-md">
	<div class="section-note warning" data-label="Warning">Processing of individual's information without proper authorization will violate R.A. 10173 Data Privacy Act.</div>
</div>
<div class="row m-b-n m-t-md">
	<div class="col s12">
		<div class="row m-b-n">
			<div class="col s5 p-l-n">
				<div class="input-field">
					<input <?php echo $disabled_str ?> type="text" name="lname" id="lname" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" value="<?php echo $lname ?>" class="white" />
					<label for="lname" class="required">Last Name</label>
				</div>
			</div>
			<div class="col s5">
				<div class="input-field">
					<input <?php echo $disabled_str ?> type="text" name="fname" id="fname" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" value="<?php echo $fname ?>" class="white"/>
					<label for="fname" class="required">First Name</label>
				</div>
			</div>
			<?php 
				if( !EMPTY( $users_mname_inp['sys_param_value'] ) ) :
			?>
			<div class="col s2 p-r-n">
				<div class="input-field">
					<input type="text" <?php echo $disabled_str ?> name="mname" id="mname" value="<?php echo $mname ?>" class="white"/>
					<label for="mname">Middle Initial</label>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="row m-b-n">
	<div class="col s5 p-t-md">
		<div class="input-field">
			<input type="text" <?php echo $disabled_str ?> name="nickname" id="nickname" value="<?php echo $nickname ?>" class="white"/>
			<label for="nickname">Nickname</label>
		</div>
	</div>
	<?php 
		if( !EMPTY( $users_gender_inp['sys_param_value'] ) ) :
	?>
	<div class="col s2 p-t-md">
		<div class="input-field">
			<label for="user_gender_male" class="active required">Sex</label>
			<input type="radio" class="labelauty" name="gender" id="user_gender_male" value="<?php echo MALE ?>" <?php echo $disabled_str ?> data-labelauty="Male" <?php echo $male ?>/>
		</div>
	</div>
	<div class="col s3 p-t-md p-l-n m-l-n-lg">
		<div class="input-field">
			<input type="radio" class="labelauty" name="gender" id="user_gender_female" value="<?php echo FEMALE ?>" <?php echo $disabled_str ?> data-labelauty="Female" <?php echo $female ?>/>
		</div>
	</div>
	<?php endif; ?>
</div>
<div class="row m-t-md">
	<div class="col s6">
		<div class="input-field">
			<input type="text" <?php echo $disabled_str ?> name="contact_no" id="contact_no" value="<?php echo $contact_no ?>" data-parsley-trigger="keyup" data-parsley-pattern="^[0-9-()+ ]+$" data-parsley-pattern-message="Telephone no. must contain parenthesis '()', dash '-', or numeric values only" class="white"/>
			<label for="contact_no">Telephone No.</label>
		</div>
	</div>
	<div class="col s6">
		<div class="input-field">
			<div class="input-group">
				<div class="input-group-addon">+ 63</div>
				<input type="text" <?php echo $disabled_str ?> name="mobile_no" id="mobile_no" value="<?php echo $mobile_no ?>" data-parsley-required="false" class="white"/>
				<label for="mobile_no">Mobile No.</label>
			</div>
		</div>
	</div>
</div>
<div class="row m-t-md">
	<?php 
		if( !EMPTY( $users_org_inp['sys_param_value'] ) ) :
	?>
	<div class="col s4">
		<div class="input-field">
			<label class="label block required">Main Organization</label>
			<select name="org" <?php echo $disabled_str ?> id="org" class="lazy-selectize main_org" data-extra_opt_function="Users.extra_opt_function(options);" placeholder="Select Agency" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-loadItemsUrl="<?php echo base_url().CORE_USER_MANAGEMENT.'/Users/get_lazy_orgs' ?>">
				<option value="">Select Agency</option>
				<?php 
					if( ISSET( $all_orgs ) AND !EMPTY( $all_orgs ) ) :
				?>	
					<?php 
						foreach( $all_orgs as $a_o ) :

							$sel_ma 	= ( !EMPTY( $main_orgs ) AND in_array($a_o['org_code'], $main_orgs, TRUE) ) ? 'selected' : '';

							$a_o_json 	= json_encode($a_o);
					?>
					<option data-data='<?php echo $a_o_json ?>' value="<?php echo $a_o['org_code'] ?>" <?php echo $sel_ma ?> ><?php echo $a_o['name'] ?></option>
					<?php 
						endforeach;
					?>
				<?php
					endif;
				?>
			</select>
		</div>
	</div>
	<?php 
		endif;
	?>
	<?php if( !EMPTY( $users_multiple_org ) ) : ?>
	<div class="col s4">
		<div class="input-field">
			<label class="label block">Other Organizations</label>
			<select name="other_orgs[]" <?php echo $disabled_str ?> data-extra_opt_function="Users.extra_opt_function(options);" id="other_orgs" class="lazy-selectize other_orgs" placeholder="Select Agency" data-parsley-required="false" multiple="multiple" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-loadItemsUrl="<?php echo base_url().CORE_USER_MANAGEMENT.'/Users/get_lazy_orgs' ?>">
				<option value="">Select Agency</option>
				<?php 
					if( ISSET( $all_orgs ) AND !EMPTY( $all_orgs ) ) :
				?>	
					<?php 
						foreach( $all_orgs as $o_o ) :

							$sel_oa 	= ( !EMPTY( $other_orgs ) AND in_array($o_o['org_code'], $other_orgs, TRUE) ) ? 'selected' : '';

							$o_o_json 	= json_encode($o_o);
					?>
					<option data-data='<?php echo $o_o_json ?>' value="<?php echo $o_o['org_code'] ?>" <?php echo $sel_oa ?> ><?php echo $o_o['name'] ?></option>
					<?php 
						endforeach;
					?>
				<?php
					endif;
				?>
			</select>
		</div>
	</div>
	<?php endif; ?>
	<?php 
		if( !EMPTY( $users_job_title_inp['sys_param_value'] ) ) :
	?>
	<div class="col s3">
		<div class="input-field m-t-md">
			<input type="text" <?php echo $disabled_str ?> name="job_title" id="job_title" value="<?php echo $job_title ?>" class="white"/>
			<label for="job_title">Job Title</label>
		</div>
	</div>
	<?php 
		endif;
	?>
</div>
<div class="row" id="avatar_upload_form">
	<div class="col s12">
		<h5 class="form-header">Display Image</h5>
		<div class="help-text">Select and upload your latest photo to help others recognize this account.</div>
		<div id="avatar_upload">Select File</div>
	</div>
</div>
<div class="row m-t-md">	
	<div class="col s12"><h5 class="form-subtitle">Notifications</h5></div>
</div>
<div class="row m-t-md">
	<!-- <div class="col s1">&nbsp;</div> -->
	<div class="col s6 p-t-md">
		<div class="input-field">
			<input type="checkbox" <?php echo $disabled_str ?> name="receive_sms" id="user_sms_notif" class="labelauty" data-labelauty="Receive notification alerts through SMS" value="1" <?php echo $receive_sms ?> />
			<label for="user_sms_notif" class="active">Notification alert settings</label>
		</div>
	</div>
	<div class="col s6 p-t-md">
		<div class="input-field">
			<input type="checkbox" <?php echo $disabled_str ?> name="receive_email" id="user_email_notif" class="labelauty" data-labelauty="Receive notification alerts through e-mail" value="1" <?php echo $receive_email ?> />
		</div>
	</div>
</div>