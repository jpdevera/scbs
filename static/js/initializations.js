/*
|--------------------------------------------------------------------------
| INITIALIZATION
|--------------------------------------------------------------------------
|
| These functions are used when initializing plugins in modals, tabs, etc.
| Instead of reloading the plugin scripts.
| s
| To be placed inside 
| 	$resources['loaded_init'] = array();
|	$this->load_resources->get_resource($resources);
*/
var GLOBAL_SELECTIZE_PLUGIN 	= {
	'remove_button' : {
		'remove_button' : {
			className : 'remove_single'
		}
	},
	'clear_button'  : {
		'label' 	: 'Clear All',
		'className' : 'clearAllLabel',
		'hideWhenEmpty' : false
	},
	'select_all_button' : {
		'label' 	: 'Select All | ',
		'className' : 'selectAllLabel',
		'hideWhenEmpty' : false
	}
};

function selectize_common_filter( input )
{
	input = input.toLowerCase();

	var check_arr 	= [];

	if( this.options )
	{
		for( var key in this.options )
		{
			if( this.options[key].text.toLowerCase() === input )
			{
				check_arr.push(true);
			}
		}
	}

	if( check_arr.indexOf(true) === -1 )
	{
		return true;
	}
	else
	{
		return false;
	}
}

var tb_dt_obj;
var datatable_objects	= {};
var datatable_objects_add_row = {};
function materialize_select_init(id)
{
	var id = id || ".material-select";
	$(id).material_select();
}
function number_init(class_id, decimal_places)
{
	var class_id = class_id || ".number",
		decimal_places = ( decimal_places !== undefined ) ? decimal_places : 2;
	
	$('input' + class_id).number(true, decimal_places);
	$('input' + '.number_zero').number(true, 0);
}

function sumo_select( class_id, settings )
{
	var default_setting 	= $.extend({
        placeholder: 'Select Here',   // Dont change it here.
        csvDispCount: 3,              // display no. of items in multiselect. 0 to display all.
        captionFormat:'{0} Selected', // format of caption text. you can set your locale.
        captionFormatAllSelected:'{0} all selected', // format of caption text when all elements are selected. set null to use captionFormat. It will not work if there are disabled elements in select.
        floatWidth: 400,              // Screen width of device at which the list is rendered in floating popup fashion.
        forceCustomRendering: false,  // force the custom modal on all devices below floatWidth resolution.
        nativeOnDevice: ['Android', 'BlackBerry', 'iPhone', 'iPad', 'iPod', 'Opera Mini', 'IEMobile', 'Silk'], //
        outputAsCSV: false,           // true to POST data as csv ( false for Html control array ie. default select )
        csvSepChar: ',',              // separation char in csv mode
        okCancelInMulti: false,       // display ok cancel buttons in desktop mode multiselect also.
        isClickAwayOk: false,         // for okCancelInMulti=true. sets whether click outside will trigger Ok or Cancel (default is cancel).
        triggerChangeCombined: true,  // im multi select mode whether to trigger change event on individual selection or combined selection.
        selectAll: false,             // to display select all button in multiselect mode.|| also select all will not be available on mobile devices.

        search: false,                // to display input for filtering content. selectAlltext will be input text placeholder
        searchText: 'Search...',      // placeholder for search input
        noMatch: 'No matches for "{0}"',
        prefix: '',                   // some prefix usually the field name. eg. '<b>Hello</b>'
        locale: ['OK', 'Cancel', 'Select All'],  // all text that is used. don't change the index.
        up: false,                    // set true to open upside.
        showTitle: true,               // set to false to prevent title (tooltip) from appearing
        modal_id : ''
    }, settings);

    $(class_id).SumoSelect(default_setting);

    if( default_setting.modal_id != '' )
	{
		$(class_id).parents('div.SumoSelect').find('div.optWrapper').attr('style', 'z-index: 9999 !important;overflow:auto !important;height:100px !important;');
	    var api_orig    =  $('#'+default_setting.modal_id).find('.scroll-pane').data('jsp');

	    if( api_orig !== undefined )
	    {
	        api_orig.destroy();
	    }

	    var container     = $('#'+default_setting.modal_id).find('.scroll-pane').jScrollPane({autoReinitialise: true, contentWidth: '0px'});

	    var api = $('#'+default_setting.modal_id).find('.scroll-pane').data('jsp');  

	    api.reinitialise();  
	}
}

function selectize_init(class_id, type, max_item)
{
	var type = type || '',
		class_id = class_id || '',
		max_item = max_item || 3;
		
		default_class = 'selectize',
		tagging_class = 'tagging',
		max_item_class = 'tagging-max';

	try
	{
		if(type != '' && class_id != '')
		{
			switch(type)
			{
				case 'default' :
					var default_class = class_id;
				break;
				
				case 'tagging' :
					var tagging_class = class_id;
				break;
				
				case 'max-items' :
					var max_item_class = class_id;
				break;
			}
		}
		
		if(  $( 'select.' + default_class ).length !== 0 )
		{
			$( 'select.' + default_class ).each( function() {

				var sel_opt		= {
					plugins 		: ['remove_button'],
					onInitialize 	: function()
					{
						var s = this;
						
						this.revertSettings.$children.each(function() 
						{
							$.extend(s.options[this.value], $(this).data());
						});
					}
				}

				if($(this).attr('data-extra_opt_function'))
				{
					var sel_check_opt 		= eval($(this).attr('data-extra_opt_function'));

					if( sel_check_opt )
					{
						$.extend( sel_opt, sel_check_opt );
					}
				}
				
				if( !$(this).hasClass( 'selectized' ) )
				{
					$(this).selectize(sel_opt);
				}
			} )
		}

		if( $( 'select.selectize-tagging' ).length !== 0 )
		{
			$( 'select.selectize-tagging' ).each( function() {

				var tag_opt 	= {
					plugins: ['remove_button'],
					createOnBlur: true,
				    create: true,
				    delimiter: ',',
				    persist: false
				}

				if($(this).attr('data-extra_opt_function'))
				{
					var tag_check_opt 		= eval($(this).attr('data-extra_opt_function'));

					if( tag_check_opt )
					{
						$.extend( tag_opt, tag_check_opt );
					}
				}

				if( !$(this).hasClass( 'selectized' ) )
				{
					$(this).selectize(tag_opt);
				}

			} );
		}

		if( $( 'input.' + tagging_class ).length !== 0 )
		{
			$( 'input.' + tagging_class ).each( function() {

				var inp_tag_opt 	= {
					plugins: ['remove_button'],
					createOnBlur: true,
				    create: true,
				    delimiter: ',',
				    persist: false
				};

				if($(this).attr('data-extra_opt_function'))
				{
					var inp_tag_check_opt 		= eval($(this).attr('data-extra_opt_function'));

					if( inp_tag_check_opt )
					{
						$.extend( inp_tag_opt, inp_tag_check_opt );
					}
				}

				if( !$(this).hasClass( 'selectized' ) )
				{
					$(this).selectize(inp_tag_opt);
				}

			} );
		}

		if( $( 'select.' + max_item_class ).length  !== 0 )
		{
			$(  'select.' + max_item_class ).each( function() {
				if( !$(this).hasClass( 'selectized' ) )
				{
					$(this).selectize({ maxItems: max_item });
				}
			} )
		}

		if( $( 'input.selectize-custom-input' ).length !== 0 )
		{
			$( 'input.selectize-custom-input' ).each( function() {

				var select_custom_opt 	= {
					plugins: ['remove_button'],
				 	delimiter: ';',
				    persist: false,
				    create: function(input) {
				        return {
				            value: input,
				            text: input
				        }
				    }
				}

				if($(this).attr('data-extra_opt_function'))
				{
					var select_check_custom_opt 		= eval($(this).attr('data-extra_opt_function'));

					if( select_check_custom_opt )
					{
						$.extend( select_custom_opt, select_check_custom_opt );
					}
				}

				if( !$(this).hasClass( 'selectized' ) )
				{
					$(this).selectize(select_custom_opt);
				}

			} );
			   
		}

		if( $('select.lazy-selectize').length !== 0 )
		{
			$( 'select.lazy-selectize' ).each( function() {
				var options 	= {
					data 	: {},
					convertArrayToOptions : function(data)
					{
						if( data )
						{	
							return JSON.parse(data);
						}
					},
					plugins : ['remove_button'],
					onInitialize 	: function()
					{
						var s = this;
						
						this.revertSettings.$children.each(function() 
						{
							$.extend(s.options[this.value], $(this).data());
						});
					}
				}

				if( $(this).attr('multiple') )
				{
					options.mode 		= 'multi';
					options.maxItems 	= null;
				}
				
				if(!$(this).attr('data-loadItemsUrl'))
				{
					throw new Error('attribute data-loadItemsUrl is required. for select '+$(this).attr('id'));
				}

				options.loadItemsUrl = $(this).attr('data-loadItemsUrl');

				if($(this).attr('data-extra_opt_function'))
				{
					var check_opt 		= eval($(this).attr('data-extra_opt_function'));

					if( check_opt )
					{
						$.extend( options, check_opt );
					}
				}
				
				if( !$(this).hasClass( 'selectized' ) )
				{
					$(this).lazySelectize(options);
				}
			} )
		}
	}
	catch(main_err) 
 	{
 		notification_msg('error', main_err);
		console.log(main_err);
	}
}

