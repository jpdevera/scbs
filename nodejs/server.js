var basepath        = __dirname;
var path            = require('path');
var path_app        = path.dirname(basepath);

var config          = require('./app/config');

var http            = require('http');
var fs              = require('fs');

if( config.ssl )
{
	var http 		= require('https');
}

/*var cmd             = require('./app/cmd');

cmd( path_app, path, basepath );*/

var app             = http.createServer(handler);

if( config.ssl )
{
	var options 	= {
	 	key: fs.readFileSync('/home/certificates.key'),
	    cert: fs.readFileSync('/home/certificates.crt'),
	    requestCert: false,
	    ca : fs.readFileSync('/home/ca.crt'),
	};

	var app        	= http.createServer(options, handler);
}

	/* if IIS/Windows */
	/*iosocket = require('socket.io')({
		'transports': [ 'xhr-polling' ],
		'resource': '/socket.io'
	}),
	io = iosocket.listen(app),*/
	/* else */
var io                      = require('socket.io').listen(app);

var Router                  = require('router');
var finalhandler            = require('finalhandler');

var router                  = Router();
var home                    = require('./app/home');
var qs 						= require('querystring');

 
var core_socket             = require('./app/core_socket')(io, router, qs, http);

// creating the server
/* if IIS/Windows */
//app.listen(process.env.PORT);
/* else */
app.listen(config.http.port);

// on server started we can load our generic html page
function handler(req, res) 
{
    router(req, res, finalhandler(req, res));
}

home( router, fs, basepath );