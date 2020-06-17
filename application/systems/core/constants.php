<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 |--------------------------------------------------------------------------
 | GENERAL CONSTANTS
 |--------------------------------------------------------------------------
 |
 | These constants are used by the whole project/product
 |
 */
/*
 |---------------------------------------------------------------------
 | CSS AND JS
 |---------------------------------------------------------------------
 | These are the frequently used CSS and JS in
 | resources(load_css and load_js)
 */

/* MATERIALIZE */
define('JS_MATERIALIZE', 'materialize');

/* CHECKBOX / RADIO BUTTON */
define('CSS_LABELAUTY', 'jquery-labelauty');
define('JS_LABELAUTY', 'jquery-labelauty');

/* JS VIEWER DIR */
define('VIEWER_DIR', 'jsviewer/');
define('CSS_VIEWER', VIEWER_DIR.'jsViewer');
define('JS_VIEWER_DOC', VIEWER_DIR.'docxjs/DocxJS.bundle.min');
define('JS_VIEWER_XLS', VIEWER_DIR.'celljs/CellJS.bundle.min');
define('JS_VIEWER_PDF', VIEWER_DIR.'pdfjs/PdfJS.bundle.min');
define('JS_VIEWER_SLIDE', VIEWER_DIR.'slidejs/SlideJS.bundle.min');
define('JS_VIEWER_ALL', serialize(
	array(JS_VIEWER_DOC, JS_VIEWER_XLS, JS_VIEWER_PDF, JS_VIEWER_SLIDE)
));


/* DATATABLE */
define('CSS_DATATABLE', 'jquery.dataTables');
define('JS_DATATABLE', 'jquery.dataTables.min');

define('CSS_DATATABLE_MATERIAL', 'dataTables.material.min');
define('JS_DATATABLE_MATERIAL', 'dataTables.material.min');

	/* DATATABLE EXTENSIONS */
	define('DATATABLE_EXTENSION_DIR', 'datatable_extensions/');
		/* DATATABLE BUTTONS AND EXPORTING */
		
		define('CSS_DATATABLE_BUTTONS', DATATABLE_EXTENSION_DIR.'jquery.dataTabeButtons.min');
		define('JS_DATATABLE_BUTTON', DATATABLE_EXTENSION_DIR.'jquery.dataTableButtons.min');
		define('JS_DATATABLE_BUTTON_FLASH', DATATABLE_EXTENSION_DIR.'jquery.dataTable.buttonFlash.min');
		define('JS_JSZIP', DATATABLE_EXTENSION_DIR.'jszip.min');
		define('JS_PDFMAKE', DATATABLE_EXTENSION_DIR.'pdfmake.min');
		define('JS_VFS_FONTS', DATATABLE_EXTENSION_DIR.'vfs_fonts');
		define('JS_BUTTONS_HTML5', DATATABLE_EXTENSION_DIR.'buttons.html5.min');
		define('JS_BUTTONS_PRINT', DATATABLE_EXTENSION_DIR.'buttons.print.min');
		define('JS_DATATABLE_COLVIS', DATATABLE_EXTENSION_DIR.'jquery.dataTableButtons.colVis.min');
		define('JS_DATATABLE_SELECT', DATATABLE_EXTENSION_DIR.'jquery.dataTableSelect');
		define('CSS_DATATABLE_SELECT', DATATABLE_EXTENSION_DIR.'jquery.dataTableSelect');
		define('JS_DATATABLE_ROW_REORDER', DATATABLE_EXTENSION_DIR.'jquery.dataTableRowreorder');
		define('CSS_DATATABLE_ROW_REORDER', DATATABLE_EXTENSION_DIR.'rowReorder.dataTables.min');

		define('JS_BUTTON_EXPORT_EXTENSION', serialize(
				array(
					JS_DATATABLE_BUTTON,
					JS_DATATABLE_BUTTON_FLASH,
					JS_DATATABLE_COLVIS,
					JS_JSZIP,
					JS_PDFMAKE,
					JS_VFS_FONTS,
					JS_BUTTONS_HTML5,
					JS_BUTTONS_PRINT
				)
			)
		);

/* DATE PICKER / TIME PICKER */
define('CSS_DATETIMEPICKER', 'jquery.datetimepicker');
define('JS_DATETIMEPICKER', 'jquery.datetimepicker');

define('CSS_NOUISLIDER', 'nouislider.min');
define('JS_NOUISLIDER', 'nouislider.min');
define('JS_WNUMB', 'wNumb.min');

/* DROPDOWN / SELECT FIELD */
  /* SELECTIZE */
  define('CSS_SELECTIZE', 'selectize.default');
  define('JS_SELECTIZE', 'selectize');
  define('JS_LAZY_SELECTIZE', 'lazyselectize');
  define('JS_LAZY_SELECTIZE_PACKAGE', serialize(
  		array(
  			JS_SELECTIZE,
  			JS_LAZY_SELECTIZE
  		)
  	)
  );

  /* SUMO SELECT */
  define('CSS_SUMO_SELECT', 'sumoselect.min');
  define('JS_SUMO_SELECT', 'jquery.sumoselect.min');

  /* ULTRASELECT */
  define('CSS_ULTRASELECT', 'jquery.ultraselect.min');
  define('JS_ULTRASELECT', 'jquery.ultraselect.min');

