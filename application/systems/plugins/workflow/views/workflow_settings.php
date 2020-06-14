<div class="page-title">
	<div class="table-display">
		<div class="table-cell valign-middle s7"><h5>Workflow Settings</h5></div>
		<div class="col s5 valign-middle right-align p-r-n">
			<a type="button" id="btn_view_gpb" class="waves-effect waves-light btn grey darken-1 m-r-xs white-text" href="<?php echo base_url().CORE_WORKFLOW.'/Manage_workflow' ?>"><i class="material-icons">arrow_back</i>Back</a>
		</div>
	</div>
</div>

 <div id="" class="tab-content col s12 active">
 	<div class="row">
		 <div class="col l10 m12 s12">
			<form id="workflow_setting_form" class="m-t-lg">

				<div class="form-basic">

					<div id="account" class="scrollspy table-display white box-shadow">
						<div class="table-cell bg-dark p-lg valign-top" style="width:25%">
							<label class="label mute">Worflow</label>
							<p class="caption m-t-sm white-text">Manage settings of system worklow.</p>
	  					</div>
						<div class="table-cell p-lg valign-top">
							<div class="row m-b-n">
								<div class="col s12">
									<div>
	  									<h6>Workflow Tabs</h6>
										<div class="help-text">Manage settings of system worklow.</div>
									</div>
					
									<div class="row">
					  					<div class="col l2 m2 s12">
											
					  					</div>
					  					<div class="col l3 m3 s12">
											<b>Display Name</b>
					  					</div>
					  					<div class="col l4 m4 s12">
											<b>Description</b>
					  					</div>
					  					<div class="col l3 m3 s12">
											<!-- <b>Is Enabled ?</b> -->
					  					</div>
									</div>	

									<?php 
										if( !EMPTY( $workflow_settings ) ) :
									?>			
										<?php 
											foreach( $workflow_settings as $w_s ) :

												$desc_name 	= $w_s['setting_name'].'_description';
												$check_name = $w_s['setting_name'].'_flag';

												$desc 		= get_setting( WORKFLOW_DESCRIPTION, $desc_name );

												$check 		= get_setting( WORKFLOW_FLAG, $check_name );

												$dis_tab 	= '';
												$check_tab 	= '';

												$read_stage = '';

												if( $w_s['setting_name'] != 'stages' )
												{
													$dis_tab = 'disabled';
												}

												$pars_req 		= 'data-parsley-required="true"';
												$pars_keyup 	= 'data-parsley-trigger="keyup"';
												
												if( EMPTY( $check ) )
												{
													$read_stage = 'readonly="readonly"';
													$pars_req 	= '';
													$pars_keyup = '';
												}

												if( !EMPTY( $check ) )
												{
													$check_tab 	= 'checked';
												}
										?>
										<div class="row">
											<div class="col l2 m2 s12">
												<?php 
													echo convert_ucwords( $w_s['setting_name'] );
												?>
						  					</div>
						  					<div class="col l3 m3 s12">
												<input <?php echo $pars_req ?> <?php echo $pars_keyup ?> name="<?php echo $w_s['setting_name'] ?>" <?php echo $read_stage ?> id="<?php echo $w_s['setting_name'] ?>_id" value="<?php echo $w_s['setting_value'] ?>" type="text">
						  					</div>
						  					<div class="col l4 m4 s12">
												<textarea id="<?php echo $desc_name ?>_id" <?php echo $read_stage ?> name="<?php echo $desc_name ?>" class="materialize-textarea"><?php echo $desc ?></textarea>
						  					</div>
						  					<div class="col l3 m3 s12">
												 <input type="checkbox" <?php echo $check_tab ?> <?php echo $dis_tab ?> class="labelauty" name="<?php echo $check_name ?>" id="<?php echo $check_name ?>_id" value="" data-labelauty="Disabled|Enabled" />
						  					</div>
										</div>
										<?php 
											endforeach;
										?>

									<?php 
										endif;
									?>
				  				</div>
							</div>
			  			</div>

					</div>
					<div class="panel-footer right-align">
				    	<div class="input-field inline m-n">
					  		<button class="btn waves-effect waves-light bg-success" type="button" id="save_workflow_setting" value="<?php echo BTN_SAVING ?>" data-btn-action="<?php echo BTN_SAVING; ?>"><?php echo BTN_SAVE ?></button>
						</div>
				  	</div>
				</div>

			</form>
		 </div>
 	</div>
 </div>