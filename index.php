<!DOCTYPE html>
<?php
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
switch ($lang){
    case "ru":
        include("index_ru.php");
        break;
    default:
        include("index_en.php");
        break;
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="flipclock/flipclock.css">
        <link rel="stylesheet" href="style.css" type="text/css" media="screen,projection" />
        
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <title>Instellar</title>
    </head>
    <body>
        <div class="back"></div>
            
        <div class="phone">
            
            <form method="post" actio="index.php" class="email_form">
                
                <?php
                if(!empty($_POST)){
                    $mail=$_POST['email'];
                    $username=isset($_POST['username']) ? $_POST['username'] : "";
                    if(isset($mail)){
                        if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                            include('checkinemail.php');
                            echo '<p class = "success" >'. $EMAIL_BEEN_SENT .'</p>';
                        }
                        else{
                            echo
                            '<input name="username" class="form-control" placeholder="'.$FORM_NAME.'">' .
                            '<style class="error">'.$EMAIL_ERROR.'</style>' .
                            '<input name="email" class="form-control" placeholder="'.$FORM_EMAIL.'">' .
                            '<button type="submit" class="btn btn-danger">'.$FORM_SUBS.'</button>';
                        }
                            
                    }
                }
                else{
                    echo 
                    '<input name="username" class="form-control" placeholder="'.$FORM_NAME.'">' .
                    '<input name="email" class="form-control" placeholder="'.$FORM_EMAIL.'">' .
                    '<button type="submit" class="btn btn-danger">'.$FORM_SUBS.'</button>';
                }
                ?>
            </form>
            
            <p>
                <?php echo $FORM_TEXT?>
            </p>
        </div>
        
        <div class="text">
            <img src="img/title.png"/>
            <p>
                <?php echo $TEXT_P1?>
            </p>
            <p>
                <?php echo $TEXT_P2?>
            </p>
        </div>



        <div clas="clock-wrapper">


        <div class="your-clock">
            <h1>
                <?php echo $BETA_TEST?>
            </h1>
            <div class="clock"></div>
        </div>
        </div>

        <script src="flipclock/flipclock.min.js"></script>
        <script>
        var clock = $('.clock').FlipClock({
            clockFace: 'DailyCounter',
            autoStart: true,
            countdown: true
        });
        clock.setTime(<?php echo 1430162464-time(); ?>);//   3600*24*14
        clock.start();
        </script>
        <?php
        ?>
    <p><?php $BOTTOM_TEXT ?></p>
    </body>
</html>
