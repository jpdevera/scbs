var Organizations = function()
{
	var $module = "user_management";

	var check_arr 			= [];
	var check_file 			= [];
	
	var init_obj = function()
	{
		deleteObj = new handleData({ controller  : 'organizations', method : 'delete_organization', module: $module });
	}
	
	var init_modal = function()
	{
		if( $('#disabled_inp').val() != '' )
		{
			$('#submit_modal_organizations').hide();
		}
		else
		{
			$('#submit_modal_organizations').show();
		}

		selectize_init();
	}

	var save = function()
	{
		$('#form_modal_organizations').parsley();
		
		$('#form_modal_organizations').off("submit.organizations").on("submit.organizations", function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();
			
			if ( $(this).parsley().isValid() ) {
			  var data = $(this).serialize();
				 
			  button_loader('submit_modal_organizations', 1);
			  $.post($base_url + $module + "/organizations/process", data, function(result) {
				
				notification_msg(result.status, result.msg);
				button_loader('submit_modal_organizations', 0);
				  
				if(result.status == "success"){

					if( result.org_code )
					{
						$('#id_organizations').val( result.org_code );
						$('#salt').val( result.org_salt );
						$('#token').val( result.org_token );

						if( result.org_dec && result.sess_org_code && result.path )
						{
							if( result.org_dec == result.sess_org_code )
							{
								$('.org_logo_img').attr('src', result.path);
							}
						}

						org_logo_uploadObj.startUpload();
					}


				  $("#modal_organizations").modal("close");
				  load_datatable(result.datatable_options);
				}
			  }, 'json');
			}
		});
	}

	var successCallback = function(files,data,xhr,pd)
	{
		var form;

		form 		= $('#form_modal_organizations');

		var post_data 		= form.serialize();

		post_data 			+= '&upd_attach=1';

		$.post( $base_url+$module+'/Organizations/update_logo', post_data ).promise().done( function( response )
		{
			response 		= JSON.parse( response );

			if( response.flag )
			{
				reload_datatable('#'+response.table_id);

				if( response.org_code && response.sess_org_code && response.path )
				{
					if( response.org_code == response.sess_org_code )
					{
						$('.org_logo_img').attr('src', response.path);
					}
				}

			}
		} );
		
	}

	var import_func = function()
	{
		var form_s 	= 'form_modal_org_import';
		var btn_s 	= 'submit_modal_org_import';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley({ excluded: 'input[type=button], input[type=submit], input[type=reset]',
		    inputs: 'input, textarea, select, input[type=hidden]' });	

		$("#"+btn_s).on( "click", function( e )
		{
			$parsley.validate();

			e.preventDefault();

			e.stopImmediatePropagation();

			if( $parsley.isValid() )
			{
				org_import_uploadObj.startUpload();

				if( typeof( files_not_auto_submit ) !== 'undefined' )
				{
					check_file 		= files_not_auto_submit;
				}

				check_arr 			= [];
			}
		});
	}

	var successImportCallback = function(files,data,xhr,pd, module_code)
	{
		var data 			= {},
			form 			= $('#form_modal_org_import')
			form_data 		= form.serializeArray();

			form_data.push({ 'name' : 'module', 'value' : module_code });

			var url = $base_url+'user_management/organizations/import';

			if( module_code == 'USERS' )
			{
				var url = $base_url+'user_management/users/import';
			}

		start_loading();

		$.post(url, form_data ).done( function( response )
	 	{
	 		var check_move 	= false;


	 		if( xhr.status == 200 )
	 		{
	 			check_arr.push(xhr.responseText);
	 		}

	 		if( check_file.length == check_arr.length )
	 		{
	 			check_move 	= true;
	 		}

	 		/*if( check_move )
	 		{*/
	 			end_loading();
	 		// }

	 		response 		= JSON.parse( response );
	 		
	 		if( response.flag == 1 )
	 		{
	 		}
	 		else if( response.flag == 2 )
	 		{
	 			var err_d 	= {};

	 			err_d['err_arr'] = response.err_arr;
	 			err_d['header']  = response.header;
	 			err_d['module'] = module_code;
	 			err_d['upl_arr'] = response.upl_arr || [];
	 			
	 			var modal_error_import = $('#modal_error_import').modal({
					dismissible: false, // Modal can be dismissed by clicking outside of the modal
					opacity: .5, // Opacity of modal background
					in_duration: 300, // Transition in duration
					out_duration: 200, // Transition out duration
					ready: function() {
						
						$.post($base_url+'user_management/organizations/modal_error_import', err_d).promise().done(function(response)
						{
							$("#modal_error_import .modal-content #content").html(response);
						})

						// $( "body" ).removeAttr('style');
					},
					complete: function() { 
				  		
					} //
				});

	 			modal_error_import.trigger('openModal');
	 			$('#modal_org_import').modal('close');
	 		}

	 		load_datatable(response.datatable_options);
	 		$('#modal_org_import').modal('close');
	 		notification_msg(response.status, response.msg)
	 	});
	}

	var extra_opt_function 	= function(options)
	{

		options.render 		= {
			option: function(item, escape) {
				
				var label = item.text || '';
	            var caption = item.org_parent_names || '';
	            return '<div>' +
	                '<span class="label">' + escape(label) + '</span><br/>' +
	                (caption ? '<span class="help-text">' + escape(caption) + '</span>' : '') +
	            '</div>';
			},
			item: function(item, escape) {
				var label = item.text || '';
	            var caption = item.org_parent_names || '';
	            return '<div>' +
	                '<span class="label">' + escape(label) + '</span><br/>' +
	                (caption ? '<span class="help-text">' + escape(caption) + '</span>' : '') +
	            '</div>';
			}
		};

		options.scrollFunc = function(self)
		{
			self.extraData['org_sel'] = $('#id_organizations').val();
			/*console.log(obj);
			console.log(id;*/
		};

		return options;
		
	}


	return {
		init_obj : function()
		{
			init_obj();
		},
		init_modal : function()
		{
			init_modal();
		},
		save : function()
		{
			save();
		},
		successCallback 		: function(files,data,xhr,pd)
		{
			successCallback(files,data,xhr,pd);
		},
		import : function(module_code)
		{
			import_func(module_code);
		},
		successImportCallback : function(files,data,xhr,pd, module_code)
		{
			successImportCallback(files,data,xhr,pd, module_code);
		},
		extra_opt_function : function(options)
		{
			extra_opt_function(options);
		}

	}
}();