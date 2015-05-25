var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var client_url=casper.cli.get(2).toString();


var auth_url='https://instagram.com/accounts/login/';

casper.start().thenOpen(auth_url,
    function() {
        this.wait(2000, function() {
            this.fillSelectors('form', {
                'input[name="username"]': uname,
                'input[name="password"]':  pass
            }, true);
        });

        this.wait(1000, function() {});
    });

casper.thenOpen(client_url, function() {
    this.wait(2000, function () {
    });
});

casper.run();