/* MODAL */
define('CSS_MODAL_COMPONENT', 'component');
define('JS_MODAL_CLASSIE', 'classie');
define('JS_MODAL_EFFECTS', 'modalEffects');

/* SCROLL */
define('CSS_SCROLLPANE', 'jquery.jscrollpane');
define('JS_SCROLLPANE', 'jquery.jscrollpane');

/* TABS */
define('CSS_TABS', 'easy-responsice-tabs');
define('JS_TABS', 'easyResponsiveTabs');

define('JS_JPEGCAMERA', 'jpegcamera/webcam');

/* UPLOAD */
define('CSS_UPLOAD', 'uploadfile');
define('JS_UPLOAD', 'jquery.uploadfile');

/* TEXT EDITOR */
define('JS_EDITOR', 'ckeditor/ckeditor');

/* CALENDAR */
define('CSS_CALENDAR', 'fullcalendar');
define('JS_CALENDAR', 'fullcalendar');
define('JS_CALENDAR_MOMENT', 'moment');

/* CHART */
define('JS_CHART', 'chart.min');
define('JS_ECHART', 'echarts.min');

/* POP MODAL */
define('CSS_POP_MODAL', 'popModal');
define('JS_POP_MODAL', 'popModal.min');

/* NUMBER */
define('JS_NUMBER', 'jquery.number.min');

/* DATASHEET */
define('JS_DATASHEET', 'datasheet');

/* COLOR PIKCER */
define('CSS_COLORPICKER', 'colorpicker/colorpicker');
define('JS_COLORPICKER', 'colorpicker/js/colorpicker');

/* DRAGULA DRAG AND DROP LIBRARY */
define('CSS_DRAGULA', 'dragula');
define('JS_DRAGULA', 'dragula');

/* DOM AUTO SCROLLER 
	ALLOWS SCROLLING WHILE DRAGING
	USUALLY USED IN CONJUNCTION WITH DRAGULA
*/
define('JS_DOM_AUTO_SCROLLER', 'dom-autoscroller.min');

define('JS_DRAGULA_SCROLL', serialize(
				array(
					JS_DRAGULA,
					JS_DOM_AUTO_SCROLLER
				)
			)
		);

/* ADD ROW */
define('JS_ADD_ROW', 'add_row');

/* WIZARD LIBRARY */
define('CSS_WIZARD', 'wizard');
define('JS_WIZARD', 'wizard');

/* LOBIBOX */
define('CSS_LOBIBOX', 'lobibox.min');
define('JS_LOBIBOX', 'lobibox');

/* MEGAMENU */
define('JS_MEGAMENU', 'megamenu');
define('CSS_MEGAMENU', 'megamenu');

/* VIEDO JS */
define('CSS_VIDEO_PLAYER', 'video-js');
define('JS_VIDEO_PLAYER', 'video-js');

/* AUDIO JS */
define('CSS_AUDIO', 'audiojs/player');
define('JS_SIRIWAVE', 'audiojs/siriwave');
define('JS_HOWLER_CORE', 'audiojs/howler.core.min');
define('JS_AUDIO_PLAYER', serialize(
	array(
		JS_HOWLER_CORE,
		JS_SIRIWAVE
	)
));

/* FORM DEFAULT VALUES */
define('JS_FORMDEFAULTVALUE', 'cbs.form.default.value');

/*
 |---------------------------------------------------------------------
 | LENGTH OF SALT
 |---------------------------------------------------------------------
 | Control the length of salt used for security purposes
 */
define('SALT_LENGTH', 15);

/*
 |---------------------------------------------------------------------
 | SYSTEM STATUS
 |---------------------------------------------------------------------
 */
define('SYSTEM_ON', 1);

/*
 |---------------------------------------------------------------------
 | PERMISSION ACTIONS
 |---------------------------------------------------------------------
 | These constants are used when defining the action to be made in
 | permission
 */
define('ACTION_SAVE', 1);
define('ACTION_ADD', 2);
define('ACTION_EDIT', 3);
define('ACTION_DELETE', 4);
define('ACTION_VIEW', 5);
define('ACTION_PRINT', 6);
define('ACTION_LOCK', 7);
define('ACTION_UPLOAD', 8);
define('ACTION_APPROVE', 13);
define('ACTION_SETTINGS', 14);
define('ACTION_VIEW_OWN', 15);
define('ACTION_EDIT_OWN', 16);
define('ACTION_DELETE_OWN', 17);
define('ACTION_DOWNLOAD', 18);
define('ACTION_ARCHIVE', 19);
define('ACTION_IMPORT', 20);
/*
 |---------------------------------------------------------------------
 | BUTTON ACTIONS
 |---------------------------------------------------------------------
 | These constants are used as button labels
 */
