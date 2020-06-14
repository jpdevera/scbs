<?php 
$email = "";
$username = "";
$pw_note = "";
$status = "checked";
$contact_only_flag = "";
$create_flag = "checked";

$consent_form_sys_filename = '';
$consent_form_orig_filename = '';

$temporary_account_flag_ch = '';
$temporary_account_expiration_date = '';

$facebook_email = '';
$google_email 	= '';

$login_with_arr_sel 		= get_setting(LOGIN, 'login_api');
$login_with_arr_sel 		= trim($login_with_arr_sel);

$login_with_arr_a 		= array();

if( !EMPTY( $login_with_arr_sel ) )
{
	$login_with_arr_a 	= explode(',', $login_with_arr_sel);
}


if(ISSET($user)){
	$sp_status = get_sys_param_val(SYS_PARAM_STATUS, $user["status"]);
	
	$email = (!EMPTY($user["email"]))? $user["email"] : "";
	$facebook_email = (!EMPTY($user["facebook_email"]))? $user["facebook_email"] : "";
	$google_email = (!EMPTY($user["google_email"]))? $user["google_email"] : "";
	$username = (!EMPTY($user["username"]))? $user["username"] : "";
	$consent_form_sys_filename = (!EMPTY($user["consent_form_sys_filename"]))? $user["consent_form_sys_filename"] : "";
	$consent_form_orig_filename = (!EMPTY($user["consent_form_orig_filename"]))? $user["consent_form_orig_filename"] : "";
	$status = ($sp_status["sys_param_value"] == ACTIVE)? "checked" : "";
	$contact_only_flag = ($user["contact_flag"]) ? "checked" : "";
	$create_flag = (!$user["contact_flag"]) ? "checked" : "";
	$pw_note = "Type in a new password below to reset / change current password.";

	if( !EMPTY($user['temporary_account_flag']) AND $user['temporary_account_flag'] == ENUM_YES )
	{
		$temporary_account_flag_ch = 'checked';
	}

	$temporary_account_expiration_date = (!EMPTY($user["temporary_account_expiration_date"]))? $user["temporary_account_expiration_date"] : "";

	if(!EMPTY($temporary_account_expiration_date) )
	{
		$temporary_account_expiration_date = date_format( date_create( $temporary_account_expiration_date ), 'm/d/Y' );
	}
}

?>
<div class="row m-b-n">
	<div class="col s12">
		<div class="input-field">
			<label class="active required" for="contact_type">Account Type</label>
			<div class="row labelauty-list m-n">
				<div class="col s3 p-n">
					<input <?php echo $disabled_str ?> type="radio" class="labelauty contact_flag" name="contact_type" id="contact_user" value="0" data-labelauty="Create this user an account" <?php echo $create_flag ?>/>
				</div>
				<div class="col s3 p-n">
					<input <?php echo $disabled_str ?> type="radio" class="labelauty contact_flag" name="contact_type" id="contact_only" value="1" data-labelauty="Tag user as contact only" <?php echo $contact_only_flag ?>/>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row m-t-md m-b-n">
	<div class="col s6">
		<div class="input-field">
			<input <?php echo $disabled_str ?> type="email" name="email" id="email" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-parsley-type="email" value="<?php echo $email ?>" class="white"/>
			<label for="email" class="required">Email Address</label>
		</div>
	</div>
	<?php 
		if( $admin_set_username ) :
	?>
	<div class="col s6">
		<div class="input-field">
			<input <?php echo $disabled_str ?> type="text" name="username" id="username" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" value="<?php echo $username ?>" class="white"/>
			<label for="username" class="required">Username</label>
		</div>
	</div>
	<?php 
		endif;
	?>
</div>

<?php 
	if( $admnin_set_password ) :
