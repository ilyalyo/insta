var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var client=casper.cli.get(2).toString();
var id=casper.cli.get(2).toString();

var auth_url='https://instagram.com/oauth/authorize/?client_id=6e336200a7f446a78b125602b90989cc&response_type=code&scope=likes+comments+relationships&redirect_uri=http://instastellar.su/get_token?account_id=' + id;
var auth_url2='https://instagram.com/oauth/authorize/?client_id=c02d1c473e53485d946a1c44d3daf8d2&redirect_uri=http://extragr.am/sessions/callback&response_type=code&scope=comments+likes+relationships';
var auth_url3='https://instagram.com/oauth/authorize/?client_id=e77306665eb54866ae0a8185c4028604&redirect_uri=http://stapico.ru/accounts/auth/complete&response_type=code&scope=likes+comments+relationships';
var auth_url4='https://instagram.com/oauth/authorize/?client_id=2b5a0c10371c4784935b03e5619e94ca&redirect_uri=http://collec.to/login&response_type=code&scope=likes+comments+relationships&display=touch';
var auth_url5='https://instagram.com/oauth/authorize?client_id=6976c26a83f44047b339578982f7eb30&redirect_uri=http%3A%2F%2Fsocialhammer.com%2Fajax.php%3Fdo%3Dinstagram_callback%26accsID%3D22725%26apiID%3D2%26groupID%3D-1&scope=basic+comments+likes+relationships&response_type=code';

var url="";

switch (client){
    case 'easytogo':
        url = auth_url;
        break;
    case 'extragram':
        url = auth_url2;
        break;
    case 'stapico':
        url = auth_url3;
        break;
    case 'collecto':
        url = auth_url4;
        break;
    default:
        url = auth_url5;
        break;
}

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
    if(this.exists('#recaptcha_challenge_image'))
        this.echo('CAPTCHA');
});

casper.then(function() {
    try{
        this.click('.button-green');
    }catch(e){}
});

casper.run();
