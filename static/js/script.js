var $base_url   = $("#base_url").val();
var cloned      = {};

// JSCROLLPANE
var settings    = {autoReinitialise: true, contentWidth: '0px'};
var $body       = $('body');

var handleModal = function(options){

    var vars = {
        controller: '',
        method: '',
        modal_id: '',
        module: '',
        height: '',
        complete_callback: '',
        ajax: false,
        
        modal_type: '',
        dismissible: false,
        draggable : true,
        resizable : true
    };

    this.construct = function(options){
        $.extend(vars , options);
    }

    this.loadModal = function(data){
        method = (vars.method === '')? "modal" : vars.method;
        path = vars.controller + "/" + method;
        path = (data.id === '')? path : path + "/" + data.id;
        mod = (vars.module === '')? '' : vars.module + "/";
        target_id = "#" + vars.modal_id;
        header_title = data.title || '';
        
        var modalObj = $(target_id).modal({
            dismissible: vars.dismissible, // Modal can be dismissed by clicking outside of the modal
            opacity: .5, // Opacity of modal background
            in_duration: 300, // Transition in duration
            out_duration: 200, // Transition out duration
            ready: function() {
                $("#" + vars.modal_id + " .modal-content #content").load($base_url + mod + path, function()
                {
                    var api_orig    = $("#" + vars.modal_id).find('div.modal-content.scroll-pane').data('jsp');

                    if( api_orig !== undefined )
                    {
                        api_orig.destroy();
                    }

                    $("#" + vars.modal_id).find('div.modal-content.scroll-pane').jScrollPane(settings).bind('mousewheel',
                        function(e)
                        {
                            e.preventDefault();
                        }
                    );

                    api = $("#" + vars.modal_id).find('div.modal-content.scroll-pane').data('jsp');  

                    if( api )
                    {
                    
                        api.reinitialise();
                    }
                });

                if(header_title != "")
                {
                    $("#" + vars.modal_id + " .modal-header > span").text(header_title);
                }
                else
                {

                    var orig_title_cache  = $("#" + vars.modal_id + " .modal-header > span").text();

                    $("#" + vars.modal_id + " .modal-header > span").text(orig_title_cache.replace(/(View|Create|Edit)/ig,""));

                    var orig_title  = $("#" + vars.modal_id + " .modal-header > span").text();
                    

                    if( data.id != '' )
                    {
                        var total_btn = $('#'+vars.modal_id).find('div.modal-footer').find('button[id*="submit_"]').length;

                        if( $('#'+vars.modal_id).find('div.modal-footer').find('button[id*="submit_"]:hidden').length == total_btn)
                        {
                            if( orig_title.indexOf('View') <= 0 )
                            {
                                $("#" + vars.modal_id + " .modal-header > span").text('View '+orig_title);
                            }
                        }
                        else
                        {
                            if( orig_title.indexOf('Edit') <= 0 )
                            {   
                                $("#" + vars.modal_id + " .modal-header > span").text('Edit '+orig_title);
                            }
                        }
                        
                    }
                    else
                    {
                        if( orig_title.indexOf('Create') <= 0 )
                        {
                            $("#" + vars.modal_id + " .modal-header > span").text('Create '+orig_title);
                        }
                    }
                }

                $body.removeAttr('style');
                $(".modal-footer").find("button,a").not(":contains('Cancel')").show();

                if( vars.succCallback )
                {
                    eval(vars.succCallback);
                }

            }, // Callback for Modal open
            complete: function() {
                var orig_title  = $("#" + vars.modal_id + " .modal-header > span").text();

                $("#" + vars.modal_id + " .modal-header > span").text(orig_title.replace(/(View|Create|Edit|Add)/ig,""));

                eval(vars.complete_callback);
            } // Callback for Modal close
        });
        
        if(vars.modal_type == "open")
        {
            modalObj.trigger("openModal");
        }

        if( vars.draggable )
        {
            modalObj.draggable({
                handle: "div.modal-header",
                containment: "body", 
                scroll: false
            });

            modalObj.find('div.modal-header').css('cursor', 'move');
        }

        if( vars.resizable )
        {
            modalObj.addClass('ui-widget-content');

            modalObj.resizable({
                containment : 'body',
                minHeight: 50,
                minWidth: 50,
                handles : 'se,e,s',
                resize : function(event, ui)
                {
                    var style   = ui.helper.attr('style');

                    var maxHeight   = $('body').height();
                    var maxWidth    = $('body').width();
                    
                    ui.helper.attr('style', style+'height:'+ui.size.height+'px !important; width:'+ui.size.width+'px !important; max-height:'+maxHeight+'px !important;'+'max-width:'+maxWidth+'px !important;');
                }
            });

            modalObj.css('overflow', 'hidden');
        }
            
    }

    this.loadModalPost = function(data){
        method = (vars.method === '')? "modal" : vars.method;
        path = vars.controller + "/" + method;
        path = (data.id === '')? path : path + "/" + data.id;
        mod = (vars.module === '')? '' : vars.module + "/";
        target_id = "#" + vars.modal_id;
        header_title = data.title || '';

        var extra_data = data.extra_data || {};
        
        var modalObj = $(target_id).modal({
            dismissible: vars.dismissible, // Modal can be dismissed by clicking outside of the modal
            opacity: .5, // Opacity of modal background
            in_duration: 300, // Transition in duration
            out_duration: 200, // Transition out duration
            ready: function() {

                var response;

                var ajax            = $.ajax( {
                    url     : $base_url + mod + path,
                    data    : extra_data,
                    success : function( response )
                    {
                       return response;
                    },
                    dataType: 'html',
                    method  : 'post',
                    async   : false,
                    error   : function() {}
                } );

                response    = ajax.responseText

                $("#" + vars.modal_id + " .modal-content #content").html(response);

                var api_orig    = $("#" + vars.modal_id).find('div.modal-content.scroll-pane').data('jsp');

                if( api_orig !== undefined )
                {
                    api_orig.destroy();
                }
                    
                $("#" + vars.modal_id).find('div.modal-content.scroll-pane').jScrollPane(settings).bind('mousewheel',
                    function(e)
                    {
                        e.preventDefault();
                    }
                );

                api = $("#" + vars.modal_id).find('div.modal-content.scroll-pane').data('jsp');  

                if( api )
                {
                
                    api.reinitialise();  
                }

                if( $( '.min_max' ).length !== 0 )
                {
                    var orig_style      = $(target_id).attr('style');
                    var old_style;

                    old_style           = orig_style.substring(orig_style.indexOf('width: 100% !important;height: 100% !important;max-height: 100% !important;')+1);

                    $( '.min_max' ).on('click', function(e)
                    {
                        e.stopImmediatePropagation();

                        var min_max     = $(this).attr('data-min_max');
                        
                        if( min_max == 'max' )
                        {
                            $(target_id).find('#modal_max').attr('style','display: none !important');
                            $(target_id).find('#modal_min').removeAttr('style');

                            var new_style = old_style+'width: 100% !important;height: 100% !important;max-height: 100% !important;';
                            
                            $(target_id).slideUp('slow', function()
                            {
                                $(target_id).attr('style', new_style);
                            });
                        }
                        else
                        {

                            $(target_id).find('#modal_min').attr('style','display: none !important');
                            $(target_id).find('#modal_max').removeAttr('style');
                            $(target_id).removeClass('full');

                            $(target_id).slideDown('slow', function()
                            {
                              $(target_id).attr('style', old_style);  
                            })
                        }

                    });
                }

                if(header_title != "")
                {
                    $("#" + vars.modal_id + " .modal-header > span").text(header_title);
                }
                else
                {
                    var orig_title_cache  = $("#" + vars.modal_id + " .modal-header > span").text();

                    $("#" + vars.modal_id + " .modal-header > span").text(orig_title_cache.replace(/(View|Create|Edit)/ig,""));

                    var orig_title  = $("#" + vars.modal_id + " .modal-header > span").text();
                    
                    var check       = false;

                    if( Object.keys(extra_data).length !== 0 )
                    {
                        for( var keys in extra_data )
                        {
                            if( extra_data[keys] == "" )
                            {
                                check = true;

                                break;
                            }
                        }
                    }

                    if( data.id != '' || !check )
                    {
                        var total_btn = $('#'+vars.modal_id).find('div.modal-footer').find('button[id*="submit_"]').length;
                        
                        if( $('#'+vars.modal_id).find('div.modal-footer').find('button[id*="submit_"]:hidden').length == total_btn)
                        {
                            if( orig_title.indexOf('View') <= 0 )
                            {
                                $("#" + vars.modal_id + " .modal-header > span").text('View '+orig_title);
                            }
                        }
                        else
                        {
                            if( orig_title.indexOf('Edit') <= 0 )
                            {   
                                $("#" + vars.modal_id + " .modal-header > span").text('Edit '+orig_title);
                            }
                        }
                        
                    }
                    else
                    {
                        if( orig_title.indexOf('Create') <= 0 )
                        {
                            $("#" + vars.modal_id + " .modal-header > span").text('Create '+orig_title);
                        }
                    }
                }

                if( vars.succCallback )
                {
                    eval(vars.succCallback);
                }

                // api.reinitialise();
                // $body.removeAttr('style');
                // $(".modal-footer").find("button,a").not(":contains('Cancel')").show();
            }, // Callback for Modal open
            complete: function() {
                var orig_title  = $("#" + vars.modal_id + " .modal-header > span").text();

                $("#" + vars.modal_id + " .modal-header > span").text(orig_title.replace(/(View|Create|Edit|Add)/ig,""));
                eval(vars.complete_callback);
            } // Callback for Modal close
        });
        
        if(vars.modal_type == "open")
        {
            modalObj.trigger("openModal");
        }

        if( vars.draggable )
        {
            modalObj.draggable({
                handle: "div.modal-header",
                containment: "body", 
                scroll: true
            });

            modalObj.find('div.modal-header').css('cursor', 'move');
        }

        if( vars.resizable )
        {
            modalObj.addClass('ui-widget-content');
            
            modalObj.resizable({
                containment : 'body',
                minHeight: 50,
                minWidth: 50,
                handles : 'se,e,s',
                resize : function(event, ui)
                {
                    var style       = ui.helper.attr('style');

                    var maxWidth    = $('body').width();
                    var maxHeight   = $('body').height();
                    
                    ui.helper.attr('style', style+'height:'+ui.size.height+'px !important; width:'+ui.size.width+'px !important; max-height:'+maxHeight+'px !important;'+'max-width:'+maxWidth+'px !important;');
                    
                }
            });

            modalObj.css('overflow', 'hidden');
        }
    }
    
    this.closeModal = function(data){
        $("#" + vars.modal_id).removeClass("md-show");
    }

    this.checkIfScroll = function()
    {
        return ( vars.height !== '' );
    }

    this.construct(options);
}

