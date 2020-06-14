<?php 

require 'Sms_autoloader.php';

$loady 		= new Sms_autoload\Sms_autoloader( __DIR__ );

$loady->setPsr4( array(
	'Sms\\' => ''
) );

$loady->register(TRUE, FALSE);