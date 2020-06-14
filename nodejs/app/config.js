var config 			= {};

config.database 		= {};
config.database.core 	= {};
// example for multiple database connection
// config.database.gmms 	= {};

config.http 			= {};
config.app 				= {};

config.ssl 				= false;

config.database.core.connectionLimit 	= 500;
config.database.core.host 				= '127.0.0.1';
config.database.core.user 				= 'root';
config.database.core.password 			= '';
config.database.core.database 			= 'asiagate_php_core';
config.database.core.debug 				= false;
config.database.core.port 				= 3306;

config.http.port 						= 8000;

module.exports  = config;