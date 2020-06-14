	window.Parsley
    .options.requiredMessage  = "this field is required";

	window.Parsley
    .addValidator('date', function (value, requirement) {
        var timestamp = Date.parse(value);

        return isNaN(timestamp) ? false : true;
    }, 32)
    .addMessage('en', 'date', 'This value should be a valid date.');

    window.Parsley.addValidator('unique', function (value, requirement) 
	{
   	 	var matches = 0;
   	 	
		$(requirement).each(function(i, val) 
		{
			var split_check = $(this).val().split('|');
			var value_split = value.split('|');

			if( split_check[1] !== undefined && value_split[1] !== undefined )
			{
				if (split_check[1] == value_split[1]) 
				{
					matches++;
				}
			}
			else
			{
				if ($(this).val() == value) 
				{
					matches++;
				}
			}
		});

		if( matches > 1 )
		{
			return false;	
		} 

		return true;
	} )
    .addMessage('en', 'unique', 'This value must be unique');