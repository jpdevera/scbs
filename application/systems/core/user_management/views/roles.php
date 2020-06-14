<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>Roles</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<button class="btn waves-effect waves-light" type="button" id="refresh_btn"><i class="material-icons">refresh</i>Refresh</button>
			<div class="inline p-l-xs">
        <?php 
          if( $add_per ) :
        ?>
				<button data-target="modal_roles" class="btn waves-effect waves-light green lighten-2" name="add_role" onclick="modal_roles_init()" type="button"><i class="material-icons">library_add</i>Create New</button>
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
    <table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="roles_table">
      <thead>
       <tr>
         <th width="20%">Code</th>
         <th width="28%">Role</th>
         <th width="30%">Accessible System/s</th>
         <th width="12%">Built In</th>
         <th width="0%">&nbsp;</th>
         <th width="10%" class="text-center">Actions</th>
       </tr>
       <tr class="table-filters">
          <td width="20%" ><input name="A-role_code" class="form-filter"/></td>
          <td width="28%" ><input name="A-role_name" class="form-filter"/></td>
          <td width="30%" ><input name="C-system_name" class="number form-filter"/></td>
          <td width="12%" >
            <select name="status" class="material-select form-filter">
                      <option value=""></option>
                      <option value="<?php echo ENUM_YES ?>">Yes</option>
                      <option value="<?php echo ENUM_NO ?>">No</option>
                  </select>
          </td>
          <td width="0%">&nbsp;</td>
          <td width="10%" class="table-actions">
            <a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Filter" data-position="top" data-delay="50"><i class="material-icons">filter_list</i></a>
            <a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset Filter" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
          </td>
        </tr>
     </thead>
   </table>
 </div>
</div>