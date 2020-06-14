<?php 
$lname 		= "";
$fname 		= "";
$mname 		= "";
$nickname 	= "";
$female 	= "";
$male 		= "";
$job_title 	= "";
$ext_name 	= "";

$receive_email 	= "checked";
$receive_sms 	= "checked";

if( ISSET( $params['first_name'] ) )
{
	$fname 		= $params['first_name'];
}

if( ISSET( $params['last_name'] ) )
{
	$lname 		= $params['last_name'];
}

if( ISSET( $params['email'] ) )
{
	$email 		= $params['email'];
}

$api_sign_up = 0;

if( ISSET( $params['api_sign_up'] ) )
{
	$api_sign_up 		= $params['api_sign_up'];
}

$checked_subs 	= '';

if(ISSET($user_details) AND !EMPTY($user_details) )
{
	$sp_status = get_sys_param_val(SYS_PARAM_STATUS, $user_details["status"]);
	$sp_gender = get_sys_param_val(SYS_PARAM_GENDER, $user_details["gender"]);
	
	$lname = (!EMPTY($user_details["lname"]))? $user_details["lname"] : "";
	$fname = (!EMPTY($user_details["fname"]))? $user_details["fname"] : "";
	$mname = (!EMPTY($user_details["mname"]))? $user_details["mname"] : "";
	$ext_name 	= (!EMPTY($user_details["ext_name"]))? $user_details["ext_name"] : "";
	
	$nickname 	= (!EMPTY($user_details["nickname"]))? $user_details["nickname"] : "";
	$female 	= ($sp_gender["sys_param_value"] == FEMALE)? "checked" : "";
	$male 		= ($sp_gender["sys_param_value"] == MALE)? "checked" : "";
	$job_title 	= (!EMPTY($user_details["job_title"]))? $user_details["job_title"] : "";

	$receive_email 	= ($user_details["receive_email_flag"] == 'Y' ? "checked" : "");
	$receive_sms 	= ($user_details["receive_sms_flag"] == 'Y' ? "checked" : "");

	$api_sign_up 		= $user_details['sign_up_api'];

	$email 		= (!EMPTY($user_details["email"]))? $user_details["email"] : "";

	$product_subscription_notif_flag = (!EMPTY($user_details["product_subscription_notif_flag"]))? $user_details["product_subscription_notif_flag"] : "";

	if( !EMPTY( $product_subscription_notif_flag ) AND $product_subscription_notif_flag == ENUM_YES )
	{
		$checked_subs = 'checked';
	}
}

$org_code 				= '';
$company_name 			= '';
$company_short_name 	= '';

if(ISSET($org_details) AND !EMPTY($org_details) )
{
	$org_code = (!EMPTY($org_details["org_code"]))? $org_details["org_code"] : "";
	$company_name = (!EMPTY($org_details["name"]))? $org_details["name"] : "";
	$company_short_name = (!EMPTY($org_details["short_name"]))? $org_details["short_name"] : "";
}

?>

