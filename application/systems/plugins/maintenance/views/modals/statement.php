<?php 
	
	$statement 			= '';
	$stat_type_id 		= '';
	$stat_mod_type_id 	= '';
	$stat_title 		= '';
	$stat_link 			= '';
	$stat_subject 		= '';

	$files 				= array();
	$orig_files 		= array();

	$file_dis 			= 'style="display:none !important;"';
	$text_dis 			= 'style="display:none !important;"';
	$link_dis 			= 'style="display:none !important;"';

	$req_file 			= 'false';
	$req_text 			= 'false';
	$req_link 			= 'false';

	$label_file 		= '';
	$label_text 		= '';
	$label_link 		= '';

	$statement_tokens 	= array();
	$statement_tokens_str = '';
	$built_in 			= ENUM_NO;

	if( !EMPTY( $details ) )
	{
		$statement 	= ( ISSET( $details['statement'] ) AND !EMPTY( $details['statement'] ) ) ? html_entity_decode( $details['statement'] ) : '';
		$built_in 	= ( ISSET( $details['built_in'] ) AND !EMPTY( $details['built_in'] ) ) ? $details['built_in'] : '';

		$stat_type_id = ( ISSET( $details['statement_type_id'] ) AND !EMPTY( $details['statement_type_id'] ) ) ? $details['statement_type_id'] : '';

		$stat_subject = ( ISSET( $details['statement_subject'] ) AND !EMPTY( $details['statement_subject'] ) ) ? $details['statement_subject'] : '';

		$stat_mod_type_id = ( ISSET( $details['statement_module_type_id'] ) AND !EMPTY( $details['statement_module_type_id'] ) ) ? $details['statement_module_type_id'] : '';

		$stat_title = ( ISSET( $details['statement_title'] ) AND !EMPTY( $details['statement_title'] ) ) ? $details['statement_title'] : '';
		$stat_code = ( ISSET( $details['statement_code'] ) AND !EMPTY( $details['statement_code'] ) ) ? $details['statement_code'] : '';

		$files 		= ( ISSET( $details['sys_file_names'] ) AND !EMPTY( $details['sys_file_names'] ) ) ? explode(',', $details['sys_file_names']) : array();
		$orig_files = ( ISSET( $details['orig_file_names'] ) AND !EMPTY( $details['orig_file_names'] ) ) ? explode(',', $details['orig_file_names']) : array();

		$stat_link = ( ISSET( $details['statement_link'] ) AND !EMPTY( $details['statement_link'] ) ) ? $details['statement_link'] : '';

		$statement_tokens = ( ISSET( $details['statement_tokens'] ) AND !EMPTY( $details['statement_tokens'] ) ) ? explode(',', $details['statement_tokens']) : array();

		switch( $stat_type_id )
		{
			case STATEMENT_TYPE_TEXT:
				$file_dis 			= 'style="display:none !important;"';
				$text_dis 			= '';
				$link_dis 			= 'style="display:none !important;"';

				$req_file 			= 'false';
				$req_text 			= 'true';
				$req_link 			= 'false';

				$label_file 		= '';
				$label_text 		= 'required';
				$label_link 		= '';
			break;
			case STATEMENT_TYPE_LINK :
				$file_dis 			= 'style="display:none !important;"';
				$text_dis 			= 'style="display:none !important;"';
				$link_dis 			= '';

				$req_file 			= 'false';
				$req_text 			= 'false';
				$req_link 			= 'true';

				$label_file 		= '';
				$label_text 		= '';
				$label_link 		= 'required';
			break;

			case STATEMENT_TYPE_FILE :
				$file_dis 			= '';
				$text_dis 			= 'style="display:none !important;"';
				$link_dis 			= 'style="display:none !important;"';

				$req_file 			= 'true';
				$req_text 			= 'false';
				$req_link 			= 'false';

				$label_file 		= 'required';
				$label_text 		= '';
				$label_link 		= '';
			break;
		}

	}

	if( !EMPTY( $statement_tokens ) )
	{
		$statement_tokens_str = implode(', ', $statement_tokens);
	}

	$readonly_code 	= '';

	if( $built_in == ENUM_YES )
	{
		$readonly_code = 'readonly';
	}

	if( !EMPTY( $orig_params ) ) :
?>
	<?php 
		foreach( $orig_params as $name => $val ) :
	?>
	<input type="hidden" id="<?php echo $name ?>_inp" name="<?php echo $name ?>" value="<?php echo $val ?>">
	<?php 
		endforeach;
	?>
<?php 
	endif;
?>

