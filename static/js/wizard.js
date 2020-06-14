var Wizard = function()
{
	var $base_url = $("#base_url").val();
	var animating;

	var animate_next = function( next_fs, current_fs )
	{
		next_fs.show(); 
		//hide the current fieldset with style
		current_fs.animate({opacity: 0}, {
			step: function(now, mx) {
				//as the opacity of current_fs reduces to 0 - stored in "now"
				//1. scale current_fs down to 80%
				scale = 1 - (1 - now) * 0.2;
				//2. bring next_fs from the right(50%)
				left = (now * 50)+"%";
				//3. increase opacity of next_fs to 1 as it moves in
				opacity = 1 - now;
				current_fs.css({
			'transform': 'scale('+scale+')',
			'position': 'absolute'
		  });
				next_fs.css({'left': left, 'opacity': opacity});
			}, 
			duration: 800, 
			complete: function(){
				current_fs.hide();
				animating = false;
			}, 
			//this comes from the custom easing plugin
			easing: 'easeInOutBack'
		});
	}

	var init = function()
	{
		//jQuery time
		var current_fs, next_fs, previous_fs; //fieldsets
		var left, opacity, scale; //fieldset properties which we will animate
		// var animating; //flag to prevent quick multi-click glitches

		var form 			= $('.wcontent').parents('form'),
			parsley 		= form.parsley({ excluded: 'input[type=button], input[type=submit], input[type=reset]',
		    inputs: 'input, textarea, select, input[type=hidden]' });

		$(".save-wizard").click(function()
		{
			current_fs 		= $(this).parent();
			next_fs 		= $(this).parent().next();

			var page_class 	= current_fs.attr('data-page');
			
			parsley.validate();

			var self 	= this;
			
			if( parsley.isValid({group : 'fieldset-'+page_class }) )
			{
				var data_action = $(this).attr('data-action');
				
				if( data_action !== undefined && data_action !== null && data_action != '' )
				{
					eval( data_action );
				}
			}
		} );

		$(".next").click(function(){
			
			current_fs 		= $(this).parent();
			next_fs 		= $(this).parent().next();

			var page_class 	= current_fs.attr('data-page');
			
			parsley.validate();

			var self 	= this;
			
			if( parsley.isValid({group : 'fieldset-'+page_class }) )
			{
				if(animating) return false;
				animating = true;

				// console.log(.validate());
				//activate next step on progressbar using the index of next_fs
				$("#progressbar li").eq($(".wcontent").index(next_fs)).addClass("active");
				$("#progressbar li").eq($(".wcontent").index(current_fs)).addClass("done");
				
				//show the next fieldset
				// if( $(this).attr('') )
				var data_action = $(this).attr('data-action');
				
				if( data_action !== undefined && data_action !== null && data_action != '' )
				{
					eval( data_action );
					animating = false;
				}
				else
				{
					animate_next( next_fs, current_fs );
				}
			}
		});

		$(".previous").click(function(){
			if(animating) return false;
			animating = true;
			
			current_fs = $(this).parent();
			previous_fs = $(this).parent().prev();
			
			//de-activate current step on progressbar
			$("#progressbar li").eq($(".wcontent").index(current_fs)).removeClass("active done");
			
			//show the previous fieldset
			previous_fs.show(); 
			//hide the current fieldset with style
			current_fs.animate({opacity: 0}, {
				step: function(now, mx) {
					//as the opacity of current_fs reduces to 0 - stored in "now"
					//1. scale previous_fs from 80% to 100%
					scale = 0.8 + (1 - now) * 0.2;
					//2. take current_fs to the right(50%) - from 0%
					left = ((1-now) * 50)+"%";
					//3. increase opacity of previous_fs to 1 as it moves in
					opacity = 1 - now;
					current_fs.css({'left': left});
					previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
				}, 
				duration: 800, 
				complete: function(){
					current_fs.hide();
					animating = false;
				}, 
				//this comes from the custom easing plugin
				easing: 'easeInOutBack'
			});
		});

		
	}
	return {
		init : function()
		{
			init();
		},
		animate_next : function( next_fs, current_fs )
		{
			animate_next( next_fs, current_fs );
		}
	}

}();