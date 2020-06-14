window.ParsleyConfig = {
		errorsContainer: function ( parsleyField ) 
		{
			if( parsleyField.$element !== undefined )
			{
				if( parsleyField.$element.is('select') )
				{
					if( parsleyField.$element.hasClass('selectize') 
						|| 
						( 
							parsleyField.$element.next().length !== 0 &&
							parsleyField.$element.next().hasClass('selectize-control')
						)
						&& parsleyField.$element.closest('div').length !== 0
					)
					{

						return parsleyField.$element.closest('div');
					}
				}
			}
		},
    };