var Demo_report 	= function()
{
	var REPORTS;

	var REPORT_TYPE 	= 
	{
			
		/**
		 *  for pdf format
		 */
	    1 : function(response, dy_form, url, form_data, link, html_next_page) 
	    {

	      _target_blank(response, dy_form, url, form_data, link, html_next_page);
	    },
	    
	    /**
		 *  for excel format
		 */

	    2 : function(response, dy_form, url, form_data, link, html_next_page) 
	    {
	      _target_blank(response, dy_form, url, form_data, link, html_next_page);
	    },
	    
	    /**
		 *  for word format
		 */
	    3 : function(response, dy_form, url, form_data, link, html_next_page) 
	    {
	      _target_blank(response, dy_form, url, form_data, link, html_next_page);
	    },
	    
	    /**
		 *  for html format
		 */
	    4 : function(response, dy_form, url, form_data, link, html_next_page)
	    {
	    	_target_modal(response, dy_form, url, form_data, link, html_next_page);
	    }
  	}

  	/**
	 * Handles the genration of modal for html format report
	 */
	var _target_modal 	= function(response, dy_form, url, form_data, link, html_next_page)
	{
		if( html_next_page )	
		{
			dy_form.attr('action', $base_url+link+'/page/');
			
			dy_form.submit();
		}
		else
		{
			var modal_obj 		= $('#modal_report_html').modal({
				dismissible 	: true,
				opacity 		: .5,
				in_duration 	: 300,
				out_duration 	: 200,
				ready: function() {
					$.post( $base_url+link+'/modal', form_data ).promise().done( function( response )
					{
						$("#modal_report_html .modal-content #content").html( response )
					});
					
				},
				complete: function() {

				}
			});

			modal_obj.trigger("openModal");
		}

		end_loading();
		_reset( dy_form );
	}
	
	/**
	 * Handles the genration of report for excel,pdf,word format report
	 */
	var _target_blank 	= function(response, dy_form, url, form_data, link, html_next_page)
	{

		dy_form.submit();
		end_loading();
		_reset( dy_form );

	}

	var _reset 		= function( form )
	{
		form.find('input.custom_data').remove();
		form.remove();
		// $("body").isLoading("hide");
	}

	var handle_change_reports 	= function()
	{
		$('select#reports')[0].selectize.on('change', function( e )
		{
			var val 	= this.getValue();
			var xhr;
			var data 	= {};

			if( REPORTS && val != '' )
			{
				var option	= this.options[val];

				data 		= generate_selectize_data(option);

				xhr 		= file_js_exists($base_url+'static/js/systems/bootcamp/'+data.link+'.js');

				if( xhr.status !== 200 )
				{
					// alert();
				}
				else
				{
					xhr.promise().done( function() 
					{
						var link_arr 					= data.link.split('/');
						
						if( link_arr[1] !== undefined )
						{
							var per_report_script_obj 	= link_arr[1];
							var js_str 					= per_report_script_obj.charAt(0).toUpperCase() + per_report_script_obj.slice(1);

							var js_obj 					= eval(js_str);
							
							if( js_obj.init_report_format !== undefined )
							{
								js_obj.init_report_format(Fms_reports);
							}
						}
					});
				}

			}

			if( data.link !== undefined && val != '' )
			{
				var url 		= $base_url+data.link+'/process_filters/';
				start_loading();
				$.post( url, data ).promise().done( function( response )
				{	
					response 	= JSON.parse( response )

					if( response.flag )
					{
						$('#report_filter_div').html( response.html );

						selectize_init();

						/**
						 * Here you can put custom javascript after the filters for that report has loaded.
						 * If there is no js file put your custom function inside the switch case
						 * must have the method [report_init] in your js file
						 */
					 	if( xhr === undefined || xhr.status !== 200 )
						{

						}
						else
						{
							var link_arr 				= data.link.split('/');
							var per_report_script_obj 	= link_arr[1];
							var js_str 					= per_report_script_obj.charAt(0).toUpperCase() + per_report_script_obj.slice(1);

							var js_obj 					= eval(js_str);	

							if( js_obj.report_init !== undefined )
							{
								js_obj.report_init();
							}

							if(response.load_js != undefined && response.load_js != undefined )
						 	{
							 	for(var key in response.load_js) {
							 		var js 	= file_js_exists($base_url+'static/js/'+response.load_js[key]+'.js');
							 	}
						 	}

						 	if(response.loaded_init != undefined && response.loaded_init != undefined )
						 	{
						 		setInterval(function(){
						 			for(var key in response.loaded_init) {
							 			eval(response.loaded_init[key]);
								 	}	
						 		}, 10);
							 	
						 	}
						}
					}
					end_loading();
				});
			}
			else
			{
				$('#report_filter_div').html('');
			}
		});
	}

	var initialize = function() 
	{
		if( $('#report_constant').length !== 0 && $('#report_constant').val() != '' )
		{
			REPORTS = JSON.parse( $('#report_constant').val() );
		}

		handle_change_reports();
	}

	var hide_func 	= function( elem, style )
	{
		if( style )
		{
			elem.attr('style', 'display : none !important;');
		}
		else
		{
			elem.hide();
		}
	}

	var show_func 	= function( elem, style )
	{
		if( style )
		{
			elem.removeAttr('style')
		}
		else
		{
			elem.show();
		}
	}

	/**
	 * Handles the click of generate button
	 */
	var generate 	= function()
	{
		var form 	= $('#report_form'),
			$parsley= form.parsley();

		$('#generate_report').on('click', function( e )
		{
			$parsley.validate();

			e.stopImmediatePropagation();

			e.preventDefault();

			if( $parsley.isValid() )
			{
				var link;
				var data 		= form.serializeArray();
				var select 		= $('#reports');

				if( select.val() )
				{
					var option;

					if( select[0].selectize !== undefined )
					{
						option 	= select[0].selectize.options[select.val()];
					}

					if( option )
					{
						var post 		= {},
							i 			= 0,
							len,
							form_data 	= '',
							url 		= $base_url+'Demo_report/generate/'
							that 		= this,
							sel_data 	= option;

						// var type_val 	= sel_data.report_type.toLowerCase();
						var dy_form;

						link 			= sel_data.link;

						if( data.length !== 0 )
						{
							len 		= data.length;
							var arr 	= [];
							var cur_key;

							for( ; i < len; i++ )
							{
								var regex = /[\[]/gi;
								var check = data[i].name.match(regex);

								if( check !== null )
								{
									if( data[i].value != '' )
									{
										arr.push( data[i].value );
									}

									/*if( post[ data[i].name ] !== undefined )
									{
										var old_arr 			= post[ data[i].name ];
										var new_arr 			= old_arr.concat(arr); 
										
										post[ data[i].name ] 	= new_arr;
										
									}
									else
									{
										post[ data[i].name ] 	= arr;
									}*/

									if( post[ data[i].name ] !== undefined )
									{
										if( data[i].value != '' )
										{
											post[ data[i].name ].push( data[i].value );
										}
										
									}
									else
									{
										post[ data[i].name ] 		= [];

										if( data[i].value != '' )
										{
											post[ data[i].name ].push( data[i].value );
										}
									}

									if( data[i+1] !== undefined )
									{
										cur_key 	= data[i+1].name;
									}
									
									if( cur_key != data[i].name )
									{
										arr 	= [];
									}
								}
								else
								{
									post[ data[i].name ]	= data[i].value;
								}
							}
						}
						
						post['link']		= sel_data.link;
						post['report_name']	= sel_data.report_name;

						form_data 		= form.serialize()+'&link='+sel_data.link+'&report_name='+sel_data.report_name;

						// url 			= url+sel_data.report_type.toLowerCase()+'/';
						var sub_url 	= $base_url+sel_data.link+'/generate/';

						$( that ).append('<form id="report_sub_form"></form>');
						$('#report_sub_form').attr('action', url).attr('method', 'post').attr('target', '_blank');

						dy_form 		= $('#report_sub_form');
						
						for( var key in post )
						{ 
							if( post[key] instanceof Array )
							{
								var l 	= post[key].length,
									z 	= 0;
								
								if( l !== 0 )
								{
									for( ; z < l; z++ )
									{
										if( post[key][z] != ""  )
										{
											var input = $("<input name='"+key+"' class='custom_data'>").attr("type", "hidden").val( post[key][z] );
											
											dy_form.append($(input));
										}
									}
								}
							}	
							else
							{
								var input = $("<input name='"+key+"' class='custom_data'>").attr("type", "hidden").val( post[key] );

								dy_form.append($(input));
							}
						}

						generate_post( form_data, dy_form, url, link, sel_data );

					}
				}
				
			}
		});
	}

	var generate_post 	= function( form_data, dy_form, url, link, sel_data )
	{
		// start_loading();
		
		$.when( $.post( $base_url + 'Demo_report/validate/', form_data ) ).then( function( response ) 
		{
			response 	= JSON.parse( response );

			if( response.flag == 1 )
			{
				var xhr 					= file_js_exists($base_url+'static/js/systems/bootcamp/'+sel_data.link+'.js');

				if( xhr.status === 200 )
				{

					var link_arr 				= sel_data.link.split('/');
					var per_report_script_obj 	= link_arr[1];
					var js_str 					= per_report_script_obj.charAt(0).toUpperCase() + per_report_script_obj.slice(1);

					var js_obj 					= eval(js_str);	

					var html_next_page 			= false;

					/**
					* If you want to change your html report to page just add .html_page : true
					* e.g. Report_gmef_score = function()
					* {
					*	return 
					* 		{
					*			html_next_page : true
					* 		}
					* }();
					*/
					if( js_obj !== undefined )
					{
						if( js_obj.html_next_page !== undefined && js_obj.html_next_page === true )
						{
							html_next_page 			= true; 
						}

					}
				}

				dy_form.append('<input type="hidden" name="format_clean" class="custom_data" value="'+response.format+'">');

				REPORT_TYPE[ response.format ]( response, dy_form, url, form_data, link, html_next_page );
			}
			else
			{
				notification_msg('error', response.msg);
				// end_loading();
			}
		});
	}

	return {
		init 	: function()
		{
			initialize();
		},
		generate : function()
		{
			generate();
		}
	}
}();