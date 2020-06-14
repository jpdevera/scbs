var Process 	= function()
{
	var $module 	= "workflow";

	var save 		= function( animate_next, next_fs, current_fs, self )
	{
		var main 	= $('#main_fieldset').serialize();
		var data 	= current_fs.serialize();

		data 		= data+'&'+main;

		if( $(self).attr('data-disable') == 'disabled' )
		{
			animate_next( next_fs, current_fs );
			return;
		}

		start_loading();

		$.post( $base_url+$module+'/Process/save/', data ).promise().done( function( response )
		{
			// animate_next( next_fs, current_fs );
			response 	= JSON.parse( response );

			if( response.flag )
			{
				if( $('#workflow_main').val() == '' )
				{
					var new_url 			= $base_url+$module+'/Manage_workflow/create_new/'+response.workflow_action+'/'+response.workflow_main+'/'+response.workflow_salt+'/'+response.workflow_token;	

					if( history.pushState )
					{
						history.pushState({}, null, new_url);
					}
					else
					{
						window.location		= new_url;
					}
					
				}

				$('#workflow_main').val( response.workflow_main );
				$('#workflow_salt').val( response.workflow_salt );
				$('#workflow_token').val( response.workflow_token );
				$('#workflow_action').val( response.workflow_action );

				if( !$(self).hasClass('save-wizard') )
				{
					animate_next( next_fs, current_fs );
				}

				if( !response.check )
				{
					Steps.load_table();
				}
			}
			else
			{

			}

			end_loading();
			notification_msg(response.status, response.msg);			
		})

		// console.log(current_fs.find('input,select,textarea').serializeObject());
	}

	return {
		save : function( animate_next, next_fs, current_fs, self )
		{
			save( animate_next, next_fs, current_fs, self );
		}
	}
}();