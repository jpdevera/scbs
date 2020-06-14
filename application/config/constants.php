<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 |--------------------------------------------------------------------------
 | File and Directory Modes
 |--------------------------------------------------------------------------
 |
 | These prefs are used when checking and setting modes when working
 | with the file system.  The defaults are fine on servers with proper
 | security, but you may wish (or even need) to change the values in
 | certain environments (Apache running a separate process for each
 | user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
 | always be used to set the mode correctly.
 |
 */
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);

/*
 |--------------------------------------------------------------------------
 | File Stream Modes
 |--------------------------------------------------------------------------
 |
 | These modes are used when working with fopen()/popen()
 |
 */

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
 |--------------------------------------------------------------------------
 | Display Debug backtrace
 |--------------------------------------------------------------------------
 |
 | If set to TRUE, a backtrace will be displayed along with php errors. If
 | error_reporting is disabled, the backtrace will not display, regardless
 | of this setting
 |
 */
define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
 |--------------------------------------------------------------------------
 | Exit Status Codes
 |--------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
define('EXIT_SUCCESS', 0); // no errors
define('EXIT_ERROR', 1); // generic error
define('EXIT_CONFIG', 3); // configuration error
define('EXIT_UNKNOWN_FILE', 4); // file not found
define('EXIT_UNKNOWN_CLASS', 5); // unknown class
define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
define('EXIT_USER_INPUT', 7); // invalid user input
define('EXIT_DATABASE', 8); // database error
define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
 |--------------------------------------------------------------------------
 | DIRECTORY and PATH
 |--------------------------------------------------------------------------
 |
 | These constants are used when working with directory and file naming
 |
 */
define('DS', DIRECTORY_SEPARATOR);
define('DOCUMENT_ROOT', dirname($_SERVER['SCRIPT_FILENAME']).DS);


// require_once 'application'.DS.'systems'.DS.'core'.DS.'constants.php';


/*
 |---------------------------------------------------------------------
 | DATA PRIVACY ACT
 |---------------------------------------------------------------------
 | These constants are used for data privacy act
*/
define('SECURITY_PASSPHRASE', '$+r@t0$_c0r3_$3cur1+y');
define('SECURITY_KEY', 512);

if( !defined('FCPATH') )
{
	define('FCPATH', dirname(dirname(dirname(__FILE__))) );
}

define('DB_PRODUCT_URL', 'subscription_core');
define('PRODUCT_SUBSCRIPTION', 0);
/*else
{
	require_once 'application'.DS.'systems'.DS.'core'.DS.'constants.php';
	require_once 'application'.DS.'systems'.DS.'bootcamp'.DS.'constants.php';
}*/

function get_current_url($localhost = FALSE, $project_code = '')
{
	$http_request 		= '';

	if( ISSET( $_SERVER['HTTP_HOST'] ) )
	{
		$http_request 	= 'http';
	}

	if (!EMPTY($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] != 'off') 
	{
	    // SSL connection
	    $http_request 	= 'https';
	}

	$base_url_str 		= '';
	if( $localhost )
	{		


		if( ISSET( $_SERVER['HTTP_HOST'] ) )
		{
			$base_url_str 	= $http_request.'://'.$_SERVER['HTTP_HOST'].'/'.$project_code.'/';
		}
	}
	else
	{
		$base_url_str 	= $_SERVER['HTTP_HOST'];
	}

	return $base_url_str;
}

function get_routes_file($current_url)
{
	$routes_url 	= NULL;
	if( !EMPTY( PRODUCT_SUBSCRIPTION ) )
	{
		require_once (APPPATH .'config/database.php');

		if( ISSET( $db[DB_PRODUCT_URL] ) )
		{

			$url_db 	= $db[DB_PRODUCT_URL];
			
			// $db 		=& DB();	

			$DB = new PDO('mysql:host='.$url_db['hostname'].'; dbname='.$url_db['database'].';charset='.$url_db['char_set'], $url_db['username'], $url_db['password']);
			$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$DB->exec('SET NAMES ' . $url_db['char_set']);

			$sql 		= "
				SELECT 	b.*
				FROM 	".DB_PRODUCT_URL.".product_web_address a 
				LEFT 	JOIN ".DB_PRODUCT_URL.".product_web_address_schema b 
					ON 	a.system_code = b.system_code
					AND a.link = b.link 
				WHERE 	a.link = '".$current_url."'
";
			$query 		= $DB->query($sql);
			$result 	= $query->fetchAll(PDO::FETCH_ASSOC);

			if( !EMPTY( $result ) )
			{
				$arr 	= array();
				foreach( $result as $res )
				{
					$arr[$res['schema_code']]	= $res['schema_name'];
				}

				$routes_url = $arr;
			}
			else 
			{
				require FCPATH.'error_url_not_allowed.php';
				exit;
			}
		}
	}
	
	return $routes_url;
}

require_once FCPATH.DS.'application'.DS.'systems'.DS.'core'.DS.'constants.php';
require_once FCPATH.DS.'application'.DS.'config'.DS.'db_constants.php';
require_once FCPATH.DS.'application'.DS.'systems'.DS.'cbs'.DS.'constants.php';