<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s6"><h5>Holidays</h5></div>
		<div class="table-cell valign-middle right-align s6">
			<div class="inline p-l-xs">
				<button data-target="modal_add" class="btn waves-effect waves-light green lighten-2 modal_add_trigger" name="add_type" onclick="modal_add_init('<?php echo $security ?>',this, 'Add Holidays')" type="button"><i class="material-icons">library_add</i>Add Holidays</button>
			</div>
		</div>
	</div>
</div>

<div class="form-basic">
	<div class="tabs-wrapper full">
		<div>
	    	<ul class="tabs row">
				<li class="tab col s6">
		  			<a href="#list_view" onclick="">
	  					<i class="material-icons">folder</i>
		  				<span class="hide-on-med-and-down">List View</span>
		  			</a>
		  		</li>
		  		<li class="tab col s6">
		  			<a href="#calendar_view" onclick="" >
		  				<i class="material-icons">folder</i>
		  				<span class="hide-on-med-and-down">Calendar View</span>
		  			</a>
		  		</li>
		  	</ul>
		</div>
	</div>
</div>
<div class="form-basic panel">
	<!-- Start of List View -->
	<div id="list_view">
		<div class="m-md">
			<div>
				<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="tbl_data_list">
					<thead>
						<tr>
							<th width="25%">Title</th>
							<th width="30%">Description</th>
							<th width="30%">Date</th>
							<th width="15%" class="center-align"></th>
						</tr>
						<tr class="table-filters">
							<td width="25%"><input name="holiday_title" class="form-filter"></td>
							<td width="30%"><input name="holiday_desc" class="form-filter"></td>
							<td width="30%"><input name="holiday_date" class="form-filter"></td>
							<td width="15%" class="table-actions">
								<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
								<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
							</td>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
	<!-- End of List View -->
	<div id="calendar_view">
		<div class="m-md">
			<div id="calendar" class="flat-calendar fc-title-white"></div>
		</div>
	</div>
</div>