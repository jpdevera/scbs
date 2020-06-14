var database            = require('./database');

var url                 = require('url');
var url_exists          = require('./checkUrlExists');
var config              = require('./config');
var fs                  = require('fs');
var path_obj            = require('path');

module.exports          = function(io, router, qs, http)
{
    var connectionsArray            = [],
    POLLING_NOTI_INTERVAL           = 3000,
    POLLING_LOGOUT_INTERVAL         = 5000,
    pollingTimerNotifications;

    var check_notif   = false;
    var check_logout  = false;
    var check_us      = false;
    var check_inact   = false;

    var pollingLoopNotifications    = function () 
    {
        database.getConnection( 'core', function( err, connection )
        {
            // Make the database query
            var query = connection.query("SELECT A.notification, A.notified_by, A.notification_date, A.read_date, B.photo, " +
                    "IF(A.notify_users = '-1', (SELECT GROUP_CONCAT(user_id SEPARATOR ',') FROM users WHERE user_id != A.notified_by), A.notify_users) notify_users, " +
                    "IF(A.notify_orgs = '-1', (SELECT GROUP_CONCAT(org_code SEPARATOR ',') FROM organizations), A.notify_orgs) notify_orgs, " +
                    "IF(A.notify_roles = '-1', (SELECT GROUP_CONCAT(role_code SEPARATOR ',') FROM roles), A.notify_roles) notify_roles " +
                    "FROM notifications A, users B " + 
                    "WHERE A.notified_by = B.user_id ORDER BY A.notification_date DESC"),
                notificationsArray = []; // this array will contain the result of our db query

            // set up the query listeners
            query
            .on('error', function(err) {
                // Handle error, and 'end' event will be emitted after this as well
                console.log( err );
                updateSocketsNotifications( err );
            })
            .on('result', function( notification ) {
                // it fills our array looping on each user row inside the db
                notificationsArray.push( notification );
            })
            .on('end',function(){
                // loop on itself only if there are sockets still connected
                if(connectionsArray.length) {
                    pollingTimerNotifications = setTimeout( pollingLoopNotifications, POLLING_NOTI_INTERVAL );

                    updateSocketsNotifications({notificationsArray:notificationsArray});
                }
            });

        } );

    };	

    var updateSocketsNotifications = function ( data ) {
        // store the time of the latest update
        data.time = new Date();
        
        // send new data to all the sockets connected
    	connectionsArray.forEach(function( tmpSocket ){
    		//tmpSocket.volatile.emit( 'notifications' , data );
    		tmpSocket.emit( 'notifications' , data );
    	});
    };

    var pollingLoopLogout = function () 
    {

        database.getConnection( 'core', function( err, connection )
        {
             // Make the database query
            var query = connection.query("SELECT GROUP_CONCAT(user_id) user_id " +
                    "FROM users " +
                    "WHERE logged_in_flag = 0"),
            logoutArray = []; // this array will contain the result of our db query

            connection.release();
            // set up the query listeners
            query
            .on('error', function(err) {
                // Handle error, and 'end' event will be emitted after this as well
                console.log( err );
                updateSocketsLogout( err );
            })
            .on('result', function( logout ) {
                // it fills our array looping on each user row inside the db
                logoutArray.push( logout );
            })
            .on('end',function(){
                // loop on itself only if there are sockets still connected
                if(connectionsArray.length) {
                    pollingTimerLogout = setTimeout( pollingLoopLogout, POLLING_LOGOUT_INTERVAL );

                    updateSocketsLogout({logoutArray:logoutArray});
                }
            });

        });
    };

    var updateSocketsLogout = function ( data ) 
    {
        // store the time of the latest update
        data.time = new Date();
        
        // send new data to all the sockets connected
        connectionsArray.forEach(function( tmpSocket ){
            //tmpSocket.volatile.emit( 'notifications' , data );
            tmpSocket.emit( 'logout' , data );
        });
    };



    //create a new websocket connection to keep the content updated without any AJAX request
    /*var alerts = io
    .of('/alerts')
    .on('connection', function (socket) {
    	// start the polling loop only if at least there is one user connected
        if (!connectionsArray.length) {
        	pollingLoopNotifications();
            pollingLoopLogout();
        }

        socket.on('disconnect', function () {
            var socketIndex = connectionsArray.indexOf( socket );
            console.log('socket = ' + socketIndex + ' disconnected');
            if (socketIndex >= 0) {
                connectionsArray.splice( socketIndex, 1 );
            }
        });
        
        console.log( 'A new socket is connected!' );
        connectionsArray.push( socket );
        console.log('Number of connections:' + connectionsArray.length);
    });*/

    var logout_func               = function( socket, query )
    {

        if( check_logout == true )
        {
            return; 
        }

        check_logout = true;

        database.getConnection( 'core', function( err, connection )
        {
            connection.query( query, function( err, rows ) 
            {
                if(!err) 
                {
                    setTimeout( logout_func.bind(null, socket, query), POLLING_LOGOUT_INTERVAL );
                    
                    socket.emit('logout', rows);
                }
                else
                {
                    console.log(err);
                }

                connection.release();
                check_logout = false;
            } );
        } );
        
    }

   /* var check_online              = function( socket )
    {
        is_online().then(function(online)
        {
            setTimeout( check_online.bind(null, socket), POLLING_LOGOUT_INTERVAL );
           
            if( online )
            {
                socket.emit('check_online', true);
            }
            else
            {
                socket.emit('check_online', false);   
            }
        });
    }

    var server_online            = function( socket )
    {
        url_exists(http, url, config.app.hostname, function( response )
        {
            setTimeout( server_online.bind(null, socket), POLLING_LOGOUT_INTERVAL );

            if( response.statusCode > 200 && response.statusCode < 400 )
            {
                socket.emit('server_online', true);
            }
            else
            {
                socket.emit('server_online', false);
            }
        });
    }*/

    var notify_generate_where     = function( data )
    {
        var add_where   = '';
        
        if( data.user_id !== undefined )
        {
            add_where       += ' ( ';

            if( data.user_id instanceof Array )
            {   

                var len_user    = data.user_id.length,
                    us_i        = 0,
                    us_ids      = data.user_id.join('\',\'');

                for( ; us_i < len_user; us_i++ )
                {
                    add_where += ' FIND_IN_SET( '+data.user_id[us_i]+', notify_users ) OR ';
                }

                add_where   = ' ( '+add_where.substring(0, add_where.length - 3)+' ) ';

                add_where   += ' OR ';
                 add_where   += " notify_users IN ('"+us_ids+"') ";
            }
            else
            {
                add_where   += ' FIND_IN_SET( '+data.user_id+', notify_users ) ';

                add_where   += ' OR ';
                add_where   += ' notify_users = '+data.user_id;
            }

            add_where       += ' ) OR ';
        }

        if( data.org_code !== undefined )
        {
            add_where       += ' ( ';

            if( data.org_code instanceof Array )
            {   

                var len_org     = data.org_code.length,
                    or_i        = 0,
                    or_ids      = data.org_code.join('\',\'');

                for( ; or_i < len_org; or_i++ )
                {
                    add_where += ' FIND_IN_SET( "'+data.org_code[or_i]+'", notify_orgs ) OR ';
                }

                add_where   = ' ( '+add_where.substring(0, add_where.length - 3)+' ) ';

                add_where   += ' OR ';
                add_where   += " notify_orgs IN ('"+or_ids+"') ";
            }
            else
            {
                add_where   += ' FIND_IN_SET( "'+data.org_code+'", notify_orgs ) ';

                add_where   += ' OR ';
                add_where   += ' notify_orgs = "'+data.org_code+'" ';
            }

            add_where       += ' ) OR ';
        }

        if( data.user_roles !== undefined )
        {
            add_where       += ' ( ';

            if( data.user_roles instanceof Array )
            {   
                var len_r       = data.user_roles.length,
                    ur_i        = 0,
                    ur_ids      = data.user_roles.join('\',\'');

                for( ; ur_i < len_r; ur_i++ )
                {
                    add_where += ' FIND_IN_SET( "'+data.user_roles[ur_i]+'", notify_roles ) OR ';
                }

                add_where   = ' ( '+add_where.substring(0, add_where.length - 3)+' ) ';

                add_where   += ' OR ';
                add_where   += " notify_roles IN ('"+ur_ids+"') ";
            }
            else
            {
                add_where   += ' FIND_IN_SET( "'+data.user_roles+'", notify_roles ) ';

                add_where   += ' OR ';
                add_where   += ' notify_roles = "'+data.user_roles+'" ';
            }

            add_where       += ' ) OR ';
        }

        add_where   = add_where.substring(0, add_where.length - 3);

        return add_where;
    }

    var notify_auto               = function( socket, user_connection_notif, query )
    {
        var shown_notif_id        = [];

        if( check_notif == true )
        {
            return; 
        }
        
        check_notif = true;

        setTimeout( notify_auto.bind(null, socket, user_connection_notif, query), POLLING_LOGOUT_INTERVAL );

        database.getConnection( 'core', function( err, connection )
        {
            connection.query( query, function( err, rows ) 
            {

                if(!err) 
                {
                    rows.forEach(function(elem, index)
                    {
                        var users   = elem.notify_users.split(','),
                            i       = 0,
                            len     = users.length;

                        // socket.broadcast.emit('notify_users', elem);
                       
                        for( ; i < len; i++ )
                        {
                            if( user_connection_notif[ users[i] ] !== undefined )
                            {  
                                socket.nsp.to( user_connection_notif[ users[i] ] ).emit('notify_users', elem);
                            }
                        }

                        shown_notif_id.push( elem.notification_id );
                        
                    } );
                }
                else
                {
                    console.log(err);
                }
                
                if( shown_notif_id.length !== 0 )
                {
                    var shown_notification_ids = shown_notif_id.join('\',\'');

                    var query_upd = `
                        UPDATE  notifications
                        SET     displayed_socket_flag = 'Y'
                        WHERE   1 = 1
                        AND     notification_id IN ('`+shown_notification_ids+`')
                    `;

                    connection.query( query_upd, function( err, rows ) 
                    {

                        if(!err) 
                        {
                            console.log('Notifications for displayed_socket_flag : '+shown_notification_ids+' are updated!');
                        }
                        else
                        {
                            console.log('auto_update');
                            console.log(err);
                        }   

                    } );

                    var query_upd_list = `
                        UPDATE  notifications
                        SET     listed_flag = 'Y'
                        WHERE   1 = 1
                    `;

                    connection.query( query_upd_list, function( err, rows ) 
                    {

                        if(!err) 
                        {
                            console.log('Notifications for listed_flag :  are updated!');
                        }
                        else
                        {
                            console.log('auto_update');
                            console.log(err);
                        }   

                    } );
                }
                connection.release();
                check_notif = false;
            } );
        } );
    }

    var inactive_user_func        = function( socket, query )
    {
        if( check_inact == true )
        {
            return; 
        }

        check_inact = true;

        database.getConnection( 'core', function( err, connection )
        {
            connection.query( query, function( err, rows ) 
            {
                if(!err) 
                {

                    // setTimeout( inactive_user_func.bind(null, socket, query), POLLING_LOGOUT_INTERVAL );
                    var result = rows;
                     // console.log(rows);
                    socket.emit('inactive_user', result);
                }
                else
                {
                    console.log(err);
                }

                connection.release();
                check_inact = false;
            } );
        } );
    }

    var check_user                = function( socket, query )
    {
        if( check_us == true )
        {
            return; 
        }

        check_us = true;

        database.getConnection( 'core', function( err, connection )
        {
            connection.query( query, function( err, rows ) 
            {
                if(!err) 
                {
                    setTimeout( check_user.bind(null, socket, query), POLLING_LOGOUT_INTERVAL );
                    
                    rows.forEach(function(elem, index)
                    {
                        socket.nsp.to( user_connection_notif[ elem.user_id ] ).emit('check_user', elem);

                    } );
                }
                else
                {
                    console.log(err);
                }

                connection.release();
                check_us = false;
            } );
        } );
    }

    var user_connection_notif     = {};
    var notifications             = io
    .of('/my_notifications')
    .on('connection', function( socket )
    {

        console.log('user connected');

        socket.on('set_user_notif', function(data){
            user_connection_notif[data.user_id] = socket.id;
            socket.user_id = data.user_id;

            var query = `
                UPDATE users
                SET logged_in_flag = 1
                WHERE user_id = `+data.user_id+`
            `;

            database.getConnection( 'core', function( err, connection )
            {
                connection.query( query, function( err, rows ) 
                {
                    if(!err) 
                    {

                    }
                    else
                    {
                        console.log(err);
                    }
                } );
            } );

            console.log('Connect');
            console.log(user_connection_notif);
        });

       /* socket.on('check_online', function(data)
        {
            check_online( socket );
        } );

        socket.on('server_online', function(data)
        {
            server_online( socket );
        } );*/

        socket.on('logout', function(data)
        {
            var query = `
                SELECT  GROUP_CONCAT(user_id) user_id
                FROM    users
                WHERE   logged_in_flag = 0
            `;


           /* database.getConnection( 'core', function( err, connection )
            {*/
                logout_func( socket, query );
            // } );
        });

        socket.on('check_user', function( data )
        {
            var query   = `
                SELECT  a.user_id
                FROM    users a
                WHERE   a.status NOT IN ('STATUS_ACTIVE')
            `;


            check_user( socket, query );
        });

        socket.on('notify_users', function(data)
        {
            var notification_ids = '';
            var shown_notif_id   = [];

            if( data.notification_ids !== undefined )
            {
                notification_ids = data.notification_ids.join('\',\'');
            }

            var query = `
                SELECT * FROM notifications
                WHERE 1 = 1
                AND displayed_socket_flag = 'N'
            `;

            if( notification_ids != '' )
            {
                query += " AND notification_id IN ('"+notification_ids+"') "
          

                database.getConnection( 'core', function( err, connection )
                {
                    connection.query( query, function( err, rows ) 
                    {
                        if(!err) 
                        {
                            rows.forEach(function(elem, index)
                            {
                                var users   = elem.notify_users.split(','),
                                    i       = 0,
                                    len     = users.length;

                                // socket.broadcast.emit('notify_users', elem);
                               
                                for( ; i < len; i++ )
                                {
                                    if( user_connection_notif[ users[i] ] !== undefined )
                                    {  
                                        socket.nsp.to( user_connection_notif[ users[i] ] ).emit('notify_users', elem);
                                    }
                                }

                                shown_notif_id.push( elem.notification_id );
                                
                            } );

                            if( shown_notif_id.length !== 0 )
                            {
                                var shown_notification_ids = shown_notif_id.join('\',\'');

                                var query_upd = `
                                    UPDATE  notifications
                                    SET     displayed_socket_flag = 'Y',
                                            listed_flag = 'Y'
                                    WHERE   1 = 1
                                    AND     notification_id IN ('`+shown_notification_ids+`')
                                `;

                                connection.query( query_upd, function( err, rows ) 
                                {

                                    if(!err) 
                                    {
                                        console.log('Notifications for displayed_socket_flag : '+shown_notification_ids+' are updated!');
                                    }
                                    else
                                    {
                                        console.log(err);
                                    }   

                                } );
                            }

                            connection.release();
                        }
                        else
                        {
                            console.log(err);
                        }  
                    });
                });
            }
            // socket.iobroadcast.to(user_connection['1']).emit('notifyUsers', data);
        });

        socket.on('notify_users_auto', function(data)
        {
            query   = `
                SELECT * FROM notifications
            `;

            var add_where   = '';

            add_where       = notify_generate_where( data );

            if( add_where != '' )
            {

                query += `
                    WHERE (
                        `+add_where+`
                    )
                    AND    displayed_socket_flag = 'N'
                `
            }

             /*  database.getConnection( 'core', function( err, connection )
            {*/
                notify_auto( socket, user_connection_notif, query );
            // } );
            /*router.post('/notification_socket', function (request, response) 
            {
                var body = '';

                request.on( 'data', function( chunk )
                {
                    body += chunk;
                });

                request.on( 'end', function() 
                {
                    var post_data   = qs.parse(body);

                    var users   = post_data.notify_users.split(','),
                    i       = 0,
                    len     = users.length;

                    for( ; i < len; i++ )
                    {   
                        if( user_connection_notif[ users[i] ] !== undefined )
                        {
                             socket.nsp.to( user_connection_notif[ users[i] ] ).emit('notify_users_auto', post_data);
                        }
                    }

                    response.end(JSON.stringify(post_data));

                } );
            } );*/
        });

        socket.on('inactive_user', function( data )
        {
            var user_where = '';

            if( Object.keys(user_connection_notif).length !== 0 )
            {
                user_where += ' AND user_id NOT IN ( ';

                for( var key in user_connection_notif )
                {
                    user_where += key+',';
                }

                user_where  = user_where.substring(0, user_where.length - 1);
                user_where += ')';
            }

             var query = `
                UPDATE  users
                SET     logged_in_flag = 0
                WHERE   logged_in_flag = 1
                `+user_where+`
            `;

            inactive_user_func( socket, query );
        });

        socket.on('updateNotification', function(data)
        {

            var add_where   = '';

            add_where       = notify_generate_where(data);
            
            var query       = `
                UPDATE notifications SET read_date = "`+data.datetime+`" WHERE read_date IS NULL 
                AND (  
                    `+add_where+`
                )
    `;
            database.getConnection( 'core', function( err, connection )
            {
                connection.query( query, function( err, rows ) 
                {
                     connection.release();

                    if(!err) 
                    {
                        console.log('Notifications for user : '+data.user_id+' are updated!');
                    }
                    else
                    {
                        console.log(err);
                    }   

                } );
            } ); 
        });

        socket.on('disconnect',function(){

          delete user_connection_notif[socket.user_id];
          
          console.log('Disconnect ' + socket.id);

          console.log(user_connection_notif);
        });
    });
}