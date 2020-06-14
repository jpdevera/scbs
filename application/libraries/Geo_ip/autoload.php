<?php 

require 'Geo_autoloader.php';

$loady 		= new Geo_autoloader\Geo_autoloader( __DIR__ );

$loady->setPsr4( array(
	'Geo_ip\\' => ''
) );

$loady->register(TRUE, FALSE);