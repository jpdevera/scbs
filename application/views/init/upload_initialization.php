<!-- UPLOAD FILE -->
<?php if( ISSET( $resources['upload'] ) AND !EMPTY( $resources['upload'] ) ):

?>
<script>
function modal_crop( file, file_name )
{
	if( typeof( $.fn.leanModal ) !== 'undefined' )
	{
		$('.modal_crop_trigger').leanModal({
				dismissible: false,
				opacity: .5, // Opacity of modal background
				in_duration: 300, // Transition in duration
				out_duration: 200, // Transition out duration
				ready: function() {
					
					$.post('<?php echo base_url() ?>Crop/modal', {file : file, file_name : file_name}).promise().done(function( response ){
						$('.lean-overlay').html(`
							<div class="fixed-action-btn horizontal" style="bottom: 24px; right: 24px;">
								<a class="btn-floating btn-large btn m-t-sm">
									<i class="material-icons">settings</i>
								</a>
								<ul style="width: 550px !important;">
									<li>
										<a class="btn-floating blue darken-1 tooltipped" id="crop_clear" data-position="top" data-delay="50" data-tooltip="Clear">
											<i class="material-icons">loop</i>
										</a>
									</li>
									<li>
										<a class="btn-floating blue darken-1 tooltipped" id="crop_reset" data-position="top" data-delay="50" data-tooltip="Reset">
											<i class="material-icons">shuffle</i>
										</a>
									</li>
									<li>
										<a class="btn-floating blue darken-1 tooltipped" id="crop_crop" data-position="top" data-delay="50" data-tooltip="Crop">
											<i class="material-icons">input</i>
										</a>
									</li>
									<li>
										<a class="btn-floating blue darken-1 tooltipped" id="crop_move" data-position="top" data-delay="50" data-tooltip="Move">
											<i class="material-icons">open_with</i>
										</a>
									</li>
									<li>
										<a class="btn-floating green darken-1 tooltipped" id="crop_zoom_out" data-position="top" data-delay="50" data-tooltip="Zoom out">
											<i class="material-icons">zoom_out</i>
										</a>
									</li>
									<li>
										<a class="btn-floating green darken-1 tooltipped" id="crop_zoom_in" data-position="top" data-delay="50" data-tooltip="Zoom in">
											<i class="material-icons">zoom_in</i>
										</a>
									</li>
									<li>
										<a class="btn-floating red darken-1 tooltipped" id="crop_flip_vertical" data-position="top" data-delay="50" data-tooltip="Flip Vertical">
											<i class="material-icons">swap_vert</i>
										</a>
									</li>
									<li>
										<a class="btn-floating red darken-1 tooltipped" id="crop_flip_horizontal" data-position="top" data-delay="50" data-tooltip="Flip Horizontal">
											<i class="material-icons">swap_horiz</i>
										</a>
									</li>
									<li>
										<a class="btn-floating red darken-1 tooltipped" id="crop_rotate_left" data-position="top" data-delay="50" data-tooltip="Rotate Left">
											<i class="material-icons">restore</i>
										</a>
									</li>
									<li>
										<a class="btn-floating red darken-1 tooltipped" id="crop_rotate_right" data-position="top" data-delay="50" data-tooltip="Rotate Right">
											<i class="material-icons">forward_10</i>
										</a>
									</li>										
								</ul>
							</div>
						`);
						$("#modal_crop .modal-content #content").html(response);
						$("#modal_crop .modal-content #content_blank").html('<img src="'+$base_url+PATH_IMAGES+'avatar.jpg" style="height : 20% !important;"></img>');
					});
				}, // Callback for Modal open
				complete: function() { 
				
				} // Callback for Modal close
			});
	}
}
<?php
	foreach($resources["upload"] as $id => $upload):

		$my_upl 			= $upload;

		/*if( !ISSET( $upload['special'] ) )
		{
			$upload['special'] 					= TRUE;
		}*/

		unset( $my_upl["successCallback"] );
		unset( $my_upl["deleteCallback"] );
		unset( $my_upl["custom_html_func"] );
?>
var <?php echo $id ?>_files_arr 				= [],
	<?php echo $id ?>_files_not_auto_submit 	= [],
	<?php echo $id ?>_orig_files_arr_main 	 	= [],
	<?php echo $id ?>_orig_files_name_arr 	 	= [];

var files_arr 									= [],
	files_not_auto_submit 						= [],
	orig_files_arr_main 	 					= [],
	orig_files_name_arr 	 					= [];

	var arr 				= JSON.parse('<?php echo json_encode( $my_upl ) ?>');

	arr['id']				= '<?php echo $id ?>';

<?php 
		if(ISSET($upload['multiple'])):
