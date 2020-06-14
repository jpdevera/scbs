var Auditlog 	= function()
{
	var $module = "audit_trail";

	var REPORT_TYPE 	= {
		1 : function( options, form, url ) {
			_pdf( options, form, url )
		},
		2 : function( options, form, url ) {
			_excel( options, form, url )
		}
	}

	var download_btn = function()
	{
		$('.btn_download').on('click', function( e )
		{
			e.stopImmediatePropagation();

			var data 		= '',
				pop_data 	= $('#download_form').serializeArray(),
				pop_data_str= $('#download_form').serialize(),
				that 		= this,
				form,
				url 		= $base_url + 'audit_trail/audit_log/print_form',
				i 			= 0,
				len,
				form_data 	= '';

			var post 		= {};
			var dy_form;

			var sSearch     = $('.dataTables_filter').find('input').val();
			var filter_by 	= $("#filter_audit_log").val();

			if( pop_data.length !== 0 )
			{
				len 		= pop_data.length;
				var arr 	= [];
				var cur_key;

				for( ; i < len; i++ )
				{
					var regex = /[\[]/gi;
					var check = pop_data[i].name.match(regex);

					if( check !== null )
					{
						if( data[i].value != '' )
						{
							arr.push( pop_data[i].value );
						}

						post[ pop_data[i].name ] 	= arr;

						if( pop_data[i+1] !== undefined )
						{
							cur_key 	= pop_data[i+1].name;
						}
						
						if( cur_key != pop_data[i].name )
						{
							arr 	= [];
						}
					}
					else
					{
						post[ pop_data[i].name ]	= pop_data[i].value;
					}
				}
			}

			$( that ).append( '<form id="print_form" action="'+url+'" method="post" target="_blank">');

			dy_form 		= $('#print_form');
			dy_form.append('<input type="hidden" value="'+sSearch+'" name="sSearch">');
			dy_form.append('<input type="hidden" value="'+filter_by+'" name="system_code">');

			for( var key in post )
			{ 
				if( post[key] instanceof Array )
				{
					var l 	= post[key].length,
						z 	= 0;
					
					if( l !== 0 )
					{
						for( ; z < l; z++ )
						{
							if( post[key][z] != ""  )
							{
								var input = $("<input name='"+key+"' class='custom_data'>").attr("type", "hidden").val( post[key][z] );
								
								dy_form.append($(input));
							}
						}
					}
				}	
				else
				{
					var input = $("<input name='"+key+"' class='custom_data'>").attr("type", "hidden").val( post[key] );

					dy_form.append($(input));
				}
			}

			dy_form.append('</form>');

			form 			= dy_form;

			data			+= 'report='+$( this ).attr( 'data-report' )+'&'+pop_data_str;

			start_loading();

			$.when( $.post( $base_url+'audit_trail/audit_log/validate_print', data ) ).then( function( response ) {
				response = JSON.parse(response);

				var modal_error 		= $( '#modal_general_error' );

				if( response.flag == 1 )
				{
					form.append( '<input type="hidden" value="'+response.report+'" name="report">' );

					REPORT_TYPE[ response.report ]( response, form, url );
				}
				else
				{
					notification_msg('error', response.msg);
					/*var modal_obj 		= $('#modal_error_audit').modal({
						dismissible 	: true,
						opacity 		: .5,
						in_duration 	: 300,
						out_duration 	: 200,
						ready: function() {
							$.post($base_url+'audit_trail/audit_log/modal_error/'+response.msg, {} ).promise().done( function( response ) 
							{
								$("#modal_error_audit .modal-content #content").html( response )	
							});
							
						},
						complete: function() {

						}
					});

					modal_obj.trigger('openModal');*/
					
					/*if( modal_error.length !== 0 )
					{
						modal_error_init( '401');
						modal_error.addClass( 'md-show' );

						$('.md-close, .cancel_modal').click( function () {
							modal_error.removeClass( 'md-show' );
						} );
					}*/
				}

				// form.remove();
				end_loading();
			}, function() {
				end_loading();
				form.remove();
			} );
		});				
	}

	var _pdf 		= function( options, form, url )
	{
		form.submit();
		form.remove();
	}

	var _excel 		= function( options, form, url )
	{
		form.submit();
		form.remove();
	}

	var archived_btn = function()
	{
		var form_s 	= 'archive_form';
		var btn_s 	= 'archived_btn';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley();

		$("#"+btn_s).on( "click", function( e )
		{
			// update_editor();
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

	var change_archive = function()
	{
		$('#change_archive').on('click', function(e)
		{
			e.stopImmediatePropagation();
			e.preventDefault();

			var data = {

				archived : 0
			};

			if( $(this).is(':checked') )
			{
				data['archived'] = 1;
			}

			$.post($base_url+'audit_trail/audit_log/set_archived', data).promise().done(function(response)
			{
				response = JSON.parse(response);
				
				if (response.status == 'success') 
				{
					window.location.reload();
				}

			});
		})
	}

	var load_save	= function(data, form_s)
	{
		start_loading();

		$.post( $base_url + "audit_trail/audit_log/archive/", data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				// $('#modal_announcement').modal("close");
				load_datatable(response.datatable_options);
				
			}

			notification_msg(response.status, response.msg);
			end_loading();
		});
	}
		
	var initForm = function( datatable_options )
	{
		var options 		= JSON.parse(datatable_options);

		download_btn();
		archived_btn();
		change_archive();
		handleArchived();

		$("#filter_audit_log").change(function(){
			var system_code = $(this).val();

			if(system_code != 0)
			{

				options.path = $module + "/audit_log/get_audit_log/" + system_code;

				load_datatable(options);
			} 
			else 
			{
				options.path = $module + "/audit_log/get_audit_log/";

				load_datatable(options);
			}
		});
	}
	
	var loadTable = function()
	{
		$("#refresh_audit_log").on("click", function(){
			load_datatable("audit_log_table", $module + "/audit_log/get_audit_log/" + $("#filter_audit_log").val());
		});
	}

	var initModal = function()
	{
		$('#modal_auditlog').find('.modal-action.modal-close:contains("Cancel")').text('Close');
	}

	var handleArchived = function()
	{
		$('.handle-archived').click(function() {
			$('.change_archive').trigger('click');
		})
	}

	return {
		initForm : function( datatable_options )
		{
			initForm( datatable_options );

		},
		loadTable : function()
		{
			loadTable();
		},
		initModal : function()
		{
			initModal();
		},
		succ_callback : function()
		{
			
		}
	}
}();