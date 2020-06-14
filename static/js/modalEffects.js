/**
 * modalEffects.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */

var ModalEffects = (function()
{
	// For Modal Scroll
	var $body 					= $(document.body);
	var scrollbarWidth      	= 0;
	var originalBodyPad 		= null;
	var originalBottomBodyPad 	= null;
	var over 	= 1040;
	
	function init() 
	{
		var overlay = document.querySelector( '.md-overlay' );

		[].slice.call( document.querySelectorAll( '.md-trigger' ) ).forEach( function( el, i ) {

			var clicked_cnt  		= 1;
			var close_clicked_cnt  	= 1;

			if( el.getAttribute( 'data-modal' ) )
			{
				var modal = document.querySelector( '#' + el.getAttribute( 'data-modal' ) ),
				close = modal.querySelector( '.md-close' );
				
				function removeModal( hasPerspective ) {
					classie.remove( modal, 'md-show' );

					if( hasPerspective ) {
						classie.remove( document.documentElement, 'md-perspective' );
					}
				}

				function removeModalHandler() {
					removeModal( classie.has( el, 'md-setperspective' ) ); 
				}

				function closeHandler( ev ) {
					ev.stopImmediatePropagation();
					/*if(close_clicked_cnt != 1)
					{
						return;
					}*/
					// ev.stopPropagation();
					removeModalHandler();

					if($('.md-modal.md-show').length > 0)
			        {
						$('.md-overlay').last().remove();
					}
					// Reset Modal Scroll
					resetScrollbar();
					if( $('.md-modal.md-show').length == 0 )
					{
						$body.removeClass('md-open');
						$body.removeAttr('style');
					}
					else
					{
						over = over-10;
					}
					// End Reset Modal Scroll
				}

				// el.removeEventListener('click',function(){});
				el.addEventListener( 'click', function( ev ) {

					ev.stopImmediatePropagation();
					modal = $(this).parents('body').find('#'+this.getAttribute( 'data-modal' ))[0];
					close = modal.querySelector( '.md-close' );

					close.addEventListener( 'click', closeHandler);

					classie.add( modal, 'md-show' );
			      	if($('.md-modal.md-show').length > 1)
			        {
			           over = over+10;
			           $('body').append('<div class="md-overlay" style="z-index : '+over+'"></div>');
			            
			            var prev_modal      = $('.md-modal.md-show')[ $('.md-modal.md-show').length - 2 ];
			            var prev_modal_zindex       = $(prev_modal).css('z-index');

			           	$('.md-modal.md-show').last().css( 'z-index', parseInt(prev_modal_zindex) + 10);

			        }

					// Set Modal Scroll
					scrollbar();
					$body.addClass('md-open');
					// End Modal Scroll

					overlay.removeEventListener( 'click', removeModalHandler );
					//overlay.addEventListener( 'click', removeModalHandler );

					if( classie.has( el, 'md-setperspective' ) ) {
						setTimeout( function() {
							classie.add( document.documentElement, 'md-perspective' );
						}, 25 );
					}
				});

				// close.removeEventListener('click',function(){});
				close.addEventListener( 'click', closeHandler);
			}
		});
	}

	// For Modal Scroll
	var resetScrollbar = function()
	{
		$body.css('padding-right', originalBodyPad);
		$body.css('padding-bottom', originalBottomBodyPad);
	}

	var measureScrollbar = function()
	{
		var scrollDiv 			= document.createElement('div');
		    scrollDiv.className = 'md-scrollbar-measure';

		$body.append(scrollDiv);

		var scrollbarWidth 		= scrollDiv.offsetWidth - scrollDiv.clientWidth;

		$body[0].removeChild(scrollDiv);
		
		return scrollbarWidth;
	}	

	var scrollbar = function()
	{
		var fullWindowWidth = window.innerWidth;

	    if (!fullWindowWidth) 
	    { 	
	    	// workaround for missing window.innerWidth in IE8
			var documentElementRect = document.documentElement.getBoundingClientRect();
			fullWindowWidth 		= documentElementRect.right - Math.abs(documentElementRect.left);
	    }

	    var bodyIsOverflowing 		= document.body.clientWidth < fullWindowWidth;
	    	scrollbarWidth 			= measureScrollbar();

      	var bodyPad 				= parseInt(($body.css('padding-right') || 0), 10),
      		bodyBottomPad 			= parseInt(($body.css('padding-bottom') || 0), 10);

      		originalBodyPad 		= document.body.style.paddingRight || '';
      		originalBottomBodyPad 	= document.body.style.paddingBottom || '';
		    
	    if (bodyIsOverflowing)
	    {
	    	$body.css('padding-right', bodyPad + scrollbarWidth);
	    	$body.css('padding-bottom', bodyBottomPad + scrollbarWidth);
	    } 
	}

	init();

	return {
		re_init : function() {
			init();
		},
		setScroll : function() {
			scrollbar();
			$body.addClass('md-open');
		},
		resetScroll : function() {
			resetScrollbar();
			if( $('.md-modal.md-show').length == 0 )
			{
				$body.removeClass('md-open');
				$body.removeAttr('style');
			}
		},
		reduceZindex : function()
		{
			over 	= over - 10;
		}
	}
})();