var Statements = function()
{
	var ite 		= 1;
	var glob_ck;

	var check_arr 			= [];
	var check_file 			= [];

	var process_statement_type 	= function(val)
	{
		var statement_div = $('#statement_div'),
			statement_label = $('#statement_label'),
			statement 	= $('#write_announcement_textarea'),
			statement_link_div = $('#statement_link_div'),
			statement_link = $('#statement_link'),
			statement_link_label = $('#statement_link_label'),
			statement_file_div = $('#statement_file_div'),
			statements_file_inp = $('.statements_file_inp')


		statement_div.attr('style', 'display : none !important;');
		statement_label.removeClass('required');
		statement.attr('data-parsley-required', 'false');

		statement_link_div.attr('style', 'display : none !important;');
		statement_link_label.removeClass('required');
		statement_link.attr('data-parsley-required', 'false');

		statement_file_div.attr('style', 'display : none !important;');
		statements_file_inp.attr('data-parsley-required', 'false');

		if( val ) 
		{
			if( val == CONSTANTS.STATEMENT_TYPE_TEXT ) 
			{				
				statement_div.removeAttr('style');
				statement_label.addClass('required');
				statement.attr('data-parsley-required', 'true');				
			}
			else if( val == CONSTANTS.STATEMENT_TYPE_LINK )
			{
				statement_link_div.removeAttr('style');
				statement_link_label.addClass('required');
				statement_link.attr('data-parsley-required', 'true');
			}
			else if( val == CONSTANTS.STATEMENT_TYPE_FILE )
			{
				statement_file_div.removeAttr('style');
				statements_file_inp.attr('data-parsley-required', 'true');	
			}
			
		}
		else
		{

		}
	}

	var modal_init	= function( )
	{
		if( $('#action_inp').val() == CONSTANTS.ACTION_VIEW )
		{
			$("#submit_modal_statements").hide();
		}
		else
		{
			$("#submit_modal_statements").show();
		}

		var ckobj 	= load_ck();

		glob_ck 	= ckobj;	

		$('#statement_type').on('change', function()
		{
			var val 	= $(this).val();
			process_statement_type(val);
		});

		proce_stat_mod($('#statement_module_type'));
		change_stat_mod();

		// save();
	}

	var change_stat_mod = function()
	{
		$('#statement_module_type').on('change', function(e)
		{
			proce_stat_mod($(this))
		})
	}

	var proce_stat_mod = function(obj)
	{
		var val = obj.val();
		
		if( val != '' && val == CONSTANTS.STATEMENT_MODULE_EMAIL_TEMPLATE )
		{
			$('#email_subject_div').removeClass('hide');
			$('#statement_subject').attr('data-parsley-required', 'true');
		}
		else
		{
			$('#email_subject_div').addClass('hide');
			$('#statement_subject').attr('data-parsley-required', 'false');
		}
	}

	var load_ck 	= function()
	{
		var cked;
		
		/*if(	!CKEDITOR.instances['write_announcement_textarea'] )
		{*/
		
			cked 	= CKEDITOR.replace('write_announcement_textarea', {
				   /*toolbarGroups: [
						{"name":"basicstyles","groups":["basicstyles"]},
						{"name":"links","groups":["links"]},
						{"name":"paragraph","groups":["list","blocks"]},
						{"name": 'insert',"groups":["Image"]},
					],*/
					autoParagraph: false,
					allowedContent: true ,
					// height: '50px',
					// format_p:{ element : 'span', style : { 'color' : 'blue' } }
			});



			CKEDITOR.on('dialogDefinition', function (e) 
			{
			 	var dialogDefinition 	= e.data.definition;

			 	var dialogName 			= e.data.name;
			 	var editor 				= e.editor;

				/*  //current editor
				  var editor = e.editor;
				  var dialogDefinition = e.data.definition;

				  if (dialogName === "image") {
				    dialogDefinition.dialog.on('show', function(e){
				      var dialogBox = e.sender;

				        //This line is the answer of your question
				       //this line will get rid of the error setCustomData
				      dialogBox.originalElement = editor.getSelection().getStartElement();
				      this.selectPage("Upload");
				    });
				  }*/

			 	var $body 	= $('body');
	    	 	$body.removeClass('md-open');
                $body.removeAttr('style');

			    /*dialogDefinition.onShow = function (e) 
			    {
			    	var $body 	= $('body');
		    	 	$body.removeClass('md-open');
	                $body.removeAttr('style');
			        // ModalEffects.resetScroll();
			        var dialogBox = e.sender;
			        dialogBox.originalElement = editor.getSelection().getStartElement();
			      	this.selectPage("Upload");
			    };

			    dialogDefinition.onHide = function () 
			    {
			    	// ModalEffects.setScroll();
				};*/

			});
		/*}
		else
		{
			
		}*/

		return cked;
	}

	var save 	= function()
	{
		var form_s 	= 'form_modal_statements';
		var btn_s 	= 'submit_modal_statements';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley({ excluded: 'input[type=button], input[type=submit], input[type=reset]',
		    inputs: 'input, textarea, select, input[type=hidden]' });

		$("#"+btn_s).on( "click", function( e )
		{
			update_editor();

			$parsley.validate();

			e.preventDefault();

			e.stopImmediatePropagation();

			if( $parsley.isValid() )
			{
				var data 		= form.serialize();

				load_save(data);
			}
		});
	}

	var load_save	= function(data, form_s)
	{
		start_loading();

		$.post( $base_url + "maintenance/Statements/save/", data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				$('#statement_id_inp').val(response.statement_enc);
				$('#salt_inp').val(response.new_salt);
				$('#token_inp').val(response.new_token);
				$('#module_inp').val(response.module_enc);
				$('#action_inp').val(response.action);

				

				statements_file_uploadObj.startUpload();

				if( typeof( files_not_auto_submit ) !== 'undefined' )
				{
					check_file 		= files_not_auto_submit;
				}

				check_arr 			= [];

				$('#modal_statements').modal("close");
				if( datatable_objects[ response.datatable_id ] !== undefined )
				{
					datatable_objects[ response.datatable_id ].ajax.reload();
				}
				
			}

			notification_msg(response.status, response.msg);
			end_loading();
		});
	}

	var successCallback 	= function( files,data,xhr,pd )
	{
		var data 			= {},
			form 			= $('#form_modal_statements')
			form_data 		= form.serializeArray();

		start_loading();

		$.post( $base_url+'maintenance/Statements/save_uploads', form_data ).done( function( response )
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

	 		if( check_move )
	 		{
	 			end_loading();
	 		}
	 		
	 		response 		= JSON.parse( response );

	 		if( response.flag )
	 		{

		 		if( datatable_objects[ response.datatable_id ] !== undefined )
				{
					datatable_objects[ response.datatable_id ].ajax.reload();
				}
			}
	 	});

 	}

	return {
		modal_init : function()
		{
			modal_init();
		},
		save : function()
		{
			save();
		},
		successCallback : function( files,data,xhr,pd )
		{
			successCallback( files,data,xhr,pd );
		}
	}

}();