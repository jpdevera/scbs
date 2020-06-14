<?php 
	if( !EMPTY( $access_rights ) ) :

		foreach( $access_rights as $cnt => $access ) :

			$group_class 		= '';

			$per_user_act 		= array();

			$group_id 			= '';
			$group_id_enc 		= '';
			$group_id_salt 		= '';
			$group_id_token 	= '';
			$file_id_enc 		= '';
			$file_id_salt 		= '';
			$file_id_token 		= '';

			$user_id_enc 		= '';
			$user_id_salt 		= '';
			$user_id_token 		= '';

			$del_url 			= "";

			if( ISSET( $access['file_id'] ) AND !EMPTY( $access['file_id'] ) )
			{
				$file_id_enc 		= base64_url_encode( $access['file_id'] );
				$file_id_salt 		= gen_salt();
				$file_id_token 		= in_salt( $access['file_id'], $file_id_salt );
				$file_id_token_del 	= in_salt( $access['file_id'].'/'.ACTION_DELETE, $file_id_salt );

				$del_url 		.= '/'.ACTION_DELETE.'/'.$file_id_enc.'/'.$file_id_salt.'/'.$file_id_token;
			}

			if( ISSET( $access['user_id'] ) AND !EMPTY( $access['user_id'] ) )
			{
				$user_id_enc 	= base64_url_encode( $access['user_id'] );
				$user_id_salt 	= gen_salt();
				$user_id_token 	= in_salt( $access['user_id'], $user_id_salt );

				$del_url 		.= '/'.$user_id_enc.'/'.$user_id_salt.'/'.$user_id_token;
			}

			if( ISSET( $access['group_id'] ) AND !EMPTY( $access['group_id'] ) )
			{

				$group_id 		= $access['group_id'];
				$group_id_enc 	= base64_url_encode( $access['group_id'] );
				$group_id_salt 	= gen_salt();

				$group_id_token = in_salt( $group_id, $group_id_salt );
			}

			if( ISSET( $access['access'] ) AND !EMPTY( $access['access'] ) )
			{
				$per_user_act 	= explode(',', $access['access']);
			}

			$onclick_del 		= '';

			if( ISSET( $access['group_name'] ) AND !EMPTY( $access['group_name'] ) )
			{
				$group_class 	= 'empty-access-rights';
			}

			if( !ISSET( $access['group_name'] ) OR EMPTY( $access['group_name'] ) )
			{
				$onclick_del 	= "onclick='content_visibility_delete(\"Access Right\", \"\", \"\", this)'";
			}

			$arr 		= $orig_params;

			$arr['file_id'] 			= $file_id_enc;
			$arr['file_salt'] 			= $file_id_salt;
			$arr['file_token'] 			= $file_id_token_del;
			$arr['file_action'] 		= ACTION_DELETE;

			$arr['user_id'] 			= $user_id_enc;
			$arr['user_salt'] 			= $user_id_salt;
			$arr['user_id_token'] 		= $user_id_token;

			$arr['group_id'] 			= $group_id_enc;
			$arr['group_salt'] 			= $group_id_salt;
			$arr['group_token'] 		= $group_id_token;

			$arr_json 					= json_encode( $arr );
			
