<?php
/* GET SYSTEM LOGO */
$sys_logo 		 = get_setting(GENERAL, "system_logo");
$system_logo_src = base_url() . PATH_IMAGES . "logo_white.png";

$favicon 		 	= get_setting(GENERAL, "system_favicon");
$system_favicon_src = base_url() . PATH_IMAGES . "favicon.ico";

$avatar_src 	 	= base_url() . PATH_IMAGES . "avatar.jpg";

$logo_class 		= "";

$root_path 			= get_root_path();

$login_with_arr_sel 		= get_setting(LOGIN, 'login_api');
$login_with_arr_sel 		= trim($login_with_arr_sel);

$login_with_arr_a 		= array();

if( !EMPTY( $login_with_arr_sel ) )
{
	$login_with_arr_a 	= explode(',', $login_with_arr_sel);
}

if( !EMPTY( $sys_logo ) )
{
	$sys_logo_path 		= $root_path. PATH_SETTINGS_UPLOADS . $sys_logo;
	$sys_logo_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

	if( file_exists( $sys_logo_path ) )
	{
		// $system_logo_src = base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
		$system_logo_src = output_image($sys_logo, PATH_SETTINGS_UPLOADS);
		$system_logo_src = @getimagesize($sys_logo_path) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
	}
}

list($width, $height) 	= getimagesize($system_logo_src);
$logo_class				= ($width > $height) ? "landscape-logo": "portait-logo";

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

$maintenance_mode 				= get_setting(GENERAL, "maintenance_mode");

$show_title_on_login 			= get_setting(GENERAL, "show_title_on_login");
$show_tagline_on_login 			= get_setting(GENERAL, "show_tagline_on_login");

$sys_title 						= get_setting(GENERAL, "system_title");
$sys_tagline 					= get_setting(GENERAL, "system_tagline");

$title 							= "";
$tagline 						= "";
$site_description				= get_setting(GENERAL, "system_description");

if( !EMPTY( $show_title_on_login ) )
{
	$title 						= $sys_title;
}
else
{
	// $title 						= "ANI";
}

if( !EMPTY( $show_tagline_on_login ) )
{
	$tagline 					= $sys_tagline;
}
else
{
	// $tagline 					= "PHP Core";
}

$sign_up_arr 					= array(
	'sign_up'	=> 1
);
$sign_up_arr_json 				= json_encode( $sign_up_arr );

$lp_background 	= get_setting(GENERAL, "lp_background");



$style_lp 	= '';

if( !EMPTY( $lp_background ) ) 
{
	$path_lp_bg 	= FCPATH.PATH_SETTINGS_UPLOADS.$lp_background;
	$path_lp_bg 	= str_replace(array('/', '\\'), array(DS, DS), $path_lp_bg);

	if( file_exists( $path_lp_bg ) )
	{
	}
}
$check_lp 	= FALSE;
$path_url_lp_bg = '';
if( !EMPTY( $lp_background ) ) 
{
	$path_lp_bg 	= FCPATH.PATH_SETTINGS_UPLOADS.$lp_background;
	$path_lp_bg 	= str_replace(array('/', '\\'), array(DS, DS), $path_lp_bg);



	if( file_exists( $path_lp_bg ) )
	{
		$check_lp = TRUE;
		$path_url_lp_bg = base_url().PATH_SETTINGS_UPLOADS.$lp_background;
	}
}
?>
<html>
<head>
  <title><?php echo get_setting(GENERAL, "system_title") ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="description" content="<?php echo $site_description; ?>"/>
  <link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo" />
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>login.css">
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>skins.css">
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>component.css">
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>materialize.css"  media="screen,projection"/>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url().PATH_CSS ?>material_icons.css">
  <link type="text/css" href="<?php echo base_url().PATH_CSS ?>jquery.jscrollpane.css" rel="stylesheet" media="all" />
  <link type="text/css" rel="stylesheet" href="<?php echo base_url().PATH_CSS.CSS_LOBIBOX ?>.css" />
  <script src="<?php echo base_url().PATH_JS ?>less.min.js" type="text/javascript"></script>
  
  <!-- JQUERY 2.1.1+ IS REQUIRED BY MATERIALIZE TO FUNCTION -->
  <script src="<?php echo base_url().PATH_JS ?>jquery-3.1.0.min.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>jquery-ui.min.js" type="text/javascript"></script>
  <style type="text/css">
  	 	html,body {
            height: 100%;
        }
        html {
            display: table;
            margin: auto;
        }
         body {
            display: table-cell;
            vertical-align: middle;
        }
        body .input-field .show-hide-btn,
		html .input-field .show-hide-btn {
		    background: rgba(255, 255, 255, .4);
		    border: none;
		    border-radius: 2px;
		    padding: 6px;
		    font-size: 11px;
		    font-weight: 500!important;
		    text-transform: uppercase;
		    color: rgba(0, 0, 0, .8);
		    margin-right: 15px
		}
  </style>
