var Notifications 	= function()
{
	var message_text 	= function()
	{
		$('#notifications_table').find('ul.dt-notif').find('li').each( function()
		{
			$(this).find('a').attr('style', 'color : black !important;font-size: 14px !important;');
		});
	}


	return {
		message_text : function()
		{
			message_text();
		}
	}
}();