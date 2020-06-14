var Session_expired 	= function()
{

	var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
	csrf_token 	= $('meta[name="csrf-token"]').attr('content');
	pass_data 	= {};

	pass_data[csrf_name]	= csrf_token;

	$.ajaxSetup({
		beforeSend: function(jqXHR, settings) 
		{
			
			if( csrf_name )	
			{
				if( settings.data )
				{
					settings.data 	+= '&'+csrf_name+'='+csrf_token;	
				}
				else
				{
					settings.data 	= csrf_name+'='+csrf_token;	
				}
				
			}
			
		},
		data 	: pass_data
	});
	
	var cancel 			= false;

	var log_in 			= function()
	{
		var form 		= $('#form_modal_sess_expired_log_in');
		var $parsley 	= form.parsley();

		$('#continue_btn').on('click', function(e)
		{
			$parsley.validate();

			e.preventDefault();

			e.stopImmediatePropagation();
			
			if( $parsley.isValid() )
			{
				var data 		= form.serialize();	

				var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
					csrf_token 	= $('meta[name="csrf-token"]').attr('content');

				data 		+= '&'+csrf_name+'='+csrf_token;

				$.post( $base_url + "Auth/sign_in/", data ).promise().done( function( response )
				{
					response 	= JSON.parse(response);

					if( response.flag )
					{
						notification_msg('success', response.msg);
						$('#modal_sess_expired_log_in').trigger('closeModal');

					}
					else
					{
						notification_msg('error', response.msg);
					}
				} );
			}
		});
	}

	var stay_connected 		= function()
	{
		$('#stay_connected').on('click', function()
		{
			$('#modal_warning_expired_log_in').trigger('closeModal');

			cancel = true;
		})
	}

	var log_out 			= function()
	{
		$('#sess_warning_logout').on('click', function()
		{
			$('#confirm_modal').confirmModal({
				topOffset : 0,
				onOkBut : function() {
					var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
						csrf_token 	= $('meta[name="csrf-token"]').attr('content');

						var data 	= {};

						data[csrf_name] = csrf_token;

					$.post($base_url + "auth/sign_out/" + $('#user_id').val(), data, function(result){
						if(result.flag == 1){
							window.location = $base_url;
						}
					},'json');
				},
				onCancelBut : function() {},
				onLoad : function() {
					$('.confirmModal_content h4').html('Are you sure you want to log out?');	
				},
				onClose : function() {}
			});
		});
	}

	var warning_sess_expired = function()
	{
		var progressbar = $( "#progressbar" ),
      	progressLabel 	= $( ".progress-label" );
		countdownLabel 	= $( ".progress-countdown" );

		cancel 			= false;

    	progressbar.progressbar({
	      value: 30,
	      max: 30,
		  change: function() {
			  if(progressbar.progressbar( "value" ) <= 1)
				  var unit = " second";
			  else
				  var unit = " seconds";
			  
			  countdownLabel.text( progressbar.progressbar( "value" ) + unit );
	      },
		  complete: function() {
        	/*progressLabel.text( "Expired" );
        	$('#session-text-label').html("Your session has already expired.");*/
        	
        	/*$.post($base_url + "auth/sign_out/" + $('#user_id').val(), function(result){
				if(result.flag == 1){
					window.location = $base_url+'auth/index/inactivity';
				}
			},'json');*/
  		  }
	    });

	    progress();
	}

	var progress 	= function() 
	{
		var progressbar 	= $( "#progressbar" ),
			progressLabel 	= $( ".progress-label" );;

		var val = progressbar.progressbar( "value" ) || 0;

		progressbar.progressbar( "value", val - 1 );

		if( cancel )
		{
			val = 0;
			clearTimeout( progress );
		}

		if ( val > 0 ) {
			setTimeout( progress, 1000 );
		}
		else
		{
			if( !cancel )
			{
				//progressLabel.text( "Expired" );
	        	$('#session-text-header').html("Your session has already expired.");
	        	$('#session-text-label').hide();
	        	$('#modal_warning_expired_log_in_close').hide();
	        	$('#sess_warning_logout').hide();
	        	$('#stay_connected').hide();
	        	$('#logged_me_in').show();
	        	$('#logged_me_in').removeClass('hide');
	        	$('#modal_warning_expired_log_in').find('#modal_warning_expired_log_in_close').hide();
	        	$('#modal_warning_expired_log_in').find('#progressbar').hide();
	        	
	        	$.post($base_url + "auth/sign_out/" + $('#user_id').val(), function(result){

					if(result.flag == 1){
						window.location = $base_url+'auth/index/inactivity';
					}
				},'json');
			}
		}
	}

	var logged_me_in 	= function()
	{
		$('#logged_me_in').on('click', function( e )
		{
			window.location = $base_url+'auth/index/inactivity';
		});
	}
 
    // setTimeout( progress, 2000 );

	return {
		log_in 		: function()
		{
			log_in();
		},
		warning_sess_expired : function()
		{
			stay_connected();
			log_out();
			logged_me_in();
			warning_sess_expired();
		}
	}
}();