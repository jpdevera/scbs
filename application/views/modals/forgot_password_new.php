<div class="wizard-form <?php echo $class_step ?>">
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
	<!-- <form id="sub_form"> -->
		<?php 
			if( !EMPTY( $segments ) ) :
		?>
			<?php 
				foreach( $segments as $seg_key => $segment ) :

					$data_page_key 	= ( $seg_key + 1 );

					$pass_data['data_page_key']	= $data_page_key;
			?>
			<fieldset id="<?php echo $segment ?>_form" class="wcontent" data-page="<?php echo $data_page_key ?>">
				<input type="hidden" name="data_page_key" value="<?php echo $data_page_key ?>">
				<?php $this->view('tabs/'.$segment, $pass_data); ?>
			</fieldset>
			<?php 
				endforeach;
			?>
		<?php 
			endif;
		?>
		<fieldset style="display:none !important;" id='main_fieldset'>
			<?php 
				if( !EMPTY( $orig_params ) ) :
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
	<!-- </form> -->
</div>

<div class="m-b-md m-t-md">
	<div class="row m-n">
		<div class="col s2">&nbsp;</div>
	 	<div class="col s8">
		 	<div class="center-align m-t-lg">
				<a href="javascript:;" onclick='$("#modal_forgot_pw").modal("close");' class="modal-action modal-close m-t-md inline">&larr; Back to account login.</a>
			</div>	
		 </div>
		 <div class="col s2">&nbsp;</div>
	</div>
</div>