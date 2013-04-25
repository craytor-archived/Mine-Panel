<?php
require_once 'inc/lib.php';

session_start();
if (!$_SESSION['user'] || !$user = user_info($_SESSION['user'])) {
    // Not logged in, redirect to login page
    header('Location: .');
    exit('Not Authorized');
}

if (!$_REQUEST['file']) {
    // Not file specified, return to file list
    header('Location: files.php');
    exit('No file specified');
}

// Save file if edited
if ($_POST['text']) {
    $file = $user['home'] . $_POST['file'];
    $text = $_POST['text'];
    if (get_magic_quotes_gpc())
        $text = stripslashes($text);
    $saved = file_put_contents($file, $text);
}

// Determine current directory
$dir = rtrim($_REQUEST['file'], basename($_REQUEST['file']));
$dir = rtrim($dir, '/');
?><!doctype html>
<html>
    <head>
        <title>Edit File | Kiwitree Panel</title>
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/docs.css">
        <meta name="author" content="">
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/jquery-1.7.2.min.js"></script>
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            var edited = false;
            $(document).ready(function() {
                $('textarea').css('height', $(window).height() - 250 + 'px')
                        .on('change', function() {
                    window.edited = true;
                });
                $('#cancel').click(function() {
                    if (window.edited)
                        return confirm('Are you sure you want to cancel editing?\nAll changes will be lost.')
                    else
                        return true;
                });
                $('#reload').click(function() {
                    if (window.edited)
                        return confirm('Are you sure you want to reload the file?\nAll changes will be lost.')
                    else
                        return true;
                });
                window.setTimeout(function() {
                    $('.alert').fadeOut();
                }, 4000);
            });
            $(document).resize(function() {
                $('textarea').css('height', $(window).height() - 250 + 'px');
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
                            <li class="active">
                                <a href="./console.php">Console</a>
                            </li>
                            <li class="">
                                <a href="./files.php">File Manager</a>
                            </li>
                            <?php if ($_SESSION['is_admin'] || $user['role'] == 'admin') { ?> <li class=""> <a href="admin.php">Administration</a> </li><?php } ?>
                        </ul>
                        <?php if ($user['ram']) { ?>
                            <div class="btn-group" style="float: right;">
                                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                    <img src="https://minotar.net/helm/<?php echo urlencode($user['user']); ?>/60.png" alt="<?php echo $user['user']; ?>" align="right" style="height: 20px; width: 20px; float: left; padding-right: 5px;"> <?php echo $user['user']; ?>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="">Dummy link...</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container" style="padding-top: 40px;">
                <form action="edit.php" method="post">
                    <div class="row-fluid">
                        <h3>Editing <?php echo $_REQUEST['file']; ?></h3>
                        <?php if ($_POST['text'] && $saved !== false) { ?>
                            <p class="alert alert-success pull-right"><i class="icon-ok"></i> File was successfully saved.</p>
                        <?php } elseif ($_POST['text']) { ?>
                            <p class="alert alert-error pull-right"><i class="icon-remove"></i> File could not be saved!</p>
                        <?php } elseif ($_GET['action'] == 'reload') { ?>
                            <p class="alert alert-info pull-right">File reloaded.</p>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <input type="hidden" name="file" value="<?php echo $_REQUEST['file']; ?>">
                        <textarea name="text" style="width:100%;box-sizing:border-box;-moz-box-sizing:border-box;font-family:monospace;"><?php echo htmlspecialchars(file_get_contents($user['home'] . $_REQUEST['file'])); ?></textarea>
                        <div class="btn-toolbar" style="text-align: right;">
                            <a href="files.php?dir=<?php echo urlencode($dir); ?>" id="cancel" class="btn">Cancel</a>
                            <a href="edit.php?file=<?php echo urlencode($_REQUEST['file']); ?>&action=reload" id="reload" class="btn btn-danger"><i class="icon-repeat icon-white"></i> Reload File</a>
                            <button type="submit" class="btn btn-primary"><i class="icon-download-alt icon-white"></i> Save File</button>
                        </div>
                    </div>
                </form>
                <?php
            }
            else
                echo '
			<p class="alert alert-info">Your account does not have a server.</p>
			<footer>
                </br>
                <p style="margin-left: 0px;">Copyright T.J.s Web Development & T.J. Youschak</p>
            </footer>
';
            ?>
        </div>
    </body>
</html>