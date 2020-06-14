<?php 
$id	= "";
$lname = "";
$fname = "";
$mname = "";
$nickname = "";
$female = "checked";
$male = "";
$email = "";
$job_title = "";
$contact_no = "";
$mobile_no = "";
$username = "";
$photo = "";
$pw_note = "";
$status = "checked";
$cancel = "";
$send = "checked";
$page_title = "Add New User";
$page_subtitle = "Create a new user account";

if(ISSET($user)){
	$page_title = "Edit User";
	$page_subtitle = "Update user account information";
	$sp_status = get_sys_param_val(SYS_PARAM_STATUS, $user["status"]);
	$sp_gender = get_sys_param_val(SYS_PARAM_GENDER, $user["gender"]);
	
	$id	= (!EMPTY($user["user_id"]))? $user["user_id"] : "";
	$lname = (!EMPTY($user["lname"]))? $user["lname"] : "";
	$fname = (!EMPTY($user["fname"]))? $user["fname"] : "";
	$mname = (!EMPTY($user["mname"]))? $user["mname"] : "";
	$nickname = (!EMPTY($user["nickname"]))? $user["nickname"] : "";
	$female = ($user["gender"] == FEMALE)? "checked" : "";
	$male = ($sp_gender["sys_param_value"] == MALE)? "checked" : "";
	$email = (!EMPTY($user["email"]))? $user["email"] : "";
	$job_title = (!EMPTY($user["job_title"]))? $user["job_title"] : "";
	$contact_no = (!EMPTY($user["contact_no"]))? $user["contact_no"] : "";
	$mobile_no = (!EMPTY($user["mobile_no"]))? $user["mobile_no"] : "";
	$username = (!EMPTY($user["username"]))? $user["username"] : "";
	$photo = (!EMPTY($user["photo"]))? $user["photo"] : "";
	$status = ($sp_status["sys_param_value"] == ACTIVE)? "checked" : "";
	$pw_note = "Type in a new password below to reset / change current password.";
	$send = $cancel = "";
}

$roles_json 				= '';
	
if( !EMPTY( $roles ) )
{
	$role_key 				= array_column( $roles, 'role_code' );
	$role_name 				= array_column( $roles, 'role_name' );
	
	$roles_json 			= json_encode( array_combine( $role_key, $role_name ) );	
}

$other_role_info_json 		= '';
$main_role_info_json 		= '';

if( ISSET( $main_role ) AND !EMPTY( $main_role ) )
{
	$main_role_info_json 	= json_encode( $main_role );
}

if( ISSET( $other_roles ) AND !EMPTY( $other_roles ) )
{
	$other_role_info_json 	= json_encode( $other_roles );
}

$salt = gen_salt();
$token	= in_salt($id, $salt);
?>
<div class="page-title">
  <h5><?php echo $page_title ?></h5>
</div>