var handleData = function(options){

    var vars = {
        controller: '',
        method: '',
        module: ''
    } 

    this.construct = function(options){
        $.extend(vars , options);
    }
    
    this.updateData = function(data){
        $.post($base_url + vars.module + "/" + vars.controller + "/" + vars.method, data, function(result){
            var type = (result.flag == 1) ? "success" : "error";
            notification_msg(type, result.msg);
        }, 'json');
    }

    this.removeData = function(data, succ_callback, self){
        start_loading();
        $.post($base_url + vars.module + "/" + vars.controller + "/" + vars.method, data, function(result){
            
            if(result.status == "success"){
                var reload = result.reload || '';
                var extra_reload = result.extra_reload || '';
                var datatable_options;
                var extra_datatable_options;
                
                switch(reload)
                {
                    case 'datatable2':
                       $('#'+result.table_id).DataTable().ajax.reload();
                    break;
                    case 'datatable':
                        
                        if( result.datatable_options !== undefined )
                        {
                            var datatable_options  = result.datatable_options;
                                
                            load_datatable(datatable_options);
                        }

                        if( result.datatable_id !== undefined )
                        {
                            var datatable_new_params;

                            if( result.datatable_new_params )
                            {
                                datatable_new_params = result.datatable_new_params;
                            }

                            refresh_ajax_datatable( result.datatable_id, datatable_new_params );
                        }
                    break;
                    case 'list':
                        $("#" + result.wrapper).isLoading();
                        
                        $.post(result.path, function(e){
                          $("#" + result.wrapper).isLoading("hide").html(e);
                        },'json');
                        
                    break;
                    case 'list_2':
                        $("#" + result.wrapper).isLoading();
                        
                        $.post($base_url + result.path, function(e){
                          $("#" + result.wrapper).isLoading("hide").html(e);
                        });
                        
                    break;
                    case 'dynamic_table':
                        var data            = result.data || {},
                            response_type   = result.response_type || 'html',
                            wrapper         = result.wrapper,
                            functions       = result.functions || [],
                            i = 0,
                            len;
                        $.post( $base_url + result.path, data, function( response ) {
                            $( wrapper ).html( response );
                            
                            if( functions.length !== 0 )
                            {
                                if( functions instanceof Array )
                                {

                                    len     = functions.length;

                                    for( ; i < len; i++ )
                                    {
                                        eval( functions[ i ] );
                                    }

                                }
                                else
                                {
                                    eval( functions );
                                }
                            }

                        }, response_type ); 
                    break;
                    case 'function':
                        if( result.function )
                        {
                            eval(result.function);
                        }
                        
                    break;
                    default:
                        location.reload(true); 
                }

                switch(extra_reload)
                {
                    case 'datatable':
                       
                        if( result.datatable_options )
                        {
                             extra_datatable_options  = result.datatable_options;
                        }

                        if( result.extra_datatable_options )
                        {
                             extra_datatable_options  = result.extra_datatable_options;
                        }
                        
                        load_datatable(extra_datatable_options);
                    break;

                     case 'function':
                        if( result.extra_function )
                        {
                            eval(result.extra_function);
                        }
                        
                    break;
                }

                if( succ_callback != '' )
                {
                    eval(succ_callback);
                }
            }
            end_loading();
            if(typeof(result.no_msg) == 'undefined')
            {
              notification_msg(result.status, result.msg);
            }

        }, 'json');
    }
    
    this.workflowData = function(data){
        var id = data.role_code.toLowerCase();
            link_id = id + "_workflow";
            icon_id = id + "_icon";
            choices_id = id + "_choices";
            
        $.post($base_url + vars.module + "/" + vars.controller + "/" + vars.method, data, function(result){
            if(result.flag == "success"){
                if(data.is_return == 1){
                    $("#" + icon_id).removeClass("flaticon-minus99 amber darken-1");
                    $("#" + icon_id).addClass("flaticon-left193 red");
                } else {
                    $("#" + icon_id).removeClass("flaticon-minus99 amber darken-1");
                    $("#" + icon_id).addClass("flaticon-checkmark21");
                }   
                
                $("#" + link_id).prop("onclick", null);
                $("#" + icon_id).off();
                
                if($("#" + choices_id).length)
                        $("#" + choices_id).removeClass("fixed-action-btn");
            }   
            notification_msg(result.flag, result.msg);
        }, 'json');
    }
    
    this.loadData = function(data){
        var cy = data.cy || $("#c_year").val();
        $("#" + data.id).isLoading();
        $.post($base_url + vars.module + "/" + vars.controller + "/" + vars.method, {office : data.office, cy : cy, parent_id : data.parent_id}, function(result){
            $("#" + data.id).isLoading("hide").html(result);
        });
    }

    this.construct(options);

}

