<?php
/*
 *	Mine-Panel Verison 1.0
 */

error_reporting(E_ALL & ~E_NOTICE);
set_time_limit(300); // 5 mins

if (!function_exists('curl_init')) {
	die('Error: PHP Curl extension is not installed');
}

function virtpanel_ConfigOptions() {
	$configarray = array(
		'Account Type' => array('Type' => 'dropdown', 'Options' => 'virtualbox,openvz,website,game'),
		'Resource Plan' => array('Type' => 'text', 'Size' => '50'),
		'IP Addresses' => array('Type' => 'text', 'Size' => '5'),
		'Server Pool' => array('Type' => 'text', 'Size' => '50'),
		'Game Template' => array('Type' => 'text', 'Size' => '50'),
	);
	return $configarray;
}

function virtpanel_CreateAccount($whmcs) {	
	if (empty($whmcs['username'])) {
		$firstname = strtolower($whmcs['clientsdetails']['firstname']);
		$lastname = strtolower($whmcs['clientsdetails']['lastname']);
		$allowedchars = range('a', 'z');
		
		$part = '';
		for ($i=0;$i<strlen($firstname);$i++) {
			if (in_array($firstname[$i], $allowedchars)) {
				$whmcs['username'] .= $firstname[$i];
				break;
			}
		}
		
		$part = '';
		for ($i=0;$i<strlen($lastname);$i++) {
			if (in_array($lastname[$i], $allowedchars)) {
				$whmcs['username'] .= $lastname[$i];
				break;
			}
		}
		$whmcs['username'] .= $whmcs['serviceid'];
	}
	
	$pieces = explode('.', $_SERVER['HTTP_HOST']);	 
	$domain = strtolower($pieces[(count($pieces)-2)].'.'.$pieces[(count($pieces)-1)]);
	$hostname = $whmcs['username'].'.'.$domain;
	
	$array = array(
		'username' => $whmcs['username'],
		'domain' => $hostname,
		);
	$where = array(
		'id' => $whmcs['serviceid'],
		);
	update_query('tblhosting', $array, $where);
	
	$template = '';
	if ($whmcs['configoption1']=='game') {
		$template = $whmcs['configoption5'];
	}
	
	$data = virtpanel_conn($whmcs, '?vm', array(
		'action' => 'create',
		'type' => $whmcs['configoption1'],
		'username' => $whmcs['username'],
		'password' => $whmcs['password'],
		'email' => $whmcs['clientsdetails']['email'],
		'hostname' => $hostname,
		'ip_addresses_from_pool' => $whmcs['configoption3'],
		'server' => '(Automatic)',
		'server_pool' => $whmcs['configoption4'],
		'plan' => $whmcs['configoption2'],
		'template' => $template,
		'create' => 'create',
		'continue' => 'continue',
	));
	if ($data===false) {
		return virtpanel_return_errors($data);
	}
	
	$success_messages = array(
		'template transfer',
		'rebuild',
		'install',
		'successfully updated',
	);
	foreach($success_messages as $success_message) {
		if (stripos($data, $success_message)) {
			virtpanel_get_ip($whmcs);
			return 'success';
		}
	}
	
	//var_dump(htmlentities($data)); exit;
	return 'Unknown Error';
}

function virtpanel_TerminateAccount($whmcs) {
	virtpanel_get_ip($whmcs);
	$data = virtpanel_conn($whmcs, '?vm', array(
		'vm['.urlencode($whmcs['username']).']' => $whmcs['username'],
		'delete' => 'Delete'
	));
	if ($data===false) {
		if ($GLOBALS['virtpanel_module_errors'][0]=='Account does not exist') {
			return 'success';
		}
		return virtpanel_return_errors($data);
	}
	if (stripos($data, 'successfully deleted')) {
		return 'success';
	}
	return 'Unknown Error';
}

function virtpanel_SuspendAccount($whmcs) {
	$data = virtpanel_conn($whmcs, '?vm', array(
		'action' => 'view',
		'vm' => $whmcs['username'],
		'subaction' => 'suspend',
		'full' => 'true',
	));
	if ($data===false) {
		if ($GLOBALS['virtpanel_module_errors'][0]=='Account does not exist') {
			return 'success';
		}
		return virtpanel_return_errors($data);
	}
	if (stripos($data, '(SUSPENDED)')!==false) {
		return 'success';
	}
	return 'Unknown Error';
}

function virtpanel_UnsuspendAccount($whmcs) {
	$data = virtpanel_conn($whmcs, '?vm', array(
		'action' => 'view',
		'vm' => $whmcs['username'],
		'subaction' => 'unsuspend',
		'full' => 'true',
	));
	if ($data===false) {
		return virtpanel_return_errors($data);
	}
	if (stripos($data, '(SUSPENDED)')===false) {
		return 'success';
	}
	return 'Unknown Error';
}

function virtpanel_ChangePassword($whmcs) {
	$data = virtpanel_conn($whmcs, '?vm', array(
		'action' => 'view',
		'vm' => $whmcs['username'],
		'subaction' => 'pass',
		'new' => $whmcs['password'],
		'new2' => $whmcs['password'],
		'update' => 'update',
	));
	if ($data===false) {
		return virtpanel_return_errors($data);
	}
	if (stripos($data, 'successfully updated')) {
		return 'success';
	}
	return 'Unknown Error';
}

function virtpanel_ChangePackage($whmcs) {
	$data = virtpanel_conn($whmcs, '?vm', array(
		'action' => 'view',
		'vm' => $whmcs['username'],
		'subaction' => 'plan',
		'plan' => $whmcs['configoption2'],
		'update' => 'update',
	));
	if ($data===false) {
		return virtpanel_return_errors($data);
	}
	if (stripos($data, 'successfully updated')) {
		return 'success';
	}
	return 'Unknown Error';
}

function virtpanel_ClientArea($whmcs) {
	$auth = virtpanel_conn($whmcs, 'api/login_auth.php', array(
		'type' => 'vm',
		'username' => $whmcs['username'],
	));
	if (!empty($whmcs['serverip'])) {
		$host = $whmcs['serverip'];
	} else {
		$host = $whmcs['serverhostname'];
	}
	return '<p><input type="button" value="&raquo; Click Here to access the Control Panel &laquo;" class="btn" onclick="window.location=\'http'.($whmcs['serversecure']=='on'?'s':'').'://'.$host.'/?login&username='.urlencode($whmcs['username']).'&auth='.urlencode($auth).'\'" /></p>';
}

function virtpanel_AdminCustomButtonArray() {
	$buttonarray = array(
		'Update WHMCS IP Addresses' => 'get_ip',
	);
	return $buttonarray;
}
