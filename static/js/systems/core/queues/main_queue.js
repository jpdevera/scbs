var Main_queue 	= function()
{
	var res_r  	= [];
	var del_r 	= [];
	var $module = 'queues';

	var form_init 	= function()
	{
		$('ul.tabs').find('li.tab:first').find('a').trigger('click');
	}

	var check_callback = function(self, default_setting, tb)
	{
		var tr 	= self.parents('tr'),
			par_wrap = tr.parents('div#'+default_setting.table_id+'_wrapper'),
			/*appr_rj_clas = par_wrap.find('.approve_reject_class'),
			rese_e_class = par_wrap.find('.resend_email_class')*/
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

				if( obj_cc.attr('data-delete_post') )
				{
					a.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}
/*
				if( obj_cc.attr('data-delete_post') && obj_cc.hasClass('resend_email_class') )
				{
					b.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}

				if( obj_cc.attr('data-delete_post') && obj_cc.hasClass('delete_inc_class') )
				{
					c.push(JSON.parse(obj_cc.attr('data-delete_post')));
				}*/

				res_r 	= a;
				del_r 	= a;
			}
		}

		if( $('.sel_id').is(':checked') )
		{
			
			if( res_r.length != 0  )
			{
				tb.buttons('.'+default_setting.table_id+'_resend_email').enable(true);
			}
			else
			{
				tb.buttons('.'+default_setting.table_id+'_resend_email').enable(false);
				// tb.buttons('.'+default_setting.table_id+'_delete').enable(false);
			}

			if( del_r.length != 0  )
			{
				tb.buttons('.'+default_setting.table_id+'_delete').enable(true);
				/*$('.btn_multi_resend').removeClass('disabled');
				$('.btn_multi_resend').removeAttr('disabled');*/
			}
			else
			{
				tb.buttons('.'+default_setting.table_id+'_delete').enable(false);
				/*$('.btn_multi_resend').addClass('disabled');	
				$('.btn_multi_resend').attr('disabled', 'disabled');					*/
			}

		}
		else
		{
			tb.buttons('.'+default_setting.table_id+'_delete').enable(false);
			tb.buttons('.'+default_setting.table_id+'_resend_email').enable(false);
		}
	}

	var custom_button_func = function(rows, default_setting, table_obj, tb)
	{
		tb.buttons('.'+default_setting.table_id+'_delete').enable(false);
		tb.buttons('.'+default_setting.table_id+'_resend_email').enable(false);
		

		if( $(rows).length == 0 )
		{
			$('.sel_all').prop('checked', false);
			$(tb.buttons('.'+default_setting.table_id+'_delete')[0].node).addClass('hide');
			$(tb.buttons('.'+default_setting.table_id+'_resend_email')[0].node).addClass('hide');
			
		}

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
			        
					$.post($base_url + $module + "/Main_queue/delete/", data, function(result){
						
						/*$.unblockUI();
						notification_msg(result.status, result.msg);*/
						$.unblockUI();
			        	notification_msg(result.status, result.msg);

			        	if( result.flag )
			        	{
			        		load_datatable(result.datatable_options);
			        		// refresh_ajax_datatable( result.datatable_id );
			        		// eval(result.extra_function);
			        	}
					}, 'json');
				},
				onCancelBut : function() {},
				onLoad : function() {
					$('.confirmModal_content h4').html('Are you sure you want to delete this Record User?');	
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

			        data['resend']	= true;
			        
					$.post($base_url + $module + "/Main_queue/delete/", data, function(result){
						
						$.unblockUI();
			        	notification_msg(result.status, result.msg);

			        	if( result.flag )
			        	{
			        		load_datatable(result.datatable_options);
			        		// refresh_ajax_datatable( result.datatable_id );
			        		// eval(result.extra_function);
			        	}
					}, 'json');
				},
				onCancelBut : function() {},
				onLoad : function() {
					$('.confirmModal_content h4').html('Are you sure you want to resend this message?');	
					$('.confirmModal_content p').html('This action will resend this message to recipient.');
				},
				onClose : function() {}
			});
		})
	}

	return {
		form_init : function()
		{
			form_init();
		},
		check_callback : function(self, default_setting, tb)
		{
			check_callback(self, default_setting, tb)
		},
		custom_button_func : function(rows, default_setting, table_obj, tb)
		{
			custom_button_func(rows, default_setting, table_obj, tb);
		},
		custom_option_callback : function(options, default_setting)
		{
			var collection = options.buttons[0];

			var btns 		= [];


			btns.push({
				text: 'Resend', 
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