define('BTN_LOG_IN', 'Log In');
define('BTN_SIGN_UP', 'Sign Up');
define('BTN_CREATE_ACCOUNT', 'Create Account');
define('BTN_SAVE', 'Save');
define('BTN_ADD', 'Add');
define('BTN_UPDATE', 'Update');
define('BTN_DELETE', 'Delete');
define('BTN_CANCEL', 'Cancel');
define('BTN_CLOSE', 'Close');
define('BTN_POST', 'Post');
define('BTN_OK', 'Ok');
define('SAVE_CLOSE', 'Save and Close');

/*
 |---------------------------------------------------------------------
 | BUTTON VERBS
 |---------------------------------------------------------------------
 | These constants are used for replacing the loading text in the
 | button after pressing it
 */
define('BTN_SIGNING_UP', 'Signing up');
define('BTN_CREATING_ACCOUNT', 'Creating Account');
define('BTN_LOGGING_IN', 'Logging in');
define('BTN_EMAILING', 'Sending email');
define('BTN_POSTING', 'Posting');
define('BTN_SAVING', 'Saving');
define('BTN_UPDATING', 'Updating');
define('BTN_DELETING', 'Deleting');
define('BTN_UPLOADING', 'Uploading');

/*
 |---------------------------------------------------------------------
 | SETTINGS LOCATION
 |---------------------------------------------------------------------
 | These constants are the sub-menus of site settings module
 */
define('AUTHENTICATION', 'AUTHENTICATION');
define('SITE_APPEARANCE', 'SITE_APPEARANCE');
define('WORKFLOW_LOCATION', 'WORKFLOW');
define('MEDIA_LOCATION', 'MEDIA');
define('DPA_LOCATION', 'DPA');

/*
 |---------------------------------------------------------------------
 | SETTINGS TYPE
 |---------------------------------------------------------------------
 | These constants are the main sections of site settings module
 */
define('GENERAL', 'GENERAL');
define('LAYOUT', 'LAYOUT');
define('THEME', 'THEME');
define('ACCOUNT', 'ACCOUNT');
define('PASSWORD_CONSTRAINTS', 'PASSWORD_CONSTRAINTS');
define('PASSWORD_EXPIRY', 'PASSWORD_EXPIRY');
define('PASSWORD_INITIAL_SET', 'PASSWORD_INITIAL_SET');
define('LOGIN', 'LOGIN');
define('USERNAME_CONSTRAINTS', 'USERNAME_CONSTRAINTS');
define('USERNAME', 'USERNAME');
define('WORKFLOW_TAB', 'WORKFLOW_TAB');
define('WORKFLOW_FLAG', 'WORKFLOW_FLAG');
define('WORKFLOW_DESCRIPTION', 'WORKFLOW_DESCRIPTION');
define('VERSION', 'VERSION');
define('MEDIA_SETTINGS', 'MEDIA_SETTINGS');
define('NOTIFICATION_CRON', 'NOTIFICATION_CRON');
define('MENU_LAYOUT', 'MENU_LAYOUT');
define('AGREEMENT', 'AGREEMENT');
define('DPA_SETTING', 'DPA_SETTING');
define('AUTH_FACTOR', 'AUTH_FACTOR'); 
define('SYSTEM_SETTINGS', 'SYSTEM_SETTINGS'); 
define('SMS_API', 'SMS_API'); 
/*
 |---------------------------------------------------------------------
 | SEARCH USER USING THE FOLLOWING PARAMETERS
 |---------------------------------------------------------------------
 | These constants are used as a parameter for searching a specific
 | user in get_active_user() function
 */
define('BY_USERNAME', 'username');
define('BY_EMAIL', 'email');
define('BY_RESET_SALT', 'reset_salt');
define('BY_SALT', 'salt');

/*
 |---------------------------------------------------------------------
 | USER STATUS
 |---------------------------------------------------------------------
 | These status are used for identifying the status of the user
 */
define('ACTIVE', '1');
define('PENDING', '2');
define('INACTIVE', '3');
define('APPROVED', '4');
define('DISAPPROVED', '5');
define('DELETED', '6');
define('BLOCKED', '7');
define('DRAFT', '8');
define('EXPIRED', '9');
define('DPA_PENDING', '10');
define('INCOMPLETE', '11');

/*
 |---------------------------------------------------------------------
 | ALERT TYPE
 |---------------------------------------------------------------------
 | These types are used for identifying the notification class
 | used by notifyModal
 */
define('SUCCESS', 'success');
define('ERROR', 'error');

/*
 |---------------------------------------------------------------------
 | AUDIT TRAIL ACTIONS
 |---------------------------------------------------------------------
 | These constants are used when defining the action made in the process
 */
define('AUDIT_INSERT', 'INSERT');
define('AUDIT_UPDATE', 'UPDATE');
define('AUDIT_DELETE', 'DELETE');

/*
 |---------------------------------------------------------------------
 | GENDER
 |---------------------------------------------------------------------
 */
define('FEMALE', 'F');
define('MALE', 'M');

