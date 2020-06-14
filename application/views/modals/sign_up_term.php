<!-- <style type="text/css">
#modal_term_condition
{
  background-color: #553445 !important;
}

#modal_term_condition::before {
  background: rgba(240,221,204,0.7) !important;
}
</style> -->

<form id="term_condition_form" class="p-lg">
	<div>
		<div class="row tab-default">
			<div class="col s12 p-n">
				<ul class="tabs m-b-md">
					<?php 
						if( !EMPTY( $agreement_text ) ) :
					?>
					<li class="tab col s6"><a id="link_tab_agreement_text" href="#tab_agreement_text">Privacy Statement</a></li>
					<?php 
						endif;
					?>
					<?php 
						if( !EMPTY( $terms_text ) ) :
					?>
					<li class="tab col s6"><a id="link_tab_terms_text" href="#tab_terms_text">Terms and Conditions</a></li>
					<?php 
						endif;
					?>
				</ul>
			</div>

			<div id="tab_agreement_text" class="col s12 tab-content">
				<div class="center-align m-t-md">
					<div style="" class="content-style-text center-align">
						<h3>Data Privacy</h3>
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
						<h6>Privacy Statement File(s)</h6>
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
					</div>
				</div>
			</div>

			<div id="tab_terms_text" class="col s12 tab-content">
				<div class="center-align m-t-md">
					<div style="" class="content-style-text center-align">
						<h3>Terms and Conditions</h3>
						<hr>
						<p>
							<?php 
								echo $terms_text;
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>