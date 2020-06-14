<?php
/* GET SYSTEM LOGO */

$sys_logo 		 = get_setting(GENERAL, "system_logo");
$system_logo_src = base_url() . PATH_IMAGES . "logo_white.png";

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

$system_title 		= get_setting(GENERAL, "system_title");

if( $initial_flag )
{
	$value_s = 'Changing';
}
else
{
	$value_s = 'Resetting';
}
$system_desc 	= get_setting(GENERAL, "system_description");	

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


$show_title_on_login 			= get_setting(GENERAL, "show_title_on_login");
$show_tagline_on_login 			= get_setting(GENERAL, "show_tagline_on_login");

$sys_tagline 					= get_setting(GENERAL, "system_tagline");

$title 							= "";
$tagline 						= "";

if( !EMPTY( $show_title_on_login ) )
{
	$title 						= $system_title;
}
else
{
	$title 						= "ANI";
}

if( !EMPTY( $show_tagline_on_login ) )
{
	$tagline 					= $sys_tagline;
}
else
{
	$tagline 					= "PHP Core";
}
?>
<html>
<head>
  <title>Reset Password |<?php echo $system_title ?></title>
  <link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>login.css">
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>reset_password.css">
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
</head>
<body class="default">
  
  <div id="wrapper">
    <div>
    <div class="panel">
	  
	  <div class="left-panel center-align">
	    <img src="<?php echo $system_logo_src ?>" class="logo"/>
		<div class="title" style="color : #323232 !important;"><?php echo $title ?></div>
		<div class="sub-title"><?php echo $tagline ?></div>
	    
	<!-- 	<div id="panel-slider" class="owl-carousel" style="width:290px;">
	  <div class="item"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus efficitur odio eu nisi vestibulum eleifend.</p></div>
	  <div class="item">For inquiries, please email Asiagate Networks Inc. at <a href="mailto:inquiries@asiagate.com">inquiries@asiagate.com</a></div>
		    </div> -->
	  </div>
	  <div class="right-panel">
	    <div id="welcome-text">
	    	<span>
	    		<?php 
					if( $initial_flag ):
				?>
					Change Password
				<?php 
					else: 
				?>
					Reset Password
				<?php endif; ?>
	    	</span>
	    	<?php 
				if( $initial_flag ): 
			?>
			<!-- IF CHANGE PASSWORD (INITIAL LOGIN) -->
			<h6 style="color: #677485;font-size:10px; !important;">
				You are required to change your password in order to continue.
			</h6>
			<?php 
				endif;
			?>
	    </div>
		
		<form id="reset-form">
		  <input type="hidden" id="base_url" value="<?php echo base_url() ?>"/>
		  <input type="hidden" id="home_page" value="<?php echo CORE_HOME_PAGE ?>"/>
		  <input type="hidden" name="id" value="<?php echo $id ?>">
		  <input type="hidden" name="key" value="<?php echo $key ?>">
		  <input type="hidden" name="initial_flag" value="<?php echo $initial_flag ?>">
		  <input type="hidden" id="to_sign_in" value="<?php echo $to_sign_in ?>">
		  <input type="hidden" id="username" value="<?php echo $username ?>">
		  <input class="none" type="password" />
		  
		  <div class="input-field">
		    <input id="password" name="password" id="password" type="password" class="validate" placeholder="Enter new passwors" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-parsley-pass="true" />
		    <label for="password" class="active">New password</label>
		  </div>
		  <div class="input-field">
		    <input id="retype_password" name="retype_password" data-parsley-required="true" data-parsley-validation-threshold="0" data-parsley-trigger="keyup" data-parsley-equalto="#password" data-parsley-equalto-message="Passwords don't match." type="password" class="validate" placeholder="Re-type password" />
		    <label for="retype_password" class="active">Confirm Password</label>
		  </div>
			
		  
		  <div class="table-display panel-footer">
			<div class="table-cell s5 valign-middle">
			  <div class="input-field m-n">
				<button type="submit" id="reset_password" data-btn-action="<?php echo $value_s ?>" class="btn waves-effect" value="<?php echo $value_s ?>">
					<?php if( $initial_flag ): ?>
						Change Password
					<?php else: ?>
						Reset Password
					<?php endif; ?>
						
				</button>
			  </div>
			</div>
		
		  </div>
	    </form>
	  </div>
    </div>
    <div id="overlay-wrapper"></div>
  </div>
  
  <!-- NOTIFICATION SECTION -->
  <div class="notify success none"><div class="success"><h4><span>Success</span></h4><p></p></div></div>
  <div class="notify error none"><div class="error"><span><i class="material-icons">priority_high</i></span><h4>Error</h4><p></p></div></div>
  
  
  <!-- PLATFORM SCRIPT -->
  <script src="<?php echo base_url().PATH_JS ?>materialize.js"></script>
  <!-- END PLATFORM SCRIPT -->

  <!-- LOBIBOX SCRIPT -->
  <script src="<?php echo base_url() . PATH_JS.JS_LOBIBOX ?>.js" type="text/javascript"></script>
  <!-- END LOBIBOX SCRIPT -->
  
  <script src="<?php echo base_url().PATH_JS ?>script.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>common.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>auth.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>reset_password.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>parsley.min.js" type="text/javascript"></script>
  
  <!-- OWL CAROUSEL SCRIPT -->
  <link href="<?php echo base_url().PATH_CSS ?>owl.carousel.css" rel="stylesheet" />
  <link href="<?php echo base_url().PATH_CSS ?>owl.theme.css" rel="stylesheet" />
  <script src="<?php echo base_url().PATH_JS ?>owl.carousel.js"></script>
  <!-- END OWL CAROUSEL SCRIPT -->
  
  <!-- JSCROLLPANE SCRIPT -->
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.mousewheel.js"></script>
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.jscrollpane.js"></script>
  <!-- END JSCROLLPANE SCRIPT -->
  
  <!-- POPMODAL SCRIPT -->
  <link type="text/css" href="<?php echo base_url().PATH_CSS ?>popModal.css" rel="stylesheet" media="all" />
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>popModal.min.js"></script>
  <!-- END POPMODAL SCRIPT -->

  <script src="<?php echo base_url().PATH_JS ?>initializations.js" type="text/javascript"></script>

  <script src="<?php echo base_url() . PATH_JS ?>initial.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url() . PATH_JS ?>parsley_extend.js" type="text/javascript"></script>
  
  <script type="text/javascript">
    //var modalObj = new handleModal({ controller : 'sign_up', modal_id: 'modal_sign_up' });
	
    $(function(){	  
      $("#panel-slider").owlCarousel({
		navigation : false,
		slideSpeed : 300,
		paginationSpeed : 400,
		singleItem : true
      });
    });

  </script>
</body>
</html>