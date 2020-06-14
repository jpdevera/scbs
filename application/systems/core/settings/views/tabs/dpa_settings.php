<?php 

	$salt = gen_salt();
  	$token = in_salt($this->session->userdata('user_id'), $salt);
	
	$dpa_enable 					= get_setting( DPA_SETTING, 'dpa_enable' );

	$dpa_email_enable 				= get_setting( DPA_SETTING, 'dpa_email_enable' );

	$check_dpa_enable 				= ( !EMPTY( $dpa_enable ) ) ? 'checked' : '';

	$check_dpa_email_enable 		= ( !EMPTY( $dpa_email_enable ) ) ? 'checked' : '';

	$email_domain 					= get_setting( DPA_SETTING, 'email_domain' );

	$has_agreement_text 				= get_setting( AGREEMENT, 'has_agreement_text' );

	$dpa_strict_mode 				= get_setting(DPA_SETTING, 'dpa_strict_mode');

	$dpa_encryption 				= get_setting(DPA_SETTING, 'encryption');

	$checked_encryption 			= '';

	if( !EMPTY( $dpa_encryption ) )
	{
		$checked_encryption 		= 'checked';
	}

	$check_dpa_basic 	= 'checked';
	$check_dpa_strict 	= '';


	$check_dpa_str_mode1 	= 'checked';
	$check_dpa_str_mode2 	= '';

	if( !EMPTY( $has_agreement_text ) )
	{
		$check_dpa_basic 	= '';
		$check_dpa_strict 	= '';

		switch( $has_agreement_text )
		{
			case DATA_PRIVACY_TYPE_BASIC :
				$check_dpa_basic 	= 'checked';
				$check_dpa_strict 	= '';
			break;
			case DATA_PRIVACY_TYPE_STRICT :
				$check_dpa_basic 	= '';
				$check_dpa_strict 	= 'checked';
			break;
			default :
				$check_dpa_basic 	= '';
				$check_dpa_strict 	= '';
			break;
		}
	}

	if( !EMPTY( $dpa_strict_mode ) )
	{
		$check_dpa_str_mode1 	= '';
		$check_dpa_str_mode2 	= '';

		switch( $dpa_strict_mode )
		{
			case DATA_PRIVACY_STRICT_CONSENT_FORM :
				$check_dpa_str_mode1 	= 'checked';
				$check_dpa_str_mode2 	= '';
			break;
			case DATA_PRIVACY_STRICT_EMAIL_NOTIF :
				$check_dpa_str_mode1 	= '';
				$check_dpa_str_mode2 	= 'checked';
			break;
			default :
				$check_dpa_str_mode1 	= '';
				$check_dpa_str_mode2 	= '';
			break;
		}
	}

  // $checked_has_agreement_text 		= ( !EMPTY( $has_agreement_text ) ) ? 'checked' : '';

  $agreement_text_value 			= get_setting( AGREEMENT, 'agremment_text' );

  /*$agreement_text_value 			= ( !EMPTY( $agreement_text_value ) ) ? html_entity_decode( $agreement_text_value ) : '';*/

   $agreement_text_arr 	= array();

	  if( !EMPTY( $agreement_text_value ) )
	  {
	  	$agreement_text_arr 	= explode(',', $agreement_text_value);
	  }

  $agreement_uploads 				= get_setting( AGREEMENT, 'agreement_uploads' );
