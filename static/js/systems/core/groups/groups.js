var Groups 	= function()
{
	var $module 	= 'groups';
	var lookingfor;

	var init_page 	= function()
	{
		$(".link-filter").click(function(){
			var id = $(this).prop('id');
			
			$("#" + id).addClass("active");
			$(".link-filter").not("#" + id).removeClass("active");
			
			/*if(id == 'link_inactive_btn')
				$("#users_table thead th:eq(4)").html("Last Logged In");
			else
				$("#users_table thead th:eq(4)").html("Roles");*/
		});
	}

	var init_search = function( obj )
	{
		return obj.lookingfor({
			input: $('#search_box_mod'),
			items: 'tbody tr',
			searchAttr : 'val'
		});
	}

	var event_search 		= function( obj, lookingObj, par_id )
	{
		obj.find('select,textarea,input').on( 'change', function( e )
		{
			e.stopImmediatePropagation();

			lookingObj[0].removeCacheVal( $(this).parents(par_id) );

			lookingObj[0].addToCacheVal( $(this).parents(par_id) );

		} );
	}

	var reset_search 		= function( )
	{
		if( $('#search_box_mod').val() != '' )
		{
		 	$('#search_box_mod').val().val('');
		 	$('#search_box_mod').val().trigger('change');
		}
	}

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


	var my_add_row 	= function()
	{
		var selectedValue 	= {};

		var table_id 	= "tbl_group_members";
		var btn_id 		= 'add_group_member';

		delete_row();

		var options = {
			btn_id 		: btn_id,
			tbl_id 		: table_id,
			scroll 		 : function( tbl_id, row_index, apnd_type, btn )
			{},
			before_copy_row : function(  row_index, self, tbl, tbl_copy )
			{
				if( $('#search_box_mod').val() != '' )
				{
					$('#search_box_mod').val('');
					$('#search_box_mod').trigger('change');
				}

				var rows 		= $( '#'+table_id ).find( 'select.selectize' ),
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
			},
			elem_to_mod 	: ['input', 'select', 'label', 'textarea'],
			each_elem_mod 	: function( obj, row_index, remove_func, tbl_id, args )
			{	
				var ul_parsley 	= obj.parent().find('ul');

				var ul_multiple_parsley 	= obj.parent().next('ul');

				obj.val('');

				obj.attr('id', obj.attr('id')+'_row_'+row_index);

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

				if( obj.attr('type') == 'checkbox' )
				{
					obj.prop( 'checked', false );
				}

				if( obj.is('label') && obj.prev().is('input') )
				{
					obj.attr('for', obj.prev().attr('id'));
					obj.removeClass( 'active' );
				}
			},
			after_copy_row  : function( row_index, par_btn, remove_func, tbl_id, args )
			{
				var table_row  		= $( '#'+tbl_id+'_row_'+row_index );

				$("#modal_groups").find('div.modal-content').jScrollPane({autoReinitialise: true});

				if( $( '#remove_'+table_id+'_row'+row_index ).length === 0 ) 
				{

					$( table_id+' tbody td:last' ).html(

						'<div class="table-actions center">'+
					
							'<a href="javascript:;" class="delete tooltipped remove_group" onclick=""  id="remove_'+table_id+'_row_'+row_index+'" data-tooltip="Delete" data-position="bottom" data-delay="50">'+

							'</a>'+
						
						'</div>'

					);

					remove_func( tbl_id, row_index, args );

				}

				checkbox($('.checkbox_members'));

				var rows 	= $( '#'+table_id ).find('select.selectize'),
					i 		= 0,
					len 	= rows.length,
					j 		= 0,
					len2;

				if( len !== 0 )
		 	 	{
	 	 		 	$( '#'+table_id ).find('select.selectize').selectize({
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

		 	 	lookingfor[0].addToCacheVal( table_row );

				$('#'+table_id).find('input,select,textarea').on('change', function(e)
				{
					e.stopImmediatePropagation();

					lookingfor[0].removeCacheVal( table_row );

					lookingfor[0].addToCacheVal( table_row );
				} );
			},
			after_remove_row 	: function(obj, row_index)
			{
				lookingfor[0].removeCacheVal( obj.closest('tr'));

				obj.closest('tr').remove();
				
				if( $('#'+table_id).find('tbody tr:visible').length == 0 )
				{
					$('#search_box_mod').val('');
					$('#search_box_mod').trigger('change');
				}
			}
		};

		add_rows( options );
	}

	var delete_row 	= function()
	{
		$('#tbl_group_members tbody').find('a.remove_group').on('click', function( e )
		{
			var tr 	= $(this).closest('tr');

			if( $('#tbl_group_members tbody tr').length > 1 )
			{
				tr.remove();
			}
			else
			{
				tr.find('input').val('');

				tr.find('input[type="checkbox"]').prop('checked', false);

				tr.find('select.selectize').each( function()
			 	{
			 		var selectize = $(this)[0].selectize;

			 		selectize.clear();

			 		$(this).val('');
			 	})
			}
		})
	}

	var init_modal = function()
	{
		load_table();

		save();

		if( $("#group_action").val() == "5" )
		{
			$("#submit_modal_groups").hide();
		}
		else
		{
			$("#submit_modal_groups").show();	
		}
	}

	var load_table = function()
	{
		var data 	= {};

		data['group_id'] 		= $('#group_id').val();
		data['group_salt'] 		= $('#group_salt').val();
		data['group_token'] 	= $('#group_token').val();
		data['group_action'] 	= $('#group_action').val();

		$.post( $base_url+$module+'/Groups/load_table', data ).promise().done( function( response )
		{
			$('#tbl_group_members tbody').html( response );

			lookingfor 	=  init_search( $('#tbl_group_members') );

			event_search( $('#tbl_group_members'), lookingfor, 'tr' );

			checkbox($('.checkbox_members'));

			selectize_init();
			labelauty_init();

			my_add_row();
		})
	}

	var adjust_list 	= function( active_cnt, inactive_cnt, datatable_options )
	{
		$("#link_active_btn").html('Active <span>'+active_cnt+'</span>');
		$("#link_inactive_btn").html('Inactive <span>'+inactive_cnt+'</span>');

		if( $(".link-filter.active").length !== 0 )
		{
			var active_link 	= $(".link-filter.active");

			switch( active_link.attr('id') )
			{
				case 'link_active_btn' :

					datatable_options['post_data']	= {
						status_link : 'Y'
					}

				break;

				case 'link_inactive_btn' :

					datatable_options['post_data']	= {
						status_link : 'N'
					}

				break;
			}
		}

		return datatable_options;
	}

	var delete_callback = function( active_cnt, inactive_cnt, datatable_options )
	{
		datatable_options = adjust_list( active_cnt, inactive_cnt, datatable_options );

		load_datatable( datatable_options );
	}

	var save 	= function()
	{
		var form 	= $('#form_modal_groups'),
			parsley = form.parsley();

		$('#submit_modal_groups').on('click', function( e )
		{
			e.preventDefault();
			e.stopImmediatePropagation();

			parsley.validate();

			if( parsley.isValid() )
			{
				var data  	= form.serialize();

				start_loading();

				$.post( $base_url+$module+'/Groups/save', data ).promise().done( function( response )   
				{
					response 				= JSON.parse( response );

					if( response.flag )
					{
						$("#modal_groups").modal("close");

						var datatable_options 	= adjust_list( response.group_active_cnt, response.group_inactive_cnt, response.datatable_options );

						load_datatable( datatable_options );
					}

					notification_msg(response.status, response.msg);

					end_loading();
				})
			}
		})
	}

	return {
		init_modal : function()
		{
			init_modal();
		},
		load_table : function()
		{
			load_table();
		},
		save 	: function()
		{
			save();
		},
		init_page : function()
		{
			init_page();
		},
		delete_callback : function( active_cnt, inactive_cnt, datatable_options )
		{
			delete_callback( active_cnt, inactive_cnt, datatable_options );
		}
	}
}();