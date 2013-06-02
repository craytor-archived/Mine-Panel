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
<!doctype html>
<html>
<head>
	<title>Dashboard | Mine-Panel</title>
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
	<script type="text/javascript">
		function updateStatus(once) {
			$.post('ajax.php',{
				req: 'server_running'
			},function(data){
				if(data) {
					$('#lbl-status').text('Running').addClass('label-success').removeClass('label-important');
					$('#btn-srv-start').prop('disabled',true);
					$('#btn-srv-stop,#btn-srv-restart').prop('disabled',false);
					$('#cmd').prop('disabled',false);
				} else {
					$('#lbl-status').text('Stopped').addClass('label-important').removeClass('label-success');
					$('#btn-srv-start').prop('disabled',false);
					$('#btn-srv-stop,#btn-srv-restart').prop('disabled',true);
					$('#cmd').prop('disabled',true);
				}
			},'json');
			if(!once)
				window.setTimeout('updateStatus();',5000);
		}
		function server_start() {
			$.post('ajax.php',{
				req: 'server_start'
			},function(){
				updateStatus(true);
			});
		}
		function server_stop(callback) {
			$.post('ajax.php',{
				req: 'server_stop'
			},function(){
				updateStatus(true);
				if(callback)
					callback();
			});
		}
		function refreshLog() {
			updateStatus();
			$.post('ajax.php',{
				req: 'server_log'
			},function(data){
				if($('#log').scrollTop()==$('#log')[0].scrollHeight) {
					$('#log').html(data).scrollTop($('#log')[0].scrollHeight);
				} else {
					$('#log').html(data);
				}
				window.setTimeout('refreshLog();',3000);
			});
		}
		function refreshLogOnce() {
			$.post('ajax.php',{
				req: 'server_log'
			},function(data){
				$('#log').html(data).scrollTop($('#log')[0].scrollHeight);
			});
		}
		$(document).ready(function(){
			updateStatus();
			$('button.ht').tooltip();
			$('#btn-srv-start').click(function(){
				server_start();
				$(this).prop('disabled',true).tooltip('hide');
			});
			$('#btn-srv-stop').click(function(){
				server_stop();
				$(this).prop('disabled',true).tooltip('hide');
			});
			$('#btn-srv-restart').click(function(){
				server_stop(server_start);
				$('').prop('disabled',true).tooltip('hide');
			});
			
			// Send commands with form onSubmit
			$('#frm-cmd').submit(function(){
				$.post('ajax.php',{
					req: 'server_cmd',
					cmd: $('#cmd').val()
				},function(){
					$('#cmd').val('').prop('disabled',false);
					refreshLogOnce();
				});
				$('#cmd').prop('disabled',true);
				return false;
			});
			
			// Fix sizing
			$('#log').css('height',$(window).height()-200+'px');
			
			// Initialize log
			$.post('ajax.php',{
				req: 'server_log'
			},function(data){
				$('#log').html(data).scrollTop($('#log')[0].scrollHeight);
				window.setTimeout('refreshLog();',3000);
			});
			
			// Keep sizing correct
			$(document).resize(function(){
				$('#log').css('height',$(window).height()-190+'px');
			});
		});
	</script>
</head>
    <body style="background-image: url('<?php echo KT_THEME_DIRECTORY; ?>/img/squares.png');">
        
        <div id="update" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="doUpdate" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h3 id="myModalLabel">Update your server...</h3>
            </div>
            <div class="modal-body">
                <p>You can get a more detailed list of available updates from <a href="./updates.php">here</a>.</p></br>
                <div align="center">
                    <a href="./update.php?s=minecraft_server" class="btn btn-success">Update Minecraft</a></br></br>
                    <a href="./update.php?s=craftbukkit" class="btn btn-success">Update Craftbukkit</a>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>

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
                            <li class="active">
                                <a href="./dashboard.php">Dashboard</a>
                            </li>
                            <li class="">
                                <a href="./console.php">Console</a>
                            </li>
                            <li class="">
                                <a href="./files.php">File Manager</a>
                            </li>
                            <?php if ($_SESSION['is_admin'] || $user['role'] == 'admin') { ?> <li class=""> <a href="admin.php">Administration</a> </li><?php } ?>
                        </ul>
<?php if($user['ram']) { ?>
                        <div class="btn-group" style="float: right;">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                <img src="https://minotar.net/helm/<?php echo urlencode($user['user']); ?>/60.png" alt="<?php echo $user['user']; ?>" align="right" style="height: 20px; width: 20px; float: left; padding-right: 5px;"> <?php echo $user['user']; ?>
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
            <div class="row">
                <div class="span3">
                    <h3>Quick Actions</h3>
                    <div style="text-align:center;">
                        <button id="btn-srv-start" class="btn btn-success" disabled>Start</button> <button id="btn-srv-restart" class="btn btn-warning" disabled>Reboot</button> <button href="#" id="btn-srv-stop" class="btn btn-danger" disabled>Stop</button></br><a href="#update" class="btn btn-info" role="button" class="btn" data-toggle="modal" style="margin-top: 4px;">Update</a><br />
                    </div>
                    <h3>Server Information</h3>
                    <b>IP:</b> <?php echo $user['ip']; ?><br />
                    <b>Port:</b> <?php echo $user['port']; ?><br />
                    <b>RAM:</b> <?php echo $user['ram']; ?> MB<br />
                    <footer>
                        </br>
                        <p>Copyright T.J.s Web Development & T.J. Youschak</p>
                    </footer>
                </div>
                <div class="span9">
                    <h3>Console</h3>
                    <pre id="log" style="color: white; background-color: #000000; width: 100%; height: 380px; font-weight:bold; resize: none; margin-right: 0px;"> </pre>
                    <form id="frm-cmd">
                        <input type="text" id="cmd" name="cmd" maxlength="250" style="color: white; background-color: #000000; width: 100%; font-weight:bold;" placeholder="Enter a console command here (excluding the /)" autofocus>
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