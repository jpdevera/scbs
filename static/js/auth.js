function trigger_verify_code(multi_auth)	
{
	var data_p 	= multi_auth || {};

	var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
		csrf_token 	= $('meta[name="csrf-token"]').attr('content');

	data_p[csrf_name]	= csrf_token;
	data_p['ck_token'] 	= 1;

	var modal_obj = $('#modal_verify_code').modal({
		dismissible: false, // Modal can be dismissed by clicking outside of the modal
		opacity: .5, // Opacity of modal background
		in_duration: 300, // Transition in duration
		out_duration: 200, // Transition out duration
		ready: function() {
			start_loading();
			$.post($base_url + 'auth/modal_verify_code/', data_p).promise().done(function(response)
			{
				$("#modal_verify_code .modal-content #content").html(response);
				end_loading();
			});
			$( "body" ).find(".modal").first().before("<div class='triggered-modal'></div>")
			$('.triggered-modal').css({
				'position'	: 'fixed',
				'width' 	: '100%',
				'height' 	: '100%',
				'top' 		: '0',
				'left' 		: '0',
				'z-index' 	: '1000',
				'opacity' 	: '0',
				'background' : 'rgba(0,0,0,0.8) !important',
				'-webkit-transition' : 'all 0.3s',
				'-moz-transition' : 'all 0.3s',
				'transition' : 'all 0.3s'
			});
			$( "body" ).removeAttr('style');
		}, // Callback for Modal open
		complete: function() { 
	  		$( "body" ).find('.triggered-modal').remove();
		} // Callback for Modal close


	});
	
	modal_obj.trigger('openModal');
}

function auth_callback(result)
{
	var $refersh_url;
	if(result.flag == 0){			
		button_loader('submit_login', 0);
		$(".notify.error p").html(result.msg);
		var err = $(".notify.error").notifyModal({
			duration : -1
		});

		if( $('#log_soft_sec_val').length !== 0 )
		{

			var eventTime 	= $('#log_soft_date').val();
		    var currentTime = moment().unix();
		    var leftTime = eventTime - currentTime;//Now i am passing the left time from controller itself which handles timezone stuff (UTC), just to simply question i used harcoded values.
		    var duration = moment.duration(leftTime, 'seconds');
		    var interval = 1000;

		    var intervalF = setInterval(timer_block, interval); // 2000 ms = start after 2sec 
			function timer_block() 
			{
				if (duration.asSeconds() <= 0) 
				{
					clearInterval(intervalF);
					// window.location.reload(true); #skip the cache and reload the page from the server
					$(".notifyModal_content:visible").find('.close').trigger('click');
					
					return;
				}

				duration = moment.duration(duration.asSeconds() - 1, 'seconds');
				$('div.error:visible').find('#log_soft_sec').text( duration.seconds() + ' seconds' );
			}
		}
	} 
	else if( result.flag == 2 )
	{
		button_loader('submit_login', 0);
	/*	$(".notify.error p").html(result.msg);
		$(".notify.error").notifyModal({
			duration : -1
		});*/

		var modal_logout = $('#modal_logout').modal({
			dismissible: false, // Modal can be dismissed by clicking outside of the modal
			opacity: .5, // Opacity of modal background
			in_duration: 300, // Transition in duration
			out_duration: 200, // Transition out duration
			ready: function() {
				var data 	= {
					username	: $('#icon_username').val()
				};
				$.post($base_url+'auth/modal_logout/', data).promise().done(function(response)
				{
					$("#modal_logout .modal-content #content").html(response);
				})

				// $( "body" ).removeAttr('style');
			},
			complete: function() { 
		  		
			} //
		});

		modal_logout.trigger('openModal');
	}
	else 
	{console.log(result.checked_term_conditions);
		if( ( result.check_has_agreement_text == 'DPA_BASIC' || result.checked_term_conditions == 1 ) && result.user_agreed == 0 )
		{
			var modal_obj = $('#modal_term_condition').modal({
				dismissible: false, // Modal can be dismissed by clicking outside of the modal
				opacity: .5, // Opacity of modal background
				in_duration: 300, // Transition in duration
				out_duration: 200, // Transition out duration
				ready: function() {
					$("#modal_term_condition .modal-content #content").load($base_url + 'auth/modal_term_condition/');
					$( "body" ).find(".modal").first().before("<div class='triggered-modal'></div>")
					$('.triggered-modal').css({
						'position'	: 'fixed',
						'width' 	: '100%',
						'height' 	: '100%',
						'top' 		: '0',
						'left' 		: '0',
						'z-index' 	: '1000',
						'opacity' 	: '0',
						'background' : 'rgba(0,0,0,0.8) !important',
						'-webkit-transition' : 'all 0.3s',
						'-moz-transition' : 'all 0.3s',
						'transition' : 'all 0.3s'
					});
					$( "body" ).removeAttr('style');
				}, // Callback for Modal open
				complete: function() { 
			  		$( "body" ).find('.triggered-modal').remove();
				} // Callback for Modal close


			});

			modal_obj.trigger('openModal');

			button_loader('submit_login', 0);
		}
		else
		{
			if( result.multi_auth.length !== 0 )
			{
				trigger_verify_code(result.multi_auth);
			}
			else
			{
				if( result.initial_flag == 0 ) 
				{
					if( result.redirect_page !== undefined )
					{
						$refersh_url = $base_url + result.redirect_page;
						window.location = $base_url + result.redirect_page;
					}
					else
					{
						$refersh_url = $base_url + $home_page;
						window.location = $base_url + $home_page;
					}					
				}
				else 
				{
					$refersh_url = $base_url + "reset_password/initial_logged_in/"+result.username+"/"+result.salt+"/"+result.initial_flag;	
					window.location = $base_url + "reset_password/initial_logged_in/"+result.username+"/"+result.salt+"/"+result.initial_flag;	
				}
			}
		}
	}	

	return $refersh_url;
}

