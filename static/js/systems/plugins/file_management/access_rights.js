var Access_rights 	= function()
{
	var VISIBLE 	= {};
	var FILE_TYPES		= {};
	var DIRECTORIES 	= {};
	var DIR_MOD_MAP 	= {};
	var $module		= "file_management";
	var lookingfor;

	var init_search = function( obj )
	{
		var parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		return obj.lookingfor({
			input: parent_modal.find('#access_rights_search'),
			items: '.access_rights_wrapper',
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
		var parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		if( parent_modal.find('#access_rights_search').val() != '' )
		{
		 	parent_modal.find('#access_rights_search').val().val('');
		 	parent_modal.find('#access_rights_search').val().trigger('change');
		}
	}

	var init_modal 	= function(file_type, module)
	{
		var parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		var visible_constants 		=  parent_modal.find( '#visible_constants' );
		var js_file_constants 		=  parent_modal.find( '#'+file_type+'_js_file_constants_vis' );
		var js_file_dir_constants 	=  parent_modal.find( '#'+file_type+'_js_file_dir_constants_vis' );

		var js_directory_module_map	= parent_modal.find( '#'+file_type+'_directory_module_map_vis' );
		
		FILE_TYPES 					= ( js_file_constants.length !== 0 && js_file_constants.val() != '' ) ? JSON.parse( js_file_constants.val() ) : {};
		DIRECTORIES 				= ( js_file_dir_constants.length !== 0 && js_file_dir_constants.val() != '' ) ? JSON.parse( js_file_dir_constants.val() ) : {};
		DIR_MOD_MAP 				= ( js_directory_module_map.length !== 0 && js_directory_module_map.val() != '' ) ? JSON.parse( js_directory_module_map.val() ) : {};

		VISIBLE 		= ( visible_constants.val() != '' ) ? JSON.parse( visible_constants.val() ) : {};
	}

	visible_to 		= function( file_type )
	{
		var parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		var vis_sel 	= parent_modal.find('#access_rights_visibility');

		var vis_hide 	= parent_modal.find('#access_rights_visible_hide');

		if( vis_hide.val() != '' )
		{
			var vis_val 	= parseInt( vis_hide.val() );

			set_visible( parent_modal, vis_val, file_type );
		}

		vis_sel[0].selectize.on('change', function(value)
		{
			var option = this.options[value];
			
			set_visible( parent_modal, option.visibility, file_type );

		});
	}

	var set_visible = function( parent_modal, visibility, file_type )
	{
		switch( visibility )
		{
			case VISIBLE.VISIBLE_GROUPS :

				parent_modal.find( '#access_rights_visibility_group' )[0].selectize.enable();
				parent_modal.find( '#access_rights_visibility_group_div' ).removeAttr( 'style' );

				parent_modal.find( '#access_rights_visibility_actions' )[0].selectize.disable();
				parent_modal.find( '#access_rights_visibility_actions' )[0].selectize.clear();
				parent_modal.find( '#access_rights_visibility_actions' ).val('');

				
			 	parent_modal.find('#access_rights_visibility_group').attr('data-parsley-required', 'true');
	      		parent_modal.find('#access_rights_visibility_group').attr('data-parsley-trigger', 'change');

	      		parent_modal.find('#access_rights_visibility_actions').removeAttr('data-parsley-required');
	      		parent_modal.find('#access_rights_visibility_actions').removeAttr('data-parsley-trigger');

	      		parent_modal.find( '#access_rights_visibility_actions_div' ).attr( 'style', 'display : none !important;' );

			break;

			case VISIBLE.VISIBLE_ALL :

				parent_modal.find( '#access_rights_visibility_actions' )[0].selectize.enable();

				parent_modal.find( '#access_rights_visibility_actions_div' ).removeAttr( 'style' );

				parent_modal.find('#access_rights_visibility_actions').attr('data-parsley-required', 'true');
	      		parent_modal.find('#access_rights_visibility_actions').attr('data-parsley-trigger', 'change');

	      		parent_modal.find('#access_rights_visibility_group').removeAttr('data-parsley-required');
	      		parent_modal.find('#access_rights_visibility_group').removeAttr('data-parsley-trigger');

	      		parent_modal.find( '#access_rights_visibility_group_div' ).attr( 'style', 'display : none !important;' );

			break;

			default :

				parent_modal.find( '#access_rights_visibility_group' )[0].selectize.disable();
				parent_modal.find( '#access_rights_visibility_group' )[0].selectize.clear();
				parent_modal.find( '#access_rights_visibility_group' ).val('');

			 	parent_modal.find('#access_rights_visibility_group').removeAttr('data-parsley-required');
	      		parent_modal.find('#access_rights_visibility_group').removeAttr('data-parsley-trigger');

	      		parent_modal.find( '#access_rights_visibility_group_div' ).attr( 'style', 'display : none !important;' );
	      		parent_modal.find( '#access_rights_visibility_actions_div' ).attr( 'style', 'display : none !important;' );

	      		parent_modal.find('#access_rights_visibility_actions').removeAttr('data-parsley-required');
	      		parent_modal.find('#access_rights_visibility_actions').removeAttr('data-parsley-trigger');

	      		parent_modal.find( '#access_rights_visibility_actions' )[0].selectize.clear();
				parent_modal.find( '#access_rights_visibility_actions' ).val('');

			break;
		}

		if(  visibility == VISIBLE.VISIBLE_GROUPS || visibility == VISIBLE.VISIBLE_INDIVIDUALS )
		{
			parent_modal.find( '.add_access_div' ).removeAttr( 'style' );
			load_table( file_type );
		}
		else
		{
			parent_modal.find( '.add_access_div' ).attr( 'style', 'display : none !important;' );
			parent_modal.find('#access_rights_div_wrapper').html('');
		}
	}

	var load_table 	= function( file_type, extra_data )
	{
		var data 			= {},
			parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		if( extra_data !== undefined )
		{
			data 			= $.extend( data, extra_data );

			start_loading();
		}

		var file_id_vis 	= parent_modal.find('#'+file_type+'_file_vis'),
			file_salt_vis 	= parent_modal.find('#'+file_type+'_file_salt_vis'),
			file_token_vis 	= parent_modal.find('#'+file_type+'_file_token_vis'),
			file_action_vis = parent_modal.find('#'+file_type+'_file_action_vis'),
			file_type_vis 	= parent_modal.find('#'+file_type+'_file_type_vis'),
			file_type_salt_vis 	= parent_modal.find('#'+file_type+'_file_type_salt_vis'),
			file_type_token_vis = parent_modal.find('#'+file_type+'_file_type_token_vis'),
			module_vis 			= parent_modal.find('#'+file_type+'_module_vis'),
			module_salt_vis 	= parent_modal.find('#'+file_type+'_module_salt_vis'),
			module_token_vis 	= parent_modal.find('#'+file_type+'_module_token_vis');


		data['file_id']			= file_id_vis.val();
		data['file_salt']		= file_salt_vis.val();
		data['file_token']		= file_token_vis.val();
		data['file_action']		= file_action_vis.val();
		data['file_type']		= file_type_vis.val();
		data['file_type_salt']	= file_type_salt_vis.val();
		data['file_type_token']	= file_type_token_vis.val();
		data['module']			= module_vis.val();
		data['module_salt']		= module_salt_vis.val();
		data['module_token']	= module_token_vis.val();
		
		$.when( $.post( $base_url+$module+'/Access_rights/load_access_rights', data ) ).then( function( response ) 
		{
			if( $('#modal_access_rights').hasClass( 'open' ) )
			{
				$('#modal_access_rights').find( '#access_rights_div_wrapper' ).html( response );
				for_all();
				load_delete();
				my_add_row();

				lookingfor = init_search( $('#modal_access_rights').find( '#access_rights_div_wrapper' ) );

				event_search( $('#modal_access_rights').find( '#access_rights_div_wrapper' ), lookingfor, '#modal_access_rights div.access_rights_wrapper' );
			}

			if( extra_data !== undefined )
			{
				end_loading();		
			}

		} );
	}

	var access_rights_group 	= function( file_type )
	{
		var parent_modal,
			select, 
			extra_data 			= {};

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		select 					= parent_modal.find( 'select#access_rights_visibility_group' );

		extra_data['group_user'] 	= [];
		extra_data['group_code'] 	= [];

		if( select.length !== 0 )
		{
			select[0].selectize.on( 'item_add', function( value, data ) {

				var v 					= value.split('|'),
					group_id 			= v[0];

				extra_data['group_user'].push( group_id );

				parent_modal.find('select#default_privilege').val('');
				parent_modal.find('select#default_privilege')[0].selectize.clear();

				parent_modal.find('div#access_rights_collapsible_header').addClass('active');
				parent_modal.find('div#access_rights_collapsible_header').trigger("expand").collapsible("refresh");

				load_table( file_type, extra_data );

			} );

			select[0].selectize.on( 'item_remove', function( value, data ) {
				var v 					= value.split('|'),
					group_id 			= v[0];

				if( extra_data['group_user'].indexOf( group_id ) !== -1 )
				{
					extra_data['group_user'].splice( extra_data['group_user'].indexOf( group_id ), 1 );
				}

				if( extra_data['group_user'].length === 0 )
				{
					parent_modal.find('div#access_rights_collapsible_header').parents('li').removeClass('active');
					parent_modal.find('div#access_rights_collapsible_header').removeClass('active');
					parent_modal.find('.collapsible-body').stop(true,false).slideUp({ duration: 350, easing: "easeOutQuart", queue: false, complete: function() {$(this).css('height', '');}});
				}

				load_table( file_type, extra_data );

			} );
		}
	}

	var for_all 	= function()
	{
		var parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		parent_modal.find('#default_privilege').on('change', function( e ) {
			var select_id 	= $( this ).attr('id'),
				select_opt 	= {
					'default_privilege'	: {
						'select_class'	: 'access_rights_privilege',
						'select_data'	: 'data-action-id'
					}
				};


			if( Object.keys( select_opt[ select_id ] ).length !== 0 )
			{
				sel 			= select_opt[ select_id ];

				var select_priv = parent_modal.find('div.empty-access-rights').find('select.'+sel['select_class']+''),
					val 		= $( this ).val();

				select_priv.each( function() {

					if( this.selectize != undefined )
					{
						this.selectize.destroy();
					}

					$(this).val('');

					if( val )
					{

						var multi_i 	= 0,
							multi_len 	= val.length,
							val_str 	= '',
							val_arr 	= [];

						if( multi_len !== 0 )
						{
							for( ; multi_i < multi_len ; multi_i++ )
							{
								$(this).find('option['+sel['select_data']+'="'+val[multi_i]+'"]').attr('selected', 'selected');

								val_arr.push( $(this).find('option['+sel['select_data']+'="'+val[multi_i]+'"]').val() )
							}
						}

						$(this).val( val_arr ).change();
					}
					else
					{
						$(this).find('option[value=""]').attr('selected', 'selected');
						$(this).val('').change();
					}

					$(this ).selectize({
						plugins : {
							'remove_button' : {
								className : 'remove_single'
							}
						}	
					});

				} );

			}
		} );
	}

	var load_delete = function()
	{
		var parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal 		= $('#modal_access_rights');
		}

		parent_modal.find('.access_rights_wrapper').on('click', 'a.load_delete', function( e ) {
			
			if( $(this).attr('onclick') === undefined )
			{
				if( parent_modal.find('.access_rights_wrapper').length !== 1 )
				{
					$(this).parents('div.access_rights_wrapper').remove();
					my_add_row();
				}
			}
		});
	}

	var my_add_row 	= function()
	{
		var selectedValue 	= {};

		var row_to_copy,
			btn,
			par_modal;

		if( $('#modal_access_rights').hasClass('open') )
		{
			row_to_copy 	= $('#modal_access_rights').find('.access_rights_wrapper').last();
			btn 			= $('#modal_access_rights').find('#add_access_rights');
			par_modal 		= $('#modal_access_rights');
		}

		var orig_tr_len 	= par_modal.find('.access_rights_wrapper').length - 1;

		var options = {
			btn_id 		: btn,
			tbl_id 		: row_to_copy,
			append_type : 'after',
			// in_modal 	: true,
			parent_elem : par_modal,
			not_table 	: true,
			rem_rule 	: {
				rem_elem : 'a.delete_row',
				rem_find : 'div.row:last'
			},	
			scroll 		 : function( tbl_id, row_index, apnd_type, btn )
			{

				$( '#modal_access_rights').find('div.modal-content').find('.jspContainer').animate( { scrollTop :$('.access_rights_wrapper').last().offset().top - row_index  }, 'slow' );
			},
			before_copy_row : function(  row_index, self, tbl, tbl_copy )
			{
				if( par_modal.find('#access_rights_search').val() != '' )
				{
					par_modal.find('#access_rights_search').val('');
					par_modal.find('#access_rights_search').trigger('change');
				}

				var rows 		= $( '.access_rights_wrapper' ).find( 'select.selectize' ),
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
			elem_to_mod 	: ['input', 'select', 'label','div' ],
			each_elem_mod 	: function( obj )
			{	
				var ul_parsley 	= obj.parent().find('ul');

				var ul_multiple_parsley 	= obj.parent().next('ul');

				obj.val('');

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

				if( obj.attr('disabled') !== undefined )
				{
					obj.prop('disabled', false);
				}

				if( obj.hasClass('sub_multiple') )
				{
					var orig_name 	= obj.attr('name');
					var tr_len 		= par_modal.find('.access_rights_wrapper').length - 1;
					
					orig_name 		= orig_name.replace('['+orig_tr_len+']', '');
					orig_name 		= orig_name.replace('[]', '');

					obj.attr('name', orig_name+'['+tr_len+'][]');
				}
			},
			after_copy_row  : function( row_index, par_btn, remove_func, tbl_id, args )
			{
				var table  		= $( '#'+tbl_id+'_row_'+row_index );
				 	last_div 	= table.find('div.row:last');

				 if( table.hasClass('empty-access-rights') === false )
				 {
				 	table.addClass('empty-access-rights');
				 }

				if( table.find('div.delete_div_row').length === 0 )
				{

					last_div.after(
						'<div class="row m-n delete_div_row">'+ 				
							'<div class="col s12 right-align p-sm deep-purple lighten-5">'+
								'<a class="delete_row" style="cursor:pointer;">'+
									'Remove'+
								'</a>'+
							'</div>'+
						'</div>'
					);

					remove_func( tbl_id, row_index, args );
				}

				 var rows 	= $( '.access_rights_wrapper' ).find('select.selectize'),
			 	 	 i 		= 0,
			 	 	 len;

				if( rows.length !== 0 )
				{
					len 	= rows.length;

					$( '.access_rights_wrapper' ).find('select.selectize').selectize({
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

				lookingfor[0].addToCacheVal( table );

				table.find('input,select,textarea').on('change', function(e)
				{
					e.stopImmediatePropagation();

					lookingfor[0].removeCacheVal( table );

					lookingfor[0].addToCacheVal( table );
				} );
			},
			after_remove_row 	: function(obj, row_index)
			{
				var parent_modal;

				lookingfor[0].removeCacheVal( obj.closest('tr'));

				obj.closest('tr').remove();

				if( $('#modal_access_rights').hasClass( 'open' ) )
				{
					parent_modal 		= $('#modal_access_rights')
				}
			

				lookingfor[0].removeCacheVal( obj.closest($('#access_rights_wrapper_row_'+row_index)));

				obj.closest($('#access_rights_wrapper_row_'+row_index)).remove();

				if( parent_modal.find('div.access_rights_wrapper:visible').length == 0 )
				{
					parent_modal.find('#access_rights_search').val('');
					parent_modal.find('#access_rights_search').trigger('change');
				}

			}
		};

		add_rows( options );

		if( !par_modal.find('div#access_rights_collapsible_header').hasClass('active') )	
		{
			par_modal.find('div#access_rights_collapsible_header').addClass('active');
			par_modal.find('div#access_rights_collapsible_header').trigger("expand").collapsible("refresh");
		}
	}

	var save 		= function( file_type )
	{
		var parent_modal;

		if( $('#modal_access_rights').hasClass( 'open' ) )
		{
			parent_modal = $('#modal_access_rights');
		}

		var form 	= parent_modal.find( '#form_modal_access_rights' ),
			btn 	= parent_modal.find( '#submit_modal_access_rights' ),
			parsley = form.parsley();

		btn.on( 'click', function( e ) {

			var that 		= this;

			e.preventDefault();
			e.stopImmediatePropagation();

			parsley.validate();

			if( parsley.isValid() )
			{

				var par_form 	= $('#modal_upload_file').find('#form_modal_upload_file'),
					par_data 	= par_form.serialize();

				var data 		= par_data;

				data 		   += '&'+form.serialize();

				start_loading();

				$.post( $base_url + $module + "/Access_rights/save/", data ).promise().done( function( response ) 
 	 			{
 	 				response = JSON.parse( response );

 	 				if( response.flag )
					{
						if( response.file_id )
						{
							if( $('#'+response.file_type+'_file').length !== 0 )
							{
								$('#'+response.file_type+'_file').val( response.file_id );
								$('#'+response.file_type+'_file_salt').val( response.file_salt );
								$('#'+response.file_type+'_file_token').val( response.file_token );
								$('#'+response.file_type+'_file_action').val( response.file_action );
							}
						}

						if( response.orig_params )
						{
							var or_json 	= JSON.stringify( response.orig_params );

							$('#'+file_type+'_access_rights_btn_modal').attr('data-modal_post', or_json);
						}

						load_datatable( response.datatable_options );

						var dir_mod 		= DIR_MOD_MAP[ response.module ]

						switch( response.file_type )
						{
							case FILE_TYPES.FILE_TYPE_DOCUMENTS :

								document_uploadObj.update( 
 									{ 
 	 									dynamicFormData : function()
 	 									{
 	 										return {
	 	 										dir 		: dir_mod+DIRECTORIES.DIRECTORY_DOCUMENTS,
 	 										};
 	 									}
 	 								} 

 	 							);

								document_uploadObj.startUpload();
							break;

							case FILE_TYPES.FILE_TYPE_IMAGES :

								images_uploadObj.update( 
 									{ 
 	 									dynamicFormData : function()
 	 									{
 	 										return {
	 	 										dir 		: dir_mod+DIRECTORIES.DIRECTORY_IMAGES,
 	 										};
 	 									}
 	 								} 

 	 							);

								images_uploadObj.startUpload();
							break;

							case FILE_TYPES.FILE_TYPE_AUDIOS :

								audios_uploadObj.update( 
 									{ 
 	 									dynamicFormData : function()
 	 									{
 	 										return {
	 	 										dir 		: dir_mod+DIRECTORIES.DIRECTORY_AUDIOS,
 	 										};
 	 									}
 	 								} 

 	 							);

								audios_uploadObj.startUpload();
							break;

							case FILE_TYPES.FILE_TYPE_VIDEOS :

								videos_uploadObj.update( 
 									{ 
 	 									dynamicFormData : function()
 	 									{
 	 										return {
	 	 										dir 		: dir_mod+DIRECTORIES.DIRECTORY_VIDEOS,
 	 										};
 	 									}
 	 								} 

 	 							);

								videos_uploadObj.startUpload();
							break;
						}

						$("#modal_access_rights").modal("close");
					}

					notification_msg(response.status, response.msg);

					end_loading();

 	 			} );
			}		

		} );
	}

	return {
		visible_to 	: function( file_type )
		{
			visible_to( file_type );
		},
		init_modal 	: function( file_type, module )
		{
			init_modal( file_type, module );
		},
		access_rights_group : function( file_type )
		{
			access_rights_group( file_type );
		},
		save : function( file_type )
		{
			save( file_type );
		}
	}
}();