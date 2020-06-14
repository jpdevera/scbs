<div class="p-md form-basic left-align" style="color: #fff !important;" class="content-style-text center-align">
	<div class="row m-b-n">
	</div>
	<div class="row m-b-n">
		<div class="col s12 black-text">
			<div class="center-align">
				<div >
					<!-- <h4>Terms and Conditions</h4> -->
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
					<h6>File(s)</h6>
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
					<?php 
						if( $data_page_key == $num_step ) :
					?>
					<p><input id="terms_checkbox" type="checkbox" /><label for="terms_checkbox">I agree.</label></p>
					<div class="m-b-md m-t-xs">
						<div class="row m-n">
							<div class="center-align">
								<button type="button" style="display:none !important;" class="waves-effect waves-light btn terms-proceed" id="terms_btn" name="" value="<?php echo BTN_SAVING ?>">Proceed</button>
							</div>		
						</div>
						<div class="col s2">&nbsp;</div>
					</div>
					<?php 
						endif;
					?>
			  	</div>
			</div>
		</div>
	</div>
</div>
<?php 
	if( $data_page_key != $num_step ) :
?>
<input type="button" name="next" id="process_save" data-action="Terms.common_move(animate_next, next_fs, current_fs, self);" class="next action-button" value="Next" />
<?php 
	endif;
?>