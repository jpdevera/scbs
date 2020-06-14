var Permissions = function()
{
	var $module = "user_management";
	
	var initForm = function()
	{
		$('#system_filter').on('change', function(){
			$('#role_filter').selectize();
			var system_val = $(this).val();
				role = $('#role_filter')[0].selectize;
			
			role.disable();
			
			$.post($base_url + $module + "/permissions/reset_options/" + system_val, function(result){
				role.clearOptions();
				role.load(function(callback) {
					callback(result);
					role.enable();
				});
			}, 'json');
		});
		
		$('#role_filter').on('change', function(){
			var val = $(this).val();
			
			if(val === ""){
				$("#programs_tbody").html("<tr><td colspan='4' style='text-align:center;'>Please select system and role first.</td></tr>");
				$("#save_permission").prop("disabled", true);
				$('#reset_default').prop("disabled", true);
			} else {
			  $("#programs_tbody").html("");
			  loadPermission();
			}
		});
		
		if( $('#system_filter').val() != '' )
		{
			$('#system_filter').trigger('change');
		}

		$('#check_all').on('change', function(){
			var checked = $(this).prop("checked");
			
			$("input:checkbox").prop('checked', checked);

			var selects = $('select.selectize-permissions').not('.filter');
			
			selects.each(function( index ) {
				if(checked){
					$(this)[0].selectize.enable();
				} else {
					$(this)[0].selectize.disable();
				}
			}); 
			
		});
		
	}
	
	var initCheckbox = function()
	{
		$(document).on('change', '.ind_checkbox', function(){
			var element = $(this);
			var checked = $(this).prop('checked');
			var selects = $(this).closest('tr').find('select');
			var action_sel = selects[0].selectize;
			
			var scope_sel = (selects.hasOwnProperty(1)) ? selects[1].selectize : 0;

			var self 		= this;
			
			if(checked)
			{
				par_check_proc( self );

				action_sel.enable();
				
				if(scope_sel !== 0)
					scope_sel.enable();
				
				checkSelected();
			}
			else
			{
				if(action_sel.getValue() != ""){
					$('#confirm_modal').confirmModal({
						topOffset : 0,
						onOkBut : function() {

							par_check_proc( self );
							action_sel.clear();
							action_sel.disable();
							
							if(scope_sel !== 0){
								scope_sel.clear();
								scope_sel.disable();
							}
							
							$('#check_all').prop("checked", false);
						},
						onCancelBut : function() {
							element.prop("checked", true);
						},
						onLoad : function() {
							$('.confirmModal_content h4').html('Are you sure you want to remove this module permission?');	
							$('.confirmModal_content p').html('This action will clear all selected module actions and scope and cannot be undone.');
						},
						onClose : function() {}
					});
				} else {
					par_check_proc( self );

					action_sel.disable();
					
					if(scope_sel !== 0){
						scope_sel.disable();
					}
				
					$('#check_all').prop("checked", false);
				}
			}
		});
	}

	var par_check_proc = function( obj )
	{
		var parent_code = $(obj).attr('data-checkbox');

		var check_ch 	= $(obj).parents('table').find('input[type="checkbox"][data-checkbox="'+parent_code+'"]:checked');

		var par_tr 		= $(obj).parents('table').find('tr.'+parent_code+'_tr');

		if( par_tr.length !== 0 )
		{
			var sel_par 	= par_tr.find('td select.selectize-permissions');
			var par_check 	= par_tr.find('td input[type="checkbox"]');

			if( check_ch.length !== 0 )
			{
				// console.log(sel_opt);
				sel_par[0].selectize.enable();
				sel_par[0].selectize.destroy();

				if( sel_par[1] !== undefined )
				{
					sel_par[1].selectize.enable();
					sel_par[1].selectize.destroy();					
				}

				var sel_opt 	= sel_par.find('option');
				
				var par_val;

				if( sel_opt.length !== 0 )
				{

					var i 	= 0,
						len = sel_opt.length;
						
					for( ; i < len; i++ )
					{
						if( $(sel_opt[i]).val() != '' )
						{
							par_val = $(sel_opt[i]).val();

							break;
						}
					}
				}

				sel_par.selectize({plugins: ['remove_button']});
				sel_par[0].selectize.addItem(par_val);
				par_check.prop('checked', true);
			}
			else
			{
				sel_par.val('');

				if( sel_par[0].selectize === undefined )
				{
					sel_par.selectize({plugins: ['remove_button']});
				}
								
				sel_par[0].selectize.clear();
				sel_par[0].selectize.disable();

				if( sel_par[1] !== undefined )
				{
					sel_par[1].selectize.clear();
					sel_par[1].selectize.disable();					
				}
				
				par_check.prop('checked', false);
			}

		
			par_check_proc(par_tr.find('.ind_checkbox'));
		}
	}

	var load_save = function(data)
	{
		$.ajax({
			url : $base_url + $module + '/permissions/save',
			data : data,		
			dataType : 'json',
			method : 'POST',
			success : function(response){
				notification_msg(response.status, response.msg);
				button_loader("save_permission", 0);
				end_loading();
			},
			error : function(jqXHR, textStatus, errorThrown){
				console.log('Error : '+textStatus);
				end_loading();
			}
		});
	}
	
	var save = function()
	{
		$('#permission_form').on('submit', function(e){
			e.preventDefault();

			var data = $(this).serialize();			
			
			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					data += '&default=1';
					start_loading();
					button_loader("save_permission", 1);
					load_save(data);
				},
				onCancelBut : function() 
				{
					button_loader("save_permission", 1);
					start_loading();
					load_save(data);
				},
				onLoad : function() {
					$('.confirmModal_content h4').html('Are you sure you want to assign the selected permissions?');	
					// $('.confirmModal_content p').html('This will save the permission set up as default.');

					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('Save as Default');
					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').addClass('font-sm');

					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="cancel"]').text('Save');
					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="cancel"]').addClass('btn btn-success font-sm bg green');
				},
				onClose : function() 
				{
					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('Ok');
					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').removeClass('font-sm bg green');

					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="cancel"]').text('Cancel');
					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="cancel"]').addClass('font-sm');
				}
			});
			
		});	

		$('#reset_default').on('click', function(e)
		{
			e.preventDefault();

			e.stopImmediatePropagation();

			var data = $('#permission_form').serialize();

			data 	+= '&default=1&system='+ $('#system_filter').val()+'&role='+$('#role_filter').val();

			button_loader("reset_default", 1);
			$('#programs_tbody').isLoading();		
			$.ajax({
				url : $base_url + $module + '/permissions/get_permission/',
				data : data,		
				dataType : 'html',
				method : 'POST',
				success : function(response){
					
					$('#programs_tbody').html('');
					$('#programs_tbody').isLoading("hide").html(response);
					$('#programs_tfoot').show();
					$('select.selectize-permissions').not('.filter').selectize({
						plugins: ['remove_button']
					});
					checkSelected();
					
					$("#save_permission").prop("disabled", false);
					$('#reset_default').prop("disabled", false);

					button_loader("reset_default", 0);
				},
				error : function(jqXHR, textStatus, errorThrown){
					console.log('Error : '+textStatus);

					$("#save_permission").prop("disabled", true);
					$('#reset_default').prop("disabled", true);	
				}
			});
		})
	}
	
	var loadPermission = function()
	{
		$('#programs_tbody').isLoading();
		var form_data = { 'system' :  $('#system_filter').val(), 'role' : $('#role_filter').val() };
		$.ajax({
			url : $base_url + $module + '/permissions/get_permission',
			method : 'POST',
			dataType : 'html',
			data : form_data,
			success : function(response){
				$('#programs_tbody').html('');
				$('#programs_tbody').isLoading("hide").html(response);
				$('#programs_tfoot').show();
				$('select.selectize-permissions').not('.filter').selectize({
					plugins: ['remove_button']
				});
				checkSelected();

				process_add_row();
				
				$("#save_permission").prop("disabled", false);
				$('#reset_default').prop("disabled", false);
			},
			error : function(jqXHR, textStatus, errorThrown){
				console.log('Error : '+textStatus);
				$("#save_permission").prop("disabled", true);
				$('#reset_default').prop("disabled", true);	
			}
		});	
	}

	var process_add_row = function()
	{
		var selectedValue 	= {};

		$('.add-permission-row').on('click', function( e )
		{
			e.preventDefault();
			e.stopImmediatePropagation();

			var rows 		= $('#programs_tbody').find( 'select.selectize-permissions' ),
				i 			= 0,
	 	 	 	len;

			if( rows.length !== 0 )
			{
				len 	= rows.length;

				for( ; i < len; i++ )
				{
					if( rows[i].selectize !== undefined )
					{
						var select_id 	= ( $(rows[i]).attr('id') === undefined ) ? i : $(rows[i]).attr('id'),
						parent 			= i;

						selectedValue[ parent+'_'+select_id ] = rows[i].selectize.getValue();

						rows[i].selectize.destroy();

						$(rows[i]).val('');
					}
				}
			}

			var parent_tr 	= $(this).closest('tr');
			var clone_tr 	= $(this).closest('tr').clone();


			clone_tr.find('.ind_checkbox').addClass('hide');
			clone_tr.find('.add-permission-row').addClass('hide');
			clone_tr.find('.ind_checkbox').next().addClass('hide');

			clone_tr.find('td:eq(3)').after(`<td>
					<a href="#" class="btn-floating remove-permission-row red cyan-text accent-4"><i class="material-icons">delete</i>Remove</a>
				</td>
			`);

			// clone_tr;
			
			var html_to_copy 	= clone_tr.wrap('<div/>').parent()
									.html();
			
			parent_tr.after(html_to_copy);

			var rows 	= $( '#programs_tbody' ).find('select.selectize-permissions'),
				i 		= 0,
				len 	= rows.length,
				j 		= 0,
				len2;

			if( len !== 0 )
	 	 	{
 	 		 	$( '#programs_tbody' ).find('select.selectize-permissions').selectize({
		 	 		plugins: ['remove_button']
		 	 	});

		 	 	for( ; i < len; i++ )
		 	 	{
		 	 		if( rows[i].selectize !== undefined )
		 	 		{

		 	 			var select_id 	= ( $(rows[i]).attr('id') === undefined ) ? i : $(rows[i]).attr('id'),
	 	 					parent_id 	= i;

	 	 				if( selectedValue[ parent_id + '_' + select_id ] !== undefined )
	 	 				{
	 	 					if( rows[i].selectize.getOption(selectedValue[ parent_id + '_' + select_id ]).length === 0 )
	 	 					{
	 	 						rows[i].selectize.addOption( { value : selectedValue[ parent_id + '_' + select_id ], text : selectedValue[ parent_id + '_' + select_id  ] } );
	 	 					}
	 	 					
	 	 				}
						
						rows[i].selectize.setValue( selectedValue[ parent_id + '_' + select_id ] );

						delete selectedValue[ parent_id + '_' + select_id ];
		 	 		}
		 	 	}
	 	 	}

	 	 	remove_permission_row();

		});
	}

	var remove_permission_row = function()
	{
		$('.remove-permission-row').on('click', function(e)
		{
			e.preventDefault();
			e.stopImmediatePropagation();

			$(this).closest('tr').remove();
		});
	}
	
	var checkSelected = function()
	{
		if($('.ind_checkbox:checked').length == $('.ind_checkbox').length){
			$('#check_all').prop("checked", true);
		} else {
			$('#check_all').prop("checked", false);
		}
	}

	return {
		initForm : function()
		{
			initForm();
		},
		initCheckbox : function()
		{
			initCheckbox();
		},
		save : function()
		{
			save();
		}
	}
}();