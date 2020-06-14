<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s12"><h5>Permissions</h5></div>
	</div>
</div>

<form id="permission_form">
	<div class="bg-white m-md m-t-n box-shadow">
	  <div class="table-display">
		<div class="table-cell s7 p-md b-r valign-top" style="border-style:dashed!important; border-right-color:#eee!important;">
		  <label class="active block m-b-sm">System</label>
		  <select name="system_filter" id="system_filter" class="selectize-permissions filter">
			<option value="">Select System</option>
			<?php  foreach($systems as $key => $val) : ?>
			  <option value="<?php echo $val['system_code']; ?>"><?php echo $val['system_name']; ?></option>
			<?php  endforeach; ?>
		  </select>
	    </div>
		<div class="table-cell s5 p-md valign-top">
		  <label class="active block m-b-sm">Role</label>
		  <select name="role_filter" id="role_filter" class="selectize-permissions filter" disabled></select>
	    </div>
	  </div>
	</div>


	<div class="m-md box-shadow">
	  <table cellpadding="0" cellspacing="0" class="table table-default" id="programs_table">
	  <thead style="background: #808080	 !important;">
		<tr>
		  <th width="5%" class="p-l-md">
		  	<input type="checkbox" name="check_all" id="check_all" />
		  	<label for="check_all"></label>
		  </th>
		  <th width="25%">Module</th>
		  <th width="40%">Permission</th>
		  <th width="30%">Scope</th>
		  <th width="5%"></th>
		</tr>
	  </thead>
	  <tbody id="programs_tbody">
	  	<tr>
	  		<td colspan='4' style='text-align:center;'>Please select system and role first.</td>
	  	</tr>
	  </tbody>
	  <tfoot id="programs_tfoot" >
	  	<tr>
	  		<td colspan="5" class="right-align p-sm p-r-md">
	  			<?php 
	  				if( $module_per ) :
	  			?>
	  		  <button class="btn waves-effect" id="save_permission" name="save_permission" type="submit" value="Save" disabled data-btn-action="<?php echo BTN_SAVING; ?>">Save</button>

	  		  <button class="btn waves-effect" id="reset_default" name="reset_default" type="button" value="Reset to default" disabled data-btn-action="Resetting">Reset to Default</button>
	  		  <?php 
	  		  		endif;
	  		  ?>
	  		</td>
	  	</tr>
	  </tfoot>
	  <!-- <tbody id="programs_tbody">
	  	<tr>
	  		<td colspan='4' style='text-align:center;'>Please select system and role first.</td>
	  	</tr>
	  </tbody> -->
	  </table>
	</div>
</form>