?>
<div class="row m-b-n m-t-md">
	<div class="col s6">
		<div class="input-field">
			<input <?php echo $disabled_str ?> type="password" name="password" id="password" <?php if(!ISSET($user)){ ?> data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" <?php } ?> class="white"/>
			<label for="password" <?php if(!ISSET($user)){ ?>class="required" <?php } ?>>Password</label>
			<div class="help-text"><?php echo $pw_note ?></div>
		</div>
	</div>
	<div class="col s6">
		<div class="input-field">
			<input <?php echo $disabled_str ?> type="password" data-parsley-equalto="#password" data-parsley-equalto-message="Passwords don't match." name="confirm_password" id="confirm_password" <?php if(!ISSET($user)){ ?> data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" <?php } ?> class="white"/>
			<label for="confirm_password" <?php if(!ISSET($user)){ ?>class="required" <?php } ?>>Confirm Password</label>
		</div>
	</div>
</div>
<?php endif; ?>
<div class="row m-b-n m-t-md">
	<div class="col s5">
		<div class="input-field">
			<select <?php echo $disabled_str ?> name="main_role[]" id="main_role" class="selectize" placeholder="Select Main User Role" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="change">
				<option value="">Select Main User Role</option>
				<?php foreach ($roles as $role): 
					$sel_main_role 	= ( !EMPTY( $main_role ) AND in_array($role['role_code'], $main_role) ) ? 'selected' : '';
				?>

				<option value="<?php echo $role["role_code"] ?>" <?php echo $sel_main_role ?>><?php echo $role["role_name"] ?></option>
				<?php endforeach; ?>
			</select>
			<label for="main_role" class="active required">Main Role</label>
			<div class="help-text m-t-sm">Assign main role to this account</div>
		</div>
	</div>
	<div class="col s5">
		<div class="input-field">
			<select <?php echo $disabled_str ?> name="role[]" id="role" class="selectize" placeholder="Select Other Role" multiple data-parsley-required="false" data-parsley-validation-threshold="0" data-parsley-trigger="keyup">
				<option value="">Select Other Role</option>
				<?php foreach ($roles as $role): ?>
				<option value="<?php echo $role["role_code"] ?>"><?php echo $role["role_name"] ?></option>
				<?php endforeach; ?>
			</select>
			<label for="role" class="active">Other Role</label>
		</div>
	</div>
	<?php if( !$add_b ) : ?>
	<div class="col s2">
		<div class="input-field">
			<label for="status" class="active required">Status</label>
			<input <?php echo $disabled_str ?> type="checkbox" class="labelauty" name="status" id="status" value="<?php echo ACTIVE ?>" data-labelauty="Inactive|Active" <?php echo $status ?>/>
		</div>
	</div>
	<?php endif; ?>
</div>

<div class="row m-b-n m-t-md p-b-sm">
	<div class="col s6">
		<div class="input-field">
			<label class="active" for="temp_account_flag">Temporary account?</label>
			<div class="row labelauty-list m-n">
				<div class="col s3 p-n">
					<input <?php echo $disabled_str ?> <?php echo $temporary_account_flag_ch ?> type="checkbox" class="labelauty temp_account_flag" name="temp_account_flag" id="temp_account_flag" value="0" data-labelauty="No|Yes" />
				</div>
			</div>
		</div>
	</div>
	<div class="col s4" id="temp_exp_date_div" style="display: none !important;">
		<div class="input-field">
			<label class="active" for="temp_expiration_date">Expiration Date</label>
			<input <?php echo $disabled_str ?> data-parsley-required="false" data-parsley-trigger="change" type="text" class="datepicker datepicker_temp_exp" name="temp_expiration_date" id="temp_expiration_date" value="<?php echo $temporary_account_expiration_date ?>"  />
		</div>
	</div>
</div>

<div class="row m-b-n m-t-md">
	<div class="col s5">
		<div class="input-field">
			<select <?php echo $disabled_str ?> name="groups[]" multiple="" id="groups_user_sel" class="selectize" placeholder="Select Groups" data-parsley-validation-threshold="0" data-parsley-trigger="change">
				<option value="">Select Groups</option>
				<?php foreach ($all_groups as $group): 
					$id_group 	= base64_url_encode( $group['group_id'] );
					$sel_grp 	= ( !EMPTY( $user_groups ) AND in_array($group['group_id'], $user_groups) ) ? 'selected' : '';
				?>

				<option value="<?php echo $id_group ?>" <?php echo $sel_grp ?>><?php echo $group['group_name'] ?></option>
				<?php endforeach; ?>
			</select>
			<label for="groups_user_sel" class="">Groups</label>
		</div>
	</div>
</div>

<?php 
	if( $ch_login_sys_param ) :
?>
<?php 
	if( !EMPTY( $login_with_arr_a ) ) :
?>
<div class="row m-t-md">	
	<div class="col s12"><h5 class="form-subtitle">Login With Details</h5></div>
</div>
<div class="row m-b-n m-t-md">
	<?php 
		if( in_array( VIA_FACEBOOK, $login_with_arr_a ) ) :
	?>
	<div class="col s3 p-t-md">
		<div class="input-field">
			<input type="text" <?php echo $disabled_str ?> data-parsley-required="false" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-parsley-type="email" name="facebook_email" id="facebook_email" class="" value="<?php echo $facebook_email ?>"  />
			<label for="facebook_email" class="active">Facebook Email Address</label>
		</div>
	</div>
	<div class="col s3 p-t-sm">
		<div class="input-field">
			<input type="checkbox" <?php echo $disabled_str ?> id="facebook_email_same" class="labelauty" data-labelauty="Same as system email" value="1" />
		</div>
	</div>
	<?php 
		endif;
	?>
	<?php 
		if( in_array( VIA_GOOGLE, $login_with_arr_a ) ) :
	?>
	<div class="col s3 p-t-md">
		<div class="input-field">
			<input type="text" <?php echo $disabled_str ?> data-parsley-required="false" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-parsley-type="email" name="google_email" id="google_email" class="" value="<?php echo $google_email ?>"  />
			<label for="google_email" class="active">Google Email Address</label>
		</div>
	</div>
	<div class="col s3 p-t-sm">
		<div class="input-field">
			<input type="checkbox" <?php echo $disabled_str ?> id="google_email_same" class="labelauty" data-labelauty="Same as system email" value="1" />
		</div>
	</div>
	<?php 
		endif;
	?>
</div>
<?php 
	endif;
?>
<?php 
	endif;
?>
<?php 
	if( !EMPTY( $dpa_enable ) ) :

		if( $has_agreement_text == DATA_PRIVACY_TYPE_STRICT AND $strict_mode == DATA_PRIVACY_STRICT_CONSENT_FORM ) :
?>
<div class="row m-b-n m-t-md" id="consent_form_upload_form">
	<input <?php echo $disabled_str ?> type="hidden" data-parsley-errors-container=".my_error_custom" data-parsley-required="true" data-parsley-error-message="This value is required." name="consent_form" id="consent_form" value="<?php echo $consent_form_sys_filename ?>" class="form_dynamic_upload upload_movs" data-origfile="<?php echo $consent_form_orig_filename ?>" >
	<input type="hidden" name="consent_form_orig_filename" id="consent_form_orig_filename" value="<?php echo $consent_form_orig_filename ?>" class="form_dynamic_upload_origfilename">
	<div class="col s12">
		<h5 class="form-header">Consent Form</h5>
		<div class="help-text">Select and upload your consent form for Data privacy compliance.</div>
		<div id="consent_form_upload">Select File</div>
		<div class="my_error_custom" ></div>
	</div>
</div>
<?php 
		endif;
	endif;
?>
