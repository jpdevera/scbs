/*! *//*!
 * yofinity.js v1.0.3 - "Sogeking no shima deeeeeee - One Piece"
 * ~~~~~~~~~~~~~~~~~~
 *
 * Example of use HTML:
 * <a href="http://example.com/page/2" title="Next posts" rel="next">Loading...</a>
 *
 * Example of use JS:
 * $('body').yofinity({
 *     buffer: 1000,
 *     ajaxUrl: 'http://www.example.com/my/ajax/call',
 *     iterator: 1,
 *     navSelector: 'a[rel="next"]',
 *     params: {
 *         param1: 'val1',
 *     },
 *     success: function ($link, response){
 *         $link.before(response);
 *         $link.attr('href', '/page/2');
 *     },
 *     error: function ($link){
 *         $link.remove();
 *     }
 * });
 *
 * ~~~~~~~~~~~~~~~~~~
 * Copyright 2015 Achraf Chouk <achrafchouk@gmail.com>
 */
var iterator   	= 0;
var yofinity_helper    = function()
{
    return {
        reset_iterator  : function()
        {
            iterator    = 0;
        }
    }
}();

(function ($){
    "use strict";
    var Yofinity = function ($el, options){
        //vars
        var _yofinity = this;
        _yofinity.$el = $el;
        _yofinity.options = $.extend({}, options);
        _yofinity.$context = $(_yofinity.options.context);
        _yofinity.$scroll  = _yofinity.options.scroll;
        _yofinity.$more    = _yofinity.options.moreButton;

        //initialize
        _yofinity.initialize();
    };

    Yofinity.prototype.options = {};
    Yofinity.prototype.$el = null;
    Yofinity.prototype.$context = null;

    Yofinity.prototype.initialize = function (){
        var _yofinity = this,
            _time = null,
            _request = function (){
                //check timer
                if (_time) {
                    clearTimeout(_time);
                    _time = null;
                }

                //set timer
                _time = setTimeout(function (){
                    _yofinity.check();
                }, 250);
            };


        //check heights and make a first ajax call if needed
        if (_yofinity.$context.get(0).scrollHeight > _yofinity.$context.height()) {
            _request();
        }

        if( _yofinity.$more )
        {
        	_request();
        }

        //bind scroll event
        _yofinity.$context.on('scroll', function (e){
        	e.stopImmediatePropagation();
            _request();
        });
    };

    Yofinity.prototype.check = function (){
        var _yofinity = this;
        //get all children
        var $navs = _yofinity.$el.find(_yofinity.options.navSelector),
            _href = _yofinity.options.ajaxUrl,
            _callback = function (href, dist, $link){
                //check buffer
                if( _yofinity.$more )
		        {
		        	_yofinity.$more.on('click', function(e)
		        	{
		        		e.stopImmediatePropagation();

		        		if( typeof( dist ) === 'object' )
		                {
		                	if( dist.plugin )
		                	{
                                if( typeof( dist.plugin ) === 'string' )
                                {
    		                		switch( dist.plugin )
    		                		{
    		                			case 'jScrollPane':

    		                				var api = dist.container.data('jsp');
    		                				
    		                				api.scrollTo(0, api.getContentPositionY() - 10 );

    		                			break;
    		                		}
                                }
                                else if( typeof( dist.plugin ) === 'function' )
                                {
                                    dist.plugin(_yofinity.$more, _yofinity);
                                }
		                	}
		                }

		        		_yofinity.options.iterator++;
		        		
		        	});
		        	
		        }	

                if( typeof( dist ) === 'object' )
                {
                    if( dist.pluginCallback !== undefined )
                    {
                        console.log($link);
                        dist.pluginCallback(_yofinity, href, $link);
                    }
                    else
                    {
                    	if( dist.plugin )
                    	{
                    		switch( dist.plugin )
                    		{
                    			case 'jScrollPane':

    								dist.container.bind('jsp-user-scroll-y', function(event, scrollPositionY, isAtTop, isAtBottom) 
    								{
    									event.stopImmediatePropagation();
                                        event.stopPropagation();

                                        if( dist.top )
                                        {
                                            if( isAtTop )
                                            {
                                                if( _yofinity.options.loaderBottom )
                                                {
                                                    _yofinity.options.loaderBottom();
                                                }
                                                
                                                _yofinity.next(href, $link);
                                            }
                                            else
                                            {
                                                 _yofinity._log('must be at top before loading.');
                                            }
                                        }
                                        else
                                        {

        									if( isAtBottom )
        									{
        										if( _yofinity.options.loaderBottom )
        										{
        											_yofinity.options.loaderBottom();
        										}

        										_yofinity.next(href, $link);
        									}
        									else
        									{
        										 _yofinity._log('must be at bottom before loading.');
        									}
                                        }
    								});

                    			break;
                    		}
                    	}
                    }
                }
                else
                {
	                if (dist > _yofinity.options.buffer) {
	                    _yofinity._log((dist - _yofinity.options.buffer) + 'px before loading.');
	                }
	                else {
	                   _yofinity.next(href, $link);
	                }
	           	}
            };

        //check children
        if (!$navs.length && '' === _href) {
            _yofinity._log('End.');
        }
        else {
            //update distances

            var check;

            if( _yofinity.$scroll )
            {
            	check 	 = _yofinity.$scroll;
            }
            else
            {
            	var _wbot = _yofinity.$context.scrollTop() + _yofinity.$context.height();

            	check 	= -_wbot;
            }

            //ajax URL only and first
            if ('' !== _href) {
                _callback(_href, check, false);
            }
            //link navSelector only
            else if ($navs.length) {
                //iterate on all
                $.each($navs, function (){
                    var $nav = $(this);
                    _callback($nav.attr('href'), $nav.offset().top - _wbot, $nav);
                });
            }
        }
    };

    Yofinity.prototype.next = function (_href, $link){
        var _yofinity 	= this;
        // iterator++;
        //check href
        if ('#' === _href || '' === _href) {
            return;
        }

        //check status
        if (_yofinity.$el.attr('data-loading')) {
            return;
        }

        //get ajax call

        var params  = _yofinity.options.params;

        if( _yofinity.options.process_params !== undefined && typeof(_yofinity.options.process_params) === 'function' )
        {
            var proc_params     = _yofinity.options.process_params( _yofinity.options.params );

            params  = $.extend(_yofinity.options.params, proc_params);
        }

        $.ajax({
            type: _yofinity.options.type.toUpperCase(),
            data: $.extend(params, {
                page: 0
            }),
            url: _href,
            beforeSend: function (){
                iterator++;
                this.data += '&page=' +iterator;
                _yofinity._loading();
            },
            error: function (){
                _yofinity._error($link);
            },
            success: function (response){
                _yofinity._success(response, _href, $link);
            }
        });
    };

    Yofinity.prototype._loading = function (){
        var _yofinity = this;

        //change status
        _yofinity.$el.attr('data-loading', true);
        _yofinity._log('Loading next page.');

        //call loading method
        if (typeof _yofinity.options.loading === 'function') {
        	if( typeof( $link ) !== 'undefined' )
        	{
            	_yofinity.options.loading($link);
            }
            else
            {
            	_yofinity.options.loading();	
            }
        }
    };

    Yofinity.prototype._error = function ($link){
        var _yofinity = this;

        //change status
        _yofinity.$el.removeAttr('data-loading');
        _yofinity._log('Error while loading next page.');

        //call error method
        if (typeof _yofinity.options.error === 'function') {
            _yofinity.options.error(_yofinity.options, $link);
        }
    };

    Yofinity.prototype._success = function (response, href, $link){
        var _yofinity = this;

        //change status
        _yofinity.$el.removeAttr('data-loading');
        _yofinity._log('Next page loaded.');

        //call success method
        if (typeof _yofinity.options.success === 'function') {
            _yofinity.options.success(response, href, $link);
        }
    };

    Yofinity.prototype._log = function (msg){
        var _yofinity = this;

        //check debug
        if (_yofinity.options.debug && typeof console !== 'undefined' && console !== null) {
            console.log('Yofinity ~ ' + msg);
        }
    };

    var methods = {
        init: function (options){

            if (!this.length) {
                return false;
            }

            var settings = {
                ajaxUrl: '',
                buffer: 1000,
                context: window,
                debug: false,
                error: null,
                iterator: 1,
                loading: null,
                scroll : undefined,
                navSelector: 'a[rel="next"]',
                type: 'get',
                moreButton : undefined,
                params: {},
                loaderBottom : undefined,
                success: null
            };

            return this.each(function (){
                if (options) {
                    $.extend(settings, options);
                }

                new Yofinity($(this), settings);
            });
        },
        update: function (){},
        destroy: function (){}
    };

    $.fn.yofinity = function (method){
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        }
        else {
            $.error('Method ' + method + ' does not exist on jQuery.Yofinity');
        }
    };
})(window.jQuery);
