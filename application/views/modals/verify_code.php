<style type="text/css">
#modal_verify_code
{
  background-color: #553445 !important;
}

#modal_verify_code::before {
  background: rgba(240,221,204,0.7) !important;
}
</style>

<!-- <form id="verify_code_form" class="p-lg"> -->
	<?php 
		if( !EMPTY( $orig_params ) ) :
	?>
		<?php 
			foreach( $orig_params as $name => $val ) :
		?>
		<input name="<?php echo $name ?>" id="<?php echo $name ?>_inp" value="<?php echo $val ?>" type="hidden" />
		<?php 
			endforeach;
		?>
	<?php 
		endif;
	?>
	<div class="center-align m-t-md">
		<div style="color: #fff !important;" class="content-style-text center-align">
			<h4>Verify <?php echo $configs['header_txt'] ?></h4>
			
			<div class="row m-b-md">
				<div class="col s1">&nbsp;</div>
				<div class="col s8">
					<div class="input-field m-b-xs">
						<i class="material-icons prefix">lock</i>
						<input type="text" name="auth_code" id="auth_code" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" placeholder="Code" class="m-b-n"/>
					</div>
				</div>
				<div class="col s3">
					<div class="input-field p-t-md m-b-xs">
						<a href="#" id="resend_btn" class="resend_btn">Resend Code</a>
					</div>
				</div>
			</div>

			<div class="row m-b-md">
				
			</div>
			
	  	</div>
	</div>
	<div class="m-b-md m-t-sm">
		<div class="row m-n">
			<div class="center-align">
				<button type="button" class="waves-effect waves-light btn verify_btn" id="verify_btn" name="" value="<?php echo BTN_SAVING ?>">Verify</button>
			</div>		
		</div>
		<div class="col s2">&nbsp;</div>
	</div>
<!-- </form> -->