var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var id=casper.cli.get(2).toString();

var casper = require('casper').create();
var auth_url='https://instagram.com/oauth/authorize/?client_id=6e336200a7f446a78b125602b90989cc&response_type=code&redirect_uri=http://instastellar.su/get_token&account_id=' + id + '&scope=likes+comments+relationships';

casper.start().thenOpen(auth_url, function() {
    this.wait(2000, function() {
        this.fillSelectors('form', {
               'input[name="username"]':  uname,
               'input[name="password"]':  pass
        }, true);
     });

     this.wait(1000, function() {});
});

casper.then(function() {
    this.click('.button-green');
});

casper.run();
