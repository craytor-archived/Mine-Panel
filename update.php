<?php
require_once 'inc/lib.php';

session_start();
if(!$_SESSION['user'] || !$user = user_info($_SESSION['user'])) {
	// Not logged in, redirect to login page
	header('Location: .');
	exit('Not Authorized');
}

function getUpdate($src) {
	global $user;

	// Move aside current .jar
	if(is_file($user['home'].'/craftbukkit.old'))
		unlink($user['home'].'/craftbukkit.old');
	rename($user['home'].'/craftbukkit.jar',$user['home'].'/craftbukkit.old');
	
	// Get new .jar
	switch($src) {
		case 'craftbukkit':
			file_download('http://dl.bukkit.org/downloads/craftbukkit/get/latest/craftbukkit.jar',$user['home'].'/craftbukkit.jar');
			break;
		case 'minecraft_server':
		default:
			file_download('https://s3.amazonaws.com/MinecraftDownload/launcher/minecraft_server.jar',$user['home'].'/craftbukkit.jar');
	}
}

// JavaScript Update
if($_GET['ajax'] && $_POST['req']=='update' && $_POST['source']) {
	getUpdate($_POST['source']);
	exit();
}

// Non-javascript update
if(!$_GET['ajax'] && $_POST['action']=='update' && $_POST['source']) {
	
	// Safely stop server
	server_stop($user['user']);
	
	// Get update
	getUpdate($_POST['source']);
	
	// Start server
	server_start($user['user']);
	
}

?><!doctype html>
<html>
<head>
	<title>Update Server | Mine-Panel</title>
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/bootstrap-responsive.min.css">
	<link rel="stylesheet" href="assets/css/smooth.css" id="smooth-css">
	<link rel="stylesheet" href="assets/css/style.css">
	<meta name="author" content="Alan Hardman (http://imalan.tk)">
	<script src="assets/js/jquery-1.7.2.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function(){
			$('#frm').submit(function() {
				// Show progress bar
				$('.modal-footer .btn').hide();
				$('.modal-footer').append(
					'<div class="progress progress-danger progress-striped active" style="width:200px;margin:20px 0 0;float:right;"><div class="bar" style="width:10%;"></div></div>'
				);
				
				// Safely stop the server
				$.post('ajax.php',{
					req: 'server_stop'
				},function(data) {
					// Increment progress bar
					$('.progress .bar').css('width','20%');
					
					// Download update
					$.post('update.php?ajax=1',{
						req: 'update',
						source: $('#source').val()
					},function(data) {
						// Inrement progress bar
						$('.progress .bar').css('width','80%');
						
						// Start server
						$.post('ajax.php',{
							req: 'server_start'
						},function(data) {
							// Increment progress bar
							$('.progress .bar').css('width','100%');
							
							// Redirect to continue page
							window.setTimeout(function(){
								self.location = 'update.php?c=1';
							},400);
							
						});
						
					});
					
				});
				
				// Prevent submit
				return false;
			});
		});
	</script>

</head>
<body>
	<div class="container-fluid">
<?php if($_POST['action']=='update' || $_GET['c']) { ?>
		<div class="modal">
			<div class="modal-header">
				<h3>Update Server</h3>
			</div>
			<div class="modal-body">
				<p>Your server has been updated.</p>
			</div>
			<div class="modal-footer">
				<a class="btn btn-primary" href="dashboard.php">Continue</a>
			</div>
		</div>
<?php } else { ?>
		<form class="modal" action="update.php" method="post" id="frm">
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="source" id="source" value="<?php echo $_GET['s']; ?>">
			<div class="modal-header">
				<h3>Update Server</h3>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to update your server to the latest version?</p>
				<p>Selected update: <?php echo $_GET['s']; ?></p>
			</div>
			<div class="modal-footer">
				<a class="btn" href="dashboard.php">Cancel</a>
				<button class="btn btn-primary" type="submit">Update</button>
			</div>
		</form>
<?php } ?>
	</div>
</body>
</html>
