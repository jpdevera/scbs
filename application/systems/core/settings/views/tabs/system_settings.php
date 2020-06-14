<?php 
	$salt 	= gen_salt();
  	$token 	= in_salt($this->session->userdata('user_id'), $salt);

  	  $sms_api_set 		= get_setting(SMS_API, 'sms_api');
	  $sms_api_set 		= trim($sms_api_set);
	  $sms_api_set_arr 		= array();

	  $notification_cron	= get_setting('NOTIFICATION_CRON', "notification_cron");

  	  $checked_notification_cron 	= ( !EMPTY( $notification_cron ) ) ? 'checked' : '';

	  if( !EMPTY( $sms_api_set ) )
	  {
	  	$sms_api_set_arr 	= explode(',', $sms_api_set);
	  }
?>
<div class="row">
	<div class="col l10 m12 s12">
		<form id="system_settings_form" class="m-t-lg">
			<input type="hidden" name="id" value="<?php echo $this->session->userdata('user_id') ?>"/>
		  	<input type="hidden" name="salt" value="<?php echo $salt ?>">
		  	<input type="hidden" name="token" value="<?php echo $token ?>">

		  	<input type="hidden" name="cert_file_upload" id="cert_file" value="<?php echo get_setting('CERTIFICATION_FILE_UPLOAD', 'cert_file_upload') ?>"/>

		  	<div class="form-basic">
		  		<div id="email-smtp" class="scrollspy table-display white m-t-lg box-shadow">
				  <div class="table-cell bg-dark p-lg valign-top" style="width:25%">
					<label class="label mute">Mail Sender Settings</label>
					<p class="caption m-t-sm white-text">Control the following Mail Sender Settings</p>
				  </div>
				  <div class="table-cell p-lg valign-top">
				  	<div class="row">
					  <div class="col s12">
					  	<div class="p-b-md">
					  		<div class="row m-n p-n">
								<div class="col s9">
									 <div class="input-field">
									 	<input id="smtp_host" name="smtp_host" type="text" class="validate" value="<?php echo $email_data['smtp_host'] ?>" data-parsley-required="true" data-parsley-trigger="keyup" />
										<label for="smtp_host" class="active">Host</label>
										<div class="help-text">Application's mail sending Host e.g. (ssl://gmail.com)</div>
									 </div>
								</div>
							</div>
					  	</div>
					  	<div class="p-b-md">
					  		<div class="row m-n p-n">
								<div class="col s9">
									 <div class="input-field">
									 	<input id="protocol" name="protocol" type="text" class="validate" value="<?php echo $email_data['protocol'] ?>" data-parsley-required="true" data-parsley-trigger="keyup" />
										<label for="protocol" class="active">Protocol</label>
										<div class="help-text">Application's mail sending Protocol e.g. (smtp)</div>
									 </div>
								</div>
							</div>
					  	</div>
					  	<div class="p-b-md">
					  		<div class="row m-n p-n">
								<div class="col s9">
									 <div class="input-field">
									 	<input id="smtp_user" name="smtp_user" type="text" class="validate" value="<?php echo $email_data['smtp_user'] ?>" data-parsley-required="true" data-parsley-trigger="keyup" />
										<label for="smtp_user" class="active">Username</label>
										<div class="help-text">Application's mail sending username e.g. (juandelacruz@email.com)</div>
									 </div>
								</div>
							</div>
					  	</div>
					  	<div class="p-b-md">
					  		<div class="row m-n p-n">
								<div class="col s9">
									 <div class="input-field">
									 	<input id="smtp_pass" name="smtp_pass" type="password" class="validate" value="<?php echo $email_data['smtp_pass'] ?>" data-parsley-required="true" data-parsley-trigger="keyup" />
										<label for="smtp_pass" class="active">Password</label>
										<div class="help-text">Application's mail sending password.</div>
									 </div>
								</div>
							</div>
					  	</div>
					  	<div class="p-b-md">
					  		<div class="row m-n p-n">
								<div class="col s9">
									 <div class="input-field">
									 	<input id="smtp_port" name="smtp_port" type="text" class="validate" value="<?php echo $email_data['smtp_port'] ?>" data-parsley-required="true" data-parsley-trigger="keyup" />
										<label for="smtp_port" class="active">Port</label>
										<div class="help-text">Application's mail sending Port e.g. (45)</div>
									 </div>
								</div>
							</div>
					  	</div>
					  	<div class="p-b-md">
					  		<div class="row m-n p-n">
								<div class="col s9">
									 <div class="input-field">
									 	<input id="smtp_reply_email" name="smtp_reply_email" type="text" class="validate" value="<?php echo $email_data['smtp_reply_email'] ?>" data-parsley-required="true" data-parsley-trigger="keyup" />
										<label for="smtp_reply_email" class="active">Reply Email</label>
										<div class="help-text">Reply Email Application</div>
									 </div>
								</div>
							</div>
					  	</div>
				  		<div class="p-b-md">
					  		<div class="row m-n p-n">
								<div class="col s9">
									 <div class="input-field">
									 	<input id="smtp_reply_name" name="smtp_reply_name" type="text" class="validate" value="<?php echo $email_data['smtp_reply_name'] ?>" data-parsley-required="true" data-parsley-trigger="keyup" />
										<label for="smtp_reply_name" class="active">Reply Name</label>
										<div class="help-text">Reply Name of Application</div>
									 </div>
								</div>
							</div>
					  	</div>
					  </div>
					 
					</div>


				  </div>
				</div>

				<div id="sms-api-settings" class="scrollspy table-display white m-t-lg box-shadow">
					<div class="table-cell bg-dark p-lg valign-top" style="width:25%">
					<label class="label mute">SMS API</label>
					<p class="caption m-t-sm white-text">Change the way the SMS will be sent</p>
				  </div>
				  <div class="table-cell p-lg valign-top">
				  	<div class="p-b-md">
						<h6>SMS API</h6>
						<div class="help-text">Change the way the SMS will be sent.</div>
					</div>
				  	<div class="row ">
					 	<div class="col s12">
							<div class="p-b-md">
						  		<div class="input-field">

							  		<select data-parsley-required="false" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" id="sms_api" name="sms_api[]" class="validate selectize" placeholder="None" >
					  					<option value="">None</option>
					  					<?php 
					  						if( !EMPTY( $sms_apis ) ) :
					  					?>
					  						<?php 
					  							foreach( $sms_apis as $code => $sms_api ) :

					  								$id_code = base64_url_encode($code);

					  								$sel_code = ( !EMPTY( $sms_api_set_arr ) AND in_array( $code, $sms_api_set_arr ) ) ? 'selected' : '';
					  								// $sel_code 	= '';
					  						?>
					  						<option value="<?php echo $id_code ?>" <?php echo $sel_code ?> ><?php echo $sms_api ?></option>
					  						<?php 
					  							endforeach;
					  						?>
					  					<?php 
					  						endif;
					  					?>
					  				</select>
					  							
						  			<!-- <label for="cert_file_upload">Certification File</label> -->
						  			
						  		</div>
						  	</div>
						</div>
					</div>
					</div>
				</div>

				<div id="notif_queue" class="scrollspy table-display m-t-lg white box-shadow">
					<div class="table-cell bg-dark p-lg valign-top" style="width:25%">
						<label class="label mute">Notification Queue</label>
						<p class="caption m-t-sm white-text">If enabled there's an ongoing process.</p>
					</div>
					<div class="table-cell p-lg valign-top">
						<div class="row m-b-n">
							<h6>Notification Queue Flag</h6>
							<div class="help-text">Please avoid in changing these button, but for some reason email or sms is not being recieved and these button is enable please disable this button. Make sure before running the queue this is disable. This will only be enabled if there is an ongoing queue and it will be disabled again once the queue is finished.</div>

							<div class="row">
								<div class="col s6">
									<input type="checkbox" class="labelauty" <?php echo $checked_notification_cron ?> name="notification_cron" id="notification_cron" value="" data-labelauty="Disable|Enable"  />
								</div>
							</div>

							<!-- <div id="quality_compression_div" style="display:none"> -->
								
						<!-- </div> -->

					</div>
				</div>
			</div>

				<div id="cert-file-upload" class="scrollspy table-display white m-t-lg box-shadow">
					<div class="table-cell bg-dark p-lg valign-top" style="width:25%">
					<label class="label mute">Certification File Upload</label>
					<p class="caption m-t-sm white-text">Upload Certification File for SSL</p>
				  	</div>
				  <div class="table-cell p-lg valign-top">
				  	<div class="row p-md p-b-n m-b-n field-multi-attachment">
					 	<div class="col s12">
							<div class="p-b-md">
						  		<div class="input-field">
						  			<div class="help-text">Upload the SSL Certificate.</div>
						  			<div id="cert_file_upload">Select File</div>
						  			<label for="cert_file_upload" class="active">SSL Certificate</label>
						  			
						  		</div>
						  	</div>
						</div>
					</div>
					</div>
				</div>



				<div class="panel-footer right-align">
				    <div class="input-field inline m-n">
				    	<?php 
				    		if( $permission ) :
				    	?>
					  <button class="btn waves-effect waves-light bg-success" type="submit" id="save_sys_settings" value="<?php echo BTN_SAVING ?>" data-btn-action="<?php echo BTN_SAVING; ?>"><?php echo BTN_SAVE ?></button>
					   <?php 
					  		endif;
					  	?>
				    </div>
			  	</div>
		  	</div>
		  </form>
	</div>
	<div class="col l2 hide-on-med-and-down">
		<div class="pinned m-t-lg">
		  <ul class="section table-of-contents">
			<li><a href="#email-smtp">Email sending variables</a></li>	
			<li><a href="#sms-api-settings">SMS API</a></li>	
			<li><a href="#notif_queue">Notification Queue</a></li>
			<li><a href="#cert-file-upload">Certification File Upload</a></li>
		  </ul>
		</div>
	  </div>
	</div>
</div>