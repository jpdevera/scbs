var Users = function()
{
	var $module = "user_management";
	var $base_url = $("#base_url").val();
	var selected_single;
	var on_c_sel 			= {};

	var check_arr 			= [];
	var check_file 			= [];
	var res_arr 			= [];

	var initObj = function()
	{
		deleteObj = new handleData({ controller : 'users', method : 'delete_user', module: $module});
		
		$(".link-filter").click(function(){
			var id = $(this).prop('id');
			
			$("#" + id).addClass("active");
			$(".link-filter").not("#" + id).removeClass("active");

			switch(id)
			{
				case 'link_inactive_btn' :

					$('.btn_deac_appr').hide();

				break;
			}
			
			if(id == 'link_inactive_btn')
			{
				$("#users_table thead th:eq(4)").html("Last Logged In");
			}
			else
			{
				$("#users_table thead th:eq(4)").html("Roles");
			}

			if(id != 'link_active_btn')
			{
				$("#users_table thead th:eq(5)").html("Remarks");
			}
			else
			{
				$("#users_table thead th:eq(5)").html("Created By");
			}
		});
		
		$("#refresh_btn").click(function(){
			$(".link-filter").removeClass("active");
			$("#link_active_btn").addClass("active");
		});
	}

	var main_role_dropdown 		= function( main_role, other_role, role_json, value_sel ) 
	{
		var json_dec 	= JSON.parse( $('#role_json').val() );

		if( json_dec[ value_sel ] !== undefined )
		{
			delete json_dec[ value_sel ];
		}

		if( Object.keys(json_dec).length !== 0 )
		{
			other_role.removeOption( value_sel );

			for( var value in json_dec )
			{
				other_role.addOption( { value : value, text : json_dec[ value ] } );
			}
		}
	}

	var other_role_dropdown 	= function( main_role, other_role, role_json, value_sel )
	{
		var json_dec 	= JSON.parse( $('#role_json').val() );
		var val 		= value_sel,
			len 		= val.length,
			i 			= 0;

		if( len !== 0 )
		{
			for( ; i < len; i++ )
			{
				main_role.removeOption( val[ i ] );

				if( json_dec[ val[ i ] ] !== undefined )
				{
					delete json_dec[ val[ i ] ];
				}
			}
		}

		if( Object.keys(json_dec).length !== 0 )
		{
			// 

			for( var value in json_dec )
			{

				main_role.addOption( { value : value, text : json_dec[ value ] } );
			}
		}
	}

	var handle_role_dropdown 	= function()
	{
		var role_json 	= $('#role_json').val();
		var other_role 	= $('#role')[0].selectize;
		
		var main_role 	= $('#main_role')[0].selectize;

		if( main_role.getValue() != '' )
		{
			main_role_dropdown( main_role, other_role, role_json, main_role.getValue() );
		}

		if( other_role.getValue().length !== 0 )
		{
			other_role_dropdown( main_role, other_role, role_json, other_role.getValue() );
		}

		if( role_json != '' )
		{
			var role_json_dec = JSON.parse( role_json );

			main_role.on('change', function( e )
			{
				main_role_dropdown( main_role, other_role, role_json, this.getValue() );

				// 
			});

			other_role.on('change', function( e )
			{
				other_role_dropdown( main_role, other_role, role_json, this.getValue() );
				
			});
		}
	}


	var main_org_dropdown 		= function( main_role, other_role, role_json, value_sel ) 
	{
		var json_dec 	= JSON.parse( $('#org_json').val() );

		if( json_dec[ value_sel ] !== undefined )
		{
			delete json_dec[ value_sel ];
		}

		if( Object.keys(json_dec).length !== 0 )
		{
			other_role.removeOption( value_sel );

			for( var value in json_dec )
			{
				other_role.addOption( { value : value, text : json_dec[ value ] } );
			}
		}
	}

	var other_org_dropdown 	= function( main_role, other_role, role_json, value_sel )
	{
		var json_dec 	= JSON.parse( $('#org_json').val() );
		var val 		= value_sel,
			len 		= val.length,
			i 			= 0;

		if( len !== 0 )
		{
			for( ; i < len; i++ )
			{
				main_role.removeOption( val[ i ] );

				if( json_dec[ val[ i ] ] !== undefined )
				{
					delete json_dec[ val[ i ] ];
				}
			}
		}

		if( Object.keys(json_dec).length !== 0 )
		{
			// 

			for( var value in json_dec )
			{

				main_role.addOption( { value : value, text : json_dec[ value ] } );
			}
		}
	}

	var handle_orgs_dropdown 	= function()
	{
		if( $('#other_orgs').length !== 0 && $('#org').length !== 0 )
		{
			var role_json 	= $('#org_json').val();
			var other_role 	= $('#other_orgs')[0].selectize;
			
			var main_role 	= $('#org')[0].selectize;

			if( main_role.getValue() != '' )
			{
				main_org_dropdown( main_role, other_role, role_json, main_role.getValue() );
			}

			if( other_role.getValue().length !== 0 )
			{
				other_org_dropdown( main_role, other_role, role_json, other_role.getValue() );
			}

			if( role_json != '' )
			{
				var role_json_dec = JSON.parse( role_json );

				main_role.on('change', function( e )
				{
					main_org_dropdown( main_role, other_role, role_json, this.getValue() );

					// 
				});

				other_role.on('change', function( e )
				{
					other_org_dropdown( main_role, other_role, role_json, this.getValue() );
					
				});
			}
		}
	}
	
	var initForm = function()
	{
		$('#facebook_email_same').on('click', function(e)
		{
			e.preventDefault();

			var self 	= $(this);
			var check_c = self.is(':checked');

			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);
					}
					
					if( self.is(':checked') )
					{
						$('#facebook_email').attr('readonly', 'readonly');
						$('#facebook_email').val($('#email').val());
					}
					else
					{
						$('#facebook_email').removeAttr('readonly');
						$('#facebook_email').val('');	
					}
				},
				onCancelBut : function()
				{
					if( self.is(':checked') )
					{
						self.prop('checked', true);
					}
					else
					{
						self.prop('checked', false);
					}
				},
				onLoad : function() 
				{
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);	
					}

					if( check_c )
					{
						msg 	= 'Are you sure you want to use system email '+$('#email').val()+' ?';
					}
					else
					{
						msg 	= 'Are you sure you want to use a different email?';	
					}

					$('.confirmModal_content h4').html(msg);	
				},
				onClose : function() {}
			});

		});

		$('#google_email_same').on('click', function(e)
		{
			e.preventDefault();

			var self 	= $(this);
			var check_c = self.is(':checked');

			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);
					}
					
					if( $(this).is(':checked') )
					{
						$('#google_email').attr('readonly', 'readonly');
						$('#google_email').val($('#email').val());
					}
					else
					{
						$('#google_email').removeAttr('readonly');
						$('#google_email').val('');	
					}
				},
				onCancelBut : function()
				{
					if( self.is(':checked') )
					{
						self.prop('checked', true);
					}
					else
					{
						self.prop('checked', false);
					}
				},
				onLoad : function() 
				{
					if( self.is(':checked') )
					{
						self.prop('checked', false);
					}
					else
					{
						self.prop('checked', true);	
					}
					
					if( check_c )
					{
						msg 	= 'Are you sure you want to use system email '+$('#email').val()+' ?';
					}
					else
					{
						msg 	= 'Are you sure you want to use a different email?';	
					}

					$('.confirmModal_content h4').html(msg);	
				},
				onClose : function() {}
			});
		});

		if( $('#disabled_str').val() != '' )
		{
			$('#submit_modal_user_mgmt').hide();
		}
		else
		{
			$('#submit_modal_user_mgmt').show();
		}
		handle_role_dropdown();
		handle_orgs_dropdown();

		if($("#user_id").val() != '')
		{
			$('.input-field label').addClass('active');
			$('.labelauty').next().removeClass('active');
		}
		
		toggleContactFields($("input[name='contact_type']:checked").val());
		
		$(".contact_flag").on('change', function(){
			var value = $(this).val();
			toggleContactFields(value);
		});

		handle_date_exp_datepicker();

		handle_temp_tag($('.temp_account_flag'));

		$('.temp_account_flag').on('click', function( e )
		{
			handle_temp_tag($(this));
		})
	}

	var handle_temp_tag = function(obj)
	{
		var div 	= $('#temp_exp_date_div'),
			inp 	= $('#temp_expiration_date'),
			label 	= $('label[for="temp_expiration_date"]')

		div.attr('style', 'display: none !important');
		inp.attr('data-parsley-required', 'false');
		label.removeClass('required');

		if( obj.is(':checked') )
		{
			div.removeAttr('style');
			inp.attr('data-parsley-required', 'true');
			label.addClass('required');
		}
	}

	var handle_date_exp_datepicker = function()
	{
		var currentTime 	= new Date();
		var currYear 		= currentTime.getFullYear();
		// var yearOffset 		= year - currYear;

		// var defaultDate 	= '1/1/'+year;
		var currDate 		= moment().add('days', 1).format('MM/DD/YYYY');

		defaultDate 	= currDate;

		$('.datepicker_temp_exp').datetimepicker('destroy');	

		var dateTimeOpt 	= {
			timepicker:false,
			scrollInput: false,
			format:'m/d/Y',
			formatDate:'m/d/Y',
			defaultDate : defaultDate,
			onSelectDate : function(ct, $i)
			{
				if( typeof( $.fn.parsley ) !== 'undefined' )
				{
					$i.parsley().validate();
				}
			},
			onClose: function(ct, $i)
			{
				if( typeof( $.fn.parsley ) !== 'undefined' )
				{
					$i.parsley().validate();
				}
			},
			yearStart : currYear,
			// yearEnd   : year
		};

		dateTimeOpt['minDate']	= defaultDate;	

		$('.datepicker_temp_exp').datetimepicker(dateTimeOpt);
	}

	var convertArrayToOptions = function(data)
	{
		console.log(data);

		return JSON.parse(data);
	}
	
	var toggleContactFields = function(value)
	{
		if(value == 1)
		{
			$("#username").attr("data-parsley-required", "false");
			$("#password").attr("data-parsley-required", "false");
			$("#confirm_password").attr("data-parsley-required", "false");
			
			$("#main_role").attr("data-parsley-required", "false");
			$("#user_cancel_email").prop("checked","checked");
			
			$("label[for='username'], label[for='password'], label[for='confirm_password'], label[for='main_role']").removeClass("required");

			
		}else{
			$("#username").attr("data-parsley-required", "true");
			
			$("#main_role").attr("data-parsley-required", "true");
			$("#user_send_email").prop("checked","checked");
			
			if($("#form_modal_user_mgmt #user_id").val() == '')
			{
				$("#password").attr("data-parsley-required", "true");
				$("#confirm_password").attr("data-parsley-required", "true");
				$("label[for='password'], label[for='confirm_password']").addClass("required");
			}
			
			$("label[for='username'],  label[for='main_role']").addClass("required");
		}
	}
	
	var successCallback 	= function(arr, data)
	{
		arr = JSON.parse(arr);

		if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
		{
			var avatar = output_image(data[0], arr.path);
		}
		else
		{
			var avatar = $base_url + arr.path + data;
		}

		$("#" + arr.id + "_src").attr("src", avatar);
		
		$("#" + arr.id).val(data);
		$("#" + arr.id + "_upload").prev(".ajax-file-upload").hide();
		$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").text("Delete");
	}

	var deleteCallback 	= function(arr)
	{
		arr = JSON.parse(arr);
		
		$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-error").fadeOut();	
		$("#" + arr.id).val(''); 
		
		// for default image value sent from controller
		if(typeof(arr.default_image_preview) != "undefined" && arr.default_image_preview !== null) {
			var avatar = $base_url + arr.path_images + arr.default_image_preview;
			$("#" + arr.id + "_src").attr("src", avatar);
		}
			
		$("#" + arr.id + "_upload").prev(".ajax-file-upload").show();

		$.post( $base_url+'user_management/Users/check_if_created_user', { user_id : $( "#user_id" ).val() }, function( response ) {

			if( response.check == 1 ) 
			{
				var avatar = $base_url+ PATH_IMAGES + "avatar.jpg";
		  
				$("#top_bar_avatar").addClass("profile_avatar");
				$("#top_bar_avatar").attr("data-name", response.name);

				create_avatar($('.profile_avatar'), {width:80,height:80,fontSize:50});
			}

		}, 'json' );
	}

	var save = function()
	{
		var $parsley = $('#form_modal_user_mgmt').parsley({ excluded: 'input[type=button], input[type=submit], input[type=reset]',
		    inputs: 'input, textarea, select, input[type=hidden]' });
		
		$('#submit_modal_user_mgmt').on("click", function(e) {

			var dpa_enable 					= $('#dpa_enable_inp').val(),
				has_agreement_text 			= $('#has_agreement_text_inp').val(),
				confirm_dpa_message			= $('#confirm_dpa_message_inp').val(),
				confirm_dpa_message_body 	= $('#confirm_dpa_message_body_inp').val(),
				user_id						= $('#user_id_inp').val();

			$parsley.validate();
		
		  e.preventDefault();
		  e.stopImmediatePropagation();

		  if ( $parsley.isValid() ) {
			var data = $('#form_modal_user_mgmt').serialize();

			if( dpa_enable )
			{
				if( has_agreement_text == CONSTANTS.DATA_PRIVACY_TYPE_BASIC && user_id == "")
				{
					$('#confirm_modal').confirmModal({
						topOffset : 0,
						onOkBut : function() {
							post_submit_user(data);
						},
						onCancelBut : function() {},
						onLoad : function() {
							$('.confirmModal_content h4').html(confirm_dpa_message);	
							$('.confirmModal_content p').html(confirm_dpa_message_body);

							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('CONTINUE');
							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').addClass('font-sm');
						},
						onClose : function() 
						{
							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('Ok');
							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').removeClass('font-sm');
						}
					});
				}
				else
				{
					post_submit_user(data);
				}
			}
			else
			{
				post_submit_user(data);
			}

		  }
		});
	}

	var post_submit_user = function(data)
	{
		button_loader('submit_modal_user_mgmt', 1);
		start_loading();
		$.post($base_url + $module + "/users/process", data, function(result) {
			
			notification_msg(result.status, result.msg);
			button_loader('submit_modal_user_mgmt', 0);
			end_loading();
			
			if(result.status == "success")
			{
				if( $('#consent_form_upload').length !== 0 )
				{
					if( typeof( files_not_auto_submit ) !== 'undefined' )
					{
						check_file 		= files_not_auto_submit;
					}

					check_arr 			= [];

					$('#user_id_inp').val(result.new_id);
					$('#salt_inp').val(result.new_salt);
					$('#token_inp').val(result.new_token);

					if( typeof( consent_form_uploadObj ) !== 'undefined' )
					{
						consent_form_uploadObj.startUpload();

						$("#modal_user_mgmt").modal("close");
						load_datatable(result.datatable_options);
					}
				}
				else
				{
					$("#modal_user_mgmt").modal("close");
					load_datatable(result.datatable_options);
				}

				if( result.statistics )
				{
					$('#link_active_btn').find('span').text(result.statistics.active_count);
					$('#link_inactive_btn').find('span').text(result.statistics.inactive_count);
					$('#link_blocked_btn').find('span').text(result.statistics.blocked_count);
				}
			}
			
		}, 'json');       
	}

	var consentFormSuccessCallback 	= function(files,data,xhr,pd)
	{
		var data 			= {},
			form 			= $('#form_modal_user_mgmt')
			form_data 		= form.serializeArray();

		var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
			csrf_token 	= $('meta[name="csrf-token"]').attr('content');

		start_loading();

		form_data.push({
			name : csrf_name,
			value : csrf_token
		})

		$.post( $base_url+'user_management/Users/save_consent_file/', form_data ).done( function( response )
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
	 		// }
	 		
	 		response 		= JSON.parse( response );

	 		if( response.flag )
	 		{
	 			if(check_move)
				{
					$("#modal_user_mgmt").modal("close");
					load_datatable(response.datatable_options);
				}
	 		}

	 		end_loading();
	 	});
	}
	
	var initProdModal = function()
	{
		$("#conent_products").on("click", ".change-assign-trigger", function(){
		 
		 	var id = $(this).closest('.toggle-products').attr('id');
			var id_val = id.split('toggle_product_')[1]; 
			
			toggle("change-assign-trigger", id_val, id);
		});
		
		$("#conent_products").on("click", ".save_people_product", function(){
			var $data = {data_val : $(this).data('value'), product_id : $(this).closest('.toggle-products').find('select').val(), selected_user : $("#selected_user").val()};
			$.post($base_url + $module+'/users/process_people_product', $data, function(result){
				if(result.status != "success")
				{
					notification_msg(result.status, result.msg);
				}
				else if(result.status == "success")
				{
					
					$("#conent_products").html(result.content);
				}

			}, 'json');

		});
		$("#form_modal_assign_product").find('.modal-footer').find('button').css('cssText', "display : none !important");
		$("#form_modal_assign_product").find('.modal-footer').find('.modal-close').html('Close');

	}
	
	var toggle = function(trigger_class, id, elem) {
		var toggle_class = $("." + trigger_class).data('toggle'),
			elem = "#" + elem || "";
			console.log($(elem + " #field_wrapper_"+ id ));
		$("#conent_products " + elem + " ." + toggle_class).toggle("slow", function(){
			var field_wrapper_visible = $("#conent_products " + elem + " #field_wrapper_"+ id ).is( ":visible" ),
				field_wrapper_hidden  = $("#conent_products " + elem + " #field_wrapper_"+ id ).is( ":hidden" ),
				
				text_wrapper_visible = $("#conent_products " + elem + " #assign_text_wrapper_"+ id ).is( ":visible" ),
				text_wrapper_hidden  = $("#conent_products " + elem + " #assign_text_wrapper_"+ id ).is( ":hidden" );
				
			if(field_wrapper_visible && text_wrapper_hidden)
			{	
				$(elem + " #action_save_" + id).show();
			}else{
				$(elem + " #action_save_" + id).hide();
			}
		});
	}

	var search_func 	= function(search_params)
	{
		var status;

		var id 	= $(".link-filter.active").attr('id');

		if( id == 'link_active_btn' )
		{
			status 	= 'STATUS_ACTIVE';
		}
		else if( id == 'link_inactive_btn' )
		{
			status 	= 'STATUS_INACTIVE';
		}
		else if( id == 'link_blocked_btn' )
		{
			status  = 'STATUS_BLOCKED';
		}

		search_params['status']	= status;
		
		return search_params
	}

	var check_callback = function(self, default_setting,tb)
	{
		var tr 	= self.parents('tr'),
			par_;rap = tr.parents('div#'+default_setting.table_id+'_wrapper'),
			a 	= [],
			b 	= [];

		var checked 	= $('.sel_id:checked'),
			len 		= checked.length,
			i 			= 0;

		if( len !== 0 )
		{
			for( ; i < len; i++ )
			{
				var obj_c 	= $(checked[i]),
					tr_c 	= obj_c.parents('tr'),
					obj_cc 	= obj_c.closest('tr').find('.dt_details');
					// inc_t 	= tr_c.find('.inc_surr_temp');

				if( obj_cc.attr('data-delete_post')  )
				{
					a.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}
				
				/*if( inc_t.length == 0 && obj_c.attr('data-delete_post') )
				{
					a.push(JSON.parse(obj_c.attr('data-delete_post')));
				}*/

				/*if( obj_cc.attr('data-delete_post') && obj_cc.hasClass('approve_reject_class') )
				{
					a.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}

				if( obj_cc.attr('data-delete_post') && obj_cc.hasClass('resend_email_class') )
				{
					b.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}*/

				/*ext_r 	= a;
				res_r 	= b;*/

				res_arr 	= a;
			}
		}

		var enable 	= false;
		
		if( $('.sel_id').is(':checked') )
		{
			enable = true;
			/*$('.btn_deac_appr').removeClass('disabled');
			$('.btn_multi_block').removeClass('disabled');
			$('.btn_multi_activate').removeClass('disabled');
			$('.btn_multi_unblock').removeClass('disabled');

			$('.btn_deac_appr').removeAttr('disabled');
			$('.btn_multi_block').removeAttr('disabled');
			$('.btn_multi_activate').removeAttr('disabled');
			$('.btn_multi_unblock').removeAttr('disabled');*/
		}
		else
		{
			enable = false;
			/*$('.btn_deac_appr').addClass('disabled');
			$('.btn_multi_block').addClass('disabled');
			$('.btn_multi_activate').addClass('disabled');
			$('.btn_multi_unblock').addClass('disabled');

			$('.btn_deac_appr').attr('disabled', 'disabled');
			$('.btn_multi_block').attr('disabled', 'disabled');
			$('.btn_multi_activate').attr('disabled', 'disabled');
			$('.btn_multi_unblock').attr('disabled', 'disabled');*/
		}

		tb.buttons('.'+default_setting.table_id+'_deactivate').enable(enable);
		tb.buttons('.'+default_setting.table_id+'_block').enable(enable);
		tb.buttons('.'+default_setting.table_id+'_activate').enable(enable);
		tb.buttons('.'+default_setting.table_id+'_unblock').enable(enable);
	}

	var custom_button_func = function(rows, default_setting, table_obj, tb)
	{
		
		/*var par_wrap;


		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_deac_appr').remove();
		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_block').remove();
		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_activate').remove();
		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_unblock').remove();
		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.sel_all').prop('checked', false);

		if( $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons').length !== 0 )
		{
			par_wrap = $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons');
		}
		else
		{
			par_wrap = $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dataTable-filter').find('div:first');
		}

		// par_wrap.find('.btn_multi_del').hide();

		par_wrap.append('<span>&nbsp;</span>');

		par_wrap.append(`
			<button class="btn waves-effect btn_multi_users red lighten-2 btn_deac_appr" 
				disabled
			>
			<i class="material-icons">remove_circle_outline</i>Deactivate</button>
		`);
		par_wrap.append('<span>&nbsp;</span>');
		par_wrap.append(`
			<button class="btn waves-effect btn_multi_users red lighten-2 btn_multi_block" 
				disabled
			>
			<i class="material-icons">block</i>Block</button>
		`);

		par_wrap.append('<span>&nbsp;</span>');
		par_wrap.append(`
			<button class="btn hide waves-effect btn_multi_users green lighten-2 btn_multi_activate" 
				disabled
			>
			<i class="material-icons">done</i>Activate</button>
		`);

		par_wrap.append('<span>&nbsp;</span>');
		par_wrap.append(`
			<button class="btn hide waves-effect green btn_multi_users lighten-2 btn_multi_unblock" 
				disabled
			>
			<i class="material-icons">done</i>Unblock</button>
		`);*/
		// resend_email_bulk();
		
		var id = $(".link-filter.active").attr('id');

		btn_multi_users(tb, default_setting);

		tb.buttons('.'+default_setting.table_id+'_deactivate').enable(false);
		tb.buttons('.'+default_setting.table_id+'_block').enable(false);
		tb.buttons('.'+default_setting.table_id+'_activate').enable(false);
		tb.buttons('.'+default_setting.table_id+'_unblock').enable(false);	
		tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(false);


		if( $(rows).length == 0 )
		{
			$('.sel_all').prop('checked', false);
			$(tb.buttons('.'+default_setting.table_id+'_bulk_delete')[0].node).addClass('hide');
			$(tb.buttons('.'+default_setting.table_id+'_deactivate')[0].node).addClass('hide');
			$(tb.buttons('.'+default_setting.table_id+'_block')[0].node).addClass('hide');
			$(tb.buttons('.'+default_setting.table_id+'_activate')[0].node).addClass('hide');
			$(tb.buttons('.'+default_setting.table_id+'_unblock')[0].node).addClass('hide');
			
		}
	}

	var btn_multi_users 	= function(tb, default_setting)
	{
		// console.log(tb.buttons('.'+default_setting.table_id+'_activate'));
		tb.buttons('.'+default_setting.table_id+'_bulk_sub_actions').action(function(e, dt, obj, opt)
		{
			var jq 	= obj
			
			e.stopImmediatePropagation();
			e.preventDefault();

			var msg_confirm = '',
				pass_status;

			if( $(jq).hasClass(default_setting.table_id+'_deactivate') )
			{
				msg_confirm = 'Deactivate';

				pass_status = 'STATUS_INACTIVE';
			}
			else if( $(jq).hasClass(default_setting.table_id+'_block') )
			{
				msg_confirm = 'Block';

				pass_status = 'STATUS_BLOCKED';
			}
			else if( $(jq).hasClass(default_setting.table_id+'_unblock') )
			{
				msg_confirm = 'Unblock';

				pass_status = 'STATUS_ACTIVE';
			}
			else if( $(jq).hasClass(default_setting.table_id+'_activate') )
			{
				msg_confirm = 'Activate';

				pass_status = 'STATUS_ACTIVE';
			}

			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					var data = {};
					if( res_arr.length !== 0 )
					{
						data = {};

						var len 	= res_arr.length,
							i 		= 0;

						if( len !== 0 )
						{
							for( ; i < len; i++ )
							{
								var o 	= res_arr[i];

								for( var key in o )						
								{
									if( key != 'new_params' )
									{
									
										if( data[ key ] !== undefined )
										{
											data[key].push( o[key] );
										}
										else
										{
											data[key] 		= [];

											if( o[key] != '' )
											{
												data[key].push( o[key] );
											}
										}
									}
									else
									{
										data[key]	= o[key];
									}
								}
							}
						}
					}

					data['pass_status']	= pass_status;

					var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
						csrf_token 	= $('meta[name="csrf-token"]').attr('content');

					data[csrf_name]	= csrf_token;
					data['extra_data'] = [];

					$.blockUI({ 
			            message: '<img src="'+$base_url+'static/images/loading.gif">'
			        });

			        $.post($base_url+'user_management/Users/delete_multi_dt', data).promise().done(function(response)
			        {
			        	response = JSON.parse(response);
			        	$.unblockUI();
			        	notification_msg(response.status, response.msg);

			        	if( response.flag )
			        	{
			        		// load_datatable(response.datatable_options);
			        		refresh_ajax_datatable( response.datatable_id );
			        		eval(response.extra_function);
			        	}
			        })

			        
				},
				onCancelBut : function() {},
				onLoad : function() {

					$('.confirmModal_content h4').html('Are you sure you want to '+msg_confirm+' this user ?');	
					// $('.confirmModal_content p').html('This will send another copy of the message.');
				},
				onClose : function() {}
			});

		});

		return;
		/*$('.btn_multi_users').on('click', function(e)
		{
			

		})*/
	}

	var func_callback = function(tb, default_setting)
	{
		var id = $(".link-filter.active").attr('id');		
		
		switch(id)
		{
			case 'link_inactive_btn' :
				$(tb.buttons('.'+default_setting.table_id+'_activate')[0].node).removeClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_deactivate')[0].node).addClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_unblock')[0].node).addClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_block')[0].node).removeClass('hide');

			break;

			case 'link_blocked_btn' :

				$(tb.buttons('.'+default_setting.table_id+'_activate')[0].node).addClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_deactivate')[0].node).addClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_unblock')[0].node).removeClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_block')[0].node).addClass('hide');

			break;

			case 'link_active_btn' :
				$(tb.buttons('.'+default_setting.table_id+'_activate')[0].node).addClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_deactivate')[0].node).removeClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_unblock')[0].node).addClass('hide');
				$(tb.buttons('.'+default_setting.table_id+'_block')[0].node).removeClass('hide');
			break;
		}	
		
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
			
			var id = self.selectize.$input.attr('id');

			
			if( id == 'other_orgs' )
			{
				if( $('#org').length !== 0 )
				{
					var real_val 		= [$('#org').val()];
				}
				else
				{
					var real_val 		= [];
				}
			}
			else
			{
				if( $('#other_orgs').length !== 0 )
				{
					var real_val 		= $('#other_orgs').val();
				}
				else
				{
					var real_val 		= [];
				}
			}

			self.extraData['type']  = id;
			self.extraData['sel_val'] = real_val;
			/*console.log(obj);
			console.log(id;*/
		};

		return options;
		
	}

	return {
		initObj : function()
		{
			initObj();
		},
		initForm : function()
		{
			initForm();
		},
		initProdModal : function()
		{
			console.log('test');
			initProdModal();
		},
		save : function()
		{
			save();
		},
		successCallback : function(arr, data)
		{
			successCallback(arr, data);
		},
		deleteCallback : function(arr)
		{
			deleteCallback(arr);
		},
		search_func : function(search_params)
		{
			return search_func(search_params);
		},
		consentFormSuccessCallback : function(files,data,xhr,pd)
		{
			consentFormSuccessCallback(files,data,xhr,pd);
		},
		extra_function : function(statistics)
		{
			$('#link_active_btn').find('span').text(statistics.active_count);
			$('#link_inactive_btn').find('span').text(statistics.inactive_count);
			$('#link_blocked_btn').find('span').text(statistics.blocked_count);
		},
		check_callback :function(self, default_setting, tb)
		{
			check_callback(self, default_setting, tb);
		},
		custom_button_func : function(rows, default_setting, table_obj, tb)
		{
			custom_button_func(rows, default_setting, table_obj, tb);
		},
		func_callback : function(tb, default_setting)	
		{
			func_callback(tb, default_setting);
			// return custom_option_callback(options);
		},
		extra_opt_function : function(options)
		{
			extra_opt_function(options);
		}
	}
}();