<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s12"><h5>Application Logs</h5></div>
		<div class="table-cell valign-middle right-align s12">
			
		</div>
	</div>
	<div class="row m-b-n right-align col s12">
		<div class="col s3"><label class="filter-label">Filter by System</label></div>
		<div class="input-field filter-field col s3 m-t-n">
			<select name="filter_audit_log" id="filter_audit_log" class="material-select" placeholder="Select System">
				<!-- <option value="0">All</option> -->
				<?php foreach($tabs as $key => $tab): 
					$sel = ( $key == 0 ) ? 'selected' : '';
				?>
				<option <?php echo $sel ?> value="<?php echo $tab ?>"><?php echo $tab ?></option>
				<?php endforeach; ?>
		   </select>
		</div>
	</div>
</div>
<div class="m-md">
	<div class="pre-datatable"></div>
	<div>
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-fixed" id="table_application_logs">
			<thead>
				<tr>
					<th width="50%">Log File</th>
					<th width="8%" class="text-center">Actions</th>
				</tr>
				<tr class="table-filters">
		          <td width="50%" ><input name="log_file" class="form-filter"/></td>
		          <td width="8%" class="table-actions">
		            <a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Submit" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
		            <a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
		          </td>
		        </tr>
			</thead>
		</table>
	</div>
</div>