<input type="hidden" name="exists_org_code" value="<?php echo $org_code ?>">
<input type="hidden" name="email_hid" value="<?php echo $email ?>">
<input type="hidden" name="api_sign_up" value="<?php echo $api_sign_up ?>">
<div class="p-md form-basic vertical">
	<div class="row m-b-n">
		<div class="title-content font-normal">Step 1: Account Information</div>
		<div class="fs-subtitle m-t-xs">Enter the login details of your account.</div>
	</div>
	<div class="row m-b-n p-b-md">
		<div>
			<?php 
		  		if( $ch_login_sys_param AND EMPTY( $api_sign_up ) ) :
		  	?>
		  	<?php 
				if( in_array( VIA_FACEBOOK, $login_with_arr_a ) ) :
			?>
		  	<a class="btn sm blue bg white-text" href="<?php echo $api_reroute.'' ?>" >Sign up via Facebook</a>
		  	<?php 
		  		endif;
		  	?>
		  	<?php 
				if( in_array( VIA_GOOGLE, $login_with_arr_a ) ) :
			?>
			<a class="btn sm red bg white-text" href="<?php echo $api_reroute.'1' ?>" >Sign up via Google</a>
			<?php 
				endif;
			?>
			<?php 
				endif;
			?>
		</div>
	</div>
	<?php 
		if( !EMPTY( $users_gender_inp['sys_param_value'] ) ) :
	?>
	<div class="row m-b-n">
		<div class="col s3">&nbsp;</div>
		<div class="col s2 right-align" style="padding:0;">
			<input type="radio" class="labelauty" <?php echo $male ?> name="gender" id="user_gender_male" value="<?php echo MALE ?>" />
		</div>
		<div class="col s2 center-align" style="padding-top:20px;"><small style="font-weight:600;">OR</small></div>
		<div class="col s2 left-align" style="padding:0 0 0 8px;">
			<input type="radio" class="labelauty" <?php echo $female ?> name="gender" id="user_gender_female" value="<?php echo FEMALE ?>"/>
		</div>
		<div class="col s3">&nbsp;</div>
	</div>
	<?php 
		endif;
	?>
	<div class="row m-b-n m-t-md">
		<div class="col s2"></div>
		<div class="col s2 right-align">
			<div class="input-field">
				<label class="label active required" for="last_name">Last Name</label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<input type="text" name="last_name" <?php echo $client_basic_info['last_name'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" placeholder="Enter Last Name" data-parsley-trigger="keyup" value="<?php echo $lname ?>" id="last_name" autocomplete="off" />
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<div class="row m-b-n">
		<div class="col s2"></div>
		<div class="col s2 right-align">
			<div class="input-field">
				<label class="label active required" for="first_name">First Name</label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<input type="text" name="first_name" <?php echo $client_basic_info['first_name'] ?>  data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $fname ?>" id="first_name" placeholder="Enter First Name" autocomplete="off" />
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<?php 
		if( !EMPTY( $users_mname_inp['sys_param_value'] ) ) :
	?>
	<div class="row m-b-n">
		<div class="col s2"></div>
		<div class="col s2 right-align">
			<div class="input-field">
				<label class="label active" for="middle_initial">Middle Name</label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<input type="text" name="middle_initial" <?php echo $client_basic_info['middle_initial'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $mname ?>" id="middle_initial" placeholder="Enter Middle Name" autocomplete="off" />
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<?php 
		endif;
	?>
	<?php 
		if( !EMPTY( $users_ename_inp['sys_param_value'] ) ) :
	?>
	<div class="row m-b-n">
		<div class="col s2"></div>
		<div class="col s2 right-align">
			<div class="input-field">
				<label class="label active" for="ext_name">Extension Name</label>
			</div>
		</div>
		<div class="col s3 left-align">
			<div class="input-field">
				<select class="selectize" name="ext_name" id="ext_name" data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="change">
					<option value="">Select Ext. Name</option>
					<?php 
						if( !EMPTY( $ext_names ) ) :
					?>
						<?php 
							foreach( $ext_names as $ext_n ) :
								$id_pext = base64_url_encode($ext_n['param_extension_name']);

								$sel_ext = ( $ext_n['param_extension_name'] == $ext_name ) ? 'selected' : '';
						?>
						<option <?php echo $sel_ext ?> value="<?php echo $id_pext ?>"><?php echo $ext_n['param_extension_name'] ?></option>
						<?php 
							endforeach;
						?>
					<?php 
						endif;
					?>
				</select>
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<?php 
		endif;
	?>
	<?php 
		if( !EMPTY( $users_job_title_inp['sys_param_value'] ) ) :
	?>
	<div class="row m-b-n">
		<div class="col s2"></div>
		<div class="col s2 right-align">
			<div class="input-field">
				<label class="label active" for="job_title">Job Title</label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<input type="text" name="job_title" <?php echo $client_basic_info['job_title'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $job_title ?>" id="job_title">
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<?php 
		endif;
	?>
	<?php 
		if( !EMPTY( $sign_up_org_name['sys_param_value'] ) ) :
	?>
	<div class="row m-b-n">
		<div class="col s2"></div>
		<div class="col s2 right-align">
			<div class="input-field">
				<label class="label active required" for="company_name">Company Name</label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<input type="text" name="company_name" <?php echo $client_basic_info['company_name'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $company_name ?>" id="company_name">
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<?php 
		endif;
	?>
	<?php 
		if( !EMPTY( $sign_up_short_name['sys_param_value'] ) ) :
	?>
	<div class="row m-b-n">
		<div class="col s2"></div>
		<div class="col s2 right-align">
			<div class="input-field">
				<label class="label active required" for="company_short_name">Company Short Name</label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<input type="text" name="company_short_name" <?php echo $client_basic_info['company_short_name'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $company_short_name ?>" id="company_short_name">
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<?php 
		endif;
	?>
	<?php 
		if( !EMPTY( $users_subs_notif_inp['sys_param_value'] ) ) :	
	?>
	<div class="row p-t-sm"></div>
	<div class="row m-b-n">
		<div class="col s3"></div>
		<div class="col s1 right-align">
			<div class="input-field">
				<input id="subs_checkbox" <?php echo $checked_subs ?> name="subs_checkbox" type="checkbox" /><label for="subs_checkbox"></label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				Yes, I would like to receive marketing communications regarding <?php echo $system_title ?>'s products, services, and events.
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<?php 
		endif;
	?>
	<?php 
		$disable_aggr = '';
		$checked_ch_aggr = '';
		$style_aggr = 'style="width: 300px !important;';
		if( $has_agreement_check ) :

			if( !EMPTY( $check_user_aggr ) )
			{
				$checked_ch_aggr = 'checked';
			}
			else
			{
				$disable_aggr = 'disabled';
				$style_aggr   = 'style="width: 300px !important;background:gray !important;cursor:auto !important;"';
			}
	?>
	<div class="row p-t-sm"></div>
	<div class="row m-b-n">
		<div class="col s3"></div>
		<div class="col s1 right-align">
			<div class="input-field">
				<input id="terms_checkbox" <?php echo $checked_ch_aggr ?> name="terms_checkbox" type="checkbox" /><label for="terms_checkbox"></label>
			</div>
		</div>
		<div class="col s4">
			<div class="input-field">
				<a href="#modal_term_condition" onclick="modal_term_condition_init('sign_up')">By clicking Sign Up, you are indicating that you have read and agree to the Terms & Conditions and Privacy Policy</a>
			</div>
		</div>
		<div class="col s4"></div>
	</div>
	<div class="row p-t-sm"></div>
	<?php 
		endif;
	?>
</div>
<input type="button" name="cancel" onclick="SignUp.cancel();" class="cancel action-button" value="Cancel" />
<!-- <input type="button" name="save-wizard" data-action="SignUp.move_basic_info(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Save" /> -->
<input type="button" name="next" <?php echo $disable_aggr ?> <?php echo $style_aggr ?> id="process_save" data-action="SignUp.move_basic_info(animate_next, next_fs, current_fs, self);" class="next action-button next_basic" value="Set-up your <?php echo $system_title ?> account" />