function datepicker_init()
{
	$('.datepicker').datetimepicker({
		timepicker:false,
		scrollInput: false,
		format:'m/d/Y',
		formatDate:'m/d/Y',
		onClose: function(ct, $i){
			if( typeof( $.fn.parsley ) !== 'undefined' )
			{
				$i.parsley().validate();
			}
		}
	  });
		
	$('.timepicker').datetimepicker({
		datepicker:false,
		format:'h:i A',
		onClose: function(ct, $i){
			if( typeof( $.fn.parsley ) !== 'undefined' )
			{
				$i.parsley().validate();
			}
		}
	});
		
	$('.datepicker_start').datetimepicker({
		format:'m/d/Y',
		formatDate:'m/d/Y',
		scrollInput: false,
		onShow:function( ct ){
		  this.setOptions({
			maxDate:jQuery('.datepicker_end').val()?jQuery('.datepicker_end').val():false
		  })
		},
		timepicker:false,
		onClose: function(ct, $i){
			if( typeof( $.fn.parsley ) !== 'undefined' )
			{
				$i.parsley().validate();
			}
		}
	});
		
	$('.datepicker_end').datetimepicker({
		format:'m/d/Y',
		formatDate:'m/d/Y',
		scrollInput: false,
		onShow:function( ct ){
			this.setOptions({
				minDate:jQuery('.datepicker_start').val()?jQuery('.datepicker_start').val():false
			})
		},
		timepicker:false,
		onClose: function(ct, $i){
			if( typeof( $.fn.parsley ) !== 'undefined' )
			{
				$i.parsley().validate();
			}
		}
	});

	var ds 	= $('.datepicker_start'),
	de 	= $('.datepicker_end'),
	i   = 0,
	len = $('.datepicker_start').length;

	if( len !== 0 )
	{
		for( ; i < len; i++ )
		{
			
			$(ds[i]).datetimepicker({
				format:'m/d/Y',
				formatDate:'m/d/Y',
				scrollInput: false,
				counter : i,
				onShow:function( ct, obj, dt, opt ){
				  this.setOptions({
					maxDate:jQuery(de[opt.counter]).val()?jQuery(de[opt.counter]).val():false
				  })
				},
				timepicker:false,
				onClose: function(ct, $i){
					if( typeof( $.fn.parsley ) !== 'undefined' )
					{
						$i.parsley().validate();
					}
				}
			});
				
			$(de[i]).datetimepicker({
				format:'m/d/Y',
				formatDate:'m/d/Y',
				scrollInput: false,
				counter : i,
				onShow:function( ct, obj, dt, opt ){
					this.setOptions({
						minDate:jQuery(ds[opt.counter]).val()?jQuery(ds[opt.counter]).val():false
					})
				},
				timepicker:false,
				onClose: function(ct, $i){
					if( typeof( $.fn.parsley ) !== 'undefined' )
					{
						$i.parsley().validate();
					}
				}
			});
		}
	}

	if($('.datepicker,.datepicker_start,.datepicker_end,.timepicker').length === 0)
	$('.datepicker,.datepicker_start,.datepicker_end,.timepicker').datetimepicker('destroy');
}

function labelauty_init()
{
	if( $('.labelauty').length !== 0 )
	{
		
		$(".labelauty").not('.labelauty-initialized').labelauty({
			checked_label: "",
			unchecked_label: "",
			class: "labelauty-initialized"
		});
	}
}

function collapsible_init()
{
	$('.collapsible').collapsible({
		accordion : false // A setting that changes the collapsible behavior to expandable instead of the default accordion style
	});
}

function scrollspy_init()
{
	$('.scrollspy').scrollSpy();
}

function dropdown_button_init(class_id)
{
	var elem 	= (  class_id !== undefined ) ? '.'+class_id : '.dropdown-button';
	
	$(elem).dropdown();
}

function colorpicker_init( selector )
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
		selector 		= $('.color-picker');
	}

	if( selector.val() != '' )
	{
		selector.css('backgroundColor', '#' + selector.val());
	}

	selector.ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		},
		onChange: function (hsb, hex, rgb) {
			selector.val('#'+hex);
			selector.css('backgroundColor', '#' + hex);
		}
	})
	.bind('keyup', function(){
		$(this).ColorPickerSetColor(this.value);
	});
}

