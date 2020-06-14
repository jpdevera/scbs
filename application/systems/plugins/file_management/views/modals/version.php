<?php 
	$display_name 	= "";
	$description 	= "";
	$class 			= "";

	if( !EMPTY( $details ) )
	{
		$display_name 	= ( ISSET( $details['display_name'] ) AND !EMPTY( $details['display_name'] ) ) ? $details['display_name'] : "";
		$description 	= ( ISSET( $details['description'] ) AND !EMPTY( $details['description'] ) ) ? $details['description'] : "";
		$class 			= "active";
	}
?>
<input type="hidden" id="<?php echo $params['file_type'] ?>_js_file_constants" value='<?php echo $js_file_constants ?>'>
<input type="hidden" id="<?php echo $params['file_type'] ?>_js_file_dir_constants" value='<?php echo $js_file_dir_constants ?>'>
<input type="hidden" id="<?php echo $params['file_type'] ?>_directory_module_map" value='<?php echo $directory_module_map_json ?>'>

<input type="hidden" id="<?php echo $params['file_type'] ?>_file" name="file_id" value="<?php echo $id ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_salt" name="file_salt" value="<?php echo $salt ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_token" name="file_token" value="<?php echo $token ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_action" name="file_action" value="<?php echo $action ?>">

<input type="hidden" id="<?php echo $params['file_type'] ?>_file_version" name="file_version" value="">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_version_salt" name="file_version_salt" value="">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_version_token" name="file_version_token" value="">

<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type" name="file_type" value="<?php echo $orig_params['file_type'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type_salt" name="file_type_salt" value="<?php echo $orig_params['file_type_salt'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type_token" name="file_type_token" value="<?php echo $orig_params['file_type_token'] ?>">

<input type="hidden" id="<?php echo $params['file_type'] ?>_module" name="module" value="<?php echo $orig_params['module'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_module_salt" name="module_salt" value="<?php echo $orig_params['module_salt'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_module_token" name="module_token" value="<?php echo $orig_params['module_token'] ?>">

<div style="border-radius:0;" class="notification-box info m-n">

	<p>
		<label class="font-semibold text-uppercase dark block">Upload New Version</label>
		Version <?php echo $version ?> » <?php echo $display_name ?>
		<br />
		<label>(This is the latest version.)</label>
	</p>

</div>

<div class="table-display m-b-lg">
  	<?php 
  		//if( EMPTY( $display_name ) ) :
  	?>
	<div class="table-cell valign-top p-md" style="width:65%;">
		<div class="scroll-pane field-multi-attachment" style="height:380px">
			<input type="hidden" data-parsley-errors-container=".my_error_container" data-parsley-error-message="This field is required." data-parsley-required="true" name="files[]" id="version" value="" class="form_dynamic_upload"
				data-origfile=""
			>
			<input type="hidden" name="files_orig_filename[]" id="version_orig_filename" value="" class="form_dynamic_upload_origfilename">
			<a href="#" id="version_upload" class="tooltipped m-r-sm" data-position="bottom" data-delay="50" data-tooltip="Upload">Choose a file to upload</a>
			<div class="left-align my_error_container"></div>
    	</div>
	</div>
	<?php 
		//endif;
	?>
	<div class="table-cell valign-top b-l" style="border-color:#e2e7e7!important; width:35%; position:relative;">
	<!-- <form id="upload_file_form"> -->
		<div class="form-float-label">
			<div class="row m-n b-l-n">
				<div class="col s12">
					<div class="input-field">
						<label for="file_display_name" class="active required block">Display Name</label>
						<input type="text" data-parsley-required="true" data-parsley-maxlength="255" data-parsley-trigger="keyup" name="file_display_name" id="file_display_name" value="<?php echo $display_name ?>"/>
					</div>
				</div>
		  
			</div>
			<div class="row m-n b-l-n">
				<div class="col s12">
					<div class="input-field">
  						<label for="file_description" class="<?php echo $class ?>">Description</label>
						<textarea name="file_description" id="file_description" class="materialize-textarea" style="min-height:120px!important"></textarea>
					</div>
				</div>
			</div>
			<div class="row m-n">

				<div class="p-t-md p-b-md p-l-sm p-r-sm m-b-lg">
		    		<input type="checkbox" class="labelauty" id="minor_revision_flag" name="minor_revision_flag" data-labelauty="I have made minor revisions only|I have made major revisions">
		    		<label for="minor_revision_flag"></label>
			  	</div>

			</div>
		</div>	
	</div>
</div>