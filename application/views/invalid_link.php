<?php 
	$favicon 		 	= get_setting(GENERAL, "system_favicon");
	$system_favicon_src = base_url() . PATH_IMAGES . "favicon.ico";

	if( !EMPTY( $favicon ) )
	{
		
		$sys_fav_path 		= $ROOT_PATH. PATH_SETTINGS_UPLOADS . $favicon;
		$sys_fav_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_fav_path);
		
		if( file_exists( $sys_fav_path ) )	
		{
			$system_favicon_src = output_image($favicon, PATH_SETTINGS_UPLOADS);
			$system_favicon_src = @getimagesize($sys_fav_path) ? $system_favicon_src : base_url() . PATH_IMAGES . "favicon.ico";		
			
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Content Unavailable</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>materialize.css" media="screen,projection" />
  	<link rel="stylesheet" type="text/css" href="<?php echo base_url().PATH_CSS ?>material_icons.css">
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>error.css">
</head>
<body class="dark">
	<div id="wrapper">
		<div class="center-align">
			<i class="material-icons">error_outline</i>
			<?php 
				if( !EMPTY( $reject_dpa ) ) :
			?>
			<h5><?php echo $reject_dpa_msg ?></h5>
			<?php 
				elseif( !EMPTY( $invalid_Login ) ) :
			?>
			<h5><?php echo $invalid_Login_msg ?></h5>
			<?php 
				else :
			?>
			<h5>This content is currently unavailable</h5>
			<p>You have tried to use a one-time activation link that has been used already. <br/>Please click the Log In button below to continue.</p>
			<a href="<?php echo base_url() ?>" class="btn m-t-md">Log In</a>
			<?php 
				endif;
			?>
		</div>
	</div>
</body>
</html>