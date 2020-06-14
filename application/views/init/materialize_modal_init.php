<?php
if(ISSET($resources['preload_modal']) and ! EMPTY($resources['preload_modal']))
{
	?>
		<script type="text/javascript">
			$(function(){
				<?php foreach($resources['preload_modal'] as $modal_id): ?>
					<?php echo $modal_id ?>_init();
				<?php endforeach; ?>
			});
		</script>
<?php } ?>

<?php
if(ISSET($resources['preload_eval_modal']) and ! EMPTY($resources['preload_eval_modal']))
{
	?>
		<script type="text/javascript">
			$(function(){
				<?php foreach($resources['preload_eval_modal'] as $modal_id): ?>
					eval('<?php echo $modal_init ?>');
				<?php endforeach; ?>
			});
		</script>
<?php } ?>

<?php
	if(ISSET($resources['load_materialize_modal']) and ! EMPTY($resources['load_materialize_modal']))
	{
		foreach($resources['load_materialize_modal'] as $modal_id => $options):
			$id = $modal_id;
			
			if(! EMPTY($options) and is_array($options))
			{
				$modal_style = (ISSET($options['modal_style'])) ? $options['modal_style'] : "";
				$modal_header_icon = (ISSET($options['modal_style']) && ISSET($options['modal_header_icon'])) ? $options['modal_header_icon'] : "";
				$save_btn_label = (ISSET($options['multi_save']) && ($options['multi_save'])) ? SAVE_CLOSE: BTN_SAVE;
				$fixed_header = (ISSET($options['fixed_header']) && !($options['fixed_header'])) ? "" : "modal-fixed-header";
				$title = (ISSET($options['title'])) ? $options['title'] : "";
				$size = (ISSET($options['size'])) ? $options['size'] : "";
				$multi_save = (ISSET($options['multi_save']) && ($options['multi_save'])) ? TRUE : FALSE;
				$has_scroll = (ISSET($options['has_scroll']) && !($options['has_scroll'])) ? "" : "scroll-pane scroll-dark";
				$modal_footer = (ISSET($options['modal_footer']) && !($options['modal_footer'])) ? FALSE : TRUE;
				$bottom_modal = (ISSET($options['bottom_modal']) && !EMPTY($options['bottom_modal'])) ? "bottom-sheet custom-modal" : "";
				$not_form 	  = (ISSET($options['not_form']) && !EMPTY($options['not_form'])) ? $options['not_form'] : "form";	
				$has_min_max  = (ISSET($options['has_min_max']) && !EMPTY($options['has_min_max'])) ? TRUE : FALSE;	
				$modal_page   = (ISSET($options['modal_page']) && !EMPTY($options['modal_page'])) ? $options['modal_page'] : "";	
				$footer_div_none   = (ISSET($options['footer_div_none']) && !EMPTY($options['footer_div_none'])) ? $options['footer_div_none'] : FALSE;	


				$modal_materialize		= 'modal-materialize';

				$modal_close = (ISSET($options['modal_close']) && !($options['modal_close'])) ? FALSE : TRUE;
				$cancel_btn_flag = (ISSET($options['cancel_btn_flag']) && !($options['cancel_btn_flag'])) ? FALSE : TRUE;
				$header_height = (ISSET($options['modal_style']) && ($options['modal_style'] == 'modal-icon')) ? 70 : 60;
				$footer_height = (ISSET($options['modal_style']) && ($options['modal_style'] == 'modal-icon')) ? 60 : 56;

				if ( $modal_style === 'modal-closed-question' )
				{
					$modal_footer = FALSE;
					$fixed_header = FALSE;
				}

				$header_footer_total = $header_height + $footer_height;

				if( !EMPTY( $bottom_modal ) )
				{
					$modal_materialize 	= '';
				}

				$custom_button = (ISSET($options['custom_button'])) ? $options['custom_button'] : array(BTN_SAVE => array("type" => "submit", "action" => BTN_SAVING));
				$permission 	= TRUE;
				
				if( ISSET( $options['permission'] ) )
				{
					$permission = $options['permission'];
				}

				if( !$modal_footer )
				{
					$footer_div_none = TRUE;
					$header_footer_total = 0;
				}
				
				if($modal_footer)
				{
					if(!EMPTY($fixed_header))
						$content_height = "height:calc(100% - ".$header_footer_total."px)";
					else
						$content_height = "height:calc(100% - ".$header_footer_total."px)";
				}else{
					if(!EMPTY($fixed_header))
					{
						if($footer_div_none)
							$content_height = "height:calc(100% - ".$header_height."px)";
						else
							$content_height = "height:calc(100% - ".$header_footer_total."px)";
					}else{
						$content_height = "height:calc(100% - ".$header_footer_total."px)";
					}
				}

				if( !EMPTY( $bottom_modal ) )
				{
					$content_height = "height:calc(100% - 150px)";
				}

				if( !EMPTY( $modal_page ) )
				{
					switch( $modal_page )
					{
						case 'comment':
							$content_height = "height:calc(100% - 76px)";

							if( !EMPTY( $bottom_modal ) )
							{
								$content_height = "height:calc(100% - 256px)";
							}

						break;
					}
				}
			}
?>
			<div id="<?php echo $id; ?>" class="modal <?php echo $modal_materialize ?> <?php echo $modal_style ?> modal-fixed-footer <?php echo $fixed_header; ?> <?php echo $bottom_modal ?> <?php echo $size; ?>">

				<?php if ( $modal_style === 'modal-closed-question' ): ?>
					<a href="javascript:;" class="modal-action modal-close">&times;</a>
				<?php endif; ?>

				<?php if(!EMPTY($fixed_header)){ ?>
					<div class="modal-header">
						<?php if(!EMPTY($modal_header_icon)) { ?>
							<i class="material-icons"><?php echo $modal_header_icon; ?></i>
						<?php } ?>
						<span id="<?php echo $id ?>_title">
							<?php echo $title; ?>
						</span>
						<?php if($modal_close) { ?>
						<a href="javascript:;" class="modal-action modal-close">&times;</a>
						<?php } ?>
						<?php 
							if( $has_min_max ) :
						?>
							<a href="javascript:;" class="modal-maximize">
								<?php 
									if( $size != 'full' ) :
								?>
								<i class="material-icons min_max circle" id="modal_max" data-min_max="max">fullscreen</i>
								<i style="display : none !important" data-min_max="min" id="modal_min" class="material-icons min_max circle">fullscreen_exit</i>
								<?php 
									else :
								?>
								<i style="display : none !important" class="material-icons min_max circle" id="modal_max" data-min_max="max">fullscreen</i>
								<i data-min_max="min" id="modal_min" class="material-icons min_max circle">fullscreen_exit</i>
								<?php 
									endif;
								?>								
							</a>
						<?php endif; ?>
						
					</div>
					<?php 
						if( !EMPTY( $modal_page ) ) :
					?>
						<?php 
							switch( $modal_page ) :
								case 'comment':								
						?>
						<div class="modal-header comment">
							<div class="row">
								<div class="col s10">
									&nbsp;
								</div>
								<div class="col s2">
									<select id="select_<?php echo $id ?>" class="selectize left-align">
										<option value="">Please Select</option>
										<?php 
											$comment_opt 	= unserialize( COMMENT_OPTION );

											if( $comment_opt ) :
										?>
										<?php 
												$cnt_com 	= 0;

												foreach( $comment_opt as $k => $c_o ) :

													$com_sel 		= "";

													if( $cnt_com == 0 )
													{
														$com_sel 	= "selected";
													}

													$id_c 	= base64_url_encode( $k );
										?>
										<option value="<?php echo $id_c ?>" <?php echo $com_sel ?> ><?php echo $c_o ?></option>
										<?php 
													$cnt_com++;

												endforeach;
										?>
										<?php 
											else :
										?>

										<?php 
											endif;
										?>
									</select>
								</div>
							</div>
						</div>
						<?php 
								break;
						?>

						<?php 
							endswitch;
						?>
					<?php 
						endif;
					?>
				<?php } ?>
				<<?php echo $not_form ?> id="form_<?php echo $id; ?>">
					<div class="modal-content <?php echo $has_scroll ?>" style="<?php echo $content_height ?>"><div id="content"></div></div>
					
					<?php if($modal_footer) { ?>
					<div class="modal-footer right-align">
						<?php if($cancel_btn_flag) { ?>
						<a href="javascript:;" class="btn-flat modal-action modal-close m-n m-r-xs"><?php echo BTN_CANCEL ?></a>
						<?php } ?>
						<?php 
							if( $permission ) 
							{
						?>
						<?php if($multi_save) { ?>
							<button type="submit" class="btn m-n m-r-xs green lighten-1" id="save_add_<?php echo $id; ?>" data-save="<?php echo SAVE_ADD ?>" data-btn-action="<?php echo BTN_SAVING; ?>"><?php echo 'Save and Add Another' ?></button>
						<?php } ?>
						<?php foreach ($custom_button as $key => $val): 
							$class_str = ( ISSET( $val['class'] ) ) ? $val['class'] : '';
							$real_id 	= $id;
							
							if( !EMPTY( $class_str ) )
							{
								$real_id = $id.'_'.$class_str;
							}
						?>
							<button type="<?php echo $val["type"] ?>" data-save="<?php echo SAVE_CLOSE ?>" id="submit_<?php echo $real_id; ?>" class="<?php echo $class_str ?> modal-action btn m-n" data-btn-action="<?php echo $val["action"]; ?>"><?php echo $key; ?></button>
						<?php endforeach; ?>	
						<?php 
							}
						?>	
					</div>
					<?php } else { ?>
						<?php 
							if( !$footer_div_none ) 
							{
						?>
						<div class="modal-footer">

						</div>
						<?php 
							}
						?>
					<?php } ?>
				</<?php echo $not_form ?>>
			</div>
<?php endforeach; ?>

	<script type="text/javascript">
<?php		
		foreach($resources['load_materialize_modal'] as $modal_id => $options):
			$id = $modal_id;
			
			$dismissible = (ISSET($options['dismissible']) && ($options['dismissible'])) ? true : false;
			$modal_type = (ISSET($options['modal_type'])) ? $options['modal_type'] : "default";
			
			$module = (ISSET($options['module'])) ? $options['module'] : "";
			$controller = strtolower($options['controller']);
			$method = (ISSET($options['method'])) ? $options['method'] : "modal";		
			$drag 	= (ISSET($options['draggable']) AND !EMPTY($options['draggable'])) ? true : false;	
			$resize = (ISSET($options['resizable']) AND !EMPTY($options['resizable'])) ? true : false;

			$given_url 	= (ISSET($options['given_url']) AND !EMPTY($options['given_url'])) ? true : false;
			$custom_title = (ISSET($options['custom_title']) AND !EMPTY($options['custom_title'])) ? $options['custom_title'] : '';
			$title 		= (ISSET($options['title']) AND !EMPTY($options['title'])) ? $options['title'] : '';

			$real_title 	= ( !EMPTY($title) ) ? $title : $custom_title;
			$succCallback 	= (ISSET($options['succCallback']) AND !EMPTY($options['succCallback'])) ? $options['succCallback'] : '';
			$complete_callback = (ISSET($options['complete_callback']) && !EMPTY($options['complete_callback'])) ? $options['complete_callback'] : '';

			if( !ISSET($options['draggable']) )	
			{
				$drag 	= true;
			}

			if( !ISSET($options['resizable']) )	
			{
				$resize = true;
			}
?>		
		var <?php echo $id; ?>_object = new handleModal({ 
			<?php if($dismissible){ ?>
				dismissible: true,
			<?php } ?>
			controller : '<?php echo $controller; ?>', 
			modal_id: '<?php echo $id; ?>', 
			module: '<?php echo $module ?>', 
			modal_type: '<?php echo $modal_type; ?>',
			method: '<?php echo $method; ?>',
			draggable : '<?php echo $drag ?>',
			resizable : '<?php echo $resize ?>',
			succCallback : '<?php echo $succCallback ?>',
			complete_callback : '<?php echo $complete_callback ?>',
		});
		
		<?php echo $id; ?>_init('<?php echo $given_url ?>', this, '<?php echo $real_title ?>');
		
		
		function <?php echo $id; ?>_init(data_id, obj, title_header)
		{
			var data_id = data_id || "";
			var extra_data = {};
			var title 		= title_header || '<?php echo $real_title ?>';

			/*if( obj !== undefined && typeof( obj ) === 'string' )
			{
				title 	= obj;
			}*/
			
			<?php 
				if(ISSET($options['post']) AND !EMPTY($options['post'])) :
			?>

			if( obj !== undefined )
			{
				var extra 	  = $(obj).attr("data-modal_post");

				if( $(obj).attr('data-modal_title')  )
				{
					title 	  = $(obj).attr('data-modal_title');
				}
				
				if( extra != null && extra != '' )
				{
					extra_data 	  = JSON.parse(extra);
				}
			}

			<?php echo $id; ?>_object.loadModalPost({ id: data_id, extra_data : extra_data, title : title});
			<?php 
				else :
			?>
				<?php echo $id; ?>_object.loadModal({ id: data_id, title : title});
			<?php 
				endif;
			?>
		}

<?php endforeach; ?>
	</script>
<?php } ?>