/*
 |---------------------------------------------------------------------
 | ANONYMOUS ACCOUNT
 |---------------------------------------------------------------------
 | This anonymous account is used as the default user_id in the audit
 | trail when a guest manually registers in the system
 */ 
define('ANONYMOUS_ID', 0);
define('ANONYMOUS_USERNAME', 'anonymous');

/*
 |---------------------------------------------------------------------
 | ACCOUNT CREATOR
 |---------------------------------------------------------------------
 */
define('ADMINISTRATOR', 'ADMINISTRATOR');
define('VISITOR', 'VISITOR');
define('VISITOR_NOT_APPROVAL', 'VISITOR_NOT_APPROVAL');

/*
 |---------------------------------------------------------------------
 | LOGIN VIA
 |---------------------------------------------------------------------
 | These constants are used for identifying which method is used
 | when logging in the system
 */
define('VIA_USERNAME', 'USERNAME');
define('VIA_EMAIL', 'EMAIL');
define('VIA_MOBILE', 'MOBILE_NO');
define('VIA_USERNAME_EMAIL', 'USERNAME_EMAIL');
define('VIA_FACEBOOK', 'FACEBOOK');
define('VIA_GOOGLE', 'GOOGLE');
define('LOGIN_WITH_ARR', serialize(array(
	VIA_FACEBOOK => 'Facebook',
	VIA_GOOGLE 	=> 'Google'
)));

/*
 |---------------------------------------------------------------------
 | MENU POSITION
 |---------------------------------------------------------------------
 | These constants are used for identifying which type of menu is 
 | used on the template
 */
define('MENU_TOP_NAV', 'TOP_NAV');
define('MENU_SIDE_NAV', 'SIDE_NAV');

/*
 |---------------------------------------------------------------------
 | MENU TYPES
 |---------------------------------------------------------------------
 | These constants are used for identifying which type of menu is 
 | used on the template
 */
define('MENU_CLASSIC', 'CLASSIC');
define('MENU_MEGAMENU', 'MEGAMENU');

/*
 |---------------------------------------------------------------------
 | MENU CHILD DISPLAY
 |---------------------------------------------------------------------
 | These constants are used for identifying what kind of menu display  
 | is used on the template
 */
define('DISPLAY_TITLE', 'TITLE');
define('DISPLAY_TITLE_ICON', 'TITLE_ICON');
define('DISPLAY_TITLE_DESC', 'TITLE_DESC');
define('DISPLAY_TITLE_ICON_DESC', 'TITLE_ICON_DESC');

/*
 |---------------------------------------------------------------------
 | PASSWORD CREATOR
 |---------------------------------------------------------------------
 */
define('SET_SYSTEM_GENERATED', 'SYSTEM_GENERATED');
define('SET_ACCOUNT_OWNER', 'ACCOUNT_OWNER');
define('SET_ADMINISTRATOR', 'ADMINISTRATOR');

/*
 |---------------------------------------------------------------------
 | DATA PRIVACY TYPE
 |---------------------------------------------------------------------
 */
define('DATA_PRIVACY_TYPE_BASIC', 'DPA_BASIC');
define('DATA_PRIVACY_TYPE_STRICT', 'DPA_STRICT');

/*
 |---------------------------------------------------------------------
 | DATA PRIVACY TYPE
 |---------------------------------------------------------------------
 */
define('MEDIA_UPLOAD_TYPE_DIR', 'DIRECTORY_PATH');
define('MEDIA_UPLOAD_TYPE_DB', 'DATABASE_PATH');

/*
 |---------------------------------------------------------------------
 | DATA PRIVACY TYPE
 |---------------------------------------------------------------------
 */
define('DATA_PRIVACY_STRICT_CONSENT_FORM', 'CONSENT_FORM');
define('DATA_PRIVACY_STRICT_EMAIL_NOTIF', 'EMAIL_NOTIFICATION');

/*
 |---------------------------------------------------------------------
 | TYPE OF EXPORTED FILE
 |---------------------------------------------------------------------
 | These constants are used to indicate the type of file to be exported
 */
define('EXPORT_PDF', 'pdf');
define('EXPORT_EXCEL', 'xls');
define('EXPORT_DOCUMENT', 'doc');

/*
 |---------------------------------------------------------------------
 | TYPE OF SYSTEM PARAMETERS
 |---------------------------------------------------------------------
 | These parameters are used when working with system parameters
 */
define('SYS_PARAM_SMTP', 'SMTP');
define('SYS_PARAM_GENDER', 'GENDER');
define('SYS_PARAM_SCOPES', 'SCOPES');
define('SYS_PARAM_STATUS', 'STATUS');
define('SYS_PARAM_ACTIONS', 'ACTIONS');
define('SYS_PARAM_MODULE_LOCATION', 'MODULE_LOCATION');

/*
 |---------------------------------------------------------------------
 | SYS PARAM STATUS CODE
 |---------------------------------------------------------------------
 | These parameters are used for filtering data from status
 */
