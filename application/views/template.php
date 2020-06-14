<?php
$sys_logo 		 = get_setting(GENERAL, "system_logo");
$system_logo_src = base_url() . PATH_IMAGES . "logo_white.png";

$favicon 		 	= get_setting(GENERAL, "system_favicon");
$system_favicon_src = base_url() . PATH_IMAGES . "favicon.ico";

$avatar_src 	 	= base_url() . PATH_IMAGES . "avatar.jpg";


if( !EMPTY( $sys_logo ) )
{
	$sys_logo_path 		= FCPATH. PATH_SETTINGS_UPLOADS . $sys_logo;
	$sys_logo_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_logo_path);

	if( file_exists( $sys_logo_path ) )
	{
		$system_logo_src = base_url() . PATH_SETTINGS_UPLOADS . $sys_logo;
		$system_logo_src = @getimagesize($sys_logo_path) ? $system_logo_src : base_url() . PATH_IMAGES . "logo_white.png";
	}
}

/* GET SYSTEM FAVICON */
if( !EMPTY( $favicon ) )
{

	$sys_fav_path 		= FCPATH. PATH_SETTINGS_UPLOADS . $favicon;
	$sys_fav_path 		= str_replace(array('\\','/'), array(DS,DS), $sys_fav_path);
	
	if( file_exists( $sys_fav_path ) )	
	{
		$system_favicon_src = base_url() . PATH_SETTINGS_UPLOADS . $favicon;

		$system_favicon_src = @getimagesize($sys_fav_path) ? $system_favicon_src : base_url() . PATH_IMAGES . "favicon.ico";		
		
	}
}



/* GET USER AVATAR */
$avatar_path 	= FCPATH . PATH_USER_UPLOADS . $this->session->userdata('photo');
$avatar_path 	= str_replace(array('\\','/'), array(DS,DS), $avatar_path);

if( !is_dir( $avatar_path ) AND file_exists( $avatar_path ) )
{	
	$avatar_src = base_url() . PATH_USER_UPLOADS . $this->session->userdata('photo');

	$avatar_src = @getimagesize($avatar_path) ? $avatar_src : base_url() . PATH_IMAGES . "avatar.jpg";	
}

$user_roles = implode(",",$this->session->user_roles);

$class_compact_header = !EMPTY(get_setting(LAYOUT, "sidebar_menu")) ? "cd-compact-header" : "";

$pass_data 					= array();

$pass_data['resources'] 	= $resources;
$pass_data['initial'] 		= TRUE;

$auto_log_inactivity 				= get_setting( LOGIN, 'auto_log_inactivity' );
$log_in_dur 						= get_setting(LOGIN, "auto_log_inactivity_duration");

?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo get_setting(GENERAL, "system_title") ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="shortcut icon" href="<?php echo $system_favicon_src; ?>" id="favico_logo" />
	<link rel="stylesheet" media="screen" href="<?php echo base_url().PATH_CSS ?>style.css" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>parsley.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>/skins/skin_<?php echo get_setting(THEME, "skins") ?>.css">
	<link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>materialize.css" media="screen,projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().PATH_CSS ?>flaticon.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().PATH_CSS ?>material_icons.css">
	<link type="text/css" href="<?php echo base_url().PATH_CSS ?>jquery.jscrollpane.css" rel="stylesheet" media="all" />
	<link type="text/css" href="<?php echo base_url().PATH_CSS ?>component.css" rel="stylesheet" media="all" />
	<link type="text/css" href="<?php echo base_url().PATH_CSS ?>popModal.css" rel="stylesheet" media="all" />
	<link type="text/css" href="<?php echo base_url().PATH_CSS ?>custom.css" rel="stylesheet" media="all" />

	<!-- ALWAYS ON TOP (nodejs) -->
	<script src="<?php echo PATH_JS ?>socket.io/socket.min.io.js"></script>

	<script src="<?php echo base_url().PATH_JS ?>less.min.js" type="text/javascript"></script>
	<!-- PAGE LOADER SCRIPT -->
	<script >
		window.paceOptions = {
		  restartOnRequestAfter: false
		}
	</script>
	<script src="<?php echo base_url().PATH_JS ?>pace.js" type="text/javascript"></script>

	<?php
	if(!EMPTY($resources["load_css"]))
	{
		foreach($resources["load_css"] as $css):
			echo '<link href="' . base_url() . PATH_CSS . $css . '.css" rel="stylesheet" type="text/css">';
		endforeach;
	}
	?>

	<!-- JQUERY 2.1.1+ IS REQUIRED BY MATERIALIZE TO FUNCTION -->
	<script src="<?php echo base_url().PATH_JS ?>jquery-2.1.1.min.js"></script>
	<script src="<?php echo base_url().PATH_JS ?>jquery-ui.min.js" type="text/javascript"></script>
