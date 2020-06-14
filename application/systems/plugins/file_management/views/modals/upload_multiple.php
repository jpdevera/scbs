<?php 
	$display_name 	= "";
	$description 	= "";
	$class 			= "";

	$file_name 		= "";
	$original_name 	= "";

	if( !EMPTY( $details ) )
	{
		$display_name 	= ( ISSET( $details['display_name'] ) AND !EMPTY( $details['display_name'] ) ) ? $details['display_name'] : "";
		$description 	= ( ISSET( $details['description'] ) AND !EMPTY( $details['description'] ) ) ? $details['description'] : "";
		$file_name 		= ( ISSET( $details['file_name'] ) AND !EMPTY( $details['file_name'] ) ) ? $details['file_name'] : "";
		$original_name 	= ( ISSET( $details['original_name'] ) AND !EMPTY( $details['original_name'] ) ) ? $details['original_name'] : "";
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

<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type" name="file_type" value="<?php echo $orig_params['file_type'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type_salt" name="file_type_salt" value="<?php echo $orig_params['file_type_salt'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_file_type_token" name="file_type_token" value="<?php echo $orig_params['file_type_token'] ?>">

<input type="hidden" id="<?php echo $params['file_type'] ?>_module" name="module" value="<?php echo $orig_params['module'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_module_salt" name="module_salt" value="<?php echo $orig_params['module_salt'] ?>">
<input type="hidden" id="<?php echo $params['file_type'] ?>_module_token" name="module_token" value="<?php echo $orig_params['module_token'] ?>">

<!-- for testing purpose -->
<input type="hidden" id="document_hide" value="<?php echo $file_name ?>">
<input type="hidden" id="document_orig_hide" value="<?php echo $original_name ?>">
<input type="hidden" id="document_display_name_hide" value="<?php echo $display_name ?>">
<input type="hidden" id="document_description_hide" value="<?php echo $description ?>">

<div class="table-display m-b-lg">
  	<?php 
  		if( EMPTY( $display_name ) ) :
  	?>
	<div class="table-cell valign-top p-md" style="width:65%;">
		<div class="field-multi-attachment">
			<input type="hidden" data-parsley-errors-container=".my_error_container" data-parsley-error-message="This field is required." data-parsley-required="true" name="files[]" id="<?php echo $upload_multi ?>" value="" class="form_dynamic_upload"
				data-origfile=""
			>
			<input type="hidden" name="files_orig_filename[]" id="<?php echo $upload_multi ?>_orig_filename" value="" class="form_dynamic_upload_origfilename">
			<a href="#" id="<?php echo $upload_multi ?>_upload" class="tooltipped m-r-sm" data-position="bottom" data-delay="50" data-tooltip="Upload">Choose a file to upload</a>
			<div class="left-align my_error_container"></div>
    	</div>
	</div>
	<?php 
		endif;
	?>
</div>