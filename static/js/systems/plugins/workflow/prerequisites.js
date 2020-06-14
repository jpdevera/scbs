var Prerequisites 	= function()
{

	var $module 	= "workflow";

	var load_table 	= function()
	{
		var data 	= {};

		data['workflow_main']	= $('#workflow_main').val();
		data['workflow_salt']	= $('#workflow_salt').val();
		data['workflow_token']	= $('#workflow_token').val();
		data['workflow_action']	= $('#workflow_action').val();

		$.post( $base_url+$module+'/Prerequisites/load_table', data ).promise().done( function( response )  
		{
			$('.prerequisites-container').html( response );

			collapsible_init();

			selectize_init();

		});
	}

	var save 		= function( animate_next, next_fs, current_fs )
	{
		var main 	= $('#main_fieldset').serialize();
		var data 	= current_fs.serialize();

		data 		= data+'&'+main;

		start_loading();

		$.post( $base_url+$module+'/Prerequisites/save/', data ).promise().done( function( response )
		{
			response 	= JSON.parse( response );

			if( response.flag )
			{
				load_table();
			}

			end_loading();
			notification_msg(response.status, response.msg);
		} );
	}

	return {
		table 	: function()
		{
			load_table();
		},
		save : function( animate_next, next_fs, current_fs )
		{
			save( animate_next, next_fs, current_fs );
		}
	}
}();