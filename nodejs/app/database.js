var config          = require('./config');
var mysql  			= require('mysql');
var pool_cluster   	= mysql.createPoolCluster();

pool_cluster.add('core', config.database.core);

module.exports  	= pool_cluster;