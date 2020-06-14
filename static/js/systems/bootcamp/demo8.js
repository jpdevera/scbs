var Demo8 	= function()
{
	var check_arr 			= [];
	var check_file 			= [];

	var save 	= function()
	{
		var form_s 	= 'upload_form';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley({ excluded: 'input[type=button], input[type=submit], input[type=reset]',
		    inputs: 'input, textarea, select, input[type=hidden]' });

		$('#save_inp').on('click', function( e )
		{
			var self 		= $(this);

			$parsley.validate();

			e.preventDefault();
			e.stopImmediatePropagation();

			if( $parsley.isValid() )
			{


				var data 	= form.serialize();
				
				load_save(data, self);

			}
		});
	}

	var load_save 			= function(data, self)
	{
		start_loading();

		$.post( $base_url + "Demo8/save_uploads/", data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				attachments_uploadObj.startUpload();

				if( typeof( files_not_auto_submit ) !== 'undefined' )
				{
					check_file 		= files_not_auto_submit;
				}

				check_arr 			= [];

				end_loading();
			}
		});
	}

	var successCallback = function(files,data,xhr,pd)
	{
		var data 			= {},
			form 			= $('#upload_form')
			form_data 		= form.serializeArray();

		$.post( $base_url+'Demo8/save_uploads', form_data ).done( function( response )
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
	 		
	 		response 		= JSON.parse( response );

	 		if( response.flag )
	 		{
	 			if( check_move )
				{
					window.location.reload();
				}
	 		}
	 	});
	}

	return {
		successCallback	: function(files,data,xhr,pd)
		{
			successCallback(files,data,xhr,pd);
		},
		save	: function()
		{
			save();
		}
	}
}();