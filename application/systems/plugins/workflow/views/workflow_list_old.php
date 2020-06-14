<div class="page-title m-b-lg">
  <ul id="breadcrumbs">
	<li><a href="<?php echo base_url().PROJECT_CORE.'/dashboard' ?>">Home</a></li>
	<li><a href="#" class="active">Workflow Management</a></li>
  </ul>
  <div class="row m-b-n">
	<div class="col s6 p-r-n">
	  <h5>Workflow Management
		<span>List of dynamic workflows.</span>
	  </h5>
	</div>
	<div class="col s6 right-align">
	  <div class="btn-group">
	    <button type="button" class="waves-effect waves-light dropdown-button"  data-beloworigin="false" data-activates="dropdown-download-to"><i class="flaticon-inbox36"></i> Download</button>
	  </div>
	  
	  <div class="input-field inline p-l-md">
	    <button class="btn waves-effect waves-light btn-success" name="add_user" id="add_user" type="button" onclick="content_form('manage_workflow/create#tab_workflow_process', '<?php echo PROJECT_CORE ?>')">Add Workflow</button>
	  </div>
	</div>
  </div>
</div>

<div class="pre-datatable"></div>
<div>
  <table cellpadding="0" cellspacing="0" class="table table-advanced table-layout-auto" id="workflow_table">
  <thead>
	<tr class="row-subheader">
	  <th width="8%" class="center-align">ID</th>
	  <th width="25%">Name</th>
	  <th width="30%">Description</th>
	  <th width="10%" class="center-align">No. Stages</th>
	  <th width="15%">Created By</th>
	  <th width="12%" class="center-align">Actions</th>
	</tr>
  </thead>
  <tbody>
  </tbody>
  </table>
</div>

<script type="text/javascript">
  var deleteObj = new handleData({ controller : 'manage_workflow', method : 'delete_workflow', module: '<?php echo PROJECT_CORE ?>'});
</script>