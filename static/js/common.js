var $base_url 				= $("#base_url").val();
	$loader 				= '&nbsp;&nbsp;<img src="'+ $base_url +'static/images/ajax-loader-bar-black.gif"/>',
	PATH_USER_UPLOADS 		= $('#path_user_uploads').val(),
	PATH_IMAGES 	  		= $('#path_images').val(),
	PATH_SETTINGS_UPLOADS	= $('#path_settings_upload').val(),
	PATH_FILE_UPLOADS 		= $('#path_file_uploads').val();

//  used whether if the system will check if the session has suddenly expire or not
//  will usually be set to false if the template has no authentication like portals.
var check_session 			= true;

function button_loader(id, active){
	var loading_bar = (active === 1) ? $loader : "",
		btn = $("#" + id),
		active_btn_label = btn.data("btn-action"),
		default_btn_label = btn.html();
	
	if(active){
		btn.html(active_btn_label + loading_bar); //updating
		btn.attr('disabled','disabled');
		btn.val(default_btn_label); // update
	} else {
		btn.removeAttr('disabled');	
		btn.html(btn.val()); // update
		btn.val(default_btn_label.split("&nbsp;")[0]);
	}
}

function content_form(controller, module, id){
	var module = module || "";
		id = id || "";
		path = module + "/" + controller + "/" + id;
		
	window.location.href = $base_url + path;
}

function content_delete(alert_text, param_1, param_2, action_msg, no_extra_msg){
	var param_2 = param_2 || "";

	var real_action_msg = action_msg || 'delete';
		
	$('#confirm_modal').confirmModal({
		topOffset : 0,
		onOkBut : function() {
			deleteObj.removeData({ param_1 : param_1, param_2 : param_2 });
		},
		onCancelBut : function() {},
		onLoad : function() {
			$('.confirmModal_content h4').html('Are you sure you want to '+real_action_msg+' this ' + alert_text + '?');	

			if( !no_extra_msg )
			{
				$('.confirmModal_content p').html('This action will permanently delete this record from the database and cannot be undone.');
			}
		},
		onClose : function() {}
	});
}

function alert_msg(type, msg){
	Materialize.toast(msg, 5000, type);
}

function notification_msg(type, msg, old, extra_opt){
/*	$(".notify." + type + " p").html(msg);
	$(".notify." + type).notifyModal({
		duration : -1
	});*/

	if( old )
	{
		$(".notify." + type + " p").html(msg);

		if( type == "success" )
		{
			$(".notify." + type).notifyModal({
				duration : 2500
			});
		}
		else
		{
			$(".notify." + type).notifyModal({
				duration : -1
			});
		}
	}
	else
	{
		var default_setting = $.extend({
			size: 'normal',
	        title: type.toUpperCase(),
	        msg: msg,
	        delay: 15000,
	        position : $.CONSTANTS.notification_position
		}, extra_opt);
		
		Lobibox.notify(type, default_setting);
	}
}

function table_export(id, type, exclude, escape){
	var escape = escape || 'false';
		ignore_col = exclude || "";
		arr = "";
		
	if(ignore_col != ""){
		var ignore_col = ignore_col.split(",");
			arr = ignore_col.map(Number);
	}
		
		
	$('#' + id).tableExport({
		type:type,
		escape:escape,
		ignoreColumn: arr
	});
}

function modal_init(data_id){
	var data_id = data_id || '',
		jscroll;
	
	jscroll = modalObj.checkIfScroll();
	
	if(jscroll){
	  modalObj.loadViewJscroll({ id : data_id });
	}else{
	  modalObj.loadView({ id : data_id });
	}
	
	return false;
}

function set_active_tab(module){
	var active_tab = window.location.hash.replace('#',''),
		controller = active_tab.replace('tab_','');
	
	load_index(active_tab, controller, module);
}

