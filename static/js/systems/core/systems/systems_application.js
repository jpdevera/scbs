var Systems = function()
{
	var $base_url = $("#base_url").val();
	var $module = "systems";
	
	var initObj = function()
	{
		deleteObj = new handleData({ controller : 'systems_application', method : 'delete_sytem', module: $module});
	}
	
	var modal_init = function()
	{
		$('#form_modal_systems').parsley();		
		
		$('#form_modal_systems').off('submit');
		$('#form_modal_systems').on('submit', function(e){
			e.preventDefault();
			
			if($(this).parsley().isValid()) {
				var data = $(this).serialize();
				// button_loader('submit_modal_systems', 1);
				
				$.ajax({
					type: 'post',
					url: $base_url + $module +'/systems_application/save_system',
					data: data,
					dataType: 'json',
					success: function(result) {
						// button_loader('submit_modal_systems', 0);
						
						notification_msg(result.status, result.msg);
						button_loader('submit_modal_systems', 0);

						if(result.status == "success")
						{
						  $("#modal_systems").trigger('closeModal');
						  load_datatable(result.datatable_options);
						}
					},
					error: function() {
						// button_loader('submit_modal_systems', 0);
					}
				});
			}
		});
	}
	
	var successCallback 	= function(arr, data)
	{
		var logo = output_image(data[0], arr.path);
		$("#" + arr.id + "_src").attr("src", logo);
		
		$("#" + arr.id).val(data);
		$("#" + arr.id + "_upload").prev(".ajax-file-upload").hide();
		$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").html("<i class='material-icons'>delete_forever</i>");
		
		var id = data.toString().split('.');
		$("input[name='system_logo']").val(data);
	}
	
	var deleteCallback 	= function(arr, data)
	{
		var id = data.toString().split('.');
		$("input[name='system_logo']").val('');

		$("#" + arr.id + "_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-error").fadeOut();	
		$("#" + arr.id).val(''); 
			
		if(typeof(arr.default_image_preview) != "undefined" && arr.default_image_preview !== null) {
			var logo = $base_url + arr.path_images + arr.default_image_preview;
			$("#" + arr.id + "_src").attr("src", logo);
		}
			
		$("#" + arr.id + "_upload").prev(".ajax-file-upload").show();
	}

	return {
		initObj : function()
		{
			initObj();
		},
		modal_init : function()
		{
			modal_init();
		},
		successCallback : function(arr, data)
		{
			successCallback(arr, data);
		},
		deleteCallback : function(arr, data)
		{
			deleteCallback(arr, data);
		}
	}
}();