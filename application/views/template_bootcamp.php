<?php

/* If this is copy paste please read
	find get_menu($curr_system) change $curr_system to the template's desired system_code
	e.g get_menu(GMMS)
*/

$sys_logo 		 = get_setting(GENERAL, "system_logo");
$system_logo_src = base_url() . PATH_IMAGES . "logo_white.png";

$favicon 		 	= get_setting(GENERAL, "system_favicon");
$system_favicon_src = base_url() . PATH_IMAGES . "favicon.ico";

$avatar_src 	 	= base_url() . PATH_IMAGES . "avatar.jpg";

$org_pic 			= base_url().PATH_IMAGES.DEFAULT_ORG_LOGO;

$change_upload_path	= get_setting(MEDIA_SETTINGS, "change_upload_path");

if( !EMPTY( $org_template_logo ) ) 
{

	$org_pic_path 		= $ROOT_PATH. PATH_ORGANIZATION_UPLOADS . $org_template_logo;
	$org_pic_path 		= str_replace(array('\\','/'), array(DS,DS), $org_pic_path);

	if( file_exists( $org_pic_path ) )
	{
		$org_pic_src	 = base_url() . PATH_ORGANIZATION_UPLOADS . $org_template_logo;

		if( !EMPTY( $change_upload_path ) )
		{	
			$org_pic_src = output_image($org_template_logo, PATH_ORGANIZATION_UPLOADS);
		}

		$org_pic 	 	 = $org_pic_src;
	}
}

if( !EMPTY( $sys_logo ) )
{
	$sys_logo_path 		= $ROOT_PATH. PATH_SETTINGS_UPLOADS . $sys_logo;
	$sys_logo_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

	if( file_exists( $sys_logo_path ) )
	{
		$system_logo_src = base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;

		if( !EMPTY( $change_upload_path ) )
		{	
			$system_logo_src = output_image($sys_logo, PATH_SETTINGS_UPLOADS);
		}
		
		$system_logo_src = @getimagesize($sys_logo_path) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
	}
}

/* GET SYSTEM FAVICON */
if( !EMPTY( $favicon ) )
{

	$sys_fav_path 		= $ROOT_PATH. PATH_SETTINGS_UPLOADS . $favicon;
	$sys_fav_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_fav_path);
	
	if( file_exists( $sys_fav_path ) )	
	{
		$system_favicon_src = base_url() . PATH_SETTINGS_UPLOADS . $favicon;

		if( !EMPTY( $change_upload_path ) )
		{	
			$system_favicon_src = output_image($favicon, PATH_SETTINGS_UPLOADS);
		}

		$system_favicon_src = @getimagesize($sys_fav_path) ? $system_favicon_src : base_url() . PATH_IMAGES . "favicon.ico";		
		
	}
}



/* GET USER AVATAR */
$avatar_path 	= $ROOT_PATH . PATH_USER_UPLOADS . $this->session->userdata('photo');
$avatar_path 	= str_replace(array('\\','/'), array(DS,DS), $avatar_path);

$avatar_photo 	= $this->session->userdata('photo');

if( !is_dir( $avatar_path ) AND file_exists( $avatar_path ) )
{	
	$avatar_src = base_url() . PATH_USER_UPLOADS . $this->session->userdata('photo');

	if( !EMPTY( $change_upload_path ) )
	{	
		$avatar_src = output_image($this->session->userdata('photo'), PATH_USER_UPLOADS);
	}

	$avatar_src = @getimagesize($avatar_path) ? $avatar_src : base_url() . PATH_IMAGES . "avatar.jpg";	
}
else
{
	$avatar_photo = '';
}

$user_role_sess 	= $this->session->user_roles;
$user_roles 		= '';

if( !EMPTY( $user_role_sess ) )
{
	$user_roles = implode(",",$this->session->user_roles);
}

$sidebar_menu = get_setting(LAYOUT, "sidebar_menu");
$class_compact_header = !empty($sidebar_menu) ? "cd-compact-header" : "";

$auto_log_inactivity 				= get_setting( LOGIN, 'auto_log_inactivity' );
$log_in_dur 						= get_setting(LOGIN, "auto_log_inactivity_duration");

$sess_expiration_warning 			= get_setting( LOGIN, 'sess_expiration_warning' );

