<style type="text/css">
#modal_term_condition
{
  background-color: #553445 !important;
}

#modal_term_condition::before {
  background: rgba(240,221,204,0.7) !important;
}
</style>

<form id="term_condition_form" class="p-lg">
	<input type="hidden" name="sign_up_check" id="sign_up_check" value="<?php echo $sign_up ?>">
	<div class="center-align m-t-md">
		<div style="color: #fff !important;" class="content-style-text center-align">
			<h2>Data Privacy</h2>
			<hr>
			<p>
				<?php 
					echo $agreement_text;
				?>
			</p>
			<?php 
				if( !EMPTY( $agreement_uploads ) ) :

					$upl_str 	= "";
			?>
			<h6>Terms and Conditions File(s)</h6>
			<p>
			<?php
					foreach( $agreement_uploads as $aggr_upl ) :

						$aggr_upl_det 	= explode('=', $aggr_upl);

						$path 			= FCPATH.PATH_TERM_CONDITIONS_UPLOADS.$aggr_upl_det[0];
						$path 			= str_replace(array('\\', '/'), array(DS, DS), $path);

						$pathinfo 		= pathinfo( $path );
						$extension 		= strtolower( $pathinfo['extension'] );

						$view_path 		= base_url().'auth/get_term_condition_file?file='.$aggr_upl_det[0];

						$upl_str .= "<a href='".$view_path."' target='_blank' class='".$extension."'>".$aggr_upl_det[1]."</a>, ";
						
			?>
			<?php 
					endforeach;

					$upl_str = rtrim( $upl_str, ", " );
			?>
			<?php echo $upl_str ?>
			</p>
			<?php 
				endif;
			?>
			<hr>
			<p><input id="terms_checkbox" type="checkbox" /><label for="terms_checkbox">I accept the terms &amp; conditions.</label></p>
	  	</div>
	</div>
	<div class="m-b-md m-t-xs">
		<div class="row m-n">
			<div class="center-align">
				<button type="button" style="display:none !important;" class="waves-effect waves-light btn terms-proceed" id="terms_btn" name="" value="<?php echo BTN_SAVING ?>">Proceed</button>
			</div>		
		</div>
		<div class="col s2">&nbsp;</div>
	</div>
</form>