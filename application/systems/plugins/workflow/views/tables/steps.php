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
	<div class="steps-header" data-parent="<?php echo $s_t['sequence_no'] ?>">
		<input type="hidden" class="stage_step_enc" value="<?php echo $stage_id_enc ?>">
		<input type="hidden" class="stage_step_salt" value="<?php echo $stage_salt ?>">
		<input type="hidden" class="stage_step_token" value="<?php echo $stage_token ?>">
		<ul class="collapsible" data-collapsible="expandable">
			<li>
				<div class="collapsible-header title-content-14 active">
					<div class="header-text">
						<div class="black-text text-uppercase">
							<b><?php echo $s_t['stage_name'] ?></b>
						</div>
					</div>
				</div>
				<div class="collapsible-body form-basic">
					<div class="steps-container">
						<?php 
							if( !EMPTY( $s_t['tasks'] ) ) :
						?>
						<input type="hidden" class="step_cnt" value="<?php echo count( $s_t['tasks'] ) ?>">
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

									$checked_version 	= "";
									$hid_ver_val 		= "";

									$checked_get 		= "";
									$hid_get_val 		= "";

									$ver_fl 			= ENUM_NO;
									$get_fl 			= ENUM_NO;

									$ver_fl  			= ( ISSET( $task['version_flag'] ) ) ? $task['version_flag'] : ENUM_NO;
									$get_fl  			= ( ISSET( $task['get_flag'] ) ) ? $task['get_flag'] : ENUM_NO;
									$appl_type 			= ( ISSET( $task['approval_type'] ) ) ? $task['approval_type'] : "";

									if( $ver_fl == ENUM_YES )
									{
										$checked_version	= "checked";
										$hid_ver_val 		= "1";
									}

									if( $get_fl == ENUM_YES )
									{
										$checked_get		= "checked";
										$hid_get_val 		= "1";
									}

									$role_arr 				= array();
									$append_arr 			= array();

									if( !EMPTY( $task['actor_role_codes'] ) )
									{
										$role_arr 			= explode('|', $task['actor_role_codes']);
									}

									if( !EMPTY( $task['append_wf'] ) )
									{
										$append_arr 		= explode('|', $task['append_wf']);
									}

							?>
							<div id="steps-div" class="row m-b-n ul-collapsible steps-div" data-sequence="<?php echo $task['task_sequence'] ?>">
								<ul class="collapsible m-b-n" data-collapsible="expandable">
									<li>
									 	<div class="collapsible-header title-content-14 active">
									 		<?php 
							 					if( EMPTY( $disable ) ) :
							 				?>
									 		<i class="material-icons handle">drag_handle</i>
									 		<?php 
									 			endif;
									 		?>
									 			<div class="header-text">
									 				Step <?php echo $task['task_sequence'] ?>
									 				<?php 
									 					if( EMPTY( $disable ) ) :
									 				?>
									 				<a class="pull-right font-lg grey-text text-lighten-2 delete_row" onclick="content_workflow_step_delete('Step', '<?php echo $step_url ?>', undefined, this, undefined, undefined, event ); return false;" data-delete_post='<?php echo $step_json ?>' href="javascript:;">&#10006;</a>
									 				<?php 
									 					endif;
									 				?>
									 			</div>
									 	</div>
										<div class="collapsible-body form-basic">
											<div class="row">
												<div class="col s8">
													<div class="input-field">
														<label for="step_name" class="active required">Step Name</label>
														<div>
															<input type="hidden" class="step_task" value="<?php echo $task['workflow_task_id'] ?>">
															<input type="hidden" class="step_sequence" name="step_sequence[<?php echo $stage_id ?>][]" value="<?php echo $task['task_sequence'] ?>" id="step_sequence">
															<input type="hidden" class="step_enc" name="workflow_step[<?php echo $stage_id ?>][]" value="<?php echo $step_id ?>">
															<input type="hidden" class="step_salt" name="workflow_step_salt[<?php echo $stage_id ?>][]" value="<?php echo $step_salt ?>">
															<input type="hidden" class="step_token" name="workflow_step_token[<?php echo $stage_id ?>][]" value="<?php echo $step_token ?>">

															<input type="text" <?php echo $disable ?> name="step_name[<?php echo $stage_id ?>][]" data-parsley-maxlength="100" data-parsley-group="fieldset-3" data-parsley-required="true" value="<?php echo $task['task_name'] ?>" data-parsley-trigger="keyup" id="step_name">
														</div>
													</div>
												</div>
												<div class="col s4">
													<div class="input-field">
														<label for="turnaround_time" class="active required">Turnaround Time</label>
														<div>
															<input type="text" <?php echo $disable ?> data-parsley-group="fieldset-3" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number" name="turnaround_time[<?php echo $stage_id ?>][]" value="<?php echo $task['tat_in_days'] ?>" class="number" id="turnaround_time">
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col s6">
													<div class="input-field">
														<label for="actor_id" class="active required">Actor</label>
														<div>
															<select <?php echo $disable ?> name="actor[<?php echo $stage_id ?>][<?php echo $task['workflow_task_id'] ?>][]" data-parsley-required="true" data-parsley-trigger="change" id="actor_id" class="selectize saved_data sub_multiple" multiple>
																<option value="">Please Select</option>
																<?php 
																	if( !EMPTY( $roles ) ) :
																?>
																	<?php 
																		foreach( $roles as $r ) :

																			$id_role 	= base64_url_encode( $r['role_code'] );

																			$sel_role 	= ( !EMPTY( $role_arr ) AND in_array($r['role_code'], $role_arr ) ) ? 'selected' : '';
																	?>
																	<option <?php echo $sel_role ?> value="<?php echo $id_role ?>"><?php echo $r['role_name'] ?></option>
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
												<div class="col s6">
													<div class="input-field">
														<label for="appendable_id" class="active">Attachable Process</label>
														<div>
															<select <?php echo $disable ?> name="appendable[<?php echo $stage_id ?>][<?php echo $task['workflow_task_id'] ?>][]" data-parsley-trigger="change" id="appendable_id" class="selectize sub_multiple saved_data" multiple>
																<option value="">Please Select</option>
																<?php 
																	if( !EMPTY( $append_wf ) ) :
																?>
																	<?php 
																		foreach( $append_wf as $wf ) :

																			$id_wf 	= base64_url_encode( $wf['workflow_id'] );

																			$sel_wf = ( !EMPTY( $append_arr ) AND in_array($wf['workflow_id'], $append_arr ) ) ? 'selected' : '';
																	?>
																	<option <?php echo $sel_wf ?> value="<?php echo $id_wf ?>"><?php echo $wf['workflow_name'] ?></option>
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

											<div class="row">
												<div class="col s4">
													<div class="input-field">
														<label for="approval_type" class="active required">Approval Type</label>
														<div>
															<select <?php echo $disable ?> name="approval_type[<?php echo $stage_id ?>][]" data-parsley-trigger="change" data-parsley-required="true" id="approval_type" class="selectize">
																<option value="">Please Select</option>
																<?php 
																	if( !EMPTY( $approval_type_arr ) ) :
																?>
																	<?php 
																		foreach( $approval_type_arr as $at => $apt ) :

																			$id_at 	= base64_url_encode( $at );

																			if( EMPTY( $appl_type ) )
																			{
																				$sl_apt = ( $at == 'BY_ROLE' ) ? 'selected' : '';
																			}	
																			else
																			{
																				$sl_apt = ( $appl_type == $at ) ? 'selected' : '';
																			}
																	?>
																	<option <?php echo $sl_apt ?> value="<?php echo $id_at ?>"><?php echo $apt ?></option>
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

											<div class="row">
												<div class="col s4">
													<div class="input-field">
														<!-- <label class="label active" for="is_version_id_<?php echo $t_k ?>">Is version?</label> -->
														<input type="hidden" class="is_version_hid" name="is_version_hid[<?php echo $stage_id ?>][]" value="<?php echo $hid_ver_val ?>">
									 					<input type="checkbox" <?php echo $disable ?> <?php echo $checked_version ?> class="labelauty is_version_check" name="is_version[<?php echo $stage_id ?>][]" id="is_version_id_<?php echo $t_k ?>" value="" data-labelauty="No Versioning|Apply Versioning" />
													</div>
												</div>
												<div class="col s6">
													<div class="input-field">
														<!-- <label class="label active" for="is_gettable_id_<?php echo $stage_id ?>_<?php echo $t_k ?>">Is gettable?</label> -->
														<input type="hidden" class="is_gettable_hid" name="is_gettable_hid[<?php echo $stage_id ?>][]" value="<?php echo $hid_get_val ?>">
									 					<input type="checkbox" <?php echo $disable ?> <?php echo $checked_get ?> class="labelauty is_gettable_check" name="is_gettable[<?php echo $stage_id ?>][]" id="is_gettable_id_<?php echo $stage_id ?>_<?php echo $t_k ?>" value="" data-labelauty="Can't be assigned to self|Can be assigned to self" />
													</div>
												</div>
											</div>

											<div class="row m-n p-t-xs">
												<div class="col s4">
												</div>
												<div class="col s8">
													<div class="right-align m-t-sm">
														<?php 
															if( EMPTY( $disable ) ) :
														?>
														<button type="button" class="btn_action btn-rounded btn btn-secondary white  cyan-text accent-4"><i class="material-icons">library_add</i>Add an Action</button>
														<?php 
															endif;
														?>
														
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col s12 b-n">
													<table class="table table-default form-basic tbl_actions" >
														<thead>
															<tr>
																<th width="35%">
																	<label class="required">Action</label>
																</th>
																<th width="35%">
																	<label class="required">Status</label>
																</th>
																<th width="20%">
																	<label class="required">Process Stop</label>
																</th>
																<th width="10%">&nbsp;</th>
															</tr>
														</thead>
														<tbody>
															
														</tbody>
													</table>				
												</div>
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
						<input type="hidden" class="step_cnt" value="">
						<div id="steps-div" class="row m-b-n ul-collapsible steps-div" data-sequence="1">
							<ul class="collapsible m-b-n" data-collapsible="expandable">
								<li>
								 	<div class="collapsible-header title-content-14 active">
								 		<?php 
								 			if( EMPTY( $disable ) ) :
								 		?>
								 		<i class="material-icons handle">drag_handle</i>
								 		<?php 
								 			endif;
								 		?>
								 			<div class="header-text">
								 				Step 1
								 				<?php 
										 			if( EMPTY( $disable ) ) :
										 		?>
								 				<a class="pull-right font-lg grey-text text-lighten-2 delete_row" href="javascript:;">&#10006;</a>
								 				<?php 
										 			endif;
										 		?>
								 			</div>
								 	</div>
									<div class="collapsible-body form-basic">
										<div class="row">
											<div class="col s8">
												<div class="input-field">
													<label for="step_name" class="active required">Step Name</label>
													<div>
														<input type="hidden" class="step_task" value="">
														<input type="hidden" class="step_sequence" name="step_sequence[<?php echo $stage_id ?>][]" value="1" id="step_sequence">
														<input type="hidden" name="workflow_step[<?php echo $stage_id ?>][]" value="">
														<input type="hidden" name="workflow_step_salt[<?php echo $stage_id ?>][]" value="">
														<input type="hidden" name="workflow_step_token[<?php echo $stage_id ?>][]" value="">

														<input type="text" <?php echo $disable ?> name="step_name[<?php echo $stage_id ?>][]" data-parsley-maxlength="100" data-parsley-group="fieldset-3" data-parsley-required="true" data-parsley-trigger="keyup" id="step_name">
													</div>
												</div>
											</div>
											<div class="col s4">
												<div class="input-field">
													<label for="turnaround_time" class="active required">Turnaround Time</label>
													<div>
														<input type="text" <?php echo $disable ?> data-parsley-group="fieldset-3" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number" name="turnaround_time[<?php echo $stage_id ?>][]" class="number" id="turnaround_time">
													</div>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col s6">
												<div class="input-field">
													<label for="actor_id" class="active required">Actor</label>
													<div>
														<select <?php echo $disable ?> name="actor[<?php echo $stage_id ?>][1_sequence][]" data-parsley-required="true" data-parsley-trigger="change" id="actor_id" class="selectize sub_multiple" multiple>
															<option value="">Please Select</option>
															<?php 
																if( !EMPTY( $roles ) ) :
															?>
																<?php 
																	foreach( $roles as $r ) :

																		$id_role 	= base64_url_encode( $r['role_code'] );
																?>
																<option value="<?php echo $id_role ?>"><?php echo $r['role_name'] ?></option>
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
											<div class="col s6">
												<div class="input-field">
													<label for="appendable_id" class="active">Attachable Process</label>
													<div>
														<select <?php echo $disable ?> name="appendable[<?php echo $stage_id ?>][1_sequence][]" data-parsley-trigger="change" id="appendable_id" class="selectize sub_multiple" multiple>
															<option value="">Please Select</option>
															<?php 
																if( !EMPTY( $append_wf ) ) :
															?>
																<?php 
																	foreach( $append_wf as $wf ) :

																		$id_wf 	= base64_url_encode( $wf['workflow_id'] );
																?>
																<option value="<?php echo $id_wf ?>"><?php echo $wf['workflow_name'] ?></option>
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

										<div class="row">
											<div class="col s4">
												<div class="input-field">
													<label for="approval_type" class="active required">Approval Type</label>
													<div>
														<select <?php echo $disable ?> name="approval_type[<?php echo $stage_id ?>][]" data-parsley-trigger="change" data-parsley-required="true" id="approval_type" class="selectize sub_multiple">
															<option value="">Please Select</option>
															<?php 
																if( !EMPTY( $approval_type_arr ) ) :
															?>
																<?php 
																	foreach( $approval_type_arr as $at => $apt ) :

																		$id_at 	= base64_url_encode( $at );

																		$sel_apt = ( $at == 'BY_ROLE' ) ? 'selected' : '';
																?>
																<option <?php echo $sel_apt ?> value="<?php echo $id_at ?>"><?php echo $apt ?></option>
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

										<div class="row">
											<div class="col s4">
												<div class="input-field">
													<!-- <label class="label active" for="is_version_id_<?php echo $stage_id ?>">Is version?</label> -->
													<input type="hidden" class="is_version_hid" name="is_version_hid[<?php echo $stage_id ?>][]" value="">
								 					<input type="checkbox" <?php echo $disable ?> class="labelauty is_version_check" name="is_version[<?php echo $stage_id ?>][]" id="is_version_id_<?php echo $stage_id ?>" value="" data-labelauty="No Versioning|Apply Versioning" />
												</div>
											</div>
											<div class="col s6">
												<div class="input-field">
													<!-- <label class="label active" for="is_gettable_id_<?php echo $stage_id ?>">Is gettable?</label> -->
													<input type="hidden" class="is_gettable_hid" name="is_gettable_hid[<?php echo $stage_id ?>][]" value="">
								 					<input type="checkbox" <?php echo $disable ?> class="labelauty is_gettable_check" name="is_gettable[<?php echo $stage_id ?>][]" id="is_gettable_id_<?php echo $stage_id ?>" value="" data-labelauty="Can't be assigned to self|Can be assigned to self" />
												</div>
											</div>
										</div>

										<div class="row m-n p-t-xs">
											<div class="col s4">
											</div>
											<div class="col s8">
												<div class="right-align m-t-sm">
													<?php 
														if( EMPTY( $disable ) ) :
													?>
													<button type="button" class="btn_action btn-rounded btn btn-secondary white  cyan-text accent-4"><i class="material-icons">library_add</i>Add an Action</button>
													<?php 
														endif;
													?>
												</div>
											</div>
										</div>
										
										<div class="row">
											<div class="col s12 b-n">
												<table class="table table-default form-basic tbl_actions" >
													<thead>
														<tr>
															<th width="35%">
																<label class="required">Action</label>
															</th>
															<th width="35%">
																<label class="required">Status</label>
															</th>
															<th width="20%">
																<label class="required">Process Stop</label>
															</th>
															<th width="10%">&nbsp;</th>
														</tr>
													</thead>
													<tbody>
														
													</tbody>
												</table>				
											</div>
										</div>

									</div>
								</li>
							</ul>
						</div>
						<?php 
							endif;
						?>
					</div>
					<div class="row m-n form-basic">
					<!-- <div class=""> -->
						<div class="col s8 p-n">
							<div class="left-align ">
								<?php 
									if( EMPTY( $disable ) ) :
								?>
								<button type="button" id="steps_add_btn" class="steps_add_btn btn-rounded btn btn-secondary white  cyan-text accent-4"><i class="material-icons">library_add</i>Add a Step</button>
								<?php 
									endif;
								?>
							</div>
						</div>
					<!-- 	<div class="col s4 p-n">
						<div class="input-field">
							<div class="right-align m-t-sm">
								<input class="search-box" id="search_box_mod" type="text" value="" placeholder="Search" />
							</div>
						</div>
					</div> -->
						
					<!-- </div> -->
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
<div class="steps-header" data-parent="1">
	<input type="hidden" class="stage_step_enc" value="">
	<input type="hidden" class="stage_step_salt" value="">
	<input type="hidden" class="stage_step_token" value="">
	<ul class="collapsible" data-collapsible="expandable">
		<li>
			<div class="collapsible-header title-content-14 active">
				<div class="header-text">
					<div class="black-text text-uppercase">
						<b><?php echo $first_stage_name ?></b>
					</div>
				</div>
			</div>
			<div class="collapsible-body form-basic">
				<div class="steps-container">
					<input type="hidden" class="step_cnt" value="">
					<div id="steps-div" class="row m-b-n ul-collapsible steps-div" data-sequence="1">
						<ul class="collapsible m-b-n" data-collapsible="expandable">
							<li>
							 	<div class="collapsible-header title-content-14 active">
							 		<?php 
										if( EMPTY( $disable ) ) :
									?>
							 		<i class="material-icons handle">drag_handle</i>
							 		<?php 
							 			endif;
							 		?>
							 			<div class="header-text">
							 				Step 1
							 				<?php 
												if( EMPTY( $disable ) ) :
											?>
							 				<a class="pull-right font-lg grey-text text-lighten-2 delete_row" href="javascript:;">&#10006;</a>
							 				<?php 
							 					endif;
							 				?>
							 			</div>
							 	</div>
								<div class="collapsible-body form-basic">
									<div class="row">
										<div class="col s8">
											<div class="input-field">
												<label for="step_name" class="active required">Step Name</label>
												<div>
													<input type="hidden" class="step_task" value="">
													<input type="hidden" class="step_sequence" name="step_sequence[]" value="1" id="step_sequence">
													<input type="hidden" name="workflow_step[]" value="">
													<input type="hidden" name="workflow_step_salt[]" value="">
													<input type="hidden" name="workflow_step_token[]" value="">

													<input <?php echo $disable ?> type="text" name="step_name[]" data-parsley-maxlength="100" data-parsley-group="fieldset-3" data-parsley-required="true" data-parsley-trigger="keyup" id="step_name">
												</div>
											</div>
										</div>
										<div class="col s4">
											<div class="input-field">
												<label for="turnaround_time" class="active required">Turnaround Time</label>
												<div>
													<input <?php echo $disable ?> type="text" data-parsley-group="fieldset-3" data-parsley-required="true" data-parsley-trigger="keyup" data-parsley-type="number" name="turnaround_time[]" class="number" id="turnaround_time">
												</div>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col s6">
											<div class="input-field">
												<label for="actor_id" class="active required">Actor</label>
												<div>
													<select <?php echo $disable ?> name="actor[1_sequence][]" data-parsley-required="true" data-parsley-trigger="change" id="actor_id" class="selectize sub_multiple" multiple>
														<option value="">Please Select</option>
														<?php 
															if( !EMPTY( $roles ) ) :
														?>
															<?php 
																foreach( $roles as $r ) :

																	$id_role 	= base64_url_encode( $r['role_code'] );
															?>
															<option value="<?php echo $id_role ?>"><?php echo $r['role_name'] ?></option>
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
										<div class="col s6">
											<div class="input-field">
												<label for="appendable_id" class="active">Attachable Process</label>
												<div>
													<select <?php echo $disable ?> name="appendable[1_sequence][]" data-parsley-trigger="change" id="appendable_id" class="selectize sub_multiple" multiple>
														<option value="">Please Select</option>
														<?php 
															if( !EMPTY( $append_wf ) ) :
														?>
															<?php 
																foreach( $append_wf as $wf ) :

																	$id_wf 	= base64_url_encode( $wf['workflow_id'] );
															?>
															<option value="<?php echo $id_wf ?>"><?php echo $wf['workflow_name'] ?></option>
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

										<div class="row">
										<div class="col s4">
											<div class="input-field">
												<label for="approval_type" class="active required">Approval Type</label>
												<div>
													<select <?php echo $disable ?> name="approval_type[]" data-parsley-trigger="change" data-parsley-required="true" id="approval_type" class="sub_multiple selectize">
														<option value="">Please Select</option>
														<?php 
															if( !EMPTY( $approval_type_arr ) ) :
														?>
															<?php 
																foreach( $approval_type_arr as $at => $apt ) :

																	$id_at 	= base64_url_encode( $at );

																	$sel_apt = ( $at == 'BY_ROLE' ) ? 'selected' : '';
															?>
															<option <?php echo $sel_apt ?> value="<?php echo $id_at ?>"><?php echo $apt ?></option>
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

									<div class="row">
										<div class="col s4">
											<div class="input-field">
												<!-- <label class="label active" for="is_version_id">Is version?</label> -->
							 					<input type="checkbox" <?php echo $disable ?> class="labelauty" name="is_version" id="is_version_id" value="" data-labelauty="No Versioning|Apply Versioning" />
											</div>
										</div>
										<div class="col s6">
											<div class="input-field">
												<!-- <label class="label active" for="is_gettable_id">Is gettable?</label> -->
							 					<input type="checkbox" <?php echo $disable ?> class="labelauty" name="is_gettable" id="is_gettable_id" value="" data-labelauty="Can't be assigned to self|Can be assigned to self" />
											</div>
										</div>
									</div>

									<div class="row m-n p-t-xs">
										<div class="col s4">
										</div>
										<div class="col s8">
											<div class="right-align m-t-sm">
												<?php 
													if( EMPTY( $disable ) ) :
												?>
												<button type="button" class="btn_action btn-rounded btn btn-secondary white  cyan-text accent-4"><i class="material-icons">library_add</i>Add an Action</button>
												<?php 
													endif;
												?>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col s12 b-n">
											<table class="table table-default form-basic tbl_actions" >
												<thead>
													<tr>
														<th width="35%">
															<label class="required">Action</label>
														</th>
														<th width="35%">
															<label class="required">Status</label>
														</th>
														<th width="20%">
															<label class="required">Process Stop</label>
														</th>
														<th width="10%">&nbsp;</th>
													</tr>
												</thead>
												<tbody>
													
												</tbody>
											</table>				
										</div>
									</div>

								</div>
							</li>
						</ul>
					</div>
				</div>
				<div class="row m-n form-basic">
				<!-- <div class=""> -->
					<div class="col s8 p-n">
						<div class="left-align ">
							<?php 
								if( EMPTY( $disable ) ) :
							?>
							<button type="button" id="steps_add_btn" class="steps_add_btn btn-rounded btn btn-secondary white  cyan-text accent-4"><i class="material-icons">library_add</i>Add a Step</button>
							<?php 
								endif;
							?>
						</div>
					</div>
				<!-- 	<div class="col s4 p-n">
					<div class="input-field">
						<div class="right-align m-t-sm">
							<input class="search-box" id="search_box_mod" type="text" value="" placeholder="Search" />
						</div>
					</div>
				</div> -->
					
				<!-- </div> -->
				</div>
			</div>
		</li>
	</ul>
</div>
<?php 
	endif;
?>