?>
			var multiple = true;
		<?php endif; ?>

		<?php if(ISSET($upload['show_progress'])): ?>
			var show_progress = true;
		<?php endif; ?>	

		<?php if(ISSET($upload['drag_drop']) AND !EMPTY($upload['drag_drop'])): ?>
			var drag_drop = true;
		<?php endif; ?>

		<?php if(ISSET($upload['disable'])): ?>
			var disable = '<?php echo $upload['disable']; ?>'
		<?php endif; ?>

		<?php if(ISSET($upload['show_preview'])): ?>
			var show_preview = true;

			<?php if(ISSET($upload['preview_height'])){ ?>
				var preview_height = "<?php echo $upload['preview_height'] ?>";
			<?php } ?>

			<?php if(ISSET($upload['preview_width'])){ ?>
				var preview_width = "<?php echo $upload['preview_width'] ?>";
			<?php } ?>
		<?php endif; ?>

		<?php if(ISSET($upload['auto_submit'])): ?>
			var autoSubmit 		= '<?php echo $upload['auto_submit'] ?>';
			autoSubmit 			= ( autoSubmit == "" ) ? false : true;
		<?php endif; ?>
		
		var multiple = multiple || false,
		show_progress = show_progress || false,
		drag_drop = drag_drop || false,
		show_preview = show_preview || false,
		preview_height = preview_height || "auto",
		preview_width = preview_width || "40px",
		disable 	  = disable || false,
		autoSubmit 	  = ( autoSubmit !== undefined ) ? autoSubmit : true;

		var maxFileCount_var;
		
		<?php if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :?>
			maxFileCount_var 						= '<?php echo $upload['max_file'] ?>';
		<?php 
			else :
		?>
			maxFileCount_var						= 1;
		<?php endif; ?>

		var <?php echo $id ?>_options_upload_dyobj 	= {};

		var delete_default_function 				= function( upl_id, data, obj_inp, or_file_obj, i )
		{
			<?php 
				if( !ISSET( $upload['special'] ) OR EMPTY( $upload['special'] ) ) :
			?>
			$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-error").fadeOut();	

				<?php 
				if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :
					if( $upload['max_file'] > 1 ) :
				?>

					if( $( '#' + upl_id ).parents( 'form' ).find('input[type="hidden"][id="'+upl_id+'"]').length === 1 )
					{
						$( '#' + upl_id ).parents( 'form' ).find('input[type="hidden"][id="'+upl_id+'"]').val('');
					}
					else
					{
						obj_inp.remove();	
					}
					
					if( $( '#' + upl_id ).parents( 'form' ).find('input[type="hidden"][id="'+upl_id+'_orig_filename"]').length === 1 )
					{
						
						$( '#' + upl_id ).parents( 'form' ).find('input[type="hidden"][id="'+upl_id+'_orig_filename"]').val('');

						$("#<?php echo $id ?>").parents("form").find( '#' + upl_id+'_orig_filename' ).removeClass( data[0] );
					}
					else
					{
						or_file_obj.remove();	
					}
					
					<?php echo $id ?>_files_arr.splice( <?php echo $id ?>_files_arr.indexOf( data[i] ), 1 );
					files_arr.splice( files_arr.indexOf( data[i] ), 1 );
					
				<?php 
						else :
				?>	
					$("#<?php echo $id ?>").val(''); 
					$("#<?php echo $id ?>_orig_filename").val(''); 
				<?php 
						endif;
				?>

				<?php 
					else:
				?>
					$("#<?php echo $id ?>").val(''); 
					$("#<?php echo $id ?>_orig_filename").val(''); 
				<?php 
					endif;
				?>

			<?php if(ISSET($upload['default_img_preview'])){ ?>
				var avatar = $base_url + "<?php echo PATH_IMAGES . $upload['default_img_preview'] ?>";
				<?php 
					//if( !ISSET( $upload['no_src'] ) OR EMPTY( $upload['no_src'] ) ) :
				?>
				$("#<?php echo $id ?>_src").attr("src", avatar);
				<?php 
					//endif;
				?>
			<?php } ?>

			<?php endif; ?>
		}

		var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
			csrf_token 	= $('meta[name="csrf-token"]').attr('content');

		var form_data 	= {
			"dir":"<?php echo str_replace('\\', '\\\\', $upload['path']) ?>", 
			"asset_type" : '<?php  
				if( ISSET( $upload['asset_type_code'] ) )
				{
					echo $upload['asset_type_code'];
				}
				else
				{
					echo "";
				}
			?>'
		};

		if( csrf_name )
		{
			form_data[csrf_name] 	= csrf_token;
		}

		var <?php echo $id ?>_options_upload_obj 	= {
			url: $base_url + "upload/",
			fileName: "file",
			allowedTypes:"<?php echo $upload['allowed_types']?>",
			acceptFiles:"*",	
			allowDuplicates: true,
			duplicateStrict: false,
			showDone: false,
			showProgress: show_progress,
			showPreview: show_preview,
			<?php if(ISSET($upload['show_preview'])){ ?>
				previewHeight: preview_height,
				previewWidth: preview_width,
			<?php } ?>
			returnType:"json",	
			formData: form_data,
			uploadFolder:$base_url + "<?php echo str_replace('\\', '\\\\', $upload['path']) ?>",
			downloadCallback: function(files,pd)
			{
				<?php 
					if( ISSET( $upload['show_download_old'] ) AND !EMPTY( $upload['show_download_old'] ) ) :
				?>
				window.location.href= $base_url + "<?php echo str_replace('\\', '\\\\', $upload['path']) ?>" + files[0];
				<?php 
					endif;
				?>
			},
			onProgress:function()
			{
				if( jQuery('.isloading-overlay').length == 0 )
				{
					jQuery( "body" ).isLoading({
						text:       "<div class='loader'></div>",
				        position:   "inside"
		  			}); 
				}
			},
			 onError: function(files,status,errMsg)
		    {       
		    	notification_msg('error', 'Invalid File');
		    	
		    	$('.field-multi-attachment,.input-file-upload').find('.ajax-file-upload-statusbar').each(function()
		    	{
		    		$(this).find('.ajax-file-upload-filename:contains("'+files[0]+'")').parents('.ajax-file-upload-statusbar').remove();
		    	})
		    	

		    	end_loading();
		        
		    },
			onSuccess:function(files,data,xhr,pd, post_data)
			{ 

				files_arr.push(data[0]);
				<?php echo $id ?>_files_arr.push(data[0]);
				<?php echo $id ?>_orig_files_name_arr.push(files[0]);
				orig_files_name_arr.push(files[0]);
				var dy_path 	= "<?php echo str_replace('\\', '\\\\', $upload['path']) ?>";

				if( <?php echo $id ?>_options_upload_dyobj !== undefined )
				{
					if( <?php echo $id ?>_options_upload_dyobj.dy_path !== undefined )
					{
						dy_path 	= <?php echo $id ?>_options_upload_dyobj.dy_path;
					}
				}
				var prev_form_file = post_data;

				if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
				{
					var avatar = output_image( data[0], dy_path );
				}
				else
				{
					var avatar = $base_url + dy_path + data;
				}

				<?php 
					if( !ISSET( $upload['clear_src'] ) OR EMPTY( $upload['clear_src'] ) ) :
				?>
					$("#<?php echo $id ?>_src").attr("src", avatar);
				<?php 
					else :
				?>
					$("#<?php echo $id ?>_src").removeAttr("src");
				<?php 
					endif;
				?>

				<?php 
					if( !ISSET( $upload['special'] ) OR EMPTY( $upload['special'] ) ) :
				?>

				<?php 
					if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) 
						AND $upload['max_file'] > 1
					) :
 				?>
	 			<?php 
	 				else :
	 			?>
				$("#<?php echo $id ?>_upload").prev(".ajax-file-upload").hide();
				<?php endif; ?>

				// $('.tooltipped').tooltip('remove');

				var stat_bar 	= $("#<?php echo $id ?>_upload").parents('div.field-multi-attachment').find('div.ajax-file-upload-statusbar');

 				if( stat_bar.length > 1 )
 				{
 					var stat_len 	= stat_bar.length,
 						stat_i 		= 0;

 					for( ; stat_i < stat_len; stat_i++ )
 					{
 						var stat 	= $( stat_bar[ stat_i ] )

 						stat.find('.ajax-file-upload-red').html("<i class='material-icons'>delete</i>");
 						stat.find('.ajax-file-upload-red').attr('data-tooltip', "Delete");
 						stat.find('.ajax-file-upload-red').attr('data-position', "bottom");
 						stat.find('.ajax-file-upload-red').attr('data-delay', "50");
 					}
 				}
 				else
 				{

					$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").html("<i class='material-icons'>delete</i>");

					$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-tooltip', "Delete");
					$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-position', "bottom");

					$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-delay', "50");
				}

				/*$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").html("<i class='material-icons'>delete</i>");

				$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-tooltip', "Delete");
				$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-position', "bottom");

				$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-delay', "50");*/

				// $("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").addClass('tooltipped');

				// $('.tooltipped').tooltip({delay: 50});

				pd.filename.text(data[0]);


				if( <?php echo $id ?>_files_not_auto_submit.length !== 0 )
				{
					<?php echo $id ?>_files_not_auto_submit 	= [];
					files_not_auto_submit 	= [];
				}

				if( <?php echo $id ?>_orig_files_arr_main.length !== 0 )
				{
					<?php echo $id ?>_orig_files_arr_main 	= [];
					orig_files_arr_main 					= [];
				}

				if($('form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form').length !== 0)
				{
					var ajax_status_bar 		= $('form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form').find('.ajax-file-upload-statusbar:not(.has-orig)');
				}
				else
				{
					var ajax_status_bar 		= $('.ajax-file-upload-statusbar:not(.has-orig)');
				}
			
				ajax_status_bar.first().addClass('has-orig');

				<?php 
					if( !ISSET( $upload['no_orig_file_name'] ) OR EMPTY( $upload['no_orig_file_name'] ) ) :
				?>

				ajax_status_bar.first().find('.ajax-file-upload-red').last().after('<div>&nbsp;</div><div>Original Filename: <b>'+files+'</b></div>');

				<?php 
					endif;
				?>

				<?php 
					if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :
 				?>
 					var upload_id 		= "<?php echo $id ?>";
 						first_obj_inp 	= $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]').first(),
						first_obj_inp_o = $("#<?php echo $id ?>_orig_filename").parents('form').find('input[type="hidden"][id="'+upload_id+'_orig_filename"]').first(),
						last_obj_inp 	= $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]').last(),
						last_obj_inp_o 	= $("#<?php echo $id ?>_orig_filename").parents('form').find('input[type="hidden"][id="'+upload_id+'_orig_filename"]').last(),
 						curr_inp 		=  $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]'); 	

 				<?php 
 						if( $upload['max_file'] > 1 ) :
 				?>
 				
 				var cnt 		= 0,
 					len  		= <?php echo $id ?>_files_arr.length,
 					clone_inp,
 					clone_o_inp,
 					cnt_o 		= 0,
 					len_o 		= <?php echo $id ?>_orig_files_name_arr.length;

 				clone_inp 		= $("#<?php echo $id ?>").clone().removeClass('upload-onselect').val('');
 				clone_o_inp 	= $("#<?php echo $id ?>_orig_filename").clone().removeAttr('class').removeClass('upload-onselect').addClass('form_dynamic_upload_origfilename '+data[0]+'').val('');
 				
 				// $( "#"+upload_id+'_orig_filename' ).parents('form').find('input[type="hidden"].upload-onselect').remove();


 				/*console.log($("#<?php //echo $upload['id'] ?>_orig_filename"));
 				$("#<?php //echo $upload['id'] ?>_orig_filename").addClass( files_arr[0] );*/

 			/*	console.log($("#<?php //echo $upload['id'] ?>_orig_filename").parents('form').find('input:not(.added-orig)[type="hidden"][id="'+upload_id+'_orig_filename"]').first());
 				console.log(data);*/
 				
 				/*$("#<?php //echo $upload['id'] ?>_orig_filename").parents('form').find('input:not(.added-orig)[type="hidden"][id="'+upload_id+'_orig_filename"]').first().addClass( data[0]+' added-orig' );*/
				
 				if(  <?php echo $id ?>_orig_files_name_arr.length === 1 && $("#<?php echo $id ?>_orig_filename").val() == '' )
 				{
 					$("#<?php echo $id ?>_orig_filename").val( <?php echo $id ?>_orig_files_name_arr[0] );
 					
 				}
 				else
 				{

 					var upl_o_id 		= '<?php echo $id ?>_orig_filename';
 					
	 				for( ; cnt_o < len_o; cnt_o++ )
	 				{
	 					var new_clone_o = clone_o_inp.clone().removeAttr('disabled').val( <?php echo $id ?>_orig_files_name_arr[ cnt_o ] );
						
						if( $( '#'+upload_id+'_orig_filename' ).parents('form').find('input:not(.form_dynamic_upload)[type="hidden"][value="'+<?php echo $id ?>_orig_files_name_arr[ cnt_o ]+'"]').length == 0 )
						{
							
							if( $( '#'+upload_id+'_orig_filename' ).length === 0 )
							{
								$('div.ajax-upload-dragdrop').parents('div.field-multi-attachment').append(new_clone_o);
							}
							else
							{
								last_obj_inp_o.after(new_clone_o);	
							}
							
						}
						else
						{
							last_obj_inp_o.after(new_clone_o);	
						}
						
	 				}

	 			}

 				if(  <?php echo $id ?>_files_arr.length === 1 && $("#<?php echo $id ?>").val() == '' )
 				{
 					$("#<?php echo $id ?>").val( <?php echo $id ?>_files_arr[0] );
 					
 				}
 				else
 				{

 					var upl_id 		= '<?php echo $id ?>';
 					
	 				for( ; cnt < len; cnt++ )
	 				{
	 					var new_clone = clone_inp.clone().removeAttr('disabled').val( <?php echo $id ?>_files_arr[ cnt ] );
						
						if( $( '#'+upload_id ).parents('form').find('input:not(.form_dynamic_upload_origfilename)[type="hidden"][value="'+<?php echo $id ?>_files_arr[ cnt ]+'"]').length == 0 )
						{
							
							if( $( '#'+upload_id ).length === 0 )
							{
								$('div.ajax-upload-dragdrop').parents('div.field-multi-attachment').append(new_clone);
							}
							else
							{
								last_obj_inp.after(new_clone);	
							}
							
						}
						
	 				}
	 				
	 				if( $( "#"+upload_id ).parents('form#'+upload_id+'_upload_form, div#'+upload_id+'_upload_form').length !== 0 )
	 				{

						$( "#"+upload_id ).parents('form#'+upload_id+'_upload_form, div#'+upload_id+'_upload_form').find('input[type="hidden"].upload-onselect').remove();
	 				}
	 				else
	 				{
	 					$( "#"+upload_id ).parents('form').find('input[type="hidden"].upload-onselect').remove();
	 				}

	 			}

 				<?php 
 						else :
 				?>
 					$("#<?php echo $id ?>").val(data);
 					if(  <?php echo $id ?>_orig_files_name_arr.length === 1 && $("#<?php echo $id ?>_orig_filename").val() == '' )
	 				{
	 					$("#<?php echo $id ?>_orig_filename").val( <?php echo $id ?>_orig_files_name_arr[0] );
	 					
	 				}
 				<?php 
 						endif;
 				?>
 				<?php 
 					else:
 				?>
 					$("#<?php echo $id ?>").val(data);
 					if(  <?php echo $id ?>_orig_files_name_arr.length === 1 && $("#<?php echo $id ?>_orig_filename").val() == '' )
	 				{
	 					$("#<?php echo $id ?>_orig_filename").val( <?php echo $id ?>_orig_files_name_arr[0] );
	 					
	 				}
				<?php 
					endif;
				?>

				<?php
					if( ISSET( $upload['show_download'] ) AND !EMPTY( $upload['show_download'] ) ) :
				?>
					var upload_id 		= "<?php echo $id ?>",
						total;

					if( $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").length !== 0 )
					{
						var	$download 		= $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").find('.ajax-file-upload-green:contains("Download")');
					}
					else
					{
						var	$download 		= $("#<?php echo $id ?>").parents("form").find('.ajax-file-upload-green:contains("Download")');
					}
					
					var	incr 			= 0;

					var $d 				= $download.first();

					$d.attr('style', 'background: none !important');

					if( $d.hasClass('download-file') === false )
					{
						$d.addClass('download-file');
					}

					if( $d.hasClass('p-r-sm p-n') === false )
					{
						$d.addClass('p-r-sm p-n');		
					}

					// href='"+$base_url+"Upload/download?file="+data[0]+"&path=<?php echo $upload['path']?>'

					/*$d.html("<a class='tooltipped view_js' style='color:gray !important;' data-tooltip='View' data-position='bottom' data-delay='50' ><i class='material-icons'>visibility</i></a>");*/

					$d.html("<a class='tooltipped view_js' data-file='"+data[0]+"' onclick='viewerjs(this, event)' href='"+$base_url+"Upload/download?file="+data[0]+"&path="+dy_path+"' target='_blank' style='color:gray !important;' data-tooltip='View' data-position='bottom' data-delay='50' ><i class='material-icons'>visibility</i></a><a class='tooltipped p-l-xs' href='"+$base_url+"Upload/force_download?file="+data[0]+"&path="+dy_path+"' target='_blank' style='color:gray !important;' data-tooltip='Download' data-position='bottom' data-delay='50' ><i class='material-icons'>cloud_download</i></a>");

					/*$d.html("<a class='tooltipped p-l-xs' href='"+$base_url+"Upload/force_download?file="+data[0]+"&path="+dy_path+"' target='_blank' style='color:gray !important;' data-tooltip='Download' data-position='bottom' data-delay='50' ><i class='material-icons'>cloud_download</i></a>");*/

					if( $( '.tooltipped' ).length !== 0 )
					{
						$('.tooltipped').tooltip('remove');
						$('.tooltipped').tooltip({delay: 50});
					}

					/*if( $("#<?php //echo $upload['id']?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]').length !== 0 )
					{
						total 			= $("#<?php //echo $upload['id']?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]').length - 1;

						for( ; total >= 0; total--  )
						{
							var $d 		= $( $download[ total ] ),
								$file 	= $($("#<?php //echo $upload['id']?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]')[ incr ] );
							
							$d.attr('style', 'background: none !important');

							if( $d.hasClass('download-file') === false )
							{
								$d.addClass('download-file');
							}

							if( $d.hasClass('p-r-sm p-n') === false )
							{
								$d.addClass('p-r-sm p-n');		
							}

							$d.html("<a class='tooltipped' href='"+$base_url+"Kms_forms/download?file="+$file.val()+"' target='_blank' style='color:gray !important;' data-tooltip='Download' data-position='bottom' data-delay='50' ><i class='flaticon-inbox36'></i></a>");

							incr++;
						}

						
					}*/

				<?php 
					endif;
				?>

				$("#<?php echo $id ?>").parents('form').find('.my_error_container, .my_error_file_container').find('ul').find('li').remove();

				$("#<?php echo $id ?>").parents('form').find('div[id$="_error_upload_container"]').find('ul').find('li').remove();

				// end_loading();

				<?php 
					endif;
				?>

				<?php 
					if( ISSET( $upload['successCallback'] ) AND !EMPTY( $upload['successCallback'] ) ) :
				?>
					<?php 
						echo $upload['successCallback'];
					?>
				<?php 
					endif;
				?>

				<?php
					if( ISSET( $upload['show_crop'] ) AND !EMPTY( $upload['show_crop'] ) ) :
				?>
				
				$($("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red[data-tooltip='Delete']")[0])
					.after(
						`
							<div class='ajax-file-upload-red'>
								<a data-tooltip='Edit Image' data-position='bottom' data-delay='50' href="#modal_crop" class="modal_crop_trigger tooltipped">
									<i class='material-icons'>mode_edit</i>
								</a>
							</div>
						`
					);
					$('.tooltipped').tooltip({delay: 50});

					modal_crop("<?php echo $upload['path'] ?>"+data[0], data[0]);

				<?php 
					endif;
				?>

				<?php echo $id ?>_files_arr 			= [];
				files_arr 								= [];
				<?php echo $id ?>_orig_files_name_arr 	= [];
				orig_files_name_arr 					= [];

				$('.field-multi-attachment,.input-file-upload').find('.ajax-file-upload-error').each(function()
	            {
	                $(this).parent().remove();
	            });

				$("body").isLoading("hide");
			},
			showDelete:true,
			deleteCallback: function(data,pd)
			{
				// $('.tooltipped').tooltip('remove');
				var i = 0;
				for(;i<data.length;i++)
				{

					var obj_inp 	= $("#<?php echo $id ?>").parents('form').find('input:not(.form_dynamic_upload_origfilename)[type="hidden"][value="'+data[i]+'"]'),
						upl_id 		= "<?php echo $id ?>",
						obj_multi,
						or_file_obj = $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][class*="'+data[i]+'"]'),
						or_file_obj_v = $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][class*="'+data[i]+'"]').val();

					var delete_options 	= {
						op 		: "delete",
						name 	: data[i],
						dir 	: "<?php echo str_replace('\\', '\\\\', $upload['path']) ?>",
						module_table 	: "",
						module_schema	: "",
						module_method 	: "",
						module_id 		: "",
						delete_path 	: "",
						delete_path_method 	: ""
					};

					if( <?php echo $id ?>_options_upload_dyobj !== undefined )
					{
						if( <?php echo $id ?>_options_upload_dyobj.dy_path !== undefined )
						{
							delete_options['dir'] 	= <?php echo $id ?>_options_upload_dyobj.dy_path;
						}
					}

					<?php 
						if( ISSET( $upload['delete_form'] ) AND !EMPTY( $upload['delete_form'] ) ) :

					?>
					var form_data	= $('<?php echo $upload['delete_form'] ?>').serializeArray(),
						len_form 	= form_data.length,
						i_form 		= 0;

					if( len_form !== 0 )
					{
						for( ; i_form < len_form; i_form++ )
						{
							delete_options[ form_data[i_form].name ]	= form_data[i_form].value;
						}
					}
					<?php 
						endif;
					?>
					
					<?php 
						if( ISSET( $upload['module_table'] ) AND !EMPTY( $upload['module_table'] ) ) :
					?>
						delete_options['module_table'] 	= '<?php echo $upload['module_table'] ?>';
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['module_schema'] ) AND !EMPTY( $upload['module_schema'] ) ) :
					?>
						delete_options['module_schema'] 	= '<?php echo $upload['module_schema'] ?>';
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['module_id'] ) AND !EMPTY( $upload['module_id'] ) ) :
					?>
						delete_options['module_id'] 	= '<?php echo $upload['module_id'] ?>';
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['module_method'] ) AND !EMPTY( $upload['module_method'] ) ) :
					?>
						delete_options['module_method'] 	= '<?php echo $upload['module_method'] ?>';
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['attach_db_column'] ) AND !EMPTY( $upload['attach_db_column'] ) ) :
					?>
						delete_options['attach_db_column'] 	= '<?php echo $upload['attach_db_column'] ?>';
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['module_id'] ) AND !EMPTY( $upload['module_id'] ) ) :
					?>
						delete_options['module_id'] 	= '<?php echo $upload['module_id'] ?>';
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['other_col'] ) AND !EMPTY( $upload['other_col'] ) ) :
					?>
						delete_options['other_col'] 	= JSON.parse( '<?php echo $upload['other_col'] ?>' );
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['delete_path'] ) AND !EMPTY( $upload['delete_path'] ) ) :
					?>
						delete_options['delete_path'] 	= '<?php echo $upload['delete_path'] ?>';
					<?php 
						endif;
					?>

					<?php 
						if( ISSET( $upload['delete_path_method'] ) AND !EMPTY( $upload['delete_path_method'] ) ) :
					?>
						delete_options['delete_path_method'] 	= '<?php echo $upload['delete_path_method'] ?>';
					<?php 
						endif;
					?>
					
					<?php 
						if( !ISSET($upload['dont_delete_in_server']) OR EMPTY( $upload['dont_delete_in_server'] ) ) :
					?>
					
					$.post($base_url + "upload/delete/", delete_options,
					function(resp, textStatus, jqXHR)
					{ 
						delete_default_function( upl_id, data, obj_inp, or_file_obj, i );
					});

					<?php 
						else :
					?>
					
					delete_default_function( upl_id, data, obj_inp, or_file_obj, i );
					<?php 
						endif;
					?>
				}
				pd.statusbar.hide();
				$("#<?php echo $id ?>_upload").prev(".ajax-file-upload").show();

				<?php 
					if( ISSET( $upload['deleteCallback'] ) AND !EMPTY( $upload['deleteCallback'] ) ) :
				?>
					<?php 
						echo $upload['deleteCallback'];
					?>
				<?php 
					endif;
				?>
			},
			onLoad:function(obj, opt)
			{
				var upl_id 		= "<?php echo $id ?>",
					file_val,
					<?php echo $id ?>_files_arr 	= [],
					files_arr 						= [];
				
				var dy_path 	= "<?php echo str_replace('\\', '\\\\', $upload['path']) ?>";

				if( opt.dy_path !== undefined )
				{
					dy_path 	= opt.dy_path;
				}

				if( opt !== undefined )
				{
					<?php echo $id ?>_options_upload_dyobj = opt;
				}

				<?php 

					$multiple_attachment_arr = array('attachments');
						
					if(ISSET($upload['id']) && in_array($upload['id'], $multiple_attachment_arr)) :

						$files = $upload['attached_files'];
							
						foreach ($files AS $file) :
						
							if($file == "." || $file == "..")
							{
								continue;
							}
				?>	
					var val = '<?php echo $file['file_name']; ?>';
						$.ajax({
							cache: true,
							url: $base_url + "upload/existing_files/",
							dataType: "json",
							method : "post",
							data: { dir: dy_path, file: val} ,
							success: function(data) 
							{
								for(var i=0;i<data.length;i++)
								{
									obj.createProgress(data[i]);
								}
								
							}
						});

				<?php 
					endforeach;
				?>
				<?php 
					else:
				?>

					<?php 
						if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :
							if( $upload['max_file'] > 1 ) :
	 				?>
	 					var cnt  	= 0,
	 						len;
	 				if( $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upl_id+'"]').length != 0 )
	 				{
	 					len 		= $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upl_id+'"]').length;

	 					var obj_inp = $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upl_id+'"]'),
	 						or_file_obj 	= $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upl_id+'_orig_filename"]');;

	 					for( ; cnt < len; cnt ++ )
	 					{

	 						var or_file_obj_c 	= $( or_file_obj[ cnt ] )

	 						<?php echo $id ?>_files_arr.push( $(obj_inp[ cnt ]).val() );
	 						files_arr.push( $(obj_inp[ cnt ]).val() );

	 						or_file_obj_c.addClass( $(obj_inp[ cnt ]).val() );

	 					}

	 					file_val 	= <?php echo $id ?>_files_arr;
	 				}

	 				<?php 
	 						else :
	 				?>
	 					file_val 	= $("#<?php echo $id ?>").val();

	 				<?php 
	 						endif;
	 				?>
	 				<?php 
	 					else :
	 				?>
	 					file_val 	= $("#<?php echo $id ?>").val();
	 				<?php 
						endif;
					?>



					$.ajax({
						cache: true,
						url: $base_url + "upload/existing_files/",
						dataType: "json",
						method : "post",
						data: { dir: dy_path, file: file_val
						<?php if(ISSET($upload['special']) AND !EMPTY($upload['special']) AND ISSET($upload['max_file'])){?>
							,max_file : <?php echo $upload['max_file'] ?>,
						<?php } ?>
						} ,						
						success: function(data) 
						{
							for(var i=0;i<data.length;i++)
							{
								obj.createProgress(data[i]);
							}

							if( disable )
							{
								obj.parents('form').find('.ajax-upload-dragdrop').off('dragenter');
								obj.parents('form').find('.ajax-upload-dragdrop').off('dragover');
								obj.parents('form').find('.ajax-upload-dragdrop').off('drop');
								obj.parents('form').find('.ajax-upload-dragdrop').off('dragleave');
								obj.prev().find('form').find('input').prop('disabled', true);
							}
							else
							{
								obj.prev().find('form').find('input').prop('disabled', false);
							}

							var len_data 	= data.length,
								cnt_data 	= 0;

							for( ; cnt_data < len_data; cnt_data++ )
							{
								if( $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").length !== 0 )
								{
									var o_obj 	= $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").find('.form_dynamic_upload[value="'+data[cnt_data]+'"]');
									var $stat 	= $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").find("div.ajax-file-upload-statusbar").find('.ajax-file-upload-filename:contains("'+data[cnt_data]+'")');
								}
								else
								{
									var o_obj 	= $("#<?php echo $id ?>").parents("form").find('.form_dynamic_upload[value="'+data[cnt_data]+'"]');
									var $stat 	= $("#<?php echo $id ?>").parents("form").find("div.ajax-file-upload-statusbar").find('.ajax-file-upload-filename:contains("'+data[cnt_data]+'")');
								}
								
								var o_name 	= '';

								if( o_obj.attr('data-origfile') !== undefined )
								{
									o_name 	= o_obj.attr('data-origfile');
								}

								if( o_name != '' )
								{

									<?php 
										if( !ISSET( $upload['no_orig_file_name'] ) OR EMPTY( $upload['no_orig_file_name'] ) ) :
									?>
										$stat.parents('div.ajax-file-upload-statusbar').addClass('has-orig').find('.ajax-file-upload-red').last().after('<div>&nbsp;</div><div>Original Filename: <b>'+o_name+'</b></div>');
									<?php 
										endif;
									?>
								}

								// console.log(data[cnt_data]);
							}

							/*var $form_dynamic_upload 	= $('.form_dynamic_upload'),
								orig_len,
								$statusbar 				= $('.ajax-file-upload-statusbar'),
								div_incr 	= 0;

							if( $form_dynamic_upload.length !== 0 )
							{
								orig_len 	= $form_dynamic_upload.length - 1;

								for( ; orig_len >= 0; orig_len--  )
								{
									var o_obj 	= $( $form_dynamic_upload[ div_incr ] ),
										$stat 	= $( $statusbar[ orig_len ] ),
										o_name 	= '';
									
									if( o_obj.attr('data-origfile') !== undefined )
									{
										o_name 	= o_obj.attr('data-origfile');
									}

									if( o_name != '' )
									{
										$stat.addClass('has-orig').find('.ajax-file-upload-red').last().after('<div>&nbsp;</div><div>Original Filename: <b>'+o_name+'</b></div>');
									}

									div_incr++;
								}
							}*/


							<?php
								if( ISSET( $upload['show_download'] ) AND !EMPTY( $upload['show_download'] ) ) :
							?>

								if( $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").length !== 0 )
								{
									<?php 
										if( ISSET( $upload['step_form'] ) AND !EMPTY( $upload['step_form'] ) ) :
									?>
									var $download 	= $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").find("div.ajax-file-upload-statusbar .ajax-file-upload-green[style='']");
									<?php 
										else :
									?>
									var $download 	= $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").find('div.ajax-file-upload-statusbar .ajax-file-upload-green:contains("Download")');
									<?php 
										endif;
									?>
									

									var $form_dy 	=$("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").find('.form_dynamic_upload');
								}
								else
								{
									<?php 
										if( ISSET( $upload['step_form'] ) AND !EMPTY( $upload['step_form'] ) ) :
									?>
									var $download 	= $("#<?php echo $id ?>").parents("form").find("div.ajax-file-upload-statusbar .ajax-file-upload-green[style='']");
									<?php 
										else :
									?>
									var $download 	= $("#<?php echo $id ?>").parents("form").find('div.ajax-file-upload-statusbar .ajax-file-upload-green:contains("Download")');
									
									<?php 
										endif;
									?>

									var $form_dy 	=$("#<?php echo $id ?>").parents("form").find('.form_dynamic_upload');												
								}
								
								var data_len 	  	= data.length,
										incr 			= 0
										i_inc 			= 0;
								
								for( ; i_inc < data_len; i_inc++ )
								{

									if( $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").length !== 0 )
									{
										var o_obj 	= $("#<?php echo $id ?>").parents("form#<?php echo $id ?>_upload_form, div#<?php echo $id ?>_upload_form").find('.form_dynamic_upload[value="'+data[i_inc]+'"]');
									}
									else
									{
										var o_obj 	= $("#<?php echo $id ?>").parents("form").find('.form_dynamic_upload[value="'+data[i_inc]+'"]');
									}
									
									var o_name 	= '';

									if( o_obj.attr('data-origfile') !== undefined )
									{
										o_name 	= o_obj.attr('data-origfile');
									}

									var $d 		= $( $download[ incr ] ),
										$file 	= data[ i_inc ];
									
									$d.attr('style', 'background: none !important');
									// $download.addClass('');
									if( $d.hasClass('p-r-sm p-n') === false )
									{
										$d.addClass('p-r-sm p-n');		
									}
									
									$d.html("<a class='tooltipped view_js' data-file='"+$file+"' onclick='viewerjs(this, event)' href='"+$base_url+"Upload/download?file="+$file+"&path="+dy_path+"' target='_blank' style='color:gray !important;' data-tooltip='View' data-position='bottom' data-delay='50' ><i class='material-icons'>visibility</i></a><a class='tooltipped p-l-xs' href='"+$base_url+"Upload/force_download?file="+$file+"&path="+dy_path+"' target='_blank' style='color:gray !important;' data-tooltip='Download' data-position='bottom' data-delay='50' ><i class='material-icons'>cloud_download</i></a>");

									/*$d.append("<a class='tooltipped p-l-xs' href='"+$base_url+"Upload/force_download?file="+$file+"&path="+dy_path+"' target='_blank' style='color:gray !important;' data-tooltip='Download' data-position='bottom' data-delay='50' ><i class='material-icons'>cloud_download</i></a>");*/

									incr++;
								}

								

									/*var data_len 	  	= data.length - 1,
										incr 			= 0;
									
									for( ; data_len >= 0; data_len-- )
									{
										var $d 		= $( $download[ incr ] ),
											$file 	= data[ data_len ];
											
										$d.attr('style', 'background: none !important');
										// $download.addClass('');
										if( $d.hasClass('p-r-sm p-n') === false )
										{
											$d.addClass('p-r-sm p-n');		
										}
										
										$d.html("<a class='tooltipped' href='"+$base_url+"Upload/download?file="+$file+"&path="+dy_path+"' target='_blank' style='color:gray !important;' data-tooltip='Download' data-position='bottom' data-delay='50' ><i class='material-icons'>cloud_download</i></a>");

										incr++;
									}*/
								

								if( $( '.tooltipped' ).length !== 0 )
								{
									$('.tooltipped').tooltip('remove');
									$('.tooltipped').tooltip({delay: 50});
								}

							<?php 
								endif;
							?>

							if(data.length > 0)
							{	
								if( disable )
								{
									$("#<?php echo $id ?>_upload").prev(".ajax-file-upload").hide();
								}

								<?php 
									if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) 
										AND $upload['max_file'] > 1
									) :
				 				?>
				 				if( <?php echo $upload['max_file'] ?> == data.length )
				 				{
				 					$("#<?php echo $id ?>_upload").prev(".ajax-file-upload").hide();
				 				}
				 				
				 				<?php 
				 					else :
				 				?>
				 			
				 				$("#<?php echo $id ?>_upload").prev(".ajax-file-upload").hide();
				 				<?php 
				 					endif;
				 				?>

				 				// $('.tooltipped').tooltip('remove');

				 				var stat_bar 	= $("#<?php echo $id ?>_upload").parents('div.field-multi-attachment').find('div.ajax-file-upload-statusbar');

				 				if( stat_bar.length > 1 )
				 				{
				 					var stat_len 	= stat_bar.length,
				 						stat_i 		= 0;

				 					for( ; stat_i < stat_len; stat_i++ )
				 					{
				 						var stat 	= $( stat_bar[ stat_i ] )

				 						stat.find('.ajax-file-upload-red').html("<i class='material-icons'>delete</i>");
				 						stat.find('.ajax-file-upload-red').attr('data-tooltip', "Delete");
				 						stat.find('.ajax-file-upload-red').attr('data-position', "bottom");
				 						stat.find('.ajax-file-upload-red').attr('data-delay', "50");
				 					}
				 				}
				 				else
				 				{

									$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").html("<i class='material-icons'>delete</i>");

									$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-tooltip', "Delete");
									$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-position', "bottom");

									$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").attr('data-delay', "50");
								}

								// $("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").addClass('tooltipped');

								// $('.tooltipped').tooltip({delay: 50});
								 
								var inps 		= $("#<?php echo $id ?>").parents('form').find('input#<?php echo $id ?>');
								var cnt_inp 	= inps.length,
									inp_c 		= 0;
								
								for( ; inp_c < cnt_inp; inp_c++ )
								{
									var dis_inp = '';
									var check_load		= true;
									var curr_upl_obj 	= $(inps[inp_c]);
									var cus_id 			= "<?php echo $id ?>";
									var filename;

									if( curr_upl_obj.attr('disabled') != null && curr_upl_obj.attr('disabled') !== undefined )
									{
										$('.ajax-file-upload-filename:contains("'+curr_upl_obj.val()+'")').parents('div.ajax-file-upload-statusbar').find('.ajax-file-upload-red').hide();

										dis_inp = 'disabled';
									}
									else
									{
										for(var i=0;i<data.length;i++)
										{
											var div_file = obj.parents('form').find('.ajax-file-upload-statusbar').find(".ajax-file-upload-filename:contains('"+data[i]+"')");

											<?php
												if( ISSET( $upload['show_crop'] ) AND !EMPTY( $upload['show_crop'] ) ) :
											?>

											$(div_file.parents('.ajax-file-upload-statusbar').find(".ajax-file-upload-red[data-tooltip='Delete']")[0]).after(
												`
													<div class='ajax-file-upload-red'>
														<a data-tooltip='Edit Image' data-position='bottom' data-delay='50' href="#modal_crop" class="modal_crop_trigger tooltipped">
															<i class='material-icons'>mode_edit</i>
														</a>
													</div>
												`
											);
											modal_crop("<?php echo $upload['path'] ?>"+data[i], data[i]);
											<?php 
												endif;
											?>
										}
									}

									var upload_stat 	= $('.ajax-file-upload-filename:contains("'+curr_upl_obj.val()+'")').parents('div.ajax-file-upload-statusbar');

									<?php 
										if( ISSET( $upload['custom_html_func'] ) AND !EMPTY( $upload['custom_html_func'] ) ) :
									?>
										<?php 
											echo $upload['custom_html_func'];
										?>
									<?php 
										else :
									?>
										<?php 
											if( ISSET( $upload['custom_title'] ) AND !EMPTY( $upload['custom_title'] ) ) :
										?>

										var custom_title 	= curr_upl_obj.attr('data-attach_custom_file');

											<?php 
												if( ISSET( $upload['custom_orig_name'] ) AND !EMPTY( $upload['custom_orig_name'] ) ) :
											?>

											var cus_n 		= curr_upl_obj.attr('data-input_custom_name');
											
											if( upload_stat.find('.form-basic').length === 0 )
											{

												upload_stat.append(`
													<div class="form-basic">
														<div class="row m-n p-t-sm">
															<div class="col s12 p-t-sm p-l-n">
																<div class="input-field">
																	<label class="active required" for="<?php echo $id ?>_custom_title">Title</label>
																	<input type="text" `+dis_inp+` class="attach_custom_title" name="`+cus_n+`" value="`+custom_title+`"" id="<?php echo $id ?>_custom_title" placeholder="Enter Title" data-parsley-required="true" data-parsley-trigger="change" />
																</div>
															</div>
														</div>
													</div>
												`);
											}
											<?php 
												else :
											?>

											if( upload_stat.find('.form-basic').length === 0 )
											{
											
												upload_stat.append(`
													<div class="form-basic">
														<div class="row m-n p-t-sm">
															<div class="col s12 p-t-sm p-l-n">
																<div class="input-field">
																	<label class="active required" for="<?php echo $id ?>_custom_title">Title</label>
																	<input type="text" `+dis_inp+` class="attach_custom_title" name="<?php echo $id ?>_custom_title[]" value="`+custom_title+`"" id="<?php echo $id ?>_custom_title" placeholder="Enter Title" data-parsley-required="true" data-parsley-trigger="change" />
																</div>
															</div>
														</div>
													</div>
												`);

											}

											<?php 
												endif;
											?>

										<?php 
											endif;
										?>
									<?php 
										endif;
									?>
								}
								// console.log(arguments);
								<?php 
									$show_preview 		 = FALSE;

									if( !ISSET( $upload['show_preview'] ) )
									{
										$show_preview 	= TRUE;
									}
									else if( ISSET( $upload['show_preview'] ) AND EMPTY( $upload['show_preview'] ) )
									{
										$show_preview 	= FALSE;	
									}
									else if( ISSET( $upload['show_preview'] ) AND !EMPTY( $upload['show_preview'] ) )
									{
										$show_preview 	= TRUE;
									}

									if( $show_preview ) :
								?>
								if( data.length === 1 )
								{
									var file_ext 	= data[0].substr( ( data[0].lastIndexOf( '.' ) + 1 ) );
									var doc_type 	= "<?php echo DOCUMENT_EXTENSIONS ?>";
									var media_type  = "<?php echo MEDIA_EXTENSIONS ?>";

									doc_type 		= doc_type.split(',');
									media_type 		= media_type.split(',');

									var img_prev 	= $("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar > img.ajax-file-upload-preview");

									if( doc_type.indexOf( file_ext ) != -1 || media_type.indexOf( file_ext ) != -1 )
									{
										var default_img_preview;

										<?php 
											if( ISSET( $upload['default_img_preview'] ) ) :
										?>
											if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
											{

												default_img_preview 	= output_image( "<?php echo $upload['default_img_preview'] ?>", "<?php echo PATH_IMAGES ?>" );
											}
											else
											{

												default_img_preview 	= $base_url + "<?php echo PATH_IMAGES . $upload['default_img_preview'] ?>";
											}

										<?php 
											else :
										?>
											default_img_preview 	= $base_url + "<?php echo PATH_IMAGES ?>image_preview.png";
										<?php 
											endif;
										?>

										var http 		= new XMLHttpRequest();
										var file_url	=  $base_url+PATH_IMAGES+'icons/file_types/'+file_ext+'.png';

										http.open('HEAD', file_url, false);
										http.send();

										var status 	= http.status;

										if( status != 404 )
										{
											default_img_preview 	= file_url;
										}
										else
										{
											<?php
												if( ISSET($upload['default_img_preview']) ) :
											?>
												if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
												{
													default_img_preview 	= output_image( "<?php echo $upload['default_img_preview'] ?>", "<?php echo PATH_IMAGES ?>" );
												}
												else
												{

													default_img_preview 	= $base_url + "<?php echo PATH_IMAGES . $upload['default_img_preview'] ?>";
												}
											
											<?php 
												else :
											?>
											default_img_preview 	= $base_url + "<?php echo PATH_IMAGES ?>image_preview.png";
											<?php endif; ?>
										}

										
										
									}
									else
									{
										if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
										{

											default_img_preview 	= output_image( data[0], dy_path );
										}
										else
										{

											default_img_preview 	= $base_url + dy_path + data[0];
										}
										
									}

									img_prev.attr('src', default_img_preview);
								}
								else
								{
									var img_prevs 	= $("#<?php echo $id ?>_upload").parents(".field-multi-attachment").find("img.ajax-file-upload-preview"),
										img_len 	= img_prevs.length,
										img_i 		= 0;
									
									if( img_prevs.length !== 0 )
									{
										var doc_type 	= "<?php echo DOCUMENT_EXTENSIONS ?>";
										var media_type  = "<?php echo MEDIA_EXTENSIONS ?>";

										doc_type 		= doc_type.split(',');
										media_type 		= media_type.split(',');

										for( ; img_i < img_len; img_i++ )
										{
											var img_prev = $(img_prevs[img_i]);

											var exts 	 = img_prev.attr("src");
											var ext 	 = exts.substr( ( exts.lastIndexOf( '.' ) + 1 ) );

											var http 		= new XMLHttpRequest();
											var file_url	=  $base_url+PATH_IMAGES+'icons/file_types/'+ext.toLowerCase()+'.png';

											if( doc_type.indexOf( ext ) != -1 || media_type.indexOf( ext ) != -1 )
											{

												http.open('HEAD', file_url, false);
												http.send();

												var status 	= http.status;

												if( status != 404 )
												{
													default_img_preview 	= file_url;
												}
												else
												{
													<?php
														if( ISSET($upload['default_img_preview']) ) :
													?>
														if( CONSTANTS.CHECK_CUSTOM_UPLOAD_PATH )
														{

															default_img_preview 	= output_image( "<?php echo $upload['default_img_preview'] ?>", "<?php echo PATH_IMAGES ?>" );
														}
														else 
														{

															default_img_preview 	= $base_url + "<?php echo PATH_IMAGES . $upload['default_img_preview'] ?>";
														}

													<?php 
														else :
													?>
													default_img_preview 	= $base_url + "<?php echo PATH_IMAGES ?>image_preview.png";
													<?php endif; ?>
												}
											}
											else
											{
												default_img_preview 		= exts;
											}

											img_prev.attr("src", default_img_preview);
										}
									}
								}
								
								<?php 
									endif;
								?>

								if( disable )
								{
									<?php 
										if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :
											if( $upload['max_file'] > 1 ) :
					 				?>

					 				$("#<?php echo $id ?>").parents('form').find('div.ajax-file-upload-statusbar .ajax-file-upload-red').hide();
					 				<?php 
					 						else :
					 				?>

					 				$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").hide();
					 				<?php 
					 						endif;
					 				?>

					 				<?php 
					 					else :
					 				?>

					 				$("#<?php echo $id ?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").hide();
					 				<?php 
					 					endif;
					 				?>

								}
								else
								{
									// $("#<?php //echo $upload['id']?>_upload + div + div.ajax-file-upload-statusbar .ajax-file-upload-red").show();
								}

							}else{
								<?php if(ISSET($upload['default_img_preview'])){ ?>
									var avatar = $base_url + "<?php echo PATH_IMAGES . $upload['default_img_preview'] ?>";
									<?php 
										// if( !ISSET( $upload['no_src'] ) OR EMPTY( $upload['no_src'] ) ) :
									?>
										$("#<?php echo $id ?>_src").attr("src", avatar);
									<?php 
										// endif;
									?>
								<?php } ?>
							}
						}
					});

				<?php 
					endif;
				?>
			}
		};

		<?php 
			if(ISSET($upload['multiple']) AND !EMPTY( $upload['multiple'] )):
		?>
			<?php echo $id ?>_options_upload_obj.multiple 		= true;
		<?php 
			else :
		?>
			<?php echo $id ?>_options_upload_obj.multiple 		= false;
		<?php 
			endif;
		?>

		<?php 
			if( 
				( ISSET( $upload['custom_title'] ) AND !EMPTY( $upload['custom_title'] ) )
				OR 
				( ISSET( $upload['custom_html_func'] ) AND !EMPTY( $upload['custom_html_func'] ) )
			) :
		?>
			<?php echo $id ?>_options_upload_obj.extraHTML 			= function(filename) 
			{
				var check_load		= false;
				var upload_stat;
				var curr_upl_obj;
				var dis_inp;

				var html 			= '';
				var cus_id 			= "<?php echo $id ?>";

				<?php 
					if( ISSET( $upload['custom_html_func'] ) AND !EMPTY( $upload['custom_html_func'] ) ) :
				?>

					return <?php echo $upload['custom_html_func']; ?>
				<?php 
					else :
				?>

					<?php 
						if( ISSET( $upload['custom_orig_name'] ) AND !EMPTY( $upload['custom_orig_name'] ) ) :
					?>

					var cus_n 		= $('#<?php echo $id ?>').attr('data-input_custom_name');

					html 	= `
						<div class="form-basic">
							<div class="row m-n p-t-sm">
								<div class="col s12 p-t-sm p-l-n">
									<div class="input-field">
										<label class="active required" for="<?php echo $id ?>_custom_title">Title</label>
										<input type="text" class="attach_custom_title" name="`+cus_n+`" id="<?php echo $id ?>_custom_title" placeholder="Enter Title" value="`+filename+`" data-parsley-required="true" data-parsley-trigger="change" />
									</div>
								</div>
							</div>
						</div>
					`;

					<?php 
						else :
					?>

					html 	= `
						<div class="form-basic">
							<div class="row m-n p-t-sm">
								<div class="col s12 p-t-sm p-l-n">
									<div class="input-field">
										<label class="active required" for="<?php echo $id ?>_custom_title">Title</label>
										<input type="text" class="attach_custom_title" name="<?php echo $id ?>_custom_title[]" id="<?php echo $id ?>_custom_title" placeholder="Enter Title" value="`+filename+`" data-parsley-required="true" data-parsley-trigger="change" />
									</div>
								</div>
							</div>
						</div>
					`;

					<?php 
						endif;
					?>
				<?php 
					endif;
				?>

				return html;
			}
		<?php 
			endif;
		?>

		<?php if(ISSET($upload['auto_submit'])): ?>

			<?php echo $id ?>_options_upload_obj.autoSubmit 		= "<?php echo $upload['auto_submit'] ?>";
			
			<?php if( EMPTY( $upload['auto_submit'] ) ) : ?>
				<?php echo $id ?>_options_upload_obj.onSelect 		= function(files, a, b)
				{	
					if( $('.ajax-file-upload-error').length > 0 )
					{
						return false;
					}

					<?php 
						if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :
	 				?>
	 					var upload_id 		= "<?php echo $id ?>";
	 						last_obj_inp 	= $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]').last(),
	 						last_obj_or_file = $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upload_id+'_orig_filename"]').last();

	 					var file_name_ext,
							new_file,
							new_file_name,
							remove_parsley 	= false;
						
						if( files.length === 1 )
						{
							file_name_ext 	= files[0].name.substr( ( files[0].name.lastIndexOf( '.' ) + 1 ) ),
							new_file 		= files[0].name.substr(0,files[0].name.lastIndexOf('.')) || files[0].name + "",
							new_file_name 	= new_file+'_'+(new Date).getTime()+Math.random().toString(16).slice(2)+'.'+file_name_ext;

							<?php echo $id ?>_files_not_auto_submit.push(new_file_name);
							files_not_auto_submit.push(new_file_name);
							<?php echo $id ?>_orig_files_arr_main.push(new_file_name);
							orig_files_arr_main.push(new_file_name);

							a.uniqueNamereal.push(new_file_name);

							a.cancelButtonClass 	= 'ajax-file-upload-cancel '+new_file_name;

							$('.ajax-file-upload-statusbar:not(.has-orig)').find('.ajax-file-upload-cancel').removeClass('ajax-file-upload-cancel').addClass(a.cancelButtonClass);

							$('.ajax-file-upload-statusbar:not(.has-orig)').find('.ajax-file-upload-cancel').find('input[value="ajax-file-upload-cancel"]').val(a.cancelButtonClass);
						}
						else
						{
							var fl_len 		= files.length,
								fl_cnt 		= 0;

							for( ; fl_cnt < fl_len; fl_cnt ++ )
							{
								file_name_ext 	= files[ fl_cnt ].name.substr( ( files[ fl_cnt ].name.lastIndexOf( '.' ) + 1 ) ),
								new_file 		= files[ fl_cnt ].name.substr(0,files[ fl_cnt ].name.lastIndexOf('.')) || files[ fl_cnt ].name + "",
								new_file_name 	= new_file+'_'+(new Date).getTime()+Math.random().toString(16).slice(2)+'.'+file_name_ext;

								<?php echo $id ?>_files_not_auto_submit.push(new_file_name);
								files_not_auto_submit.push(new_file_name);
								<?php echo $id ?>_orig_files_arr_main.push(new_file_name);
								orig_files_arr_main.push(new_file_name);
								a.uniqueNamereal.push(new_file_name);

								a.cancelButtonClass 	= 'ajax-file-upload-cancel '+new_file_name;

								$($('.ajax-file-upload-statusbar:not(.has-orig)')[fl_cnt]).find('.ajax-file-upload-cancel').removeClass('ajax-file-upload-cancel').addClass(a.cancelButtonClass);

								$($('.ajax-file-upload-statusbar:not(.has-orig)')[fl_cnt]).find('.ajax-file-upload-cancel').find('input[value="ajax-file-upload-cancel"]').val(a.cancelButtonClass);
							}
						}
	 				<?php 
	 						if( $upload['max_file'] > 1 ) :
	 				?>

	 				var cnt 	= 0,
	 					len  	= <?php echo $id ?>_files_not_auto_submit.length,
	 					cnt_or 	= 0,
	 					len_or 	= <?php echo $id ?>_orig_files_arr_main.length;
	 				
 					if(  <?php echo $id ?>_orig_files_arr_main.length === 1 && ( $("#<?php echo $id ?>_orig_filename").parents('form').find('input[type="hidden"][id="'+upload_id+'_orig_filename"]').length === 1 && $("#<?php echo $id ?>_orig_filename").val() == '' ) )
	 				{
	 					$("#<?php echo $id ?>").parents("form").find( '#'+upload_id+'_orig_filename' ).addClass('upload-onselect').val( <?php echo $id ?>_orig_files_arr_main[0] );
	 				}
	 				else
	 				{
	 					for( ; cnt_or < len_or; cnt_or++ )
		 				{
							var clone_inp_or 	= $("#<?php echo $id ?>_orig_filename").clone().removeAttr('disabled').addClass('upload-onselect').val(<?php echo $id ?>_orig_files_arr_main[ cnt_or ]);
																													
							if( $( '#'+upload_id+'_orig_filename' ).val() == '' )
							{
								$("#<?php echo $id ?>").parents("form").find( '#'+upload_id+'_orig_filename' ).addClass('upload-onselect').val( <?php echo $id ?>_orig_files_arr_main[ cnt_or ] );
							}
							else
							{
								if( $( '#'+upload_id+'_orig_filename' ).parents('form').find('input[type="hidden"][value="'+<?php echo $id ?>_orig_files_arr_main[ cnt_or ]+'"]').length === 0 )
								{
									last_obj_or_file.after(clone_inp_or);
								}
							}
		 					
		 				}
	 				}
	 				
	 				if(  <?php echo $id ?>_files_not_auto_submit.length === 1 && ( $("#<?php echo $id ?>").parents('form').find('input[type="hidden"][id="'+upload_id+'"]').length === 1 && $("#<?php echo $id ?>").val() == '' ) )
	 				{
	 					$("#<?php echo $id ?>").parents("form").find( '#'+upload_id ).attr('class', 'upload-onselect').val( <?php echo $id ?>_files_not_auto_submit[0] );
	 					remove_parsley 	= true;
	 				}
	 				else
	 				{
		 				for( ; cnt < len; cnt++ )
		 				{
		 					
							var clone_inp 	= $("#<?php echo $id ?>").clone().attr('class', 'upload-onselect').removeAttr('disabled').val(<?php echo $id ?>_files_not_auto_submit[ cnt ]);
							
							if( $( '#'+upload_id ).val() == '' )
							{
								$("#<?php echo $id ?>").parents("form").find( '#'+upload_id ).attr('class', 'upload-onselect').val( <?php echo $id ?>_files_not_auto_submit[ cnt ] );
								remove_parsley 	= true;
							}
							else
							{
								
								if( $( '#'+upload_id ).parents('form').find('input:not(.form_dynamic_upload_origfilename)[type="hidden"][value="'+<?php echo $id ?>_files_not_auto_submit[ cnt ]+'"]').length === 0 )
								{
									last_obj_inp.after(clone_inp);
									remove_parsley 	= true;
								}
							}
		 					
		 				}
		 			}

	 				<?php 
	 						else :
	 				?>
	 					$("#<?php echo $id ?>").val(files[0].name);
	 					$("#<?php echo $id ?>_orig_filename").val(files[0].name);
	 					remove_parsley 	= true;
	 				<?php 
	 						endif;
	 				?>
	 				<?php 
	 					else:
	 				?>
	 					$("#<?php echo $id ?>").val(files[0].name);
	 					$("#<?php echo $id ?>_orig_filename").val(files[0].name);
	 					remove_parsley 	= true;
					<?php 
						endif;
					?>

					if( remove_parsley )
					{
						$("#<?php echo $id ?>").parents('form').find('.my_error_container, .my_error_file_container').find('ul').find('li').remove();

						$("#<?php echo $id ?>").parents('form').find('div[id$="_error_upload_container"]').find('ul').find('li').remove();
					}
					remove_parsley 	= false;

					// <?php echo $id ?>_orig_files_arr_main 		= [];
					orig_files_arr_main 		= [];
					// <?php echo $id ?>_files_not_auto_submit 	= [];
					// files_not_auto_submit 		= [];
				}

				<?php echo $id ?>_options_upload_obj.onCancel 	= function(files,pd,uniqueName)
				{
					<?php 
						if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :
	 				?>

	 				<?php 
	 						if( $upload['max_file'] > 1 ) :
	 				?>
	 			
	 				var len 		= uniqueName.length,
	 					cnt 		= 0,
	 					upload_id 	= "<?php echo $id ?>";
	 					
	 				for( ; cnt < len; cnt++ )
	 				{
 						if( $("#"+upload_id).parents('form').find('input[type="hidden"][id="'+upload_id+'"]').length === 1 )
	 					{
	 						$("#"+upload_id).parents('form').find('input[type="hidden"][id="'+upload_id+'"]').val('');
	 					}
	 					else
	 					{
	 						$("#"+upload_id).parents('form').find('input[type="hidden"][value="'+uniqueName[ cnt ]+'"]').remove();
	 					}

	 					if( $("#"+upload_id+'_orig_filename').parents('form').find('input[type="hidden"][id="'+upload_id+'_orig_filename"]').length === 1 )
	 					{
	 						if( $("#"+upload_id).parents('form').find('input[type="hidden"][id="'+upload_id+'"]').val() == '' )
	 						{
	 							$("#"+upload_id+'_orig_filename').parents('form').find('input[type="hidden"][id="'+upload_id+'_orig_filename"]').val('');
	 						}
	 					}
	 					else
	 					{
	 						$("#"+upload_id+'_orig_filename').parents('form').find('input[type="hidden"][value="'+uniqueName[ cnt ]+'"]').remove();
	 					}

	 					<?php echo $id ?>_files_not_auto_submit.splice( <?php echo $id ?>_files_not_auto_submit.indexOf( uniqueName[ cnt ] ), 1 );
	 					files_not_auto_submit.splice( files_not_auto_submit.indexOf( uniqueName[ cnt ] ), 1 );
	 					<?php echo $id ?>_orig_files_arr_main.splice( <?php echo $id ?>_orig_files_arr_main.indexOf( uniqueName[ cnt ] ), 1 );
	 					orig_files_arr_main.splice( orig_files_arr_main.indexOf( uniqueName[ cnt ] ), 1 );
	 				}


	 				/*var classes  	= pd.cancel.attr('class'),
	 					classes 	= classes.split(" "),
	 					file_class 	= classes[2],
	 					upload_id 	= "<?php //echo $upload['id'] ?>";
	 					
	 					if( $("#"+upload_id).parents('form').find('input[type="hidden"][id="'+upload_id+'"]').length === 1 )
	 					{
	 						$("#"+upload_id).parents('form').find('input[type="hidden"][id="'+upload_id+'"]').val('');
	 					}
	 					else
	 					{
	 						$("#"+upload_id).parents('form').find('input[type="hidden"][value="'+file_class+'"]').remove();
	 					}

	 					files_not_auto_submit.splice( files_not_auto_submit.indexOf( file_class ), 1 );*/
	 				

	 				<?php 
	 						else :
	 				?>
	 					$("#<?php echo $id ?>").val('');
	 					$("#<?php echo $id?>_orig_filename").val('');
	 				<?php 
	 						endif;
	 				?>

	 				<?php 
	 					else :
	 				?>
	 					$("#<?php echo $id ?>").val('');
	 					$("#<?php echo $id ?>_orig_filename").val('');
	 				<?php 
	 					endif;
	 				?>


	 				<?php if( ISSET( $upload['modal'] ) AND !EMPTY( $upload['modal'] ) ) : ?>
	 					var api_orig    = $("<?php echo $upload['modal'] ?>").find('div.modal-content').data('jsp');

		                if( api_orig !== undefined )
		                {
		                    api_orig.destroy();
		                }
		                    
		                $("<?php echo $upload['modal'] ?>").find('div.modal-content').jScrollPane({autoReinitialise: true, contentWidth: '0px'}).bind('mousewheel',
		                    function(e)
		                    {
		                        e.preventDefault();
		                    }
		                );

		                api = $("<?php echo $upload['modal'] ?>").find('div.modal-content').data('jsp');  
		                
		                api.reinitialise();  
	 				<?php endif; ?>

	 				files_not_auto_submit.pop();
				}
			<?php 
				endif;
			?>

		<?php else: ?>
			<?php echo $id ?>_options_upload_obj.autoSubmit 	= true;
		<?php endif; ?>

		<?php if( ISSET( $upload['drag_drop'] ) AND !EMPTY( $upload['drag_drop'] ) ) :?>
			<?php echo $id ?>_options_upload_obj.dragDrop 		= true;
		<?php 
			else :
		?>
			<?php echo $id ?>_options_upload_obj.dragDrop 		= false;
		<?php endif; ?>

		<?php if( ISSET( $upload['max_file'] ) AND !EMPTY( $upload['max_file'] ) ) :?>
			<?php echo $id ?>_options_upload_obj.maxFileCount 		= '<?php echo $upload['max_file'] ?>';
		<?php 
			else :
		?>
			<?php echo $id ?>_options_upload_obj.maxFileCount 		= 1;
		<?php endif; ?>

		<?php if( ISSET( $upload['show_download'] ) AND !EMPTY( $upload['show_download'] ) ) :?>
			<?php echo $id ?>_options_upload_obj.showDownload 		= '<?php echo $upload['show_download'] ?>';
		<?php 
			else :
		?>
			<?php echo $id ?>_options_upload_obj.showDownload 		= false;
		<?php 
			endif;
		?>

		<?php 
			if( ISSET( $upload['multiple_obj'] ) AND !EMPTY( $upload['multiple_obj'] ) ) :
		?>
			// var <?php //echo $upload['id']?>_jq_obj	= $("#<?php //echo $upload['id']?>_upload");
			var <?php echo $id ?>_uploadObj = $("#<?php echo $id ?>_upload").uploadFile( <?php echo $id ?>_options_upload_obj );
		<?php 
			else :
		?>
			// var upload_jq_obj = $("#<?php //echo $upload['id']?>_upload");
			var uploadObj = $("#<?php echo $id ?>_upload").uploadFile( <?php echo $id ?>_options_upload_obj );
		<?php 
			endif;
		?>

