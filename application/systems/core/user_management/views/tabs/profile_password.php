<?php 
$id	= (!EMPTY($user["user_id"]))? $user["user_id"] : "";
$salt = gen_salt();
$token	= in_salt($id, $salt);
$email = (!EMPTY($user["email"]))? $user["email"] : "";
$roles = (!EMPTY($user["role_names"]))? $user["role_names"] : "";

?>
<form id="form_profile_pass">
	<div class="row m-md">
		<div class="col l12 m12 s12 p-n">
			<div class="white box-shadow">
				<div class="table-display">
					<div class="table-cell s12 p-lg valign-top">
						<input type="hidden" name="user_id" value="<?php echo $id ?>">
						<input type="hidden" name="salt" value="<?php echo $salt ?>">
						<input type="hidden" name="token" value="<?php echo $token ?>">
						
						<div class="form-basic">
							<div class="row">
								<div class="col s12">
									<h6>Account Settings</h6>
									<div class="help-text">Manage your email and password associated with your account</div>
								</div>
							</div>
							<div class="row">
								<div class="col s8" id="email_wrapper">
									<div class="input-field">
								  		<input <?php echo $email_ver_readonly ?> type="email" name="email" id="email" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-type="email" data-parsley-trigger="keyup" value="<?php echo $email ?>" placeholder="Enter your email address"  autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"/>
								  		<label for="email" class="active required position-relative" style="display:block!important; width:100%;">Email Address 
								  			<?php 
											if( !EMPTY( $email_ver_readonly ) ) :
											?>
											<a href="#" data-target="modal_verify_code" class="position-absolute  modal_verify_code_trigger" onclick="modal_verify_code_init('', this)" data-modal_post='<?php echo json_encode($email_auth_data) ?>' style="right:0">Change Email</a>
											<?php 
												endif;
											?>
								  		</label>
								  		<div class="help-text">Changing your email will require you to verify your new email.</div>
									</div>
							  	</div>
						  		<div class="col s4">
									<div class="input-field">
										<label for="password" class="active">Assigned Role/s</label>
										<p class="p-t-sm font-bold"><?php echo $roles ?></p>
									</div>
						  		</div>
							</div>
							<div class="row">
						  		<div class="col s12">
									<div class="input-field">
										<input type="password" name="current_password" id="current_password" data-parsley-required="false" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" placeholder="Enter your current password" />
										<label for="current_password" class="active required">Current Password</label>
										<div class="help-text">Enter your current password to allow setting of new password.</div>
									</div>
						  		</div>
						  	</div>
						  	<div class="row">
						  		<div class="col s6">
									<div class="input-field">
										<input type="password" name="password" data-parsley-pass="true" disabled id="new_password" data-parsley-trigger="keyup" />
										<label for="password" class="active required">New Password</label>
									</div>
						  		</div>
						  		<div class="col s6">
									<div class="input-field">
										<input type="password" name="confirm_password" disabled id="confirm_password" data-parsley-equalto="#new_password" data-parsley-trigger="keyup" data-parsley-equalto-message="The passwords you entered do not match."  />
										<label for="confirm_password" class="active required">Confirm Password</label>
									</div>
						  		</div>
						  	</div>
						</div>
					</div>
				</div>
				<?php if($this->permission->check_permission(MODULE_PROFILE, ACTION_SAVE)){ ?>
				<div class="panel-footer right-align">
				    <div class="input-field inline m-n">
						<button class="btn waves-effect bg-success" type="button" id="submit_profile_pass" value="Save" data-btn-action="<?php echo BTN_SAVING ?>" ><?php echo BTN_SAVE ?></button>
				    </div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</form>
