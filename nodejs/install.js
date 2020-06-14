var Service = require('node-windows').Service;
var basepath= __dirname;

// Create a new service object
var svc = new Service({
  name:'AsiagatePhpCoreNode',
  description: 'Real time event web server of GMMS.',
  script: basepath+'\\server.js'
});

// Install the script as a service.
svc.install();