// Always center modal when opening
$.fn.center = function() {
    
    $(".md-content").removeAttr("style");
    
    this.css({
        'position': 'fixed',
        'left': '50%',
        'top': '40%',
        'right': '50%',
        'bottom': '-10%'
    });
    
    return this;
}

/**
 * Number.prototype.format(n, x)
 * 
 * @param integer n: length of decimal
 * @param integer x: length of sections
 */
Number.prototype.format = function(n, x) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
};

var dateFormat = function () {
    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        var dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        var _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d:    d,
                dd:   pad(d),
                ddd:  dF.i18n.dayNames[D],
                dddd: dF.i18n.dayNames[D + 7],
                m:    m + 1,
                mm:   pad(m + 1),
                mmm:  dF.i18n.monthNames[m],
                mmmm: dF.i18n.monthNames[m + 12],
                yy:   String(y).slice(2),
                yyyy: y,
                h:    H % 12 || 12,
                hh:   pad(H % 12 || 12),
                H:    H,
                HH:   pad(H),
                M:    M,
                MM:   pad(M),
                s:    s,
                ss:   pad(s),
                l:    pad(L, 3),
                L:    pad(L > 99 ? Math.round(L / 10) : L),
                t:    H < 12 ? "a"  : "p",
                tt:   H < 12 ? "am" : "pm",
                T:    H < 12 ? "A"  : "P",
                TT:   H < 12 ? "AM" : "PM",
                Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default":      "ddd mmm dd yyyy HH:MM:ss",
    shortDate:      "m/d/yy",
    mediumDate:     "mmm d, yyyy",
    longDate:       "mmmm d, yyyy",
    fullDate:       "dddd, mmmm d, yyyy",
    shortTime:      "h:MM TT",
    mediumTime:     "h:MM:ss TT",
    longTime:       "h:MM:ss TT Z",
    isoDate:        "yyyy-mm-dd",
    isoTime:        "HH:MM:ss",
    isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
    return dateFormat(this, mask, utc);
};

function set_system($system)
{
    $.get($base_url + "common/systems/set_system/" + $system, function(result){
        window.location.href = $base_url + result.link;
    }, 'json');
}
