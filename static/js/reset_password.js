var Reset_password = function()
{
	var reset 		= function()
	{
		var base_url 	= $('#base_url').val();
		var form  		= $( '#reset-form' );
		var $home_page 	= $("#home_page").val();

		jQuery(document).off('click', '#reset_password');
	    jQuery(document).on('click', '#reset_password', function(e)
	    {

	    	form.parsley().validate();

	    	e.preventDefault();
	    	if( form.parsley().isValid() )
	    	{
		    	var data = form.serialize();

	  	    	jQuery.ajax({
	  	    		type: "POST",
	  	    		url: base_url + 'auth/update_password',
	  	    		data: data,
	  	    		dataType: 'json'
	  	    		})
	  	    		.done(function( result ) {
		        		if(result.flag == 1)
		        		{
		        			notification_msg('success', result.msg);
							button_loader('reset_password', 0);
		        		
		        			if( $('#to_sign_in').val() == '1' )
		        			{
		        				var data 	= {};

		        				var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
									csrf_token 	= $('meta[name="csrf-token"]').attr('content');

		        				data['username']	= $('#username').val();
		        				data['password']	= $('#password').val();
		        				data[csrf_name]		= csrf_token;

		        				$.post( $base_url+'auth/sign_in', data ).promise().done( function( result )
		        			 	{
		        			 		result 	= JSON.parse( result);

		        			 		if( result.redirect_page !== undefined )
									{
										window.location = $base_url + result.redirect_page;
									}
									else
									{
										window.location = $base_url;
									}	
		        			 	});
		        			}
		        			else
		        			{
		        				if( result.initial_flag == 0 )
			        			{
			        				window.location = base_url;
			        				
			        			}
			        			else
			        			{
			        				window.location = base_url;
			        			}
		        			}
		        		}
		        		else
		        		{
		        			notification_msg('error', result.msg);
							button_loader('reset_password', 0);
		        		}
	  	        		
	    		});
	  	    }
	    });
	}

	return {
		reset : function()
		{
			reset();
		}
	};

}();

$( function() {
	Reset_password.reset();
} );