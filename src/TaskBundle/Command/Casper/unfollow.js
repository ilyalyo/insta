var casper = require('casper').create();

var uname=casper.cli.get(0).toString();
var pass=casper.cli.get(1).toString();
var task_id=casper.cli.get(2).toString();

var fs = require('fs');
var data = fs.read('/var/www/instastellar/tasks/' + task_id);
var arr_data=data.split(",");

var auth_url='https://instagram.com/accounts/login/';

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


 casper.then(function() {
       casper.echo('auth complite');
 });

 casper.then(function(self) {
   for (a = 0; a < arr_data.length; a++)
   {
    (function(self){
        var i = a;
        var rnd_wait = getRandomArbitrary(30, 50);
            casper.thenOpen('https://instagram.com/' + arr_data[i], function(self) {
                casper.wait(rnd_wait,function(self){
                    casper.echo(casper.getTitle());
                if (this.exists('.followButtonFollowing')) {
                    this.echo('found ', 'INFO');
                    this.mouseEvent('click', '.FollowButton');
                    this.wait(1000,function(self){
                        thus.reload(function(){
                            if (this.exists('.followButtonFollow')) {
                                this.echo('un subscribed', 'INFO');
                               casper.thenOpen('http://instastellar.su/tasks/set_result/' + task_id + '/' + arr_data[i], function () {})
                            }
                            else{
                                this.echo('un follow broken', 'ERROR');
                            }
                         });
                    });
                } else {
                    this.echo('not found', 'ERROR');
                }
            });
        });
    })();
    }
 });

casper.run();

function getRandomArbitrary(min, max) {
    return Math.random() * (max - min) + min;
}