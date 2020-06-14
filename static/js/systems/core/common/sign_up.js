var SignUp = function()
{
	var init_form 	= function(trigger_next)
	{
		if( trigger_next )
		{
			$('.next').trigger('click');
		}

		if( $('#terms_checkbox').length !== 0 )
		{
			$('#terms_checkbox').on('click', function(e)
			{
				// e.preventDefault();

				proc_terms_checkbox($(this));
			});
		}
		// handle_orgs_dropdown();
	}

	var proc_terms_checkbox 	= function(obj)
	{
		if( obj.is(':checked') )
		{
			$('.next_basic').removeAttr('disabled')
			$('.next_basic').attr('style', 'width: 300px !important;');
		}
		else
		{
			$('.next_basic').attr('disabled', 'disabled')
			$('.next_basic').attr('style', 'width: 300px !important;background:gray !important;cursor:auto !important;');
		}
	}

	var main_org_dropdown 		= function( main_role, other_role, role_json, value_sel ) 
	{
		var json_dec 	= JSON.parse( $('#org_json').val() );

		if( json_dec[ value_sel ] !== undefined )
		{
			delete json_dec[ value_sel ];
		}

		if( Object.keys(json_dec).length !== 0 )
		{
			other_role.removeOption( value_sel );

			for( var value in json_dec )
			{
				other_role.addOption( { value : value, text : json_dec[ value ] } );
			}
		}
	}

	var other_org_dropdown 	= function( main_role, other_role, role_json, value_sel )
	{
		var json_dec 	= JSON.parse( $('#org_json').val() );
		var val 		= value_sel,
			len 		= val.length,
			i 			= 0;

		if( len !== 0 )
		{
			for( ; i < len; i++ )
			{
				main_role.removeOption( val[ i ] );

				if( json_dec[ val[ i ] ] !== undefined )
				{
					delete json_dec[ val[ i ] ];
				}
			}
		}

		if( Object.keys(json_dec).length !== 0 )
		{
			// 

			for( var value in json_dec )
			{

				main_role.addOption( { value : value, text : json_dec[ value ] } );
			}
		}
	}

	var handle_orgs_dropdown 	= function()
	{
		var role_json 	= $('#org_json').val();
		var other_role 	= $('#other_orgs')[0].selectize;
		
		var main_role 	= $('#main_org')[0].selectize;

		if( main_role.getValue() != '' )
		{
			main_org_dropdown( main_role, other_role, role_json, main_role.getValue() );
		}

		if( other_role.getValue().length !== 0 )
		{
			other_org_dropdown( main_role, other_role, role_json, other_role.getValue() );
		}

		if( role_json != '' )
		{
			var role_json_dec = JSON.parse( role_json );

			main_role.on('change', function( e )
			{
				main_org_dropdown( main_role, other_role, role_json, this.getValue() );

				// 
			});

			other_role.on('change', function( e )
			{
				other_org_dropdown( main_role, other_role, role_json, this.getValue() );
				
			});
		}
	}
	
	var initResetModal = function(validate_pass_length, pass_length, upper_length, digit_length, pass_err, repeat_pass)
	{
		password_constraints({
			pass_length : validate_pass_length,
			upper_length : upper_length,
			digit_length : digit_length,
			pass_err : pass_err,
			repeat_pass : repeat_pass,
			pass_same : 0
		});
	}
	
	var save = function()
	{
		$('#form_modal_sign_up').parsley();
		
		$('#form_modal_sign_up').off("submit.sign_up").on("submit.sign_up", function(e) {
			e.preventDefault();
			
			if ( $(this).parsley().isValid() ) {
			  var data = $(this).serialize();
				  
			  button_loader('create_account_btn', 1);
			  $.post($base_url + "sign_up/process/", data, function(result) {
				var opt = {
					size : 'large'
				};

				notification_msg(result.status, result.msg, false, opt);
				button_loader('create_account_btn', 0);				
				
				if(result.status == "success"){
				  $("#modal_sign_up").modal("close");
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

		if( $(self).attr('data-disable') == 'disabled' )
		{
			animate_next( next_fs, current_fs );
			return;
		}

		start_loading();

		$.post( $base_url+'Auth/save_sign_up/', data ).promise().done( function( response )
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

				if( response.add )
				{
					if( response.ref )
					{
						window.location		= response.new_url+'1/';
						
					}
					else
					{
						if( history.pushState )
						{
							history.pushState({}, null, response.new_url);
						}
						else
						{
							window.location		= response.new_url;
						}
					}
				}
				else
				{
					if( history.pushState && response.new_url )
					{
						history.pushState({}, null, response.new_url);
					}
				}

				if( response.disable_verify )
				{
					current_fs.find('#email_verification_code').attr('disabled', 'disabled');
					current_fs.find('.save-wizard').attr('data-disable', 'disabled');
					current_fs.find('.next').attr('data-disable', 'disabled');
					current_fs.find('#resend_btn').addClass('hide');
					current_fs.find('#email_inps').addClass('hide');
					current_fs.find('#email_ver_title').text('Email has been verified. Please proceed.');

					current_fs.find('#mobile_inps').addClass('hide');
					current_fs.find('#mobile_verification_code').attr('disabled', 'disabled');
					current_fs.find('#resend_mob_btn').addClass('hide');;
					current_fs.find('#mobile_ver_title').text('Mobile No. has been verified. Please proceed.');
				}

				if( response.email_auth_fac_id )
				{
					$('#email_authentication_factor_id').val(response.email_auth_fac_id);
				}
				
				if( response.mobile_auth_fac_id )
				{
					$('#mobile_authentication_factor_id').val(response.mobile_auth_fac_id);
				}

				if( !$(self).hasClass('save-wizard') )
				{
					if( !response.ref )
					{
						animate_next( next_fs, current_fs );
					}
				}

				if( response.redirect_to_login )
				{
					setTimeout(function()
					{
						window.location.href = $base_url;
					}, 5000);
				}
			}

			end_loading();
			notification_msg(response.status, response.msg);			
		});
	}

	var move_basic_info = function(animate_next, next_fs, current_fs, self)
	{
		common_move(animate_next, next_fs, current_fs, self, 'basic_info');
	}

	var move_id_details = function(animate_next, next_fs, current_fs, self)
	{
		common_move(animate_next, next_fs, current_fs, self, 'identification_detail');
	}

	var move_acc_details = function(animate_next, next_fs, current_fs, self)
	{
		common_move(animate_next, next_fs, current_fs, self, 'account_detail');
	}

	var move_verification = function(animate_next, next_fs, current_fs, self)
	{

		common_move(animate_next, next_fs, current_fs, self, 'verification');
	}

	var resend = function()
	{
		$('#resend_btn, #resend_mob_btn').on('click', function(e)
		{
			e.preventDefault();

			e.stopImmediatePropagation();

			var self = $(this);
			var parent_fieldset = self.parents('fieldset');
			var main 		= $('#main_fieldset').serialize();

			var data 		= parent_fieldset.serialize()+'&'+main;
			start_loading();
			$.post($base_url+'auth/resend_code/', data).promise().done(function(response)
			{
				response = JSON.parse(response);

				if( response.flag )
				{

				}
				end_loading();
				notification_msg(response.status, response.msg);
			})

		})
	}

	return {
		initResetModal : function(validate_pass_length, pass_length, upper_length, digit_length, pass_err, repeat_pass)
		{
			initResetModal(validate_pass_length, pass_length, upper_length, digit_length, pass_err, repeat_pass);
		},
		save : function()
		{
			save();
		},
		cancel : function()
		{
			window.location 	= $base_url;
		},
		move_basic_info : function(animate_next, next_fs, current_fs, self)
		{
			move_basic_info(animate_next, next_fs, current_fs, self);
		},
		move_id_details : function(animate_next, next_fs, current_fs, self)
		{
			move_id_details(animate_next, next_fs, current_fs, self);
		},
		move_acc_details : function(animate_next, next_fs, current_fs, self)
		{
			move_acc_details(animate_next, next_fs, current_fs, self);
		},
		move_verification : function(animate_next, next_fs, current_fs, self)
		{
			move_verification(animate_next, next_fs, current_fs, self);
		},
		init_form : function(trigger_next)
		{
			init_form(trigger_next);
		},
		resend : function()
		{
			resend();
		}
	}
}();