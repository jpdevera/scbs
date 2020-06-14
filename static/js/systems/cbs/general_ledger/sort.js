var Sort = function()
{

	var $module 	 = 'general_ledger';
	var $controller  = 'sort';

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

	}

	//Delete Data
	var remove = function()
	{
		deleteObj = new handleData({ controller : $controller, method : 'process_delete', module: $module});
	}

	var add = function()
	{
		var button_id = '';
		var submit_flag = '';
		$('#submit_modal_add').off('click.add').on('click.add', function() {
			button_id = 'submit_modal_add';
			$("#form_modal_add").submit();
		});

		$('#form_modal_add').parsley();
		
		$('#form_modal_add').off("submit.add").on("submit.add", function(e) {
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
					$("#form_modal_add").modal("close");
					load_datatable(result.datatable);
				}
				return;
				
			}, 'json');       
		  }
		});
	}

	var edit = function()
	{	

		var button_id = '';
		var status = 'Y';
		$('#submit_modal_edit').off('click.add').on('click.add', function() {
			button_id = 'submit_modal_edit';
			status = 'Y';
			$("#form_modal_edit").submit();
		});

		$('#form_modal_edit').parsley();
		
		$('#form_modal_edit').off("submit.add").on("submit.add", function(e) {
		  e.preventDefault();
		  e.stopImmediatePropagation();

		  if ( $(this).parsley().isValid() ) {
			var data = $(this).serialize()+'&action=3';
			button_loader(button_id, 1);
			$.post($base_url + $module + "/"+$controller+"/process_action", data, function(result) {
				notification_msg(result.status, result.msg);
				button_loader(button_id, 0);
				
				if(result.status == "success")
				{
					$("#form_modal_edit").modal("close");
					load_datatable(result.datatable);
				}
				return;
				
			}, 'json');       
		  }
		});
	}


	return {
		init: function() 
		{
      		init();
    	},
    	add: function()
    	{
    		add();
    	},
    	edit: function()
    	{
    		edit();
    	},
		remove: function()
    	{
    		remove();
    	}
	}
}();
