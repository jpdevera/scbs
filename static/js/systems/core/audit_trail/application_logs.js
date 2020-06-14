var Application_logs 	= function()
{
	var $module = "audit_trail";

	var initForm = function( datatable_options )
	{
		var options 		= JSON.parse(datatable_options);

		$("#filter_audit_log").change(function(){
			var system_code = $(this).val();

			if(system_code != 0)
			{

				options.path = $module + "/application_logs/get_list/" + system_code;

				load_datatable(options);
			} 
			else 
			{
				options.path = $module + "/application_logs/get_list/";

				load_datatable(options);
			}
		});
	}

	return {
		initForm : function( datatable_options )
		{
			initForm( datatable_options );

		}
	};
}();