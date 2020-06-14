<script type="text/javascript">
	$(function(){
		$('.tooltipped').tooltip({delay: 50});
	
		$('#app-selector').material_select();

		if($('#org-selector').length > 0) {
			$('#org-selector').material_select();
		}

		create_avatar($('.profile_avatar'), {width:80,height:80,fontSize:50});
		create_avatar($('.default-avatar'), {width:80,height:80,fontSize:30});
		
		// SHORTEN TEXT
		$(".shorten-text").each(function() {
			$(this).shorten({
				showChars: $(this).data('char'),
				moreText: "Show More",
				lessText: "Show Less"
			});
		});

		// JSCROLLPANE
		$('.scroll-pane').jScrollPane(settings).bind('mousewheel',
			function(e)
			{
				e.preventDefault();
			}
		);

		// ACCORDION MENU
		$('.collapsible').collapsible({
			accordion : false 
			/* A setting that changes the collapsible behavior to expandable 
			instead of the default accordion style */
		});

		// SCROLLSPY
		$('.scrollspy').scrollSpy();

		// TABS
		$('ul.tabs').tabs();

		// FULLSCREEN MODAL
		$(".fullscreen-trigger").each(function() {
			var target = $(this).data("modal-target");
			$(this).animatedModal({
				color:'#fff',
				modalTarget: target,
				animatedIn:'bounceInUp',
				animatedOut:'bounceOutDown'
			});
		});

		// DROPDOWN
		$('.dropdown-button').dropdown({
			inDuration: 300,
			outDuration: 225,
			constrain_width: false, // Does not change width of dropdown to that of the activator
			hover: false, // Activate on hover
			gutter: 0, // Spacing from edge
			belowOrigin: true, // Displays dropdown below the button
			alignment: 'left' // Displays dropdown with edge aligned to the left of button
		});

		// FLOAT LABEL FORM ON FOCUS FUNCTION
		$(".form-float-label input[type='text'], .form-float-label input[type='password'], .form-float-label input[type='email'], .form-float-label input[type='url'], .form-float-label input[type='time'], .form-float-label textarea.materialize-textarea").focusin(function() {
			$(this).closest(".col").css("background","#DCEAF1");
		}).focusout(function() {
			$(this).closest(".col").css("background","none");
		});
		// END FLOAT LABEL FORM ON FOCUS FUNCTION

		$(".form-float-label .parsley-error").closest(".row").css("background", "#ffebee");

		// NOTIFICATION DROPDOWN FUNCTION
		$(".top-bar-notif a").blur(function() {
			$(this).parent().removeClass("active");
		});	 

		$(".top-bar-notif a").click(function() {
			$(this).parent().addClass("active");
		});

		$("#toggle-side-sub-nav").click(function(){
			$('.cd-side-sub-nav').toggleClass('is-collapsed');
    		$('#content-wrapper').toggleClass('is-full-width');
		});

		$(".filter-submit").click(function(){
			$(this).addClass('active');
		});

		$(".filter-cancel").click(function(){
			$(".filter-submit").removeClass('active');
		});

		<?php if( ISSET( $initial ) ) : ?>

			load_initial_tab();

		<?php endif; ?>

		// END NOTIFICATION DROPDOWN FUNCTION

		<?php if(!EMPTY($resources["load_js"])){ ?>

			<!-- DATEPICKER -->
			<?php if(in_array('jquery.datetimepicker', $resources["load_js"])): ?>
				datepicker_init();
			<?php else: ?>
				<!-- DESTROY DATEPICKER TO HIDE xdsoft_datetimepicker -->
				if($('.datepicker,.datepicker_start,.datepicker_end,.timepicker').length)
					$('.datepicker,.datepicker_start,.datepicker_end,.timepicker').datetimepicker('destroy');
			<?php endif; ?>

			<!-- SELECTIZE -->
			<?php 
				if(in_array('selectize', $resources["load_js"])):
					if(ISSET($resources["selectize"]))
					{
						foreach($resources["selectize"] as $class_name => $options):
							$class = $class_name;
							$type = 'default';
							$max_items = 1;
							
							if(!EMPTY($options) and is_array($options))
							{
								$type = (ISSET($options['type'])) ? $options['type'] : $type;
								$max_items = (ISSET($options['max_items'])) ? $options['max_items'] : $max_items;
							}
			?>
				selectize_init('<?php echo $class; ?>', '<?php echo $type; ?>', '<?php echo $max_items; ?>');
			<?php
						endforeach;
					}else{
			?>
				selectize_init();
			<?php	} 	?>


				<!-- ASSIGN VALUES TO SELECT (SINGLE) -->
				<?php
				if(ISSET($resources["single"]))
				{
					foreach($resources["single"] as $k => $v):
				?>
					if($("#<?php echo $k ?>").length !== 0)
					{
						$("#<?php echo $k ?>")[0].selectize.setValue('<?php echo $v ?>');
					}
				<?php
					endforeach;
				}
				?>

				<!-- ASSIGN VALUES TO SELECT (MULTIPLE) -->
				<?php
				if(ISSET($resources["multiple"]))
				{
					foreach($resources["multiple"] as $a => $b):
						if(! EMPTY($b))
						{
							for($i = 0; $i < count($b); $i ++)
							{
				?>
								if($("#<?php echo $a ?>").length !== 0)
								{
									$("#<?php echo $a ?>")[0].selectize.addItem("<?php echo $b[$i] ?>");
								}
				<?php
							}
						}
					endforeach;
				}
			endif;
			?>

			<!-- LABELAUTY RADIO BUTTON -->
			<?php if(in_array('jquery-labelauty', $resources["load_js"])): ?>
				labelauty_init();
			<?php endif; ?>

			<!-- NUMBER FORMAT -->
			<?php if(in_array('jquery.number.min', $resources["load_js"])): ?>
				<?php 
					if( ISSET( $resources['decimal_places'] ) ) :
				?>
				var decimal_places = parseInt('<?php echo $resources['decimal_places'] ?>');
				
				number_init(undefined, decimal_places);
				<?php 
					else :
				?>
				number_init();
				<?php 
					endif;
				?>
			<?php endif; ?>

			<?php if(ISSET($resources['sumo_select'])): ?>

				<?php 
					foreach( $resources['sumo_select'] as $obj => $settings ) :
				?>

				var settings 	= JSON.parse('<?php echo json_encode($settings) ?>');

				sumo_select('<?php echo $obj ?>', settings);
				<?php 
					endforeach;
				?>

			<?php endif; ?>

			<!-- DATATABLE -->
			<?php if(ISSET($resources["datatable"])){
				$datatable = $resources["datatable"];
			?>
				<?php 
					if( ISSET( $datatable[0] ) )
					{
						
						foreach( $datatable as $table ) 
						{
				?>	
						var settings 	= JSON.parse('<?php echo json_encode($table) ?>');

						var <?php echo $table['table_id'] ?>_datatable 	= load_datatable(settings);
				<?php
						}
					}
					else
					{
				?>
				<?php 

				?>

					var settings 	= JSON.parse('<?php echo json_encode($datatable) ?>');

					var <?php echo $datatable['table_id'] ?>_datatable 	= load_datatable(settings);
				<?php
					}
				?>
			
				<?php } ?>
			<?php } else { ?>
					<?php if(ISSET($resources["datatable"])){
					$datatable = $resources["datatable"];
				?>
			<?php 
				if( ISSET( $datatable[0] ) )
				{
					
					foreach( $datatable as $table ) 
					{
			?>	
					var settings 	= JSON.parse('<?php echo json_encode($table) ?>');

					var <?php echo $table['table_id'] ?>_datatable 	= load_datatable(settings);
			<?php
					}
				}
				else
				{
			?>
			<?php 

			?>

				var settings 	= JSON.parse('<?php echo json_encode($datatable) ?>');

				var <?php echo $datatable['table_id'] ?>_datatable 	= load_datatable(settings);
			<?php
				}
			?>
		
			<?php } ?>
			
			
		<?php } ?>

		// UPLOAD FILE
		
		/*<!-- (nodejs) -->
		$("#alerts_div").load("<?php echo base_url() ?>nodejs/index.html");
		<!-- (nodejs) -->*/

		<?php if( ISSET( $resources['loaded_init'] ) AND !EMPTY( $resources['loaded_init'] ) ){
			foreach( $resources['loaded_init'] as $init ){
				echo $init; 
			}
		} ?>

		<?php if( ISSET( $resources['loaded_doc_init'] ) AND !EMPTY( $resources['loaded_doc_init'] ) ){
			foreach( $resources['loaded_doc_init'] as $init ){
				echo $init; 
			}
		} ?>
		
	});

	
</script>