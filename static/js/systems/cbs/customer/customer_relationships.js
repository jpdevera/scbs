var Customer_relationships = function()
{

	var $module 	 = 'customer';
	var $controller  = 'customer_relationships';

	var init = function()
	{

		/*$( document ).ajaxSend(function( event, jqxhr, settings ) {
			var str = settings.url;
			if(str.includes("process_action") != false){
				start_loading();
			}
			else if(str.includes("process_delete") != false){
				start_loading();
			}
			else if(str.includes("get") != false){
				start_loading();
			}
		});

		$( document ).ajaxComplete(function( event, xhr, settings ) {
			var str = settings.url;
			if(str.includes("process_action") != false){
				end_loading();
			}
			else if(str.includes("process_delete") != false){
				end_loading();
			}
			else if(str.includes("get") != false){
				end_loading();
			}
		});*/

	  	$(document).on( 'click', '#tbl_data_list_wrapper tbody tr', function () {
	          $('#tbl_data_list_wrapper tbody tr td').closest("input").addClass("input_selected");

	          $('#tbl_data_list_wrapper tbody tr td').removeAttr('style');
	          if ( $(this).hasClass('selected') ) {
	              $(this).removeClass('selected');
	          }
	          else {
	              $(this).find('td').attr('style', 'background: #acbad4 !important');
	              $('#tbl_data_list_wrapper tbody tr').removeClass('selected');
	              $(this).addClass('selected');
	          }
	  	});

	  	$("#submit_modal_customer").on('click', function()
	    { 
	      // var address_type = $("input[name='address_type']").val();
	      set_selected();
	    });

	}

	var addRow	= function()
	{
		var selectedValue 	= {};
		var table 			= '#details_table';
		var options			= {
		 	btn_id			: 'add_details',
		 	tbl_id			: 'details_table',
		 	before_copy_row	: function(row_index, self, tbl, tbl_copy)
		 	{
		 		$( '#delete_row').show();
		  		var rows 	= $( table + ' > tbody' ).find( 'select.selectize' ),
		  		i 		= 0,
		  		len;


		  		if( rows.length !== 0 )
		  		{
		  			len = rows.length;

		   			for( ; i < len; i++ )
		   			{
		   				if( rows[i].selectize !== undefined )
		    			{
		     				var select_id 	= ( $(rows[i]).attr('id') === undefined ) ? i : i,
		      				parent 		= $(rows[i]).parents(table + ' > tbody tr').attr('id');

		     				if( parent === undefined )
		     				{
		      					parent = $(table).attr('id');
		     				}

			     			selectedValue[ parent+'_'+select_id ] = rows[i].selectize.getValue()
			     			rows[i].selectize.destroy();

			     			$(rows[i]).val('');
		   			 	}
		   			}
		  		}
		  		// selectize_init();

		 	},
		 	elem_to_mod 	: ['input', 'select', 'textarea', 'label', 'a'],
		 	each_elem_mod	: function(obj, row_index, remove_func, tbl_id, args){
		 		console.log(obj);
		  		obj.val('');

				obj.attr( 'id', obj.attr('id')+'_'+row_index );

				if( obj.attr('for') !== undefined )
				{
					obj.attr( 'for', obj.attr('for')+'_'+row_index );
				}

				if( obj.is(':checkbox') )
				{
					obj.prop('checked', false);
					obj.val('0');
				}
				$("#modal_customer_id_"+row_index).attr("onclick", `modal_customer_init('${row_index}', '', 'Search Customer')`);
		 	},
			after_copy_row: function(row_index, par_btn, _for_remove, tbl_id, args)
		 	{
			  	// $('#delete_row').hide();
			  	var rows 	= $( table + ' > tbody' ).find('select.selectize'),
			  	i 		= 0,
			  	len;

		   		$('.tooltipped').tooltip('remove');

		   		$('.table-actions').removeClass('none');

		  		if( rows.length !== 0 )
		  		{
		   			len = rows.length;

		   			$(`#${tbl_id}_row_${row_index}`).find('.cnt').html(len);

		   			$( '#details_table > tbody' ).find('select.selectize').selectize({
		    			plugins : {
			    			'remove_button' : {
			      				className : 'remove_single'
			     			}
		    			}
		   			});

				   	for( ; i < len; i++ )
				   	{
				    	if( rows[i].selectize !== undefined )
				    	{
				     		var parent_id 	= $( rows[i] ).parents( table + ' > tbody tr' ).attr('id'),
				     			select_id  	=  ( $(rows[i]).attr('id') === undefined ) ? i : i;

				     		if( parent_id === undefined )
				     		{
				      			parent_id  	= $(table).attr('id');
				     		}

				     		rows[i].selectize.setValue( selectedValue[ parent_id + '_' + select_id ] );
				    	}
		   			}
		  		}
		  		let vrow_index = "_"+row_index;
		      	$('.tooltipped').tooltip({delay: 10});
				$( '#delete_row').hide(); //delete button in first row
				
		 	}
		};

		//INITIALIZE THE ADD ROW
		add_rows(options);

	}


	function remove_table(row_index)
	{
		$("#tablerow" + row_index).remove();
	}

	var delete_row = function()
    {
        $('#details_table tbody').find('.delete_row').on('click', function( e )
        {
            $('.tooltipped').tooltip('remove');
            if($('#details_table tbody tr').length > 1 )
            {
                $(this).closest('tr').remove();
                $("#amount").trigger('keyup');
            }
            $('.tooltipped').tooltip({delay: 10});
        });
    }

    //SET SELECTED CUSTOMERS
    var set_selected = function(id)
    {
       
       // Row index
       let row_index = $("input[name='row_index']").val();
       let index_customer_id = "#customer_id";
       let index_full_name   = "#full_name";
       if( row_index > 0 )
       {
       	index_customer_id = index_customer_id+'_'+row_index;
       	index_full_name   = index_full_name+'_'+row_index;
       }

       // selected value
       var selected = $('#tbl_data_list_wrapper tbody tr.selected').find("input").val();
       selected = selected.split("@@");
        $(index_customer_id).val(selected[0]);
        $(index_full_name).val(selected[1]);
        $("#modal_customer").modal("close");
    }


	return {
		init: function() 
		{
      		init();
    	},
    	addRow: function() 
		{
      		addRow();
    	}
	}
}();