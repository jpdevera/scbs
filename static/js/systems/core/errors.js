var Errors 	= function()
{

	var hide_btn 	= function()
	{
		var btns 	= $(".modal-footer").find("button:visible,a:visible").not(":contains('Cancel')");

		btns.hide();
	}

	return {
		hide_btn : function()
		{
			hide_btn();
		}
	}

}();