function load_index(id, controller, module, func_callback)
{
	var module = (module != undefined) ? module + "/" : '';
	var path = module + controller;

	$(".tab-content").not("#"+id).html("");
	$("#"+id).load($base_url + path).fadeIn("slow").show();

	if( func_callback !== undefined && typeof( func_callback ) === 'string' )
	{
	    eval( func_callback );
	}

	window.location.hash = id;
}

function load_index_post( id, controller, module, obj, func_callback, modal )
{
	var path = module + "/" + controller,
		ul = $('.tabs'),
		link = ul.find('li').find('a[href="#'+id+'"]'),
		post_data,
		data = {};

	if( link.length === 0 )
	{
	    link         = $(obj);
	}

	post_data         = link.attr('data-post');

	if( post_data != '' )
	{
	    data         = JSON.parse( post_data );
	}

	data['tab_id']     = id;

	if( link.attr('data-form') )
	{
	    var form_id     = JSON.parse(link.attr('data-form'));

	    if(form_id instanceof Array)
	    {
	        var f_i        = 0,
	            f_len    = form_id.length;

	        for( ; f_i < f_len; f_i++ )
	        {
	            var f_form    = form_id[f_i];

	            if( $(f_form).length !== 0 )
	            {
	                var f_form_id    = $(f_form).attr('id');

	                var f_form_data = $(f_form).serializeArray();
	                

	                var f_f_i     = 0,
	                    f_f_len = f_form_data.length;

	                data[f_form_id] = {};
	                var arr         = [];
	                var cur_key;

	                for( ; f_f_i < f_f_len; f_f_i++ )
	                {
	                     var regex = /[\[]/gi;
	                    var check = f_form_data[f_f_i].name.match(regex);

	                    if( check !== null )
	                    {
	                        var real_name     = f_form_data[f_f_i].name.replace('[]', '');

	                        if( f_form_data[f_f_i].value != '' )
	                        {
	                            arr.push( f_form_data[f_f_i].value );
	                        }

	                        if( data[f_form_id][ real_name ] !== undefined )
	                        {
	                            if( f_form_data[f_f_i].value != '' )
	                            {
	                                data[f_form_id][ real_name ].push( f_form_data[f_f_i].value );
	                            }

	                        }
	                        else
	                        {
	                            data[f_form_id][ real_name ]         = [];

	                            if( f_form_data[f_f_i].value != '' )
	                            {
	                                data[f_form_id][ real_name ].push( f_form_data[f_f_i].value );
	                            }
	                        }

	                        if( f_form_data[f_f_i+1] !== undefined )
	                        {
	                            cur_key     = real_name;
	                        }

	                        if( cur_key != real_name )
	                        {
	                            arr     = [];
	                        }
	                    }
	                    else
	                    {
	                        data[f_form_id][f_form_data[f_f_i].name] = f_form_data[f_f_i].value;
	                    }
	                }
	            }

	        }
	    }
	    else
	    {
	        if( $(form_id).length !== 0 )
	        {
	            var form_data = $(form_id).serializeArray();

	            var i     = 0,
	                len = form_data.length;

	            var arr         = [];
	            var cur_key;

	            for( ; i < len; i++ )
	            {
	                var regex = /[\[]/gi;
	                var check = form_data[i].name.match(regex);

	                if( check !== null )
	                {
	                    if( form_data[i].value != '' )
	                    {
	                        arr.push( form_data[i].value );
	                    }

	                    if( data[ form_data[i].name ] !== undefined )
	                    {
	                        if( form_data[i].value != '' )
	                        {
	                            data[ form_data[i].name ].push( form_data[i].value );
	                        }

	                    }
	                    else
	                    {
	                        data[ form_data[i].name ]         = [];

	                        if( form_data[i].value != '' )
	                        {
	                            data[ form_data[i].name ].push( form_data[i].value );
	                        }
	                    }

	                    if( form_data[i+1] !== undefined )
	                    {
	                        cur_key     = form_data[i+1].name;
	                    }

	                    if( cur_key != form_data[i].name )
	                    {
	                        arr     = [];
	                    }
	                }
	                else
	                {
	                    data[form_data[i].name] = form_data[i].value;
	                }
	            }
	        }
	    }
	}

	$(".tab-content").not("#"+id).html("");

	$.post( $base_url + path, data ).promise().done( function ( response ) {

	    $("#"+id).html(response).fadeIn("slow").show();

	    if( modal )
	    {
	        /*$("#" + modal).find('div.modal-content #content').jScrollPane({autoReinitialise: true, contentWidth: '0px'});

	         var api = $("#" + modal).find('div.modal-content #content').data('jsp');  

	        api.reinitialise();  */
	    }

	    if( func_callback !== undefined && typeof( func_callback ) === 'string' )
	    {
	        eval( func_callback );
	    }

	} );

	window.location.hash = id;
}

function search_wrapper(id, input, items, highlight){
	console.log("hehe");
	var highlight = highlight || "";
	var highlight = (highlight === "") ? true : false;
	$(id).lookingfor({
		input: $(input),
		items: items,
		highlight: highlight
	});
}

function sort(id, order_by, wrapper, action, input_id, item){
	var id = id.attr('id'),
		up_ico = 'flaticon-up151',
		down_ico = 'flaticon-down95';
	
	// RESET ACTIVE BUTTONS
	$('.sort-btn').not('#' + id).removeClass('active').find("i").switchClass(up_ico, down_ico, 1000, "easeInOutQuad" );
	
    $('#' + id).toggleClass('active');

    if(($('#' + id).hasClass('active'))){
		var order = 'ASC',
			class_from = down_ico,
			class_to = up_ico;
    } else {
		var order = 'DESC',
			class_from = up_ico;
			class_to = down_ico;
    }
    
	filter(order_by, order, wrapper, action, input_id, item);
	$('#' + id).find("i").switchClass(class_from, class_to, 1000, "easeInOutQuad" );
}

function filter(filter_1, filter_2, wrapper, action, input_id, item){
	
	var data = {filter_1: filter_1, filter_2: filter_2};

	$(wrapper).isLoading();
	
	$.post($base_url + action, data, function(result){
	  $(wrapper).isLoading("hide").html(result);
	  search_wrapper(wrapper, input_id, item, 1);
	},'json');
}

function generate_report(controller, extension, filter){
	var filter = filter || "",
		url = $base_url + controller + "/" + extension + "/" + filter;
	
	window.open(url, '_blank');
}

/* Replaces all broken images with "avatar" class with the default no avatar image */
function avatar_fix(){
	$('img.avatar').error(function(){
		$(this).attr('src', $base_url + 'static/images/avatar/no_avatar.jpg');
	});
}

function help_text(form_id){
	$.each($('.help', '#' + form_id),function(){
		var data = $(this).data(),
			text = data.helpText;
		
		$(this).append('<i class="help-tooltip material-icons titleModal" data-placement="right" title="'+text+'">help</i>');
	});
}

function do_process(role_code, proceeding_step, action, is_return, org_code){
	var org_code = org_code || "";
	$('#confirm_modal').confirmModal({
		topOffset : 0,
		onOkBut : function() {
			workflowObj.workflowData({ role_code : role_code, proceeding_step : proceeding_step, is_return : is_return, org_code : org_code });
		},
		onCancelBut : function() {},
		onLoad : function() {
			$('.confirmModal_content h4').html(action);	
			$('.confirmModal_content p').html('This action will submit the form and proceed to the specified stage of process. Are you sure?');
		},
		onClose : function() {}
	});
}

function ajax_loader(wrapper){
	$(wrapper).isLoading({
		position: 'inside',
		tpl: '<div class="loader"></div>'
		
	});
}
/*--------------------------------------------------------------
  TABS by Doc
  - Loads initial tab: <li class="tab"><a class="active"></li>
  	  If there's no "active" class, the default is the first tab. 
  - Loads the same active tab when refreshed.
--------------------------------------------------------------*/

function load_initial_tab()
{
	var tabs = $(".tabs-wrapper"),
	cnt  = 0,
	len;

	if( tabs.length !== 0 )
	{
		len = tabs.length;

		for( ; cnt < len; cnt++ )
		{
			var initial = $( tabs[cnt] ).find( 'li a.active' ),
			id;

			if( initial.length !== 0 && initial.length === 1 )
			{
				initial.click();
			}
			else 
			{
				initial.each( function() {
					id  		= $( this ).attr('href');

					var div 	= $( this ).parents('div.tabs-wrapper').parent();

					if( div.find('div'+id).not(':hidden').length !== 0 )
					{
						$(this).click();
					}

				} )
			}
		}
	}
}

function toggle(id, content_id, custom_func){
	if($("#" + id).is(':checked')){
		$('#' + content_id).fadeIn('slow').show();
	} else {
		$('#' + content_id).fadeOut('slow').hide();
	}

	if( custom_func !== undefined )
	{
		if( typeof( custom_func ) === 'string' )
		{
			eval( custom_func );
		}
		else
		{
			custom_func.apply(null, [id, content_id]);
		}
	}
}

function password_constraints( constraints )
{

	Array.prototype.frequencies = function() {
	    var l = this.length, result = {all:[]};
	    while (l--){
	       result[this[l]] = result[this[l]] ? ++result[this[l]] : 1;
	    }
	    // all pairs (label, frequencies) to an array of arrays(2)
	    for (var l in result){
	       if (result.hasOwnProperty(l) && l !== 'all'){
	          result.all.push([ l,result[l] ]);
	       }
	    }
	    return result;
	};
	
	window.Parsley.addValidator('pass', 
	    function (input, data_val) {
	 	var input_copy 	= input;
	    var input_count = input.length;
	    var lower_count = (input_copy.match(/[a-z]/g) || []).length;;
	    var upper_count = input_copy.replace(/[^A-Z]/g, "").length;
	    var digit_count = input_copy.replace(/[^0-9]/g, "").length;
	    var letter_count = input_copy.replace(/[^A-Za-z]/g, "").length;
	    var spec_count  = input_copy.match(/[!@#$%^&*+]/g);
	    
	    spec_count = ( spec_count == null ) ? 0 : spec_count.length;
	    
	    var rep_pass 			= input_copy.split('').frequencies();
	    var check_repeat_pass 	= false;
	    var check_pass_same  	= false;
	    var pass_same_user 		= parseInt( constraints.pass_same );
	    var user_repeat_pass 	= parseInt( constraints.repeat_pass );
	    var letter_length 		= constraints.letter_length || 0;
	    var spec_length 		= constraints.spec_length || 0;
	    
	    letter_length = parseInt( letter_length );
	    spec_length = parseInt( spec_length );
	    
	    var $username 			= jQuery( data_val );
	    
	    // console.log(spec_length);
	   /* if( Object.keys(rep_pass).length !== 0 )
	    {
	    	var rep_arr 	= [];
	    	
	    	for( var keys in rep_pass )
	    	{
	    		
	    		if( rep_pass[ keys ] instanceof Array === false && rep_pass[ keys ] != '' )
	    		{
	    			rep_arr.push( rep_pass[ keys ] );
	    		}
	    	}
	    	
			var rep_max = Math.max.apply( null, rep_arr );
			
			check_repeat_pass = ( user_repeat_pass !== 0 ) ? rep_max > 1 : false;
	    }*/
	    
	    var rep_pass 		= input_copy.match(/(.)\1+/g);

	    var check_rep 		= false;

	    if( rep_pass instanceof Array )
	    {
		    var i 				= 0,
		    	len 			= rep_pass.length;

		    for( ; i < len; i ++)
		    {
		    	if( rep_pass[i].length >= 3 )
		    	{
		    		check_rep 	= true;
		    	}
		    }
		}

	    check_repeat_pass 	= ( user_repeat_pass !== 0 ) ? ( check_rep ) : false;

	    var pass_length		= parseInt( constraints.pass_length );
	    var lower_length	= parseInt( constraints.lower_length );
	    var upper_length	= parseInt( constraints.upper_length );
	    var digit_length 	= parseInt( constraints.digit_length );

	   	if( pass_same_user != 0 )	
	   	{
	   		if( $username.length != 0 && $username.val() != '' )
	   		{
	   			check_pass_same = input == $username.val();
	   		}
	   	}

	    /*if( pass_length > 0 )
	    {*/
	    	if( input_count < pass_length || upper_count < upper_length || lower_count < lower_length || letter_count < letter_length || spec_count < spec_length || digit_count < digit_length || check_repeat_pass || check_pass_same )
			{
				return false;
			}	 

			return true;   
	    // }

	}).addMessage('en', 'pass', constraints.pass_err);
}

function username_constraints( constraints )
{

	/*Array.prototype.frequencies_user = function() {
	    var l = this.length, result = {all:[]};
	    while (l--){
	       result[this[l]] = result[this[l]] ? ++result[this[l]] : 1;
	    }
	    // all pairs (label, frequencies) to an array of arrays(2)
	    for (var l in result){
	       if (result.hasOwnProperty(l) && l !== 'all'){
	          result.all.push([ l,result[l] ]);
	       }
	    }
	    return result;
	};*/
	
	window.Parsley.addValidator('username', 
	    function (input, data_val) {
	 	var input_copy = input;
	    var input_count = input.length;
	    var digit_count = input_copy.replace(/[^0-9]/g, "").length;
	    var letter_count = input_copy.replace(/[^A-Za-z]/g, "").length;
	    
	  
	    var user_has_cons 		= parseInt( constraints.user_has_cons );
	    var user_min_length		= parseInt( constraints.user_min_length );
	    var user_max_length		= parseInt( constraints.user_max_length );
	    var digit_length 		= parseInt( constraints.user_digit_length );
		var letter_length 		= constraints.user_lett_length || 0;

		letter_length 			= parseInt( letter_length );

	    var check_user_min 		= false;
	    var check_user_max 		= false;
	    var check_user_dig 		= false;
	    var check_user_lett 	= false;

	   	if( input_count != 0 && user_has_cons != 0 )
	   	{
	   		if( user_min_length != 0 )
	   		{
	   			check_user_min 	= input_count < user_min_length;
	   		}

	   		if( user_max_length != 0 )
	   		{
	   			check_user_max 	= input_count > user_max_length;
	   		}

	   		if( digit_length != 0 )
	   		{
	   			check_user_dig 	= digit_count < digit_length;
	   		}

	   		if( letter_length != 0 )
	   		{
	   			check_user_lett 	= letter_count < letter_length;
	   		}
	   		
	   		if( check_user_min || check_user_max || check_user_dig || check_user_lett )
			{
				return false;
			}	 

	   	}

	    /*if( pass_length > 0 )
	    {*/
	    	
			return true;   
	    // }

	}).addMessage('en', 'username', constraints.user_err);
}

function refresh_datatable( datatable_options, btn_id )
{
	var button_id 	= btn_id || "#refresh_btn";
	var options 	= JSON.parse(datatable_options);

	$(button_id).on("click", function()
	{
		load_datatable(options);
	} );
}

function force_logout(user_id)
{
	$('#confirm_modal').confirmModal({
		topOffset : 0,
		onOkBut : function() {
			 $.post($base_url + "user_management/users/force_sign_out", {user_id : user_id}, function(result){
				if(result.flag == 0){
					notification_msg("error", result.msg);
				}
				else
				{
					notification_msg("success", result.msg);
					$("#active_users_list").html(result.list);
				}	
			  }, 'json');
		},
		onCancelBut : function() {},
		onLoad : function() {
			$('.confirmModal_content h4').html('Are you sure you want to log out this user account?');	
		},
		onClose : function() {}
	});
}

function get_action(elem)
{
	var id= elem.id;
	document.getElementById("select").addEventListener("click", $("#" + id).val(), false);
	
}

function load_content(load_id, path, trigger_id){
	var trigger_id = trigger_id || "";
	
	$("#"+ load_id).load($base_url + path);
	
	if(trigger_id != "")
	{
		$("#" + trigger_id).addClass("active");
		$(".links").not("#" + trigger_id).removeClass("active");
	}
}

function start_loading()
{
	/*$( "body" ).isLoading({
		text:       "<div class='loader'></div>",
        position:   "inside"
  	});*/

  	$.blockUI({ 
        message: '<img src="'+$base_url+'static/images/loading.gif">',
        css: { zIndex: '9999'},
		overlayCSS:  { zIndex: '9999'}
    });
}

function end_loading()
{
	// $("body").isLoading("hide");
	$.unblockUI();
}

function is_json( str )
{
	var check 			= true;

    try 
    {
        // var my_json 	= JSON.stringify(str);
        var json 		= JSON.parse(str);
        
        if(typeof(str) == 'string')
        {
            /*if( str.length == 0)
            {
                check 	= false;
            }

           	var match 	= str.match(/^{/);
           		
           	if( match === null || match.length == 0 )
           	{
           		check 	= false;
           	}*/

        }
        else if( typeof( str ) !== 'object' )
        {
        	check 		= false;
        }
    }
    catch(e)
    {
        check 			= false;
    }

    return check;
}

function toggle_fields(module_code)
{
	$.post($base_url + "common/system_modules/toggle_fields", {module_code : module_code}, function(result)
	{
        for (var i=0; i<result.length; i++) 
        {
            var field_id = result[i];
            $("#" + field_id + "_wrapper").hide();
        }
	}, 'json');
}


function dynamic_parsley( fields, field_label, btn_obj )
{
	var len 		= fields.length,
		cnt 		= 0,
		trig_modal 	= [],
		req_str 	= '',
		check 		= true;

	for( ; cnt < len; cnt++ )
	{
		var pars 	= $( fields[ cnt ] ).parsley();

		if( pars !== undefined ) 
		{

			if( $( fields[ cnt ] ).attr('data-parsley-required') === undefined )
			{
				$( fields[ cnt ] ).attr('data-parsley-required', 'true');
			}

			pars.validate();

			if( pars.isValid() )
			{
				trig_modal.push( true );
			}
			else
			{
				trig_modal.push( false );

				req_str 	+= '<b>'+field_label[ cnt ]+'</b>'+' is required. <br/>';
			}
		}
	}
	
	if( trig_modal.indexOf( false ) === -1 )
	{
		$( btn_obj ).click();
	}
	else
	{

		var br_len 		= '<br/>';

		notification_msg( "error", req_str.substring(0, req_str.length - br_len.length) );

		check 			= false;
	}

	return check;
}

function reload_datatable( selector )
{
	var selector;

	if( selector !== undefined )
	{
		if( typeof( selector ) === 'string' )
		{
			selector 	= $( selector );
		}
		else if( selector instanceof jQuery )
		{
			selector 	= selector
		}
	}
	else
	{
		// selector 		= $('.color-picker');
	}

	if( selector !== undefined )
	{
		selector.DataTable().ajax.reload();
	}
}

function output_image(file, path)
{
	var ajax 		= $.ajax( {
 		url 	: $base_url+'Media/output_image',
 		data  	: {
 			file 	: file,
 			path  	: path
 		},
 		success : function( response )
 		{
			return response;
 		},
 		async 	: false,
 		error 	: function() {}
 	} );

 	if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
 	{
 		var img 	= ajax.responseText;
 	}
 	else
 	{
 		var img 	= $base_url + path + file;
 	}
	
 	return ajax.responseText;
}

function load_editor( id, in_modal )
{
	if( typeof(CKEDITOR) !== 'undefined' && CKEDITOR !== undefined )
	{
		if( $( '#'+id ) !== null && $( '#'+id ).length !== 0 )
		{
			setTimeout(function()
			{
				 try {
				     CKEDITOR.instances[id].destroy(true);
				 } catch (e) { }

				CKEDITOR.replace(id, {height: '300px'});

				if( in_modal )
				{
					CKEDITOR.on('dialogDefinition', function (e) 
					{
					 	
					 	var dialogDefinition 	= e.data.definition;

					 	var dialogName 			= e.data.name;
					 	var editor 				= e.editor;

					    dialogDefinition.onShow = function () 
					    {
					    	var $body 	= $('body');
				    	 	$body.removeClass('md-open');
	                        $body.removeAttr('style');
					        ModalEffects.resetScroll();
					    };

					    dialogDefinition.onHide = function () 
					    {
					    	ModalEffects.setScroll();
						};

						var $body 	= $('body');
			    	 	$body.removeClass('md-open');
		                $body.removeAttr('style');

					});
				}
			}, 1000);
		}
	}
}

function update_editor()
{
	if( typeof(CKEDITOR) !== 'undefined' && CKEDITOR !== undefined )
	{
		if( Object.keys(CKEDITOR.instances).length !== 0 )
		{
			for ( instance in CKEDITOR.instances ) 
			{ 
	       		CKEDITOR.instances[instance].updateElement();
	       	}
	   	}
   	}
}

function generate_selectize_data( obj )
{
	var data 	= {};
	
	for( var key in obj )
	{
		if( key !== 'data' )
		{
			if( key === '$order' )
			{
				data['order'] = obj[key];
			}
			else
			{
				data[key]	= obj[key];
			}
		}
	}

	return data;
}

function file_js_exists(url)
{
	return $.ajax({
	  url 		: url,
	  dataType	: "script",
	  async 	: false
	});
}

function get_data_attributes( node )
{
 	var d 		= {}, 
    re_dataAttr = /^data\-(.+)$/;

    $.each(node.attributes, function(index, attr) 
    {
        if (re_dataAttr.test(attr.nodeName)) 
        {
            var key = attr.nodeName.match(re_dataAttr)[1];
            d[key] = attr.nodeValue;
        }
    });

    return d;
}

function refresh_new_datatable_params(table_id, btn_id, new_params)
{
	var button_id 	= btn_id || "#refresh_btn";
	
	$(button_id).on("click", function()
	{
		refresh_ajax_datatable(table_id, new_params);		
	} );
}

function refresh_ajax_datatable(table_id, new_params)
{
	if( datatable_objects[table_id] !== undefined )
	{
	 	if( new_params )
        {
            new_datatable_params(datatable_objects[table_id], result.datatable_new_params);
        }

        datatable_objects[table_id].ajax.reload();
	}
}

function new_datatable_params(dt_obj, new_params)
{
	dt_obj.on('preXhr.dt', function ( e, settings, data ) {
		e.stopImmediatePropagation();
		var real_params 	= {};

		if( typeof( new_params ) === 'string' )
		{
			real_params 	= JSON.parse( new_params );
		}
		else
		{
			real_params 	= new_params;
		}

		if( new_params.action )
		{
			if( new_params.action == "filter_cancel" )
			{
				real_params 	= {};
			}
		}
		
		$.extend( data, real_params );
    } )
}

function modal_webcam(upload_id, path, default_check)
{
	var real_path 	= ( path !== undefined ) ? path : '';

	if( typeof( $.fn.modal ) !== 'undefined' )
	{
		var data 	= {
			upload_id 	: upload_id,
			path 		: real_path,
			default_check : default_check || false
		};

		
		var mod = $('#modal_webcam').modal({
			dismissible: false,
			opacity: .5, // Opacity of modal background
			in_duration: 300, // Transition in duration
			out_duration: 200, // Transition out duration
			ready: function() {

				$("#modal_webcam").css('z-index', '9999');

				$.post($base_url+'Crop/modal_webcam', data).promise().done(function( response ){
					$("#modal_webcam .modal-content #content").html(response);
				});
			},
			complete: function() { 
				Webcam.reset();
			} // Callback for Modal close
		});

		mod.trigger('openModal');
	}
}