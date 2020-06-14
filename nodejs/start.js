// run server.js programmatically

var forever = require('forever-monitor');

var child = new (forever.Monitor)('server.js', {
  max: 10,
  silent: true,
  args: []
});

child.on('exit', function () {
  console.log('server.js has exited after 10 restarts');
});

child.on('watch:restart', function(info) {
    console.error('Restaring script because ' + info.file + ' changed');
});

child.on('restart', function() {
    console.error('Forever restarting script for ' + child.times + ' time');
});

child.on('exit:code', function(code) {
    console.error('Forever detected script exited with code ' + code);
});

child.start();