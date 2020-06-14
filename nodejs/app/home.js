module.exports 	= function( router, fs, basepath )
{
	router.get('/', function (req, res) {
		fs.readFile(basepath + '/index.html', function(err, data) {
			if (err) {
				console.log(err);
				res.writeHead(500);
				return res.end('Error loading index.html');
			}
			res.writeHead(200);
			res.end(data);
		});
	});

}