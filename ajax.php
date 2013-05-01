<?php
require_once 'inc/lib.php';

session_start();

if(!$user = user_info($_SESSION['user']))
	exit();

if(is_file($user['home'].'/server.log')) {
switch($_POST['req']) {
	case 'dir':
		// Initial vars
		$dirs = array();
		$files = array();
		
		// Get directory contents
		$h = opendir($user['home'].$_POST['dir']);
		while(false!==($f = readdir($h)))
		if($f != '.' && $f != '..')
			if(is_dir($user['home'].$_POST['dir'].'/'.$f))
				$dirs[] = $f;
			elseif(is_file($user['home'].$_POST['dir'].'/'.$f))
				$files[] = $f;
		closedir($h);
		unset($f);
		
		// Sort data
		sort($dirs);
		sort($files);
		
		// Get file sizes
		$sizes = array();
		foreach($files as $f)
			$sizes[] = filesize($user['home'].$_POST['dir'].'/'.$f);
		
		// Output data
		echo json_encode(array(
			'dirs' => $dirs,
			'files' => $files,
			'sizes' => $sizes
		));
		
		break;
	case 'file_get':
		if(is_file($user['home'].$_POST['file']))
			echo file_get_contents($user['home'].$_POST['file']);
		break;
	case 'file_put':
		if(is_file($user['home'].$_POST['file']))
			file_put_contents($user['home'].$_POST['file'],$_POST['data']);
		break;
	case 'delete':
		foreach($_POST['files'] as $f)
			if(is_file($user['home'].$f))
				unlink($user['home'].$f);
		break;
	case 'rename':
		file_rename($_POST['path'],$_POST['newname'],$user['home']);
		break;
	case 'mkdir':
		mkdir($user['home'].$_POST['dir'].'/'.$_POST['name']);
		break;
	case 'delete-dir':
		rmdirr($user['home'].$_POST['dir']);
		break;
	case 'rename-dir':
		file_rename($_POST['dir'],$_POST['new'],$user['home']);
		break;
	case 'server_start':
		echo server_start($user['user']);
		break;
	case 'server_cmd':
		server_cmd($user['user'],$_POST['cmd']);
		break;
	case 'server_stop':
		server_stop($user['user']);
		break;
	case 'server_kill':
		server_kill($user['user']);
		break;
	case 'server_running':
		echo json_encode(server_running($user['user']));
		break;
	case 'server_log':
		echo mclogparse(file_backread($user['home'].'/server.log',50));
		break;
}
} else {
    echo "Hey! It looks like you just got your server? Anxious to get started? Well it is really easy! You update your server</br> to the version you want, or if you want to see what it is like right out of the box, press that big green \"start\"</br> button over there!";
}

?>