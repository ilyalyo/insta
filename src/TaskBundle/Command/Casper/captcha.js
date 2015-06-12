var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var id=casper.cli.get(2).toString();

var url='https://instagram.com/accounts/login';

casper.start().thenOpen(url, function() {
    this.wait(2000, function() {
        this.fillSelectors('form', {
            'input[name="username"]':  uname,
            'input[name="password"]':  pass
        }, true);
    });

    this.wait(1000, function() { casper.echo('');});
});

casper.then(function() {
    if(this.exists('#recaptcha_challenge_image')){
        this.captureSelector('/var/www/instastellar/tasks/' + id + 'captcha.png', '#recaptcha_challenge_image');
        casper.echo('/var/www/instastellar/tasks/' + id + 'captcha.png');
    }
    this.wait(60000, function() { casper.echo('');});
});

/*
casper.then(function() {
    try{
        this.click('.button-green');
    }catch(e){}
});
*/
casper.run();
