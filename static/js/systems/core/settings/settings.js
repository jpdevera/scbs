var Settings = function()
{
	var $module = "settings";
	var terms_upl 	= [];
	var terms_file 	= {};
	
	var init = function()
	{
	}
	
	var initForm = function()
	{
		set_active_tab($module);
	}

	var process_term_cond 	= function(obj)
	{
		var val 	= obj.val();

		$('#term_cond_val_div').attr('style', 'display : none !important;');		
		$('#term_condition_value').attr('data-parsley-required', 'false');

		if(obj.is(':checked'))
		{
			$('#term_cond_val_div').removeAttr('style');		
			$('#term_condition_value').attr('data-parsley-required', 'true');
		}
		else
		{
			$('#term_cond_val_div').attr('style', 'display : none !important;');		
			$('#term_condition_value').attr('data-parsley-required', 'false');
		}
	}
	
	var initSiteSettings = function($sidebar_menu, $skin, $header, $menu_position, $menu_display, $menu_type)
	{
		$("#site_settings_form #site-info input").each(function(){
			if( $(this).val().length > 0 ) {
				var id = $(this).attr("id");
				$("label[for='"+ id +"']").addClass("active");
			}
		});
		
		if($sidebar_menu == 'cd-nav-compact'){ 
		  $("#layout_collapsed").prop("checked", true);
		} else {
		  $("#layout_expanded").prop("checked", true);
		}
		
		$("#skin_" + $skin).prop("checked", true);
		$("#header_" + $header).prop("checked", true);
		$("#menu_" + $menu_position).prop("checked", true);
		$("#display_" + $menu_display).prop("checked", true);
		$("#type_" + $menu_type).prop("checked", true);

		showHideElem($menu_position);

		$('input[name="menu_position"]').on('change', function(e) {
			var val = $(this).val();
			
			showHideElem(val);
		});

		process_term_cond($('#term_conditions'));

		$("#term_conditions").on('click', function(e)
		{
			process_term_cond($(this));
		});
	}
	
	var showHideElem = function(val)
	{
		if(val == 'TOP_NAV')
		{
			$("#top_nav_elem").show();
			$("#side_nav_elem").hide();
		}else {
			$("#top_nav_elem").hide();
			$("#side_nav_elem").show();
		}
	}

	var process_initial_log = function(obj, checked_event)
	{
		if( obj.attr('id') == 'set_system_generated' )
		{
			$('#change_initial_log_in').prop('checked', true);
			$('#change_initial_log_in').prop('disabled', true);
		}
		else if( obj.attr('id') == 'set_account_owner' )
		{
			$('#change_initial_log_in').prop('checked', false);
			$('#change_initial_log_in').prop('disabled', true);
		}
		else if( obj.attr('id') == 'set_administrator' )
		{
			if( checked_event )
			{
				$('#change_initial_log_in').prop('checked', true);
			}
			$('#change_initial_log_in').prop('disabled', false);
		}
	}

	var process_auth_account_factors = function(obj)
	{
		console.log(obj.attr('id'));
		if( obj.is(':checked') && 
			( obj.attr('id') != 'account_administrator')
		)
		{
			$('#who_can_set_us_div').attr('style', 'display : none !important;');
			$('#who_can_set_pass_div').attr('style', 'display : none !important;');
			$('#set_username_administrator').prop('checked', true);
			$('#set_administrator').prop('checked', true);
		}
		else
		{
			$('#who_can_set_us_div').removeAttr('style');
			$('#who_can_set_pass_div').removeAttr('style');
		}

		if( obj.is(':checked') && 
			( obj.attr('id') == 'account_visitor'|| obj.attr('id') == 'account_visitor_not_approval' )
		)
		{
			$('#additional_ver_acc_div').removeAttr('style');
		}
		else
		{
			$('#additional_ver_acc_div').attr('style', 'display : none !important;');
		}
	}

	var process_auth_factors = function(obj)
	{
		if( obj.is(':checked') )
		{
			$('#enable_multi_auth_factor_value').removeAttr('style');
			$('#authentication_factor').attr('data-parsley-required', 'true');
			$('#auth_code_decay').attr('data-parsley-required', 'true');

		}
		else
		{
			$('#authentication_factor').attr('data-parsley-required', 'false');
			$('#auth_code_decay').attr('data-parsley-required', 'false');
			// $('#auth_code_decay').val('1');
			$('#authentication_factor').val('');

			if( typeof($('#authentication_factor')[0].selectize) !== 'undefined' )
			{
				$('#authentication_factor')[0].selectize.clear();	
			}

			$('#enable_multi_auth_factor_value').attr('style', 'display : none !important;');
		}
	}

	var process_ip 	= function(obj)
	{
		if( obj.is(':checked') )
		{
			$('#enable_ip_blacklist_div').removeAttr('style');
			$('#ip_blacklist').removeAttr('data-parsley-required', 'true');
		}
		else
		{
			$('#enable_ip_blacklist_div').attr('style', 'display : none !important;');	
			$('#ip_blacklist').removeAttr('data-parsley-required', 'false');
		}
	}

	var initAccountSettings = function($account_creator, $login_via, $password_expiry, $password_initial_set, $username_initial_set)
	{
		$("#account_" + $account_creator).prop("checked", true);
		$("#login_via_" + $login_via).prop("checked", true);
		$("#set_" + $password_initial_set).prop("checked", true);
		
		$("#set_username_" + $username_initial_set).prop("checked", true);

		$('.password_creator_type').on('click', function(e)
		{
			process_initial_log($(this), true);
		});

		$('#enable_ip_blacklist').on('click', function(e)
		{
			process_ip($(this));
		})

		process_ip($('#enable_ip_blacklist'));

		process_initial_log($('.password_creator_type:checked'));

		if($password_expiry == 1){
			$("#password_expiry").prop("checked", true);
		}
		
		toggle('password_expiry', 'password_expiry_duration');
		toggle('log_in_deactivation', 'log_in_deactivation_duration');
		toggle('auto_log_inactivity', 'auto_log_inactivity_duration');
		toggle('apply_username_constraints', 'apply_username_constraints_div');
		toggle('single_session', 'self_logout_div');

		number_init('.number_zero', 0);

		process_auth_account_factors($('.account_reg:checked'));

		$('.account_reg').on('click', function(e)
		{
			process_auth_account_factors($(this));
		});

		/*process_auth_factors($('#enable_multi_auth_factor'));

		$('#enable_multi_auth_factor').on('click', function(e)
		{
			process_auth_factors($(this));
		})*/
	}

	var init_media_type 	= function()
	{
		$('.file_upload_type').on('click', function()
		{
			process_media_type($(this));
		});
	}

	var process_media_type 	= function(obj)
	{
		var val 	= obj.val();

		$('#change_dir_type_div').attr('style', 'display : none !important;');
		$('#new_upload_path').attr('data-parsley-required', 'false');
		// $('#new_upload_path').val('');
		
		if( val == CONSTANTS.MEDIA_UPLOAD_TYPE_DIR ) 
		{
			$('#change_dir_type_div').removeAttr('style');
			$('#new_upload_path').attr('data-parsley-required', 'true');
		}
		else if( val == CONSTANTS.MEDIA_UPLOAD_TYPE_DB )
		{
			$('#change_dir_type_div').attr('style', 'display : none !important;');
			$('#new_upload_path').attr('data-parsley-required', 'false');
			$('#new_upload_path').val('');
		}
		else
		{
			$('#change_dir_type_div').attr('style', 'display : none !important;');
			$('#new_upload_path').attr('data-parsley-required', 'true');
		}
	}

	var init_media_setting 	= function($image_quality)
	{
		toggle('change_upload_path', 'custom_upload_path_div', custom_path_toggle);

		toggle('enable_image_compression', 'quality_compression_div');
		init_media_type();
		process_media_type($('.file_upload_type:checked'));

		if( !$('#change_upload_path').is(':checked') )
		{
			$('#new_upload_path').attr('data-parsley-required', 'false');
		}

		var slider = document.getElementById('test-slider');
		noUiSlider.create(slider, {
			start: $image_quality,
			connect: 'lower',
			step: 1,
			orientation: 'horizontal', // 'horizontal' or 'vertical'
			range: {
				'min': 0,
				'max': 100
			},
			tooltips: [ wNumb({ decimals: 0 })],
			format: wNumb({
		 		decimals: 0
			})
		});

		var rangeSliderValueElement = $('#slider-range-value');

		slider.noUiSlider.on('update', function (values, handle) {
		    rangeSliderValueElement.val(values[handle]);
		});
	}
	
	var saveSiteSettings = function()
	{
		$("#save_site_settings").on("click", function(){

		  update_editor();
		  var data = $("#site_settings_form").serialize();
		  
		  button_loader('save_site_settings', 1);
		  
		  $.post($base_url + $module + "/site_settings/process", data, function(result){
			  notification_msg(result.status, result.msg);
			  button_loader('save_site_settings', 0);
			  
			  if(result.status == "success")
				location.reload();
		  }, 'json');
		});
	}
	
	var saveAccountSettings = function()
	{
		$('#account_settings_form').parsley();
	
		$('#account_settings_form').submit(function(e) {
			e.preventDefault();
			
			if ($(this).parsley().isValid()) {
			 $('#change_initial_log_in').prop('disabled', false);
			  var data = $(this).serialize();
		  
			  button_loader('save_account_settings', 1);
			  
			  $.post($base_url + $module + "/account_settings/process", data, function(result){
				  notification_msg(result.status, result.msg);
				  button_loader('save_account_settings', 0);
				  	process_initial_log($('.password_creator_type:checked'));
				  if(result.status == "success")
				  {
					load_index('tab_account_settings', 'account_settings', $module);
			      }
			  }, 'json');       
			}
		});
	}

	var save_media_settting  	= function()
	{
		var form 	= $('#media_settings_form'),
			$parsley = form.parsley();
	
		$('#save_media_settings').on('click',function(e) {

			e.preventDefault();

			$parsley.validate();
			
			if ($parsley.isValid()) {
		 		var data = form.serialize();

		  		button_loader('save_media_settings', 1);

	  		 	$.post($base_url + $module + "/Media_settings/process", data, function(result){

	 		  		notification_msg(result.status, result.msg);
			  		button_loader('save_media_settings', 0);

		  		 	if(result.status == "success")
						load_index('tab_media_settings', 'media_settings', $module)
	  		 	}, 'json');
			}

		});
	}

	var init_dpa_setting 	= function()
	{
		if($("#terms_conditions").val() != '' )
		{


			var terms_upl_det 	= $("#terms_conditions").val().split('|'),
				t_i 			= 0,
				t_l 			= terms_upl_det.length;

			for( ; t_i < t_l; t_i++ )
			{
				var t_d 		= terms_upl_det[ t_i ].split('=');

				terms_upl.push( t_d[0] );
				terms_file[ t_d[0] ] = t_d[1];
			}
		}

		load_editor("agremment_text");

		// toggle('has_agreement_text', 'has_agreement_text_value');
		toggle('dpa_enable', 'dap_enable_value');

		process_dpa_enable($('#dpa_enable'));

		$('#dpa_enable').on('click', function()
		{
			process_dpa_enable($(this));
		});

		init_dpa_type();

		process_dpa_type($('.label-icon-side:checked'))

		process_dpa_email($('#dpa_email_enable'));

		$('#dpa_email_enable').on('click', function(e)
		{
			process_dpa_email($(this));
		})
	}

	var process_dpa_enable = function(obj)
	{
		if(!obj.is(':checked'))
		{
			$('#agremment_text_sel').attr('data-parsley-required', 'false');
		}
	}

	var process_dpa_email = function(obj)
	{

		if( obj.is(':checked') )
		{
			$('#dap_email_enable_value').removeAttr('style');
			$('#email_domain').attr('data-parsley-required', 'true');

		}
		else
		{
			$('#email_domain').attr('data-parsley-required', 'false');
			$('#email_domain').val('');

			if( typeof($('#email_domain')[0].selectize) !== 'undefined' )
			{
				$('#email_domain')[0].selectize.clear();	
			}

			$('#dap_email_enable_value').attr('style', 'display : none !important;');
		}
	}

	var init_dpa_type 	= function()
	{
		$('.label-icon-side').on('click', function()
		{
			process_dpa_type($(this));
		})
	}

	var process_dpa_type 	= function(obj)
	{
		var val 	= obj.val();

		$('#has_agreement_text_value').attr('style', 'display : none !important;');
		$('#strict_type').attr('style', 'display : none !important;');
		$('#agremment_text_sel').attr('data-parsley-required', 'false');

		if( val == CONSTANTS.DATA_PRIVACY_TYPE_BASIC )
		{
			$('#agremment_text_sel').attr('data-parsley-required', 'true');
			$('#has_agreement_text_value').removeAttr('style');
			$('#strict_type').attr('style', 'display : none !important;');
		}
		else if( val == CONSTANTS.DATA_PRIVACY_TYPE_STRICT )
		{
			$('#strict_type').removeAttr('style');
			$('#has_agreement_text_value').attr('style', 'display : none !important;');
		}
		else
		{
			$('#has_agreement_text_value').attr('style', 'display : none !important;');
			$('#strict_type').attr('style', 'display : none !important;');
		}
	}
	
	var save_dpa_settings 	= function()
	{
		$('#dpa_settings_form').parsley();
	
		$('#dpa_settings_form').submit(function(e) {
			e.preventDefault();
			
			if ($(this).parsley().isValid()) {
				update_editor();
		 		var data = $(this).serialize();

		  		button_loader('save_dpa_settings', 1);

	  		 	$.post($base_url + $module + "/Dpa_settings/process", data, function(result){

	 		  		notification_msg(result.status, result.msg);
			  		button_loader('save_dpa_settings', 0);

		  		 	if(result.status == "success")
						load_index('tab_dpa_settings', 'dpa_settings', $module)
	  		 	}, 'json');
			}

		});	
	}
	var successCallback 	= function(arr, data)
	{
		arr 		= JSON.parse(arr);

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
		arr 		= JSON.parse(arr);

		$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-error").fadeOut();	
		$("#" + arr.id).val(''); 
			
		if(typeof(arr.default_image_preview) != "undefined" && arr.default_image_preview !== null) {
			var avatar = $base_url + arr.path_images + arr.default_image_preview;
			$("#" + arr.id + "_src").attr("src", avatar);
		}
			
		$("#" + arr.id + "_upload").prev(".ajax-file-upload").show();
	}

	var custom_path_toggle = function(id, content_id)
	{
		/*if($("#" + id).is(':checked'))
		{
			$('#new_upload_path').attr('data-parsley-required', 'true');
		}
		else
		{
			$('#new_upload_path').removeAttr('data-parsley-required');
			$('#new_upload_path').val('');
		}*/
		
	}

	var successCallbackTerm 	= function(arr, data, files)
	{
	/*	$("#" + arr.id).val(data);
		$("#" + arr.id + "_upload").prev(".ajax-file-upload").hide();
		$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").text("Delete");*/

		if( arr.multiple !== undefined && arr.multiple === true )
		{
			var i 		= 0,
				len 	= data.length,
				val_obj = [],
				str 	= "";

			terms_upl.push( data[0] );
			terms_file[data[0]] = files[0];
			
			if( terms_upl.length !== 0 )
			{
				var j 	= 0,
					t_l = terms_upl.length;

				for( ;j < t_l; j++ )
				{
					str += terms_upl[ j ]+"="+terms_file[ terms_upl[ j ] ]+"|";
				}

				str 	= str.substring(0, str.length - 1);

				$("#" + arr.id).val(str); 
			}
			
		}
		else
		{
			var avatar = $base_url + arr.path + data;

			$("#" + arr.id + "_src").attr("src", avatar);
		
			$("#" + arr.id).val(data);
			$("#" + arr.id + "_upload").prev(".ajax-file-upload").hide();
			$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").text("Delete");
		}

	}

	var deleteCallbackTerm 	= function(arr, data)
	{
		if( arr.multiple !== undefined && arr.multiple === true )
		{
			terms_upl.splice( terms_upl.indexOf( data[0] ), 1 );
			
			delete terms_file[ data[0] ];

			if( terms_upl.length !== 0 )
			{
				var j 	= 0,
					t_l = terms_upl.length,
					str = "";

				for( ;j < t_l; j++ )
				{
					str += terms_upl[ j ]+"="+terms_file[ terms_upl[ j ] ]+"|";
				}

				str 	= str.substring(0, str.length - 1);

				$("#" + arr.id).val(str); 
			}
			else
			{
				$("#" + arr.id).val(''); 
			}
		}
		else
		{

			$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-error").fadeOut();	
			$("#" + arr.id).val(''); 

			if(typeof(arr.default_image_preview) != "undefined" && arr.default_image_preview !== null) {
				var avatar = $base_url + arr.path_images + arr.default_image_preview;
				$("#" + arr.id + "_src").attr("src", avatar);
			}
				
			$("#" + arr.id + "_upload").prev(".ajax-file-upload").show();

		}
	}

	var init_sys_setting = function()
	{

	}

	var save_sys_settings = function()
	{
		$('#system_settings_form').parsley();
	
		$('#system_settings_form').submit(function(e) {
			e.preventDefault();
			
			if ($(this).parsley().isValid()) {
				update_editor();
		 		var data = $(this).serialize();

		  		button_loader('save_sys_settings', 1);

	  		 	$.post($base_url + $module + "/System_settings/process", data, function(result){

	 		  		notification_msg(result.status, result.msg);
			  		button_loader('save_sys_settings', 0);

		  		 	if(result.status == "success")
						load_index('tab_sys_settings', 'system_settings', $module)
	  		 	}, 'json');
			}

		});	
	}
	
	return {
		initForm : function()
		{
			initForm();
		},
		initSiteSettings : function($sidebar_menu, $skin, $header, $menu_position, $menu_display, $menu_type)
		{
			initSiteSettings($sidebar_menu, $skin, $header, $menu_position, $menu_display, $menu_type);
		},
		saveSiteSettings : function()
		{
			saveSiteSettings();
		},
		initAccountSettings : function($account_creator, $login_via, $password_expiry, $password_initial_set, $username_initial_set)
		{
			initAccountSettings($account_creator, $login_via, $password_expiry, $password_initial_set, $username_initial_set);
		},
		saveAccountSettings : function()
		{
			saveAccountSettings();
		},
		successCallback : function(arr, data)
		{
			successCallback(arr, data);
		},
		deleteCallback : function(arr)
		{
			deleteCallback(arr);
		},
		init_media_setting : function(image_quality)
		{
			init_media_setting(image_quality);
		},
		save_media_setting : function()
		{
			save_media_settting();
		},
		init_dpa_setting	: function()
		{
			init_dpa_setting();
		},
		save_dpa_settings 	: function()
		{
			save_dpa_settings();
		},
		custom_path_toggle : function(id, content_id)
		{
			custom_path_toggle(id, content_id);
		},
		init : function()
		{
			init();
		},
		successCallbackTerm( arr, data, files )
		{
			successCallbackTerm( arr, data, files );
		},
		deleteCallbackTerm( arr, data )
		{
			deleteCallbackTerm( arr, data );
		},
		init_sys_setting : function()
		{
			init_sys_setting();
		},
		save_sys_settings : function()
		{
			save_sys_settings();
		}
	}
}();