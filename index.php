<?php

require_once 'inc/lib.php';

session_start();
// Destroy session on ?logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
// Redirect logged in users to the dashboard
if ($_SESSION['user'])
    header('Location: dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Login - Mine-Panel</title>
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

    <body>

        <div class="container">
            <form class="form-signin" action="dashboard.php" method="post">
                <h2 class="form-signin-heading" align="center">Login</h2>
                <?php
                if ($_GET['error'] == 'badlogin')
                    echo '<p class="alert alert-error">Invalid login details.</p>';
                ?>
                <input type="text" name="user" id="user" class="input-block-level" placeholder="Username">
                <input type="password" name="pass" id="pass" class="input-block-level" placeholder="Password">
                <div>
                <label class="checkbox">
                    <input type="checkbox" value="remember-me"> Remember me
                </label>
                <button class="btn btn-large btn-primary" type="submit">Sign in</button>
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
