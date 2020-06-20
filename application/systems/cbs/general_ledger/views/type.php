<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>GL Type</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<div class="inline p-l-xs">

				<button data-target="modal_add" class="btn waves-effect waves-light green lighten-2 modal_add_trigger" name="add_type" onclick="modal_add_init('<?php echo $security ?>',this, 'Add GL Type')" type="button"><i class="material-icons">library_add</i>Add GL Type</button>
			</div>
		</div>
	</div>
</div>

<div class="view-content p-md">
	<div class="flat-tbl">
	<div>
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="tbl_data_list">
			<thead>
				<tr>
					<th width="25%">Type Code</th>
					<th width="30%">Type Name</th>
					<th width="30%">Position</th>
					<th width="15%" class="center-align">Actions</th>
				</tr>
				<tr class="table-filters">
					<td width="25%"><input name="type_code" class="form-filter"></td>
					<td width="30%"><input name="type_name" class="form-filter"></td>
					<td width="30%"><input name="position" class="form-filter"></td>
					<td width="15%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>
</div>
</div>