<div class="m-lg">
	<form id="form_modal_user_mgmt" name="form_modal_user_mgmt" class="form-vertical form-styled" autocomplete="off">
		<input type="hidden" name="user_id" id="user_id" value="<?php echo $id ?>">
		<input type="hidden" name="salt" value="<?php echo $salt ?>">
		<input type="hidden" name="token" value="<?php echo $token ?>">
		<input type="hidden" name="image" id="avatar" value="<?php echo $photo ?>"/>
		<input type="hidden" id="role_json" value='<?php echo $roles_json ?>'/>
		<input type="hidden" id="other_role_json" value='<?php echo $other_role_info_json ?>'/>
		<input type="hidden" id="main_role_json" value='<?php echo $main_role_info_json ?>'/>
		<input class="none" type="password" />
		
		<div class="form-basic">
			<div class="panel p-t-lg p-b-lg">
				<div class="row m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s10"><h5 class="form-subtitle">General Information</h5></div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s10">
						<div class="row m-b-n">
							<div class="col s5 p-l-n">
								<div class="input-field">
									<input type="text" name="lname" id="lname" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" value="<?php echo $lname ?>"/>
									<label for="lname">Last Name</label>
								</div>
							</div>
							<div class="col s4">
								<div class="input-field">
									<input type="text" name="fname" id="fname" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" value="<?php echo $fname ?>"/>
									<label for="fname">First Name</label>
								</div>
							</div>
							<div class="col s3 p-r-n">
								<div class="input-field">
									<input type="text" name="mname" id="mname" value="<?php echo $mname ?>"/>
									<label for="mname">Middle Initial</label>
								</div>
							</div>
						</div>
					</div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s5 p-t-md p-r-lg">
						<div class="input-field">
							<input type="text" name="nickname" id="nickname" value="<?php echo $nickname ?>"/>
							<label for="nickname">Nickname</label>
						</div>
					</div>
					<div class="col s2 p-t-md">
						<div class="input-field">
							<input type="radio" class="labelauty" name="gender" id="user_gender_male" value="<?php echo MALE ?>" data-labelauty="Male" <?php echo $male ?>/>
							<label for="user_gender_male" class="active">Gender</label>
						</div>
					</div>
					<div class="col s3 p-t-md">
						<div class="input-field">
							<input type="radio" class="labelauty" name="gender" id="user_gender_female" value="<?php echo FEMALE ?>" data-labelauty="Female" <?php echo $female ?>/>
						</div>
					</div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row m-t-md m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s10">
						<h5 class="form-header">Contact Information</h5>
						<div class="help-text">All supplied contact numbers will only be used as reference and not for public viewing.</div>
					</div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row">
					<div class="col s1">&nbsp;</div>
					<div class="col s5">
						<div class="input-field">
							<input type="text" name="contact_no" id="contact_no" value="<?php echo $contact_no ?>" data-parsley-trigger="keyup" data-parsley-pattern="^[0-9-()+ ]+$" data-parsley-pattern-message="Telephone no. must contain parenthesis '()', dash '-', or numeric values only"/>
							<label for="contact_no">Telephone No.</label>
						</div>
					</div>
					<div class="col s5">
						<div class="input-field">
							<div class="input-group">
								<div class="input-group-addon">+ 63</div>
								<input type="text" name="mobile_no" id="mobile_no" value="<?php echo $mobile_no ?>" data-parsley-required="false" />
								<label for="mobile_no">Mobile No.</label>
							</div>
						</div>
					</div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row">
					<div class="col s1">&nbsp;</div>
					<div class="col s6">
						<label class="label m-t-n-xs block" style="margin-bottom:8px;">Department/Agency</label>
						<select name="org" id="org" class="selectize" placeholder="Select Agency" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup">
							<option value="">Select Agency</option>
							<?php 
								if( ISSET( $real_orgs ) AND !EMPTY( $real_orgs ) ) :
							?>	
							<?php 
								echo $real_orgs;
							?>
							<?php
								endif;
							?>
						</select>
					</div>
					<div class="col s4">
						<div class="input-field m-t-md">
							<input type="text" name="job_title" id="job_title" value="<?php echo $job_title ?>"/>
							<label for="job_title">Job Title</label>
						</div>
					</div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row">
					<div class="col s1">&nbsp;</div>
					<div class="col s10">
						<h5 class="form-header">Display Image</h5>
						<div class="help-text">Select and upload your latest photo to help others recognize this account.</div>
						<div id="avatar_upload">Select File</div>
					</div>
					<div class="col s1">&nbsp;</div>
				</div>
			</div>
		  
			<div class="panel m-t-lg p-t-lg p-b-lg">
				<div class="row m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s10"><h5 class="form-subtitle">Account Details</h5></div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s5">
						<div class="input-field">
						<input type="email" name="email" id="email" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-parsley-type="email" value="<?php echo $email ?>"/>
						<label for="email">Email Address</label>
						<div class="help-text">Supply a valid email address for your log in and to receive notifications from the system (e.g.forget password).</div>
					</div>
				</div>
				<div class="col s5">
					<div class="input-field">
						<input type="text" name="username" id="username" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" value="<?php echo $username ?>"/>
						<label for="username">Username</label>
						<div class="help-text">Enter a unique username for this account.</div>
					</div>
				</div>
				<div class="col s1">&nbsp;</div>
			</div>
			<div class="row m-b-n">
				<div class="col s1">&nbsp;</div>
				<div class="col s5">
					<div class="input-field">
						<input type="password" name="password" id="password" <?php if(!ISSET($user)){ ?> data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" <?php } ?>/>
						<label for="password">Password</label>
						<div class="help-text"><?php echo $pw_note ?></div>
					</div>
				</div>
				<div class="col s5">
					<div class="input-field">
						<input type="password" name="confirm_password" id="confirm_password" <?php if(!ISSET($user)){ ?> data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" <?php } ?>/>
						<label for="confirm_password">Confirm Password</label>
					</div>
				</div>
				<div class="col s1">&nbsp;</div>
			</div>
			<div class="row">
				<div class="col s1">&nbsp;</div>
				<div class="col s3">
					<div class="input-field">
						<select name="main_role[]" id="main_role" class="selectize" placeholder="Select Main User Role" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="change">
							<option value="">Select Main User Role</option>
							<?php foreach ($roles as $role): 
								$sel_main_role 	= ( !EMPTY( $main_role ) AND in_array($role['role_code'], $main_role) ) ? 'selected' : '';
							?>

							<option value="<?php echo $role["role_code"] ?>" <?php echo $sel_main_role ?>><?php echo $role["role_name"] ?></option>
							<?php endforeach; ?>
						</select>
						<label for="role" class="active">Main Role</label>
						<div class="help-text m-t-sm">Assign main role to this account</div>
					</div>
				</div>
				<div class="col s3">
					<div class="input-field">
						<select name="role[]" id="role" class="selectize" placeholder="Select User Role" multiple data-parsley-validation-threshold="0" >
							<option value="">Select User Role</option>
							<?php foreach ($roles as $role): ?>
							<option value="<?php echo $role["role_code"] ?>"><?php echo $role["role_name"] ?></option>
							<?php endforeach; ?>
						</select>
						<label for="role" class="active">Other Role</label>
						<div class="help-text m-t-sm">Assign other role/s to this account</div>
					</div>
				</div>
				<div class="col s2">
					<div class="input-field">
						<input type="checkbox" class="labelauty" name="status" id="status" value="<?php echo ACTIVE ?>" data-labelauty="Inactive|Active" <?php echo $status ?>/>
						<label for="status" class="active">Status</label>
					</div>
				</div>
			</div>
		</div>
		  
		<div class="panel m-t-lg p-t-lg p-b-lg">
			<?php if(!ISSET($user)){ ?>
				<div class="row m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s10"><h5 class="form-subtitle">Welcome Email</h5></div>
					<div class="col s1">&nbsp;</div>
				</div>
				<div class="row m-b-n">
					<div class="col s1">&nbsp;</div>
					<div class="col s4 p-t-md">
						<div class="input-field">
							<input type="radio" name="send_email" id="user_send_email" class="labelauty" data-labelauty="Send a 'Welcome' email to this user" <?php echo $send ?> value="1"/>
							<label for="user_send_email" class="active">Send Email</label>
						</div>
					</div>
					<div class="col s5 p-t-md">
						<div class="input-field">
							<input type="radio" name="send_email" id="user_cancel_email" class="labelauty" data-labelauty="Don't send a 'Welcome!' email to this user" value="0" <?php echo $cancel ?> />
						</div>
					</div>
					<div class="col s2">&nbsp;</div>
				</div>
			<?php } ?>
		</div>
		<div class="panel-footer right-align">
			<a href="<?php echo base_url() . CORE_USER_MANAGEMENT ?>/users" class="waves-effect waves-teal btn-flat"><?php echo BTN_CANCEL ?></a>
			<button class="btn waves-effect waves-light" id="submit_modal_user_mgmt" name="action" type="submit" value="<?php echo BTN_SAVE ?>"><?php echo BTN_SAVE ?></button>
		</div>
	</form>
</div>