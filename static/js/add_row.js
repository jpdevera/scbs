Array.prototype.inArray = function(valeur) {
	for (var i in this) { 

		if (this[i] === valeur) return true; 
	}

	return false;
}

var add_rows 			= function()

{
	var args_key 		= []
		,msg_bag 		= ''
		,settings 		= {}
		,btn_keys 		= []
		,apnd_arr 		= []
		,evt_arr 		= []
		,rem_rule_arr 	= [];

	// CONSTANTS 

 	var CLICK 			= 'click'
 		,CHANGE 		= 'change';

	var __construct 	= function(args)

	{

		args_key 			= [ 'btn_id', 'tbl_id', 'row_index', 'tbl_length_zero', 'append_type'
								,'elem_to_mod', 'each_elem_mod', 'after_copy_row', 'after_remove_row'
								,'tr_find', 'rem_rule', 'scroll', 'event_type', 'cache_index', 'before_copy_row'
								,'row_index_func', 'not_table', 'parent_elem', 'in_modal'
						  	  ];

		btn_keys 			= ['btn_id', 'tbl_id'];

		apnd_arr 			= ['append', 'prepend', 'appendto', 'prependto', 'after'];

		rem_rule_arr 		= ['rem_elem', 'rem_find'];

		evt_arr 			= [ 'click', 'change' ];

		var settings 		= {};

		settings.args 		= args;
		settings.types 		= typeof(args);
		settings.btn_id 	= args.btn_id;
		settings.tbl_id 	= args.tbl_id;
		settings.row_index	= args.row_index;
		settings.tbl_zero 	= args.tbl_length_zero;
		settings.apnd_type 	= args.append_type;
		settings.ele_mod 	= args.elem_to_mod;
		settings.each_ele 	= args.each_elem_mod;
		settings.aft_row 	= args.after_copy_row;
		settings.aft_rem 	= args.after_remove_row;
		settings.tr_find 	= args.tr_find;
		settings.rem_rule 	= args.rem_rule;
		settings.scrl 		= args.scroll;
		settings.evt_type 	= args.event_type;
		settings.cache_index= args.cache_index;
		settings.bfore_copy = args.before_copy_row;
		settings.r_ind_func = args.row_index_func;
		settings.not_table 	= args.not_table;
		settings.parent_elem= args.parent_elem;
		settings.in_modal 	= args.in_modal;

		__validate(settings);

		return settings;

	}

	var add_rows  		= function ( args )
	{

		main_function( args );
	}

	var main_function 	= function(args) 

	{

		cons_args 		= __construct(args);

		_add(cons_args);

	}

	var _add 			= function(args)

	{

		var btn 		= (args.btn_id.btn_id !== undefined) ? _conv_data(args.btn_id.btn_id) : _conv_data(args.btn_id)
			,btn_obj 	= typeof(args.btn_id)
			,tbl_de
			,parent_ch 	= (args.btn_id.btn_id !== undefined) ? btn.parents('table').length : 0
			,row_index 	= _row_ind(args)
			,evt 		= args.evt_type || CLICK
			,row_inp
			,cache_index= ( args.cache_index !== undefined ) ? args.cache_index : false
			,tbl 		= _conv_data(args.tbl_id)
			,tr_find 	= (args.tr_find !== undefined) ? args.tr_find : ':eq(0)'
			,tbl_copy 	= tbl.find('tbody tr'+tr_find)
			,tbl_id 	= tbl.attr('id')
			,tbl_zero 	= (args.tbl_zero !== undefined) ? args.tbl_zero : undefined
			,not_table  = args.not_table || false,
			in_modal 	= args.in_modal || false;

		tbl_copy 		= ( not_table ) ? tbl : tbl_copy;

		args.not_table 	= not_table;

		if(args.row_index !== undefined && typeof(args.row_index) === 'object' ) {

			row_inp 	= _conv_data(args.row_index);

			row_inp.val(row_index);

		}
		else
		{
			if( cache_index )
			{
				var latest_row = _conv_data(args.tbl_id).find( 'tbody tr:last' );
				if( latest_row.attr( 'id' ) !== undefined )
				{
					row_index  = latest_row.attr( 'id' ).split( '_' ).pop();	
				}
				
			}
		}


		if(btn_obj.toLowerCase() === 'object' && parent_ch > 0) {

			tbl_de 		= _conv_data(args.btn_id.tbl_id);

			tbl_de.off( evt );

			tbl_de.on( evt, args.btn_id.btn_id, function(e){

				var self = this;

				row_index++;

				tbl_copy 	= ( not_table ) ? tbl : tbl.find('tbody tr'+tr_find);

				if( args.bfore_copy !== undefined )
				{
					if(  args.bfore_copy( row_index, self, tbl, tbl_copy ) === false )
					{
						return;
					}
				}

				_copy(args, row_index, tbl, tr_find, tbl_copy, tbl_id, tbl_zero, self);

			});

		} else {

			btn.off( evt );

			btn.on( evt, function(e){

				if( args.r_ind_func !== undefined )
				{
					row_index 	= args.r_ind_func( row_index );
				}
				else
				{
					row_index++;
				}

				tbl_copy 	= ( not_table ) ? tbl : tbl.find('tbody tr'+tr_find);

				var self = this;

				if( args.bfore_copy !== undefined )
				{
					if(  args.bfore_copy( row_index, self, tbl, tbl_copy ) === false )
					{
						return;
					}
				}
				
				_copy(args, row_index, tbl, tr_find, tbl_copy, tbl_id, tbl_zero, self);

			});	

		}

	}

	var _copy 			= function(args, row_index, tbl, tr_find, tbl_copy, tbl_id, tbl_zero, self)

	{
		
		if(tbl_copy.length != 0) {

			_apnd_type(tbl_copy, tbl_id, row_index, tbl, args, self);

		} else {

			if(tbl_zero !== undefined) {

				tbl_zero();

			} else {

				// alert();

			}

		}

		_each_ele_mod(args, row_index, tbl, self);

	}

	var _apnd_type 		= function (tbl_copy, tbl_id, row_index, tbl, args, self)

	{

		var container 	= ( args.not_table ) ? tbl_copy : tbl.find('tbody');
		
		if(args.apnd_type !== undefined) {

			switch(args.apnd_type.toLowerCase()) {

				case 'append':

					tbl_copy.before(tbl_copy).clone().attr('id', tbl_id+'_row_'+row_index)
						.addClass('cloned')
						.removeAttr('style').append(container);

					_scroll(args.scrl, tbl_id, row_index, args.apnd_type, self);

				break;

				case 'prepend':

					tbl_copy.before(tbl_copy).clone().attr('id', tbl_id+'_row_'+row_index)
						.addClass('cloned')
						.removeAttr('style').prepend(container);

					_scroll(args.scrl, tbl_id, row_index, args.apnd_type, self);

				break;

				case 'appendto':

					tbl_copy.before(tbl_copy).clone().attr('id', tbl_id+'_row_'+row_index)
						.addClass('cloned')
						.removeAttr('style').appendTo(container);

					_scroll(args.scrl, tbl_id, row_index, args.apnd_type, self);

				break;

				case 'prependto':

					tbl_copy.before(tbl_copy).clone().attr('id', tbl_id+'_row_'+row_index)
						.addClass('cloned')
						.removeAttr('style').prependTo(container);

					_scroll(args.scrl, tbl_id, row_index, args.apnd_type, self);

				break;

				case 'after':

					if( args.not_table )
					{
						var container_after 	= ( $( '.'+tbl_id).length > 1 ) ? $( '.'+tbl_id ).last() : container;

						if( args.parent_elem !== undefined )
						{
							container_after 	= ( args.parent_elem.find( '.'+tbl_id ).length > 1 ) ? args.parent_elem.find( '.'+tbl_id ).last() : args.parent_elem.find( '.'+tbl_id );
						}

						if( args.in_modal )
						{
							var in_modal_container 	= container.parents('.modal.open');

							container_after  	= ( in_modal_container.find( '.'+tbl_id).length > 1 ) ? in_modal_container.find('.'+tbl_id).last() : in_modal_container.find(container);
						}
						else
						{
							// container_after 	= ( $( '.'+tbl_id).length > 1 ) ? $( '.'+tbl_id ).last() : container;
						}

						container_after.after( tbl_copy.clone().attr('id', tbl_id+'_row_'+row_index)
							.addClass('cloned')
							.removeAttr('style') );
						
					}
					else 
					{
						if( $( self ).parents( 'table' ).length !== 0 )
						{
							$( self ).parents( 'tr' ).after( tbl_copy.clone().attr('id', tbl_id+'_row_'+row_index)
								.addClass('cloned')
								.removeAttr('style') );
						}
						else
						{
							tbl.find( 'tbody' ).append( tbl_copy.clone().attr('id', tbl_id+'_row_'+row_index)
								.addClass('cloned')
								.removeAttr('style') );
						}
					}
					
					_scroll(args.scrl, tbl_id, row_index, 'append', self);

				break;

				default:

					tbl_copy.before(tbl_copy).clone().attr('id', tbl_id+'_row_'+row_index)
						.addClass('cloned')
						.removeAttr('style').appendTo(container);

					_scroll(args.scrl, tbl_id, row_index, args.apnd_type, self);

				break;

			}

		} else {

			tbl_copy.before(tbl_copy).clone().attr('id', tbl_id+'_row_'+row_index)
				.addClass('cloned')
				.removeAttr('style').appendTo(container);

			_scroll(args.scrl, tbl_id, row_index, args.apnd_type, self);

		}

	}

	var _scroll 		= function(scrl, tbl_id, row_index, apnd_type, btn)

	{

		var scrl_check 	= (scrl !== undefined && typeof(scrl) === 'function' ) ? 1 : 0
			,scr_false 	= (scrl !== undefined && typeof(scrl) === 'boolean' && scrl == 0) ? 1 : 0
			,scr_true 	= (scrl !== undefined && typeof(scrl) === 'boolean' && scrl == 1) ? 1 : 0;

		if(apnd_type !== undefined) {

			switch(apnd_type.toLowerCase()) {

				case 'append':
				case 'appendto':

					if(!scr_false) {

						if(scrl_check) {

							scrl(tbl_id, row_index, apnd_type, btn);

						} else {

							if(scr_true) {
								
								if($('.md-modal:visible').length > 0) $( '.modal_scroll').find('.jspContainer').animate( { scrollTop 	: $( '#'+tbl_id+'_row_'+row_index ).offset().top + row_index }, 'slow' );

								else $(".scrollable").animate({scrollTop:$('#'+tbl_id+'_row_'+row_index).offset().top + row_index}, "slow");

							} 

						}

					}

				break;

				case 'prepend':
				case 'prependto':

					if(!scr_false) {

						if(scrl_check) {

							scrl(tbl_id, row_index, apnd_type, btn);

						} else {

							if(scr_true) {

								if($('.md-modal:visible').length > 0) $( '.modal_scroll').find('.jspContainer').animate( { scrollTop 	: $( '#'+tbl_id+'_row_'+row_index ).offset().top + row_index }, 'slow' );
								
								else $(".scrollable").animate({scrollTop:$('#'+tbl_id+'_row_'+row_index).offset().top - row_index}, "slow");

							}

						}

					}

				break;

				default:

					if(!scr_false) {

						if(scrl_check) {

							scrl(tbl_id, row_index, apnd_type, btn);

						} else {

							if(scr_true) {

								if($('.md-modal:visible').length > 0) $( '.modal_scroll').find('.jspContainer').animate( { scrollTop 	: $( '#'+tbl_id+'_row_'+row_index ).offset().top + row_index }, 'slow' );

								else $(".scrollable").animate({scrollTop:$('#'+tbl_id+'_row_'+row_index).offset().top + row_index}, "slow");

							}

						}

					}

				break;

			}

		} else {

			if(!scr_false) {

				if(scrl_check) {

					scrl(tbl_id, row_index, apnd_type, btn);

				} else {

					if(scr_true) {
						
						if($('.md-modal:visible').length > 0) $( '.modal_scroll').find('.jspContainer').animate( { scrollTop 	: $( '#'+tbl_id+'_row_'+row_index ).offset().top + row_index }, 'slow' );

						else $(".scrollable").animate({scrollTop:$('#'+tbl_id+'_row_'+row_index).offset().top + row_index}, "slow"); 

					}

				}

			}

		}

	}

	var _each_ele_mod 	= function(args, row_index, tbl, self)

	{

		var tbl_id 		= tbl.attr('id')
			,ele_mod
			,ele_str 	= ''
			,each_ele_id;

		if(args.ele_mod !== undefined) {

			ele_mod 	= args.ele_mod;

			for(var i = 0, len = ele_mod.length; i < len; i++) {

				ele_str += '#'+tbl_id+'_row_'+row_index+' '+ele_mod[i]+', ';

			}


			ele_str 	= ele_str.substring(0, ele_str.length - 2);
			
			_each_func(ele_str, tbl_id, row_index, args, self);

		} else {


			ele_str 	= '#'+tbl_id+'_row_'+row_index+' input';

			_each_func(ele_str, tbl_id, row_index, args, self);


		}


	}

	var _each_func 		= function(elem_str, tbl_id, row_index, args, par_btn, main_btn)

	{

		var ele_mod
			,each_ele_id
			,each_ele   = args.each_ele
			,self
			,aft_row 	= (args.aft_row !== undefined) ? args.aft_row : undefined;

		_for_remove(tbl_id, row_index, args);	

		var selector;

		if( args.parent_elem !== undefined )
		{
			selector = args.parent_elem.find(elem_str);
		}
		else
		{
			selector = $(elem_str);
		}

		selector.each(function(){

			each_ele_id = $(this).attr('id');

			self 		= $(this);

			if(each_ele !== undefined) {

				each_ele( self, row_index, _for_remove, tbl_id, args, main_btn );

			} else {

				if(each_ele_id !== undefined && each_ele_id != '') {

					$(this).attr('id', each_ele_id+'_'+row_index);

				} 

			}

		});

		if(aft_row !== undefined) {

			aft_row(row_index, par_btn, _for_remove, tbl_id, args );

		}

	}

	var _for_remove 	= function(tbl_id, row_index, args)

	{

		var rem_rule 	= (args.rem_rule !== undefined) ? args.rem_rule : undefined
			,ele_str 	= ''
			,rem_rule_check
			,rem_elem
			,rem_find;

		if(rem_rule !== undefined) {

			rem_rule_check = rem_rule instanceof Array;

			if(!rem_rule_check) {

				rem_elem = args.rem_rule.rem_elem;

				rem_find = args.rem_rule.rem_find;

				if( args.not_table )
				{
					ele_str = "#"+tbl_id+'_row_'+row_index+' '+rem_find+' '+rem_elem;
				}
				else 
				{
					ele_str = "#"+tbl_id+'_row_'+row_index+' td'+rem_find+' '+rem_elem;
				}

			} else {

				for(var i = 0, len = args.rem_rule.length; i < len; i++) {

					rem_elem = args.rem_rule[i].rem_elem;

					rem_find = args.rem_rule[i].rem_find;

					if( args.not_table )
					{
						ele_str += "#"+tbl_id+'_row_'+row_index+' '+rem_find+' '+rem_elem+', ';
					}
					else 
					{
						ele_str += "#"+tbl_id+'_row_'+row_index+' td'+rem_find+' '+rem_elem+', ';	
					}

				}

				ele_str 	= ele_str.substring(0, ele_str.length - 2);

			}


		} else {

			if( args.not_table )
			{
				ele_str = "#"+tbl_id+'_row_'+row_index+' div:last a';	
				
			}
			else 
			{
				ele_str = "#"+tbl_id+'_row_'+row_index+' td:last a';
			}

		}

		if( args.parent_elem !== undefined )
		{

			args.parent_elem.find(ele_str).each(function(){

				$(this).attr('id', 'remove_'+tbl_id+'_row_'+row_index);

				if( $(this).attr('onclick') !== undefined ) {

					$(this).attr('onclick','').unbind('click');

				}

			});
		}
		else
		{
			$(ele_str).each(function(){

				$(this).attr('id', 'remove_'+tbl_id+'_row_'+row_index);

				if( $(this).attr('onclick') !== undefined ) {

					$(this).attr('onclick','').unbind('click');

				}

			});
		}

		_for_rem_click(tbl_id, row_index, args);

	}

	var _for_rem_click = function(tbl_id, row_index, args)

	{
		var self
			,aft_rem = (args.aft_rem !== undefined) ? args.aft_rem : undefined;

		if( args.parent_elem !== undefined )
		{
			args.parent_elem.find('#remove_'+tbl_id+'_row_'+row_index).click(function(e)
			{

				self 	= $(this);

				if(aft_rem !== undefined) {

					aft_rem(self, row_index, args);

				} else {

					if( args.not_table )
					{
						$(this).closest($("#"+tbl_id+'_row_'+row_index)).remove();
					}
					else 
					{
						$(this).closest("tr").remove();
					}

				}

			});
		}
		else
		{
			$('#remove_'+tbl_id+'_row_'+row_index).click(function(e){

				self 	= $(this);

				if(aft_rem !== undefined) {

					aft_rem(self, row_index, args);

				} else {

					if( args.not_table )
					{
						$(this).closest($("#"+tbl_id+'_row_'+row_index)).remove();
					}
					else 
					{
						$(this).closest("tr").remove();
					}

				}

			});
		}

	}


	var _row_ind 		= function(args)

	{

		var row_index
			,row_ind_type
			,args_row_ind
			,args_row_obj
			,args_row_obj_val;

		if(args.row_index !== undefined) {

			row_ind_type = typeof(args.row_index);

			args_row_ind = args.row_index

			switch(row_ind_type.toLowerCase()) {

				case 'string': 

					row_index = (isNaN(parseInt(args_row_ind))) ? 0 : parseInt(args_row_ind);

				break;

				case 'number':

					row_index = args_row_ind;

				break;

				case 'object':

					args_row_obj 	 = _conv_data(args.row_index);

					args_row_obj_val = args_row_obj.val();

					row_index 		 = (args_row_obj_val !== undefined && args_row_obj_val != '') ? args_row_obj_val : 0;

				break;

			}

		} else {

			row_index 	 = 0;

		}

		return row_index;

	}

	var _conv_data 		= function(elem)

	{

		var data
			,data_type 		= typeof(elem)
			,elem_jq_check 	= elem instanceof jQuery;


		switch(data_type.toLowerCase()) {

			case 'string':

				data 		= $('#'+elem);

			break;

			case 'object':

				data 		= elem;

				if( data instanceof Array )
				{

					var cnt = 0,
						len = data.length,
						btn_str = '';

					for( ; cnt < len; cnt++ )
					{
						btn_str += data[ cnt ]+',';
					}

					btn_str 	= btn_str.substring( 0, btn_str.length - 1 );

					data 		= $( btn_str );
				}
				else if(!elem_jq_check)
				{
					data = $(elem);	
				} 

			break;

		}

		return data;

	}

	var __validate 		= function(args)

	{

		if(args.args === undefined || Object.keys(args.args).length == 0 || args.types != 'object') throw new Error('Arguments must not be empty and it must be a json object');

		for(var keys in args.args) {
			
			if(!args_key.inArray(keys)) msg_bag += 'The following JSON key is not valid ' + keys + ' - - ';

		}

		if(msg_bag) throw new Error(msg_bag);

		if(args.btn_id === undefined) throw new Error('btn_id key must not be empty.');

		if(args.btn_id !== undefined) {

			var btn_type  = typeof(args.btn_id)
				,btn_check = args.btn_id instanceof Array;

			if(btn_type.toLowerCase() !== 'object' && btn_type.toLowerCase() !== 'string') throw new Error('btn_id key must only be a json object or a string.');

			// if(btn_check) throw new Error('btn_id key must only be a json object or a string.');

			switch(btn_type.toLowerCase()) {

				case 'string':

					if(args.btn_id == '') throw new Error('btn_id key must not be empty.');

				break;

				case 'object':

					if( !args.btn_id instanceof jQuery )
					{

						if(Object.keys(args.btn_id).length == 0) throw new Error('btn_id key must not be empty.');

						for(var keys in args.btn_id) {
				
							if(!btn_keys.inArray(keys)) msg_bag += 'The following JSON key is not valid ' + keys + ' - - ';

						}

						if(msg_bag) throw new Error(msg_bag);

						if(args.btn_id.btn_id === undefined || args.btn_id.btn_id == '') throw new Error('btn_id key delegate must not be empty.');

						if(args.btn_id.btn_id !== undefined && (typeof(args.btn_id.btn_id) !== 'string')) throw new Error('btn_id key delegate must only be a string');

						if(args.btn_id.tbl_id === undefined || args.btn_id.tbl_id == '') throw new Error('tbl_id key delegate must not be empty.');

						if(args.btn_id.tbl_id !== undefined &&(typeof(args.btn_id.tbl_id) !== 'string' || typeof(args.btn_id.tbl_id) !== 'object')) throw new Error('tbl_id key delegate must only be a string or an object');

					}

				break;

			}

		}

		if(args.tbl_id === undefined || args.tbl_id == '') throw new Error('tbl_id key must not be empty.');

		if(args.tbl_id !== undefined && (typeof(args.tbl_id) !== 'string' && typeof(args.tbl_id) !== 'object')) throw new Error('tbl_id key must only be a string or an object');

		if(args.row_index !== undefined && (typeof(args.row_index) !== 'string' && typeof(args.row_index) !== 'object' && typeof(args.row_index) !== 'number')) throw new Error('row_index key must only be a number or a string or an object');

		if(args.apnd_type !== undefined) {

			if(typeof(args.apnd_type) !== 'string' || args.apnd_type == '') throw new Error('append_type key must not be empty and must be a string.');

			if(!apnd_arr.inArray(args.apnd_type)) throw new Error('valid append type are append, prepend, appendto and prependto');

		} 

		if(args.ele_mod !== undefined) {

			var ele_mod_check = args.ele_mod instanceof Array;

			if(typeof(args.ele_mod) !== 'object' || !ele_mod_check) throw new Error('elem_to_mod key must be an array.');

			if(ele_mod_check && args.ele_mod.length == 0) throw new Error('elem_to_mod key must not be empty.');

		}

		if(args.each_ele !== undefined && (typeof(args.each_ele) !== 'function')) throw new Error('each_elem_mod key must be a function.');

		if(args.aft_row !== undefined && (typeof(args.aft_row) !== 'function')) throw new Error('after_copy_row key must be a function.');

		if(args.tbl_zero !== undefined && (typeof(args.tbl_zero) !== 'function')) throw new Error('tbl_length_zero key must be a function.');

		if(args.aft_rem !== undefined && (typeof(args.aft_rem) !== 'function' )) throw new Error('after_remove_row key must be a function.');

		if(args.tr_find !== undefined && (typeof(args.tr_find) !== 'string') || args.tr_find == '' ) throw new Error('tr_find key must not be empty and must be a string');

		if(args.rem_rule !== undefined) {

			var rem_rule_check 	= args.rem_rule instanceof Array
				rem_rule_type 	= typeof(args.rem_rule);

			if(rem_rule_type.toLowerCase() !== 'object') throw new Error('rem_rule key must be either an array or an object.');

			switch(rem_rule_type.toLowerCase()) {

				case 'object':

					if(Object.keys(args.rem_rule).length == 0) throw new Error('rem_rule key must not be empty.');

					if(!rem_rule_check) {

						for(var keys in args.rem_rule) {

							if(!rem_rule_arr.inArray(keys)) msg_bag += 'The following JSON key is not valid ' + keys + ' - - ';

						}

						if(msg_bag) throw new Error(msg_bag);

						if(Object.keys(args.rem_rule).length > 2) throw new Error('rem_rule key must only have two keys.');

						if(args.rem_rule.rem_elem === undefined || args.rem_rule.rem_elem == '' || typeof(args.rem_rule.rem_elem) !== 'string') throw new Error('rem_elem key must not be empty and must be a string.');

						if(args.rem_rule.rem_find === undefined || args.rem_rule.rem_find == '' || typeof(args.rem_rule.rem_find) !== 'string') throw new Error('rem_find key must not be empty and must be a string.');

					} else {

						if(args.rem_rule.length == 0) throw new Error('rem_rule key must not be empty.');

						for(var i = 0, len = args.rem_rule.length; i < len; i++) {

							if(rem_rule_type.toLowerCase() !== 'object') throw new Error('rem_rule key must be either an array or an object.')

							if(Object.keys(args.rem_rule[i]).length == 0) throw new Error('rem_rule key must not be empty.');

							for(var keys in args.rem_rule[i]) {

								if(!rem_rule_arr.inArray(keys)) msg_bag += 'The following JSON key is not valid ' + keys + ' - - ';

							}

							if(msg_bag) throw new Error(msg_bag);

							if(Object.keys(args.rem_rule[i]).length > 2) throw new Error('rem_rule key must only have two keys.');

							if(args.rem_rule[i].rem_elem === undefined || args.rem_rule[i].rem_elem == '' || typeof(args.rem_rule[i].rem_elem) !== 'string') throw new Error('rem_elem key must not be empty and must be a string.');

							if(args.rem_rule[i].rem_find === undefined || args.rem_rule[i].rem_find == '' || typeof(args.rem_rule[i].rem_find) !== 'string') throw new Error('rem_find key must not be empty and must be a string.');

						}

					}

				break;

			}

		} 

		if(args.scrl !== undefined && (typeof(args.scrl) !== 'boolean' && typeof(args.scrl) !== 'function' )) throw new Error('scroll key must be only a boolean or a function');

		if(args.not_table !== undefined && (typeof(args.not_table) !== 'boolean' )) throw new Error('not table key must be only a boolean or a function');

		if(args.in_modal !== undefined && (typeof(args.in_modal) !== 'boolean' )) throw new Error('not table key must be only a boolean or a function');

		if(args.evt_type !== undefined) {

			var evt_str = '';

			for(var i = 0, len = evt_arr.length; i < len; i++) {

				evt_str += evt_arr[i]+' or ';

			}

			evt_str 	= evt_str.substring(0, evt_str.length - 3);

			if(typeof(args.evt_type) !== 'string' || args.evt_type == '') throw new Error('event_type key must be a string and must not be empty.');

			if(!evt_arr.inArray(args.evt_type.toLowerCase())) throw new Error('event type key must be '+evt_str+' only');

		}

	}

	return function add_rows(args) {

		try {

			// new add_rows( args );

			main_function(args);

		} catch(main_err) {

			console.log(main_err);

		}

	};

}();