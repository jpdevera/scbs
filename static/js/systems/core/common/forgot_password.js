var ForgotPw = function()
{
	window.Parsley.addValidator('existsemail', function(value, options)
	{
		var data 	= {
			email : value,
		};

		var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
			csrf_token 	= $('meta[name="csrf-token"]').attr('content');

		data[csrf_name]	= csrf_token;
		// data_p['ck_token'] 	= 1;
		var self = this;

		var valid = false;
		
		$.ajax({
            url: $base_url+'Forgot_password/validate_post_email',
            data: data,
            async: false,
            dataType : 'json',
            success: function(response) 
            {
            	if( response.msg )
            	{
            		window.Parsley.addMessage('en', 'existsemail', response.msg);
            	}

            	valid = response.valid;
            }
        });

        return valid;
	});

	var initResetModal = function(validate_pass_length, pass_length, upper_length, digit_length, pass_err)
	{
		/*window.ParsleyValidator.addValidator('pass', 
			function (input, data_val) {
			var input_copy = input;
			var input_count = input.length;
			var upper_count = input_copy.replace(/[^A-Z]/g, "").length;
			var digit_count = input_copy.replace(/[^0-9]/g, "").length;
			if(validate_pass_length)
			{
				if(input_count < parseInt(pass_length) || upper_count < parseInt(upper_length) || digit_count < parseInt(digit_length)){
					return false;
				}
				return true;
			}
		}).addMessage('en', 'pass', pass_err);*/
	}
	
	var save = function()
	{
		$('#forgot_password_form').parsley();
		
		$('#forgot_password_form').off("submit.forgot_password").on("submit.forgot_password", function(e) {
			e.preventDefault();
			
			if ( $(this).parsley().isValid() ) {
			  var data = $(this).serialize();
				  
			  button_loader('forgot_password_btn', 1);
			  $.post($base_url + "forgot_password/request_reset/", data, function(result) {
				
				notification_msg(result.status, result.msg);
				button_loader('forgot_password_btn', 0);				
				
				if(result.status == "success"){
				  $("#modal_forgot_pw").modal("close");
				}
				
			  }, 'json');       
			}
		});
	}
	
	var saveReset = function()
	{
		$('#form_modal_reset_pw').parsley();
		
		$('#form_modal_reset_pw').off("submit.reset_password").on("submit.reset_password", function(e) {
			e.preventDefault();
			
			if ( $(this).parsley().isValid() ) {
			  var data = $(this).serialize();
				  
			  button_loader('reset_password_btn', 1);
			  $.post($base_url + "forgot_password/update/", data, function(result) {
				
				notification_msg(result.status, result.msg);
				button_loader('reset_password_btn', 0);				
				
				if(result.status == "success"){
					setTimeout(function(){ window.location = $base_url; }, 5000);
				}
				
			  }, 'json');       
			}
		});
	}
	
	var saveUser = function()
	{
		$('#form_modal_verify_account').parsley();
		
		$('#form_modal_verify_account').off("submit").on("submit", function(e) {
			e.preventDefault();
			
			if ( $(this).parsley().isValid() ) {
			  var data = $(this).serialize();
				  
			  button_loader('continue_btn', 1);
			  $.post($base_url + "auth/update_user_account/", data, function(result) {
				button_loader('continue_btn', 0);				
				
				if(result.status == "success"){
					var data = $('#form_modal_verify_account').serialize();
					$.post($base_url + "auth/sign_in/", data, function(x) {
					
						if(x.flag == 0){			
							button_loader('submit_login', 0);
							
						} else {
							if( x.redirect_page !== undefined && x.redirect_page != '' )
							{
								window.location = $base_url + x.redirect_page;
							}
							else
							{
								window.location = $base_url;
							}
						}
					}, 'json');
				}else{
					notification_msg(result.status, result.msg);
				}
				
			  }, 'json');       
			}
		});
	}

	var common_move 	= function(animate_next, next_fs, current_fs, self, tab)
	{
		var main 	= $('#main_fieldset').serialize();
		var data 	= current_fs.serialize();

		data 		= data+'&'+main+'&tab_type='+tab;

		if( $('ul.tabs').find('li.tab').find('a.active').length !== 0 )
		{
			data 	+= '&sub_tab='+$('ul.tabs').find('li.tab').find('a.active').attr('id');
		}
		
		if( $(self).attr('data-disable') == 'disabled' )
		{
			animate_next( next_fs, current_fs );
			return;
		}

		start_loading();

		$.post( $base_url+'Forgot_password/process_forgot_password/', data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				if( response.user_id_enc )
				{
					$('#user_id_inp').val( response.user_id_enc );
					$('#salt_inp').val( response.user_salt );
					$('#token_inp').val( response.user_token );
					$('#action_inp').val( response.action );
				}

				if( response.security_question_view )
				{
					$('#sec_ques_div').html( response.security_question_view );
				}

				if( response.click_li )
				{
					if( $('ul.tabs').find('li.tab').length == 1 )
					{
						$('ul.tabs').find('li.tab:first').find('a').addClass('active');
						$('ul.tabs').find('li.tab:first').find('a').trigger('click');
						$('#email_help_text').addClass('hide');
					}
					else
					{
						$('ul.tabs').find('li.tab').find('a').removeClass('active');
						$('#email_help_text').removeClass('hide');
					}
				}

				if( response.close_modal )
				{
					$("#modal_forgot_pw").modal("close");
				}

				if( !$(self).hasClass('save-wizard') )
				{
					animate_next( next_fs, current_fs );
				}
			}
			end_loading();
			notification_msg(response.status, response.msg);			
		});
	}

	var move_email 	= function(animate_next, next_fs, current_fs, self)
	{
		common_move(animate_next, next_fs, current_fs, self, 'email');
	}

	var move_sec_answer = function(animate_next, next_fs, current_fs, self)
	{
		common_move(animate_next, next_fs, current_fs, self, 'security_question');	
	}

	var move_verify = function(animate_next, next_fs, current_fs, self)
	{
		common_move(animate_next, next_fs, current_fs, self, 'verification');		
	}

	var move_acc_details = function(animate_next, next_fs, current_fs, self)
	{
		common_move(animate_next, next_fs, current_fs, self, 'password');
	}

	return {
		initResetModal : function(validate_pass_length, pass_length, upper_length, digit_length, pass_err)
		{
			initResetModal(validate_pass_length, pass_length, upper_length, digit_length, pass_err);
		},
		save : function()
		{
			save();
		},
		saveReset : function()
		{
			saveReset();
		},
		saveUser : function()
		{
			saveUser();
		},
		move_email : function(animate_next, next_fs, current_fs, self)
		{
			move_email(animate_next, next_fs, current_fs, self);
		},
		move_sec_answer : function(animate_next, next_fs, current_fs, self)
		{
			move_sec_answer(animate_next, next_fs, current_fs, self);
		},
		move_verify : function(animate_next, next_fs, current_fs, self)
		{
			move_verify(animate_next, next_fs, current_fs, self);
		},
		move_acc_details : function(animate_next, next_fs, current_fs, self)
		{
			move_acc_details(animate_next, next_fs, current_fs, self);
		}
	}
}();