<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>Branch</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<div class="inline p-l-xs">
				<!-- <button data-target="modal_add" class="btn waves-effect waves-light green lighten-2 modal_add_trigger" name="add_branch" onclick="modal_add_init('<?php //echo $security ?>',this, 'Add Branch')" type="button"><i class="material-icons">library_add</i>Add Branch</button> -->
				<button class="btn waves-effect waves-light green lighten-2" onclick="content_form('<?php echo $security; ?>', 'file_maintenance/branch/form')">
					<i class="material-icons p-b-xxs">library_add</i> <span> Create Branch<span>
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
					<th width="15%">Branch Code</th>
					<th width="25%">Branch Name</th>
					<th width="30%">Institution Name</th>
					<th width="15%">System Date</th>
					<th width="15%" class="center-align"></th>
				</tr>
				<tr class="table-filters">
					<td width="15%"><input name="brn_code" class="form-filter"></td>
					<td width="25%"><input name="brn_name" class="form-filter"></td>
					<td width="30%"><input name="institution_name" class="form-filter"></td>
					<td width="15%"><input name="system_date" class="form-filter"></td>
					<td width="15%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>
</div>