<?php
	endforeach;
?>

</script>
<?php 
	endif;
?>
<script>
var $body = $('body');
var $loading =  $("#parser-loading");
var $modal =  $("#modal_jsviewer");
var $modalCloseBtn =  $modal.find("#modal-close-btn");
var $docxjsWrapper =$("#docxjs-wrapper");
var instance = null;

$modalCloseBtn.on("click", function(e)
{
    $docxjsWrapper.empty();
    $modal.hide();

	if( instance )
	{
		instance.destroy(function(){
			instance = null;
		});
	}

});

var getInstanceOfFileType = function(file) {
    var fileExtension = null;

    if (file) {
        var fileName = file.name;
        fileExtension = fileName.split('.').pop();
    }

    return fileExtension;
};

var documentParser = function(file) {
    var fileType = getInstanceOfFileType(file);
     
    if (fileType) {
        if (fileType == 'docx' || fileType == 'doc') {
            instance = window.docxJS = window.createDocxJS ? window.createDocxJS() : new window.DocxJS();

        } else if (fileType == 'xlsx' || fileType == 'xls') {
            instance = window.cellJS = window.createCellJS ? window.createCellJS() : new window.CellJS();

        } else if (fileType == 'pptx' || fileType == 'ppt') {
            instance = window.slideJS = window.createSlideJS ? window.createSlideJS() : new window.SlideJS();

        } else if (fileType == 'pdf') {
            instance = window.pdfJS = window.createPdfJS ? window.createPdfJS() : new window.PdfJS();
            instance.setCMapUrl('cmaps/');
        }
        
        if (instance) {
            $loading.show();
            instance.parse(
                file,
                function () {

                    $docxjsWrapper[0].filename = file.name;
                    afterRender(file, fileType);
                    $loading.hide();
                }, function (e) {
                    if(!$body.hasClass('is-docxjs-rendered')){
                        $docxjsWrapper.hide();
                    }

                    if(e.isError && e.msg){
                        alert(e.msg);
                    }

                    $loading.hide();
                }, null
            );
        }
    }
};