define('STATUS_ACTIVE', 'STATUS_ACTIVE');
define('STATUS_APPROVED', 'STATUS_APPROVED');
define('STATUS_BLOCKED', 'STATUS_BLOCKED');
define('STATUS_DELETED', 'STATUS_DELETED');
define('STATUS_DISAPPROVED', 'STATUS_DISAPPROVED');
define('STATUS_DRAFT', 'STATUS_DRAFT');
define('STATUS_EXPIRED', 'STATUS_EXPIRED');
define('STATUS_INACTIVE', 'STATUS_INACTIVE');
define('STATUS_PENDING', 'STATUS_PENDING');
define('STATUS_DPA_PENDING', 'STATUS_DPA_PENDING');
define('STATUS_INCOMPLETE', 'STATUS_INCOMPLETE');

/*
 |---------------------------------------------------------------------
 | SYS PARAM GENDER CODE
 |---------------------------------------------------------------------
 | These parameters are used for filtering data from gender
 */
define('GENDER_MALE', 'GENDER_MALE');
define('GENDER_FEMALE', 'GENDER_FEMALE');

/*
 |---------------------------------------------------------------------
 | LIMIT OF ITEMS SHOWN AND USED BY JSCROLL
 |---------------------------------------------------------------------
 | Control the default number of items to be shown before loading the
 | next set of content
 */
define('ITEM_LIMIT', 5);

/*
 |---------------------------------------------------------------------
 | NODE JS SERVER PATH : NEEDED FOR LINUX ONLY
 |---------------------------------------------------------------------
 */
 $http_request 		= 'http';

if (!EMPTY($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] != 'off') 
{
    // SSL connection
    $http_request 	= 'https';
}

