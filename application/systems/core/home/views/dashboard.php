<?php
$pending_ctr			= $statistics['pending_count'];
$active_ctr				= $statistics['active_count'];
$approved_ctr			= $statistics['approved_count'];
$disapproved_ctr		= $statistics['disapproved_count'];

$pending_ctr_temp		= $statistics_temp['pending_count'];
$approved_ctr_temp		= $statistics_temp['approved_count'];
$incomplete_ctr_temp	= $statistics_temp['incomplete_count'];
?>

<div class="page-title">
	<div class="row m-b-n">
		<div class="col s12 p-r-n">
			<h5>Dashboard</h5>
		</div>
	</div>
</div>

<div class="row m-b-n prototype">
	<div class="col s9 p-l-n">
		<div class="bg-white m-b-lg">
			<div class="table-display">
				<div class="table-cell p-md b-r valign-middle" style="width:50%; border-style:dashed!important; border-right-color:#eee!important;">
					<div class="row m-n center-align">
						<div class="col s2 left-align font-semibold">
							<h6 class="m-n">Statistics</h6>
						</div>
						<ul class="list link-tab inline m-l-sm">
							<li><a href="javascript:;" class="link-filter active" id="ctr_pending">pending approval <span><?php echo $pending_ctr_temp ?></span></a></li>
							<li><a href="javascript:;" class="link-filter" id="ctr_incomplete">incomplete <span><?php echo $incomplete_ctr_temp ?></span></a></li>
						</ul>
					</div>
				</div>

				<div class="table-cell p-md valign-middle" style="width:15%;">
					<label class="active block m-b-sm">Status</label>
					<select name="filter_user_status" id="filter_user_status" class="selectize">
						<option value="0">All</option>
						<?php foreach ($status_arr as $key => $value) : ?>
							<option value="<?php echo base64_url_encode($key); ?>"><?php echo $value; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto col-align-middle" id="user_approval_table">
			<thead>
				<tr>
					<th width="20%" class="center-align">Full Name</th>
					<th width="20%" class="center-align">Agency</th>
					<th width="15%" class="center-align">Position/Job Title</th>
					<th width="15%" class="center-align">Email Address</th>
					<th width="15%" class="center-align">Status</th>
					<th width="15%" class="center-align">Actions</th>
				</tr>

				<tr class="table-filters">
					<td width="20%"><input name="full_name" class="form-filter" /></td>
					<td width="20%"><input name="agency" class="form-filter" /></td>
					<td width="15%"><input name="job_title" class="form-filter" /></td>
					<td width="15%"><input name="email" class="form-filter" /></td>
					<td width="15%">
						<select name="status" class="material-select form-filter">
							<option value=""></option>
							<?php foreach ($status_arr as $key => $value) : ?>
								<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td width="15%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Submit" data-position="top" data-delay="50">
							<i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50">
							<i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>

	<div class="col s3 p-r-n new-users">
		<div class="bg-white">
			<div class="header">New Users</div>
			<?php if (count($new_users)): ?>
			<ul>
				<?php foreach ($new_users as $user): ?>
					<li>
						<div>
							<?php print $user['photo']; ?>
							<div class="name-role">
								<?php print $user['fname'] .' '. $user['lname']; ?>
								<small class="mute"><?php print $user['role_name']; ?></small>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php else: ?>
				<div class="p-t-xs">No record(s) found</div>
			<?php endif; ?>
		</div>
	</div>
</div>