var Demo2 	= function()
{
	var modal_init 	= function()
	{

		number_init('.number_zero', 0);
		if( $('#action__inp').val() == CONSTANTS.ACTION_VIEW )
		{
			$("#submit_modal_sample").hide();
		}
		else
		{
			$("#submit_modal_sample").show();
		}

		save();
	}

	var save 		= function()
	{
		var form_s 	= 'form_modal_sample';
		var btn_s 	= 'submit_modal_sample';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley();

		$("#"+btn_s).on( "click", function( e )
		{
			$parsley.validate();

			e.preventDefault();

			e.stopImmediatePropagation();

			if( $parsley.isValid() )
			{
				var data 		= form.serialize();

				load_save(data);
			}
		});
	}

	var load_save	= function(data, form_s)
	{
		start_loading();

		$.post( $base_url + "Demo2/save/", data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				$('#modal_sample').modal("close");
				if( datatable_objects[ response.datatable_id ] !== undefined )
				{
					datatable_objects[ response.datatable_id ].ajax.reload();
				}
				
			}

			notification_msg(response.status, response.msg);
			end_loading();
		});
	}

	var extra_func 	= function(per_data)
	{
		var str 	= '';
		
		str 		= `
			<div class="pre-datatable">
		
			</div>

			<div>
				<table cellpadding="0" cellspacing="0" class="table table-default table-layout-auto" id="table_demo2_`+per_data.DT_RowId+`">
					<thead>
						<tr>
							<th width="1%"></th>
							<th width="20%">Column 1</th>
							<th width="15%">Column 2</th>
							<th width="20%">Column 3</th>
							<th width="15%" class="center-align">Actions</th>
						</tr>
						<tr class="table-filters">
							<td width="1%"><input name="" class="form-filter"></td>
							<td width="20%"><input name="column_1" class="form-filter"></td>
							<td width="15%"><input name="column_2" class="form-filter"></td>
							<td width="20%"><input name="column_3" class="form-filter"></td>
							<td width="15%" class="table-actions">
								<a href="javascript:;" class="tooltipped filter-submit" data-tooltip="Search" data-position="top" data-delay="50"><i class="material-icons">search</i></a>
								<a href="javascript:;" class="tooltipped filter-cancel" data-tooltip="Reset" data-position="top" data-delay="50"><i class="material-icons">find_replace</i></a>
							</td>
						</tr>
					</thead>
				</table>
			</div>
		`;



		/*$.post($base_url+'Demo2/get_list_reorder', {}).promise().done(function(response)
		{*/
			/*str = 'Full name: '+per_data.column_1+' '+per_data.column_2+'<br>'+
		        'Salary: '+per_data.column_3+'<br>'+
		        'The child row can contain any data you wish, including links, images, inner tables etc.';*/
		// });

		// console.log(str);
		return str;
	}

	var extra_data_after_callback = function(per_data, default_setting)
	{
		default_setting.table_id 	= "table_demo2_"+per_data.DT_RowId;
		
		load_datatable(default_setting);

	}

	return {
		save : function()
		{
			save();
		},
		modal_init : function()
		{
			modal_init();
		},
		extra_func : function(per_data)
		{
			return extra_func(per_data);
		},
		extra_data_after_callback : function(per_data, default_setting)
		{
			extra_data_after_callback(per_data, default_setting)
		},
		draw_callback : function()
		{
			$('.quick_add').on('click', function()
			{
				datatable_objects_add_row['table_demo2'].push([
					'',
					'',
					`<input type="text" name="col1[]">`,
					`<input type="text" name="col2[]">`,
					`<input type="text" name="col3[]">`,
					`
						<div class='table-actions'>
							<a onclick='Demo2.remove_row(this);' class='tooltipped cursor-pointer' data-tooltip='Delete' data-position='bottom' data-delay='50'><i class='grey-text material-icons'>delete</i></a>
						</div>
					`
				]);
				datatable_objects['table_demo2'].draw(false);
			});

			
		},
		remove_row : function(obj)
		{
			$('.tooltipped').tooltip({delay: 50});
			datatable_objects_add_row['table_demo2'].pop();
			$(obj).closest('tr').remove();
			$('.tooltipped').tooltip('remove');
		}
	};
}();