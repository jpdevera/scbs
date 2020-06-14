<?php 
$active 	= "Active <span>" . $workflow_active_cnt . "</span>";
$inactive 	= "Inactive <span>" . $workflow_inactive_cnt . "</span>";
$append 	= "Attachable <span>" . $workflow_append_cnt . "</span>";
?>
<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s2"><h5>Workflow</h5></div>
		<div class="table-cell valign-middle s6 center-align">
			
			<ul class="list link-tab inline m-l-sm">
				<li><a href="javascript:;" class="link-filter active" id="link_active_btn"><?php echo $active ?></a></li>
				<li><a href="javascript:;" class="link-filter" id="link_inactive_btn"><?php echo $inactive ?></a></li>
				<li><a href="javascript:;" class="link-filter" id="link_append_btn"><?php echo $append ?></a></li>
			</ul>
		</div>
		<div class="table-cell valign-middle right-align s4">
			<?php
				if( $setting_per ) :
			?>
				<a class="btn waves-effect waves-light grey darken-1 white-text" target="_blank" href="<?php echo $workflow_setting_url ?>" ><i class="material-icons">settings</i>Settings</a>
			<?php 
				endif;
			?>
			<div class="inline p-l-xs">
				<?php 
					if( $add_per ) :
				?>
				<a class="btn waves-effect waves-light green lighten-2 white-text" href="<?php echo base_url().CORE_WORKFLOW ?>/Manage_workflow/create_new" name="add_workflow" type="button"><i class="material-icons">library_add</i>Create New</a>
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
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="workflow_table">
		 	<thead>
				<tr class="row-subheader">
					<th width="25%">Name</th>
					<th width="20%">Description</th>
					<th width="10%" class="center-align">No. Stages</th>
					<th width="10%" class="center-align">Status</th>
					<th width="12%" class="center-align">Actions</th>
				</tr>
				<tr class="table-filters">
					<td width="25%" ><input name="a-workflow_name" class="form-filter"/></td>
					<td width="20%" ><input name="a-description" class="form-filter"/></td>
					<td width="10%" ><input name="count_stages" class="number form-filter"/></td>
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