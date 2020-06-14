<div class="m-md">
	<div class="pre-datatable"></div>
	<div>
		<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="<?php echo $table_id ?>">
			<thead>
				<tr>
					<th width="15%">From</th>
					<th width="10%" class="center-align">To</th>
					<th width="10%" class="center-align">Message</th>
					<th width="10%" class="center-align">Sent Date</th>
					<th width="10%" class="center-align">Sent Flag</th>
					<th width="12%" class="center-align">&nbsp;</th>
				</tr>
				<tr class="table-filters">
					<td width="15%" ><input name="from_user" class="form-filter"/></td>
					<td width="10%" ><input name="to_user" class=" form-filter"/></td>
					<td width="10%" ><input name="message" class=" form-filter"/></td>
					<td width="10%" ><input name="created_date_format" class=" form-filter"/></td>
					<td width="10%" ><input name="sent_flag" class=" form-filter"/></td>
					<td width="12%" class="table-actions">
						<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Submit" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
						<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
					</td>
				</tr>
			</thead>
		</table>
	</div>
</div>
