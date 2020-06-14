<?php 
	$pending_ctr 		= $statistics['pending_count'];
	$pending_ctr_temp 		= $statistics_temp['pending_count'];
	$active_ctr 		= $statistics['active_count'];
	$approved_ctr 		= $statistics['approved_count'];
	$approved_ctr_temp 		= $statistics_temp['approved_count'];
	$disapproved_ctr 	= $statistics['disapproved_count'];
	$incomplete_ctr_temp  	= $statistics_temp['incomplete_count'];
	
	$visitor 			= get_setting(ACCOUNT, "account_creator");


	if( !EMPTY( $roles ) )
	{
		$role_key 				= array_column( $roles, 'role_code' );
		$role_name 				= array_column( $roles, 'role_name' );
		
		$roles_json 			= json_encode( array_combine( $role_key, $role_name ) );
	}
?>

<div class="page-title">
  <div class="row m-b-n">
	<div class="col s12 p-r-n">
	  <h5>Sign up</h5>
	</div>
  </div>
</div>

<input type="hidden" id="sign_up_role_json" value='<?php echo $roles_json ?>'/>

<?php if($visitor == VISITOR){ ?>
	<div class="m-md">
		<div class="page-title">
			<div class="table-display">
				<div class="table-cell valign-middle s4"><h5>User roles with permission to add users</h5></div>
				<div class="table-cell valign-middle right-align s8">
					
					<div class="input-field inline p-l-xs">
						
					</div>
				</div>
			</div>
		</div>
		<div class="pre-datatable">
			
		</div>

		<div>
			<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="table_role_user_add">
				<thead>
					<tr>
						<th width="35%">Role Code</th>
						<th width="35%">Role Name</th>
						<th width="10%" class="center-align"></th>
					</tr>
					<tr class="table-filters">
						<td width="35%"><input name="role_code" class="form-filter"></td>
						<td width="35%"><input name="role_name" class="form-filter"></td>
						<td width="10%" class="table-actions">
							<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
							<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
						</td>
					</tr>
				</thead>
			</table>
		</div>
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
				<button type="button" class="btn red darken-3 waves-effect waves-light" data-popmodal-but="ok" onclick="Sign_up.updateStatus('reject_user_form', '<?php echo DISAPPROVED ?>')">Reject</button>
				<button type="button" class="btn-flat" data-popmodal-but="cancel">Cancel</button>
			</div>
		  </form>
		</div>
	</div>
	<div class="none">
		<div id="approve_content" class="approve_content">
		  <form id="approve_user_form" class="form-basic">
			<input type="hidden" name="id" id="approve_id" value="" />
			<p class="p-n m-b-md font-bold font-sm">Are you sure you want to approve this registration?</p>
			<label class="active font-sm font-bold" for="main_role">Assign main role to this account</label>
			<div class="input-field m-t-xs">
			  <select class="selectize main_role" id="main_role" name="main_role[]" placeholder="Select Roles">
				<option value="">Select Role</option>
				<?php foreach($roles as $role): 
					$sel_main_role = "";

							if( $role['default_role_sign_up_flag'] == ENUM_YES )
							{
								$sel_main_role = "selected";								
							}
				?>
				<option <?php echo $sel_main_role ?> value="<?php echo $role['role_code'] ?>"><?php echo $role['role_name'] ?></option>
				<?php endforeach; ?>
			  </select>
			</div>
			<label class="active font-sm font-bold" for="approve_user_roles">Assign other role/s to this account</label>
			<div class="input-field m-t-xs">
			  <select class="selectize other_role" multiple id="approve_user_roles" name="role[]" placeholder="Select Roles">
				<option value="">Select Roles</option>
				<?php foreach($roles as $role): ?>
				<option value="<?php echo $role['role_code'] ?>"><?php echo $role['role_name'] ?></option>
				<?php endforeach; ?>
			  </select>
			</div>
			<div class="popModal_footer">
				<button type="button" class="btn green darken-3 waves-effect waves-light" data-popmodal-but="ok" onclick="Sign_up.updateStatus('approve_user_form', '<?php echo APPROVED ?>')">Approve</button>
				<button type="button" class="btn-flat" data-popmodal-but="cancel">Cancel</button>
			</div>
		  </form>
		</div>
	</div>
<?php 	
	
	}
?>