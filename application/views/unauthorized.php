<?php 
	$favicon 		 	= get_setting(GENERAL, "system_favicon");
	$system_favicon_src = base_url() . PATH_IMAGES . "favicon.ico";

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
?>
<!DOCTYPE html>
<html>
<head>
	<title>Access Denied!</title>
	<link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo"/>
	<link rel="stylesheet" media="screen" href="<?php echo base_url().PATH_CSS ?>style.css" />
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>materialize.css"  media="screen,projection"/>
</head>
<body id="unauthorized-page">
	<div class="wrapper">
		<h1>Access <span class="red-text text-darken-2">Denied</span></h1>
		<p>You do not have sufficient permission to access this page.<br/>
		Please contact your site administrator to request access.</p>
		<a href="<?php echo base_url() . PROJECT_CORE ?>/dashboard/" class="btn-large m-t-sm">&laquo; Back to dashboard</a>
	</div>
</body>
</html>