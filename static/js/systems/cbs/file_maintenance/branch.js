var Branch = function()
{

	var $module 	 = 'file_maintenance';
	var $controller  = 'branch';

	var init = function()
	{

		$( document ).ajaxSend(function( event, jqxhr, settings ) {
			var str = settings.url;
			if(str.includes("process_action") != false){
				start_loading();
			}
			else if(str.includes("process_delete") != false){
				start_loading();
			}
			else if(str.includes("get") != false){
				start_loading();
			}
		});

		$( document ).ajaxComplete(function( event, xhr, settings ) {
			var str = settings.url;
			if(str.includes("process_action") != false){
				end_loading();
			}
			else if(str.includes("process_delete") != false){
				end_loading();
			}
			else if(str.includes("get") != false){
				end_loading();
			}
		});

		// Delete upload
	 	$('.ajax-file-upload-statusbar.uploaded .ajax-file-upload-red').click(function() {
	      var filename = $(this).closest('.ajax-file-upload-statusbar.uploaded').find('.ajax-file-upload-filename').text();
	      var action = confirm(`Are you sure you want to delete ${filename}`);

	      if (action)
	        $(this).closest('.ajax-file-upload-statusbar.uploaded').remove();
	    });

	}

	//Delete Data
	var remove = function()
	{
		deleteObj = new handleData({ controller : $controller, method : 'process_delete', module: $module});
	}

	var save = function()
	{
		var button_id = '';
		var submit_flag = '';
		$('#submit_branch').off('click.add').on('click.add', function() {
			button_id = 'submit_branch';
			get_uploads();
			$("#form_branch").submit();
		});

		$('#form_branch').parsley();
		
		$('#form_branch').off("submit.add").on("submit.add", function(e) {
		  e.preventDefault();
		  e.stopImmediatePropagation();

		  if ( $(this).parsley().isValid() ) {
			var data = $(this).serialize();
			button_loader(button_id, 1);
			$.post($base_url + $module + "/"+$controller+"/process_action", data, function(result) {
				notification_msg(result.status, result.msg);
				button_loader(button_id, 0);
				
				if(result.status == "success")
				{
					var url = `${$base_url}${$module}/${$controller}`;
					setTimeout(function(){ 
						window.location = url;
					}, 2000);
				}
				return;
				
			}, 'json');       
		  }
		});
	}

	var get_uploads = function() {
    $('.ajax-file-upload-statusbar').not('.uploaded').each(function(x) {
      let file_name = $(this).find('.ajax-file-upload-red').next().next().text();
      let sys_file_name = $(this).find('.ajax-file-upload-filename').text();
      file_name = file_name.split("Original Filename: ");

      $("input[name='sys_file_name']").val(sys_file_name);
      $("input[name='file_name']").val(file_name);

    });
  }


	return {
		init: function() 
		{
      		init();
    	},
    	save: function()
    	{
    		save();
    	},
		remove: function()
    	{
    		remove();
    	}
	}
}();
