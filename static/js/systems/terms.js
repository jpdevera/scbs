var Terms 	= function()
{
	var init 	= function()
	{
		$("#terms_checkbox").on("click", function(e){
			if( $(this).is(":checked") )
			{
				$("#terms_btn").removeAttr("style");
			}
			else
			{
				$("#terms_btn").attr("style", "display: none !important;");
			}	
		});
	}

	var proceed 	= function()
	{
		$("#terms_btn").on("click", function( e )
		{
			var data 	= {};
			
			e.stopImmediatePropagation();

			if( $("#terms_checkbox").is(":checked") )
			{
				data["agreement"]	= 1;
			}
			else
			{
				data["agreement"]	= 0;
			}

			var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
				csrf_token 	= $('meta[name="csrf-token"]').attr('content');

			data['sign_up_check']	= $('#sign_up_check').val();
			data["username"] 		= $("#icon_username").val();
			data["password"] 		= $("#icon_password").val();
			data[csrf_name]			= csrf_token;
			data['ck_token'] 	= 1;

		/*	$( "body" ).isLoading({
	        	text:       "<div class='loader'></div>", 
	        	position:   "overlay"
	  		});*/
	  		start_loading();

			$.post($base_url + "auth/update_user_agreement", data ).promise().done(function( response ){

				response = JSON.parse( response );

				if(response.flag == 0)
				{	
					$(".notify.error p").html(response.msg);
					$(".notify.error").notifyModal({
						duration : -1
					});
				}
				else
				{
					if( response.sign_up )
					{
						$('#modal_term_condition').modal('close');
						$('#modal_sign_up_link').click();
					}
					else
					{
						if( response.user_id )
						{
							var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
								csrf_token 	= $('meta[name="csrf-token"]').attr('content'),
								data_p 	= {};

							data_p[csrf_name]	= csrf_token;
							data_p['username']	= response.username;
							data_p['password']	= $('#icon_password').val();
							data_p['ck_token'] 	= 1;

							$.post($base_url+'auth/sign_out/'+response.user_id, data_p).promise().done(function()
							{
								$.post($base_url+'auth/sign_in/', data_p).promise().done(function(result)
								{
									result = JSON.parse(result);
									$refresh_url = auth_callback(result);

									/*if($refresh_url)
									{
										window.location = $refresh_url;
									}*/
								});
							})
						}
						/*var $home_page 	= $("#home_page").val();

						if( response.redirect_page != '' )
						{
							window.location = $base_url + response.redirect_page;
						}
						else
						{
							window.location = $base_url;
						}		*/
					}
				}

				$('#modal_term_condition').modal('close');
				
				// $("body").isLoading("hide");		  	
				end_loading();
			});

		});
	}

	var common_move 	= function(animate_next, next_fs, current_fs, self, tab)
	{
		/*if( $(self).attr('data-disable') == 'disabled' )
		{*/
			animate_next( next_fs, current_fs );
			// return;
		// }
	}

	return {
		init : function()
		{
			init();
		},
		proceed : function()
		{
			proceed();
		},
		common_move : function(animate_next, next_fs, current_fs, self)
		{
			common_move(animate_next, next_fs, current_fs, self);
		}
	};

}();