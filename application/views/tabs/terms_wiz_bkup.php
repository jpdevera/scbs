<!-- <div class="wizard-form <?php echo $class_step ?>">
	<ul id="progressbar" >
		<?php 
			if( !EMPTY( $lists ) ) :
		?>
			<?php 
				foreach( $lists as $key => $list ) :

					$cl_ac 	= ( $key == 0 ) ? 'active' : '';
			?>
			<li class="<?php echo $cl_ac ?>"><?php echo $list ?></li>
			<?php 
				endforeach;
			?>
		<?php 
			endif;
		?>
	</ul>

	<?php 
		if( !EMPTY( $segments ) ) :
	?>
		<?php 
			foreach( $segments as $seg_key => $segment ) :

				$data_page_key 	= ( $seg_key + 1 );

				$pass_data['data_page_key']	= $data_page_key;
		?>
		<fieldset id="<?php echo $segment ?>_form" class="wcontent" data-page="<?php echo $data_page_key ?>">
			<input type="hidden" name="data_page_key" value="<?php echo $data_page_key ?>" style="background-color: #553445 !important;">
			<?php $this->view('tabs/'.$segment, $pass_data); ?>
		</fieldset>
		<?php 
			endforeach;
		?>
	<?php 
		endif;
	?>
	<fieldset style="display:none !important;" id='term_condition_form'>
		<input type="hidden" name="sign_up_check" id="sign_up_check" value="<?php echo $sign_up ?>">
		<?php 
			if( ISSET($orig_params) AND !EMPTY( $orig_params ) ) :
		?>
			<?php 
				foreach( $orig_params as $name => $val ) :
			?>
			<input type="hidden" id="<?php echo $name ?>_inp" name="<?php echo $name ?>" value="<?php echo $val ?>">
			<?php 
				endforeach;
			?>
		<?php 
			endif;
		?>
	</fieldset>
</div> -->