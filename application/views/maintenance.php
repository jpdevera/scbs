<?php
$sys_logo 		 = get_setting(GENERAL, "system_logo");
$system_logo_src = base_url() . PATH_IMAGES . "logo_white.png";

$favicon 		 	= get_setting(GENERAL, "system_favicon");
$system_favicon_src = base_url() . PATH_IMAGES . "favicon.ico";

$avatar_src 	 	= base_url() . PATH_IMAGES . "avatar.jpg";
$root_path 			= get_root_path();

if( !EMPTY( $sys_logo ) )
{
	$sys_logo_path 		= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
	$sys_logo_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

	if( file_exists( $sys_logo_path ) )
	{
		$system_logo_src = output_image($sys_logo, PATH_SETTINGS_UPLOADS);
		$system_logo_src = @getimagesize($sys_logo_path) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
	}
}

/* GET SYSTEM FAVICON */
if( !EMPTY( $favicon ) )
{
	$sys_fav_path 		= $root_path. PATH_SETTINGS_UPLOADS . $favicon;
	$sys_fav_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_fav_path);
	
	if( file_exists( $sys_fav_path ) )	
	{
		$system_favicon_src = output_image($favicon, PATH_SETTINGS_UPLOADS);

		$system_favicon_src = @getimagesize($sys_fav_path) ? $system_favicon_src : base_url() . PATH_IMAGES . "favicon.ico";		
		
	}
}



/* GET USER AVATAR */
$avatar_path 	= $root_path . PATH_USER_UPLOADS . $this->session->userdata('photo');
$avatar_path 	= str_replace(array('\\','/'), array(DS,DS), $avatar_path);

if( !is_dir( $avatar_path ) AND file_exists( $avatar_path ) )
{	
	$avatar_src = output_image($this->session->userdata('photo'), PATH_USER_UPLOADS);

	$avatar_src = @getimagesize($avatar_path) ? $avatar_src : base_url() . PATH_IMAGES . "avatar.jpg";	
}

$user_roles = implode(",",$this->session->user_roles);

$class_compact_header = !EMPTY(get_setting(LAYOUT, "sidebar_menu")) ? "cd-compact-header" : "";

$pass_data 					= array();

$pass_data['resources'] 	= $resources;
$pass_data['initial'] 		= TRUE;

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo get_setting(GENERAL, "system_title") ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>materialize.css" media="screen,projection" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>maintenance.css">
</head>
<body class="default">
	<div id="wrapper">
		<div class="center-align">
	    	<img src="<?php echo base_url() . PATH_IMAGES ?>mo1.jpg" style="height:auto; width:620px;"/>
    		<div class="font-semibold" style="font-size:30px;">
    			Site Under Maintenance
    		</div>
			<span style="color:#9A9389 !important; font-size: 17px !important;  text-align: center !important;" >	
				We should be back shortly. Thank you for your patience.
			</span>
		</div>
    </div>
</body>
</html>