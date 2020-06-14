<?php 
  $salt 	= gen_salt();
  $token 	= in_salt($this->session->userdata('user_id'), $salt);

  $change_upload_path	= get_setting(MEDIA_SETTINGS, "change_upload_path");

  $checked_upload_path 	= ( !EMPTY( $change_upload_path ) ) ? 'checked' : '';

  $enable_image_compression	= get_setting(MEDIA_SETTINGS, "enable_image_compression");

  $checked_enable_image_compression 	= ( !EMPTY( $enable_image_compression ) ) ? 'checked' : '';

  $media_ft 			= get_setting(MEDIA_SETTINGS, 'file_upload_type');

  $check_ft_dir 		= 'checked';
  $check_ft_db 			= '';

  // print_r(get_media_upload_type());die;

	if( !EMPTY( $media_ft ) )
	{
		$check_ft_dir 	= '';
		$check_ft_db 	= '';

		switch( $media_ft )
		{
			case MEDIA_UPLOAD_TYPE_DIR :
				$check_ft_dir 	= 'checked';
				$check_ft_db 	= '';
			break;
			case MEDIA_UPLOAD_TYPE_DB :
				$check_ft_dir 	= '';
				$check_ft_db 	= 'checked';
			break;
			default :
				$check_ft_dir 	= '';
				$check_ft_db 	= '';
			break;
		}
	}

  $placeholder_path 	= FCPATH.'uploads';
  $placeholder_path 	= str_replace(array('/', '\\'), array(DS, DS), $placeholder_path);
?>
<div class="row">
	<div class="col l10 m12 s12">
		<form id="media_settings_form" class="m-t-lg">
			<input type="hidden" name="id" value="<?php echo $this->session->userdata('user_id') ?>"/>
		  	<input type="hidden" name="salt" value="<?php echo $salt ?>">
		  	<input type="hidden" name="token" value="<?php echo $token ?>">

		  	<div class="form-basic">
				<div id="media" class="scrollspy table-display white box-shadow">
					<div class="table-cell bg-dark p-lg valign-top" style="width:25%">
						<label class="label mute">File Upload</label>
						<p class="caption m-t-sm white-text">File Upload.</p>
					</div>
					<div class="table-cell p-lg valign-top">
						<div class="row m-b-n">
							<h6>File Upload Path</h6>
							<div class="help-text">Change path where the uploaded files will be stored. The directory must be writeable by the system and not accessible over the web. (Note: System saves file in the "application_folder_name/uploads/“ by default)</div>

							<div class="row">
								<div class="col s6">
									<input type="checkbox" class="labelauty" name="change_upload_path" id="change_upload_path" value="" data-labelauty="Change Path" onclick="toggle('change_upload_path', 'custom_upload_path_div', Settings.custom_path_toggle)" <?php echo $checked_upload_path ?> />
								</div>
							</div>
	
							<div id="custom_upload_path_div" style="display:none">
								<div class="row m-b-n">
									<div class="col s12">
										<div>
											<h6>Uploaded File Path Type</h6>
											<div class="help-text">Choose where to upload and save files.</div>
										</div>
									</div>
								</div>
								<div class="row">
					  				<div class="col l4 m4 s12">
					  					<input type="radio" class="labelauty file_upload_type" name="file_upload_type" id="file_upload_type_path" value="<?php echo MEDIA_UPLOAD_TYPE_DIR ?>" <?php echo $check_ft_dir ?> data-labelauty="Directory"/>
					  				</div>
					  				<div class="col l4 m4 s12">
					  					<input type="radio" class="labelauty file_upload_type" name="file_upload_type" id="file_upload_type_database" value="<?php echo MEDIA_UPLOAD_TYPE_DB ?>" <?php echo $check_ft_db ?> data-labelauty="Database"/>
					  				</div>
					  			</div>
					  			<div id="change_dir_type_div" style="display:none;">
									<div class="row p-md p-b-n m-b-n">
										<div class="col s9">
											 <label class="label m-b-sm">New Upload path</label>
											 <div class="help-text">Specify new path where the uploaded files will be stored. (e.g. C:\uploads)</div>
										</div>
									</div>
									<div class="row p-md p-t-n m-t-n p-b-n m-b-n">
										<div class="col s9">
											 <input type="text" data-parsley-trigger="keyup" placeholder="<?php echo $placeholder_path ?>" name="new_upload_path" id="new_upload_path" value="<?php echo get_setting(MEDIA_SETTINGS, "new_upload_path") ?>"/>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div id="image_compression" class="scrollspy table-display m-t-lg white box-shadow">
					<div class="table-cell bg-dark p-lg valign-top" style="width:25%">
						<label class="label mute">Image Compression</label>
						<p class="caption m-t-sm white-text">Enable image compression and choose the quality of image.</p>
					</div>
					<div class="table-cell p-lg valign-top">
						<div class="row m-b-n">
							<h6>Image Compression</h6>
							<div class="help-text">Compress images for smaller file size and faster loading.</div>

							<div class="row">
								<div class="col s6">
									<input type="checkbox" class="labelauty" name="enable_image_compression" id="enable_image_compression" value="" data-labelauty="Enable Image Compression" onclick="toggle('enable_image_compression', 'quality_compression_div')" <?php echo $checked_enable_image_compression ?> />
								</div>
							</div>

							<div id="quality_compression_div" style="display:none">
								<div class="row m-b-n">
									<div class="col s12">
										<div>
											<h6>Choose the image quality</h6>
											<div class="help-text">.</div>
										</div>
									</div>
									<div class="row">
										<div class="col s12">
											<div id="test-slider"></div>
											<div class="help-text p-t-sm">0 (worst quality, smallest file size) to 100 (best quality, actual file size).</div>
											<input type="hidden" name="image_quality_compression"  id="slider-range-value" value="<?php echo get_setting(MEDIA_SETTINGS, "image_quality_compression") ?>" />
										</div>
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
					  <button class="btn waves-effect waves-light bg-success" type="button" id="save_media_settings" value="<?php echo BTN_SAVING ?>" data-btn-action="<?php echo BTN_SAVING; ?>"><?php echo BTN_SAVE ?></button>
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
			<li><a href="#media">Media</a></li>
			<li><a href="#image_compression">Image Compression</a></li>
		  </ul>
		</div>
	  </div>
	</div>

</div>

