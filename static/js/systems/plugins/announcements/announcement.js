var Announcement = function()
{
	var ite 		= 1;
	var glob_ck;

	var modal_init	= function( )
	{
		if( $('#action_inp').val() == CONSTANTS.ACTION_VIEW )
		{
			$("#submit_modal_announcement").hide();
		}
		else
		{
			$("#submit_modal_announcement").show();
		}

		var ckobj 	= load_ck();

		glob_ck 	= ckobj;	

		save();
	}

	var load_ck 	= function()
	{
		var cked;
		
		/*if(	!CKEDITOR.instances['write_announcement_textarea'] )
		{*/
		
			cked 	= CKEDITOR.replace('write_announcement_textarea', {
				   toolbarGroups: [
						{"name":"basicstyles","groups":["basicstyles"]},
						{"name":"links","groups":["links"]},
						{"name":"paragraph","groups":["list","blocks"]},
						// {"name": 'insert',"groups":["Image"]},
					],
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
		var form_s 	= 'form_modal_announcement';
		var btn_s 	= 'submit_modal_announcement';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley();

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
		// start_loading();

		$.post( $base_url + "announcements/Announcement/save/", data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				$('#modal_announcement').modal("close");
				if( datatable_objects[ response.datatable_id ] !== undefined )
				{
					datatable_objects[ response.datatable_id ].ajax.reload();
				}
				
			}

			notification_msg(response.status, response.msg);
			// end_loading();
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
		}
	}
}();