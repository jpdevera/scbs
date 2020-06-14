<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>Announcements</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<button class="btn waves-effect waves-light" type="button" id="refresh_btn"><i class="material-icons">refresh</i>Refresh</button>
		<?php 
			if( $add_per ) :
		?>
			<button href='#modal_announcement' data-target='modal_announcement' data-modal_post='' class="btn waves-effect waves-light green lighten-2 modal_announcement_trigger" name="add_announcement" onclick="modal_announcement_init('', this, 'Add Announcement')" type="button"><i class="material-icons">library_add</i>Create New</button>
		<?php 
			endif;
		?>
		</div>
	</div>
</div>
<div class="pre-datatable"></div>
<div class="m-md">
	<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="<?php echo $table_id ?>">
		<thead>
			<tr>
				<th width="50%">Annoucement</th>
				<th width="10%" class="text-center">Actions</th>
			</tr>
			<tr class="table-filters">
				<td width="50%" ><input name="announcemet" class="form-filter"/></td>
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