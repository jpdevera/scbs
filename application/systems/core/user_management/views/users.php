<?php 
$active 	= "Active <span>" . $statistics['active_count'] . "</span>";
$inactive 	= "Inactive <span>" . $statistics['inactive_count'] . "</span>";
$blocked 	= "Blocked <span>" . $statistics['blocked_count'] . "</span>";
?>

<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s2"><h5>Users</h5></div>
		<div class="table-cell valign-middle s6 center-align">
			
			<ul class="list link-tab inline m-l-sm">
				<li><a href="javascript:;" class="link-filter active" id="link_active_btn"><?php echo $active ?></a></li>
				<li><a href="javascript:;" class="link-filter" id="link_inactive_btn"><?php echo $inactive ?></a></li>
				<li><a href="javascript:;" class="link-filter" id="link_blocked_btn"><?php echo $blocked ?></a></li>
			</ul>
		</div>
		<div class="table-cell valign-middle right-align s4">
			<!-- <button class="btn waves-effect waves-light" type="button" id="refresh_btn"><i class="material-icons">refresh</i>Refresh</button> -->
			<div class="inline p-l-xs">
        <?php 
          if( $add_per ) :
        ?>
				<button data-target="modal_user_mgmt" class="btn waves-effect waves-light green lighten-2 modal_user_mgmt_trigger" name="add_user" onclick="modal_user_mgmt_init('<?php echo $security ?>',this, 'Add User')" type="button"><i class="material-icons">library_add</i>Create New</button>
        <?php 
          endif;
        ?>
          <ul class="users-more action-more btn">
            <li>
               <a class="dropdown-button more tooltipped" href="#!" data-tooltip='More' data-activates="dropdown_1" ><i class="material-icons">more_vert</i></a>
                  <ul id="dropdown_1" class="box-shadow dropdown-content">
                    <?php 
                      if( $download_per ) :
                    ?>
                      <li>
                         <a href='<?php echo base_url().CORE_USER_MANAGEMENT ?>/Users/download_template' target="_blank" class='tooltipped grey-text' data-tooltip='Download' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>file_download</i> Download Template</a>
                      </li>
                    <?php 
                      endif;
                    ?>
                    <?php 
                      if( $import_per ) :
                    ?>
                    <li>
                       <a data-target="modal_org_import" target="_blank" class='tooltipped grey-text modal_org_import_trigger' data-tooltip='Import' onclick="modal_org_import_init('<?php echo $module ?>', this, 'Import Users')" data-position='bottom' data-delay='50'><i class='grey-text material-icons'>file_upload</i> Import Users</a>
                    </li>
                    <?php 
                      endif;
                    ?>
                  </ul>
            </li>
          </ul>
			</div>
		</div>
	</div>
  <div class="row p-t-md p-r-n">
      <div class="left-align col s6"></div>
       
  </div>
</div>

<div class="m-md">
  <div class="pre-datatable"></div>
  <div>
    <table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="users_table">
      <thead>
        <tr>
          <th width="17%">Username</th>
          <th width="15%">Name</th>
          <th width="10%">Organizations</th>
          <th width="10%">Email</th>
          <th width="10%">Roles</th>
          <th width="15%">Created By</th>
          <th width="18%" class="center-align col-actions">Actions</th>
        </tr>
        <!-- For Advanced Filters -->
        <tr class="table-filters">
          <td width="17%" ><input name="username" class="form-filter" /></td>
          <td width="15%" ><input name="merge_name" class="form-filter"/></td>
          <td width="10%" ><input name="organization_name" class="form-filter"/></td>
          <td width="10%" ><input name="email" class="form-filter"/></td>
          <td width="10%" ><input name="roles" class="form-filter"/></td>
          <td width="15%" ><input name="date_created" class="form-filter"/></td>
          <td width="18%" class="table-actions">
            <a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Filter" data-position="top" data-delay="50"><i class="material-icons">filter_list</i></a>
            <a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset Filter" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
          </td>
        </tr>
      </thead>
    </table>    
  </div>
</div>