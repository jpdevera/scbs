<?php 
	$disable 	= '';

	if( $action == ACTION_VIEW )
	{
		$disable = 'disabled';
	}
?>
<?php 
	if( !EMPTY( $stage_task ) ) :
?>
	<?php 
		foreach( $stage_task as $stage_id => $s_t ) :

			$stage_id_enc 	= base64_url_encode( $stage_id );
			$stage_salt 	= gen_salt();
			$stage_token 	= in_salt( $stage_id, $stage_salt );
			$stage_url 		= $stage_id.'/'.$stage_salt.'/'.$stage_token;
			$stage_obj 		= array(
				'stage_id'		=> $stage_id,
				'stage_salt'	=> $stage_salt,
				'stage_token'	=> $stage_token
			);
	?>	
	<div id="prerequisites-div" class="row m-b-n prerequisites-div" data-sequence="<?php echo $s_t['sequence_no'] ?>">
		<input type="hidden" class="stage_preq_enc" value="<?php echo $stage_id_enc ?>">
		<input type="hidden" class="stage_preq_salt" value="<?php echo $stage_salt ?>">
		<input type="hidden" class="stage_preq_token" value="<?php echo $stage_token ?>">
		<ul class="collapsible m-b-n" data-collapsible="expandable">
			<li>
				<div class="collapsible-header title-content-14 active">
			 			<div class="header-text">
			 				<?php echo $s_t['stage_name'] ?>
			 				<!-- <a class="pull-right font-lg grey-text text-lighten-2 delete_row" href="javascript:;">&#10006;</a> -->
			 			</div>
			 	</div>

			 	<div class="collapsible-body form-basic">
			 		<div class="pre-req-steps-container">
						<?php 
							if( !EMPTY( $s_t['tasks'] ) ) :
						?>
						<input type="hidden" class="preq_cnt" value="<?php echo count( $s_t['tasks'] ) ?>">
							<?php 
								foreach( $s_t['tasks'] as $t_k => $task ) :

									$step_id 		= base64_url_encode( $task['workflow_task_id'] );
									$step_salt 		= gen_salt();
									$step_token 	= in_salt( $task['workflow_task_id'], $step_salt );
									$step_url 		= $step_id.'/'.$step_salt.'/'.$step_token;
									$step_obj 		= array(
										'step_id'		=> $step_id,
										'step_salt'		=> $step_salt,
										'step_token'	=> $step_token
									);

									$step_json 	= json_encode( $step_obj );

									if( !EMPTY( $first_task ) AND $first_task['workflow_task_id'] == $task['workflow_task_id'] )
									{
										continue;
									}

									$previous_task 	= array();

									$previous_task 	= $preq_obj->get_previous_tasks( $workflow_id, $s_t['sequence_no'], $task['task_sequence'] );

									$predecessor 	= array();
									$pred_id 		= array();

									$predecessor 	= $preq_obj->get_workflow_task_predecessor( $task['workflow_task_id'] );

									if( !EMPTY( $predecessor ) )
									{
										$pred_id 	= array_column($predecessor, 'pre_workflow_task_id');
									}
							?>
							<div class="row name-connector">
								<div class="col s8 ">
									<div class="input-field ">
										<label for="stage_name" class="active"><?php echo $task['task_name'] ?></label>
										<div>
											<select <?php echo $disable ?> name="prerequisites[<?php echo $task['workflow_task_id'] ?>][]" data-parsley-group="fieldset-4" data-parsley-trigger="change" id="actor_id" class="selectize" multiple>
												<option value="">Please Select</option>
												<?php 
													if( !EMPTY( $previous_task ) ) :
												?>
													<?php 
														foreach( $previous_task as $p_t ) :

															$sel_prev = ( !EMPTY( $pred_id ) AND in_array($p_t['workflow_task_id'], $pred_id) ) ? 'selected' : '';

															$id_pt 	= base64_url_encode( $p_t['workflow_task_id'] );
													?>
													<option <?php echo $sel_prev ?> value="<?php echo $id_pt ?>"><?php echo $p_t['task_name'] ?></option>
													<?php 
														endforeach;
													?>

												<?php 
													endif;
												?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<?php 
								endforeach;
							?>
						<?php 
							else :
						?>

						<?php 
							endif;
						?>
			 		</div>
			 	</div>
			</li>
		</ul>

	</div>
	<?php 
		endforeach;
	?>
<?php 
	else :
?>
<div id="prerequisites-div" class="row m-b-n prerequisites-div" data-sequence="1">
	<ul class="collapsible m-b-n" data-collapsible="expandable">
		<li>
			<div class="collapsible-header title-content-14 active">
		 			<div class="header-text">
		 				<h6>No stage(s) avalaible</h6>
		 				<!-- <a class="pull-right font-lg grey-text text-lighten-2 delete_row" href="javascript:;">&#10006;</a> -->
		 			</div>
		 	</div>

		 	<div class="collapsible-body form-basic">
				
		 	</div>
		</li>
	</ul>

</div>
<?php 
	endif;
?>