</head>
<body class="default">
	<input type="hidden" id="base_url" value="<?php echo base_url() ?>">
	<form id="login_form">

		<input type="hidden" id="base_url" value="<?php echo base_url() ?>"/>
		<input type="hidden" id="home_page" value="<?php echo CORE_HOME_PAGE ?>"/>
		<input class="none" type="password" />
		<div class="card-panel z-depth-5">

			<div class="row margin" style="margin-bottom: 20px;">
				<div class="col s12 m12 l12 center">
		   			<img src="<?php echo $system_logo_src ?>" class="responsive-img circle" style="width:100px;"/>
					<div class="title"><?php echo $title ?></div>
					<div class="sub-title"><?php// echo $tagline ?></div>
					<div class="sub-title font-xs m-t-xs"><?php// echo $site_description; ?></div>
				</div>
			</div>

			<div class="col s12 m12 l12">
				<div class="input-field">
					<i class="material-icons prefix">person</i>
			    	<input id="icon_username" name="username" type="text" value="<?php echo $pass_username ?>" class="validate" placeholder="Enter username" />
					<label for="username">Username</label>
				</div>
			</div>

			<div class="col m12 l12">
				<div class="input-field">
					<i class="material-icons prefix">lock</i>
					<input id="icon_password" name="password" type="password" class="validate" placeholder="Enter password" />
					<label for="password">Password</label>
				</div>
			</div>

			<div class="left">
				<input type="checkbox" id="checkbox">
				<label for="checkbox">Remember Me</label>
			</div>
			<br><br>

			<div class="center">
				<button style="width:100%;" type="button" id="submit_login" class="btn waves-effect waves-light" data-btn-action="<?php echo BTN_LOGGING_IN ?>">LOG IN</button>
			</div>

			<div class="" style="font-size:14px;"><br>
				<a class="m-l-sm" onclick="modal_forgot_pw_init()" href="#modal_forgot_pw">Forgot your password?</a>
			</div><br>
		</div>
	</form>
		
		  
  <!-- NOTIFICATION SECTION -->
  <div class="notify success none"><div class="success"><h4><span>Success</span></h4><p></p></div></div>
  <div class="notify error none"><div class="error"><h4><span>Error</span></h4><p></p></div></div>
  
  <!-- PLATFORM SCRIPT -->
	<script src="<?php echo base_url().PATH_JS ?>constants.js"></script>
	<!-- END PLATFORM SCRIPT -->
  <!-- PLATFORM SCRIPT -->
  <script src="<?php echo base_url().PATH_JS ?>materialize.js"></script>
  <!-- END PLATFORM SCRIPT -->

  <!-- LOBIBOX SCRIPT -->
  <script src="<?php echo base_url() . PATH_JS.JS_LOBIBOX ?>.js" type="text/javascript"></script>
  <!-- END LOBIBOX SCRIPT -->
  
  <script src="<?php echo base_url().PATH_JS ?>script.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>common.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>auth.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>parsley.min.js" type="text/javascript"></script>
  
  <!-- OWL CAROUSEL SCRIPT -->
  <link href="<?php echo base_url().PATH_CSS ?>owl.carousel.css" rel="stylesheet" />
  <link href="<?php echo base_url().PATH_CSS ?>owl.theme.css" rel="stylesheet" />
  <script src="<?php echo base_url().PATH_JS ?>owl.carousel.js"></script>
  <!-- END OWL CAROUSEL SCRIPT -->
  
  <!-- JSCROLLPANE SCRIPT -->
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.mousewheel.js"></script>
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.jscrollpane.js"></script>

  <script type="text/javascript" src="<?php echo base_url() . PATH_JS ?>moment.js"></script>
  <!-- END JSCROLLPANE SCRIPT -->

  <!-- BLOCK UI SCRIPT -->
	<script src="<?php echo base_url() . PATH_JS ?>jquery.blockUI.js" type="text/javascript"></script>
	<!-- END BLOCK UI SCRIPT -->
  
  <!-- POPMODAL SCRIPT -->
  <link type="text/css" href="<?php echo base_url().PATH_CSS ?>popModal.css" rel="stylesheet" media="all" />
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>popModal.min.js"></script>
  <!-- END POPMODAL SCRIPT -->
  
  <script src="<?php echo base_url().PATH_JS ?>initializations.js" type="text/javascript"></script>

  <script src="<?php echo base_url() . PATH_JS ?>initial.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url() . PATH_JS ?>parsley_extend.js" type="text/javascript"></script>

  <link type="text/css" href="<?php echo base_url().PATH_CSS ?>hideShowPassword.css" rel="stylesheet" media="all" />
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>hideShowPassword.min.js"></script>

  <script type="text/javascript">
	
    $(function(){	  

      parsley_listener_duplicate();

      $("#panel-slider").owlCarousel({
		navigation : false,
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem : true
      });

	/*$('#icon_password').hidePassword('focus', {
	  toggle: { className: 'show-hide-btn' }
	});
	$('#icon_password').focus(function() {
		$('label[for="icon_password"]').addClass('active-link');
	}).focusout(function() {
		$('label[for="icon_password"]').removeClass('active-link');
	});*/

    });

  </script>
</body>
</html>