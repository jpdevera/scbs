<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/*
 |---------------------------------------------------------------------
 | SYSTEMS DATABASE
 |---------------------------------------------------------------------
 | Define the name of database/s used in the system.
 */

if( PRODUCT_SUBSCRIPTION )
{
	$current_url 	= get_current_url(TRUE, PROJECT_CODE);
	$routes_url 	= get_routes_file($current_url);

	if( ISSET( $routes_url ) AND !EMPTY( $routes_url ) )
	{
		$db_details 	= $routes_url;
		
		foreach( $db_details as $db_const => $schema )
		{
			if( !EMPTY( $schema ) )			
			{
				define($db_const, $schema);		
			}
		}
	}
	else
	{
		define('DB_CORE', PROJECT_CODE . '_core');
	}
}
else
{
	define('DB_CORE', PROJECT_CODE . '_core');	
}
