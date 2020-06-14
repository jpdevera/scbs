var Multi_auth 	= function()
{
	var verify_btn = function(page)
	{
		var form_s 	= 'form_modal_verify_code';
		var btn_s 	= 'verify_btn';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley();

		$("."+btn_s).on( "click", function( e )
		{	
			$parsley.validate();

			e.preventDefault();

			e.stopImmediatePropagation();

			if( $parsley.isValid() )
			{
				var data 		= form.serialize();

				load_save(data, form_s, page);
			}
		});
	}

	var load_save	= function(data, form_s, page)
	{
		start_loading();

		$.post( $base_url + "Auth/verify_code/", data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				$('#modal_verify_code').modal("close");

				var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
					csrf_token 	= $('meta[name="csrf-token"]').attr('content'),
					data_p 	= {};

				data_p[csrf_name]	= csrf_token;
				data_p['username']	= response.username;
				data_p['password']	= $('#icon_password').val();
				data_p['ck_token'] 	= 1;

				if( page === undefined )
				{

					$.post($base_url+'auth/sign_in/', data_p).promise().done(function(result)
					{
						result = JSON.parse(result);
						auth_callback(result);
					});
				}
				else
				{
					switch(page)
					{
						case 'profile' :
							var new_email = $('#new_email').val();
							$('#email').val(new_email);
						break;
					}
				}
			}

			notification_msg(response.status, response.msg);
			end_loading();
		});
	}

	var send_code = function(page)
	{
		var form_s 	= 'form_modal_verify_code';
		var btn_s 	= 'send_code_btn';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley();

		$("."+btn_s).on( "click", function( e )
		{

			e.preventDefault();

			e.stopImmediatePropagation();

			$parsley.validate('new_value');

			if( $parsley.isValid('new_value') )
			{
				var data 		= form.serialize();
				start_loading();
				$.post($base_url+'user_management/Profile_account/send_code/', data).promise().done(function(response)
				{
					response = JSON.parse(response);

					if( response.flag )
					{

					}
					end_loading();
					notification_msg(response.status, response.msg);
				});
			}
		});
	}

	var resend 	= function(page, custom_data)
	{
		var form_s 	= 'form_modal_verify_code';
		var btn_s 	= 'resend_btn';
		var form 	= $('#'+form_s);
		var $parsley= form.parsley();

		$("."+btn_s).on( "click", function( e )
		{
			e.preventDefault();

			e.stopImmediatePropagation();

			$parsley.validate('new_value')

			if( $parsley.isValid('new_value') )
			{
				var data 		= ( custom_data ) ? custom_data : form.serialize();
				start_loading();

				var url 	= $base_url+'auth/resend_code/';

				if( page == 'profile' )
				{
					var url 	= $base_url+'user_management/Profile_account/send_code/';					
				}

				$.post(url, data).promise().done(function(response)
				{
					response = JSON.parse(response);

					if( response.flag )
					{

					}
					end_loading();
					notification_msg(response.status, response.msg);
				})
			}
		});
	}

	return {
		verify_btn : function(page)
		{
			verify_btn(page);
		},
		resend : function(page, custom_data)
		{
			resend(page, custom_data);
		},
		send_code : function(page)
		{
			send_code(page);
		}
	}
}();