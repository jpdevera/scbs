var Holidays = function()
{

	var $module 	 = 'file_maintenance';
	var $controller  = 'holidays';

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

	var calendar_js = function()
	{
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
				},
				// selectable: false,
				// selectHelper: true,
				// select: function(start, end) {
				// 	var title = prompt('Event Title:');
				// 	var eventData;
				// 		if (title) {
				// 			eventData = {
				// 				title: title,
				// 				start: start,
				// 				end: end
				// 			};
				// 			$('#calendar').fullCalendar('renderEvent', eventData, true); // stick? = true
				// 		}
				// 		$('#calendar').fullCalendar('unselect');
				// 	},
			editable: false,
			displayEventTime: true,
			eventLimit: true, // allow "more" link when too many events
			events: function(start, end, timezone, callback)
			{
				var self = this;
				var current_date 	= moment( self.currentDate._d );

				var opt         = {
		         	url     : $base_url + $module + "/"+$controller+"/get_calendar_info",
		        	type    : 'POST',
		        	dataType: 'json'
		    	};

		    	jQuery.ajax( opt ).promise().done( function( res ) {

		    		var events      = [];


		    		$.map( res, function( r ) {

		    			var options = {};

		    			if(r.status == 6 || r.status == 7 )
		    			{

			    			if( current_date.isLeapYear() )
		               		{
		               			options.repeat_freq = 366;
		               		}
		               		else
		               		{
		               			options.repeat_freq = 365;
		               		}


						   var cur_year = current_date.format('YYYY');
				           var cur_day = current_date.format('DD');
				           var cur_month = current_date.format('MM');

	           			    start_date_format = moment(r.start).format('YYYY-MM-DD');


		               		var start_date_arr_1 = r.start.split('-');

		               		find_current_date_1 = cur_year + '-' + cur_month +'-' + start_date_arr_1[2];
		               		find_current_date = moment(find_current_date_1).format('YYYY-MM-DD');

	           				var start_d_arr 	= r.start.split('-'),
	               			start_d 		= cur_year+'-'+start_d_arr[1]+'-'+start_d_arr[2];

	               			start_date = moment(start_d);

	               			start_date = start_date.format('YYYY-MM-DD HH:mm:ss');
	               		}
	               		else
	               		{
	               			start_date = r.start;
	               		}


	              		options.title  				= r.title;
	                    options.color  				= r.color;
	                    options.start				= start_date;


	                    events.push(options);

		    		});


		    		callback(events);

		    	});


			},
			eventRender: function(event, element) {

				// $('#calendar').fullCalendar('renderEvent', event, true);
		        element.click(function(e) {



		         /* var modal = $('#attendance-calendar > a[href="#modal_view"]');
		          console.log(modal);
		          modal.attr('onclick', 'modal_edit_attendance_init("", "", "Edit Attendance")');
		          modal.trigger('click');*/
		        });
	      	}
		});

		$("#calendar_view").on('click', function(){
			$('#calendar').fullCalendar('refetchEvents');
			// alert('a');
		});
		

		//Bind tabs
		$('#article_tab').find('.tabs > li > a').click(function(event){
			event.preventDefault();
			var mode = $(this).data('mode');

			if (mode == 'calendar') {
				//re-fetch calendar
				$('#calendar').fullCalendar('refetchEvents');
			} else {
				//load list work sched
				//load_datatable('tbl_events', 'code_library/work_calendar/get_event_list',false,0,0,true);
				load_datatable({
					'path': 'code_libraries/work_calendar/get_event_list',
					'table_id': 'tbl_events',
					'advanced_filter': true,
					'jump_to_page': true
				});

				//load_datatable
			}
			$('.datepicker,.datepicker_start,.datepicker_end,.timepicker').datetimepicker('destroy');
			$('.tabs > li > a').removeClass('active');
			$(this).addClass('active');
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
    	},
    	calendar_js : function() {
			 calendar_js();
		}
	}
}();
