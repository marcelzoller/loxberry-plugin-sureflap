<?php
require_once "loxberry_log.php";

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

if(empty($background)) {
	// send output to buffer
	ob_start();
}
	
// print request moment
print "System@DateTime@".date('d.m.Y H:i:s')."<br>";
print "System@DateTimeLox@".epoch2lox(time())."<br>";
print "System@DateTimeUnix@".time()."<br><br>";

// called from other modul?
if(empty($background)) {	
	LOGSTART("SureFlap HTTP getData.php started");
} else {
	// send output to buffer
	ob_start();
	LOGINF("Getting data from getData.php...");
}

// check last run - only allow 10 seconds interval
include_once 'includes/checkUpdate.php';

// load config
include_once 'includes/config.php';
include_once 'includes/curl.php';

// send request
if(isset($token)) {
	LOGDEB("Starting request...");
	$curl = get_curl($endpoint."/api/me/start", $token);
	LOGDEB("Request received with code: ".$curl['http_code']);
}

// get new token?
if(!isset($token) or $curl['http_code'] != "200") {
	LOGWARN("Token needs to be renewed!");
	// getting new token
	include_once 'includes/login.php';
	
	// resend request
	LOGDEB("Restarting request...");
	$curl = get_curl($endpoint."/api/me/start", $token);
	LOGDEB("Re-Request received with code: ".$curl['http_code']);
}

if($curl['code_ok']) {
	// getting household
	LOGDEB("Getting households...");
	$households = $curl['result']['data']['households'];
	include 'includes/getHouseholds.php';

	// getting devices
	LOGDEB("Getting devices...");
	$devices = $curl['result']['data']['devices'];
	include 'includes/getDevices.php';

	// Backward compatibility
	if(isset($_GET['name'])) {
		$_GET['petname'] = $_GET['name'];
		LOGWARN("Parameter name should no longer be used! Please use petname instead.");
	}

	// getting pets
	LOGDEB("Getting pets...");
	$pets = $curl['result']['data']['pets'];
	// check location, if empty try to use timeline
	include 'includes/checkLocation.php';
	// now get the pets data
	include 'includes/getPets.php';
	// send data?
	if(empty($background)) $send_data = true;	
}

if(empty($background)) {
	// Responce to virutal input?
	if($config_send and $send_data) {
		LOGDEB("Starting Response to miniserver...");
		include_once 'includes/sendResponces.php';
	} 
	// print data from buffer
	ob_end_flush();	

	LOGEND("SureFlap HTTP getData.php stopped");	
} else {
	// do not print data in background
	LOGINF("Returning from getData.php...");
	// clear output
	ob_end_clean();
}

?>
