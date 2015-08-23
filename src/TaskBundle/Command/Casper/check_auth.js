var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();

var inst_auth_url='https://instagram.com/accounts/login/';

casper.start().thenOpen(inst_auth_url, function() {
    this.wait(2000, function() {
        this.fillSelectors('form', {
            'input[name="username"]':  uname,
            'input[name="password"]':  pass
        }, true);
    });

    this.wait(1000, function() {
        if (this.exists('#errorAlert'))
            casper.echo(0);
        else
            casper.echo(1);
    });
});

casper.run();