<div>
	<div class="form-basic p-md p-t-lg">
		<div class="row" >
			<div class="col s6">
				<div class="input-field">
					<label class="active required">Statement Code</label>
					<input type="text" <?php echo $disabled ?> <?php echo $readonly_code ?> name="statement_code" <?php echo $disabled ?> <?php echo $client_side['statement_code'] ?>  id="statement_code" value="<?php echo $stat_code ?>">
				</div>
			</div>
			<div class="col s6">
				<div class="input-field">
					<label class="active required">Statement Title</label>
					<input type="text" <?php echo $disabled ?> name="statement_title" <?php echo $disabled ?> <?php echo $client_side['statement_title'] ?>  id="statement_title" value="<?php echo $stat_title ?>">
				</div>
			</div>
			
		</div>
		<div class="row">
			<div class="col s3">
				<div class="input-field">
					<label class="active required">Statement Module Type</label>
					<div>
						
						<select class='selectize' id="statement_module_type" name='statement_module_type' <?php echo $disabled ?> <?php echo $client_side['statement_module_type'] ?>>
							<option value="">Please select</option>
							<?php if( !EMPTY( $statement_module_type ) ) : ?>
								<?php 
									foreach( $statement_module_type as $sm ) :

										$id_sm = $sm['statement_module_type_id'];

										$sel_sm = ( $stat_mod_type_id == $sm['statement_module_type_id'] ) ? 'selected' : '';
								?>
								<option value="<?php echo $id_sm ?>" <?php echo $sel_sm ?> ><?php echo $sm['statement_module_type'] ?></option>
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
			<div class="col s3">
				<div class="input-field">
					<label class="active required">Statement Type</label>
					<div>
						
						<select class='selectize' id="statement_type" name='statement_type' <?php echo $disabled ?> <?php echo $client_side['statement_type'] ?>>
							<option value="">Please select</option>
							<?php if( !EMPTY( $statement_type ) ) : ?>
								<?php 
									foreach( $statement_type as $st ) :

										$id_st = $st['statement_type_id'];

										$sel_st = ( $stat_type_id == $st['statement_type_id'] ) ? 'selected' : '';

										if( $stat_mod_type_id == STATEMENT_MODULE_EMAIL_TEMPLATE AND $built_in == ENUM_YES )
										{
											if( $id_st != STATEMENT_TYPE_TEXT )
											{
												continue;
											}
										}
								?>
								<option value="<?php echo $id_st ?>" <?php echo $sel_st ?> ><?php echo $st['statement_type'] ?></option>
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
			<?php 
				if( !EMPTY( $statement_tokens ) ) :
			?>
			<div class="col s3">
				<div class="help-text">Tokens must be inside {example_token}.</div>
				<p>
					<a class="tooltipped" data-tooltip='<?php echo $statement_tokens_str ?>' data-position='bottom' data-delay='50' href='#'>Available tokens</a>
				</p>
			</div>
			<?php 
				endif;
			?>
		</div>

		<div class="row hide" id="email_subject_div">
			<div class="col s12">
				<div class="input-field">
					<label class="active required">Email Subject</label>
					<input type="text" <?php echo $disabled ?> data-parsley-required="false" name="statement_subject" <?php echo $disabled ?> <?php echo $client_side['statement_subject'] ?>  id="statement_subject" value="<?php echo $stat_subject ?>">
				</div>
			</div>
		</div>

		<div class="row" id="statement_div" <?php echo $text_dis ?>>
			<div class="col s12">
				<div class="input-field">
					<label class="active <?php echo $label_text ?>" id="statement_label">Statement</label>
					<textarea <?php echo $disabled ?> <?php echo $client_side['statement'] ?> data-parsley-trigger="change" data-parsley-required="<?php echo $req_text ?>" name="statement" id="write_announcement_textarea" class="materialize-textarea" placeholder = "Write your statment here"><?php echo $statement ?></textarea>
				</div>
			</div>
		</div>
		<div class="row" id="statement_link_div" <?php echo $link_dis ?>>
			<div class="col s12">
				<div class="input-field">
					<label class="active <?php echo $label_link ?>" id="statement_link_label">Link</label>
					<input type="text" name="statement_link" <?php echo $disabled ?> <?php echo $client_side['statement_link'] ?> data-parsley-required="<?php echo $req_link ?>" id="statement_link" value="<?php echo $stat_link ?>">
				</div>
			</div>
		</div>
		<div class="row" id="statement_file_div" <?php echo $file_dis ?>>
			<div class="col s12">
				<div class="input-field">
					<div class="field-multi-attachment p-t-md">

						<?php 
							if( ISSET($files) AND (!EMPTY($files)) ):
						?>
					
						<?php 
							foreach ($files as $key => $file):
								$orig_f 	= ( ISSET( $orig_files[$key] ) ) ? $orig_files[$key] : "";
						?>
							<input type="hidden" data-parsley-errors-container=".my_error_container_req_mov" data-parsley-error-message="This value is required." name="statements_file[]" id="statements_file" value="<?php echo $file; ?>" class="form_dynamic_upload statements_file_inp" data-origfile="<?php echo $orig_f; ?>" data-parsley-required="<?php echo $req_file ?>" >
							<input type="hidden" name="statements_file_orig_filename[]" id="statements_file_orig_filename" value="<?php echo $orig_f; ?>" class="form_dynamic_upload_origfilename">
						<?php endforeach;?>
						<?php 
							else :
						?>

						<input type="hidden" data-parsley-errors-container=".my_error_container_req_mov" data-parsley-error-message="This value is required." class='form_dynamic_upload statements_file_inp' name="statements_file[]" id="statements_file" value="" data-parsley-required="<?php echo $req_file ?>" >
						<input type="hidden" name="statements_file_orig_filename[]" id="statements_file_orig_filename" value="" class="form_dynamic_upload_origfilename">
						<?php 
							endif;
						?>

						<a href="#" id="statements_file_upload" class="tooltipped m-r-sm" data-position="bottom" data-delay="50" data-tooltip="Upload File">Upload</a>
						<div class="my_error_container_req_mov"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="none">
	<div id="reject_content">
	  <form id="reject_user_form" class="form-basic">
		<input type="hidden" name="id" id="reject_id" value="" />
		<p class="p-n m-b-md font-bold font-sm">Are you sure you want to reject this registration?</p>
		<div class="input-field">
		  <textarea class="materialize-textarea" name="reject_reason" id="reject_reason" placeholder="Write a reject reason here..."></textarea>
		</div>
		<div class="popModal_footer">
			
		</div>
	  </form>
	</div>
</div>