<?php

require_once 'data/config.php';
require_once 'inc/mclogparse.inc.php';

//////////////////////////
// FILESYSTEM FUNCTIONS //
//////////////////////////
function file_rename($path,$newname,$home) {
	return rename($home.$path,$home.rtrim($path,basename($path)).$newname);
}

function download($path,$home,$force = true) {
	if(is_file($home.$path) && $force) {
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($path).'";');
		header('Content-Transfer-Encoding: binary');
		readfile($home.$path);
	} elseif(is_file($home.$path)) {
		header('Content-type: '.mimetype($home.$path));
		readfile($home.$path);
	} else {
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
		header('Status: 404 Not Found');
		$_SERVER['REDIRECT_STATUS'] = 404;
		if(!$force)
			echo 'The requested file is not available.';
	}
}

function mimetype($filename) {
	$mime_types = array(
		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'php' => 'text/html',
		'css' => 'text/css',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'swf' => 'application/x-shockwave-flash',
		'flv' => 'video/x-flv',
		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
		// archives
		'zip' => 'application/zip',
		'rar' => 'application/x-rar-compressed',
		'exe' => 'application/x-msdownload',
		'msi' => 'application/x-msdownload',
		'cab' => 'application/vnd.ms-cab-compressed',
		// audio/video
		'mp3' => 'audio/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',
		// adobe
		'pdf' => 'application/pdf',
		'psd' => 'image/vnd.adobe.photoshop',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',
		// ms office
		'doc' => 'application/msword',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		// open office
		'odt' => 'application/vnd.oasis.opendocument.text',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	);

	$ext = strtolower(array_pop(explode('.',$filename)));
	if(array_key_exists($ext, $mime_types)) {
		return $mime_types[$ext];
	} elseif(function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);
		return $mimetype;
	} else
		return 'application/octet-stream';
}

// Get a file size using native methods
// This allows file sizes greater than 4GB to work properly on a 32-bit environment
function getsize($file) {
	$size = filesize($file);
	if($size < 0)
		if(!(strtoupper(substr(PHP_OS,0,3))=='WIN'))
			$size = trim(`stat -c%s $file`);
		else {
			$fsobj = new COM('Scripting.FileSystemObject');
			$f = $fsobj->GetFile($file);
			$size = $file->Size;
		}
	return $size;
}

// *************************************** //
// *************************************** //
// ********** READ THE LOG FILE ********** //
// *************************************** //
// *************************************** //
function __file_backread_helper(&$haystack,$needle,$x) { 
    $pos=0;$cnt=0;
    while($cnt < $x && ($pos=strpos($haystack,$needle,$pos))!==false){$pos++;$cnt++;}  
    return $pos==false ? false:substr($haystack,$pos,strlen($haystack));
} 

function file_backread($file,$lines,&$fsize=0){ 
    $f=fopen($file,'r'); 
    if(!$f)return Array(); 
    
    
    $splits=$lines*50; 
    if($splits>10000)$splits=10000; 

    $fsize=filesize($file); 
    $pos=$fsize; 
    
    $buff1=Array(); 
    $cnt=0; 

    while($pos) 
    { 
        $pos=$pos-$splits; 
        
        if($pos<0){ $splits+=$pos; $pos=0;} 

        fseek($f,$pos); 
        $buff=fread($f,$splits); 
        if(!$buff)break; 
        
        $lines -= substr_count($buff, "\n"); 

        if($lines <= 0) { 
            $buff1[] = __file_backread_helper($buff,"\n",abs($lines)+1); 
            break; 
        } 
        $buff1[] = $buff; 
    } 

    return str_replace("\r",'',implode('',array_reverse($buff1))); 
}

function file_download($url,$path) {
	$file = fopen($url,'rb');
	if($file) {
		$newf = fopen($path,'wb');
		if($newf)
			while(!feof($file))
				fwrite($newf,fread($file,1024*8),1024*8);
		else
			return false;
	}
	
	if($file)
		fclose($file);
	else
		return false;
	
	if($newf)
		fclose($newf);
	
	return $path;
}

// Delete a folder and it's contents (stack algorithm, faster than a recursive function)
function rmdirr($dirname) {
	// Sanity check
	if(!file_exists($dirname))
		return false;

	// Simple delete for a file
	if(is_file($dirname) || is_link($dirname))
		return unlink($dirname);

	// Create and iterate stack
	$stack = array($dirname);
	while($entry = array_pop($stack)) {
		// Watch for symlinks
		if(is_link($entry)) {
			unlink($entry);
			continue;
		}

		// Attempt to remove the directory
		if(@rmdir($entry))
		continue;

		// Otherwise add it to the stack
		$stack[] = $entry;
		$dh = opendir($entry);
		while(false !== $child = readdir($dh)) {
			// Ignore pointers
			if($child === '.' || $child === '..')
				continue;

			// Unlink files and add directories to stack
			$child = $entry . DIRECTORY_SEPARATOR . $child;
			if(is_dir($child) && !is_link($child))
				$stack[] = $child;
			else
				unlink($child);
		}
		closedir($dh);
		print_r($stack);
	}

	return true;
}

