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
							echo $terms_text;
						?>
					</p>
					
					<hr>
					<?php 
						if( $data_page_key == $num_step ) :
					?>
					<p><input id="terms_checkbox" type="checkbox" /><label for="terms_checkbox">I accept the terms &amp; conditions.</label></p>
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