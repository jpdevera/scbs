<?php
defined('BASEPATH') OR exit('No direct script access allowed');

 /*
 |---------------------------------------------------------------------
 | SYSTEMS DATABASE
 |---------------------------------------------------------------------
 | Define the name of database/s used in the system.
 */
define('DB_SCBS', PROJECT_CODE);


/*
 |---------------------------------------------------------------------
 | SYSTEM CODE
 |---------------------------------------------------------------------
 | Define the system code, this will be used to tag which features and
 | modules are included in a system
 */
define('CBS', 'CBS');

/*
 |---------------------------------------------------------------------
 | SYSTEMS FOLDER
 |---------------------------------------------------------------------
 | These constants are used when defining the systems folder path of
 | your controller in the hyperlink or function.
 */
define('SYSTEM_CBS', 'cbs');
define('CBS_DASHBOARD_PAGE', 'dashboard/dashboard');

/*
 |---------------------------------------------------------------------
 | MODULES FOLDER
 |---------------------------------------------------------------------
 | These constants are used when defining the modules folder path of
 | your controller in the hyperlink or function.
 */

define('FOLDER_DASHBOARD', 'dashboard');
define('FOLDER_CUSTOMER', 'customer');
define('FOLDER_SAVINGS', 'savings');
define('FOLDER_CURRENT', 'current');
define('FOLDER_LOANS', 'loans');
define('FOLDER_BATCH', 'batch');
define('FOLDER_GENERAL_LEDGER', 'general_ledger');
define('FOLDER_FILE_MAINTENANCE', 'file_maintenance');

/*
 |---------------------------------------------------------------------
 | PROJECT MODULES
 |---------------------------------------------------------------------
 | Declare all the modules parent in your project.
 */
define('MODULE_CBS_DASHBOARD', 'CBS_DASHBOARD');
define('MODULE_CBS_CUSTOMER', 'CBS_CUSTOMER');
define('MODULE_CBS_SAVINGS', 'CBS_SAVINGS');
define('MODULE_CBS_CURRENT', 'CBS_CURRENT');
define('MODULE_CBS_LOANS', 'CBS_LOANS');
define('MODULE_CBS_BATCH', 'CBS_BATCH');
define('MODULE_CBS_BACK_OFFICE', 'CBS_BACK_OFFICE');
define('MODULE_CBS_GENERAL_LEDGER', 'CBS_GENERAL_LEDGER');
define('MODULE_CBS_USERS', 'CBS_USERS');
define('MODULE_CBS_PERIODIC_PROCESS', 'CBS_PERIODIC_PROCESS');
define('MODULE_CBS_FILE_MAINTENANCE', 'CBS_FILE_MAINTENANCE');
define('MODULE_CBS_MISCELLANEOUS', 'CBS_MISCELLANEOUS');


/*
| GENERAL LEDGER
*/
define('MODULE_GENERAL_LEDGER_TYPE', 'GENERAL_LEDGER_TYPE');
define('MODULE_GENERAL_LEDGER_CODE', 'GENERAL_LEDGER_CODE');
define('MODULE_GENERAL_LEDGER_SORT', 'GENERAL_LEDGER_SORT');
define('MODULE_GENERAL_LEDGER_ACCOUNTS', 'GENERAL_LEDGER_ACCOUNTS');
define('MODULE_GENERAL_LEDGER_TRANSACTIONS', 'GENERAL_LEDGER_TRANSACTIONS');

/*
| FILE MAINTENANCE
*/
define('MODULE_FILE_MAINTENANCE_BRANCH', 'FILE_MAINTENANCE_BRANCH');
define('MODULE_FILE_MAINTENANCE_HOLIDAYS', 'FILE_MAINTENANCE_HOLIDAYS');


/*
 |---------------------------------------------------------------------
 | ADDRESS TYPES
 |---------------------------------------------------------------------
 |
 */


 /*
 |---------------------------------------------------------------------
 | UPLOAD PATH AND SUPPORTING DOCUMENTS
 |---------------------------------------------------------------------
 |
 */
 define('PATH_UPLOAD_BRANCH', PATH_UPLOADS.SYSTEM_CBS.'/branch/');
 define('MAX_FILE_SIZE', 25);
 define('LOGO_ALLOWED_TYPES', "jpg,jpeg,png");
 // define('LOGO_ALLOWED_TYPES', "pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif,bmp,tiff");
