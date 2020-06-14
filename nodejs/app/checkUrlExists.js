module.exports = function (http, url, url_str, callback) {
    var options = {
        method: 'HEAD',
        host: url.parse(url_str).host,
        port: 80,
        path: url.parse(url_str).pathname
    };
    var req = http.request(options, function (r) 
    {
        callback(r);
    });

    req.on('error', callback)

    req.end();
}