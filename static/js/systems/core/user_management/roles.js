var Roles = function()
{
	var $module = "user_management";
	
	var initObj = function()
	{
		deleteObj = new handleData({ controller : 'roles', method : 'delete_role', module: $module });

	}
	
	var initModal = function()
	{
		if($("#id_roles").val() != '')
		{
			$('.input-field label').addClass('active');
		}

		/*if( $('#system_role').val().length !== 0 )
		{
			process_default_system( $('#system_role').val() );
		}*/

		default_system();
	}

	var process_default_system 	= function( val )
	{
		var system_json 		= {};
		var options 			= [];
		var defaul_sys 			= $('#default_system')[0].selectize;
		var remov_opt;
		
		if( $('#system_json').val() != '' )
		{
			system_json 		= JSON.parse( $('#system_json').val() );

			if( val.length !== 0 )
			{
				var i 			= 0,
					len 		= val.length;

				for( ; i < len; i++ )
				{
					if( system_json[ val[i] ] )
					{
						var sys 	= system_json[ val[i] ],
							opt 	= {};

						defaul_sys.addOption( { value : val[i], text : sys } );

						if( i == 0 )
						{
							defaul_sys.setValue( val[i] );
						}

						delete system_json[ val[i] ];
						
					}
				}
			}

			remov_opt 	= system_json;

			if( Object.keys( remov_opt ).length !== 0 )
			{
				for( var key in remov_opt )
				{
					defaul_sys.removeOption( key );
				}
			}
		}
	}

	var default_system 	= function()
	{
		$('#system_role').on('change', function( e )
		{
			e.stopImmediatePropagation();

			process_default_system($(this).val());
		})
	}
	
	var loadTable = function()
	{
		$("#refresh_roles").on("click", function(){
			load_datatable("roles_table", $module + "/roles/get_role_list", "", "0", "0", false, "", "0", "asc", 4);
		});
	}

	var save = function()
	{
		$('#form_modal_roles').parsley();
		
		$('#form_modal_roles').off("submit.roles").on("submit.roles", function(e) {
			e.preventDefault();
			
			if ( $(this).parsley().isValid() ) {
			  var data = $(this).serialize();
				  
			  button_loader('submit_modal_roles', 1);
			  $.post($base_url + $module + "/roles/process", data, function(result) {
				
				notification_msg(result.status, result.msg);
				button_loader('submit_modal_roles', 0);				
				
				if(result.status == "success"){
				  $("#modal_roles").modal("close");
				  load_datatable(result.datatable_options);
				}
				
			  }, 'json');       
			}
		});
	}

	return {
		initObj : function()
		{
			initObj();
		},
		initModal : function()
		{
			initModal();
		},
		loadTable : function()
		{
			loadTable();
		},
		save : function()
		{
			save();
		}
	}
}();