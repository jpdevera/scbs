<?php 
$active 	= "Active <span>" . $group_active_cnt . "</span>";
$inactive 	= "Inactive <span>" . $group_inactive_cnt . "</span>";
?>
<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s2"><h5>Groups</h5></div>
		<div class="table-cell valign-middle s6 center-align">
			<!-- <ul class="list link-tab inline m-l-sm">
				<li><a href="javascript:;" class="link-filter active" id="link_active_btn"><?php echo $active ?></a></li>
				<li><a href="javascript:;" class="link-filter" id="link_inactive_btn"><?php echo $inactive ?></a></li>
			</ul> -->
		</div>
		<div class="table-cell valign-middle right-align s4">
			<div class="inline p-l-xs">
				<?php 
					if( $add_per ) :
				?>
				<button data-target="modal_groups" class="btn waves-effect waves-light green lighten-2 modal_groups_trigger" name="add_groups" onclick="modal_groups_init()" type="button"><i class="material-icons">library_add</i>Create New</button>
				<?php 
					endif;
				?>
			</div>
		</div>
	</div>
</div>

<div class="m-md">
	<div class="pre-datatable"></div>
	<div>
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="groups_main_table">
			<thead>
				<tr>
					<th width="10%" class="center-align">Group Color</th>
					<th width="10%" class="center-align">No of Members</th>
					<th width="25%">Group Name</th>
					<th width="20%">Group Description</th>
					
					<th width="10%" class="center-align">Status</th>
					<th width="12%" class="center-align">Actions</th>
				</tr>
				<tr class="table-filters">
					<td width="10%" ><input name="a-group_color" class=" form-filter"/></td>
					<td width="10%" ><input name="member_no" class=" form-filter"/></td>
					<td width="25%" ><input name="a-group_name" class="form-filter"/></td>
					<td width="20%" ><input name="a-group_description" class="form-filter"/></td>
					
					<td width="10%" >
						<select name="status" class="material-select form-filter">
			              	<option value=""></option>
			              	<option value="<?php echo ENUM_YES ?>">Active</option>
			              	<option value="<?php echo ENUM_NO ?>">Inactive</option>
			            </select>
					</td>
					<td width="12%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Submit" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>
</div>