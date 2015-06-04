var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var id=casper.cli.get(2).toString();

var auth_url='https://instagram.com/oauth/authorize/?client_id=6e336200a7f446a78b125602b90989cc&response_type=code&scope=likes+comments+relationships&redirect_uri=http://instastellar.su/get_token?account_id=' + id;
var auth_url2='https://instagram.com/oauth/authorize/?client_id=c02d1c473e53485d946a1c44d3daf8d2&redirect_uri=http://extragr.am/sessions/callback&response_type=code&scope=comments+likes+relationships';
var auth_url3='https://instagram.com/oauth/authorize/?client_id=e77306665eb54866ae0a8185c4028604&redirect_uri=http://stapico.ru/accounts/auth/complete&response_type=code&scope=likes+comments+relationships';
var auth_url4='https://instagram.com/oauth/authorize/?client_id=2b5a0c10371c4784935b03e5619e94ca&redirect_uri=http://collec.to/login&response_type=code&scope=likes+comments+relationships&display=touch';
var auth_url5='https://instagram.com/oauth/authorize?response_type=code&client_id=0be8de7ce4924f69ae3f9b4c8c35acd6&redirect_uri=http%3A%2F%2Fwww.latergram.me%2Fusers%2Fauth%2Finstagram%2Fcallback&state=5eb7d79a7e76eb504d9f1cb82e79d8146e9ee8120d52ba41&scope=relationships+comments+likes';

casper.start().thenOpen(auth_url, function() {
    this.wait(2000, function() {
        this.fillSelectors('form', {
               'input[name="username"]':  uname,
               'input[name="password"]':  pass
        }, true);
     });

     this.wait(1000, function() { casper.echo(1);});
});

casper.then(function() {
    try{
        this.click('.button-green');
    }catch(e){}
});

casper.thenOpen(auth_url2, function() {
    this.wait(1000, function() {});
});

casper.then(function() {
    try{
        this.click('.button-green');
    }catch(e){}
});

casper.thenOpen(auth_url3, function() {
    this.wait(1000, function() {});
});

casper.then(function() {
    try{
        this.click('.button-green');
    }catch(e){}
});

casper.thenOpen(auth_url4, function() {
    this.wait(1000, function() {});
});

casper.then(function() {
    try{
        this.click('.button-green');
    }catch(e){}
});


casper.thenOpen(auth_url5, function() {
    this.wait(1000, function() {});
});

casper.then(function() {
    try{
        this.click('.button-green');
    }catch(e){}
});

casper.run();