//////////////////////
// SERVER FUNCTIONS //
//////////////////////


function checkIP() {
    // Verify server.properties (Prevent user from modifying port)
		if(is_file($user['home'].'/server.properties')) {
			$prop = file($user['home'].'/server.properties',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			
			// Remove any port setting
			foreach($prop as $i=>$p) {
				if(strpos($p,'server-ip')!==false) {
					unset($prop[$i]);
					continue;
				}
			}
			
			// Add user's port
			$prop[] = 'server-ip='.$user['ip'];
			
			// Save properties file
			file_put_contents($user['home'].'/server.properties',implode("\n",$prop));
			
		} else {
			// File doesn't exist, use template from ./serverbase
			file_put_contents(
				$user['home'].'/server.properties',
				str_replace(
					'%IP%',
					$user['ip'],
					file_get_contents('serverbase/server.properties')
				)
			);
		}
}


function checkPort(){
    if(is_file($user['home'].'/server.properties')) {
			$prop = file($user['home'].'/server.properties',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			
			// Remove any port setting
			foreach($prop as $i=>$p) {
				if(strpos($p,'server-port')!==false) {
					unset($prop[$i]);
					continue;
				}
			}
			
			// Add user's port
			$prop[] = 'server-port='.intval($user['port']);
			
			// Save properties file
			file_put_contents($user['home'].'/server.properties',implode("\n",$prop));
			
		} else {
			// File doesn't exist, use template from ./serverbase
			file_put_contents(
				$user['home'].'/server.properties',
				str_replace(
					'%PORT%',
					intval($user['port']),
					file_get_contents('serverbase/server.properties')
				)
			);
		}
}

function checkSlots() {
        if(is_file($user['home'].'/server.properties')) {
			$prop = file($user['home'].'/server.properties',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			
			// Remove any port setting
			foreach($prop as $i=>$p) {
				if(strpos($p,'max-players')!==false) {
					unset($prop[$i]);
					continue;
				}
			}
			
			// Add user's port
			$prop[] = 'max-players='.intval($user['slots']);
			
			// Save properties file
			file_put_contents($user['home'].'/server.properties',implode("\n",$prop));
			
		} else {
			// File doesn't exist, use template from ./serverbase
			file_put_contents(
				$user['home'].'/server.properties',
				str_replace(
					'%SLOTS%',
					intval($user['port']),
					file_get_contents('serverbase/server.properties')
				)
			);
		}
}





// Start a server with a given username
function server_start($name) {

	// Get user details
	$user = user_info($name);
	
	// Make sure server isn't already running
	if(server_running($user['user']))
		return false;
	
	// Check that server has a .jar
	if(is_file($user['home'].'/craftbukkit.jar')) {

		//checkIP();

            // Verify server.properties (Prevent user from modifying port)
		if(is_file($user['home'].'/server.properties')) {
			$prop = file($user['home'].'/server.properties',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			
			// Remove any port setting
			foreach($prop as $i=>$p) {
				if(strpos($p,'server-ip')!==false) {
					unset($prop[$i]);
					continue;
				}
			}
			
			// Add user's port
			$prop[] = 'server-ip='.$user['ip'];
			
			// Save properties file
			file_put_contents($user['home'].'/server.properties',implode("\n",$prop));
			
		} else {
			// File doesn't exist, use template from ./serverbase
			file_put_contents(
				$user['home'].'/server.properties',
				str_replace(
					'%IP%',
					intval($user['ip']),
					file_get_contents('serverbase/server.properties')
				)
			);
		}



        //checkPort();

        if(is_file($user['home'].'/server.properties')) {
			$prop = file($user['home'].'/server.properties',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			
			// Remove any port setting
			foreach($prop as $i=>$p) {
				if(strpos($p,'server-port')!==false) {
					unset($prop[$i]);
					continue;
				}
			}
			
			// Add user's port
			$prop[] = 'server-port='.intval($user['port']);
			
			// Save properties file
			file_put_contents($user['home'].'/server.properties',implode("\n",$prop));
			
		} else {
			// File doesn't exist, use template from ./serverbase
			file_put_contents(
				$user['home'].'/server.properties',
				str_replace(
					'%PORT%',
					intval($user['port']),
					file_get_contents('serverbase/server.properties')
				)
			);
		}



        //checkSlots();
		
		
         if(is_file($user['home'].'/server.properties')) {
			$prop = file($user['home'].'/server.properties',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			
			// Remove any port setting
			foreach($prop as $i=>$p) {
				if(strpos($p,'max-players')!==false) {
					unset($prop[$i]);
					continue;
				}
			}
			
			// Add user's port
			$prop[] = 'max-players='.intval($user['slots']);
			
			// Save properties file
			file_put_contents($user['home'].'/server.properties',implode("\n",$prop));
			
		} else {
			// File doesn't exist, use template from ./serverbase
			file_put_contents(
				$user['home'].'/server.properties',
				str_replace(
					'%SLOTS%',
					intval($user['port']),
					file_get_contents('serverbase/server.properties')
				)
			);
		}



		// Launch server process in a detached GNU Screen
		shell_exec(
			'cd '.escapeshellarg($user['home']).'; '. // Change to server directory
			sprintf(
				KT_SCREEN_CMD_START, // Base command
				escapeshellarg(KT_SCREEN_NAME_PREFIX.$user['user']), // Screen Name
				$user['ram'], // Startup RAM
				$user['ram']  // Maximum RAM
			)
		);
		
	}
}

// Pass a command to a running server
function server_cmd($name,$cmd) {
	shell_exec(
		sprintf(
			KT_SCREEN_CMD_EXEC, // Base command
			KT_SCREEN_NAME_PREFIX.$name, // Screen Name
			str_replace(array('\\','"'),array('\\\\','\\"'),(get_magic_quotes_gpc() ? stripslashes($cmd) : $cmd)) // Server command
		)
	);
}

// Safely shut down a server
function server_stop($name) {
	shell_exec(
		
		// "stop" command
		sprintf(
			KT_SCREEN_CMD_EXEC, // Base command
			KT_SCREEN_NAME_PREFIX.$name, // Screen Name
			'stop' // Server command
		).';'.
		
		// wait 5 seconds
		'sleep 5;'.
		
		// kill process
		sprintf(
			KT_SCREEN_CMD_KILL, // Base command
			escapeshellarg(KT_SCREEN_NAME_PREFIX.$name) // Screen Name
		)
	);
}

// Immediately kill a server with a given username (does not save anything!)
function server_kill($name) {
	$user = user_info($name);
	shell_exec(
		sprintf(
			KT_SCREEN_CMD_KILL, // Base command
			escapeshellarg(KT_SCREEN_NAME_PREFIX.$user['user']) // Screen Name
		)
	);
}

// Kill ALL RUNNING GNU-SCREENS (under the web server user)
function server_kill_all() {
	shell_exec(KT_SCREEN_CMD_KILLALL);
}

// Check if a server is running
function server_running($name) {
	return (bool) strpos(`screen -ls`,KT_SCREEN_NAME_PREFIX.$name);
}

////////////////////
// USER FUNCTIONS //
////////////////////

// Add a new user
function user_add($user,$pass,$role,$home,$ram,$port,$ip) {
        // Details for query
	    $PDOUser = KT_DATABASE_USERNAME; //Username for MySQL
        $PDOPass = KT_DATABASE_PASSWORD; //Password for MySQL
        $dbh = new PDO('mysql:host=localhost;dbname=minepanel', $PDOUser, $PDOPass);
        $dbh->exec("set names utf8");
        $stmt = $dbh->prepare("INSERT INTO users (ID, email, user, pass, role, home, ram, ip, port, fName, lName, slots) VALUES (:ID, :email, :user, :pass, :role, :home, :ram, :ip, :port, :fName, :lName, :slots)");
        
        // Pass items in to get cleaned
        $id = null;
        $userEnter = clean_alphanum($user);
        $passEnter = bcrypt($pass);
        $homeEnter = $home;
        $ramEnter = intval($ram);
        $portEnter = $port;
        $notApplicable = "No data yet";
        $ipEnter = $ip;
        $slots = "10";

        
        // Clean items up, get them clean of any hazards
        $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
        $stmt->bindParam(':email', $notApplicable); // This settings will come later
        $stmt->bindParam(':user', $userEnter);
        $stmt->bindParam(':pass', $passEnter);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':home', $homeEnter);
        $stmt->bindParam(':ram', $ramEnter);
        $stmt->bindParam(':ip', $ipEnter); // This settings will come later
        $stmt->bindParam(':port', $portEnter);
        $stmt->bindParam(':fName', $notApplicable); // This settings will come later
        $stmt->bindParam(':lName', $notApplicable); // This settings will come later
        $stmt->bindParam(':slots', $slots); // This settings will come later
        //
        
        
        
        mkdir($home, 0777);
        
        shell_exec(
		sprintf(
			"useradd -d ".$homeEnter." ".$user." ", // Base command
			"chown ".$user." ".$homeEnter." "
		)
	);
        
        // Insert the data into database
        $stmt->execute();
        
}

// Delete a user
function user_delete($user) {
        // Details for query
	    $PDOUser = KT_DATABASE_USERNAME; //Username for MySQL
        $PDOPass = KT_DATABASE_PASSWORD; //Password for MySQL
        $dbh = new PDO('mysql:host=localhost;dbname=minepanel', $PDOUser, $PDOPass);
        $dbh->exec("set names utf8");
        $stmt = $dbh->prepare("DELETE FROM users WHERE user = :user");
        
        // Pass items in to get cleaned
        $id = null;
        $userEnter = $user;

        $stmt->bindParam(':user', $userEnter);
        //
        
        $stmt->execute();
        
}

// Get user data
function user_info($user) {
        $PDOUser = KT_DATABASE_USERNAME; //Username for MySQL
        $PDOPass = KT_DATABASE_PASSWORD; //Password for MySQL
        $dbh = new PDO('mysql:host=localhost;dbname=minepanel', $PDOUser, $PDOPass);
        $dbh->exec("set names utf8");
        $stmt = $dbh->prepare("SELECT 1 FROM users WHERE user = :user");//you're prepareing this query twice. Why?
        
        // Pass items in to get cleaned
        $stmt->bindParam(':user', $user);
        
        // Check it
        if ($stmt->execute() > 0) {
                $data = $dbh->prepare("SELECT * FROM users WHERE user = :user");
                $data->bindParam(':user', $user);
                $data->execute();
             
                $results = $data->Fetch( PDO::FETCH_ASSOC );//you need another code editor...
                
                return $results;//run that, please   cra[, sorry   run that now
                //run this script, pelase    run that   do we need that exit; gone?
                //right, so i think i finally understand what you're trying to do. But you don't need to use JSON with the database version, as it's already returned as an array.
        } else {
                return false;
        }
    
    
    
    
    
	/*if(is_file('data/users/'.strtolower(clean_alphanum($user)))) {
		echo 'returning: '. json_decode(file_get_contents('data/users/'.strtolower(clean_alphanum($user))),true);
        } else {
		return false;
        }*/
}

// List users
function user_list() {
        $PDOUser = KT_DATABASE_USERNAME; //Username for MySQL
        $PDOPass = KT_DATABASE_PASSWORD; //Password for MySQL
        $dbh = new PDO('mysql:host=localhost;dbname=minepanel', $PDOUser, $PDOPass);
        $dbh->exec("set names utf8");
        $stmt = $dbh->prepare("SELECT * FROM users");
        $stmt->execute();
        
        // Check it
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN, 2);
        return $users;
    
    
    
    
	//$h = opendir('data/users/');
	//$users = array();
	//while(($f = readdir($h)) !== false)
	//	if($f != '.' && $f != '..')
	//		$users[] = $f;
	//closedir($h);
	//return $users;
}

/////////////////////////
// FILTERING FUNCTIONS //
/////////////////////////

// Remove non-alphanumeric characters from a string
function clean_alphanum($s) {
	return preg_replace('/([^A-Za-z0-9])/','',$s);
}

// Remove non-alphabetic characters from a string
function clean_alpha($s) {
	return preg_replace('/([^A-Za-z0-9])/','',$s);
}

// Remove non-numeric characters from a string
function clean_digit($s) {
	return preg_replace('/([^0-9])/','',$s);
}

// Verify email address syntax
function check_email($email) {
	return filter_var($email,FILTER_VALIDATE_EMAIL);
}


////////////////////////////
// CRYPTOGRAPHY FUNCTIONS //
////////////////////////////

// Generate a Base-64 salt string
function base64_salt($len = 22) {
	$characterList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/';
	$salt = '';
	for($i=0;$i<$len;$i++)
		$salt.= $characterList{mt_rand(0,(strlen($characterList)-1))};
	return $salt;
}

// Securely encrypt a password
function bcrypt($str) {
	$salt = strtr(base64_salt(22),'+','.');
	$work = 13;
	$salt = sprintf('$2y$%s$%s',$work,$salt);
	$hash = crypt($str,$salt);
	if(strlen($hash)>13)
		return $hash;
	else
		return false;
}

// Verify a bcrypt-encyrpted string
function bcrypt_verify($str,$hash) {
	return (crypt($str,$hash) === $hash);
}

?>
