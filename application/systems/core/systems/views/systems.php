<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>Systems</h5></div>
		<!-- <div class="table-cell valign-middle right-align s6">
			<button class="btn waves-effect waves-light" type="button" id="refresh_orgs"><i class="material-icons">refresh</i>Refresh</button>
			<div class="input-field inline p-l-xs">
				<button data-target="modal_systems" class="btn waves-effect waves-light green lighten-2 modal_systems_trigger" name="add_organization" onclick="modal_systems_init()" type="button"><i class="material-icons">library_add</i>Create New</button>
			</div>
		</div> -->
	</div>
</div>
<div class="pre-datatable"></div>
<div class="m-md">
	<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="systems_table">
		<thead>
			<tr>
				<th width="25%">System Name</th>
				<th width="25%">Description</th>
				<th width="20%">Status</th>
				<th width="10%" class="text-center">Actions</th>
			</tr>
			<tr class="table-filters">
				 <td width="25%" ><input name="A-system_name" class="form-filter"/></td>
		          <td width="25%" ><input name="A-description" class="form-filter"/></td>
		          <td width="20%" >
		          	 <select name="status" class="material-select form-filter">
	                      <option value=""></option>
	                      <option value="Active">Active</option>
	                      <option value="Inactive">Inactive</option>
	                  </select>
		          </td>
		          <td width="10%" class="table-actions">
		            <a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Submit" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
		            <a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
		          </td>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>