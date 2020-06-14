var Stages 	= function()
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

	var reorder_el 	= function( container )
	{
		var stages_div 	= container.find('.stages-div'),
			i 			= 0,
			seq 		= 1,
			len  		= stages_div.length;

		if( len !== 0 )
		{
			for( ; i < len; i++ )
			{
				var s_d = $(stages_div[i]);

				var obj_div = s_d.find('.header-text').contents().filter(function() {
			    	return this.nodeType == 3
				});

				obj_div[0].nodeValue = 'Stage '+seq;

				s_d.attr('data-sequence', seq);

				s_d.find('input.stage_sequence').val( seq );

				seq++;
			}
		}
	}

	var dragging 	= function()
	{
		var containers 	= $('.stages-container').toArray();
		
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
			reorder_el( $( source ) );

			if( $(el).find('.workflow_stage_inp').val() != '' )
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
		data['workflow_stage']			= el.find('.workflow_stage_inp').val();
		data['workflow_stage_salt']		= el.find('.workflow_stage_salt_inp').val();
		data['workflow_stage_token']	= el.find('.workflow_stage_token_inp').val();
		data['sequence']		= el.find('.stage_sequence').val();

		var response;

        var ajax            = $.ajax( {
            url     : $base_url+$module+'/Stages/check_for_prerequsite',
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
        
        if( response.check )
		{
			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					// $('[data-confirmmodal-but="cancel"]').show();
					// $('[data-confirmmodal-but="ok"]').text('Ok');
				},
				onCancelBut : function(extra)
				{
					load_table();
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

	var load_table 	= function()
	{
		var data 	= {};

		data['workflow_main']	= $('#workflow_main').val();
		data['workflow_salt']	= $('#workflow_salt').val();
		data['workflow_token']	= $('#workflow_token').val();
		data['workflow_action']	= $('#workflow_action').val();

		$.post( $base_url+$module+'/Stages/load_table', data ).promise().done( function( response )  
		{
			$('.stages-container-load').html( response );

			checkbox($('.skippable_check'));

			labelauty_init();

			collapsible_init();

			table();
		});
	}

	var table 		= function()
	{
		dragging();

		var btn 			= $('#stages_add_btn');
		var row_to_copy		= $('.stages-div').last();
		var selectedValue 	= {};

		var row_ind 		= ( $('#stage_cnt').val() != '' && $('#stage_cnt').val() != 0 ) ? $('#stage_cnt').val() : 1;

		var options = {
			btn_id 		: btn,
			tbl_id 		: row_to_copy,
			append_type : 'after',
			not_table 	: true,
			rem_rule 	: {
				rem_elem : 'a.delete_row',
				rem_find : 'div.collapsible-header'
			},	
			before_copy_row : function(  row_index, self, tbl, tbl_copy )
			{
				var rows 		= $( '.stages-div' ).find( 'select.selectize' ),
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
			each_elem_mod 	: function( obj, row_index )
			{
				var ul_parsley 	= obj.parent().find('ul');

				var ul_multiple_parsley 	= obj.parent().next('ul');

				obj.val('');

				obj.attr('id', obj.attr('id')+'_row_'+row_index);

				if( obj.attr('type') == 'checkbox' )
				{
					obj.prop( 'checked', false );
				}

				if( ul_multiple_parsley !== undefined && ul_multiple_parsley.length !== 0 )
				{
					ul_multiple_parsley.remove();
				}

				if( ul_parsley !== undefined )
				{
					ul_parsley.remove();
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
				 	// last_div 	= table.find('div.row:last');

				collapsible_init();

			 	row_ind++;
			 	
			 	table.attr('data-sequence', row_ind);
			 	
			 	var obj_div = table.find('.header-text').contents().filter(function() {
			    	return this.nodeType == 3
				});

				obj_div[0].nodeValue = 'Stage '+row_ind;

				table.find('input.stage_sequence').val(row_ind);

				checkbox($('.skippable_check'));
				 	
				 var rows 	= $( '.stages-div' ).find('select.selectize'),
			 	 	 i 		= 0,
			 	 	 len,
			 	 	 j 		= 0,
			 	 	 len2;

			 	 if( rows.length !== 0 )
			 	 {
			 	 	len 	= rows.length;
			 	 	len2 	= rows.length;

			 	 	$( '.stages-div' ).find('select.selectize').selectize({
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
			after_remove_row 	: function(obj, row_index)
			{
				obj.closest($('#stages-div_row_'+row_index)).remove();
				reorder_el( $( '.stages-container' ) );
				row_ind--;
			}
		};

		add_rows( options );
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

		$.post( $base_url+$module+'/Stages/save/', data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				load_table();

				Steps.load_table();

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
		}
	}
}();