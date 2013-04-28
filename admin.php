<?php
require_once 'inc/lib.php';


session_start();
if (!$_SESSION['user'] && !$user = user_info($_SESSION['user'])) {
    // Not logged in, redirect to login page
    header('Location: .');
    exit('Not Authorized');
} elseif (!$_SESSION['is_admin'] && $user['role'] != 'admin') {
    // Not an admin, redirect to login page
    header('Location: .');
    exit('Not Authorized');
}

// Switch users
if ($_POST['action'] == 'user-switch' && $_POST['user']) {
    $_SESSION['is_admin'] = true;
    $_SESSION['user'] = $_POST['user'];
    header('Location: .');
    exit('Switching Users');
}

// Add new user
if ($_POST['action'] == 'user-add')
    user_add($_POST['user'], $_POST['pass'], $_POST['role'], $_POST['dir'], $_POST['ram'], $_POST['port']);

// Start a server
if ($_POST['action'] == 'server-start') {
    $stu = user_info($_POST['user']);
    if (!server_running($stu['user']))
        server_start($stu['user']);
}

// Kill a server
if ($_POST['action'] == 'server-stop')
    if ($_POST['user'] == 'ALL')
        server_kill_all();
    else
        server_kill($_POST['user']);
?>
<!doctype html>
<html>
    <head>
        <title>Administration | Mine-Panel</title>
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/docs.css">
        <meta name="author" content="">
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/jquery-1.7.2.min.js"></script>
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                window.setTimeout(function() {
                    $('.alert-success,.alert-error').fadeOut();
                }, 3000);
                $('#frm-killall').submit(function() {
                    return confirm('Are you sure you want to KILL EVERY SERVER?\nServers will not save any new data, and all connected players will be disconnected!');
                });
            });
        </script>
    </head>
    <body style="background-image: url('<?php echo KT_THEME_DIRECTORY; ?>/img/squares.png');">
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="brand" href="/" style="float: left; padding-right: 20px; color: #fff; margin-left: 0px;">Mine-Panel</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="">
                                <a href="./dashboard.php">Dashboard</a>
                            </li>
                            <li class="">
                                <a href="./console.php">Console</a>
                            </li>
                            <li class="">
                                <a href="./files.php">File Manager</a>
                            </li>
                            <?php if ($_SESSION['is_admin'] || $user['role'] == 'admin') { ?> <li class="active"> <a href="admin.php">Administration</a> </li><?php } ?>
                        </ul>
                        <div class="btn-group" style="float: right;">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                <img src="https://minotar.net/helm/<?php echo urlencode($_SESSION['user']); ?>/60.png" alt="<?php echo $_SESSION['user']; ?>" align="right" style="height: 20px; width: 20px; float: left; padding-right: 5px;"> <?php echo $_SESSION['user']; ?>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="index.php?logout">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container" style="padding-top: 40px;">
            <h3>Administration</h3>
            <div id="button-side" style="float: right; margin-top: -70px;">
                <?php if ($_POST['action'] == 'user-add') { ?>
                    <p class="alert alert-success pull-right"><i class="icon-ok"></i> User added successfully.</p>
                <?php } elseif ($_POST['action'] == 'server-start') { ?>
                    <p class="alert alert-success pull-right"><i class="icon-ok"></i> Server started.</p>
                <?php } elseif ($_POST['action'] == 'server-stop') { ?>
                    <p class="alert alert-success pull-right"><i class="icon-ok"></i> Server killed.</p>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
            <div class="row-fluid">
                <div class="span8 well">
                    <legend>Running Servers</legend>
                    <pre style="background-color: #000; color: #fff;">Running as user: <?php echo `whoami` . "\n" . `screen -ls`; ?></pre>
                    <form action="admin.php" method="post">
                        <input type="hidden" name="action" value="server-start">
                        <select name="user" style="vertical-align: top;">
                            <optgroup label="Users">
                                <?php
                                $ul = user_list();
                                foreach ($ul as $u)
                                    echo '<option value="' . $u . '">' . $u . '</option>';
                                ?>
                            </optgroup>
                        </select>
                        <button type="submit" class="btn btn-success">Start Server</button>
                    </form>
                    <form action="admin.php" method="post">
                        <input type="hidden" name="action" value="server-stop">
                        <select name="user" style="vertical-align: top;">
                            <option value="ALL">All Servers</option>
                            <optgroup label="Users">
                                <?php
                                $ul = user_list();
                                foreach ($ul as $u)
                                    echo '<option value="' . $u . '">' . $u . '</option>';
                                ?>
                            </optgroup>
                        </select>
                        <button type="submit" class="btn btn-danger">Kill Server</button>
                    </form>
                </div>
                <div class="span4">
                    <div class="well">
                        <form action="admin.php" method="post">
                            <legend>Switch to a User</legend>
                            <input type="hidden" name="action" value="user-switch">
                            <select name="user" style="vertical-align: top;">
                                <?php
                                $ul = user_list();
                                foreach ($ul as $u)
                                    echo '<option value="' . $u . '">' . $u . '</option>';
                                ?>
                            </select>
                            <button type="submit" class="btn btn-danger">Log In</button>
                        </form>
                    </div>

                    <div class="well">
                        <form action="admin.php" method="post">
                            <legend>Remove a User</legend>
                            <input type="hidden" name="action" value="user-switch">
                            <select name="user" style="vertical-align: top;">
                                <?php
                                $ul = user_list();
                                foreach ($ul as $u)
                                    echo '<option value="' . $u . '">' . $u . '</option>';
                                ?>
                            </select>
                            <button type="submit" class="btn btn-danger">Remove</button>
                            <p><b>WARNING:</b> This change is irreversible!</p>
                        </form>
                    </div>
                </div>
                </br>
                </br>
                <div class="span12 well" style="margin-left: 0px;">
                    <form action="admin.php" method="post" autocomplete="off">
                        <input type="hidden" name="action" value="user-add">
                        <legend>Add New User</legend>
                        <div class="span4">
                            <div class="control-group">
                                <label class="control-label" for="user">Username</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-user"></i></span>
                                        <input class="span4" type="text" name="user" id="user" style="width: 100%;">
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="pass">Password</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-lock"></i></span>
                                        <input class="span4" type="password" name="pass" id="pass" style="width: 100%;">
                                    </div>
                                </div>
                                <a href="https://www.random.org/passwords/?num=1&len=8&format=html&rnd=new" target="_blank">Generate a password</a>
                            </div>
                        </div>
                        <div class="span4">
                            <div class="control-group">
                                <label class="control-label" for="dir">Home Directory</label>
                                <div class="controls">
                                    <div class="input-prepend">
                                        <span class="add-on"><i class="icon-folder-open"></i></span>
                                        <input class="span10" type="text" name="dir" id="dir" value="/home/admin/Minecraft/" style="width: 100%;">
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="ram">Server Memory</label>
                                <div class="controls">
                                    <div class="input-append">
                                        <input class="span3" type="number" name="ram" id="ram" value="512" style="width: 150px;">
                                        <span class="add-on">MB</span>
                                    </div>
                                    <span class="text-info">0 MB = No Server</span>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="port">Server Port</label>
                                <div class="controls">
                                    <div class="input-append">
                                        <input class="span3" type="number" name="port" id="port" value="25565" style="width: 150px;">
                                    </div>
                                    <span class="text-info">0 = No Server</span>
                                </div>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="role">User Role</label>
                            <div class="controls">
                                <select name="role" id="role" class="span3">
                                    <option value="user" selected>User</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add User</button>

                    </form>

                </div>
            </div>
        </div>
    </body>
</html>