<?php 

	$hash_code 	= (!empty($system_code)) ? base64_url_encode($system_code) : '';
	$salt 		= gen_salt();
	$token 		= in_salt($hash_code, $salt);

	$system_code = (!empty($system_code)) ? $system_code : '';
	$system_name = (!empty($system_name)) ? $system_name : '';
	$system_link = (!empty($system_link)) ? $system_link : '';
	$description = (!empty($description)) ? $description : '';
	$system_logo = (!empty($system_logo)) ? $system_logo : '';
	$status  	 = (!empty($on_off_flag)) ? $on_off_flag : '';	

	$img_src = base_url() . PATH_SYSTEMS_UPLOADS . 'default_logo.png';

	$photo_path 	= "";

	if( !EMPTY( $system_logo ) )
	{
		$root_path 			= get_root_path();
		$photo_path = $root_path.PATH_SYSTEMS_UPLOADS.$system_logo;
		$photo_path = str_replace(array('\\','/'), array(DS,DS), $photo_path);
		
		if( file_exists( $photo_path ) )
		{			
			$img_src = output_image($system_logo, PATH_SETTINGS_UPLOADS);
		}
		else
		{
			$photo_path = "";
		}
	}

	$disable_code 		= '';

	if( !EMPTY( $check_used ) )
	{
		$disable_code 	= 'disabled';
	}

	$allowed_inactive 	= TRUE;

	if( ISSET( $core_flag ) AND !EMPTY( $core_flag ) )
	{
		$allowed_inactive = FALSE;
	}
?>
<input type = "hidden" name = "id"    value = "<?php echo $hash_code ?>">
<input type = "hidden" name = "salt"  value = "<?php echo $salt ?>">
<input type = "hidden" name = "token" value = "<?php echo $token ?>">

<div class="form-basic">

	<div class="p-md">
		<div class="row m-t-sm m-b-n">
			<div class="col s12">
				<div class="input-field">
					<input type = "text" <?php echo $disable_code ?> data-parsley-maxlength="25" data-parsley-trigger="keyup" data-parsley-required="true" name = "system_code" value="<?php echo $system_code ?>" placeholer = "System Code" />
					<label class="active required">System Code</label>
				</div>
			</div>
		</div>
	</div>

	<div class="p-md">
		<div class="row m-t-sm m-b-n">
			<div class="col s12">
				<div class="input-field">
					<input type = "text" data-parsley-maxlength="100" data-parsley-trigger="keyup" data-parsley-required="true" name = "system_name" value="<?php echo $system_name ?>" placeholer = "System Name" />
					<label class="active required">System Name</label>
				</div>
			</div>
		</div>
	</div>

	<div class="p-md">
		<div class="row m-t-sm m-b-n">
			<div class="col s12">
				<div class="input-field">
					<input type = "text" name = "description" value="<?php echo $description ?>" placeholer = "System Description" />
					<label class="active">Description</label>
				</div>
			</div>
		</div>
	</div>

	<div class="p-md">
		<div class="row m-t-sm m-b-n">
			<div class="col s12">
				<div class="input-field">
					<input type = "text" name = "system_link" value="<?php echo $system_link ?>" placeholer = "System Link" />
					<label class="active">System Link</label>
				</div>
			</div>
		</div>
	</div>

	<?php 
		if( $allowed_inactive ) :
	?>

	<div class="p-md">
		<div class="row m-b-n">
			<div class="col s6">
				<div class="input-field">
					<label class="active" for="status">Status</label>
					<div>
						<input name = "status" id="status" type="checkbox" class="labelauty m-t-md" data-labelauty="Inactive|Active" value="<?php ECHO 1; ?>" <?php ECHO (ISSET($status) ? ($status == 1 ? 'checked' : ''): 'checked');?>/>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php 
		endif;
	?>

	<div class="p-md">
		<div class="row m-b-n">
			<div class="col s12">
				<h6>System Logo</h6>
				<div class = "m-t-sm help-text">
					It is recommended that you use a logo with a transparent background <small>(.png file extension)</small>. This logo will appear on the upper left corner of the screen and login page.
				</div>

				<div class="avatar-container lg" style="width:100%;">
					<input type="hidden" name="system_logo" id="system_logo" value="<?php echo $system_logo ?>">
					<div class="avatar-action">
						<a href="#" id="system_logo_upload" class="tooltipped m-r-sm" data-position="bottom" data-delay="50" data-tooltip="Upload"><i class="material-icons">file_upload</i></a>
					</div>
					<img id="system_logo_src" src="<?php echo $img_src ?>" class="m-b-md">
				</div>
			</div>
	  	</div>
  	</div>

</div>