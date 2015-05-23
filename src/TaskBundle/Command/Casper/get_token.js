var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var client_id=casper.cli.get(2).toString();


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
    var r = "";
    if(client_id == 1){
         r = this.evaluate(function(){
            return $('#client_easytogo').find('form').find('input').val();
        });
    }
    else if (client_id == 2) {
        r = this.evaluate(function(){
           return $('#client_extragram').find('form').find('input').val();
        });
    }
    else if (client_id == 3) {
        r = this.evaluate(function(){
            return $('#client_ficonosquare').find('form').find('input').val();
        })
    }
    this.echo(r);
});

casper.run();