var afterRender = function (file, fileType) {
    var element = $docxjsWrapper[0];
    $(element).css('height','calc(100% - 65px)');

    var loadingNode = document.createElement("div");
    loadingNode.setAttribute("class", 'docx-loading');
    element.parentNode.insertBefore(loadingNode, element);

    $modal.show();

    /*$modal.openModal({
		dismissible: false,
		opacity: .5, // Opacity of modal background
		in_duration: 300, // Transition in duration
		out_duration: 200, // Transition out duration
		ready: function() {
		},
		complete: function() { 
			$docxjsWrapper.empty();
			instance.destroy(function(){
	            instance = null;
	        });
		} // Callback for Modal close
	});*/

    
    var endCallBackFn = function(result){
        if (result.isError) {
            if(!$body.hasClass('is-docxjs-rendered')){
                $docxjsWrapper.hide();
                $body.removeClass('is-docxjs-rendered');
                element.innerHTML = "";

                $modal.hide();
                $body.addClass('rendered');
            }
        } else {
        	$modal.find('p:contains("KUKUDOCS")').text('');
    		$modal.find('p:contains("Js Document Viewer")').text('');
            $body.addClass('is-docxjs-rendered');
            console.log("Success Render");
        }

        loadingNode.parentNode.removeChild(loadingNode);
    };

    if (fileType === 'docx') {
        window.docxAfterRender(element, endCallBackFn);

    } else if (fileType === 'xlsx') {
        window.cellAfterRender(element, endCallBackFn);

    } else if (fileType === 'pptx') {
        window.slideAfterRender(element, endCallBackFn, 0);

    } else if (fileType === 'pdf') {
        window.pdfAfterRender(element, endCallBackFn, 0);
    }
};


