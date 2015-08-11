var ws = require("nodejs-websocket")
var fs = require("fs")

var options = {
    secure: true,
    key: fs.readFileSync('/etc/ssl/private/server.key'),
    cert: fs.readFileSync('/etc/ssl/certs/server.pem')
};

var server = ws.createServer(options,function (conn) {
    conn.on("text", function (str) {
        console.log('connect');
        var RuCaptcha = require('./rucaptcha/index.js');
        var solver = new RuCaptcha({
            apiKey: '9b5a393207b21e19b979059cf970639e', //required
            tmpDir: './tasks',                //optional, default is './tmp'
            checkDelay: 1000                    //optional, default is 1000 - interval between captcha checks
        });

        solver.solve(str, function (err, answer) {
            if (!err)
                if(conn.readyState == 1)
                    conn.sendText(answer);
                else
                    console.log('connection was closed')
            console.log(answer);
        });
    })
 }).listen(8001)


