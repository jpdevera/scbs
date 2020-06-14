<?php 
	$value_visibility 			= '';

	if( !EMPTY( $visibility_details ) )
	{
		$value_visibility 		= ( ISSET( $visibility_details['visibility_id'] ) AND !EMPTY( $visibility_details['visibility_id'] ) ) ? $visibility_details['visibility_id'] : '';

	}
?>

<input type="hidden" id="visible_constants" value='<?php echo $visible_constants ?>'>
<input type="hidden" id="access_rights_visible_hide" value="<?php echo $value_visibility ?>">

<input type="hidden" id="<?php echo $params['file_type'] ?>_js_file_constants_vis" value='<?php echo $js_file_constants ?>'>
<input type="hidden" id="<?php echo $params['file_type'] ?>_js_file_dir_constants_vis" value='<?php echo $js_file_dir_constants ?>'>
<input type="hidden" id="<?php echo $params['file_type'] ?>_directory_module_map_vis" value='<?php echo $directory_module_map_json ?>'>

<input type="hidden" id="<?php echo $params['file_type'] ?>_file_vis" name="file_id_vis" value="<?php echo $orig_params['file_id'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_salt_vis" name="file_salt_vis" value="<?php echo $orig_params['file_salt'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_token_vis" name="file_token_vis" value="<?php echo $orig_params['file_token'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_action_vis" name="file_action_vis" value="<?php echo $orig_params['file_action'] ?>">

<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type_vis" name="file_type_vis" value="<?php echo $orig_params['file_type'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type_salt_vis" name="file_type_salt_vis" value="<?php echo $orig_params['file_type_salt'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type_token_vis" name="file_type_token_vis" value="<?php echo $orig_params['file_type_token'] ?>">

<input type="hidden" id="<?php echo $params['file_type'] ?>_module_vis" name="module_vis" value="<?php echo $orig_params['module'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_module_salt_vis" name="module_salt_vis" value="<?php echo $orig_params['module_salt'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_module_token_vis" name="module_token_vis" value="<?php echo $orig_params['module_token'] ?>">

<div class="row white p-lg p-b-n p-t-sm m-b-n-sm form-basic">
	<div class="form-float-label">
		<div class="p-n col s6 input-field">
			<div>
				<select class="selectize" name="access_rights_visibility" id="access_rights_visibility" placeholder="Visible to"
					data-parsley-required="true" data-parsley-trigger="change">
					<option value=""></option>
					<?php
						if( !EMPTY( $visibilities ) ) :

							foreach( $visibilities as $visible ) :

								$sel_vis = '';

								$sel_vis = ( !EMPTY( $value_visibility ) AND $value_visibility == $visible['visibility_id'] ) ? 'selected' : '';

								if( EMPTY( $sel_vis ) )
								{
									$sel_vis = ( $visible['visibility_id'] == VISIBLE_ALL ) ? 'selected' : '';
								}

								$id_vis 	= base64_url_encode( $visible['visibility_id'] );
					?>
					<option data-visibility="<?php echo $visible['visibility_id'] ?>" value="<?php echo $id_vis ?>" <?php echo $sel_vis ?>><?php echo $visible['visibility_name'] ?></option>
					<?php 
							endforeach;
					?>
					<?php endif; ?>
				</select>
			</div>
		</div>

		<div class="col s6 right-align input-field" id="access_rights_visibility_actions_div">
			<div>
				<select class="selectize" multiple name="access_rights_visibility_actions[]" data-parsley-required="true" data-parsley-trigger="change" id="access_rights_visibility_actions" placeholder="Select Privilege For All">
					<option value=""></option>
					<?php
					if( !EMPTY( $action_access ) ) :

						foreach( $action_access as $action ) :

							// ( !EMPTY( $actions_all ) AND in_array( $action['action_id'], $actions_all )  ) ? 'selected' :

							$sel_act	= ( !EMPTY( $actions_all ) AND in_array( $action['sys_param_value'], $actions_all )  ) ? 'selected' : '';

							$act_id 	= base64_url_encode( $action['sys_param_value'] );

					?>
					<option value="<?php echo $act_id ?>" <?php echo $sel_act ?>><?php echo $action['sys_param_name'] ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
		</div>

		<div class="col s6 right-align input-field" style="display :none !important;" id="access_rights_visibility_group_div" >
			<div>
				<select class="selectize" disabled multiple name="access_rights_visibility_group[]" id="access_rights_visibility_group" placeholder="Select Group">
					<option value=""></option>
					<?php
					if( !EMPTY( $groups ) ) :

						foreach( $groups as $group ) :

							$sel_grp	= ( !EMPTY( $group_vis ) AND in_array( $group['group_id'], $group_vis )  ) ? 'selected' : '';
							$id_group 	= base64_url_encode( $group['group_id'] );

					?>
					<option value="<?php echo $id_group ?>" <?php echo $sel_grp ?>><?php echo $group['group_name'] ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
		</div>

	</div>
</div>

<div class="row white p-lg p-b-n p-t-lg m-b-n-sm add_access_div" style="display :none !important;">
	<div class="form-basic">
		<div class="col s6 p-n left-align">
			<div class="input-field">
				<input class="search-box" id="access_rights_search" type="text" value="" placeholder="Search" />
			</div>
		</div>
		<div class="col s6 right-align">
			<button type="button" class="waves-effect waves-light dropdown-button btn btn-secondary" data-beloworigin="false" data-activates="dropdown-download-to" id="add_access_rights">Add</button>
		</div>
	</div>

	<div class="row m-n">
		<div class="col s12 p-n p-t-sm">
			<ul class="collapsible panel p-n" data-collapsible="expandable">
				<li>
					<div id="access_rights_collapsible_header" data-tooltip="Click to expand or minimize" data-position="bottom" data-delay="50" class="collapsible-header cyan darken-1 white-text tooltipped">Apply to All</div>
					<div class="collapsible-body form-float-label m-n p-n">
						<div class="row m-n">
							<div class="col s6">
								<div class="input-field">
									<label for="default_privilege" class="active block">Privileges</label>
									<select  id="default_privilege" class="selectize" multiple placeholder="Select privilege for all">
										<option value="">Select privilege for all</option>
										<?php 
											if( !EMPTY( $action_access ) ) :

												foreach( $action_access as $action ):

													$act_id 		= $action['sys_param_value'];
										?>
										<option value="<?php echo $act_id ?>"><?php echo $action['sys_param_name'] ?></option>
										<?php 
												endforeach;
										?>

										<?php 
											endif;
										?>
										</select>
									</div>
								</div>
								<div class="col s6">&nbsp;</div>
							</div>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>

<div id="access_rights_div_wrapper" class="add_access_div" style="display:none !important;">
</div>