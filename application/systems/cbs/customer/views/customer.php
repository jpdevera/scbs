<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>Customer</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<div class="inline p-l-xs">
				<button class="btn waves-effect waves-light" onclick="content_form('<?php echo $security; ?>', 'customer/customer/form')">
					<i class="material-icons p-b-xxs">library_add</i> <span> Create Customer<span>
				</button>
			</div>
		</div>
	</div>
</div>
<div class="m-md">
	<div>
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="tbl_data_list">
			<thead>
				<tr>
					<th width="20%">Profile</th>
					<th width="20%">First Name</th>
					<th width="20%">Last Name</th>
					<th width="20%">Birth Date</th>
					<th width="20%" class="center-align"></th>
				</tr>
				<tr class="table-filters">
					<td width="20%"><input name="title_id"   class="form-filter"></td>
					<td width="20%"><input name="first_name" class="form-filter"></td>
					<td width="20%"><input name="last_name"  class="form-filter"></td>
					<td width="20%"><input name="birth_date" class="form-filter"></td>
					<td width="20%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>
</div>