?>
<div class="row">
	<div class="col l10 m12 s12">
		<form id="dpa_settings_form" class="m-t-lg">
			<input type="hidden" name="id" value="<?php echo $this->session->userdata('user_id') ?>"/>
		  	<input type="hidden" name="salt" value="<?php echo $salt ?>">
		  	<input type="hidden" name="token" value="<?php echo $token ?>">

		  	<input type="hidden" name="agreement_uploads" id="terms_conditions" value="<?php echo $agreement_uploads ?>"/>

		  	<div class="form-basic">
		  		<div id="site-terms" class="scrollspy table-display white m-t-lg box-shadow">
				  <div class="table-cell bg-dark p-lg valign-top" style="width:25%">
					<label class="label mute">Data Privacy Compliance</label>
					<p class="caption m-t-sm white-text">Control if the app is data privacy compliant.</p>
				  </div>
				  <div class="table-cell p-lg valign-top">
				  	<div class="row m-b-n">
					  <div class="col s8">
						<div class="p-b-md">
						  <h6>Data Privacy</h6>
						  <div class="help-text">Enables data privacy notes on some screens, and presents data privacy statement that user has to agree on before he/she can proceed with initial login.</div>
						</div>
					  </div>
					  <div class="col s4 right-align">
						<input type="checkbox" class="labelauty" name="dpa_enable" id="dpa_enable" value="" data-labelauty="Disabled|Enabled" onclick="toggle('dpa_enable', 'dap_enable_value')"  <?php echo $check_dpa_enable ?> />
					  </div>
					</div>

					<div id="dap_enable_value" style="display:none">

						<div class="row m-b-n">
							<div class="col s12">
								<div>
									<h6>Data Privacy Encryption</h6>
									<div class="help-text">Enable/Disable encryption of data.</div>
								</div>
							</div>

							<div class="row">
				  				<div class="col l4 m4 s12">
				  					<input type="checkbox" class="labelauty" name="encryption" id="encryption" value="1" <?php echo $checked_encryption ?> data-labelauty="Disable|Enable"/>
				  				</div>
				  			</div>
						</div>

						<div class="row m-b-n">
							<div class="col s12">
								<div>
									<h6>Data Privacy Type</h6>
									<div class="help-text">Type of Data Privacy Compliance.</div>
								</div>
							</div>

							<div class="row">
				  				<div class="col l4 m4 s12">
				  					<input type="radio" class="labelauty label-icon-side" name="has_agreement_text" id="has_agreement_text" value="<?php echo DATA_PRIVACY_TYPE_BASIC ?>" <?php echo $check_dpa_basic ?> data-labelauty="Basic"/>
				  				</div>
				  				<div class="col l4 m4 s12">
				  					<input type="radio" class="labelauty label-icon-side" name="has_agreement_text" id="data_privacy_type_strict" value="<?php echo DATA_PRIVACY_TYPE_STRICT ?>" <?php echo $check_dpa_strict ?> data-labelauty="Strict"/>
				  					<div class="help-text">	
				  						Requires consent from individual thru manually uploaded consent form or email confirmation.  This option also encrypts selected information in the database.
				  					</div>
				  				</div>
				  			</div>
						</div>

						<div id="has_agreement_text_value" style="display:none">

							<div class="row p-md p-b-n m-b-n">
							  <div class="col s12">
								<div class="p-b-md">
								  <div class="input-field">
								  	<!-- data-parsley-required="true" data-parsley-maxlength="60000" data-parsley-trigger="keyup" -->
								  	<div class="help-text">Please choose a statement</div>
								  	<select data-parsley-required="false" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" id="agremment_text_sel" name="agremment_text[]" class="validate selectize" placeholder="Please select" >
					  					<option value="">Please select</option>
					  					<?php 
					  						if( !EMPTY( $statements ) ) :
					  					?>
					  						<?php 
					  							foreach( $statements as $stat ) :

					  								$id_a = base64_url_encode($stat['statement_id']);

					  								$sel_auth = ( !EMPTY( $agreement_text_arr ) AND in_array( $stat['statement_id'], $agreement_text_arr ) ) ? 'selected' : '';

					  								// $sel_auth 	= '';
					  						?>
					  						<option value="<?php echo $id_a ?>" <?php echo $sel_auth ?> ><?php echo $stat['statement_code'].' - '.$stat['statement_title'] ?></option>
					  						<?php 
					  							endforeach;
					  						?>
					  					<?php 
					  						endif;
					  					?>
					  				</select>

								 <!--  	<textarea style="min-height : 200px !important;"  id="agremment_text" class="editor materialize-textarea" name="agremment_text"><?php echo $agreement_text_value ?></textarea> -->
									<!-- <label for="agremment_text">Terms and Conditions</label> -->
								  </div>
								</div>
							  </div>
							</div>
							<!-- <div class="row p-md p-b-n m-b-n field-multi-attachment">
							 	<div class="col s12">
									<div class="p-b-md">
								  		<div class="input-field">
								  			<div id="terms_conditions_upload">Select File</div>
								  			<div class="help-text">Upload the terms and conditions file.</div>
								  		</div>
								  	</div>
								</div>
							</div> -->
						</div>

						<div id="strict_type" style="display:none">
							<div class="row p-md p-b-n m-b-n">
				  				<div class="col l4 m4 s12">
				  					<input type="radio" class="labelauty dpa_strict_type_mode" name="dpa_strict_mode" id="dpa_strict_type_mode_1" value="<?php echo DATA_PRIVACY_STRICT_CONSENT_FORM ?>" <?php echo $check_dpa_str_mode1 ?> data-labelauty="Consent Form"/>
				  					<div class="p-t-sm help-text">System requires uploading of a consent form when adding user.</div>
				  				</div>
				  				<div class="col l4 m4 s12">
				  					<input type="radio" class="labelauty dpa_strict_type_mode" name="dpa_strict_mode" id="dpa_strict_type_mode_2" value="<?php echo DATA_PRIVACY_STRICT_EMAIL_NOTIF ?>" <?php echo $check_dpa_str_mode2 ?>  data-labelauty="Email Confirmation"/>
				  					<div class="p-t-sm help-text">Email notification for confirmation will be sent to individual that has been added.  If not confirmed within 24 hrs, link will expire and user will be permanently deleted.</div>
				  				</div>
							</div>
						</div>

					</div>
				  </div>
				</div>

				<div id="email-terms" class="scrollspy table-display white m-t-lg box-shadow">
				  <div class="table-cell bg-dark p-lg valign-top" style="width:25%">
					<label class="label mute">Data Privacy Email Constraints</label>
					<p class="caption m-t-sm white-text">Control the email domain/s not allowed when signing up</p>
				  </div>
				  <div class="table-cell p-lg valign-top">
				  	<div class="row m-b-n">
					  <div class="col s8">
						<div class="p-b-md">
						  <h6>Data Privacy Email Constraints</h6>
						  <div class="help-text"></div>
						</div>
					  </div>
					  <div class="col s4 right-align">
						<input type="checkbox" class="labelauty" name="dpa_email_enable" id="dpa_email_enable" value="" data-labelauty="Disabled|Enabled"  <?php echo $check_dpa_email_enable ?> />
					  </div>
					</div>

					<div id="dap_email_enable_value" style="display:none">
						<div class="row m-b-n">
							<div class="col s12">
								<div>
									<h6>Blacklist Email domain</h6>
									<div class="help-text">Indicate blacklisted email domain/s.  Note: Email domains indicated will not be accepted during sign-up.</div>
								</div>
							</div>

							<div class="row">
								<div class="col s12">
									<div>
					  					<input type="text" data-parsley-required="false" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" id="email_domain" name="email_domain" class="validate tagging" value="<?php echo $email_domain ?>" />
					  				</div>
									<!-- <div class="help-text">Don't allow the email domain upon signing up (e.g. gmail.com,yahoo.com).</div>
 -->
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
					  <button class="btn waves-effect waves-light bg-success" type="submit" id="save_dpa_settings" value="<?php echo BTN_SAVING ?>" data-btn-action="<?php echo BTN_SAVING; ?>"><?php echo BTN_SAVE ?></button>
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
			<li><a href="#site-terms">Data Privacy Compliance</a></li>		
			<li><a href="#email-terms">Data Privacy Email Constraints</a></li>		
		  </ul>
		</div>
	  </div>
	</div>

</div>