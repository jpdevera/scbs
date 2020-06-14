var CONSTANTS;

var $base_url 				= $("#base_url").val();

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

var options = {
	url 	: $base_url + 'Params/get_constants_ajax',
	async 	: false,
	success : function(response)
	{
		CONSTANTS 	= response;

	    Object.freeze(CONSTANTS);

	    $.CONSTANTS =  CONSTANTS;
	},
	dataType  : 'json'
};

$.ajax( options );