$(function(){
	var $base_url = $("#base_url").val();
		$home_page = $("#home_page").val();
		$login = $("#login_form");
		$logout = $("#logout"),
		submit_btn 	= $('#submit_login,#login_form'),
		$submit_modal_logout = $('#submit_modal_logout,#form_modal_logout'),
		$form_modal_logout 	 = $('#form_modal_logout_form2');	
		
	$submit_modal_logout.on('click keypress', function(e)
	{

		// e.preventDefault();
		// e.stopImmediatePropagation();

		var subm = false;

		if( $(this).attr('id') != 'submit_modal_logout' )
		{
			if( event.keyCode == 13 )
			{
				subm = true;
			}
		}
		else
		{
			if( event.type == 'click' )
			{
				subm = true;
			}
		}

		if( subm )
		{
			var data = $('#form_modal_logout').serialize();

			start_loading();
		
	  		$.post($base_url + "auth/auto_logout/", data, function(result) 
	  		{
	  			if(result.flag == 0)
				{
					$(".notify.error p").html(result.msg);
					$(".notify.error").notifyModal({
						duration : -1
					});
				}
				else
				{
					$(".notify.success p").html(result.msg);
					$(".notify.success").notifyModal({
						duration : -1
					});

					$('#modal_logout').modal('close');

					if($('#submit_login').length !== 0)
					{
						$('#submit_login').trigger('click');
					}
				}

	  			end_loading();  				

	  		}, 'json');
	  	}
	});

	// $login.submit(function(event){
	submit_btn.on('click keypress', function(event){
		var data = $("#login_form").serialize();
		// event.preventDefault();

		var subm = false;

		if( $(this).attr('id') != 'submit_login' )
		{
			if( event.keyCode == 13 )
			{
				subm = true;
			}
		}
		else
		{
			if( event.type == 'click' )
			{
				subm = true;
			}
		}

		if( subm )
		{
			button_loader('submit_login', 1);
			$.post($base_url + "auth/sign_in/", data, function(result) {
				auth_callback(result);
			}, 'json');	
		}
	});
	
	$logout.on("click", function(){
		$.post($base_url + "auth/sign_out/", function(result) {
			if(result.flag == 0){
				Materialize.toast(result.msg, 3000);
			}
			else
			{
				window.location = $base_url;
			}	
		}, 'json');
	});
	
});