<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>GL Account</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<div class="inline p-l-xs">
				<button data-target="modal_add" class="btn waves-effect waves-light green lighten-2 modal_add_trigger" name="add_sortcode" onclick="modal_add_init('<?php echo $security ?>',this, 'Add GL Account')" type="button"><i class="material-icons">library_add</i>Add GL Account</button>
			</div>
		</div>
	</div>
</div>

<div class="m-md">
	<div>
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="tbl_data_list">
			<thead>
				<tr>
					<th width="25%">Account Code</th>
					<th width="30%">Account Name</th>
					<th width="30%">Sort</th>
					<th width="15%" class="center-align"></th>
				</tr>
				<tr class="table-filters">
					<td width="25%"><input name="acct_code" class="form-filter"></td>
					<td width="30%"><input name="acct_name" class="form-filter"></td>
					<td width="30%"><input name="sort_name" class="form-filter"></td>
					<td width="15%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>
	</div>