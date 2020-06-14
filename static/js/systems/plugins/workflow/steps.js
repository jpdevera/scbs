var Steps 	= function()
{
	var $module 	= "workflow";

	var checkbox 	= function( selector )
	{
		selector.on('click', function()
		{
			if( $(this).is(':checked') )
			{
				$(this).prev().val('1');
			}
			else
			{
				$(this).prev().val('');	
			}
		})
	}

	var reorder_el 	= function( container, row_to_copy )
	{
		var stages_div 	= container.find('.steps-div'),
			i 			= 0,
			seq 		= 1,
			len  		= stages_div.length;
			
		var last_id 	= ( row_to_copy && row_to_copy.find('.step_task').val() != '' ) ? row_to_copy.find('.step_task').val() : 0;

		if( len !== 0 )
		{
			for( ; i < len; i++ )
			{
				var s_d = $(stages_div[i]);

				var obj_div = s_d.find('.header-text').contents().filter(function() {
			    	return this.nodeType == 3
				});

				obj_div[0].nodeValue = 'Step '+seq;

				s_d.attr('data-sequence', seq);

				s_d.find('input.step_sequence').val( seq );

				rename_sub( s_d.find('.sub_multiple'), seq, last_id );

				seq++;
			}
		}
	}

	var dragging 	= function( container, parent )
	{
		var containers 	= [container[0]];
		var row_to_copy = parent.find('.steps-div').last()
		
		var drago = {
			moves: function (el, container, handle) {

                return $(handle).hasClass('handle')
            },
            revertOnSpill: true
		};

		var drake = dragula(containers, drago);

		var scroll = autoScroll([
		        window,
		        document.querySelector('body')
		    ],{
		    margin: 20,
		    autoScroll: function(){
		        return this.down && drake.dragging;
		    }
		});

		drake.on('drop', function( el, target, source, sibling )
		{
			reorder_el( $(source), row_to_copy );

			if( $(el).find('.step_enc').val() != '' )
			{
				check_for_preq( this, $(el) );
			}
		});

		drake.on('cancel', function( el, source )
		{
			reorder_el( $( source ) );
		});
	}

	var check_for_preq 	= function( drake, el )
	{
		var data 		= {};

		data['workflow_main']	= $('#workflow_main').val();
		data['workflow_salt']	= $('#workflow_salt').val();
		data['workflow_token']	= $('#workflow_token').val();
		data['workflow_action']	= $('#workflow_action').val();
		data['workflow_step']			= el.find('.step_enc').val();
		data['workflow_step_salt']		= el.find('.step_salt').val();
		data['workflow_step_token']		= el.find('.step_token').val();
		data['sequence']				= el.find('.step_sequence').val();

		var response;

        var ajax            = $.ajax( {
            url     : $base_url+$module+'/Steps/check_for_prerequsite',
            data    : data,
            success : function( response )
            {
               return response;
            },
            dataType: 'json',
            method  : 'post',
            async   : false,
            error   : function() {}
        } );

        response    = ajax.responseJSON
        
        if( response !== undefined && response.check )
		{
			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					// $('[data-confirmmodal-but="cancel"]').show();
					// $('[data-confirmmodal-but="ok"]').text('Ok');
				},
				onCancelBut : function(extra)
				{
					load_table(true);
					// $('[data-confirmmodal-but="cancel"]').show();
					// $('[data-confirmmodal-but="ok"]').text('Ok');
				},
				onLoad : function() {
					// $('[data-confirmmodal-but="cancel"]').hide();
					// $('[data-confirmmodal-but="ok"]').text('close');
					$('.confirmModal_content h4').html('Changing the sequence of this Stage may affect some workflow prerequisites.');	
					// $('.confirmModal_content p').html('Please return the stage to its original sequence if you don\'t want to contiune.');	
				},
				onClose : function()
				{
					// $('[data-confirmmodal-but="cancel"]').show();
					// $('[data-confirmmodal-but="ok"]').text('Ok');
				}
			});
		}
	}

	var table 				= function()
	{
		var stage_div 		= $('.steps-header'),
			i 				= 0,
			len 			= stage_div.length;

		for( ;i < len; i++ )
		{
			var s_d 		= $( stage_div[i] );
			
			table_add( s_d );

			process_actions_table( s_d.find('.steps-div') );
		}
	}

	var process_actions_table = function( parent, sequence )
	{
		var steps_div 		= parent,
			len 			= steps_div.length,
			i 				= 0;
		
		if( len !== 0 )
		{
			for( ;i < len; i++ )
			{
				var s_d 		= $( steps_div[i] );
			
				load_actions_table( s_d, sequence );

			}
		}
	}

	var actions_add_row 	= function( parent )  
	{
		var btn 			= parent.find('.btn_action');
		var row_to_copy 	= parent.find('.tbl_actions');
		var selectedValue 	= {};

		var options 		= {
			btn_id 		: btn,
			tbl_id 		: row_to_copy,
			parent_elem : parent,
			before_copy_row : function(  row_index, self, tbl, tbl_copy )
			{
				var par 		= tbl.parents('div.steps-div');
				
				var rows 		= par.find('.tbl_actions').find( 'select.selectize' ),
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
		 	 					parent 		= i;

							// console.log();
							selectedValue[ parent+'_'+select_id ] = rows[i].selectize.getValue()
							
							rows[i].selectize.destroy();

							$(rows[i]).val('');
			 	 		}
			 	 	}

			 	 }
			},
			elem_to_mod 	: ['input', 'select', 'label', 'textarea'],
			each_elem_mod 	: function( obj, row_index, remove_func, tbl_id, args )
			{
				var ul_parsley 	= obj.parent().find('ul');

				var ul_multiple_parsley 	= obj.parent().next('ul');

				obj.val('');

				obj.attr('id', obj.attr('id')+'_row_'+row_index);
				// obj.removeClass('saved_data');

				if( ul_multiple_parsley !== undefined && ul_multiple_parsley.length !== 0 )
				{
					ul_multiple_parsley.remove();
				}

				if( ul_parsley !== undefined )
				{
					ul_parsley.remove();
				}

				if( obj.attr('type') == 'checkbox' )
				{
					obj.prop( 'checked', false );
				}

				if( obj.hasClass('parsley-error') )
				{
					obj.removeClass('parsley-error');
				}

				if( obj.hasClass('parsley-success' ) )
				{
					obj.removeClass('parsley-success');
				}

				if( obj.is('label') && obj.prev().is('input') )
				{
					obj.attr('for', obj.prev().attr('id'));
					obj.removeClass( 'active' );
				}

				if( obj.is('textarea') )
				{
					obj.text('');
				}

				if( obj.attr('disabled') !== undefined )
				{
					obj.prop('disabled', false);
				}
			},
			after_copy_row  : function( row_index, par_btn, remove_func, tbl_id, args )
			{
				var table  		= $( '#'+tbl_id+'_row_'+row_index );

				if( args.parent_elem !== undefined )
				{
					table 		= args.parent_elem.find( '#'+tbl_id+'_row_'+row_index );
				}

				act_process_stop();
				// dragging( args.parent_elem.find('.steps-div') );

				 	// last_div 	= table.find('div.row:last');

				 var par 	= table.parents('div.steps-div');
				 var rows 	= par.find('.tbl_actions').find('select.selectize'),
			 	 	 i 		= 0,
			 	 	 len,
			 	 	 j 		= 0,
			 	 	 len2;

			 	 if( rows.length !== 0 )
			 	 {
			 	 	len 	= rows.length;
			 	 	len2 	= rows.length;

			 	 	par.find('.tbl_actions').find('select.selectize').selectize({
			 	 		plugins : {
							'remove_button' : {
								className : 'remove_single'
							}
						}
			 	 	});
			 	 	
			 	 	for( ; i < len; i++ )
			 	 	{
			 	 		if( rows[i].selectize !== undefined )
			 	 		{

			 	 			var select_id 	= ( $(rows[i]).attr('id') === undefined ) ? i : $(rows[i]).attr('id'),
		 	 					parent_id 	= i;
							
							rows[i].selectize.setValue( selectedValue[ parent_id + '_' + select_id ] );
			 	 		}
			 	 	}
			 	 }
			}
		}

		add_rows( options );
	}

	var load_actions_table 	= function( parent, sequence )
	{
		var step_id 	= parent.find('.step_enc').val();
		var step_salt 	= parent.find('.step_salt').val();
		var step_token 	= parent.find('.step_token').val();
		var stage_id 	= parent.parents('.steps-header').find('.stage_step_enc').val();
		var stage_salt 	= parent.parents('.steps-header').find('.stage_step_salt').val();
		var stage_token = parent.parents('.steps-header').find('.stage_step_token').val();
		var data 		= {
			step_id  	: step_id,
			step_salt 	: step_salt,
			step_token  : step_token,
			stage_id 	: stage_id,
			stage_salt 	: stage_salt,
			stage_token : stage_token
		}

		data['workflow_main']	= $('#workflow_main').val();
		data['workflow_salt']	= $('#workflow_salt').val();
		data['workflow_token']	= $('#workflow_token').val();
		data['workflow_action']	= $('#workflow_action').val();
		
		$.post( $base_url+$module+'/Steps/load_actions_table', data ).promise().done( function( response )
		{
			parent.find('.tbl_actions > tbody').html( response );

			selectize_init();

			labelauty_init();

			if( sequence !== undefined )
			{
				rename_sub( parent.find('.tbl_actions > tbody').find('.sub_multiple'), sequence, 0 );
			}

			$('.process_stop').each(function(key, obj)
			{
				var tr = $(obj).closest('tr');
				proc_act_flag($(obj), tr);
			})

			act_process_stop();

			actions_add_row( parent );
		});
	}

	var act_process_stop 	= function()
	{
		$('.process_stop').on('click', function(e)
		{
			e.stopImmediatePropagation();

			var tr = $(this).closest('tr');

			proc_act_flag($(this), tr);
		})
	}

	var proc_act_flag = function(obj, tr)
	{
		if( obj.is(':checked') )
		{
			tr.find('.act_checkbox_hidden').val('1');
		}
		else
		{
			tr.find('.act_checkbox_hidden').val('0');	
		}
	}

	var table_add 			= function( parent )
	{
		dragging( parent.find('.steps-container'), parent );

		var btn 			= parent.find('.steps_add_btn');
		var row_to_copy 	= parent.find('.steps-div').last();
		var selectedValue 	= {};

		var row_ind 		= ( parent.find('.step_cnt').val() != '' && parent.find('.step_cnt').val() != 0 ) ? parent.find('.step_cnt').val() : 1;

		var options = {
			btn_id 		: btn,
			tbl_id 		: row_to_copy,
			append_type : 'after',
			parent_elem : parent,
			not_table 	: true,
			rem_rule 	: {
				rem_elem : 'a.delete_row',
				rem_find : 'div.collapsible-header'
			},	
			before_copy_row : function(  row_index, self, tbl, tbl_copy )
			{
				var par 		= tbl.parents('div.steps-header');

				var rows 		= par.find('.steps-div').find( 'select.selectize' ),
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
		 	 					parent 		= i;

							// console.log();
							selectedValue[ parent+'_'+select_id ] = rows[i].selectize.getValue()
							
							rows[i].selectize.destroy();

							$(rows[i]).val('');
			 	 		}
			 	 	}

			 	 }
			},
			elem_to_mod 	: ['input', 'select', 'label', 'textarea'],
			each_elem_mod 	: function( obj, row_index, remove_func, tbl_id, args )
			{
				var ul_parsley 	= obj.parent().find('ul');

				var ul_multiple_parsley 	= obj.parent().next('ul');

				obj.val('');

				obj.attr('id', obj.attr('id')+'_row_'+row_index);
				obj.removeClass('saved_data');

				if( ul_multiple_parsley !== undefined && ul_multiple_parsley.length !== 0 )
				{
					ul_multiple_parsley.remove();
				}

				if( ul_parsley !== undefined )
				{
					ul_parsley.remove();
				}

				if( obj.attr('type') == 'checkbox' )
				{
					obj.prop( 'checked', false );
				}

				if( obj.hasClass('parsley-error') )
				{
					obj.removeClass('parsley-error');
				}

				if( obj.hasClass('parsley-success' ) )
				{
					obj.removeClass('parsley-success');
				}

				if( obj.is('label') && obj.prev().is('input') )
				{
					obj.attr('for', obj.prev().attr('id'));
					obj.removeClass( 'active' );
				}

				if( obj.is('textarea') )
				{
					obj.text('');
				}

				if( obj.attr('disabled') !== undefined )
				{
					obj.prop('disabled', false);
				}
			},
			after_copy_row  : function( row_index, par_btn, remove_func, tbl_id, args )
			{
				var table  		= $( '#'+tbl_id+'_row_'+row_index );

				if( args.parent_elem !== undefined )
				{
					table 		= args.parent_elem.find( '#'+tbl_id+'_row_'+row_index );
				}

				table.find('.tbl_actions > tbody').html('');

				// dragging( args.parent_elem.find('.steps-div') );

				 	// last_div 	= table.find('div.row:last');

				checkbox($('.is_version_check'));
				checkbox($('.is_gettable_check'));

				collapsible_init();

				number_init();

			 	row_ind++;

			 	var last_id = ( row_to_copy.find('.step_task').val() != '' ) ? row_to_copy.find('.step_task').val() : 0;

			 	process_actions_table( table, row_ind );

			 	rename_sub( table.find('.sub_multiple'), row_ind, last_id );

			 	table.attr('data-sequence', row_ind);

			 	var obj_div = table.find('.header-text').contents().filter(function() {
			    	return this.nodeType == 3
				});

				obj_div[0].nodeValue = 'Step '+row_ind;

				table.find('input.step_sequence').val(row_ind);

				 var par 	= table.parents('div.steps-header');
				 var rows 	= par.find('.steps-div').find('select.selectize'),
			 	 	 i 		= 0,
			 	 	 len,
			 	 	 j 		= 0,
			 	 	 len2;

			 	 if( rows.length !== 0 )
			 	 {
			 	 	len 	= rows.length;
			 	 	len2 	= rows.length;

			 	 	par.find('.steps-div').find('select.selectize').selectize({
			 	 		plugins : {
							'remove_button' : {
								className : 'remove_single'
							}
						}
			 	 	});
			 	 	
			 	 	for( ; i < len; i++ )
			 	 	{
			 	 		if( rows[i].selectize !== undefined )
			 	 		{

			 	 			var select_id 	= ( $(rows[i]).attr('id') === undefined ) ? i : $(rows[i]).attr('id'),
		 	 					parent_id 	= i;
							
							rows[i].selectize.setValue( selectedValue[ parent_id + '_' + select_id ] );
			 	 		}
			 	 	}
			 	 }
			},
			after_remove_row 	: function(obj, row_index, args)
			{

				if( args.parent_elem !== undefined )
				{
					obj.closest(args.parent_elem.find('#steps-div_row_'+row_index)).remove();

					reorder_el( args.parent_elem, row_to_copy );
				}
				else
				{
					obj.closest($('#steps-div_row_'+row_index)).remove();
				}	

				row_ind--;
			}
		};

		add_rows( options );
	}

	var rename_sub 	= function( selector, sequence, last_id )
	{
		var sub 			= selector,
	 		sub_len 		= sub.length,
	 		sub_i 			= 0;

	 	if( sub_len !== 0 )
	 	{
	 		for( ;sub_i < sub_len; sub_i++ )
	 		{
	 			var obj 			= $( selector[ sub_i ] );

	 			if( !obj.hasClass('saved_data') && obj.attr('name') !== undefined && obj.attr('name') !== null )
	 			{
		 			var orig_name 		= obj.attr('name');
		 			var matches 		= orig_name.match(/\[(.+?)\]/g);

		 			if( matches.length !== 0 )
		 			{
		 				var new_name 	= orig_name.replace(/\[(.+?)\]/g, '');
		 				new_name 		= new_name.replace('[]', '');

		 				if( matches[1] !== undefined )
		 				{
							obj.attr('name', new_name+matches[0]+'['+sequence+'_sequence][]');
						}
						else
						{
							obj.attr('name', new_name+'['+sequence+'_sequence][]');
						}
					}
					/*orig_name 		= orig_name.replace('['+last_id+']', '');
					orig_name 		= orig_name.replace('[]', '');*/

					// obj.attr('name', orig_name+'['+sequence+'][]');
				}
	 		}
	 	}
	}

	var load_table 	= function( loading )
	{
		var data 	= {};

		data['workflow_main']	= $('#workflow_main').val();
		data['workflow_salt']	= $('#workflow_salt').val();
		data['workflow_token']	= $('#workflow_token').val();
		data['workflow_action']	= $('#workflow_action').val();

		if( loading )
		{
			start_loading();
		}

		$.post( $base_url+$module+'/Steps/load_table', data ).promise().done( function( response )  
		{
			$('.steps-container').html( response );

			checkbox($('.is_version_check'));
			checkbox($('.is_gettable_check'));

			labelauty_init();

			selectize_init();

			number_init();

			collapsible_init();

			table();

			if( loading )
			{
				end_loading();
			}
		});
	}

	var save 		= function( animate_next, next_fs, current_fs, self )
	{
		var main 	= $('#main_fieldset').serialize();
		var data 	= current_fs.serialize();

		data 		= data+'&'+main;

		if( $(self).attr('data-disable') == 'disabled' )
		{
			animate_next( next_fs, current_fs );
			return;
		}

		start_loading();

		$.post( $base_url+$module+'/Steps/save/', data ).promise().done( function( response )
		{	
			response 	= JSON.parse( response );

			if( response.flag )
			{
				load_table();

				Prerequisites.table();

				if( !$(self).hasClass('save-wizard') )
				{
					animate_next( next_fs, current_fs );
				}
			}
			
			end_loading();
			notification_msg(response.status, response.msg);
		} );
	}

	return {
		table : function()
		{
			load_table();
		},
		save : function( animate_next, next_fs, current_fs, self )
		{
			save( animate_next, next_fs, current_fs, self );
		},
		load_table : function()
		{
			load_table();
		},
		load_actions_table_del : function( self )
		{
			load_actions_table( $(self).parents('.steps-div') );
		}
	}

}();