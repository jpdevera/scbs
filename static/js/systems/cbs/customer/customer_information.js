var Customer_information = function()
{

	var $module 	 = 'customer';
	var $controller  = 'Customer_information';

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

		// Initialize function on page load
		init_locations();

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
		$('#submit_customer_information').off('click.save').on('click.save', function() {
			button_id = 'submit_customer_information';
			submit_flag = 'Y';
			$("#form_customer_information").submit();
		});

		$('#form_customer_information').parsley();
		
		$('#form_customer_information').off("submit.save").on("submit.save", function(e) {
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
					console.log('a');
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

  	/**
    * Initialize Locations 
    * Provinces, City_Muni and Barangay
    */
    init_locations = () => 
    {

    	// Residential Address
    	$('input[name="same_as_residential"]').click(function() {
      		if ($(this).is(':checked')) {
		        $('#permanent_div').slideUp('fast');
		        $('#permanent_div .input-field > input').val('');
		        $('#permanent_div .input-field > input').removeAttr('data-parsley-required');
		        $('#permanent_div .input-field > select.selectized').removeAttr('data-parsley-required');
		        $('#permanent_div select.region')[0].selectize.disable();
		        $('#permanent_div select.region')[0].selectize.setValue('');
	      	} else {
		        $('#permanent_div').slideDown('fast');
		        $('#permanent_div select.region')[0].selectize.enable();
		        $('#permanent_div .input-field > select.selectized').attr('data-parsley-required', 'true');
	      	}
	    });

    	// set permanent address
	  	if ($('input[name="same_as_residential"]').hasClass('checked')) 
	      $('#same_as_residential').trigger('click');

      	// Set the locations value
		if( $("select.region").attr('value') )
			$("select.region").trigger('change');

	  	$('select.region').on('change', function() {
			let url = `${$base_url}${$module}/${$controller}/get_province`;
			let parent = $(this).closest('.address');
			let data = {
			region_code: $(this).val()
			};

			_init_selectize(url, data, parent, 'select.province');
		});

	    $('select.province').on('change', function() {
			let url = `${$base_url}${$module}/${$controller}/get_citymuni`;
			let parent = $(this).closest('.address');
			let data = {
			region_code: parent.find('select.region').val(),
			province_code: $(this).val()
			};

			_init_selectize(url, data, parent, 'select.citymuni');
	    });

	    $('select.citymuni').on('change', function() {
			let url = `${$base_url}${$module}/${$controller}/get_barangay`;
			let parent = $(this).closest('.address');
			let data = {
			region_code: parent.find('select.region').val(),
			province_code: parent.find('select.province').val(),
			citymuni_code: $(this).val()
			};

			_init_selectize(url, data, parent, 'select.barangay');
	    });
    }

    _init_selectize = (url, data, parent, select) => {
		$.post(url, data, function(res) {
			let sltz = parent.find(select);
			sltz[0].selectize.blur();
			sltz[0].selectize.enable();
			sltz[0].selectize.clearOptions();

			for (let x=0; x<res.data.length; x++) {
				sltz[0].selectize.addOption(res.data[x]);
			}

			if (sltz.attr('value')) {
				console.log(sltz.attr('value'));
				sltz[0].selectize.setValue(sltz.attr('value'));
				sltz.removeAttr('value');
			}
		}, 'json');
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