var nodeCmd         = require('node-cmd');
var config          = require('./config');

module.exports 		= function(path_app, path, basepath)
{
	nodeCmd.get('php '+path_app+path.sep+'index.php', function(err, data, stderr)
	{
	    console.log(data);
	    console.log(err);
	    console.log(stderr);
	});
};