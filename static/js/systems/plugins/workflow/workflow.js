var Workflow 	= function()
{
	var $module 	= "workflow";

	var init_page 	= function()
	{
		$(".link-filter").click(function(){
			var id = $(this).prop('id');
			
			$("#" + id).addClass("active");
			$(".link-filter").not("#" + id).removeClass("active");
			
			/*if(id == 'link_inactive_btn')
				$("#users_table thead th:eq(4)").html("Last Logged In");
			else
				$("#users_table thead th:eq(4)").html("Roles");*/
		});
	}

	var cancel 	= function()
	{
		window.location 	= $base_url+$module+'/Manage_workflow';
	}

	var delete_callback = function( workflow_active_cnt, workflow_inactive_cnt, workflow_append_cnt, datatable_options )
	{

		$("#link_active_btn").html('Active <span>'+workflow_active_cnt+'</span>');
		$("#link_inactive_btn").html('Inactive <span>'+workflow_inactive_cnt+'</span>');
		$("#link_append_btn").html('Attachable <span>'+workflow_append_cnt+'</span>');


		if( $(".link-filter.active").length !== 0 )
		{
			var active_link 	= $(".link-filter.active");
			
			switch( active_link.attr('id') )
			{
				case 'link_active_btn' :

					datatable_options['post_data']	= {
						status_link : 'Y'
					}

				break;

				case 'link_inactive_btn' :

					datatable_options['post_data']	= {
						status_link : 'N'
					}

				break;

				case 'link_append_btn' :

					datatable_options['post_data']	= {
						append_link : 'Y'
					}

				break;
			}

			load_datatable( datatable_options );
		}
	}

	var search_func 	= function(search_params)
	{
		var id 	= $(".link-filter.active").attr('id');

		if( id == 'link_active_btn' )
		{
			search_params['search_status_link']	= 'Y';
		}
		else if( id == 'link_inactive_btn' )
		{
			search_params['search_status_link']	= 'N';
		}
		else if( id == 'link_append_btn' )
		{
			search_params['search_append_link']	= 'Y';
		}

		return search_params;
	}

	return {
		cancel : function()
		{
			cancel();
		},
		init_page : function()
		{
			init_page();
		},
		delete_callback : function( workflow_active_cnt, workflow_inactive_cnt, workflow_append_cnt, datatable_options )
		{
			delete_callback( workflow_active_cnt, workflow_inactive_cnt, workflow_append_cnt, datatable_options );
		},
		search_func 	: function(search_params)
		{
			return search_func(search_params);
		}
	}
}();