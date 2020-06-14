<?php
/* ---------------------------------------------------------------------
 *  NOTE: 
 *  	- always put the original error message of mysql as comment
 *  	- always add prefix "mysql_err_" on error code
 *  
 *  	ex.
 *  		// error code : cannot update or delete parent row - fk exists
 *  		$lang['mysql_err_1451']	= ' is already use.';
 *  
 *  SOURCE: https://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html
 * ---------------------------------------------------------------------
 */

// default 
$lang['mysql_err_default'] = 'Database error, please contact the system administrator.';
// error code : cannot update or delete parent row - fk exists
$lang['mysql_err_1451']	= 'Sorry, record with dependency should not be altered.';
$lang['mysql_err_1451_4']	= 'Sorry, record with dependency cannot be deleted.';
// error code : Cannot add or update a child row: a foreign key constraint fails (%s)
$lang['mysql_err_1452']	= 'Cannot add or update a record.';
// error code :  Integrity constraint violation: 1062 Duplicate entry %s for key %s
$lang['mysql_err_1062']	= 'Sorry, record already exists.';