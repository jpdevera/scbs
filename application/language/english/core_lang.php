<?php
// SYSTEM AUTHENTICATION
$lang['invalid_login'] 		= "Sorry, the login was incorrect. Please try again.";
$lang['username_required'] 	= "Username is required.";
$lang['password_required'] 	= "Password is required.";
$lang['email_required'] 	= "Email is required.";
$lang['confirm_password'] 	= "Password confirmation is required.";
$lang['contact_admin'] 		= "There is something wrong with your account, please contact the system's administrator.";
$lang['system_error'] 		= "Sorry, but there seems to be a problem with the system or your internet connection. Please try again later.";
$lang['reset_password'] 	= "An instruction on how to reset your password has been sent to your email.";
$lang['email_exist'] 		= "The email address entered has already been registered. Please use a different email.";
$lang['invalid_action'] 	= "Sorry, but your current action is invalid.";
$lang['password_reset'] 	= "Your new password has been reset, you may now log in using your new password.";
$lang['signup_success'] 	= "Your registration has been sent for validation and approval.  Please check your email for further instructions.";
$lang['pending_account'] 	= "Sorry, your registration account is awaiting approval by the site administrator. Once approved or denied you will be notified thru email.";
$lang['account_blocked'] 	= "Sorry, your account has been permanently blocked. Please contact the administrator to unblock your account.";
$lang['account_expired'] 	= "Sorry, your account has expired. Please contact the administrator to reactivate your account.";
$lang['invalid_multi'] 		= "Sorry, this %s is invalid at row %s.";
$lang['cant_delete_user'] 	= "Sorry, but you can\'t delete this user record.";

// ACTIONS MESSAGES
$lang['data_saved'] 		= "Record was successfully saved.";
$lang['data_imported'] 		= "Record was successfully imported.";
$lang['data_updated'] 		= "Record was successfully updated.";
$lang['data_deleted'] 		= "Record was successfully deleted.";
$lang['data_not_saved'] 	= "An error occurred while saving the record. Please try again later.";
$lang['data_not_updated'] 	= "An error occurred while updating the record. Please try again later.";
$lang['data_not_deleted'] 	= "An error occurred while deleting the record. Please try again later.";

// SYSTEM MESSAGES
$lang['confirm_error'] 				= "There was an error on your password confirmation. Please try again.";
$lang['parent_delete_error'] 		= "Record with dependency cannot be deleted.";
$lang['member_admin_delete_error'] 	= "This action cannot be performed because this member is assigned as the initiative manager.";
$lang['detail_view_error'] 		= "Parent table is empty, the following details can't be viewed.";
$lang['detail_delete_error'] 	= "Parent table is empty, the following details can't be deleted.";
$lang['detail_save_error'] 		= "Parent table is empty, the following details can't be saved.";
$lang['data_empty'] 			= "No matching records found.";
$lang['is_required'] 			= "%s is required.";
$lang['invalid_data'] 			= "Invalid data for %s";
$lang['duplicate_data'] 		= "Duplicate %s";

// PERMISSION MESSAGE
$lang['err_unauthorized_add'] 			= "Sorry, you don't have permission to add this record. Please contact the system's administrator.";
$lang['err_unauthorized_archive'] 			= "Sorry, you don't have permission to archive this record. Please contact the system's administrator.";
$lang['err_unauthorized_edit'] 			= "Sorry, you don't have permission to edit this record. Please contact the system's administrator.";
$lang['err_unauthorized_delete'] 		= "Sorry, you don't have permission to delete this record. Please contact the system's administrator.";
$lang['err_unauthorized_save'] 			= "Sorry, you don't have permission to save this record. Please contact the system's administrator.";
$lang['err_unauthorized_view'] 			= "Sorry, you don't have permission to view this record. Please contact the system's administrator.";
$lang['err_unauthorized_access'] 		= "Sorry, you don't have permission to access this page. Please contact the system's administrator.";
$lang['err_unauthorized_approve_disapprove_user'] 	= "Sorry, you don't have permission to approve or disapproved this user. Please contact the system's administrator.";
$lang['err_unauthorized_import'] 		= "Sorry, you don't have permission to import record/s. Please contact the system's administrator.";
$lang['err_unauthorized_download'] 		= "Sorry, you don't have permission to download record/s. Please contact the system's administrator.";

