<?php
/* GET SYSTEM LOGO */
$sys_logo 		 = get_setting(GENERAL, "system_logo");
$system_logo_src = base_url() . PATH_IMAGES . "logo_white.png";

$favicon 		 	= get_setting(GENERAL, "system_favicon");
$system_favicon_src = base_url() . PATH_IMAGES . "favicon.ico";

$avatar_src 	 	= base_url() . PATH_IMAGES . "avatar.jpg";

$logo_class 		= "";

$root_path 			= get_root_path();

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

?>
<html>
<head>
	<title><?php echo get_setting(GENERAL, "system_title") ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo" />
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>skins.css">
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>component.css">
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>style_wizard.css">
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
	<input type="hidden" id="base_url" value="<?php echo base_url() ?>">
	<h4 class="body-title">Create New Account</h4>
	<div class="body-wrapper">
		<?php 
			$org_json = '';
			if( !EMPTY( $all_orgs ) )
			{
				$org_key 				= array_column( $all_orgs, 'org_code' );
				$org_name 				= array_column( $all_orgs, 'name' );
				
				$org_json 			= json_encode( array_combine( $org_key, $org_name ) );
			}
		?>
		<main class="cd-main-content">
			<div id="content-wrapper">
				<div class="wizard-form <?php echo $class_step ?>">
					<ul id="progressbar" >
						<?php 
							if( !EMPTY( $lists ) ) :
						?>
							<?php 
								foreach( $lists as $key => $list ) :

									$cl_ac 	= ( $key == 0 ) ? 'active' : '';
							?>
							<li class="<?php echo $cl_ac ?>"><?php echo $list ?></li>
							<?php 
								endforeach;
							?>
						<?php 
							endif;
						?>
					</ul>
					<form id="sub_form">
						<input type="hidden" id="org_json" value='<?php echo $org_json ?>'/>
						<?php 
							if( !EMPTY( $segments ) ) :
						?>
							<?php 
								foreach( $segments as $seg_key => $segment ) :

									$data_page_key 	= ( $seg_key + 1 );

									$pass_data['data_page_key']	= $data_page_key;
							?>
							<fieldset id="<?php echo $segment ?>_form" class="wcontent" data-page="<?php echo $data_page_key ?>">
								<?php $this->view('tabs/'.$segment, $pass_data); ?>
							</fieldset>
							<?php 
								endforeach;
							?>
						<?php 
							endif;
						?>
						<fieldset style="display:none !important;" id='main_fieldset'>
							<?php 
								if( !EMPTY( $orig_params ) ) :
							?>
								<?php 
									foreach( $orig_params as $name => $val ) :
								?>
								<input type="hidden" id="<?php echo $name ?>_inp" name="<?php echo $name ?>" value="<?php echo $val ?>">
								<?php 
									endforeach;
								?>
							<?php 
								endif;
							?>
						</fieldset>
					</form>
				</div>
			</div>
		</main>
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
</body>
</html>