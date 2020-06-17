<div class="m-md">
	<input type="hidden" value="<?=$row_index?>" name="row_index">
	<h2 class="note-label">Note : Please click or select the name of customer and then click "Select" button</h2>
	<div>
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="tbl_data_list">
			<thead>
				<tr>
					<th width="20%">Profile</th>
					<th width="25%">First Name</th>
					<th width="25%">Last Name</th>
					<th width="20%">Birth Date</th>
					<th width="20%" class="center-align">Actions</th>          
					<!-- <th width="20%" class="center-align"></th> -->
				</tr>
				<tr class="table-filters">
					<td width="20%"><!-- <input name="title_id"   class="form-filter"> --></td>
					<td width="25%"><!-- <input name="first_name" class="form-filter"> --></td>
					<td width="25%"><!-- <input name="last_name"  class="form-filter"> --></td>
					<td width="20%"><!-- <input name="birth_date" class="form-filter"> --></td>
					<td width="10%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>
</div>
<style>
  h2.note-label{
    text-transform: uppercase;
    font-size: 12px;
    border: 1px solid #58bf2e;
    padding: 10px;
    margin-bottom: 10px;
    font-style: italic;
    background-color: #d7fadf;
  }
</style>