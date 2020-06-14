var Sign_up = function()
{
	var $module = "home";
	var ext_r  	= [];
	var res_r  	= [];
	var del_r 	= [];
	
	var initObj = function( datatable_options )
	{
		var options 		= JSON.parse( datatable_options );

		$(".link-filter").click(function(){
			var id = $(this).prop('id');
			
			$("#" + id).addClass("active");
			$(".link-filter").not("#" + id).removeClass("active");
			
			/*if(id == 'link_inactive_btn')
				$("#users_table thead th:eq(4)").html("Last Logged In");
			else
				$("#users_table thead th:eq(4)").html("Roles");*/
		});

		$("#filter_user_status").on("change", function(){
			var status 		= $(this).val();

			options.path 	= $module + "/Sign_up/get_user_list/" + status;
			
			load_datatable(options);
		});
	}

	var main_role_dropdown 		= function( main_role, other_role, role_json, value_sel ) 
	{
		var json_dec 	= JSON.parse( $('#sign_up_role_json').val() );

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
		var json_dec 	= JSON.parse( $('#sign_up_role_json').val() );
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

	var handle_role_dropdown 	= function( parent )
	{
		var role_json 	= $('#sign_up_role_json').val();
		var other_role 	= parent.find('select.other_role')[0].selectize;
		
		var main_role 	= parent.find('select.main_role')[0].selectize;

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
	
	var initTable = function()
	{
		$('.popmodal-dropdown').click(function()
		{
			var data 		= $(this).data();
			
			var parents 	= $(this).parents('.table-actions').find('.popModal');
			var appr_cont 	= parents.find('.approve_content');

			$("#" + data.idSelector).val(data.id);
			
			if(data.idSelector === 'approve_id')
			{
				handle_role_dropdown(appr_cont);
				$("#approve_user_roles")[0].selectize.clear();
				$("#main_role")[0].selectize.clear();
			}
			else
			{
				$("#reject_reason").val("");
			}
		});
	}
	
	var updateStatus = function(form_id, status_id)
	{
		var data = $("#" + form_id).serialize() + "&status_id=" + status_id;

		if( ext_r.length !== 0 )
		{
			data = {};

			var len 	= ext_r.length,
				i 		= 0;

			if( len !== 0 )
			{
				for( ; i < len; i++ )
				{
					var o 	= ext_r[i];

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

			var form_d = $("#" + form_id).serializeArray(),
				form_d_l = form_d.length,
				j 		= 0,
				post 	= {};

			if( form_d_l !== 0 )
			{
				var arr 	= [];
				var cur_key;

				for( ; j < form_d_l; j++ )
				{
					var regex = /[\[]/gi;
					var check = form_d[i].name.match(regex);

					if( check !== null )
					{
						if( form_d[j].value != '' )
						{
							arr.push( form_d[j].value );
						}

						/*if( post[ data[i].name ] !== undefined )
						{
							var old_arr 			= post[ data[i].name ];
							var new_arr 			= old_arr.concat(arr); 
							
							post[ data[i].name ] 	= new_arr;
							
						}
						else
						{
							post[ data[i].name ] 	= arr;
						}*/

						if( post[ form_d[j].name ] !== undefined )
						{
							if( form_d[j].value != '' )
							{
								post[ form_d[j].name ].push( form_d[j].value );
							}
							
						}
						else
						{
							post[ form_d[j].name ] 		= [];

							if( form_d[j].value != '' )
							{
								post[ form_d[j].name ].push( form_d[j].value );
							}
						}

						if( form_d[j+1] !== undefined )
						{
							cur_key 	= form_d[j+1].name;
						}
						
						if( cur_key != form_d[j].name )
						{
							arr 	= [];
						}
					}
					else
					{
						post[ form_d[j].name ]	= form_d[j].value;
					}
				}
			}

			delete post['id'];

			data 				= $.extend(data, post);
			data['status_id']	= status_id;
		}
		
		$('#confirm_modal').confirmModal({
			topOffset : 0,
			onOkBut : function() {
				$.blockUI({ 
		            message: '<img src="'+$base_url+'static/images/loading.gif">'
		        });
				$.post($base_url + $module + "/Sign_up/update_user_status/", data, function(result){
					notification_msg(result.status, result.msg);
					
					if(result.status == "success"){
						
						$("#ctr_pending").html('pending approval <span>'+result.pending+'</span>');
						/*$("#ctr_disapproved").html('rejected <span>'+result.disapproved+'</span>');
						$("#ctr_approved").html('approved <span>'+result.approved+'</span>');*/
						$('#ctr_incomplete').html('incomplete <span>'+result.incomplete+'</span>');
						
						var status_id = $("#filter_user_status").val();

						refresh_ajax_datatable( result.datatable_id );
						
						// load_datatable(result.datatable_options);
					}
					$.unblockUI();
				}, 'json');	
			},
			onCancelBut : function() {},
			onLoad : function() {
				var status_msg = ( status_id == '4' ) ? 'Approve' : 'Reject'
				$('.confirmModal_content h4').html('Are you sure you want to '+status_msg+' this user(s) ?');	
				// $('.confirmModal_content p').html('This action will permanently delete this record from the database and cannot be undone.');
			},
			onClose : function() {}
		});
	}
	
	var resendEmail = function(id, status, email)
	{
		$('#confirm_modal').confirmModal({
			topOffset : 0,
			onOkBut : function() {
				var data = "id=" + id+ "&status_id=" + status;
				$.blockUI({ 
		            message: '<img src="'+$base_url+'static/images/loading.gif">'
		        });
				$.post($base_url + $module + "/Sign_up/resend_approval_email/", data, function(result){
					
					$.unblockUI();
					notification_msg(result.status, result.msg);
				}, 'json');
			},
			onCancelBut : function() {},
			onLoad : function() {
				$('.confirmModal_content h4').html('Are you sure you want to resend the email?');	
				$('.confirmModal_content p').html('This will send another copy of the message to <strong>' + email + '</strong>');
			},
			onClose : function() {}
		});
	}

	var search_func 	= function(search_params)
	{
		var id 	= $(".link-filter.active").attr('id');
		
		if( id == 'ctr_pending' )
		{
			search_params['search_status_sign_up'] 	= 0;
		}
		else if( id == 'ctr_disapproved' )
		{
			search_params['search_status_sign_up'] 	= CONSTANTS.DISAPPROVED;
		}
		else if( id == 'ctr_approved' )
		{
			search_params['search_status_sign_up'] 	= CONSTANTS.APPROVED;
		}

		return search_params;
	}

	var check_callback = function(self, default_setting, tb)
	{
		var tr 	= self.parents('tr'),
			par_wrap = tr.parents('div#'+default_setting.table_id+'_wrapper'),
			appr_rj_clas = par_wrap.find('.approve_reject_class'),
			rese_e_class = par_wrap.find('.resend_email_class')
			// inc_surr_temp = tr.find('.inc_surr_temp'),
			a 	= [],
			b 	= [],
			c 	= [];

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
				
				/*if( inc_t.length == 0 && obj_c.attr('data-delete_post') )
				{
					a.push(JSON.parse(obj_c.attr('data-delete_post')));
				}*/

				if( obj_cc.attr('data-delete_post') && obj_cc.hasClass('approve_reject_class') )
				{
					a.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}

				if( obj_cc.attr('data-delete_post') && obj_cc.hasClass('resend_email_class') )
				{
					b.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}

				if( obj_cc.attr('data-delete_post') && obj_cc.hasClass('delete_inc_class') )
				{
					c.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}

				ext_r 	= a;
				res_r 	= b;
				del_r 	= c;
			}
		}

		if( $('.sel_id').is(':checked') )
		{
			
			if( ext_r.length != 0  )
			{
				tb.buttons('.'+default_setting.table_id+'_approve').enable(true);
				tb.buttons('.'+default_setting.table_id+'_reject').enable(true);
				

				/*$('.btn_multi_appr').removeClass('disabled');
				$('.btn_multi_appr').removeAttr('disabled');

				$('.btn_multi_reject').removeClass('disabled');
				$('.btn_multi_reject').removeAttr('disabled');*/
			}
			else
			{
				tb.buttons('.'+default_setting.table_id+'_approve').enable(false);
				tb.buttons('.'+default_setting.table_id+'_reject').enable(false);
				/*$('.btn_multi_appr').addClass('disabled');	
				$('.btn_multi_appr').attr('disabled', 'disabled');	

				$('.btn_multi_reject').addClass('disabled');	
				$('.btn_multi_reject').attr('disabled', 'disabled');			*/
			}

			if( res_r.length != 0  )
			{
				tb.buttons('.'+default_setting.table_id+'_resend_email').enable(true);
				/*$('.btn_multi_resend').removeClass('disabled');
				$('.btn_multi_resend').removeAttr('disabled');*/
			}
			else
			{
				tb.buttons('.'+default_setting.table_id+'_resend_email').enable(false);
				/*$('.btn_multi_resend').addClass('disabled');	
				$('.btn_multi_resend').attr('disabled', 'disabled');					*/
			}

			if( del_r.length != 0  )
			{
				tb.buttons('.'+default_setting.table_id+'_delete').enable(true);
			}
			else
			{
				tb.buttons('.'+default_setting.table_id+'_delete').enable(false);
			}
		}
		else
		{
			tb.buttons('.'+default_setting.table_id+'_approve').enable(false);
			tb.buttons('.'+default_setting.table_id+'_reject').enable(false);
			tb.buttons('.'+default_setting.table_id+'_resend_email').enable(false);
			tb.buttons('.'+default_setting.table_id+'_delete').enable(false);
			/*$('.btn_multi_appr').addClass('disabled');	
			$('.btn_multi_appr').attr('disabled', 'disabled');	

			$('.btn_multi_reject').addClass('disabled');	
			$('.btn_multi_reject').attr('disabled', 'disabled');	

			$('.btn_multi_resend').addClass('disabled');	
			$('.btn_multi_resend').attr('disabled', 'disabled');	*/
		}
	}

	var custom_button_func = function(rows, default_setting, table_obj, tb)
	{
		
		/*var par_wrap;

		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_appr').remove();
		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_reject').remove();
		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_resend').remove();
		$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.sel_all').prop('checked', false);

		if( $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons').length !== 0 )
		{
			par_wrap = $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons');
		}
		else
		{
			par_wrap = $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dataTable-filter').find('div:first');
		}

		par_wrap.find('.btn_multi_del').hide();*/

		/*par_wrap.append(`
			<button class="btn waves-effect green lighten-2 btn_multi_appr popmodal-dropdown" data-id-selector="approve_id" 
				data-ondocumentclick-close="false" ondocumentclick-close-prevent="e" data-placement="rightCenter" data-showclose-but="false"
				data-popmodal-bind="#approve_content"
				disabled
			>
			<i class="material-icons">done</i>Approve</button>
		`);
		par_wrap.append('<span>&nbsp;</span>');
		par_wrap.append(`
			<button class="btn waves-effect red lighten-2 btn_multi_reject popmodal-dropdown" 
				data-id-selector="reject_id" data-ondocumentclick-close="false" data-ondocumentclick-close="false"
				ondocumentclick-close-prevent="e" data-placement="rightCenter" data-popmodal-bind="#reject_content" data-showclose-but="false"
				disabled
			>
			<i class="material-icons">clear</i>Reject</button>
		`);

		par_wrap.append('<span>&nbsp;</span>');


		par_wrap.append(
			`<button class="btn waves-effect blue lighten-2 btn_multi_resend" 
				disabled
			>
			<i class="material-icons">markunread</i>Resend Email</button>`
		);*/

		// $.getScript($base_url+'static/js/popModal.min.js');

		/*$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).addClass('popmodal-dropdown');
		$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).attr('data-id-selector', 'approve_id');
		$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).attr('data-ondocumentclick-close', 'false');
		$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).attr('ondocumentclick-close-prevent', 'e');
		$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).attr('data-placement', 'rightCenter');
		$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).attr('data-showclose-but', 'false');
		$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).attr('data-popmodal-bind', '#approve_content');

		$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).addClass('popmodal-dropdown');
		$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).attr('data-id-selector', 'reject_id');
		$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).attr('data-ondocumentclick-close', 'false');
		$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).attr('ondocumentclick-close-prevent', 'e');
		$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).attr('data-placement', 'rightCenter');
		$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).attr('data-showclose-but', 'false');
		$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).attr('data-popmodal-bind', '#reject_content');*/



		tb.buttons('.'+default_setting.table_id+'_approve').enable(false);
		tb.buttons('.'+default_setting.table_id+'_reject').enable(false);
		tb.buttons('.'+default_setting.table_id+'_resend_email').enable(false);
		

		if( $(rows).length == 0 )
		{
			$('.sel_all').prop('checked', false);
			$(tb.buttons('.'+default_setting.table_id+'_approve')[0].node).addClass('hide');
			$(tb.buttons('.'+default_setting.table_id+'_reject')[0].node).addClass('hide');
			$(tb.buttons('.'+default_setting.table_id+'_resend_email')[0].node).addClass('hide');
			
		}

		tb.buttons('.'+default_setting.table_id+'_bulk_sub_actions').action(function(e, dt, obj, opt)
		{
			var jq 	= obj
			
			e.stopImmediatePropagation();
			e.preventDefault();

			if( $(jq).hasClass(default_setting.table_id+'_resend_email') || $(jq).hasClass(default_setting.table_id+'_delete') )
			{
				return;
			}

			var data 		= $(jq).data();

			$(obj).popModal({
				html : $(data.popmodalBind),
				placement : data.placement,
				showCloseBut : data.showcloseBut,
				onDocumentClickClose : false,
				onDocumentClickClosePrevent : 'e',				
				overflowContent : false,
				inline : false
			});

			var appr_cont 	= $('#approve_user_form');
			
			$("#" + data.idSelector).val(data.id);
			
			if(data.idSelector === 'approve_id')
			{
				handle_role_dropdown(appr_cont);
				$("#approve_user_roles")[0].selectize.clear();
				$("#main_role")[0].selectize.clear();
			}
			else
			{
				$("#reject_reason").val("");
			}
		});

		resend_email_bulk(tb, default_setting);
		delete_inc(tb, default_setting);
		
	}

	var delete_inc 		= function(tb, default_setting)
	{
		tb.buttons('.'+default_setting.table_id+'_delete').action(function(e, dt, obj, opt)
		{
			e.preventDefault();
			e.stopImmediatePropagation();

			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					var data = {};
					if( del_r.length !== 0 )
					{
						data = {};

						var len 	= del_r.length,
							i 		= 0;

						if( len !== 0 )
						{
							for( ; i < len; i++ )
							{
								var o 	= del_r[i];

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

					$.blockUI({ 
			            message: '<img src="'+$base_url+'static/images/loading.gif">'
			        });
			        
					$.post($base_url + $module + "/Sign_up/delete_incomplete_user/", data, function(result){
						
						/*$.unblockUI();
						notification_msg(result.status, result.msg);*/
						$.unblockUI();
			        	notification_msg(result.status, result.msg);

			        	if( result.flag )
			        	{
			        		// load_datatable(response.datatable_options);
			        		refresh_ajax_datatable( result.datatable_id );
			        		eval(result.extra_function);
			        	}
					}, 'json');
				},
				onCancelBut : function() {},
				onLoad : function() {
					$('.confirmModal_content h4').html('Are you sure you want to delete this Incomplete User?');	
					$('.confirmModal_content p').html('This action will permanently delete this record from the database and cannot be undone.');
				},
				onClose : function() {}
			});
		});
	}
	
	var resend_email_bulk = function(tb, default_setting)
	{
		/*$('.btn_multi_resend').on('click', function(e)
		{*/
		tb.buttons('.'+default_setting.table_id+'_resend_email').action(function(e, dt, obj, opt)
		{
			e.preventDefault();
			e.stopImmediatePropagation();

			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					var data = {};
					if( res_r.length !== 0 )
					{
						data = {};

						var len 	= res_r.length,
							i 		= 0;

						if( len !== 0 )
						{
							for( ; i < len; i++ )
							{
								var o 	= res_r[i];

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

					$.blockUI({ 
			            message: '<img src="'+$base_url+'static/images/loading.gif">'
			        });
			        
					$.post($base_url + $module + "/Sign_up/resend_approval_email/", data, function(result){
						
						$.unblockUI();
						notification_msg(result.status, result.msg);
					}, 'json');
				},
				onCancelBut : function() {},
				onLoad : function() {
					$('.confirmModal_content h4').html('Are you sure you want to resend the email?');	
					$('.confirmModal_content p').html('This will send another copy of the message.');
				},
				onClose : function() {}
			});
		})
	}

	var modal_init = function() {
		let action = '';
		const form = $('#form_modal_user_details');
	
		form.on('submit', function(e) {
			e.preventDefault();
	
			$('#confirm_modal').confirmModal({
				topOffset: 0,
				onOkBut: function() {
					const data = form.serialize();
					load_save(data);
				},
				onCancelBut: function() {},
				onLoad: function() {
					if (action === 4) {
						$('.confirmModal_content h4').html('You are about to approve this registration and provide access to this system.');	
						$('.confirmModal_content p').html('Are you sure you want to continue?');
	
						const set_opts = (d = true) => {
							let role_opts = '';
							const roles = JSON.parse($('#sign_up_role_json').val());
							for (const item of roles) {
								const selected = item.default_role_sign_up_flag === 'Y' ? 'selected' : '';
								role_opts += `<option ${d ? selected : ''} value="${item.role_code}">${item.role_name}</option>`;
							}
	
							return role_opts;
						}
	
						$('.confirmModal_content h4').after(`
							<div class="form-basic add-ons p-t-sm">
								<div class="row">
									<div class="col s12 p-l-n p-r-n">
										<div class="input-field">
											<label class="label required active">Assign main role to this account</label>
											<select class="selectize" id="confirm_main_role" placeholder="Select Roles">
												${set_opts()}
											</select>
										</div>
									</div>
								</div>
	
								<div class="row m-b-xs">
									<div class="col s12 p-l-n p-r-n">
										<div class="input-field">
											<label class="label required active">Assign other role/s to this account</label>
											<select class="selectize" id="confirm_role" placeholder="Select Roles" multiple>
												${set_opts(false)}
											</select>
										</div>
									</div>
								</div>
							</div>
						`);
						$('.confirmModal_content .selectize').selectize();
						$(document).find('#confirm_main_role').trigger('change');
	
						$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('YES, APPROVE REGISTRATION');
						$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').addClass('font-sm');
					} else if (action === 5) {
						$('.confirmModal_content h4').html('You are about to deny this registration.');	
						$('.confirmModal_content p').html('Are you sure you want to continue?');

						$('.confirmModal_content h4').after(`
							<div class="form-basic add-ons p-t-sm">
								<div class="row m-b-n">
									<div class="col s12 p-l-n p-r-n">
										<div class="input-field">
											<label class="label required active">Enter reason for rejection</label>
											<textarea class="materialize-textarea" id="confirm_reject"></textarea>
										</div>
									</div>
								</div>
							</div>
						`);

						$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').prop('disabled', true);
						$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('YES, DENY REGISTRATION');
						$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').addClass('font-sm');
					}
				},
				onClose: function() {
					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('Ok');
					$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').removeClass('font-sm bg green');
					$('.confirmModal_content .add-ons').remove();

					form.find('input[name="role[]"]').prop('disabled', false);
					form.find('input[name="main_role[]"]').prop('disabled', false);
				}
			});
		});
	
		form.find('button').click(function() {
			action = $(this).hasClass('approve') ? 4 : 5;
			form.find('input[name="status_id"]').val(action);

			if (action === 5) {
				form.find('input[name="role[]"]').prop('disabled', true);
				form.find('input[name="main_role[]"]').prop('disabled', true);
			}
		});

		$(document).on('change', '#confirm_main_role', function() {
			form.find('input[name="main_role[]"]').val($(this).val());
		});

		$(document).on('change', '#confirm_role', function() {
			form.find('input[name="role[]"]').val($(this).val());
		});

		$(document).on('change', '#confirm_reject', function() {
			form.find('input[name="reject_reason"]').val($(this).val());
		});

		$(document).on('keyup', '#confirm_reject', function() {
			if ($(this).val().length > 1) {
				$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').prop('disabled', false);
			} else {
				$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').prop('disabled', true);
			}
		});
	}

	var process_status_type 	= function(obj)
	{
		var divs_appr 	= $('#status_approve_div'),
			divs_dappr  = $('#status_reject_div'),
			main_role 	= $('#main_role'),
			reject_text = $('#reject_reason');

		divs_appr.attr('style', 'display : none !important');
		divs_dappr.attr('style', 'display : none !important');
		main_role.attr('data-parsley-required', 'false');
		reject_text.attr('data-parsley-required', 'false');

		if(obj.is(':checked'))
		{
			if( obj.attr('id') == 'status_approve' )
			{
				divs_appr.removeAttr('style');
				main_role.attr('data-parsley-required', 'true');
			}
			else if( obj.attr('id') == 'status_reject' )
			{
				divs_dappr.removeAttr('style');
				reject_text.attr('data-parsley-required', 'true');
			}
		}
	}

	var save_modal = function()
	{
		var form_s 	= 'form_modal_user_details';
		var btn_s 	= 'submit_modal_user_details';
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
				var data 		= form.serialize();

				var checked_status = $('.status_type:checked');

				$('#confirm_modal').confirmModal({
					topOffset : 0,
					onOkBut : function() {
						// start_loading();
						load_save(data);
					},
					onCancelBut : function() 
					{
					},
					onLoad : function()
					{
						if( checked_status.attr('id') == 'status_approve' )
						{
							$('.confirmModal_content h4').html('You are about to approve this registration and provide access to this system.');	
							$('.confirmModal_content p').html('You are about to approve this registration and provide access to this system.');

							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('YES, APPROVE REGISTRATION');
							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').addClass('font-sm');
						}
						else if( checked_status.attr('id') == 'status_reject' )
						{
							$('.confirmModal_content h4').html('You are about to deny this registration.');	
							$('.confirmModal_content p').html('Are you sure you want to continue?');

							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('YES, DENY REGISTRATION ');
							$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').addClass('font-sm');
						}
						
					},
					onClose : function() 
					{
						$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').text('Ok');
						$('.confirmModal').find('.confirmModal_footer').find('button[data-confirmmodal-but="ok"]').removeClass('font-sm bg green');
					}
				});

				
			}
		});
	}

	var load_save	= function(data, form_s)
	{
		start_loading();

		$.post( $base_url + "home/dashboard/update_user_status/", data ).promise().done( function( result )
		{
			result 	= JSON.parse( result );

			if(result.status == "success")
			{
						
				$("#ctr_pending").html('new <span>'+result.pending+'</span>');
				$("#ctr_disapproved").html('rejected <span>'+result.disapproved+'</span>');
				$("#ctr_approved").html('approved <span>'+result.approved+'</span>');
				$('#ctr_incomplete').html('incomplete <span>'+result.incomplete+'</span>');
				
				var status_id = $("#filter_user_status").val();

				refresh_ajax_datatable( result.datatable_id );

				$('#modal_user_details').modal("close");
				
				// load_datatable(result.datatable_options);
			}

			end_loading();
			notification_msg(result.status, result.msg);
		});
	}

	return {
		save_modal : function()
		{
			save_modal();
		},
		modal_init : function()
		{
			modal_init();
		},
		initObj : function( datatable_options )
		{
			initObj( datatable_options );
		},
		initTable : function()
		{
			initTable();
		},
		updateStatus : function(form_id, status)
		{
			updateStatus(form_id, status);
		},
		resendEmail : function(id, status, email)
		{
			resendEmail(id, status, email);
		},
		search_func : function(search_params)
		{
			return search_func(search_params);
		},
		check_callback : function(self, default_setting, tb)
		{
			check_callback(self, default_setting, tb);
		},
		custom_button_func : function(rows, default_setting, table_obj, tb)
		{
			custom_button_func(rows, default_setting, table_obj, tb);
		},
		resend_email_bulk : function()
		{
			resend_email_bulk();
		},
		extra_function : function(statistics)
		{
			$("#ctr_pending").html('pending approval <span>'+statistics.pending_count+'</span>');
			/*$("#ctr_disapproved").html('rejected <span>'+statistics.disapproved_count+'</span>');
			$("#ctr_approved").html('approved <span>'+statistics.approved_count+'</span>');*/
			$('#ctr_incomplete').html('incomplete <span>'+statistics.incomplete_count+'</span>');
			
		},
		custom_option_callback : function(options, default_setting)
		{
			var collection = options.buttons[0];

			var btns 		= [];

			btns.push({
				text: 'Approve', 
				init : function(cont, obj, opt)
				{
					$(obj).addClass('popmodal-dropdown');
					$(obj).attr('data-id-selector', 'approve_id');
					$(obj).attr('data-ondocumentclick-close', 'false');
					$(obj).attr('ondocumentclick-close-prevent', 'e');
					$(obj).attr('data-placement', 'rightCenter');
					$(obj).attr('data-showclose-but', 'false');
					$(obj).attr('data-popmodal-bind', '#approve_content');
				},
				name : 'bulk_approve',
				className : default_setting.table_id+'_approve'+' '+default_setting.table_id+'_bulk_sub_actions',
				action: function () {  }, 
				enabled: false, 
			});

			btns.push({
				text: 'Reject', 
				init : function(cont, obj, opt)
				{
					$(obj).addClass('popmodal-dropdown');
					$(obj).attr('data-id-selector', 'reject_id');
					$(obj).attr('data-ondocumentclick-close', 'false');
					$(obj).attr('ondocumentclick-close-prevent', 'e');
					$(obj).attr('data-placement', 'rightCenter');
					$(obj).attr('data-showclose-but', 'false');
					$(obj).attr('data-popmodal-bind', '#reject_content');
				},
				name : 'bulk_reject',
				className : default_setting.table_id+'_reject'+' '+default_setting.table_id+'_bulk_sub_actions',
				action: function () {  }, 
				enabled: false, 
			});

			btns.push({
				text: 'Resend Email', 
				name : 'bulk_resend_email',
				className : default_setting.table_id+'_resend_email'+' '+default_setting.table_id+'_bulk_sub_actions',
				action: function () {  }, 
				enabled: false, 
			});

			btns.push({
				text: 'Delete', 
				name : 'bulk_delete',
				className : default_setting.table_id+'_delete'+' '+default_setting.table_id+'_bulk_sub_actions',
				action: function () {  }, 
				enabled: false, 
			});

			collection.buttons = btns;

			options.buttons[0] 	= collection;

			return options;
			
		}

	}
}();