function load_datatable(custom_settings)
{

	var default_setting 	= $.extend({
		path 					: "",
		table_id 				: "",
		scrollX 				: "",
		scrollY 				: "",
		modal 					: '',
		highlight 				: false,
		group_column 			: 0,
		colspan 				: 0,
		order 					: 0,
		sort					: true,
		sort_order 				: "asc",
		hidden_column			: "",
		cols 					: [],
		sortable_index 			: "_all",
		advanced_filter 		: false,
		display_length 			: 10,
		length_change			: true,
		custom_option_callback 	: "",
		func_callback 			: "",
		succ_callback 			: "",
		post_data 				: {},
		with_search 			: true,
		info		 			: true,
		dom 					: "<'row m-n dataTable-filter p-t-md' <'col s6'B> <'col s3 right-align p-n'l> <'col s3 p-n'f> > <'row dt-table m-n'tr> <'row dataTable-footer m-n' <'col s4 p-n'i> <'col s8 p-n'p> > ",
		buttons 				: [],
		export_file_name 		: '',
		export_title 			: '',
		export_pdf_fontsize     : '',
		export_pdf_orientation  : '',
		delete_remove_filter	: false,
		select 					: false,
		state_save 				: false,
		page_length 			: 10,
		jump_to_page 			: true,
		post_data_func 			: '',
		search_func 			: '',
		row_reorder 			: false,
		update_sort_url 		: '',
		add_multi_del 			: '',
		extra_data_func_callback : '',
		extra_data_after_callback : '',
		extra_bulk_custom_button : {},
		no_export : false,
		no_colvis : false,
		no_bulk_delte : false,
		no_buttons : false,
		export_container		: '',
		colvis_show_btn 		: ''
	}, custom_settings);

	var default_buttons 	= [
		'excel', 'pdf', 'print', 'colvis'
	],
	buttons,
	button_i 				= 0,
	button_len,
	button_opt 				= [];

	var extra_arr_data 		= [];
	var export_buttons 		= [];
	var other_buttons 		= [];

	try
	{
		if( default_setting.buttons.length !== 0 )
		{
			buttons 				= default_setting.buttons;
		}
		else
		{
			buttons 				= default_buttons;
		}

		if( default_setting.buttons === false )
		{
			buttons 				= [];
		}

		button_len 					= buttons.length;

			var buttonCommon = {
       	 		exportOptions: {
	            	format: {
	            		header : function(data, column)
	            		{
	            			var last_col 	= $('#'+default_setting.table_id).find('thead tr th').length - 1;
							
		                    if( last_col != 0 || ( last_col - 1 ) != 0 )
		                    {
		                    	if( group_column != 0 )
		                    	{
		                    		return ( ( last_col + group_column ) == column ) ?
			                        '' :
			                        data.replace(/(<([^>]+)>)/ig, '').replace(/[\n\r\t]+/g, '');
		                    	}
		                    	else
		                    	{
		                    		return ( last_col == column ) ?
			                        '' :
			                        data.replace(/(<([^>]+)>)/ig, '').replace(/[\n\r\t]+/g, '');
		                    	}
		                    }
		                    else
		                    {
		                    	return data.replace(/(<([^>]+)>)/ig, '').replace(/[\n\r\t]+/g, '');
		                    }
	            		},
		                body: function ( data, row, column, node ) {
							
		                    // Strip $ from salary column to make it numeric
		                    var last_col 	= $('#'+default_setting.table_id).find('thead tr th').length - 1;
		                    
		                   
		                    if( last_col != 0 || ( last_col - 1 ) != 0 )
		                    {
		                    	if( group_column != 0 )
		                    	{
		                    		return ( ( last_col + group_column ) == column ) ?
			                        '' :
			                        data.replace(/(<([^>]+)>)/ig, '').replace(/[\n\r\t]+/g, '');
		                    	}
		                    	else
		                    	{
		                    		return ( last_col == column ) ?
			                        '' :
			                        data.replace(/(<([^>]+)>)/ig, '').replace(/[\n\r\t]+/g, '');
		                    	}
		                    	
		                    }
		                    else
		                    {
		                    	return data.replace(/(<([^>]+)>)/ig, '').replace(/[\n\r\t]+/g, '');
		                    }
		                    
		                }
		            }
	        	}
	    };

		if( button_len !== 0 )
		{
			for( ; button_i < button_len; button_i++ )
			{
				var b_opt 				= {};

				if( buttons[ button_i ] != 'colvis' )
				{
					if( buttons[ button_i ] != 'print' )
					{
						b_opt.extend 			= buttons[ button_i ]+'Html5';
					}
					else
					{
						b_opt.extend 			= buttons[ button_i ];
					}

					if( buttons[ button_i ] == 'pdf' )
					{
						b_opt.download 			= 'open';
					}

					b_opt.exportOptions 		= {
						columns : ':visible:not(.col-actions)'
					};

					if( default_setting.export_file_name != '' )
					{
						b_opt.filename 			= default_setting.export_file_name;
					}

					if( default_setting.export_title != '' )
					{
						b_opt.title 			= default_setting.export_title;
					}

					if( buttons[ button_i ] === 'pdf' )
					{
						if ( default_setting.export_pdf_fontsize !== '' )
						{
							b_opt = {...b_opt, customize: function(print) {
								print.defaultStyle.fontSize       = default_setting.export_pdf_fontsize;
								print.styles.tableHeader.fontSize = default_setting.export_pdf_fontsize;
								print.content[1].table.widths     = 'auto';

								print.content.forEach(function(item)
						        {
						            if (item.table)
						            {
						            	var columns 	= item.table.body[0].length,
						            		i 			= 0;
						            	
						            	if( columns > 5 )
						            	{
							            	var width = 50;
							            	var widths = [];

							            	for( ; i < columns; i++ )
							            	{
							            		widths.push(width);
							            	}

							            	item.table.widths = widths;
							            }
						            	
						            } 
						        });
							}};
						}
						else
						{
							b_opt = {...b_opt, customize: function(print) {
								print.content.forEach(function(item)
						        {
						            if (item.table)
						            {
						            	var columns 	= item.table.body[0].length,
						            		i 			= 0;
						            	
						            	if( columns > 5 )
						            	{
							            	var width = 50;
							            	var widths = [];

							            	for( ; i < columns; i++ )
							            	{
							            		widths.push(width);
							            	}

							            	item.table.widths = widths;
							            }
						            	
						            } 
						        });
							}};	
						}

						if ( default_setting.export_pdf_orientation !== '' )
						{
							b_opt = {...b_opt, orientation: default_setting.export_pdf_orientation}
						}
					}

					$.extend(b_opt, buttonCommon);

					export_buttons.push(b_opt);
				}
				else
				{
					b_opt.extend 				= buttons[ button_i ];
					b_opt.text					= "<i class='material-icons valign-middle'>view_column</i> Show / Hide";

					/*var default_col 	= $('#'+default_setting.table_id).find('thead').find('th:not(.sel_all_th)'),
						def_len 		= default_col.length
						def_i 			= 0,
						def_col 		= [];

					if( def_len !== 0 )
					{
						for( ; def_i < def_len; def_i++ )
						{
							if( $('#'+default_setting.table_id).find('thead').find('th.sel_th').length === 0  )
							{
								if( ( default_setting.add_multi_del != '' && default_setting.add_multi_del !== undefined ) || default_setting.extra_bulk_custom_button.length !== 0 )
								{
									if( def_i == 0 )
									{
										continue;
									}
								}
							}

							if( def_i == def_len )
							{
								continue;
							}

							if( ( default_setting.add_multi_del != '' && default_setting.add_multi_del !== undefined ) || default_setting.extra_bulk_custom_button.length !== 0 )
							{
								def_col.push( def_i );
							}
							else
							{
								def_col.push( def_i );	
							}
						}
					}*/
					
					if( default_setting.colvis_show_btn !== '' )
					{
						def_col 				= default_setting.colvis_show_btn;

						b_opt.columns 				= def_col;
					}
					else
					{
						/*if( ( default_setting.add_multi_del != '' && default_setting.add_multi_del !== undefined ) || default_setting.extra_bulk_custom_button.length !== 0 )
						{
							if( $('#'+default_setting.table_id).find('thead').find('th.sel_th').length !== 0  )
							{
								def_col.pop();
							}
						}*/
					}

					$.extend(b_opt, buttonCommon);

					other_buttons.push( b_opt );
				}
				
			}

			if( export_buttons.length !== 0 && !default_setting.no_export )
			{
				button_opt.push({
					extend 	: 'collection',
	            	text 	: 'Export',
	            	name 	: 'export_buttons',
	            	className : default_setting.table_id+'_export_buttons',
	            	buttons : export_buttons
				});
			}
			// button_opt 	= ;
		}
		 
        var def_bulk_actions 	= [];

        if( !default_setting.no_bulk_delte )
        {

	        def_bulk_actions.push({
	        	text: 'Delete', 
	        	action: function () {  }, 
	        	enabled: false, 
	        	name : 'bulk_delete',
	        	className : default_setting.table_id+'_bulk_delete'
	        });
	    }

        if( Object.keys(default_setting.extra_bulk_custom_button).length !== 0 )
        {
        	for( var butkey in default_setting.extra_bulk_custom_button )
        	{
        		var b 		= default_setting.extra_bulk_custom_button[butkey];
        		var b_t 	= b.text || butkey;

        		def_bulk_actions.push({
        			text: b_t, 
        			name : 'bulk_'+butkey,
        			className : default_setting.table_id+'_'+butkey+' '+default_setting.table_id+'_bulk_sub_actions',
        			action: function () {  }, 
        			enabled: false, 
        		});
        	}
        	
        }

        if( ( default_setting.add_multi_del != "" && default_setting.add_multi_del !== undefined ) 
        	|| Object.keys(default_setting.extra_bulk_custom_button).length !== 0
        )
		{

			button_opt.push({
				extend 	: 'collection',
            	text 	: 'Bulk Actions',
            	name 	: 'bulk_actions',
            	className : default_setting.table_id+'_bulk_actions',
            	buttons : def_bulk_actions
			});
		}

		if( other_buttons.length !== 0 && !default_setting.no_colvis )
		{
			button_opt.push(other_buttons);
		}

		if( default_setting.no_buttons )
		{
			button_opt = [];
		}

		if( default_setting.table_id == "" )	
		{
			throw new Error("Table id is required.");
		}

		if( default_setting.path == "" )	
		{
			throw new Error("path is required.");
		}

		var scrollX 				= default_setting.scrollX,
			scrollY 				= default_setting.scrollY,
			modal 					= default_setting.modal,
			highlight 				= default_setting.highlight,
			group_column 			= default_setting.group_column,
			colspan 				= default_setting.colspan,
			order 					= default_setting.order,
			sort 					= default_setting.sort,
			sort_order 				= default_setting.sort_order,
			hidden_column			= default_setting.hidden_column,
			cols 					= default_setting.cols,
			page_length 			= default_setting.page_length,
			length_change			= default_setting.length_change,
			custom_option_callback 	= default_setting.custom_option_callback,
			func_callback 			= default_setting.func_callback,
			post_data 				= default_setting.post_data,
			with_search 			= default_setting.with_search,
			info		 			= default_setting.info,
			delete_remove_filter 	= default_setting.delete_remove_filter,
			options 		 		= {},
			table_obj 				= $("#"+default_setting.table_id),
			select 					= default_setting.select,
			state_save 				= default_setting.state_save,
			display_length 			= default_setting.display_length,
			search_params 			= {},
			row_reorder 			= default_setting.row_reorder,
			search_func 			= default_setting.search_func,
			add_multi_del 			= default_setting.add_multi_del,
			export_container 		= default_setting.export_container,
			// JEFF'S CODE array object of sortable fields
			sortable_index	 		= default_setting.sortable_index || "_all",
			sortable_index 			= (sortable_index != '_all') ? (( typeof( sortable_index ) === 'string' ) ? JSON.parse(sortable_index) : sortable_index ) : sortable_index;

		var tableD_obj;

		datatable_objects_add_row[default_setting.table_id] 	= [];
		
		options 					= {
			"bDestroy": true,
			"bProcessing": true,
			"bServerSide": true,
			"scrollX": scrollX,
			"scrollY": scrollY,
			"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
			"sAjaxSource": $base_url + default_setting.path, 
			"order": [[ order, sort_order ]],
			"bLengthChange" : length_change,
			"pageLength" : page_length,
			"bSort": sort,
			"rowReorder" : row_reorder,
			"searching" : with_search,
			"jump_to_page" : default_setting.jump_to_page,
			"info" : info,
			"iDisplayLength" : display_length,
			stateSave : state_save,
			select : select,
			dom : default_setting.dom,
	        buttons: button_opt,
			"rowCallback": function( row, data ) {
				if(highlight === true) {
					if(row._DT_RowIndex == 0)
						$(row).addClass('highlightRow');
				}
	        },
			"fnServerData": function ( sSource, aoData, fnCallback ) {
				if( Object.keys(search_params).length !== 0 )
				{
					for( var key in search_params )
					{  
						aoData.push( { 'name' : key, 'value' : search_params[ key ] } );
					}
					
				}

				if( post_data !== undefined && post_data != '' )
				{
					if( typeof( post_data ) !== 'object' )
					{
						post_data 	= JSON.parse( post_data );
					}
					if( post_data instanceof Array )
					{
						$.extend(aoData, post_data);
					}
					else
					{
						if( Object.keys(post_data).length !== 0 )
						{
							
							for( var key in post_data )
							{ 
								if( post_data[ key ] instanceof Array )
								{
									var len_data 	= post_data[ key ].length,
										data_cnt 	= 0;
		
									for( ; data_cnt < len_data; data_cnt++ )
									{
										aoData.push( { 'name' : key+'[]', 'value' : post_data[ key ][ data_cnt ] } );	
									}
								}
								else
								{
									aoData.push( { 'name' : key, 'value' : post_data[ key ] } );	
								}
								
							}
						}
					}
				}

				if( default_setting.post_data_func != '' )
				{
					var check_p = eval(default_setting.post_data_func);

					if( check_p )
					{
						aoData = check_p
					}
				}

				$.ajax( {
					"dataType": 'json', 
					"type": "POST", 
					"url": sSource, 
					"data": aoData, 
					"success": function( result ) {
						
						if( result.flag !== undefined )
						{
							if( result.flag == 0 )
							{
								notification_msg('error', result.msg);

							}
						}

						if( result.extra_data !== undefined )
						{
							extra_arr_data 	= result.extra_data;
						}

						if( ( add_multi_del != "" && add_multi_del !== undefined ) || extra_arr_data.length !== 0 )
						{
							if( result.aaData.length !== 0 )
							{
								var len2 	= result.aaData.length,
									j 		= 0;

								for( ; j < len2; j++ )
								{
									var html 	= `<div class="row p-n m-n">
									`;

									if( add_multi_del != "" && add_multi_del !== undefined )
									{
										html  	+= `
											<div class="col s12 p-n m-n">
												<input type='checkbox' data-delete_post='' name='sel_id[]' class='labelauty sel_id'>
											</div>
										`;
									}

									if( extra_arr_data.length !== 0 )
									{
										html 	+= `
											<div class="col s6">
												<div class="dt-details-control" style="cursor:pointer"><i class="material-icons">keyboard_arrow_down</i></div>
											</div>
										`;

										if( extra_arr_data[j]['extra_html'] !== undefined )
										{
											html 	+= extra_arr_data[j]['extra_html'];
										}
									}

									html += `</div>`;

									var new_arr = result.aaData[j].unshift(html);

									// console.log(result.aaData[j]);
								}
							}
						}

						if( datatable_objects_add_row[default_setting.table_id].length !== 0 )
						{
							var new_a = result.aaData.concat(datatable_objects_add_row[default_setting.table_id]);
							
							result.aaData = new_a;
						}
						
						fnCallback( result );

						if( default_setting.succ_callback != '' )
						{
							eval( default_setting.succ_callback );

						}

						if( modal )
						{
						  	$(modal).find('div.modal-content').jScrollPane({autoReinitialise: true, contentWidth: '0px'});
						}
					} 
				} );	
			}
		};

		var arr_json 	= [];

		if( ( add_multi_del != "" && add_multi_del !== undefined ) || default_setting.extra_data_func_callback != '' )
		{

			table_obj.find('thead').find('tr:first').find('th.sel_th').remove();
			table_obj.find('thead').find('tr').find('th.sel_all_th').remove();
			table_obj.find('tbody').find('tr').find('td.sel_td').remove();

			if(table_obj.find('thead').find('tr').length > 1)
			{
				table_obj.find('thead').find('tr:first').prepend('<th width="8%" class="sel_th"></th>');
			}

			if( add_multi_del != "" && add_multi_del !== undefined )
			{
				if(table_obj.find('thead').find('tr').length > 1)
				{
					table_obj.find('thead').find('tr:eq(1)').prepend('<th width="8%" class="sel_all_th"><input type="checkbox" class="labelauty sel_all"></th>');
				}else{
					table_obj.find('thead').find('tr:eq(0)').prepend('<th width="8%" class="sel_all_th"><input type="checkbox" class="labelauty sel_all"></th>');
				}
			}
			else
			{
				if(table_obj.find('thead').find('tr').length > 1)
				{
					table_obj.find('thead').find('tr:eq(1)').prepend('<th width="8%" class="sel_all_th"></th>');
				}else{
					table_obj.find('thead').find('tr:eq(0)').prepend('<th width="8%" class="sel_all_th"></th>');
				}
			}
		}
			
		if(group_column > 0)
		{
			for(cnt = 0; cnt < group_column; cnt++)
			{
				cols.push(cnt);
			}	

			options['drawCallback'] 	= function ( settings ) {

				var api 		= this.api();
				var row_ap 		= api.rows( {page:'current'} );
				var rows 		= row_ap.nodes();
				var nodes 		= row_ap.nodes();

				var node_i 		= 0,
					node_l 		= nodes.length;

				if( extra_arr_data.length !== 0 )
				{
					for( ; node_i < node_l; node_i++ )
					{
						var jq_node 	= $(nodes[node_i]);
						jq_node.attr('data-row_order', node_i);

						if( extra_arr_data[node_i] !== undefined )
						{
							if( extra_arr_data[node_i]['DT_RowId'] !== undefined )
							{
								jq_node.attr('id', extra_arr_data[node_i]['DT_RowId']);
							}
						}
					}
				}

				var last=null;
				var td_class = "";
				var td_colspan = "";

				if( ( add_multi_del != "" && add_multi_del !== undefined ) || default_setting.extra_data_func_callback != '' )
				{
					if( table_obj.find('thead').find('tr:first').find('th.sel_th').length === 1 )
					{
						table_obj.find('thead').find('th:first').removeClass('sorting_asc');
					}
				}

				if( add_multi_del != "" && add_multi_del !== undefined )
				{
					/*var api 		= dt_obj.api();
					var rows 		= api.rows( {page:'current'} ).nodes();
					// var add_multi_js 	= JSON.parse(add_multi_del);
					var last 			= null;
					var td_class 		= "";
					var td_colspan 		= "";*/

					if( table_obj.find('thead').find('tr:first').find('th.sel_th').length === 1 )
					{

						// table_obj.find('thead').find('th:first').removeClass('sorting_asc');

						/*$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_del').remove();

						if( $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons').length !== 0 )
						{
							$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons').append('<button class="btn waves-effect red lighten-2 btn_multi_del" disabled><i class="material-icons">delete</i>Delete</button>');
						}
						else
						{
							$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dataTable-filter').find('div:first').append('<button class="btn waves-effect red lighten-2 btn_multi_del" disabled><i class="material-icons">delete</i>Delete</button>');
						}*/

						if( add_multi_del.custom_button_func )
						{
							eval(add_multi_del.custom_button_func);
						}
					}

					table_obj.find('tbody').find('tr').find('td:first').addClass('sel_td');

					var c_s 	= $('.sel_id'),
						len_j 	= c_s.length,
						j		= 0;

					if( len_j !== 0 )
					{
						for( ; j < len_j; j++ )
						{
							var obj_c 	= $(c_s[j]);
							var obj_cc 	= obj_c.closest('tr').find('.dt_details');

							if( obj_cc.attr('data-disabled') )
							{
								obj_c.attr('disabled', 'disabled');
								obj_c.addClass('disabled');
							}
						}
					}	
					
					$('.sel_id', table_obj).on('click', function( e )
					{
						var arr 	= [];
						var self 	= $(this);
						var par_table = $(this).parents('#'+default_setting.table_id);
						var par_wrap  = $(this).parents('#'+default_setting.table_id+'_wrapper');
						
						
						if( rows.length == par_table.find('.sel_id:not(.disabled):checked').length )
						{
							par_table.find('.sel_all').prop('checked', true);
						}
						else
						{
							par_table.find('.sel_all').prop('checked', false);	
						}

						if( par_table.find('.sel_id').is(':checked') )
						{
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(true);
							// par_wrap.find('.btn_multi_del').removeAttr('disabled');
						}
						else
						{
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(false);
							// par_wrap.find('.btn_multi_del').attr('disabled', 'disabled');	
						}

						if( add_multi_del.check_callback && add_multi_del.check_callback != "" )
						{
							eval(add_multi_del.check_callback);
						}

						var checked 	= par_table.find('.sel_id:checked'),
							len 		= checked.length,
							i 			= 0;

						if( len !== 0 )
						{
							for( ; i < len; i++ )
							{
								var obj_c 	= $(checked[i]);
								var obj_cc 	= obj_c.closest('tr').find('.dt_details');

								if( obj_cc.attr('data-delete_post') )
								{
									arr.push(JSON.parse(obj_cc.attr('data-delete_post')));
								}

								arr_json 	= arr;
							}
						}

					});

					$('.sel_all', table_obj).on('click', function( e )
					{
						var arr 	= [];
						var self 	= $(this);
						var par_table = $(this).parents('#'+default_setting.table_id);
						var par_wrap  = $(this).parents('#'+default_setting.table_id+'_wrapper');

						if( $(this).is(':checked') ) 
						{
							par_table.find('.sel_id:not(.disabled)').prop('checked', true);	
						}
						else
						{
							par_table.find('.sel_id:not(.disabled)').prop('checked', false);	
						}
						

						if( par_table.find('.sel_id').is(':checked') )
						{
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(true);
							// par_wrap.find('.btn_multi_del').removeAttr('disabled');
						}
						else
						{
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(false);
							// par_wrap.find('.btn_multi_del').attr('disabled', 'disabled');	
						}

						if( add_multi_del.check_callback && add_multi_del.check_callback != "" )
						{
							eval(add_multi_del.check_callback);
						}

						var checked 	= par_table.find('.sel_id:checked'),
							len 		= checked.length,
							i 			= 0;

						if( len !== 0 )
						{
							for( ; i < len; i++ )
							{
								var obj_c 	= $(checked[i]);
								var obj_cc 	= obj_c.closest('tr').find('.dt_details');

								if( obj_cc.attr('data-delete_post') )
								{
									arr.push(JSON.parse(obj_cc.attr('data-delete_post')));
								}

								arr_json 	= arr;
							}
						}
					});
				}
				
				$.each(cols, function( index, value ) {
					
					td_class = " class='yellow lighten-3 font-semibold'";
					if (group_column === value+1){
						if(colspan > 0)	
							td_colspan = "colspan='"+colspan+"'";
					}	
						
					api.column(value, {page:'current'} ).data().each( function ( group, i ) {
						if ( last !== group && group.length > 0) {
							$(rows).eq( i ).before(
								'<td '+td_colspan + td_class+'>' + group + '</td>'
							);
		 
							last = group;
						}
					} );
				});
				
				/* To automatically activate tooltips */
				if( $( '.tooltipped' ).length !== 0 )
				{
					$('.tooltipped').tooltip({delay: 50});
				}
				
				/* To automatically activate modal */
				if( ModalEffects !== undefined && $( '.md-trigger' ).length !== 0 )
				{
					ModalEffects.re_init();
				}

				if( $('.labelauty').length !== 0 )
				{					
					labelauty_init();					
				}

				if( $('.datepicker').length !== 0 )
				{
					
						datepicker_init();
					
				}

				if( $('.selectize').length !== 0 )
				{
					if( typeof( $.fn.selectize ) === 'undefined' )
					{
						$.getScript( $base_url+'static/js/selectize.js' );
					}

					selectize_init();
				}

				if( $('.default-avatar').length !== 0 && typeof( $.fn.initial ) !== 'undefined'  )
				{
					create_avatar($('.default-avatar'), {width:80,height:80,fontSize:30});
				}

				if( typeof($.fn.dropdown) !== "undefined" && $('.dropdown-button').length !== 0 )
				{
					dropdown_button_init();
				}
			};

			if( ( add_multi_del != "" && add_multi_del !== undefined ) || default_setting.extra_data_func_callback != '' )
			{
				options['columnDefs'] 	= [
					{ orderable: false, targets: [0,-1] },
					{ targets: sortable_index, orderable: true},
			        (sortable_index !== '_all') ? { targets: '_all', orderable: false }  : '',
					{ visible: false, targets: cols }
				];
			}
			else
			{
				options['columnDefs'] 	= [
					{ orderable: false, targets: -1 },
					{ targets: sortable_index, orderable: true},
			        (sortable_index !== '_all') ? { targets: '_all', orderable: false }  : '',
					{ visible: false, targets: cols }
				];
			}
				
		} else {	

			options['drawCallback'] 	= function() {
				var api 		= this.api();
				var row_ap 		= api.rows( {page:'current'} );
				var rows 		= row_ap.nodes();
				var nodes 		= row_ap.nodes();

				var node_i 		= 0,
					node_l 		= nodes.length;

				if( extra_arr_data.length !== 0 )
				{
					for( ; node_i < node_l; node_i++ )
					{
						var jq_node 	= $(nodes[node_i]);
						jq_node.attr('data-row_order', node_i);

						if( extra_arr_data[node_i] !== undefined )
						{
							if( extra_arr_data[node_i]['DT_RowId'] !== undefined )
							{
								jq_node.attr('id', extra_arr_data[node_i]['DT_RowId']);
							}
						}
					}
				}

				if( ( add_multi_del != "" && add_multi_del !== undefined ) || default_setting.extra_data_func_callback != '' )
				{
					if( table_obj.find('thead').find('tr:first').find('th.sel_th').length === 1 )
					{
						table_obj.find('thead').find('th:first').removeClass('sorting_asc');
					}
				}

				if( add_multi_del != "" && add_multi_del !== undefined )
				{
					// var add_multi_js 	= JSON.parse(add_multi_del);
					var last 			= null;
					var td_class 		= "";
					var td_colspan 		= "";

					if( table_obj.find('thead').find('tr:first').find('th.sel_th').length === 1 )
					{

						/*$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.btn_multi_del').remove();

						if( $(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons').length !== 0 )
						{
							$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dt-buttons').append('<button class="btn waves-effect red lighten-2 btn_multi_del" disabled><i class="material-icons">delete</i>Delete</button>');
						}
						else
						{
							$(rows).parents('div#'+default_setting.table_id+'_wrapper').find('.dataTable-filter').find('div:first').append('<button class="btn waves-effect red lighten-2 btn_multi_del" disabled><i class="material-icons">delete</i>Delete</button>');
						}*/

						if( add_multi_del.custom_button_func )
						{
							eval(add_multi_del.custom_button_func);
						}

					}

					table_obj.find('tbody').find('tr').find('td:first').addClass('sel_td');

					var c_s 	= $('.sel_id'),
						len_j 	= c_s.length,
						j		= 0;

					if( len_j !== 0 )
					{
						for( ; j < len_j; j++ )
						{
							var obj_c 	= $(c_s[j]);
							var obj_cc 	= obj_c.closest('tr').find('.dt_details');

							if( obj_cc.attr('data-disabled') )
							{
								obj_c.attr('disabled', 'disabled');
								obj_c.addClass('disabled');
							}
						}
					}						
					
					$('.sel_id', table_obj).on('click', function( e )
					{
						var arr 	= [];
						var self 	= $(this);
						var par_table = $(this).parents('#'+default_setting.table_id);
						var par_wrap  = $(this).parents('#'+default_setting.table_id+'_wrapper');
						var par_bt_wrap = $('.'+default_setting.table_id+'_bulk_delete');
						
						
						if( rows.length == par_table.find('.sel_id:not(.disabled):checked').length )
						{
							par_table.find('.sel_all').prop('checked', true);
						}
						else
						{
							par_table.find('.sel_all').prop('checked', false);	
						}
						
						if( par_table.find('.sel_id').is(':checked') )
						{
							
							// par_bt_wrap.find('.btn_multi_del').removeAttr('disabled');
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(true);
						}
						else
						{
							// par_bt_wrap.find('.btn_multi_del').attr('disabled', 'disabled');	
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(false);
						}

						if( add_multi_del.check_callback && add_multi_del.check_callback != "" )
						{
							eval(add_multi_del.check_callback);
						}

						var checked 	= par_table.find('.sel_id:checked'),
							len 		= checked.length,
							i 			= 0;

						if( len !== 0 )
						{
							for( ; i < len; i++ )
							{
								var obj_c 	= $(checked[i]);
								var obj_cc 	= obj_c.closest('tr').find('.dt_details');

								if( obj_cc.attr('data-delete_post') )
								{
									arr.push(JSON.parse(obj_cc.attr('data-delete_post')));
								}

								arr_json 	= arr;
							}
						}
						
					});

					$('.sel_all', table_obj).on('click', function( e )
					{
						var arr 	= [];
						var self 	= $(this);
						var par_table = $(this).parents('#'+default_setting.table_id);
						var par_wrap  = $(this).parents('#'+default_setting.table_id+'_wrapper');

						if( $(this).is(':checked') ) 
						{
							par_table.find('.sel_id:not(.disabled)').prop('checked', true);	
						}
						else
						{
							par_table.find('.sel_id:not(.disabled)').prop('checked', false);	
						}
						

						if( par_table.find('.sel_id').is(':checked') )
						{
							// par_wrap.find('.btn_multi_del').removeAttr('disabled');
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(true);
						}
						else
						{
							// par_wrap.find('.btn_multi_del').attr('disabled', 'disabled');	
							tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(false);
						}

						if( add_multi_del.check_callback && add_multi_del.check_callback != "" )
						{
							eval(add_multi_del.check_callback);
						}

						var checked 	= par_table.find('.sel_id:checked'),
							len 		= checked.length,
							i 			= 0;

						if( len !== 0 )
						{
							for( ; i < len; i++ )
							{
								var obj_c 	= $(checked[i]);
								var obj_cc 	= obj_c.closest('tr').find('.dt_details');

								if( obj_cc.attr('data-delete_post') )
								{
									arr.push(JSON.parse(obj_cc.attr('data-delete_post')));
								}

								arr_json 	= arr;
							}
						}
					});
				}
				
				if( $( '.tooltipped' ).length !== 0 )
				{
					$('.tooltipped').tooltip({delay: 50});
				}

				if( $('.datepicker').length !== 0 )
				{
					
					datepicker_init();
					
				}

				if( typeof( $.fn.labelauty ) === 'undefined' )
				{
					if( $('.labelauty').length !== 0 )
					{	
						$.getScript( $base_url+'static/js/jquery-labelauty.js').promise().done( function() 
						{
							labelauty_init();					
						});	
					}
				}
				else
				{
					labelauty_init();					
				}
				
				/* To automatically activate modal */
				if( ModalEffects !== undefined && $( '.md-trigger' ).length !== 0 )
				{
					ModalEffects.re_init();
				}

				if( $('.selectize').length !== 0 )
				{
					if( typeof( $.fn.selectize ) === 'undefined' )
					{
						$.getScript( $base_url+'static/js/selectize.js' );
					}

					selectize_init();
				}

				if( $('.default-avatar').length !== 0 && typeof( $.fn.initial ) !== 'undefined'  )
				{
					create_avatar($('.default-avatar'), {width:80,height:80,fontSize:30});
				}

				if( typeof($.fn.dropdown) !== "undefined" && $('.dropdown-button').length !== 0 )
				{
					dropdown_button_init();
				}
			}

			if( ( add_multi_del != "" && add_multi_del !== undefined ) || default_setting.extra_data_func_callback != '' )
			{
				options['columnDefs'] 	=  [
					{ orderable: false, targets: [0,-1] },
					{ targets: sortable_index, orderable: true},
					(hidden_column !== '') ? { targets: hidden_column, visible: false, searchable: false } : '',
			        (sortable_index !== '_all') ? { targets: '_all', orderable: false }  : ''
				];
			}
			else
			{
			
				options['columnDefs'] 	=  [
					{ orderable: false, targets: -1 },
					{ targets: sortable_index, orderable: true},
					(hidden_column !== '') ? { targets: hidden_column, visible: false, searchable: false } : '',
			        (sortable_index !== '_all') ? { targets: '_all', orderable: false }  : ''
				];
			}
		}	

		if( default_setting.advanced_filter )
		{
			options["orderCellsTop"] = true;

			if( with_search )
			{
				options['searching'] 	 = true;
			}
			else
			{
				options['searching'] 	 = false;
			}

		}

		if( custom_option_callback != '' )
		{
			var check_options 	= eval(custom_option_callback);

			if( check_options !== undefined )
			{
				options 		= check_options;
			}

		}

	 	if( default_setting.scrollX )
        {
			options['initComplete']	= function(settings)
			{
				var my_table 			= this;
				
			 	var main_obj 			= $('#'+default_setting.table_id+'_wrapper').find('.dataTables_scroll').find('.dataTables_scrollHead').find('table')	
			 	var filter_submit 		= main_obj.find('.filter-submit');
			 	var filter_cancel 		= main_obj.find('.filter-cancel');

			 	main_obj.find('.form-filter').bind('keyup', function(e) {
			 		e.stopImmediatePropagation();
					if(e.keyCode == 13) {

						main_obj.find('.filter-submit').trigger('click');
					}
				});
					 
				filter_submit.on('click', function(e)
				{
				  	e.stopImmediatePropagation();
					search_params['action'] 	= 'filter';

					$('textarea.form-filter-outside, select.form-filter-outside, input.form-filter-outside:not([type="radio"],[type="checkbox"])').each(function() {
					    search_params[$(this).attr("name")] = $(this).val();
					});

					$('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', main_obj).each(function() {
						search_params[$(this).attr("name")] = $(this).val();
					});

					  // get all checkboxes
					$('input.form-filter[type="checkbox"]:checked', main_obj).each(function() {
					    search_params[$(this).attr("name")] = $(this).val();
					});

					$('input.form-filter-outside[type="checkbox"]:checked').each(function() {
					    search_params[$(this).attr("name")] = $(this).val();
					});

					// get all radio buttons
					$('input.form-filter[type="radio"]:checked', main_obj).each(function() {
					    search_params[$(this).attr("name")] = $(this).val();
					});

					 $('input.form-filter-outside[type="radio"]:checked').each(function() {
					    search_params[$(this).attr("name")] = $(this).val();
					});

				 	if( search_func )
		            {
		            	search_params 	= eval(search_func);
		            }

		             if( tableD_obj )
			        {

			        	tableD_obj.state.clear();
			        }

			        new_datatable_params(my_table, search_params);

			        datatable_objects_add_row[default_setting.table_id] = [];
					my_table.DataTable().ajax.reload();

					$('.tooltipped').tooltip('remove');

				});

				filter_cancel.on('click', function(e)
				{
					e.stopImmediatePropagation();

					search_params['action'] 	= 'filter_cancel';

				 	$('textarea.form-filter, select.form-filter, input.form-filter', main_obj).each(function() {
					    $(this).val("");
					});

					$('input.form-filter[type="checkbox"]', main_obj).each(function() {
					    $(this).attr("checked", false);
					});

						$('select.form-filter.material-select', main_obj).each(function(){
						$(this).prop('selectedIndex', 0);
						$(this).material_select();   
					});
					
					$('select.form-filter.selectize', main_obj).each(function(){
						var selectize = $(this)[0].selectize;
						selectize.clear();   
						$(this).val('');
					});

					search_params 				= {};

					if( search_func )
		            {
		            	search_params 	= eval(search_func);
		            }

		             if( tableD_obj )
			        {

			        	tableD_obj.state.clear();
			        }

			        // new_datatable_params(my_table, search_params);

			        datatable_objects_add_row[default_setting.table_id] = [];
					my_table.DataTable().ajax.reload();

					$('.tooltipped').tooltip('remove');
				});
			}
		}

		var tb;

		var table 	= {
			table_id : function()
			{
				tb	= table_obj.DataTable( options );

				tb_dt_obj = tb;

				return tb;
			},
			reload 	: function()
			{
				tb.ajax.reload()
			}
		}

		tableD_obj 		= table.table_id();

		if( add_multi_del != "" && add_multi_del !== undefined )
		{
			setInterval(function()
			{ 
				/*table_obj.parents('.dataTables_wrapper').find('.btn_multi_del').on('click', function( e )
				{*/
				tb.buttons('.'+default_setting.table_id+'_bulk_delete').action( function(e) 
				{
					
					e.stopImmediatePropagation();

					var self = $(this);
					
					var p_data 	= {},
					len 	= arr_json.length,
					i 		= 0;

					if( len !== 0 )
					{
						for( ; i < len; i++ )
						{
							var o 	= arr_json[i];

							for( var key in o )						
							{
								if( key != 'new_params' )
								{
								
									if( p_data[ key ] !== undefined )
									{
										p_data[key].push( o[key] );
									}
									else
									{
										p_data[key] 		= [];

										if( o[key] != '' )
										{
											p_data[key].push( o[key] );
										}
									}
								}
								else
								{
									p_data[key]	= o[key];
								}
							}
						}
						
						var opt_del 	= add_multi_del;
						var alert_text 	= opt_del.msg || "";
						var ext_d 		= opt_del.extra_data || {};
						var path 		= opt_del.delete_path || 'Upload/delete_multi_dt';
						var tables 		= opt_del.tables || [];

						var extr_d 				= {};

						extr_d['tables'] 		= tables;
						extr_d['extra_data'] 	= ext_d;

						p_data 		= $.extend( p_data, extr_d );

						$('#confirm_modal').confirmModal({
							topOffset : 0,
							onOkBut : function() {
								start_loading();
								$.post( $base_url+path, p_data ).promise().done( function( response ) 
								{
									response 	= JSON.parse(response);

									if( response.flag )
									{
										if( response.datatable_new_params )
										{
											new_datatable_params(tb, response.datatable_new_params);
										}

										if( response.extra_function )
										{
											eval(response.extra_function);
										}

										table.reload();

										tb.buttons('.'+default_setting.table_id+'_bulk_delete').enable(false);
									}
									
									table_obj.find('.sel_id').prop('checked', false);
									table_obj.find('.sel_all').prop('checked', false);

									self.prop('disabled', true);

									notification_msg(response.status, response.msg);
									end_loading();

								});
							},
							onCancelBut : function() {},
							onLoad : function() {
								$('.confirmModal_content h4').html('Are you sure you want to delete this ' + alert_text + '?');	
								$('.confirmModal_content p').html(
									`
										<br>This action will permanently delete this record from the database and cannot be undone.
										<br>
									`
								);
							},
							onClose : function() {}
						});
					}

				});
				// table_obj

			}, 1000);
		}

		if( default_setting.extra_data_func_callback !== '' )
		{
			/*setInterval(function()
			{*/
				var detailRows 	= [];
				// console.log(table_obj.find('div.dt-details-control'));
				tb.on('click', 'div.dt-details-control', function(e)
				{
					var tr 	= $(this).closest('tr');
					var row = tb.row( tr );
					
					var idx = $.inArray( tr.attr('id'), detailRows );
					var row_ord 	= tr.attr('data-row_order');
					var per_data 	= extra_arr_data[row_ord] || [];
					var func 		= eval(default_setting.extra_data_func_callback);

					if( row.child.isShown() )
					{
						$(this).find('i').text('keyboard_arrow_down');
						row.child.hide();
						detailRows.splice( idx, 1 );
					}
					else
					{
						$(this).find('i').text('keyboard_arrow_up');
						row.child( func ).show();
						// console.log("table_demo2_"+per_data.DT_RowId);
						
						if( default_setting.extra_data_after_callback != '' )
						{
							eval(default_setting.extra_data_after_callback)
						}

						if( idx === -1 ) 
						{
							detailRows.push( tr.attr('id') );
						}
					}

				});

				tb.on( 'draw', function()
				{
					 $.each( detailRows, function ( i, id ) 
					 {
					 	$('#'+id+' div.dt-details-control', tb).trigger( 'click' );
					 });
				});
			// }, 1000);
		}

		tableD_obj.on('row-reorder', function ( e, diff, edit )
		{
			var i 	= 0,
				len = diff.length,
				p_d = {};

			p_d['main_id']			= [];
			p_d['old_position']		= [];
			p_d['new_position']		= [];
			p_d['sort_order'] 		= [];

			for( ; i < len; i++ )
			{
				var tr 		= diff[i].node,
					trq 	= $('<div>'+diff[i].oldData+'</div>'),
					m_in 	= trq.find('.main_dt_id'),
					s_id 	= trq.find('.sort_order_dt');
				
				if( m_in.length !== 0 )
				{
					var newData 	= $('<div>'+diff[i].newData+'</div>'),
						n_s 		= newData.find('.sort_order_dt');
					
					p_d['main_id'].push(m_in.val());
					p_d['old_position'].push(diff[i].oldPosition + 1);
					p_d['new_position'].push(diff[i].newPosition + 1);
					
					if( n_s.length !== 0 )
					{
						p_d['sort_order'].push(n_s.val());	
					}
					else
					{
						p_d['sort_order'].push(0);	
					}
					
				}
			}

			if( default_setting.update_sort_url )
			{
				$.post($base_url+default_setting.update_sort_url, p_d).promise().done(function(response)
				{
					response = JSON.parse(response)

					if( response.msg && response.status && response.status == 'error' )
					{
						notification_msg(response.status, response.msg);
					}
				});
			}
		});

		if( func_callback != "" )
		{
			eval( func_callback );
		}

		if( default_setting.advanced_filter )
		{
			table_obj.find('.form-filter').bind('keyup', function(e) {
				e.stopImmediatePropagation();
				if(e.keyCode == 13) {

					table_obj.find('.filter-submit').trigger('click');
				}
			});
			
		 	table_obj.on('click', '.filter-submit', function(e) {
		        e.stopImmediatePropagation();
		        search_params['action'] 	= 'filter';

		         $('textarea.form-filter-outside, select.form-filter-outside, input.form-filter-outside:not([type="radio"],[type="checkbox"])').each(function() {
	                search_params[$(this).attr("name")] = $(this).val();
	            });

	        	$('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', table_obj).each(function() {
		        	search_params[$(this).attr("name")] = $(this).val();
		        });

	        	  // get all checkboxes
	            $('input.form-filter[type="checkbox"]:checked', table_obj).each(function() {
	                search_params[$(this).attr("name")] = $(this).val();
	            });

	            $('input.form-filter-outside[type="checkbox"]:checked').each(function() {
	                search_params[$(this).attr("name")] = $(this).val();
	            });

	            // get all radio buttons
	            $('input.form-filter[type="radio"]:checked', table_obj).each(function() {
	                search_params[$(this).attr("name")] = $(this).val();
	            });

	             $('input.form-filter-outside[type="radio"]:checked').each(function() {
	                search_params[$(this).attr("name")] = $(this).val();
	            });

	            if( search_func )
	            {
	            	search_params 	= eval(search_func);
	            }

	            if( tableD_obj )
		        {

		        	tableD_obj.state.clear();
		        }

		        // new_datatable_params(datatable_objects[default_setting.table_id], search_params);		        

		        datatable_objects_add_row[default_setting.table_id] = [];
		        // datatable_objects[default_setting.table_id].ajax.reload();

		        table.table_id();

		        $('.tooltipped').tooltip('remove');
		    });

	     	table_obj.on('click', '.filter-cancel', function(e) {
	            
	            e.stopImmediatePropagation();

	         	search_params['action'] 	= 'filter_cancel';
	            
	     	 	$('textarea.form-filter, select.form-filter, input.form-filter', table_obj).each(function() {
	                $(this).val("");
	            });

	            $('input.form-filter[type="checkbox"]', table_obj).each(function() {
	                $(this).attr("checked", false);
	            });

             	$('select.form-filter.material-select', table_obj).each(function(){
	            	$(this).prop('selectedIndex', 0);
	    			$(this).material_select();   
        		});

        		$('select.form-filter.selectize', table_obj).each(function(){
	            	var selectize = $(this)[0].selectize;
	    			selectize.clear();   
	    			$(this).val('');
        		});

	            search_params 				= {};

	            if( search_func )
	            {
	            	search_params 	= eval(search_func);
	            }

             	if( tableD_obj )
		        {

		        	tableD_obj.state.clear();
		        }

		        // new_datatable_params(datatable_objects[default_setting.table_id], search_params);

		        datatable_objects_add_row[default_setting.table_id] = [];
	            // datatable_objects[default_setting.table_id].ajax.reload();
	            table.table_id();
	            $('.tooltipped').tooltip('remove');
	        });
	 	}

	 	if( delete_remove_filter )
	 	{
 			$('textarea.form-filter, select.form-filter, input.form-filter', table_obj).each(function() {
                $(this).val("");
            });

          	$('input.form-filter[type="checkbox"]', table_obj).each(function() {
                $(this).attr("checked", false);
            });

         	$('select.form-filter.material-select', table_obj).each(function(){
            	$(this).prop('selectedIndex', 0);
    			$(this).material_select();   
    		});

    		$('select.form-filter.selectize', table_obj).each(function(){
            	var selectize = $(this)[0].selectize;
    			selectize.clear();   
    			$(this).val('');
    		});
	 	}

		datatable_objects[default_setting.table_id]	= tb;
	 	
	 	if(export_container !== '')
	 		tb.buttons().container().appendTo( export_container );

	 	return tb;
 	}
 	catch(main_err) 
 	{
 		notification_msg('error', main_err);
		console.log(main_err);
	}
}

