<?php 
	
	$tagline 		 = get_setting(GENERAL, "system_tagline");
	$title 		 	 = get_setting(GENERAL, "system_title");

	$app_version 	 = get_setting(VERSION, "application_version");
	$core_version 	 = get_setting(VERSION, "core_version");

	$sys_logo 		 = get_setting(GENERAL, "system_logo");
	$system_logo_src = base_url() . PATH_IMAGES . "logo_white.png";

	if( !EMPTY( $sys_logo ) )
	{
		$root_path 			= get_root_path();
		$sys_logo_path 		= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
		$sys_logo_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

		if( file_exists( $sys_logo_path ) )
		{
			$system_logo_src = output_image($sys_logo, PATH_SETTINGS_UPLOADS);
			$system_logo_src = @getimagesize($sys_logo_path) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
		}
	}
?>

<div class="center-align p-t-lg p-b-n font-xl">
	<img class="m-t-sm" src="<?php echo $system_logo_src ?>" style="height:65px;"/>
	<div class="m-t-sm font-semibold"><?php echo $title ?></div>
	<div class="m-t-xs font-md font-semibold"><?php echo $tagline ?></div>
	<div class="m-t-xs m-b-lg font-sm font-thin">v<?php echo $app_version ?></div>
	
	<div class="font-sm">Powered By Asiagate PHP Core v<?php echo $core_version ?></div>
</div>