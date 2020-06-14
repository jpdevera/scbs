<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>Organizations</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<!-- <button class="btn waves-effect waves-light" type="button" id="refresh_btn"><i class="material-icons">refresh</i>Refresh</button> -->
			<div class="inline p-l-xs">
				<?php 
					if( $add_per ) :
				?>
				<button data-target="modal_organizations" class="btn waves-effect waves-light green lighten-2 modal_organizations_trigger" name="add_organization" onclick="modal_organizations_init()" type="button"><i class="material-icons">library_add</i>Create New</button>
				<?php 
					endif;
				?>
				 <ul class='list-action-buttons btn circles p-n action-more' >
		            <li>
		               <a class="dropdown-button more tooltipped" href="#!" data-tooltip='More' data-activates="dropdown_1" ><i class="material-icons">more_vert</i></a>
		                  <ul id="dropdown_1" class="box-shadow dropdown-content">
		                    <?php 
		                      if( $download_per ) :
		                    ?>
		                      <li>
		                         <a href='<?php echo base_url().CORE_USER_MANAGEMENT ?>/Organizations/download_template' target="_blank" class='tooltipped grey-text' data-tooltip='Download' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>file_download</i> Download Template</a>
		                      </li>
		                    <?php 
		                      endif;
		                    ?>
		                    <?php 
		                      if( $import_per ) :
		                    ?>
		                    <li>
		                       <a data-target="modal_org_import" target="_blank" class='tooltipped grey-text modal_org_import_trigger' data-tooltip='Import' onclick="modal_org_import_init('<?php echo $module ?>', this, 'Import Organizations')" data-position='bottom' data-delay='50'><i class='grey-text material-icons'>file_upload</i> Import Organizations</a>
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
		<div class="right-align col s6">
			
		</div>
	</div>
</div>
<div class="pre-datatable"></div>
<div class="m-md">
  <table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="organizations_table">
  <thead>
	<tr>
	  <th width="20%">Organization</th>
	  <th width="20%">Parent Organization</th>
	  <!-- <th width="15%">Website</th> -->
	  <th width="15%">Email</th>
	  <th width="15%">Status</th>
	  <th width="15%" class="center-align">Actions</th>
	</tr>
	<tr class="table-filters">
          <td width="20%" ><input name="A-name" class="form-filter"/></td>
          <td width="20%" ><input name="parent_name" class="form-filter"/></td>
          <!-- <td width="15%" ><input name="A-website" class="number form-filter"/></td> -->
          <td width="15%" >
          		<input name="A-email" class="number form-filter"/>
          </td>
          <td width="15%" >
          		<select name="status" class="material-select form-filter">
	              	<option value=""></option>
	              	<option value="<?php echo ENUM_YES ?>">Active</option>
	              	<option value="<?php echo ENUM_NO ?>">Inactive</option>
	            </select>
          </td>
          <td width="15%" class="table-actions">
            <a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Filter" data-position="top" data-delay="50"><i class="material-icons">filter_list</i></a>
            <a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset Filter" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
          </td>
        </tr>
  </thead>
  <tbody>
  </tbody>
  </table>
</div>