function create_avatar( obj, settings )
{
	var my_setting = $.extend( {
		name: 'Name',
        seed: 0,
        charCount: 1,
        textColor: '#ffffff',
        height: 100,
        width: 100,
        fontSize: 60,
        fontWeight: 400,
        fontFamily: 'HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica, Arial,Lucida Grande, sans-serif',
        radius: 0
	}, settings );

	if( obj.initial !== undefined )
	{
		obj.initial( my_setting );
	}
}

function parsley_listener_duplicate()
{
	window.Parsley.on('field:validated', function(ParsleyForm) 
	{
		var field_ins 	= [];
		var field_check = [];

		if( ParsleyForm.parent !== undefined )
		{
 
			for(var field in ParsleyForm.parent.fields)
			{
				var fieldInstance = ParsleyForm.parent.fields[field];

				if( fieldInstance.constraintsByName !== undefined && 
	        		Object.keys( fieldInstance.constraintsByName ).length !== 0 && 
	        		"unique" in fieldInstance.constraintsByName &&
	        		fieldInstance.$element !== undefined
	        	)
	    		{
	    			field_check.push( fieldInstance.isValid() );
	    			field_ins.push( fieldInstance );
	    			
	    		}
			}
            
			if( field_check.length !== 0  
				&& field_check.indexOf(false) === -1
				&& field_ins.length !== 0
			)
			{
				var f_len 	= field_ins.length,
					f_i 	= 0; 

				for( ; f_i < f_len; f_i++ )
				{
					field_ins[ f_i ].reset();
				}
			}
		}
            
	});
}