</head>

<body class="skin_<?php echo get_setting(THEME, "skins") ?>">
	<div class="se-pre-con" style="display : none !important"></div>
	<input type="hidden" id="base_url" value="<?php echo base_url() ?>">

	<!-- (nodejs) -->
	<input type="hidden" id="nodejs_server" value="<?php echo NODEJS_SERVER ?>"/>
	<input type="hidden" id="user_id" value="<?php echo $this->session->user_id ?>"/>
	<input type="hidden" id="org_code" value="<?php echo $this->session->org_code ?>"/>
	<?php $user_roles = implode(",",$this->session->user_roles); ?>
	<input type="hidden" id="user_roles" value="<?php echo $user_roles ?>"/>
	<input type="hidden" id="notif_cnt_<?php echo $this->session->user_id ?>"/>
	<!-- (nodejs) -->

	<input type="hidden" id="path_user_uploads" value="<?php echo PATH_USER_UPLOADS ?>" />
	<input type="hidden" id="path_images" value="<?php echo PATH_IMAGES ?>" />
	<input type="hidden" id="path_settings_upload" value="<?php echo PATH_SETTINGS_UPLOADS ?>">
	<input type="hidden" id="path_file_uploads" value="<?php echo PATH_FILE_UPLOADS ?>">

	<script src="<?php echo base_url().PATH_JS ?>script.js" type="text/javascript"></script>

	<div id="wrapper" class="<?php echo get_setting(LAYOUT, "sidebar_menu") ?>">

		<!-- SIDEBAR MENU SECTION -->
		<nav class="side-nav fixed" id="nav-mobile">

			<!-- LOGO SECTION -->
			<div class="logo <?php echo get_setting(LAYOUT, "header") ?>">
				<img src="<?php echo $system_logo_src ?>" /> <span class="hide-display show-on-hover"><?php echo get_setting(GENERAL, "system_title") ?><small>Powered by LGU 360 &copy;</small></span>
			</div>
			<!-- END LOGO SECTION -->
			<div class=" scroll-pane scroll-dark" style="height:calc(100% - 25px);">

				<ul class="collapsible menu " data-collapsible="accordion">
					<li>
						<div class="collapsible-header"><a class="waves-effect waves-teal" href="<?php echo base_url() . PROJECT_CORE ?>/dashboard"><i class="flaticon-home145"></i> <span class="hide-display show-on-hover">Dashboard</span></a></div>
					</li>
					<li class="menu-title-item hide-display show-on-hover m-l-n"><span>Admin</span></li>
					<li>
						<div class="collapsible-header"><a class="waves-effect waves-teal" style="cursor: pointer"><i class="flaticon-user153"></i> <span class="hide-display show-on-hover">User Management</span></a></div>
						<div class="collapsible-body">
							<ul class="menu-item">
								<li><a href="<?php echo base_url() . PROJECT_CORE ?>/users">Users</a></li>
								<li><a href="<?php echo base_url() . PROJECT_CORE ?>/roles">Roles</a></li>
								<li><a href="<?php echo base_url() . PROJECT_CORE ?>/permissions">Permissions</a></li>
								<li><a href="<?php echo base_url() . PROJECT_CORE ?>/organizations">Organizations</a></li>
							</ul>
						</div>
					</li>
					<li>
						<div class="collapsible-header"><a href="<?php echo base_url() . PROJECT_CORE ?>/files" class="waves-effect waves-teal"><i class="flaticon-folder209"></i> <span class="hide-display show-on-hover">Files</span></a></div>
					</li>
					<li>
						<div class="collapsible-header"><a href="<?php echo base_url() . PROJECT_CORE ?>/manage_workflow" class="waves-effect waves-teal"><i class="flaticon-hierarchical9"></i> <span class="hide-display show-on-hover">Workflow</span></a></div>
					</li>
					<li>
						<div class="collapsible-header"><a href="<?php echo base_url() . PROJECT_CORE ?>/audit_log" class="waves-effect waves-teal"><i class="flaticon-volume44"></i> <span class="hide-display show-on-hover">Audit Trail</span></a></div>
					</li>
				</ul>
			</div>
		</nav>
		<!-- END SIDEBAR MENU SECTION -->

		<!-- RESPONSIVE MENU BUTTON -->
		<a class="button-collapse top-nav full hide-on-large-only" data-activates="nav-mobile" href="#"><i class="flaticon-menu51"></i></a>
		<!-- END RESPONSIVE MENU BUTTON -->

		<section id="content">
			<?php 
			// HEADER FIX FOR CHROME BROWSER
			$sidebar = get_setting(LAYOUT, "sidebar_menu"); 
			$sidebar_width = (!EMPTY($sidebar)) ? "width:calc(100% - 90px)" : "width:calc(100% - 240px)";
			?>
			
			<!-- HEADER SECTION -->
			<header class="<?php echo get_setting(LAYOUT, "header") ?>" style="<?php echo $sidebar_width ?>">
				<div class="container">
					<div class="row">
						<div class="col l6 s5">
							<h5 class="header-title">Asiagate Template</h5>
						</div>

						<div class="col l5 s5 right-align p-r-md">
							<ul class="top-bar-notif">
								<!--li><a class="dropdown-button waves-effect waves-light" href="#" data-activates="dropdown-followers"><i class="flaticon-user153"></i><span class="notif-count"></span></a></li-->
								<li><a class="dropdown-button waves-effect waves-light" href="#" data-activates="dropdown-inbox"><i class="flaticon-inbox35"></i><span class="notif-count"></span></a></li>
								<li><a class="dropdown-button waves-effect waves-light" href="#" data-activates="dropdown-settings"><i class="flaticon-gear33"></i></a></li>
							</ul>
						</div>

						<div class="col l1 s2 right-align">
							<!-- Account Dropdown Trigger -->
							<a class="dropdown-button top-bar-account" href="#" data-activates="dropdown-account"><img id="top_bar_avatar" src="<?php echo $avatar_src ?>"/><span class="notif-count" id="noti_red"></span></a>
						</div>

						<!-- Account Dropdown Structure -->
						<div id="dropdown-account" class="dropdown-content">
							<div class="row account-details">
								<div class="col s7 account-name">
									<a href="javascript:;" class="md-trigger" data-modal="modal_profile" onclick="profile_modal_init()"><?php echo $this->session->name ?></a>
									<small><?php echo $this->session->job_title ?></small>
								</div>
								<div class="col s5 account-action right-align">
									<a href="javascript:;" id="logout"><i class="flaticon-power103"></i> Logout</a>
								</div>
							</div>

							<!-- Notifications List -->
							<div class="dropdown-title">Notifications <span class="new badge" id="notif_cnt"></span></div>
							<ul class="collection scroll-pane m-t-md notif-list" style="height:210px;"></ul>
						</div>
						<!-- End Account Dropdown Structure -->

						<!-- Inbox Dropdown Structure -->
						<div id="dropdown-inbox" class="dropdown-content" style="width:380px">			  

							<div class="dropdown-title m-t-n-sm">Messages <span class="new badge">1</span></div>
							<ul class="collection scroll-pane m-t-sm" style="height:275px;">
								<li class="collection-item avatar active">
									<img class="circle" alt="" src="<?php echo base_url().PATH_IMAGES ?>avatar/avatar_001.jpg">
									<a href="#" class="title mute">Kenneth Manalo</a>
									<p class="timestamp">3 hours ago</p>
									<p class="mute truncate">Lorem ipsum dolor sit consectetur adipiscing elit sollicitudin congue</p>
								</li>
								<li class="collection-item avatar">
									<img class="circle" alt="" src="<?php echo base_url().PATH_IMAGES ?>avatar/avatar_002.jpg">
									<a href="#" class="title mute">Rodel Satuito</a>
									<p class="timestamp">7 hours ago</p>
									<p class="mute truncate">Vivamus eu lacus hendrerit, feugiat sem ut, pellentesque nisi. Suspendisse potenti</p>
								</li>
								<li class="collection-item avatar">
									<img class="circle" alt="" src="<?php echo base_url().PATH_IMAGES ?>avatar/avatar_005.jpg">
									<a href="#" class="title mute">Kevin Villarojo</a>
									<p class="timestamp">23 hours ago</p>
									<p class="mute truncate">Suspendisse potenti feugiat sem ut, pellentesque nisi. </p>
								</li>
							</ul>
						</div>
						<!-- End Inbox Dropdown Structure -->

						<!-- Followers Dropdown Structure -->
						<div id="dropdown-followers" class="dropdown-content" style="width:370px">			  

							<div class="dropdown-title m-t-n-sm">Friend Requests <span class="new badge">3</span></div>
							<ul class="collection scroll-pane m-t-sm" style="height:275px;">
								<li class="collection-item avatar">
									<img class="circle" alt="" src="<?php echo base_url().PATH_IMAGES ?>avatar/avatar_001.jpg">
									<div class="row m-n">
										<div class="col s7 p-l-n">
											<a href="#" class="title">Kenneth Manalo</a>
											<p class="timestamp">Software Engineer</p>
										</div>
										<div class="col s5 right-align">
											<a href="#" class="btn-circle success m-r-sm  tooltipped" data-position="bottom" data-delay="50" data-tooltip="Accept"><i class="flaticon-checkmark21"></i></a>
											<a href="#" class="btn-circle mute tooltipped" data-position="bottom" data-delay="50" data-tooltip="Decline"><i class="flaticon-cross93"></i></a>
										</div>
									</div>
								</li>
								<li class="collection-item avatar">
									<img class="circle" alt="" src="<?php echo base_url().PATH_IMAGES ?>avatar/avatar_003.jpg">
									<div class="row m-n">
										<div class="col s7 p-l-n">
											<a href="#" class="title">Paolo Abendanio</a>
											<p class="timestamp">Network Administrator</p>
										</div>
										<div class="col s5 right-align">
											<a href="#" class="btn-circle success m-r-sm  tooltipped" data-position="bottom" data-delay="50" data-tooltip="Accept"><i class="flaticon-checkmark21"></i></a>
											<a href="#" class="btn-circle mute tooltipped" data-position="bottom" data-delay="50" data-tooltip="Decline"><i class="flaticon-cross93"></i></a>
										</div>
									</div>
								</li>
								<li class="collection-item avatar">
									<img class="circle" alt="" src="<?php echo base_url().PATH_IMAGES ?>avatar/avatar_005.jpg">
									<div class="row m-n">
										<div class="col s7 p-l-n">
											<a href="#" class="title">Kevin Villarojo</a>
											<p class="timestamp">Associate Brand Manager</p>
										</div>
										<div class="col s5 right-align">
											<a href="#" class="btn-circle success m-r-sm  tooltipped" data-position="bottom" data-delay="50" data-tooltip="Accept"><i class="flaticon-checkmark21"></i></a>
											<a href="#" class="btn-circle mute tooltipped" data-position="bottom" data-delay="50" data-tooltip="Decline"><i class="flaticon-cross93"></i></a>
										</div>
									</div>
								</li>
							</ul>
						</div>
						<!-- End Inbox Dropdown Structure -->

						<!-- Settings Dropdown Structure -->
						<div id="dropdown-settings" class="dropdown-content p-n" style="width:250px">
							<ul class="collection m-n">
								<li class="collection-item">
									<a href="<?php echo base_url() . PROJECT_CORE ?>/manage_settings#tab_site_settings" class="collection-link">Manage Settings<div class="secondary-content inline"><i class="flaticon-gears5"></i></div></a>
									<p class="timestamp">Adjust your site's general settings, including modifying your site's info and features.</p>
								</li>
								<li class="collection-item">
									<a href="#!" class="collection-link">Online Help<div class="secondary-content inline"><i class="flaticon-help17"></i></div></a>
									<p class="timestamp">See examples and tutorials to help you navigate and use the system.</p>
								</li>
							</ul>
						</div>
						<!-- End Settings Dropdown Structure -->
					</div>
				</div>
			</header>	
			<!-- END HEADER SECTION -->

			<!-- SUB NAVIGATION SECTION (for two-column menu layout) -->
			<aside id="sub-nav" class="scroll-pane scroll-dark show-sub-nav">
				<div class="list-basic sub-padder">
					<div class="list-header">
						<h5><i class="flaticon-text145"></i> LBP Forms Checklist</h5>
					</div>
					<ul>
						<li>
							<input type="checkbox" name="lbp1_checkbox" id="lbp1_checkbox" />	
							<label for="lbp1_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_1">Budget of Expenditures and Sources of Financing</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp2_checkbox" id="lbp2_checkbox" />	
							<label for="lbp2_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_2">Programmed Appropriation and Obligation by Object of Expenditure</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp3_checkbox" id="lbp3_checkbox" />	
							<label for="lbp4_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_3">Personnel Schedule</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp4_checkbox" id="lbp4_checkbox" />	
							<label for="lbp4_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_4">Mandate, Vision/Mission, Major Final Output, Performance Indicators and Targets</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp6_checkbox" id="lbp6_checkbox" />	
							<label for="lbp6_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_6">Statement of Indebtedness</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp7_checkbox" id="lbp7_checkbox" />	
							<label for="lbp7_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_7">Statement of Statutory and Contractual Obligations and Budgetary Requirements</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp8_checkbox" id="lbp8_checkbox" />	
							<label for="lbp8_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_8">Statement of Fund Allocation by Sector</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp9_checkbox" id="lbp9_checkbox" />	
							<label for="lbp9_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_9">Statement of Funding Sources (Supplemental Budget)</a></label>
						</li>
						<li>
							<input type="checkbox" name="lbp10_checkbox" id="lbp10_checkbox" />	
							<label for="lbp10_checkbox"><a href="<?php echo base_url() . MOD_BUDGET ?>/lbp_form_10">Statement of Supplemental Appropriation</a></label>
						</li>
						<li class="p-md m-t-sm center-align">
							<button class="btn waves-effect waves-light" type="button" name="sign-up">Submit</button>
						</li>
					</ul>
				</div>
			</aside>
			<!-- END SUB NAVIGATION SECTION -->

			<!-- MAIN CONTENT SECTION -->
			<main class="container">
				<?php echo $contents ?>	
			</main>
		</section>

		<footer class="page-footer transparent">
			<div class="footer-copyright">
				<div class="left">
					<ul class="footer-links">
						<li><a href="#"><i class="flaticon-information68"></i> About</a></li>
						<li><a href="#"><i class="flaticon-lifesaver5"></i> Help</a></li>
					</ul>
				</div>
				<div class="right">
					Powered by <a href="www.asiagate.com" target="_blank" class="font-bold">LGU 360 by Asiagate Networks, Inc.</a>
				</div>
			</div>
		</footer>
	</div>


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
			<button type="button" value="Ok" id="confirm_modal_btn" class="btn btn-success" data-confirmmodal-but="ok">Ok</button>
		</div>
	</div>

	<div id="loading" class="none" align="center">
		<div class="p-lg center-align">
			<img src="<?php echo base_url() . PATH_IMAGES ?>loading40.gif" />
		</div>
	</div>

	<!-- Modal -->
	<div id="modal_profile" class="md-modal lg md-effect-<?php echo MODAL_EFFECT ?>">
		<div class="md-content">
			<a class="md-close icon none">&times;</a>
			<div id="modal_profile_content"></div>
		</div>
	</div>

	<?php echo $this->view('modal_initialization', $pass_data); ?>

	<!-- (nodejs) -->
	<!-- since $.get('nodejs/index.html') doesn't work, we need this dummy div - $('alerts_div').load('nodejs/index.html') works -->
	<div id="alerts_div" class="none"></div>
	<!-- (nodejs) -->

	<!-- PLATFORM SCRIPT -->
	<script src="<?php echo base_url().PATH_JS ?>materialize.js"></script>
	<!-- END PLATFORM SCRIPT -->

	<script src="<?php echo base_url().PATH_JS ?>auth.js"></script>
	<script src="<?php echo base_url().PATH_JS ?>parsley.min.js" type="text/javascript"></script>
	<script src="<?php echo base_url().PATH_JS ?>collapsible-menu.js"></script>

	<!-- JSCROLLPANE SCRIPT -->
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.mousewheel.js"></script>
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.jscrollpane.js"></script>
	<!-- END JSCROLLPANE SCRIPT -->

	<!-- UPLOAD FILE -->
	<link href="<?php echo base_url() . PATH_CSS; ?>uploadfile.css" rel="stylesheet" type="text/css">
	<script src="<?php echo base_url() . PATH_JS ?>jquery.uploadfile.js" type="text/javascript"></script>
	<!-- END UPLOAD FILE -->

	<!-- POPMODAL SCRIPT -->
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>popModal.min.js"></script>
	<!-- END POPMODAL SCRIPT -->

	<!-- MODAL -->
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>classie.js"></script>
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>modalEffects.js"></script>
	<!-- END MODAL -->

	<!-- FULLSCREEN MODAL -->
	<link href="<?php echo base_url() . PATH_CSS; ?>animate.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="<?php echo base_url().PATH_JS ?>animatedModal.min.js"></script>
	<!-- END FULLSCREEN MODAL -->

	<!-- SEARCH -->
	<script src="<?php echo base_url().PATH_JS ?>jquery.lookingfor.min.js"></script>
	<!-- END SEARCH -->

	<!-- PAGE LOADER -->
	<script src="<?php echo base_url() . PATH_JS ?>jquery.isloading.js" type="text/javascript"></script>
	<!-- END PAGE LOADER -->

	<!-- SELECTIZE -->
	<script src="<?php echo base_url() . PATH_JS ?>selectize.js" type="text/javascript"></script>
	<!-- SELECTIZE -->

	<!-- SHORTEN TEXT -->
	<script src="<?php echo base_url() . PATH_JS ?>jquery.shorten.min.js" type="text/javascript"></script>
	<!-- SHORTEN TEXT -->

	<?php
	if(! EMPTY($resources["load_js"]))
	{
		foreach($resources["load_js"] as $js)
		{
			echo '<script src="' . base_url() . PATH_JS . $js . '.js" type="text/javascript"></script>';
		}
	}
	?>

	<!-- (nodejs) use for time and date, ex: 5 seconds ago, 2 days ago, etc. -->
	<script type="text/javascript" src="<?php echo base_url() . PATH_JS ?>moment.js"></script>
	<!-- (nodejs) -->

	<script src="<?php echo base_url().PATH_JS ?>common.js" type="text/javascript"></script>
	<script src="<?php echo base_url().PATH_JS ?>initializations.js" type="text/javascript"></script>

	<script src="<?php echo base_url() . PATH_JS ?>idle.min.js" type="text/javascript"></script>
	
	<?php 

		$this->view( 'initializations', $pass_data );
	?>

	<script>

		Pace.on("start", function(){
		   $(".se-pre-con").show();
		});

		Pace.start();

		Pace.on("done", function(){
		   $(".se-pre-con").hide();
		});s
		
		var awayCallback = function(){
			$.post($base_url + "auth/sign_out/" + $('#user_id').val(), function(result){
				if(result.flag == 1){
					window.location = $base_url;
				}
			},'json');
		};
		
		var awayBackCallback = function(){
			// console.log(new Date().toTimeString() + ": back");
		};

		<?php if( !EMPTY( $auto_log_inactivity ) AND !EMPTY( $log_in_dur ) ) : ?>

		var log_in_dur 	= '<?php echo $log_in_dur ?>';

		var idle = new Idle({
			onAway: awayCallback,
			onAwayBack: awayBackCallback,
			awayTimeout: parseInt( log_in_dur ) * 1000
		}).start();

		<?php endif; ?>
	</script>
		
</body>
</html>
