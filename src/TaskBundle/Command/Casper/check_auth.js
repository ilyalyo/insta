var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();

var auth_url='https://instagram.com/accounts/login';

casper.start().thenOpen(auth_url, function() {
    this.wait(2000, function() {
        this.fillSelectors('form', {
               'input[name="username"]':  uname,
               'input[name="password"]':  pass
        }, true);
     });

     this.wait(5000, function() { });
});

casper.then(function() {
    if(this.exists('.-cx-PRIVATE-Navigation__menuItems'))
        this.echo(1);
    else
        this.echo(0);
});

casper.run();