?>
	<div class="form-float-label access_rights_wrapper m-lg b-t b-light-gray p-t-xxxs <?php echo $group_class ?>" id="access_rights_wrapper">

		<input type="hidden" name="group_id_enc[]" <?php //echo $disabled ?> value="<?php echo $group_id_enc ?>">
		<input type="hidden" name="group_id_salt[]" <?php //echo $disabled ?> value="<?php echo $group_id_salt ?>">
		<input type="hidden" name="group_id_token[]" <?php //echo $disabled ?> value="<?php echo $group_id_token ?>">

		<div class="row m-n">
			<div class="col s6">
				<div class="input-field">
					<label for="user_name" class="active block required">User</label>
					<div>
						<select name="user_name[]"  data-parsley-group="users-unique" data-parsley-unique=".users-unique"   data-parsley-required="true" data-parsley-trigger="change" id="user_name" class="selectize users-unique" placeholder="Select User" >
							<option value="">Select User</option>
							<?php 
								if( !EMPTY( $users ) ) :

									foreach( $users as $user ):

										$id 			= base64_url_encode( $user['user_id'] );

										$select_user 	= ( ISSET( $access['user_id'] ) AND $access['user_id'] ==  $user['user_id'] ) ? 'selected' : '';
							?>
							<option value="<?php echo $id ?>|<?php echo $user['user_id'] ?>" <?php echo $select_user ?> ><?php echo $user['fullname'] ?></option>
							<?php 
									endforeach;
							?>

							<?php 
								endif;
							?>
						</select>
					</div>
					<div></div>
				</div>
			</div>
			<div class="col s6">
				<div class="input-field">
					<label for="access_rights_priv" class="active block required">Privileges</label>
					<select name="access_rights_priv[<?php echo $cnt ?>][]"  data-parsley-required="true" data-parsley-trigger="change" id="access_rights_priv" class="selectize sub_multiple access_rights_privilege" placeholder="Select Privileges" multiple>
						<option value="">Select Privileges</option>
						<?php 
							if( !EMPTY( $actions ) ) :

								foreach( $actions as $action ):

									$sel_act 		= ( !EMPTY( $per_user_act ) AND in_array( $action['sys_param_value'], $per_user_act ) ) ? 'selected' : '';

									$act_id 		= base64_url_encode( $action['sys_param_value'] );
						?>
						<option value="<?php echo $act_id ?>" <?php echo $sel_act ?> data-action-id="<?php echo $action['sys_param_value'] ?>" ><?php echo $action['sys_param_name'] ?></option>
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
		<div class="row m-n delete_div_row">	
			<div class="col s12 right-align p-sm deep-purple lighten-5">
				<a class="delete_row load_delete" data-delete_post='<?php echo $arr_json ?>' <?php echo $onclick_del ?> style="cursor:pointer;">
					Remove
				</a>
			</div>
		</div>
	</div>
<?php 
		endforeach;
?>

<?php 
	else :
?>
<div class="form-float-label access_rights_wrapper m-lg b-t b-light-gray p-t-xxxs empty-access-rights" id="access_rights_wrapper">
	<div class="row m-n">
		<div class="col s6">
			<div class="input-field">
				<label for="user_name" class="active block required">User</label>
				<div>
					<select name="user_name[]" data-parsley-group="users-unique" data-parsley-unique=".users-unique"  data-parsley-required="true" data-parsley-trigger="change" id="user_name" class="selectize users-unique" placeholder="Select User" >
						<option value="">Select User</option>
						<?php 
							if( !EMPTY( $users ) ) :

								foreach( $users as $user ):

									$id 		= base64_url_encode( $user['user_id'] );
						?>
						<option value="<?php echo $id ?>|<?php echo $user['user_id'] ?>"><?php echo $user['fullname'] ?></option>
						<?php 
								endforeach;
						?>

						<?php 
							endif;
						?>
					</select>
				</div>
				<div></div>
			</div>
		</div>
		<div class="col s6">
			<div class="input-field">
				<label for="access_rights_priv" class="active block required">Privileges</label>
				<select name="access_rights_priv[0][]"  data-parsley-required="true" data-parsley-trigger="change" id="access_rights_priv" class="selectize sub_multiple access_rights_privilege" placeholder="Select Privileges" multiple>
					<option value="">Select Privileges</option>
					<?php 
						if( !EMPTY( $actions ) ) :

							foreach( $actions as $action ):

								$act_id 		= base64_url_encode( $action['sys_param_value'] );
					?>
					<option value="<?php echo $act_id ?>" data-action-id="<?php echo $action['sys_param_value'] ?>" ><?php echo $action['sys_param_name'] ?></option>
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
	endif;
?>