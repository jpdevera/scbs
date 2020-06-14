<?php 
	$pending_ctr = $statistics['pending_count'];
	$active_ctr = $statistics['active_count'];
	$approved_ctr = $statistics['approved_count'];
	$disapproved_ctr = $statistics['disapproved_count'];
	
	$visitor = get_setting(ACCOUNT, "account_creator");
?>

<div class="page-title">
  <div class="row m-b-n">
	<div class="col s12 p-r-n">
	  <h5>Dashboard</h5>
	</div>
  </div>
</div>

<?php if($visitor == VISITOR){ ?>
	<div class="bg-white m-b-lg box-shadow">
	  <div class="table-display">
		
		<div class="table-cell p-md b-r valign-middle" style="width:50%; border-style:dashed!important; border-right-color:#eee!important;">
		  <div class="row m-n center-align">
			<div class="col s2 left-align font-semibold"><h6 class="m-n">Statistics</h6></div>
			<div class="col s3 font-semibold">
			  <div class="legend circle xs m-r-xs yellow accent-4"></div> <span id="ctr_pending"><?php echo $pending_ctr ?></span> new
			</div>
			<div class="col s3 font-semibold">
			  <div class="legend circle xs m-r-xs red darken-1"></div> <span id="ctr_disapproved"><?php echo $disapproved_ctr ?></span> rejected
			</div>
			<div class="col s3 font-semibold">
			  <div class="legend circle xs m-r-xs light-green"></div> <span id="ctr_approved"><?php echo $approved_ctr ?></span> approved
			</div>
		  </div>
		</div>
		
		<div class="table-cell p-md valign-middle" style="width:15%;">
		  <label class="active block m-b-sm">Status</label>
		  <?php $status_arr = array(PENDING => "Pending", APPROVED => "Approved", DISAPPROVED => "Disapproved"); ?>
		  <select name="filter_user_status" id="filter_user_status" class="selectize">
			<option value="0">All</option>
			<?php foreach($status_arr as $key => $value): ?>
			  <option value="<?php echo base64_url_encode($key); ?>"><?php echo $value; ?></option>
			<?php endforeach; ?>
		  </select>
		</div>
	  </div>
	</div>

	<div>
	  <table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="user_approval_table">
	  <thead>
		<tr>
		  <th width="25%" class="center-align">Name</th>
		  <th width="25%" class="center-align">Agency / Position</th>
		  <th width="20%" class="center-align">Email</th>
		  <th width="15%" class="center-align">Status</th>
		  <th width="15%" class="center-align">Actions</th>
		</tr>
	  </thead>
	  </table>
	</div>

	<div class="none">
		<div id="reject_content">
		  <form id="reject_user_form" class="form-basic">
			<input type="hidden" name="id" id="reject_id" value="" />
			<p class="p-n m-b-md font-bold font-sm">Are you sure you want to reject this registration?</p>
			<div class="input-field">
			  <textarea class="materialize-textarea" name="reject_reason" id="reject_reason" placeholder="Write a reject reason here..."></textarea>
			</div>
			<div class="popModal_footer">
				<button type="button" class="btn red darken-3 waves-effect waves-light" data-popmodal-but="ok" onclick="Dashboard.updateStatus('reject_user_form', '<?php echo DISAPPROVED ?>')">Reject</button>
				<button type="button" class="btn-flat" data-popmodal-but="cancel">Cancel</button>
			</div>
		  </form>
		</div>
	</div>
	<div class="none">
		<div id="approve_content">
		  <form id="approve_user_form" class="form-basic">
			<input type="hidden" name="id" id="approve_id" value="" />
			<p class="p-n m-b-md font-bold font-sm">Are you sure you want to approve this registration?</p>
			<label class="active font-sm font-bold" for="main_role">Assign main role to this account</label>
			<div class="input-field m-t-xs">
			  <select class="selectize" id="main_role" name="main_role[]" placeholder="Select Roles">
				<option value="">Select Role</option>
				<?php foreach($roles as $role): ?>
				<option value="<?php echo $role['role_code'] ?>"><?php echo $role['role_name'] ?></option>
				<?php endforeach; ?>
			  </select>
			</div>
			<label class="active font-sm font-bold" for="approve_user_roles">Assign other role/s to this account</label>
			<div class="input-field m-t-xs">
			  <select class="selectize" multiple id="approve_user_roles" name="role[]" placeholder="Select Roles">
				<option value="">Select Roles</option>
				<?php foreach($roles as $role): ?>
				<option value="<?php echo $role['role_code'] ?>"><?php echo $role['role_name'] ?></option>
				<?php endforeach; ?>
			  </select>
			</div>
			<div class="popModal_footer">
				<button type="button" class="btn green darken-3 waves-effect waves-light" data-popmodal-but="ok" onclick="Dashboard.updateStatus('approve_user_form', '<?php echo APPROVED ?>')">Approve</button>
				<button type="button" class="btn-flat" data-popmodal-but="cancel">Cancel</button>
			</div>
		  </form>
		</div>
	</div>
<?php 	
	
	}
?>