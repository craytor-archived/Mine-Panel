<?php

require_once 'inc/lib.php';

session_start();
if ($_SESSION['user']) {
    if (!$user = user_info($_SESSION['user'])) {
        // User does not exist, redirect to login page
        header('Location: .');
        exit('Not Authorized');
    }
} elseif ($_POST['user'] && $_POST['pass']) {
    // Get user data
    $user = user_info($_POST['user']);
//riun that did you copy over lib.php?
    //you made a change? yeah... haha right.. okay doing it now
    //so that's your pass....i'm guessing it's encrypted in some way... yeah..how? i will show you
    $_SESSION['is_admin'] = $user['role'] == 'admin'; //yup
    // Check user exists and password is good
    if ($user === false || !bcrypt_verify($_POST['pass'], $user['pass'])) {
        // Login failure, redirect to login page
        header('Location: ./?error=badlogin');
        //right, so these passwords are different...they're returning two different encryptions... herm...go back into the DB and sotor that new encrypted pass in it ok
        exit('Not Authorized');//run taht
        // DECODE FOR ABOVE      your pass: '.$_POST['pass'].' stored pass: '.$user['pass']. 'and the encrypted version of your pass is..' . bcrypt($_POST['pass'])
    }
    // Current user is valid run that
    $_SESSION['user'] = $user['user'];
} else {
    // Not logged in, redirect to login page
    header('Location: .');
    exit('Not Authorized');
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Update Mine-Panel</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="T.J. Youschak">

        <!-- Le styles -->
        <link href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
            body {
                padding-top: 90px;
                padding-bottom: 40px;
                background-color: #f5f5f5;
            }

            .form-signin {
                max-width: 310px;
                padding: 19px 29px 29px;
                margin: 0 auto 20px;
                background-color: #fff;
                border: 1px solid #e5e5e5;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                border: 1px solid #C7C7C7;
                border-bottom-width: 4px;
            }
            .form-signin .form-signin-heading,
            .form-signin .checkbox {
                margin-bottom: 0; /* dropper: remove margin */
                padding-bottom: 10px;  /* dropper: translate margin to padding */
            }
            .form-signin .form-signin-heading {
                text-align: center;
            }
            .form-signin input[type="text"],
            .form-signin input[type="password"] {
                font-size: 16px;
                height: auto;
                margin-bottom: 15px;
                padding: 7px 9px;
            }

        </style>
        <link href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap-responsive.min.css" rel="stylesheet">

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="assets/js/html5shiv.js"></script>
        <![endif]-->

        <!-- Fav and touch icons -->

        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/jquery-1.7.2.min.js"></script>
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/bootstrap.min.js"></script>
    </head>

    <body style="background-image: url('<?php echo KT_THEME_DIRECTORY; ?>/img/squares.png');">


        <div class="container">
            <h2>We have done some work... <small>and you may want to update Mine-Panel!</small></h2>
            <hr>
            </br>
            <h4>MySQL Information</h4>
            <form>
                    <div class="control-group">
                        <label class="control-label" for="user">MySQL Username</label>
                        <div class="controls">
                                <input class="span4" type="text" name="user" id="user">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="user">MySQL Password</label>
                        <div class="controls">
                                <input class="span4" type="text" name="user" id="user">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="user">MySQL Database</label>
                        <div class="controls">
                                <input class="span4" type="text" name="user" id="user">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="user">MySQL Table</label>
                        <div class="controls">
                                <input class="span4" type="text" name="user" id="user">
                        </div>
                    </div>
                <button type="submit" class="btn btn-primary">Update Mine-Panel!</button>
            </form>

        </div> <!-- /container -->
        <div align="center"><br />Copyright &copy; T.J. Youschak & T.J.'s Web Development. All rights reserved.</div>
        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/assets/js/jquery.js"></script>
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/assets/js/bootstrap.min.js"></script>


    </body>
</html>
