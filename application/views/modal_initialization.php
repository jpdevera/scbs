<script type="text/javascript">
	var modal_height = {};
</script>

<!-- For Delete -->
<?php if( ISSET( $resources['load_delete'] ) AND !EMPTY( $resources['load_delete'] ) ): ?>
	<script>
		<?php foreach( $resources['load_delete'] as $key => $options ) :
			$delete_cntrl 		= '';
			$delete_method 		= 'delete';
			$delete_module 		= CORE_USER_MANAGEMENT;

			if( is_numeric( $key ) ) :
				$delete_cntrl 		= ISSET( $resources['load_delete'][0] ) ? $resources['load_delete'][0] : '';
				$delete_method 		= ISSET( $resources['load_delete'][1] ) ? $resources['load_delete'][1] : 'delete';
				$delete_module 		= ISSET( $resources['load_delete'][2] ) ? $resources['load_delete'][2] : CORE_USER_MANAGEMENT;

			if( $key == 0 ) :
			?>
				var options_delete = {};

				options_delete.controller 	= '<?php echo $delete_cntrl ?>';
				options_delete.method 		= '<?php echo $delete_method ?>';
				options_delete.module 		= '<?php echo $delete_module ?>';

				var deleteObj 	= new handleData( options_delete );
			<?php endif; ?>
			<?php else : ?>
				var options_delete = {},
				<?php echo $key.'Obj' ?>;

				<?php 
				if( ISSET( $options['delete_cntrl'] ) ) :
					$delete_cntrl 	= $options['delete_cntrl'];
				endif;
				if( ISSET( $options['delete_method'] ) ) :
					$delete_method 	= $options['delete_method'];
				endif;
				if( ISSET( $options['delete_module'] ) ) :
					$delete_module 	= $options['delete_module'];
				endif;
				?>

				options_delete.controller  = "<?php echo $delete_cntrl ?>";
				options_delete.method	   = "<?php echo $delete_method ?>";
				options_delete.module 	   = "<?php echo $delete_module ?>";

				<?php echo $key.'Obj' ?> 	= new handleData( options_delete );

				function <?php echo 'content_'.$key.'_delete' ?>( alert_text, param_1, param_2, obj, succ_callback, msg_detail, event )
				{
					if( event !== undefined )
					{
						event.stopImmediatePropagation();
						event.preventDefault();
					}
					
					var param_2 		= param_2 || "";
					var extra_data 		= {};
					var self 			= obj;
					var msg_obj 		= {
						'h4' : 'Are you sure you want to delete this ' + alert_text + '?',
						'p'  : 'This action will permanently delete this record from the database and cannot be undone.'
					}

					$('#confirm_modal').confirmModal({
						topOffset : 0,
						onOkBut : function() {
							
							if( obj !== undefined )
							{
								var extra 	  = $(obj).attr("data-delete_post");
								
								if( extra != null && extra != '' )
								{
									extra_data 	  = JSON.parse(extra);
								}
							}

							var post_data 		= { 
								param_1 : param_1, 
								param_2 : param_2 
							};

							post_data 			= $.extend( post_data, extra_data )

							<?php echo $key.'Obj' ?>.removeData(post_data, succ_callback, self);
						},
						onCancelBut : function() {},
						onLoad : function() {
							if( msg_detail !== undefined )
							{
								if( typeof( msg_detail ) === 'string' )
								{
									msg_detail = JSON.parse(msg_detail);
								}

								msg_obj 	= $.extend( msg_obj, msg_detail );
							}

							for( var key in msg_obj )
							{
								$('.confirmModal_content '+key).html( msg_obj[ key ] );
							}
						},
						onClose : function() {}
					});
				}
			<?php endif; ?>
		<?php endforeach; ?>
	</script>
<?php endif; ?>