// GET MENU POSITION SETTING
$menu_position = get_setting(MENU_LAYOUT, "menu_position");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo get_setting(GENERAL, "system_title") ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>materialize.css" media="screen,projection" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>base.css">
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>stylev2.css">
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>/skins/skin_<?php echo get_setting(THEME, "skins") ?>.css">
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>parsley.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().PATH_CSS ?>material_icons.css">
  	<link type="text/css" href="<?php echo base_url().PATH_CSS ?>jquery.jscrollpane.css" rel="stylesheet" media="all" />
	<link type="text/css" href="<?php echo base_url().PATH_CSS ?>component.css" rel="stylesheet" media="all" />
	<link type="text/css" href="<?php echo base_url().PATH_CSS ?>custom.css" rel="stylesheet" media="all" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>widgets.css">
	<link type="text/css" rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>offline-theme-chrome.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>offline-language-english.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo base_url().PATH_CSS.CSS_LOBIBOX ?>.css" />
	
	<!-- ALWAYS ON TOP (nodejs) -->
	<script src="<?php echo base_url(). PATH_JS ?>socket.io/socket.io.min.js"></script>

	<!-- JQUERY 2.1.1+ IS REQUIRED BY MATERIALIZE TO FUNCTION -->
	<script src="<?php echo base_url().PATH_JS ?>jquery-3.1.0.min.js"></script>
	<script src="<?php echo base_url().PATH_JS ?>jquery-ui.min.js" type="text/javascript"></script>
	<script src="<?php echo base_url() . PATH_JS ?>offline.js" type="text/javascript"></script>
	<script >

		Offline.options = {
			checkOnLoad: false,
			reconnect: {
		    // How many seconds should we wait before rechecking.
		    initialDelay: 3
		  },
		  checks : {
		  	xhr : {
		  		url : "<?php echo base_url().PATH_IMAGES ?>" + "favicon.ico"
		  		// active : 'image'
		  	}
		  }
		}

		var request_check_offl 	= false;

		var run = function()
		{
			if( request_check_offl == true )
			{
				return;	
			}

	  		if( Offline.state === 'up' )
	  		{	request_check_offl = true;
		    	Offline.check();
		    	request_check_offl = false;
		    }
		}

		setInterval(run, 600000);

		window.paceOptions = {
		  restartOnRequestAfter: false
		}
	</script>
	<script src="<?php echo base_url().PATH_JS ?>pace.js" type="text/javascript"></script>
