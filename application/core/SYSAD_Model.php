<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SYSAD_Model extends Common_Model {
	
	protected static $dsn = DB_CORE;
	protected static $system = SYSTEM_CORE;
				
	const CORE_TABLE_APPS					= 'apps';
	const CORE_TABLE_APP_ROLES				= 'app_roles';
	const CORE_TABLE_AREA_COLUMNS			= 'area_columns';
	const CORE_TABLE_AREAS					= 'areas';
	const CORE_TABLE_AUDIT_TRAIL			= 'audit_trail';
	const CORE_TABLE_AUDIT_TRAIL_DETAIL		= 'audit_trail_detail';
	const CORE_TABLE_FILE 					= 'file';
	const CORE_TABLE_FILE_VERSIONS 			= 'file_versions';
	const CORE_TABLE_GROUPS 				= 'groups';
	const CORE_TABLE_LOCATIONS 				= 'locations';
	const CORE_TABLE_LOCATION_CITY_CLASS 	= 'location_city_class';
	const CORE_TABLE_LOCATION_CLASS 		= 'location_class';
	const CORE_TABLE_LOCATION_TYPES	 		= 'location_types';
	const CORE_TABLE_MODULES 				= 'modules';
	const CORE_TABLE_MODULE_ACTIONS			= 'module_actions';
	const CORE_TABLE_MODULE_ACTION_ROLES	= 'module_action_roles';
	const CORE_TABLE_MODULE_SCOPES			= 'module_scopes';
	const CORE_TABLE_MODULE_SCOPE_ROLES		= 'module_scope_roles';
	const CORE_TABLE_NOTIFICATIONS			= 'notifications';
	const CORE_TABLE_ORGANIZATIONS			= 'organizations';
	const CORE_TABLE_ORG_PATHS 				= 'org_paths';
	const CORE_TABLE_ORG_PARENTS 			= 'org_parents';
	const CORE_TABLE_ORG_GROUP_TYPE 		= 'org_group_type';
	const CORE_TABLE_PROCESS				= 'process';
	const CORE_TABLE_PROCESS_ACTIONS		= 'process_actions';
	const CORE_TABLE_PROCESS_STAGES			= 'process_stages';
	const CORE_TABLE_PROCESS_STAGE_ROLES	= 'process_stage_roles';
	const CORE_TABLE_PROCESS_STEPS			= 'process_steps';
	const CORE_TABLE_ROLES					= 'roles';
	const CORE_TABLE_SITE_SETTINGS			= 'site_settings';
	const CORE_TABLE_SYSTEMS				= 'systems';
	const CORE_TABLE_SYSTEM_ROLES			= 'system_roles';
	const CORE_TABLE_SYS_PARAM				= 'sys_param';
	const CORE_TABLE_TO_DOS					= 'to_dos';
	const CORE_TABLE_TOGGLE_FIELDS			= 'toggle_fields';
	const CORE_TABLE_USER_GROUPS 			= 'user_groups';
	const CORE_TABLE_USERS					= 'users';
	const CORE_TABLE_USER_HISTORY			= 'user_history';
	const CORE_TABLE_USER_ROLES				= 'user_roles';
	const CORE_TABLE_WIDGET_ROLES			= 'widget_roles';
	const CORE_TABLE_WIDGET_TYPES			= 'widget_types';
	const CORE_TABLE_WIDGETS				= 'widgets';
	const CORE_WORKFLOWS 					= 'workflows';
	const CORE_PARAM_TASK_ACTIONS 			= 'param_task_actions';
	const CORE_WORKFLOW_STAGES 				= 'workflow_stages';
	const CORE_WORKFLOW_STAGE_TASKS 		= 'workflow_stage_tasks';
	const CORE_WORKFLOW_TASK_ACTIONS 		= 'workflow_task_actions';
	const CORE_WORKFLOW_TASK_ROLES 			= 'workflow_task_roles';
	const CORE_WORKFLOW_TASK_APPENDABLE		= 'workflow_task_appendable';
	const CORE_WORKFLOW_TASK_PREDECESSORS	= 'workflow_task_predecessors';
	const CORE_WORKFLOW_TASK_OTHER_DETAILS  = 'workflow_task_other_details';

	const CORE_TABLE_PARAM_VISIBILITY 		= 'param_visibility';
	const CORE_TABLE_FILE_VISIBILITY 		= 'file_visibility';
	const CORE_TABLE_FILE_ACCESS_RIGHTS 	= 'file_access_rights';

	const CORE_TABLE_EMAIL_NOTIFICATION_QUEUES	= 'email_notification_queues';
	const CORE_TABLE_SMS_NOTIFICATION_QUEUES	= 'sms_notification_queues';

	const CORE_TABLE_USER_AGREEMENTS 			= 'user_agreements';

	const CORE_TABLE_ANNOUNCEMENTS 				= 'announcements';
	const CORE_TABLE_USER_ANNOUNCEMENTS 		= 'user_announcements';
	const CORE_TABLE_TEMP_USERS 				= 'temp_users';

	const CORE_TABLE_FILE_DB_STORAGE 			= 'file_db_storage';
	const CORE_TABLE_ORGANIZATION_PARENTS 		= 'organization_parents';
	const CORE_TABLE_USER_ORGANIZATIONS 		= 'user_organizations';
	const CORE_TABLE_TEMP_USER_ORGANIZATIONS 	= 'temp_user_organizations';
	const CORE_TABLE_TEMP_USER_ROLES 			= 'temp_user_roles';

	const CORE_TABLE_USER_SECURITY_ANSWERS 		= 'user_security_answers';
	const CORE_TABLE_TEMP_USER_SECURITY_ANSWERS = 'temp_user_security_answers';
	const CORE_TABLE_TEMP_USER_AGREEMENTS 		= 'temp_user_agreements';

	const CORE_TABLE_AUTHENTICATION_FACTORS 	= 'authentication_factors';

	const CORE_TABLE_USER_MULTI_AUTH 			= 'user_multi_auth';

	const CORE_TABLE_TEMP_USER_MULTI_AUTH 		= 'temp_user_multi_auth';

	const CORE_TABLE_AUTHENTICATION_FACTOR_SECTIONS = 'authentication_factor_sections';
	const CORE_TABLE_STATEMENT_MODULE_TYPES 		= 'statement_module_types';
	const CORE_TABLE_STATEMENT_TYPES 				= 'statement_types';

	const CORE_TABLE_STATEMENTS 				= 'statements';
	const CORE_TABLE_STATEMENT_UPLOADS 			= 'statement_uploads';

	const CORE_TABLE_PARAM_EXTENSION_NAME 		= 'param_extension_name';
	const CORE_TABLE_SECURITY_QUESTIONS 		= 'security_questions';

	const CORE_TABLE_DEFAULT_MODULE_ACTION_ROLES 	= 'default_module_action_roles';
	const CORE_TABLE_DEFAULT_MODULE_SCOPE_ROLES 	= 'default_module_scope_roles';
	const CORE_TABLE_ARCHIVED_AUDIT_TRAIL 			= 'archived_audit_trail';
	const CORE_TABLE_ARCHIVED_AUDIT_TRAIL_DETAIL 	= 'archived_audit_trail_detail';

	const CORE_TABLE_USER_DEVICE_LOCATION_AUTH 		= 'user_device_location_auth';
	const CORE_ORGANIZATION_TYPES 					= 'organization_types';
	const CORE_TABLE_STATEMENT_TOKENS 				= 'statement_tokens';

	const CORE_TABLE_TEMP_ORGANIZATIONS 			= 'temp_organizations';
}