if( ISSET( $_SERVER['HTTP_HOST'] ) )
{
	define('NODEJS_SERVER', $http_request.'://'.$_SERVER['HTTP_HOST'].':8000/');
}

	/*
	 |--------------------------------------------------------------------------
	 | CHANGE ALL CONSTANTS BELOW DEPENDING ON THE PROJECT
	 |--------------------------------------------------------------------------
	 */
	/*
	 |---------------------------------------------------------------------
	 | PROJECT NAME
	 |---------------------------------------------------------------------
	 | Used for encryption_key and sess_cookie_name. Naming of constants
	 | should contain the project or company name and avoid long values
	 | with spaces.
	 */
	define('PROJECT_NAME', 'SCBS');
	define('PROJECT_CODE', 'scbs');

	/*
	 |---------------------------------------------------------------------
	 | MAIN HMVC FOLDER
	 |---------------------------------------------------------------------
	 */
	define('HMVC_FOLDER', 'systems');

	/*
	 |---------------------------------------------------------------------
	 | SYSTEM CODE
	 |---------------------------------------------------------------------
	 | Define the system code, this will be used to tag which features and modules are included in a system
	 */
	define('SYSAD', 'SYSAD');
	define('PLUGINS', 'PLUGINS');

	/*
	 |---------------------------------------------------------------------
	 | SYSTEMS FOLDER
	 |---------------------------------------------------------------------
	 | These constants are used when defining the systems folder path of
	 | your controller in the hyperlink or function.
	 */
	define('SYSTEM_DEFAULT', 'default');
	define('SYSTEM_CORE', 'core');
	define('SYSTEM_PLUGIN', 'plugins');
	define('SYSTEM_BOOTCAMP', 'bootcamp');

	/*
	 |---------------------------------------------------------------------
	 | MODULES FOLDER
	 |---------------------------------------------------------------------
	 | These constants are used when defining the modules folder path of
	 | your controller in the hyperlink or function.
	 */
	define('CORE_DASHBOARD', 'home');
	define('CORE_USER_MANAGEMENT', 'user_management');
	define('CORE_FILE_MANAGEMENT', 'file_management');
	define('CORE_WORKFLOW', 'workflow');
	define('CORE_AUDIT_TRAIL', 'audit_trail');
	define('CORE_SETTINGS', 'settings');
	define('CORE_COMMON', 'common');
	define('CORE_SYSTEMS', 'systems');
	define('CORE_GROUPS', 'groups');
	define('CORE_ANNOUNCEMENTS', 'announcements');
	define('CORE_MAINTENANCE', 'maintenance');
	define('CORE_QUEUES', 'queues');

	/*
	 |---------------------------------------------------------------------
	 | HOMEPAGE
	 |---------------------------------------------------------------------
	 | Define the landing page after login.
	 | Note that every project can have its own homepage.
	 */
	define('CORE_HOME_PAGE', CORE_DASHBOARD.'/dashboard');

	/*
	 |---------------------------------------------------------------------
	 | PROJECT MODULES
	 |---------------------------------------------------------------------
	 | Declare all the modules in your project.
	 */
	define('MODULE_DASHBOARD', 'DASHBOARD');
	define('MODULE_SETTINGS', 'SETTINGS');
	define('MODULE_USER_MANAGEMENT', 'USER_MGT');
	define('MODULE_USER', 'USERS');
	define('MODULE_ROLE', 'ROLES');
	define('MODULE_PERMISSION', 'PERMISSIONS');
	define('MODULE_ORGANIZATION', 'ORGS');
	define('MODULE_FILE', 'FILES');
	define('MODULE_WORKFLOW', 'WORKFLOW');
	define('MODULE_AUDIT_TRAIL', 'AUDIT_TRAIL');
	define('MODULE_SITE_SETTINGS', 'SITE_SETTINGS');
	define('MODULE_AUTH_SETTINGS', 'AUTH_SETTINGS');
	define('MODULE_SIGN_UP_APPROVAL', 'SIGN_UP_APPROVAL');
	define('MODULE_SYSTEMS', 'SYSTEMS');
	define('MODULE_PROFILE', 'PROFILE');
	define('MODULE_GROUPS', 'GROUPS');
	define('MODULE_MEDIA_SETTINGS', 'MEDIA_SETTINGS');
	define('MODULE_ANNOUNCEMENTS', 'ANNOUNCEMENTS');
	define('MODULE_DATA_PRIVACY_SETTING', 'DATA_PRIVACY_SETTING');
	define('MODULE_MAINTENANCE', 'MAINTENANCE');
	define('MODULE_STATEMENTS', 'STATEMENTS');
	define('MODULE_SYSTEM_SETTINGS', 'SYSTEM_SETTINGS');
	define('MODULE_APPLICATION_LOGS', 'APPLICATION_LOGS');
	define('MODULE_QUEUES', 'QUEUES');
	define('MODULE_EMAIL_QUEUE', 'EMAIL_QUEUE');
	define('MODULE_SMS_QUEUE', 'SMS_QUEUE');
	/*
	 |---------------------------------------------------------------------
	 | PASSWORD CONSTRAINTS
	 |---------------------------------------------------------------------
	 |
	 */

	define('PASS_CONS_DIGIT', 'constraint_digit');
	define('PASS_CONS_HISTORY', 'constraint_history');
	define('PASS_CONS_LENGTH', 'constraint_length');
	define('PASS_CONS_UPPERCASE', 'constraint_uppercase');
	define('PASS_CONS_REPEATING', 'constraint_repeating_characters');
	define('PASS_CONS_DIFF_USER', 'constraint_pass_diff_username');

	/*
	 |---------------------------------------------------------------------
	 | USERNAME CONSTRAINTS
	 |---------------------------------------------------------------------
	 |
	 */

	define('USERNAME_MIN_LENGTH', 'constraint_username_min_length');
	define('USERNAME_MAX_LENGTH', 'constraint_username_max_length');
	define('USERNAME_DIGIT', 'constraint_username_digit');

	/*
	 |---------------------------------------------------------------------
	 | PASSWORD EXPIRY
	 |---------------------------------------------------------------------
	 |
	 */
	define('PASS_EXP_DURATION', 'password_duration');
	define('PASS_EXP_EXPIRY', 'password_expiry');
	define('PASS_EXP_REMINDER', 'password_reminder');

	/*
	 |---------------------------------------------------------------------
	 | SITE SETTINGS
	 |---------------------------------------------------------------------
	 |
	 */
	define('SIDEBAR_MENU_CLASS', 'cd-nav-compact');

	/*
	 |---------------------------------------------------------------------
	 | STATIC PATH
	 |---------------------------------------------------------------------
	 | These constants are used when defining the folder path of your css,
	 | js, images and file upload
	 */
	define('PATH_CSS', 'static/css/');
	define('PATH_JS', 'static/js/');
	define('PATH_IMAGES', 'static/images/');
	define('PATH_UPLOADS', 'uploads/');
	define('PATH_USER_UPLOADS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_USER_MANAGEMENT.'/users/');
	define('PATH_USER_IMPORTS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_USER_MANAGEMENT.'/user_imports/'); 
	define('PATH_SETTINGS_UPLOADS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_SETTINGS.'/site_settings/');
	define('PATH_FILE_UPLOADS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_FILE_MANAGEMENT.'/files/');
	// define('PATH_FILE_USER_UPLOADS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_FILE_MANAGEMENT.'/users/');
	define('PATH_SYSTEMS_UPLOADS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_SYSTEMS.'/systems_app/');
	define('PATH_ORGANIZATION_UPLOADS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_USER_MANAGEMENT.'/organizations/');
	define('PATH_ORGANIZATION_IMPORTS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_USER_MANAGEMENT.'/organizations/imports/');
	define('PATH_TERM_CONDITIONS_UPLOADS', PATH_UPLOADS.'terms_conditions_uploads/');
	define('PATH_CERTIFICATION_FILE_UPLOADS', PATH_UPLOADS.'certification_file/');
	define('PATH_STATEMENTS', PATH_UPLOADS.SYSTEM_CORE.'/'.CORE_MAINTENANCE.'/statements/');
	define('PATH_CKEDITOR_UPLOADS', PATH_UPLOADS.'ckeditor_uploads/');

	/*
	|---------------------------------------------------------------------
	| SCOPE
	|---------------------------------------------------------------------
	*/
	define('SCOPE_SYSTEM', '1');
	define('SCOPE_REGION', '2');
	define('SCOPE_AGENCY', '3');	
	define('SCOPE_DIRECT_NODES', '4');	
	define('SCOPE_OWN_AND_REGION', '5');
	define('SCOPE_OWN_AND_DR', '6');

	/*
	 |---------------------------------------------------------------------
	 | FILE TYPE
	 |---------------------------------------------------------------------
	 | These constants are used when defining the file type being uploaded in files module,
	 |
	 */
 	define( 'FILE_TYPE_DOCUMENTS', 'DOCUMENTS');
	define( 'FILE_TYPE_VIDEOS', 'VIDEOS');
	define( 'FILE_TYPE_AUDIOS', 'AUDIOS');
	define( 'FILE_TYPE_IMAGES', 'IMAGES');
	define( 'FILE_TYPE_ALBUMS', 'ALBUMS');

	/*
	 |---------------------------------------------------------------------
	 | FILE TYPE DIRECTORY
	 |---------------------------------------------------------------------
	 | These constants are used when defining the file type being uploaded in files module,
	 |
	 */
 	define( 'DIRECTORY_DOCUMENTS', 'documents/');
	define( 'DIRECTORY_VIDEOS', 'videos/');
	define( 'DIRECTORY_AUDIOS', 'audios/');
	define( 'DIRECTORY_IMAGES', 'images/');
	define( 'DIRECTORY_ALBUMS', 'albums/');

	/* EXTENSIONS */
	define('IMAGE_EXTENSIONS', 'png,jpg,jpeg,giff,bmp,tiff');
	define('DOCUMENT_EXTENSIONS', 'pdf,xls,xlsx,docx,doc,ppt,pptx,csv,txt');
	define('MEDIA_EXTENSIONS', 'avi,mp4,wmv,mp3');
	define('AUDIO_EXTENSIONS', 'mp3,flac');
	define('VIDEO_EXTENSIONS', 'avi,mp4,wmv,mkv');

	// INTIAL FLAGS
	define( 'INITIAL_YES', 1);
	define( 'INITIAL_NO', 0);

	// MAINTAINER FLAGS
	define( 'MAINTAINER_YES', 1);
	define( 'MAINTAINER_NO', 0);

	// ERROR CODE USED IN ERROR PAGE
	define('ERROR_CODE_500', '500');
	define('ERROR_CODE_404', '404');
	define('ERROR_CODE_401', '401');
	define('ERROR_CODE_402', '402');
	define('ERROR_CODE_NO_ORG', 'no_org');

	// ERROR TYPE USED IN ERROR PAGE
	define('ERROR_TYPE_INDEX', 'index');
	define('ERROR_TYPE_MODAL', 'modal');

	/* LOGGED IN FLAG */
	define('LOGGED_IN_FLAG_YES', 1);
	define('LOGGED_IN_FLAG_NO', 0);

	/* AUTHENTICATED_USER ROLE */
	define('AUTHENTICATED_USER_ROLE', 'AUTHENTICATED_USER');

	// REPORT TYPE
	define('REPORT_TYPE_PDF', 1);
	define('REPORT_TYPE_EXCEL', 2);
	define('REPORT_TYPE_DOC', 3);
	define('REPORT_TYPE_HTML', 4);

	// WORKFLOW SETTING URL ID
	define('WORKFLOW_SETTING', 'workflow_setting');

	// ENUM YES AND NO
	define('ENUM_YES', 'Y');
	define('ENUM_NO', 'N');

	// VISIBILITY VALUE
	define('VISIBLE_ALL', 1);
	define('VISIBLE_ONLY_ME', 2);
	define('VISIBLE_GROUPS', 3);
	define('VISIBLE_INDIVIDUALS', 4);

	// DEFAULT IMAGE/PIC 
	define( 'DEFAULT_ORG_LOGO', 'avatar_org.jpg' );

	/* DATATABLE QUERY MODES  */
	define('GET_RECORDS', 1);
	define('GET_TOTAL_RECORDS', 2);

	/*
	 |---------------------------------------------------------------------
	 | LOCATION
	 |---------------------------------------------------------------------
	 */
	define('LOCATION_REGION', 1);
	define('LOCATION_PROVINCE', 2);
	define('LOCATION_CITY', 3);
	define('LOCATION_MUNICIPALITY', 4);
	define('LOCATION_BARANGAY', 5);

	/*
	 |---------------------------------------------------------------------
	 | ORG TYPE
	 |---------------------------------------------------------------------
	 */
	define('ORG_TYPE_REGION', 1);
	define('ORG_TYPE_PROVINCE', 2);
	define('ORG_TYPE_CITY', 3);
	define('ORG_TYPE_MUNICIPALITY', 4);
	define('ORG_TYPE_SUBMUNICIPALITY', 5);
	define('ORG_TYPE_BARANGAY', 6);

	/* CKEDITOR IMAGE UPLOADER */
	define('CKEDITOR_ADMIN', 'administrator');
	define('CKEDITOR_ADMIN_PASS', 'default');
	
/*
 |---------------------------------------------------------------------
 | FOR ENCRYPTING/DECRYPTING DATA SAVED IN DATABASE
 |---------------------------------------------------------------------
 */
define('CORE_KEY_STRING', 'AS!AGA+E-C0R3~PHP');

// EXEMPT MODULES
define('EXEMPT_CORE_MODULES', 
	serialize( 
		array(
			
		) 
	)
);

// NOTIFICATION TYPE
define('NOTIFICATION_TYPE_EMAIL', 'EMAIL');
define('NOTIFICATION_TYPE_SMS', 'SMS');
define('NOTIFICATION_TYPE_SYSTEM', 'SYSTEM');

// SYS_PARAM SYSTEM USE 
define('SYSTEM_USE_TYPE_GOVERNMENT', 'GOVERNMENT');
define('SYSTEM_USE_TYPE_PRIVATE', 'PRIVATE');

// AUTHENTICATION FACTOR SECTION
define('AUTH_SECTION_ACCOUNT', 'AUTH_SECTION_ACCOUNT');
define('AUTH_SECTION_LOGIN', 'AUTH_SECTION_LOGIN');
define('AUTH_SECTION_PASSWORD', 'AUTH_SECTION_PASSWORD');

// AUTHENTICATION FACTOR
define('AUTHENTICATION_FACTOR_EMAIL', 1);
define('AUTHENTICATION_FACTOR_SMS', 2);

// STATEMENT MODULE TYPES 
define('STATEMENT_MODULE_TYPE_DPA', 1);
define('STATEMENT_MODULE_TYPE_TERM_COND', 2);
define('STATEMENT_MODULE_EMAIL_TEMPLATE', 3);

// STATEMENT TYPES 
define('STATEMENT_TYPE_TEXT', 1);
define('STATEMENT_TYPE_LINK', 2);
define('STATEMENT_TYPE_FILE', 3);

// STATEMENT CODE
define('STATEMENT_CODE_EMAIL_VERIFY_CODE', 'EMAIL_VERIFY_CODE');
define('STATEMENT_CODE_EMAIL_DPA', 'EMAIL_DPA');
define('STATEMENT_CODE_EMAIL_WELCOME_USER', 'EMAIL_WELCOME_USER');
define('STATEMENT_CODE_EMAIL_ACCOUNT_OWNER', 'EMAIL_ACCOUNT_OWNER');
define('STATEMENT_CODE_EMAIL_APPROVE_USER', 'EMAIL_APPROVE_USER');
define('STATEMENT_CODE_EMAIL_REJECT_USER', 'EMAIL_REJECT_USER');
define('STATEMENT_CODE_EMAIL_NEW_DEVICE_LOCATION', 'EMAIL_NEW_DEVICE_LOCATION');

// DEFAULT SMS API
define('DEFAULT_SMS_API', 'smsgatewayme');
define('SMS_APIS', serialize(array(
	'serial'		=> 'Modem/Stick',
	'smsgatewayme'  => 'SMS Gateway me',
	'twilio'		=> 'Twilio'
)));

// COMMON TEMPLATE CONSTANTS
define('TEMPLATE_DR_NAME', 'dropdown_value');
define('TEMPLATE_DR_MARK', '|+');
define('TEMPLATE_REGION_SEPARATOR', '_');
define('TEMPLATE_DR_NUM_DV', 150);
define('TEMPLATE_LOCATION_FILE', 'locations.xlsx');
define('TEMPLATE_PASSWORD', 'templatep@asWord');
define('TEMPLATE_REGION_SHEET', 'REGIONS');
define('TEMPLATE_PROVINCE_SHEET', 'PROVINCES');
define('TEMPLATE_MUNI_CITY_SHEET', 'CITIES_MUNICIPALITIES');
define('TEMPLATE_SUB_MUNICIPALTIES_SHEET', 'SUB_MUNICIPALTIES');
define('TEMPLATE_BARANGGAYS_SHEET', 'BARANGGAYS');

define('TEMPLATE_README_FILE', 'readme.xlsx');
define('OPERATION_COLUMN', 'operation type (A, E, D)');

 /*
 |---------------------------------------------------------------------
 | UPLOAD TEMPLATE SHEET NAMES
 |---------------------------------------------------------------------
 */	
define('REFERENCE_SHEET', 'REFERENCES');
define('ORGANIZATION_SHEET', 'ORGANIZATION');
define('USERS_SHEET', 'USERS');
define('ORGANIZATION_PARENT_SHEET', 'ORGANIZATION PARENTS');

// ICONS 
define('ICON_REFRESH', 'refresh');
define('ICON_ADD', 'library_add');
define('ICON_IMPORT', 'file_upload');
define('ICON_DELETE', 'delete');
define('ICON_EDIT', 'mode_edit');
define('ICON_VIEW', 'visibility');
define('ICON_UNDO', 'undo');
define('ICON_SAVE', 'save');
define('ICON_PRINT', 'local_printshop');
define('ICON_LOCK', 'LOCK');
define('ICON_UPLOAD', 'file_upload');
define('ICON_SETTING', 'settings_applications');
define('ICON_APPROVE', 'check');
define('ICON_ARCHIVE', 'archive');
