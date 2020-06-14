var Template_common 	= function()
{
	var interval_id  = 0;

	var $noti_circle = $('#noti_red');
	var $noti_count  = $('#notif_cnt');

	var $dropdown    = $('.notif');
	var request_check 	= false;
	var cache_announcements = {};

	$dropdown.on('click', function()
	{
		if(  $(this).hasClass('selected') )
		{
			if( typeof( Socket_notification ) !== 'undefined' )
			{
				Socket_notification.refreshNotiTime();
				Socket_notification.updateNotification();

				interval_id = setInterval(Socket_notification.refreshNotiTime, 3000);
			}

			infinite_scroll();
		}
		else
		{
			clearInterval(interval_id);
			
			$noti_circle.attr('style', 'display : none !important;');
			$noti_count.hide();
		}
	});

	$(document).on('click', function(event)
	{
		if( !$(event.target).is('.has-children a') ) 
		{
			if( $dropdown.hasClass('selected') )
			{
				clearInterval(interval_id);
				
				$noti_circle.attr('style', 'display : none !important;');
				$noti_count.hide();
			}
		}
	} );	

	var infinite_scroll 	= function()
	{
		var api_orig    =  $('.notification-title').next().find('.scroll-pane').data('jsp');

		if( api_orig !== undefined )
		{
			api_orig.destroy();
		}

		var container 	= $('.notification-title').next().find('.scroll-pane').jScrollPane({autoReinitialise: true, contentWidth: '0px'});

		var api = $('.notification-title').next().find('.scroll-pane').data('jsp');  

	    api.reinitialise();  

		yofinity_helper.reset_iterator();

		var options 		= {
			ajaxUrl : $base_url+'Notifications/get_notifications',
		 	buffer 	: $('.notification-title').next().find('.scroll-pane').height(),
		 	debug 	: false,
		 	navSelector: 'a[rel="next"]',
		 	moreButton : $('#more_button'),
		 	context  :  $('.notification-title').next().find('.scroll-pane').find('.collection-notif'),
		 	loading  : function()
			{

			},
			scroll 	: {
		 		plugin 		: 'jScrollPane',
		 		container	: container
		 		// top 		: true
		 	},
		 	type 	: 'post',
		 	success : function( response )
		  	{
		  		if( response )
	  			{
	  				response 	= JSON.parse( response );

	  				if( response.html != '' )
	  				{
	  					$('.notification-title').next().find('.scroll-pane').find('ul').append(response.html);
	  				}

	  				if( response.unread )   
	  				{
	  					var unread 	= parseInt($('#notif_cnt').text());

	  					if( !isNaN( unread ) )
	  					{
	  						$('#notif_cnt').text( unread + response.unread );
	  					}
	  				}
	  			}
		  	}
		};

		var yofinity = $('.notification-title').next().find('.scroll-pane').yofinity( options );
	}

	var modal_sess_expired_log_in 	= function()
	{
		var modal_obj = $('#modal_sess_expired_log_in').modal({
			dismissible: false,
			opacity: .5, // Opacity of modal background
			in_duration: 300, // Transition in duration
			out_duration: 200, // Transition out duration
			ready: function() {
				$("#modal_sess_expired_log_in .modal-content #content").load($base_url+'Unauthorized/session_expired_modal/');
			}, // Callback for Modal open
			complete: function() { 
			
			} // Callback for Modal close
		});

		return modal_obj;
	}


	var check_session 	= function()
	{
		if( request_check == true )
		{
			return;	
		}
		
		$.ajax({
			url:$base_url+'Unauthorized/check_session',
			beforeSend : function()
			{
				request_check = true;
			},
			success: function(session_expired)
			{
				var check_json 	= is_json( session_expired )

				request_check 	= false;

				if( check_json )
				{
					var json_obj = JSON.parse( session_expired );
					
					if( !json_obj.check )
					{
						if( !$('#modal_sess_expired_log_in').hasClass('open') 
							&& !$('#modal_warning_expired_log_in').hasClass('open')
						)
						{
							modal_sess_expired_log_in().trigger('openModal');
						}
					}
					else
					{
						modal_sess_expired_log_in().trigger('closeModal');
					}
				}
				else
				{
					if( !$('#modal_sess_expired_log_in').hasClass('open') 
						&& !$('#modal_warning_expired_log_in').hasClass('open')
					)
					{
						modal_sess_expired_log_in().trigger('openModal');
					}
				}
			}
		});
	}

	var sse_annoucements 		= function()
	{
		/*if( typeof(Socket_notification) == 'undefined' )
		{*/
			if (!!window.EventSource) 
			{
			 	var source = new EventSource($base_url+'Stream/get_announcements/');

				source.addEventListener('message', function(e) 
				{
					// console.log(e);
					/*var data = JSON.parse(e.data);
					
					if( data )
					{
						if( data.check )
						{
							$.post( $base_url+"auth/sign_out/" + data.user_id, function(result)
							{
								if(result.flag == 1)
								{
									window.location = $base_url;
								}
							},'json');
						}
					}*/

				}, false);

				source.addEventListener('announcement', function(e) 
				{
					var data = JSON.parse(e.data);
					
					var announcements 	= data.announcements,
						len 			= announcements.length,
						i 				= 0;

					if( len != 0 )
					{
						for( ;i < len; i++ )
						{
							var ann 	= announcements[i];

							var html 	= '';

							var html 	= `
								<div id="announcement_growl_"`+ann.announcement_id+` style="width:400px !important;">
									<div><i class="material-icons blue-text">announcement</i></div>
									<div style="width:400px !important;">`+ann.description_decode+`</div>
								</div>
							`;


							if( cache_announcements[ann.announcement_id] == undefined )
							{
								
								var upd_data = {};

								upd_data['user_id']				= ann.user_id;
								upd_data['announcement_id'] 	= ann.announcement_id;
									
								Materialize.toast(html, 10000, 'notification', function()
								{
									
								});

								$.post( $base_url+'Stream/update_user_announcement', upd_data ).promise().done(function( response )
								{
									console.log(response);
								});
							}

							cache_announcements[ann.announcement_id] = true;
						}
					}

				});

				source.addEventListener('error', function(e) 
				{
					if( e.readyState == EventSource.CLOSED ) 
					{
						console.log('event source closed')
					}

				}, false);
			}
			else
			{
				// console.log('bb');
			}
		// }
	}

	return {
		check_session : function()
		{
			check_session();
		},
		sse_annoucements : function()
		{
			sse_annoucements();
		}
	}
}();

$(function()
{
	Template_common.sse_annoucements();

	if( typeof( check_session ) === 'undefined' || check_session )
	{
		setInterval(Template_common.check_session, 50000);
	}
});