</head>
<body class="skin_<?php echo get_setting(THEME, "skins") ?>">
	<div class="se-pre-con" style="display : none !important"></div>
	<input type="hidden" id="base_url" value="<?php echo base_url() ?>">

	<!-- (NODEJS) -->
	<input type="hidden" id="nodejs_server" value="<?php echo NODEJS_SERVER ?>"/>
	<input type="hidden" id="user_id" value="<?php echo $this->session->user_id ?>"/>
	<input type="hidden" id="org_code" value="<?php echo $this->session->org_code ?>"/>
	
	<?php  ?>
	<input type="hidden" id="user_roles" value="<?php echo $user_roles ?>"/>
	<input type="hidden" id="notif_cnt_<?php echo $this->session->user_id ?>"/>
	<!-- (NODEJS) -->

	<input type="hidden" id="path_user_uploads" value="<?php echo PATH_USER_UPLOADS ?>" />
	<input type="hidden" id="path_images" value="<?php echo PATH_IMAGES ?>" />
	<input type="hidden" id="path_settings_upload" value="<?php echo PATH_SETTINGS_UPLOADS ?>">
	<input type="hidden" id="path_file_uploads" value="<?php echo PATH_FILE_UPLOADS ?>">

	<script src="<?php echo base_url().PATH_JS ?>script.js" type="text/javascript"></script>
	
	<header class="cd-main-header <?php echo $class_compact_header ?> <?php echo get_setting(LAYOUT, "header") ?> ">
		<a class="cd-logo" id="org-select">
			<?php 
				if( !EMPTY( $org_sys_owner ) AND $org_sys_owner == ENUM_YES ) :
			?>
			<img src="<?php echo $org_pic ?>" class="org_logo_img" id="app-logo"/>
			<?php 
				else :
			?>
			<img src="<?php echo $system_logo_src ?>" class="org_logo_img" id=""/>
			<?php 
				endif;
			?>
			<!-- <div class="input-field p-n m-n">
				<select id="org-selector" name="org-selector" disabled onchange="">
					<?php //echo get_organization_options(); ?>
				</select>
			</div> -->
		</a>
		<!-- <a href="#0" class="cd-logo"><img src="<?php //echo $system_logo_src ?>" alt="Logo"></a> -->
		
		<!-- div class="cd-search is-hidden">
			<form action="#0">
				<input type="search" placeholder="Search...">
			</form>
		</div--> <!-- cd-search -->
		
		<?php if($menu_position == MENU_TOP_NAV): ?>
		<div class="menu">
		<?php
			$active_sub_menu = ! empty($active_sub_menu) ? $active_sub_menu : '';
			get_system_modules_menu(SYSAD, $active_sub_menu);
		?>
		</div>
		<?php endif; ?>
		
		<a href="#0" class="cd-nav-trigger">Menu<span></span></a>

		<nav class="cd-nav">
			<ul class="cd-top-nav">
				<li class="has-children notif">
					<a href="javascript:;" class="notification-panel"><i class="material-icons">notifications</i><span style="<?php echo ( !ISSET( $unread_notif ) OR EMPTY($unread_notif)) ? 'display:none !important;' : ''; ?>" id="noti_red"></span></a>
					<ul>
						<li class="notification-title"><span id = "notif_cnt" class="new badge red" data-badge-caption="" style="<?php echo (!ISSET( $unread_notif ) OR EMPTY($unread_notif)) ? 'display:none;' : ''; ?>" >
							<?php if( ISSET( $unread_notif ) ) : ?>
								<?php echo $unread_notif; ?>
							<?php endif; ?>
							</span>New Notification
						</li>
						<li>
							<div class="scroll-pane scroll-light" style="height:200px;">
								<ul class="collection collection-notif">
									<?php if( ISSET( $notif_list ) ) : ?>
										<?php echo $notif_list; ?>
									<?php endif; ?>
								</ul>
							</div>
						</li>
						<li class="">
							<a href="<?php echo base_url(). CORE_USER_MANAGEMENT . '/profile#tab_profile_notifications' ?>" target="_blank" >Show all Notifications</a>
						</li>
					</ul>
				</li>
				<li class="has-children account">
					<a href="javascript:;">
						<?php 
							if( !EMPTY( $avatar_photo ) ) :	
						?>	
						<img src="<?php echo $avatar_src ?>" class=""  alt="avatar" id="top_bar_avatar">
						<?php 
							else :
						?>
						<img src="" class="profile_avatar" data-name="<?php echo $this->session->name ?>" alt="avatar" id="top_bar_avatar">
						<?php 
							endif;
						?>
						<div id="top_bar_account_name"><?php echo $this->session->name ?></div>
					</a>

					<ul>
						<?php //if($this->permission->check_permission(MODULE_PROFILE, ACTION_VIEW)){ ?>
							<li><a href="<?php echo base_url() . CORE_USER_MANAGEMENT ?>/profile#tab_profile_account">My Profile</a></li>
						<?php //} ?>
						<?php //if($this->permission->check_permission(MODULE_PERMISSION, ACTION_VIEW)){ ?>
							<!-- <li><a href="<?php echo base_url().CORE_SETTINGS ?>/manage_settings#tab_site_settings">Settings</a></li> -->
						<?php //} ?>
						<li><a href="#modal_version_info">About</a></li>
						<li><a href="javascript:;" id="logout">Log Out</a></li>
					</ul>
				</li>
			</ul>
		</nav>
	</header> <!-- .cd-main-header -->

	<main class="cd-main-content">
	
		<?php if($menu_position == MENU_SIDE_NAV): ?>
			<nav class="cd-side-nav <?php echo get_setting(LAYOUT, "sidebar_menu") ?>">
				<div class="cd-side-app">
					<!-- <div class="agency-logo">
					  <img id="org_logo_img" style="cursor: pointer" src="<?php echo $org_pic ?>" />
					  <input type="file" class="hide" id="org_logo_file_inp">
					</div> -->
					<?php get_systems(); ?>
				</div>	
				<?php get_menu($curr_system); ?>
			</nav>
		<?php endif; ?>
		
		<?php 
			if( ISSET( $sub_nav ) ) :
				echo $sub_nav;
			endif;
		?>

		<div id="content-wrapper">
		<?php echo $contents ?>
		</div> <!-- .content-wrapper -->
	</main> <!-- .cd-main-content -->
	
	<!-- NOTIFICATION SECTION -->
	<div class="notify success none">
		<div class="success">
			<h4>
				<span>Success!</span>
			</h4>
			<p></p>
		</div>
	</div>
	<div class="notify error none">
		<div class="error">
			<h4>
				<span>Warning!</span>
			</h4>
			<p></p>
		</div>
	</div>

	<!-- CONFIRMATION SECTION -->
	<div id="confirm_modal" style="display:none">
		<div class="confirmModal_content">
			<h4></h4>
			<p></p>
		</div>
		<div class="confirmModal_footer">
			<button type="button" data-confirmmodal-but="cancel"><?php echo BTN_CANCEL ?></button>
			<button type="button" value="<?php echo BTN_OK ?>" id="confirm_modal_btn" class="btn btn-success" data-confirmmodal-but="ok"><?php echo BTN_OK ?></button>
		</div>
	</div>
	
	<div id="modal_profile" class="modal modal-materialize modal-fixed-footer md">
		<form id="form_modal_profile">
			<div class="modal-content scroll-pane scroll-dark" style="height:calc(100%-156px)">
				<div id="content"></div>
			</div>
			
			<div class="modal-footer right-align">
				<a href="javascript:;" class="btn-flat modal-action modal-close m-n m-r-xs"><?php echo BTN_CANCEL ?></a>
				<button type="submit" id="submit_modal_profile" class="modal-action waves-effect waves-light btn m-n" data-btn-action="<?php echo BTN_SAVING ?>"><?php echo BTN_SAVE ?></button>
			</div>
			
		</form>
	</div>

	<div id="modal_sess_expired_log_in" class="modal modal-materialize modal-fixed-footer modal-fixed-header sm" style="height:45% !important;max-height: 45% !important;">
		<div class="modal-header">
			Session Expired
			<a href="javascript:;" id="modal_warning_expired_log_in_close" class="modal-action modal-close">&times;</a>
		</div>
		<form id="form_modal_sess_expired_log_in">
			<div class="modal-content scroll-pane scroll-dark" style="height:calc(100%-156px)">
				<div id="content"></div>
			</div>
			<div class="modal-footer right-align">
				<button type="button" class="btn m-n waves-effect waves-light m-r-xs blue lighten-1"id="continue_btn" name="continue_btn" data-btn-action="Getting Started" >Continue &rarr;</button>
			</div>
		</form>
	</div>

	<div id="modal_warning_expired_log_in" class="modal modal-materialize modal-fixed-footer modal-fixed-header xs">
		<div class="modal-header">
			Session Expiration
			<a href="javascript:;" id="modal_warning_expired_log_in_close" class="modal-action modal-close">&times;</a>
		</div>
		<form id="form_modal_warning_expired_log_in">
			<div class="modal-content scroll-pane scroll-dark" style="height:calc(100%-156px)">
				<div id="content"></div>
			</div>
			<div class="modal-footer right-align">
				<a href="javascript:;" class="btn-flat modal-action m-n m-r-xs" id="sess_warning_logout">No, log me out</a>
				<button type="button" class="btn m-n waves-effect waves-light m-r-xs blue lighten-1 hide" id="logged_me_in" data-save="Logged me in" >Go to Login Page</button>
				<button type="button" class="btn m-n waves-effect waves-light m-r-xs blue lighten-1" id="stay_connected" data-save="Stay Connected" >Yes, keep me logged in</button>
			</div>
		</form>
	</div>
	
	<div id="modal_version_info" class="modal modal-materialize modal-fixed-footer xs">
		<a href="javascript:;" class="modal-action modal-close">&times;</a>
		<form id="form_modal_version_info">
			<div class="modal-content scroll-pane scroll-dark" style="height:calc(100%)">
				<div id="content"></div>
			</div>
		</form>
	</div>

	<div id="modal_jsviewer">
		<div id="modal-container"><a id="modal-close-btn"></a>
			<div id="docxjs-wrapper" style="width:100%;height:100%;"></div>
		</div>
	</div>
    <div id="parser-loading"></div>
	
	<!-- (nodejs) -->
	<!-- since $.get('nodejs/index.html') doesn't work, we need this dummy div - $('alerts_div').load('nodejs/index.html') works -->
	<div id="alerts_div" class="none"></div>
	<!-- (nodejs) -->
		
	<!-- PLATFORM SCRIPT -->
	<script src="<?php echo base_url().PATH_JS ?>constants.js"></script>
	<!-- END PLATFORM SCRIPT -->

	<!-- PLATFORM SCRIPT -->
	<script src="<?php echo base_url().PATH_JS ?>materialize.js"></script>
	<!-- END PLATFORM SCRIPT -->
	
	<!-- SIDEBAR MENU SCRIPT -->
	<script src="<?php echo base_url().PATH_JS ?>jquery.menu-aim.js"></script>
	<script src="<?php echo base_url().PATH_JS ?>main.js"></script>
	<!-- END SIDEBAR MENU SCRIPT -->
	
	<!-- AUTHENTICATION SCRIPT -->
	<script src="<?php echo base_url().PATH_JS ?>auth.js"></script>
	<!-- END AUTHENTICATION SCRIPT -->
	
	<!-- PARSLEY FORM VALIDATION SCRIPT -->
	<script src="<?php echo base_url() . PATH_JS ?>parsley_config.js" type="text/javascript"></script>
	<script src="<?php echo base_url().PATH_JS ?>parsley.min.js" type="text/javascript"></script>
	<!-- END PARSLEY FORM VALIDATION SCRIPT -->
	
	<!-- JSCROLLPANE SCRIPT -->
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.mousewheel.js"></script>
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.jscrollpane.js"></script>
	<!-- END JSCROLLPANE SCRIPT -->
	
	<!-- UPLOAD FILE -->
	<link href="<?php echo base_url() . PATH_CSS; ?>uploadfile.css" rel="stylesheet" type="text/css">
	<script src="<?php echo base_url() . PATH_JS ?>jquery.uploadfile.js" type="text/javascript"></script>
	<!-- END UPLOAD FILE -->

	<!-- POPMODAL SCRIPT -->
	<link href="<?php echo base_url().PATH_CSS.CSS_POP_MODAL ?>.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="<?php echo base_url().PATH_JS.JS_POP_MODAL ?>.js"></script>
	<!-- END POPMODAL SCRIPT -->

	<!-- MODAL SCRIPT -->
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>classie.js"></script>
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>modalEffects.js"></script>
	<!-- END MODAL SCRIPT -->

	<!-- SEARCH SCRIPT -->
	<script src="<?php echo base_url().PATH_JS ?>jquery.lookingfor.min.js"></script>
	<!-- END SEARCH SCRIPT -->

	<!-- PAGE LOADER SCRIPT -->
	<script src="<?php echo base_url() . PATH_JS ?>jquery.isloading.js" type="text/javascript"></script>
	<!-- END PAGE LOADER SCRIPT -->

	<!-- BLOCK UI SCRIPT -->
	<script src="<?php echo base_url() . PATH_JS ?>jquery.blockUI.js" type="text/javascript"></script>
	<!-- END BLOCK UI SCRIPT -->

	<!-- SELECTIZE SCRIPT -->
	<script src="<?php echo base_url() . PATH_JS ?>selectize.js" type="text/javascript"></script>
	<!-- END SELECTIZE SCRIPT -->

	<!-- MEGAMENU SCRIPT -->
	<link href="<?php echo base_url() . PATH_CSS . CSS_MEGAMENU; ?>.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="<?php echo base_url().PATH_JS.JS_MEGAMENU ?>.js"></script>
	<!-- END MEGAMENU SCRIPT -->
	
	<!-- LOBIBOX SCRIPT -->
	<script src="<?php echo base_url() . PATH_JS.JS_LOBIBOX ?>.js" type="text/javascript"></script>
	<!-- END LOBIBOX SCRIPT -->

	<!-- LOBIBOX SCRIPT -->
	<!-- <script src="<?php echo base_url() . PATH_JS ?>push.min.js" type="text/javascript"></script> -->
	<!-- END LOBIBOX SCRIPT -->

	<!-- (NODEJS) USE FOR TIME AND DATE, EX: 5 SECONDS AGO, 2 DAYS AGO, ETC. -->
	<script type="text/javascript" src="<?php echo base_url() . PATH_JS ?>moment.js"></script>
	<!-- (NODEJS) -->

	<script src="<?php echo base_url().PATH_JS ?>common.js" type="text/javascript"></script>
	<script src="<?php echo base_url().PATH_JS ?>initializations.js" type="text/javascript"></script>

	<script src="<?php echo base_url() . PATH_JS ?>idle.min.js" type="text/javascript"></script>

	<script src="<?php echo base_url() . PATH_JS ?>initial.min.js" type="text/javascript"></script>
	<script src="<?php echo base_url() . PATH_JS ?>parsley_extend.js" type="text/javascript"></script>
	<script src="<?php echo base_url() . PATH_JS ?>yofinity.min.js" type="text/javascript"></script>
	
	<script>
		$(".ui-progressbar > .ui-widget-header").each(function() {
		  $(this)
		    .data("origWidth", $(this).width())
		    .width(0)
		    .animate({
		      width: $(this).data("origWidth") // or + "%" if fluid
		    }, 1200);
		});
	
		var ci_details 		= {
			ci_base_url 	: '<?php echo base_url() ?>',
			ci_user_id 		: '<?php echo $this->session->user_id ?>',
			ci_user_roles 	: '<?php echo $user_roles ?>',
			// ci_org_code 	: '<?php echo $this->session->org_code ?>',
			ci_nodejs_server: '<?php echo NODEJS_SERVER ?>',
			ci_sess_expiration : '<?php echo $this->config->item('sess_expiration') ?>',
		};
		
		Pace.on("start", function(){
		   $(".se-pre-con").show();
		});

		Pace.start();

		Pace.on("done", function(){
		   $(".se-pre-con").hide();
		});

		var modal_warn_obj = $('#modal_warning_expired_log_in').modal({
			dismissible: false,
			opacity: .5, // Opacity of modal background
			in_duration: 300, // Transition in duration
			out_duration: 200, // Transition out duration
			ready: function() {
				$("#modal_warning_expired_log_in .modal-content #content").load($base_url+'Unauthorized/warning_expired_sess_modal/');
			}, // Callback for Modal open
			complete: function() { 
			
			} // Callback for Modal close
		});

		var awayCallback = function(){
			if( !$('#modal_warning_expired_log_in').hasClass('open') )
			{
				$.post($base_url + "auth/sign_out/" + $('#user_id').val(), function(result){
					if(result.flag == 1){
						
							window.location = $base_url + 'auth/index/inactivity';
						
					}
				},'json');
			}
		};
		
		var awayBackCallback = function(){
			// console.log(new Date().toTimeString() + ": back");
		};

		<?php 
			if( !EMPTY( $sess_expiration_warning ) ) :
		?>
		var onWarning 		= function()
		{
			if( !$('#modal_warning_expired_log_in').hasClass('open') )
			{
				modal_warn_obj.trigger('openModal');
			}
		}
		
		<?php 
			else :
		?>
		var onWarning 		= function()
		{
		}

		<?php 
			endif;
		?>

		<?php if( !EMPTY( $auto_log_inactivity ) AND !EMPTY( $log_in_dur ) ) : ?>

		var log_in_dur 	= '<?php echo $log_in_dur ?>';

		if( log_in_dur == 30 )
		{
			log_in_dur = parseInt( log_in_dur ) + 10;
		}
		else if( log_in_dur <= 29 ) 
		{
			log_in_dur = parseInt( log_in_dur ) + 30;
		}

		var idle = new Idle({
			onAway: awayCallback,
			onAwayBack: awayBackCallback,
			awayTimeout: parseInt( log_in_dur ) * 1000,
			onWarning : onWarning
		}).start();

		<?php endif; ?>

		

		$(function(){
			<!-- (nodejs) -->
			// $("#alerts_div").load("<?php echo base_url() ?>nodejs/index.html");
			<!-- (nodejs) -->

			parsley_listener_duplicate();
			
		});

	</script>
	<!-- <script src="<?php echo base_url().PATH_JS ?>socket_notification.js" type="text/javascript"></script> -->
	<?php 
		if( ISSET( $resources ) )
		{
			$resources['initial'] 		= TRUE;
		
			$this->view('footer', $resources);
		}
	?>
	<div id="overlay-wrapper"></div>
	<script src="<?php echo base_url().PATH_JS ?>socket_notification.js" type="text/javascript"></script>
	<script src="<?php echo base_url().PATH_JS ?>template_common.js" type="text/javascript"></script>
</body>
</html>