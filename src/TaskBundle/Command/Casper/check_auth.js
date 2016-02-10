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
	casper.debugPage();
    if(this.exists('.-cx-PRIVATE-Navigation__menuItems') || this.exists('#recaptcha_challenge_image'))
        this.echo(1);
    else if (this.getPageContent().search('connected to the internet and try again.') > 0)
        this.echo(2);
    else
        this.echo(0);
});

casper.run();
