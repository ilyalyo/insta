var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var client=casper.cli.get(2).toString();


var auth_url='https://instagram.com/accounts/login/';
var manage='https://instagram.com/accounts/manage_access/';

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

//1 easytogo
//2 extragram
//3 ficonosquare

casper.thenOpen(manage, function() {
     r = this.evaluate(function(){
        return $('#client_' + client).find('form').find('input').val();
    });
    this.echo(r);
});

casper.run();
