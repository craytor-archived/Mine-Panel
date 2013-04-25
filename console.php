<?php
require_once 'inc/lib.php';

session_start();
if (!$_SESSION['user'] || !$user = user_info($_SESSION['user'])) {
    // Not logged in, redirect to login page
    header('Location: .');
    exit('Not Authorized');
}
?>
<!doctype html>
<html>
<head>
	<title>Console | Mine-Panel</title>
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.css">
	<link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/bootstrap-responsive.min.css">
        <link rel="stylesheet" href="<?php echo KT_THEME_DIRECTORY; ?>/css/docs.css">
	<meta name="author" content="">
	<style type="text/css">
            #cmd,#log{background-color:#000;color:#fff;}
            #cmd,#log{box-sizing:border-box;-moz-box-sizing:border-box;width:100%;}
            #cmd::selection,#log::selection{background:rgba(255,255,255,.4);color:#fff;}
            #log{overflow-y:scroll;}
            #cmd{height:30px;}
            form{margin:0;}
	</style>
	<script src="<?php echo KT_THEME_DIRECTORY; ?>/js/jquery-1.7.2.min.js"></script>
	<script src="<?php echo KT_THEME_DIRECTORY; ?>/js/bootstrap.min.js"></script>
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/bootstrap-dropdown.js"></script>
        <script src="<?php echo KT_THEME_DIRECTORY; ?>/js/js/bootstrap-modal.js"></script>
	<script type="text/javascript">
            function refreshLog() {
                updateStatus();
                $.post('ajax.php', {
                    req: 'server_log'
                }, function(data) {
                    if ($('#log').scrollTop() == $('#log')[0].scrollHeight) {
                        $('#log').html(data).scrollTop($('#log')[0].scrollHeight);
                    } else {
                        $('#log').html(data);
                    }
                    window.setTimeout('refreshLog();', 1000);
                });
            }

            function refreshLogOnce() {
                $.post('ajax.php', {
                    req: 'server_log'
                }, function(data) {
                    $('#log').html(data).scrollTop($('#log')[0].scrollHeight);
                });
            }

            function updateStatus() {
                $.post('ajax.php', {
                    req: 'server_running'
                }, function(data) {
                    if (data) {
                        $('#cmd').prop('disabled', false);
                    } else {
                        $('#cmd').prop('disabled', true);
                    }
                }, 'json');
            }

            $(document).ready(function() {

                // Send commands with form onSubmit
                $('#frm-cmd').submit(function() {
                    $.post('ajax.php', {
                        req: 'server_cmd',
                        cmd: $('#cmd').val()
                    }, function() {
                        $('#cmd').val('').prop('disabled', false);
                        refreshLogOnce();
                    });
                    $('#cmd').prop('disabled', true);
                    return false;
                });

                // Fix sizing
                $('#log').css('height', $(window).height() - 380 + 'px');

                // Check if server is running
                updateStatus();

                // Initialize log
                $.post('ajax.php', {
                    req: 'server_log'
                }, function(data) {
                    $('#log').html(data).scrollTop($('#log')[0].scrollHeight);
                    window.setTimeout('refreshLog();', 3000);
                });

                // Keep sizing correct
                $(document).resize(function() {
                    $('#log').css('height', $(window).height() - 190 + 'px');
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
                            <li class="active">
                                <a href="./console.php">Console</a>
                            </li>
                            <li class="">
                                <a href="./files.php">File Manager</a>
                            </li>
                        </ul>
<?php if($user['ram']) { ?>
                        <div class="btn-group" style="float: right;">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                <img src="https://minotar.net/helm/Craytor/60.png" alt="Craytor" align="right" style="height: 20px; width: 20px; float: left; padding-right: 5px;"> Craytor
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
            <div class="row">
                <div class="span12">
                    <h3>Console</h3>
                    <pre id="log" class="well well-small" style="color: white; background-color: #000000; width: 100%; height: 480px; font-weight:bold;"></pre>
                    <form id="frm-cmd">
                        <input type="text" id="cmd" name="cmd" autofocus style="color: white; background-color: #000000; width: 100%; font-weight:bold;" placeholder="Enter a console command here (excluding the /)">
                    </form>
                </div>
            </div>
            <?php
} else
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
    
    
    
    
</body>
</html>