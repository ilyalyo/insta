var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var client=casper.cli.get(2).toString();
var id=casper.cli.get(2).toString();

var auth_url='https://instagram.com/oauth/authorize/?client_id=6e336200a7f446a78b125602b90989cc&response_type=code&scope=likes+comments+relationships&redirect_uri=http://instastellar.su/get_token?account_id=' + id;
var auth_url2='https://instagram.com/oauth/authorize/?client_id=c02d1c473e53485d946a1c44d3daf8d2&redirect_uri=http://extragr.am/sessions/callback&response_type=code&scope=comments+likes+relationships';
var auth_url3='https://instagram.com/oauth/authorize?client_id=d9494686198d4dfeb954979a3e270e5e&redirect_uri=http%3A%2F%2Ficonosquare.com&response_type=code&scope=likes+comments+relationships';
var auth_url4='https://instagram.com/oauth/authorize?client_id=67be0e2777b94bc9bdacf4e2680d77d4&response_type=code&redirect_uri=http%3A%2F%2Flogingram.com%2Findex.php&scope=likes+comments+relationships';
var url="";

switch (client){
    case 'easytogo':
        url = auth_url;
        break;
    case 'extragram':
        url = auth_url2;
        break;
    case 'iconosquare':
        url = auth_url3;
        break;
    default:
        url = auth_url4;
        break;
}

casper.start().thenOpen(url, function() {
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

casper.run();