function viewerjs(obj, event)
{
	var jq_obj 	= $(obj);

	if( jq_obj.attr('data-file') )
	{
		var filename 	= jq_obj.attr('data-file');
		var ext 		= filename.substr( ( filename.lastIndexOf( '.' ) + 1 ) );
		var img_type  = "<?php echo IMAGE_EXTENSIONS ?>";
		var valid_view = ['pdf', 'xlsx', 'docx', 'pptx'];
		var valid_doc = ['docx'];
		var valid_xls = ['xlsx'];
		var valid_ppt = ['pptx'];
		var valid_pdf = ['pdf'];

		img_type 		= img_type.split(',');

		if( !(valid_view.indexOf( ext ) != -1) )
		{
			return
		} 
		else if( valid_view.indexOf(ext) != -1 )
		{
			if( typeof( WebViewer ) === 'undefined' )
			{
				if( valid_doc.indexOf(ext) != -1 )
				{
					if( typeof( window.createDocxJS ) == 'undefined' )
					{
						return;
					}
				}
				else if( valid_xls.indexOf(ext) != -1 )
				{
					if( typeof( window.createCellJS ) == 'undefined' )
					{
						return;
					}
				}
				else if( valid_ppt.indexOf(ext) != -1 )
				{
					if( typeof( window.createSlideJS ) == 'undefined' )
					{
						return;
					}
				}
				else if( valid_pdf.indexOf(ext) != -1 )
				{
					if( typeof( window.createPdfJS ) == 'undefined' )
					{
						return;
					}
				}
				else
				{
					return;
				}
			}

		}
		else if( img_type.indexOf( ext ) != -1 )
		{
			return;
		}
	}

	event.preventDefault();

	var csrf_name 	= $('meta[name="csrf-name"]').attr('content'),
	csrf_token 	= $('meta[name="csrf-token"]').attr('content');
	pass_data 	= {};

	pass_data[csrf_name]	= csrf_token;
	pass_data['js_viewer']  = 1;

	if( typeof( WebViewer ) !== 'undefined' )
	{

		WebViewer({
			path: $base_url+'static/js/webviewer/lib/'
		}, $docxjsWrapper[0])
		.then(function(instance) {
			$.post(jq_obj.attr('href'), pass_data).promise().done(function(response)
			{
				var element = $docxjsWrapper[0];
				$(element).css('height','calc(100% - 65px)');
				$modal.show();
				var file = dataURLtoFile(response, jq_obj.attr('data-file'));
				instance.loadDocument( file,  { filename: jq_obj.attr('data-file') });
			});

		});
	}
	else
	{
		$.post(jq_obj.attr('href'), pass_data).promise().done(function(response)
		{
			// response = JSON.parse(response);
			documentParser(dataURLtoFile(response, jq_obj.attr('data-file')));
		});
	}
}

function dataURLtoFile(dataurl, filename) 
{
	try
	{
	    var arr = dataurl, mime = filename.substr( ( filename.lastIndexOf( '.' ) + 1 ) ),
	        bstr = atob(dataurl), n = bstr.length, u8arr = new Uint8Array(n);
	    while(n--){
	        u8arr[n] = bstr.charCodeAt(n);
	    }
	    return new File([u8arr], filename, {type:mime});
	}
	catch( main_err )
	{
		notification_msg('error', 'file does\'nt exists.');
	}
}
</script>