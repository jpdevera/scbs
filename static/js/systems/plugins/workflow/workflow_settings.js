var Workflow_settings 	= function()
{
	var $module 		= "workflow";

	var init_settings 	= function()
	{
		$('#stages_flag_id').on('click', function()
		{
			process_stages( $(this) );
		});
	}

	var process_stages 	= function( obj )
	{
		if(  obj.is(':checked') )
		{
			$('#stages_id').removeAttr('readonly');
			$('#stages_description_id').removeAttr('readonly');
			$('#stages_id').attr('data-parsley-required', 'true');
			$('#stages_id').attr('data-parsley-trigger', 'keyup');
		}
		else
		{
			$('#stages_id').attr('readonly', 'readonly');
			$('#stages_description_id').attr('readonly', 'readonly');
			$('#stages_id').removeAttr('data-parsley-required');
			$('#stages_id').removeAttr('data-parsley-trigger');
		}
	}

	var save_settings 	= function()
	{
		var parsley 	= $("#workflow_setting_form").parsley();

		$("#save_workflow_setting").on("click", function()
		{
			parsley.validate();

			if( parsley.isValid() )
			{

				var data = $("#workflow_setting_form").serialize();

				// button_loader('save_site_settings', 1);

				$.post($base_url + $module + "/Workflow_settings/save", data ).promise().done( function( result )
				{
					result 	= JSON.parse( result );

					notification_msg(result.status, result.msg);
					// button_loader('save_site_settings', 0);

					if(result.status == "success")
					{
						location.reload();
					}
				} );
			}
		});
	}

	return {
		init_settings : function()
		{
			init_settings();
		},
		save_settings : function()
		{
			save_settings();
		}
	}
}();