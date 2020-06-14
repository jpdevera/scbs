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
  	<?php 
  		if( $check_lp ) :
  	?>
  	body 
  	{
  		background-image: url('<?php echo $path_url_lp_bg ?>') !important;
  	}
  	<?php 
  		endif;
  	?>
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
  <div id="wrapper">
    <div>
    	<?php 
	  		if( !EMPTY( $logout_inactivity ) ) :
	  	?>
	  		<?php 
	  			if( $logout_inactivity == 'inactivity' ) :
	  		?>
			<div class="message-box error">
    			<i class="material-icons">timer</i>
		  		You have been logged out due to inactivity.
		  	</div>
	  		<?php 
	  			endif;
	  		?>
	  	<?php 
	  		endif;
	  	?>
	  	
	  	
	  	<?php 
	  		if( !EMPTY( $maintenance_mode ) ) :
	  	?>
	  	<div class="message-box info" style="position: absolute; top: 0; left: 0; width: 100%; text-align: center;">
	  		<i class="material-icons" style="position: relative; left: 0; top: -2px;">info_outline</i>
			MAINTENACE: MODE: We apologize for the inconvenience. Our site is currently undergoing scheduled maintenance and upgrades. We will be back shortly.
	  	</div>
	  	<?php 
	  		endif;
	  	?>
    <div class="panel">
	  
	  <div class="left-panel center-align none">
	    <img src="<?php echo $system_logo_src ?>" class="logo <?php echo $logo_class ?>"/>
		<div class="title"><?php echo $title ?></div>
		<div class="sub-title"><?php echo $tagline ?></div>
		<div class="sub-title font-xs m-t-xs"><?php echo $site_description; ?></div>
	    
		<div id="panel-slider" class="owl-carousel" style="width:290px;">
		 <!--  <div class="item"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus efficitur odio eu nisi vestibulum eleifend.</p></div>
		 <div class="item">For inquiries, please email Asiagate Networks Inc. at <a href="mailto:inquiries@asiagate.com">inquiries@asiagate.com</a></div> -->
	    </div>
	  </div>
	  <div class="right-panel">
	    <div id="welcome-text"><span>Welcome.</span> Please login</div>
		
		<form id="login_form">
		  <input type="hidden" id="base_url" value="<?php echo base_url() ?>"/>
		  <input type="hidden" id="home_page" value="<?php echo CORE_HOME_PAGE ?>"/>
		  <input class="none" type="password" />
		  
		  <?php 
			$login_via = get_setting(LOGIN, "login_via");
			$type = "text";
			
			switch($login_via){
				case 'USERNAME_EMAIL':
					$field_title = "Username or Email or Mobile no"; 
				break;
				case 'EMAIL':
					$field_title = ucwords(strtolower($login_via));
					$type = strtolower($login_via);
				break;
				case 'MOBILE_NO':
					$field_title = ucwords(strtolower(str_replace('_', ' ', $login_via)));
					// $type = strtolower($login_via);
				break;
				default:
					$field_title = ucwords(strtolower($login_via));
			}
		  ?>
		  
		  <div class="input-field">
		    <input id="icon_username" name="username" type="<?php echo $type ?>" value="<?php echo $pass_username ?>" class="validate" placeholder="Enter <?php echo strtolower($field_title) ?>" />
		    <label for="icon_username" class="active"><?php echo $field_title ?></label>
		  </div>
		  <div class="input-field">
		    <input id="icon_password" name="password" type="password" class="validate" placeholder="Enter password" />
		    <label for="icon_password" class="active">Password</label>
		  </div>
		  <?php 
			$account_creator = get_setting(ACCOUNT, "account_creator");
		  
			if($account_creator == VISITOR
				OR $account_creator == VISITOR_NOT_APPROVAL
			){  ?>
			  <div class="link m-b-lg">
				Not yet a member? 

				<?php 
					// if( $check_has_agreement_text ) :
				?>
				<!-- <a onclick="modal_term_condition_init('', this)" data-modal_post='<?php echo $sign_up_arr_json ?>' href="#modal_term_condition">Sign up now!</a>
				<a onclick="modal_sign_up_init('', this)" id="modal_sign_up_link" style="display:none !important;" data-modal_post='<?php echo $sign_up_arr_json ?>' href="#modal_sign_up">Sign up now!</a> -->
				<?php 
					// else :
				?>
				<!-- aes_mname -->
				<a  data-modal_post='<?php echo $sign_up_arr_json ?>' href="<?php echo base_url() ?>Auth/sign_up_form">Sign up now!</a>
				<?php 
					// endif;
				?>
				
			  </div>
		  <?php } ?>
		  
		  <div class="table-display panel-footer">
			<div class="table-cell s5 valign-middle">
			  <div class="input-field m-n">
				<button type="button" id="submit_login" class="btn waves-effect" data-btn-action="<?php echo BTN_LOGGING_IN ?>"><?php echo BTN_LOG_IN ?></button>
			  </div>
			</div>
			<div class="table-cell s7 valign-middle">
			  <a class=" m-l-sm" onclick="modal_forgot_pw_init()" href="#modal_forgot_pw">Forgot your password?</a>
			
			</div>
		  </div>
		  <div class="table-display panel-footer p-t-md">
		  	<div class="table-cell s12 valign-middle none">
			  <div class="input-field m-n">
			  	<?php 
			  		if( $ch_login_sys_param ) :
			  	?>
			  	<?php 
					if( in_array( VIA_FACEBOOK, $login_with_arr_a ) ) :
				?>
			  	<a class="btn sm blue bg white-text" href="<?php echo $login_api_route ?>" >Login via Facebook</a>
			  	<?php 
			  		endif;
			  	?>
			  	<?php 
					if( in_array( VIA_GOOGLE, $login_with_arr_a ) ) :
				?>
				<a class="btn sm red bg white-text" href="<?php echo $login_api_route.'1' ?>" >Login via Google</a>
				<?php 
					endif;
				?>
				<?php 
					endif;
				?>
			  </div>
		  </div>
	    </form>
	  </div>
    </div>
    <div id="overlay-wrapper"></div>
  </div>
  
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

	$('#icon_password').hidePassword('focus', {
	  toggle: { className: 'show-hide-btn' }
	});
	$('#icon_password').focus(function() {
		$('label[for="icon_password"]').addClass('active-link');
	}).focusout(function() {
		$('label[for="icon_password"]').removeClass('active-link');
	});

    });

  </script>
</body>
</html>