// AUDIT TRAIL 
$lang['audit_trail_add']				= "%s has been added";
$lang['audit_trail_update']				= "%s has been updated";
$lang['audit_trail_delete']				= "%s has been deleted";
$lang['audit_trail_update_specific'] 	= "%s has been updated for %s";
$lang['audit_trail_add_specific'] 		= "%s has been added for %s";
$lang['audit_trail_delete_specific'] 	= "%s has been deleted for %s";
$lang['audit_trail_save'] 				= "%s has been saved";

// EMAIL
$lang['email_not_initialized'] = "Email parameters were not properly configured.";
$lang['email_fields_required'] = "%s are required.";
$lang['email_field_empty'] 	= "%s is empty.";
$lang['email_not_sent'] 	= "Email not sent! Please try again later.";
$lang['email_sent'] 		= "Email was successfully sent.";

// MAINTENANCE MODE
$lang['maintenance_mode'] 	= "Sorry for the inconvenience, but the system is on maintenance mode. Please try to access again later.";

// ERROR MESSAGE USED IN ERROR PAGE

$lang['err_page_404_msg'] 		= "We can not find the page that you're looking for.<br/><a href=".base_url().">Return home </a>";
$lang['err_page_404_heading']	= "Oops! You're lost.";

$lang['err_page_500_msg']		= "Please contact the system's administrator.";
$lang['err_page_500_heading']	= "Oops! Something went wrong.";

$lang['err_page_402_heading']	= "Oops! The link that you're trying to open is either broken or missing.";
$lang['err_page_no_org_heading']= "Oops! This page can't be accessed. because you must have been assigned to an organization.";
$lang['err_page_no_org_message']=  "Please contact system's administrator.<br/><a href=".base_url().">Return home </a>";

$lang['multiple_login']			= "The account you are trying to log in has active session in another device.  Please log out the account from another device and try logging in again.";

// Workflow errors
$lang['workflow_empty']			= "Sorry but there must be an existing workflow before saving a %s";
$lang['workflow_stage_empty']	= "Sorry but there must be an existing workflow stage before saving a %s";
$lang['workflow_step_empty']	= "Sorry but there must be an existing workflow step before saving a %s";

$lang['err_ck_upload1']						= "Sorry, the system only accepts image.";
$lang['err_ck_upload2'] 					= "Sorry, file already exists.";
$lang['err_ck_upload3'] 					= "Sorry, file size is too large.";
$lang['err_ck_upload4'] 					= "Sorry, the system only accepts JPG, JPEG, PNG, & GIF file formats.";
$lang['err_ck_upload5']						= "Sorry, file was not uploaded. The upload directory must be writable";
$lang['err_ck_upload6']						= "Sorry, there was an error uploading your file.";
$lang['err_ck_upload7']						= "Don't forget to set CHMOD writable permission (0777) to imageuploader folder on your server.";
$lang['err_ck_upload8']						= "Sorry, the file that you're trying to upload is not allowed.";

$lang['err_invalid_data'] 					= "Invalid value";

// MESSAGE
$lang['message_sent'] 			= "Message was successfully sent.";

// DPA CONFIRM MESSAGE 
$lang['confirm_dpa_message'] 	= "<b>Warning!</b>: Processing of individual's information without proper authorization will violate R.A. 10173 Data Privacy Act.";
$lang['confirm_dpa_message_body'] = "<b>Are you sure you want to create this user account?</b><br/>By clicking CONTINUE, you confirm that you are <b>authorized</b> by your organization to create user account on behalf of this individual.";
$lang['reject_dpa_msg'] 		= "The invitation to be a user has been rejected.";
$lang['sign_up_with_approval_success'] = "Your registration requires approval of system's administrator. You will be notified via email if your registration has been approved or denied. <br/><br/> Note: Some email providers may mark our emails as spam, so make sure to check your spam or junk folder.
";
$lang['sign_up_without_approval_success'] 		= "Congratulations! You have completed the sign-up. You may now log in.";
$lang['self_logout_success']					= "Account was successfully logged-out from other device/s.";
$lang['temp_tag_expired_account'] 				= "Sorry, your account is expired. Please contact system's administrator.";

$lang['login_soft_blocked'] 				= 'Sorry, this account has been blocked. You may log in again after <input type="hidden" value="%s" id="log_soft_sec_val"> <input type="hidden" value="%s" id="log_soft_date"> <span id="log_soft_sec">%s seconds</span>.';
$lang['new_device_location']				= "A device or location we haven't encountered before is requesting access to your account. Please check your email to authorize this request.";