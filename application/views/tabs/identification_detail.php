<?php 

	$email 					= "";
	$mobile_no 				= "";

	$label_mobile_no 		= '';
	$required_mobile_no  	= 'false';
	if($required_mobile)
	{
		$required_mobile_no  = 'true';
		$label_mobile_no 	= 'required';
	}

	if( ISSET( $params['email'] ) )
	{
		$email 		= $params['email'];
	}

	$readonly_email 	= '';

	$api_sign_up 		= '';

	if(ISSET($user_details) AND !EMPTY($user_details))
	{
		$email 		= (!EMPTY($user_details["email"]))? $user_details["email"] : "";
		$mobile_no 	= (!EMPTY($user_details["mobile_no"]))? $user_details["mobile_no"] : "";

		$api_sign_up 		= $user_details['sign_up_api'];
		
	}

	if( $api_sign_up )
	{
		$readonly_email 	= 'readonly="readonly"';
	}

?>
<input type="hidden" name="api_sign_up" value="<?php echo $api_sign_up ?>">
<div class="p-md form-basic left-align">
	<div class="row m-b-n">
		<div class="title-content font-normal">Identification Detail</div>
		<div class="fs-subtitle m-t-sm">Please provide your email, mobile no and answer security questions.</div>
	</div>
	<div class="row m-b-n m-t-md">
		<div class="col s6 m-t-md">
			<div class="input-field">
				<label class="label active required" for="email">Email Address</label>
				<div>
					<input type="text" <?php echo $readonly_email ?> name="email" <?php echo $client_id_info['email'] ?> data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $email ?>" id="email">
					<div class="help-text"><?php echo $email_domain_str ?></div>
				</div>
			</div>
		</div>
		<div class="col s6 m-t-md">
			<div class="input-field">
				<div class="input-group">
					<label class="label active <?php echo $label_mobile_no ?>" for="mobile_no">Mobile No.</label>
					<div class="input-group-addon">+ 63</div>
					<input type="text" name="mobile_no" <?php echo $client_id_info['mobile_no'] ?>  data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-required="<?php echo $required_mobile_no ?>" data-parsley-trigger="keyup" value="<?php echo $mobile_no ?>" id="mobile_no">
				</div>
			</div>
		</div>
	</div>
	<?php 
		if( !EMPTY( $security_questions ) AND EMPTY( $not_req_sec_question ) ) :

			$sec_ids_ans 	= array();
			$sing_ans 		= '';

			if( !EMPTY( $sec_answers ) )
			{
				$sec_ids_ans 	= array_column($sec_answers, 'security_question_id');
				$sing_ans_arr 	= array_column($sec_answers, 'answer');

				$sing_ans 		= $sing_ans_arr[0];
			}
	?>

	<div class="row m-b-n m-t-md">	
		<div class="col s12 m-t-md"><h5 class="form-subtitle">Security Questions</h5></div>
	</div>
	<?php 
	/*	foreach( $security_questions as $secs ) :

			$id_sec = base64_url_encode($secs['security_question_id']);*/
	?>
	<div class="row m-b-n">	
		
		<div class="col s6 ">
			<div class="input-field">
				<div>
					<select class="selectize" name="security_question_id[]">	
						<option value="">Please select</option>
						<?php 
							foreach( $security_questions as $secs ) :

							$id_sec = base64_url_encode($secs['security_question_id']);

							$sel_sec = ( !EMPTY( $sec_ids_ans ) AND in_array($secs['security_question_id'], $sec_ids_ans) ) ? 'selected' : '';
						?>
						<option value="<?php echo $id_sec ?>" <?php echo $sel_sec ?>><?php echo $secs['question'] ?></option>
						<?php 
							endforeach;
						?>
					</select>
				</div>
			</div>
			<!-- <input type="hidden" name="security_question_id[]" value="<?php //echo $id_sec ?>">
			<p><?php //echo $secs['question'] ?></p> -->
		</div>
		<div class="col s6">
			<div class="input-field">
				<div>
					<input type="text" name="security_question_answers[]" data-parsley-required="false" data-parsley-group="fieldset-<?php echo $data_page_key ?>" data-parsley-trigger="keyup" value="<?php echo $sing_ans ?>" id="security_question_answers">
				</div>
			</div>
		</div>
	</div>
	<?php 
		// endforeach;
	?>
	<?php 
		endif;
	?>
</div>
<input type="button" name="previous" class="previous action-button" value="Previous" />
<!-- <input type="button" name="save-wizard" data-action="SignUp.move_id_details(animate_next, next_fs, current_fs, self);" class="save-wizard action-button" value="Save" /> -->
<input type="button" name="next" id="process_save" style="width: 300px !important;" data-action="SignUp.move_id_details(animate_next, next_fs, current_fs, self);" class="next action-button" value="Set-up your <?php echo $system_title ?> account" />