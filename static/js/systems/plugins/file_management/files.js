var Files = function()
{
	var $module		 	= "file_management";
	var FILE_TYPES		= {};
	var DIRECTORIES 	= {};
	var DIR_MOD_MAP 	= {};
	var save_attr 		= {};
	var colspan 		= 4;
	var player;
	var players 		= [];
	
	/*var initObj = function()
	{
		deleteObj = new handleData({ controller : 'files', method : 'delete_file', module: $module });
	}
	
	var initForm = function()
	{
		search_wrapper('#file_list .list-grid', 'input.search-box', 'li.list-item', 1);
	}
	
	var initModal = function()
	{
		if($("#id_files").val() != '')
			$('.input-field label, .materialize-textarea').addClass('active');
	}

	var upload = function($form_id)
	{
		var $button_id = ($form_id == "form_modal_files")? "save_upload_file" : "save_upload_file_version";
			$method = ($form_id == "form_modal_files")? "process" : "insert_file_version";
		
		$('#' + $form_id).parsley();
		
		$('#' + $form_id).off("submit.files").on("submit.files", function(e) {
			e.preventDefault();
			
			if ( $(this).parsley().isValid() ) {
			  var data = $(this).serialize();
				  
			  button_loader($button_id, 1);
			  $.post($base_url + $module + "/files/" + $method, data, function(result) {
				
				notification_msg(result.status, result.msg);
				button_loader($button_id, 0);				
				
				if(result.status == "success"){
				  //$("#modal_roles").closeModal();
				  window.location.reload();
				}
				
			  }, 'json');       
			}
		});
	}*/

	var datatable_custom_option = function( options )
	{		
		options['drawCallback'] 	= function( settings )
		{
			var api 	= this.api();
			var rows 	= api.rows( { page:'current'} ).nodes();
			var last	= null;
			
			api.column(0, {page:'current'}).data().each( function( group, i ) 
			{
				$(rows[i]).html(`
					<td colspan="`+colspan+`">`+group+`</td>
				`);
			});

			if( $('.selectize').length !== 0 )
			{
				if( typeof( $.fn.selectize ) === 'undefined' )
				{
					$.getScript( $base_url+'static/js/selectize.js' );
				}

				selectize_init();
			}

			if( $( '.tooltipped' ).length !== 0 )
			{
				$('.tooltipped').tooltip({delay: 50});
			}

			if( typeof($.fn.dropdown) !== "undefined" && $('.dropdown-button').length !== 0 )
			{
				$('.dropdown-button').dropdown({
					inDuration: 300,
					outDuration: 225,
					constrain_width: false, // Does not change width of dropdown to that of the activator
					hover: false, // Activate on hover
					gutter: 0, // Spacing from edge
					belowOrigin: true, // Displays dropdown below the button
					alignment: 'left' // Displays dropdown with edge aligned to the left of button
				});
			}
		}

		return options;
	}


	var create_button_modal 	= function( file_type, obj )
	{
		var elem = $('#hid_'+file_type+'_val');

		if( elem.length !== 0 && elem.val() != '' )
		{
			$('#add_files').attr('data-modal_post', elem.val() )
			$('#add_files_multi_hide').attr('data-modal_post', elem.val() )
		}
	}

	var init_modal 				= function( file_type, module, multi_check )
	{
		var js_file_constants 		=  $( '#'+file_type+'_js_file_constants' );
		var js_file_dir_constants 	=  $( '#'+file_type+'_js_file_dir_constants' );

		var js_directory_module_map	= $( '#'+file_type+'_directory_module_map' );
		
		if( multi_check )
		{
			$('.access_right_btn').hide();
		}
		else
		{
			$('.access_right_btn').show();	
		}

		FILE_TYPES 					= ( js_file_constants.length !== 0 && js_file_constants.val() != '' ) ? JSON.parse( js_file_constants.val() ) : {};
		DIRECTORIES 				= ( js_file_dir_constants.length !== 0 && js_file_dir_constants.val() != '' ) ? JSON.parse( js_file_dir_constants.val() ) : {};
		DIR_MOD_MAP 				= ( js_directory_module_map.length !== 0 && js_directory_module_map.val() != '' ) ? JSON.parse( js_directory_module_map.val() ) : {};

		if( Object.keys( FILE_TYPES ).length !== 0 
			&& Object.keys( DIRECTORIES ).length !== 0
			&& Object.keys( DIR_MOD_MAP ).length !== 0
		)
		{
			var dir_mod 		= DIR_MOD_MAP[ module ];

			save_attr[ FILE_TYPES.FILE_TYPE_DOCUMENTS ] 	= {
				url 		: $base_url + $module + '/Files/save',
				dir 		: dir_mod+DIRECTORIES.DIRECTORY_DOCUMENTS,
				upload_obj 	: undefined,
				upload_options : undefined,
				field_ids 	: [ '#modal_upload_file.open #file_display_name', '#modal_upload_file.open #document' ],
				field_label : [ 'Display Name', 'File' ]
			}

			if( typeof( document_uploadObj ) !== 'undefined' )
			{
				save_attr[ FILE_TYPES.FILE_TYPE_DOCUMENTS ]['upload_options'] 	= document_options_upload_obj;
				save_attr[ FILE_TYPES.FILE_TYPE_DOCUMENTS ]['upload_obj'] 		= document_uploadObj;
			}

			save_attr[ FILE_TYPES.FILE_TYPE_VIDEOS ] 	= {
				url 		: $base_url + $module + '/Files/save',
				dir 		: dir_mod+DIRECTORIES.DIRECTORY_VIDEOS,
				upload_obj 	: undefined,
				upload_options : undefined,
				field_ids 	: [ '#modal_upload_file.open #file_display_name', '#modal_upload_file.open #videos' ],
				field_label : [ 'Display Name', 'File' ]
			}

			if( typeof( videos_uploadObj ) !== 'undefined' )
			{
				save_attr[ FILE_TYPES.FILE_TYPE_VIDEOS ]['upload_options'] 		= videos_options_upload_obj;
				save_attr[ FILE_TYPES.FILE_TYPE_VIDEOS ]['upload_obj'] 			= videos_uploadObj;
			}

			save_attr[ FILE_TYPES.FILE_TYPE_AUDIOS ] 	= {
				url 		: $base_url + $module + '/Files/save',
				dir 		: dir_mod+DIRECTORIES.DIRECTORY_AUDIOS,
				upload_obj 	: undefined,
				upload_options : undefined,
				field_ids 	: [ '#modal_upload_file.open #file_display_name', '#modal_upload_file.open #audios' ],
				field_label : [ 'Display Name', 'File' ]
			}

			if( typeof( audios_uploadObj ) !== 'undefined' )
			{
				save_attr[ FILE_TYPES.FILE_TYPE_AUDIOS ]['upload_options'] 		= audios_options_upload_obj;
				save_attr[ FILE_TYPES.FILE_TYPE_AUDIOS ]['upload_obj'] 			= audios_uploadObj;
			} 

			save_attr[ FILE_TYPES.FILE_TYPE_IMAGES ] 	= {
				url 		: $base_url + $module + '/Files/save',
				dir 		: dir_mod+DIRECTORIES.DIRECTORY_IMAGES,
				upload_obj 	: undefined,
				upload_options : undefined,
				field_ids 	: [ '#modal_upload_file.open #file_display_name', '#modal_upload_file.open #images' ],
				field_label : [ 'Display Name', 'File' ]
			}

			if( typeof( images_uploadObj ) !== 'undefined' )
			{
				save_attr[ FILE_TYPES.FILE_TYPE_IMAGES ]['upload_options'] 		= images_options_upload_obj;
				save_attr[ FILE_TYPES.FILE_TYPE_IMAGES ]['upload_obj'] 			= images_uploadObj;
			}

		}

		if( Object.keys( FILE_TYPES ).length !== 0
			&& save_attr[ file_type ] !== undefined
		)
		{
			var file_detail = save_attr[ file_type ]

			if( file_detail.upload_obj !== undefined )
			{
				if( file_detail.upload_options !== undefined )
				{
					file_detail.upload_options.dy_path 	= file_detail.dir;
				}

				var file_val,
					file_orig_val,
					display_n,
					desc_n;

				if( file_type != FILE_TYPES.FILE_TYPE_ALBUMS )
				{
					switch( file_type )
					{
						case FILE_TYPES.FILE_TYPE_DOCUMENTS :

							file_val 		= $('#document_hide').val();
							file_orig_val 	= $('#document_orig_hide').val();
							display_n 		= $('#document_display_name_hide').val();
							desc_n 			= $('#document_description_hide').val();
							
							$('#document').val(file_val);
							$('#document').attr('data-origfile', file_orig_val);
							$('#document').attr('data-upload-display-name', display_n);
							$('#document').attr('data-upload-description', desc_n);
							$('#document_orig_filename').val(file_orig_val);
						break;
					}
				}
				
				file_detail.upload_obj.recallOnLoad( file_detail.upload_options );
			}

			trigger_access_rights( save_attr, file_type );
		}
		
	}

	var trigger_access_rights 	= function( details, file_type )
	{
		var btn = $('.access_right_btn');
		
		btn.on('click', function( e )
		{
			var fil_type 	= $('#modal_upload_file.open .file_type').val();
			
			e.preventDefault();
			e.stopImmediatePropagation();
			
			dynamic_parsley( details[fil_type]['field_ids'], details[fil_type]['field_label'], '#'+fil_type+'_access_rights_btn_modal' );
			
			// $('#'+file_type+'_access_rights_btn_modal').click();
		})
	}

	var save 	= function( file_type, module, revision, multi_check )
	{
		var btn,
			form,
			parsley;

		var url;
			
		if( revision )
		{
			btn 	= $('#submit_modal_upload_file_version');
			form 	= $('#form_modal_upload_file_version');
		}
		else
		{
			btn 	= $('.submit_file');
			form 	= $('#form_modal_upload_file');
		}

		parsley		= form.parsley({ excluded: 'input[type=button], input[type=submit], input[type=reset]',
		    inputs: 'input, textarea, select, input[type=hidden]' });

		if( Object.keys( save_attr ).length !== 0 
			&& save_attr[ file_type ] !== undefined
		)
		{
			var file_detail 	= save_attr[ file_type ];

			url 				= file_detail.url;

			if( revision )
			{
				url 			= $base_url + $module + '/Files/save_revision';
			}

			if( multi_check )
			{
				url 			= $base_url + $module + '/Files/save_multiple_files';
			}
			
			btn.on('click', function( e )
			{
				e.preventDefault();
				e.stopImmediatePropagation();

				parsley.validate();

				if( parsley.isValid() )
				{
					var data 		= form.serialize();

					start_loading();

					$.post( url, data ).promise().done( function( response )
					{
						response 	= JSON.parse( response );

						if( response.flag )
						{
							if( response.file_id )
							{
								$('#'+response.file_type+'_file').val( response.file_id );
								$('#'+response.file_type+'_file_salt').val( response.file_salt );
								$('#'+response.file_type+'_file_token').val( response.file_token );
								$('#'+response.file_type+'_file_action').val( response.file_action );
							}

							if( revision )
							{
								if( response.file_version_id )
								{
									$('#'+response.file_type+'_file_version').val( response.file_version_id );
									$('#'+response.file_type+'_file_version_salt').val( response.file_version_salt );
									$('#'+response.file_type+'_file_version_token').val( response.file_version_token );
								}
							}

							load_datatable( response.datatable_options );

							if( revision )
							{
								$("#modal_upload_file_version").modal("close");
							}
							else
							{
								$("#modal_upload_file").modal("close");
							}

							var dir_mod 		= DIR_MOD_MAP[ response.module ]

							if( revision )
							{
								var dir_dy;

								switch( response.file_type )
								{
									case FILE_TYPES.FILE_TYPE_DOCUMENTS :
										dir_dy = dir_mod+DIRECTORIES.DIRECTORY_DOCUMENTS;
									break;
									case FILE_TYPES.FILE_TYPE_IMAGES :
										dir_dy = dir_mod+DIRECTORIES.DIRECTORY_IMAGES;
									break;
									case FILE_TYPES.FILE_TYPE_AUDIOS :
										dir_dy = dir_mod+DIRECTORIES.DIRECTORY_AUDIOS;
									break;
									case FILE_TYPES.FILE_TYPE_IMAGES :
										dir_dy = dir_mod+DIRECTORIES.DIRECTORY_VIDEOS;
									break;
								}

								version_uploadObj.update(
									{ 
 	 									dynamicFormData : function()
 	 									{
 	 										return {
	 	 										dir 		: dir_dy,
 	 										};
 	 									}
 	 								} 
								);

								version_uploadObj.startUpload();
							}
							else
							{

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
							}							
						}

						notification_msg(response.status, response.msg);

						end_loading();
					})
				}
			})
		}
		
	}

	var upload_multi 		= function()
	{
		var btn_hide 		= $('#add_files_multi_hide');
		var data_post 		= btn_hide.attr('data-modal_post');

		if( data_post != '' )
		{
			var json_post 			= JSON.parse(data_post);

			json_post['multiple']	= 1;

			var json_str 	 		= JSON.stringify( json_post );

			btn_hide.attr('data-modal_post', json_str);

			btn_hide.click();
		}
		
	}

	var successCallback 	= function(files,data,xhr,pd,revision, multi_check, prev_form_file)
	{
		var form;

		if( revision )
		{
			form 			= $('#form_modal_upload_file_version');
		}
		else
		{
			form 			= $('#form_modal_upload_file');
		}

		var post_data 		= form.serialize();

		post_data 			+= '&upd_attach=1';

		if( revision )
		{
			post_data 			+= '&revision=1';
		}

		var url 			= $base_url+$module+'/Files/update_file_attachment';

		if( multi_check )
		{
			url 			= $base_url+$module+'/Files/update_multiple_file_attachment';
			
			if( prev_form_file )
			{
				var prev_file 	= prev_form_file.uniqueNamereal,
					len_prev 	= prev_file.length,
					i 			= 0;
					
				if( len_prev !== 0 )
				{
					post_data += '&'

					for( ; i < len_prev; i++ )
					{
						var prev_f = prev_file[i];

						post_data += 'file_prev_name[]='+prev_f+'&';
					}

					post_data 	= post_data.substring(0, post_data.length - 1);
				}

			}
		}

		$.post( url, post_data ).promise().done( function( response )
		{
			response 		= JSON.parse( response );

			if( response.flag )
			{
				load_datatable( response.datatable_options );
			}
		} );

	}

	var custom_html_func = function( check_load, cus_id, file_type, upload_stat, curr_upl_obj, dis_inp, filename )
	{
		var html 		= '';
		
		if( check_load )
		{
			if( upload_stat && curr_upl_obj )
			{
				var val = {
					display_name : curr_upl_obj.attr('data-upload-display-name') || "", 
					description  : curr_upl_obj.attr('data-upload-description') || ""
				}

				html 	= custom_html( cus_id, undefined, val );

				upload_stat.append(html);
			}
		}
		else
		{
			html 		= custom_html( cus_id, filename );

			return html;
		}
	}

	var obj_extend 		= function()
	{
		for(var i=1; i<arguments.length; i++)
		{
        	for(var key in arguments[i])
        	{
        		if(arguments[i].hasOwnProperty(key)) 
        		{ 
            		if (typeof arguments[0][key] === 'object'
                		&& typeof arguments[i][key] === 'object')
                	{
         				extend(arguments[0][key], arguments[i][key]);
                	}
            		else
            		{
               			arguments[0][key] = arguments[i][key];
               		}
         		}
            }
        }

		return arguments[0];
	
	}

	var custom_html 	= function( cus_id, filename, values )
	{

		var val  = {
			display_name : '',
			description  : ''
		};

		val 	= obj_extend( val, values );
		console.log(val);
		var html = '';

		html 	= `
			<div class="form-basic">
				<div class="row m-n p-t-sm">
					<div class="col s6 p-t-sm p-l-n">
						<div class="input-field">
							<label class="active required" for="`+cus_id+`_custom_title">Display Name</label>
							<input type="text" class="" name="display_name_multi[]" id="`+cus_id+`_custom_title" placeholder="Enter Display Name" value="`+val.display_name+`" data-parsley-required="true" data-parsley-trigger="change" />
						</div>
					</div>
					<div class="col s6 p-t-sm p-l-n">
						<label for="`+cus_id+`_custom_description" class="active">Description</label>
						<textarea name="file_description_multi[]" id="`+cus_id+`_custom_description" class="materialize-textarea" style="">`+val.description+`</textarea>
					</div>
				</div>
			</div>
		`;

		return html;
	}

	return {
		/*initObj : function()
		{
			initObj();
		},
		initForm : function()
		{
			initForm();
		},
		initModal : function()
		{
			initModal();
		},
		upload : function($form_id)
		{
			upload($form_id);
		},*/
		datatable_custom_option : function( options, colspan )
		{
			var opt 	= datatable_custom_option( options, colspan );

			return opt;
		},
		create_button_modal : function( file_type, obj )
		{
			create_button_modal( file_type, obj )
		},
		init_modal 		: function( file_type, module, multi_check )
		{
			init_modal( file_type, module, multi_check );
		},
		save 			: function( file_type, module, revision, multi_check )
		{
			save( file_type, module, revision, multi_check );
		},
		successCallback : function(files,data,xhr,pd,revision, multi_check, prev_form_file)
		{
			successCallback(files,data,xhr,pd,revision, multi_check, prev_form_file);
		},
		upload_multi : function()
		{
			upload_multi();
		},
		custom_html_func : function( check_load, cus_id, file_type, upload_stat, curr_upl_obj, dis_inp, filename )
		{
			html 	= custom_html_func( check_load, cus_id, file_type, upload_stat, curr_upl_obj, dis_inp, filename );

			if( !check_load )
			{
				return html;
			}

			
		},
		videos : function()
		{
			player = videojs(document.querySelector('.video-js'), 
				{
					"controls" 	: true, 
					"height" 	: 350, 
					"width" 	: 800, 
					// "aspectRatio" : '4:3',
					"responsive"	: true,
					"autoplay"	: true, 
					"preload" 	: "auto",

				}
			);

			players.push(player);
		},
		close_modal_video : function()
		{	
			// var allvid = videojs.getPlayers();
			if(players)
			{
				for( var key in players )
				{
					players[key].dispose();
					delete players[key];
				}
				// console.log(allvid);
			}
		}
	}
}();