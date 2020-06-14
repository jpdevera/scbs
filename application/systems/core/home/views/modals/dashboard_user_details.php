<?php
$email		= '';
$lname		= '';
$fname		= '';
$mname		= '';
$ext_name	= '';
$job_title	= '';
$mobile_no	= '';
$contact_no	= '';

$key = array_column($status, 'sys_param_code');
$value = array_column($status, 'sys_param_name');
$status = array_combine($key, $value);

if (isset($user)) {
	$email		= (!empty($user['email'])) ? $user['email'] : '';
	$lname		= (!empty($user['lname'])) ? $user['lname'] : '';
	$fname		= (!empty($user['fname'])) ? $user['fname'] : '';
	$mname		= (!empty($user['mname'])) ? $user['mname'] : '';
	$ext_name	= (!empty($user['ext_name'])) ? $user['ext_name'] : '';
	$org_name	= (!empty($user['org_name'])) ? $user['org_name'] : '';
	$job_title	= (!empty($user['job_title'])) ? $user['job_title'] : '';
	$mobile_no	= (!empty($user['mobile_no'])) ? $user['mobile_no'] : '';
	$contact_no	= (!empty($user['contact_no'])) ? $user['contact_no'] : '';
}

if (!empty($roles)) {
	$role_key	= array_column($roles, 'role_code');
	$role_name	= array_column($roles, 'role_name');
	$roles_json	= json_encode($roles);
}
?>

<input type="hidden" id="sign_up_role_json" value='<?php echo $roles_json ?>' />
<?php if (!empty($orig_params)): ?>
	<?php foreach ($orig_params as $key => $value): ?>
		<input type="hidden" name="<?php print $key; ?>" value="<?php print $value; ?>" />
	<?php endforeach; ?>
<?php endif; ?>

<div class="modal-header">
	<div class="font-lg font-bold"><?php print $fname . ' ' . $mname . ' ' . $lname . ' '; ?></div>
	<div class="font-md m-t-sm"><?php print $job_title; ?></div>
</div>

<div class="modal-body">
	<div class="elem-centered">
		<div class="m-b-sm font-md"><i class="material-icons m-r-xs">email</i> <?php print $email; ?></div>
		<div class="m-b-sm font-md"><i class="material-icons m-r-xs">phone_android</i> <?php print(!empty($mobile_no) ? $mobile_no : $contact_no); ?></div>
		<?php if (!empty($org_name)) : ?>
			<div class="m-b-sm font-md"><i class="material-icons m-r-xs">store</i> <?php print $org_name; ?></div>
		<?php endif; ?>
	</div>

	<div class="text-align-center registration-status">
		<div class="m-b-sm">Registration Status</div>
		<div class="font-md"><?php print $status[$user['status']]; ?></div>
	</div>
</div>

<div class="modal-button">
	<button class="btn approve">approve</button>
	<button class="btn deny red">deny</button>
</div>

<input type="hidden" name="main_role[]" />
<input type="hidden" name="role[]" />
<input type="hidden" name="status_id" />
<input type="hidden" name="reject_reason" />