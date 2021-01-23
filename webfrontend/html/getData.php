<?php
require_once "loxberry_log.php";

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

// called from other modul?
ob_start();
if($background) {	
	LOGINF("Getting data from getData.php...");
} else {
	LOGSTART("SureFlap HTTP getData.php started");
}

// load config
include_once 'includes/config.php';

// send request
if($token) {
	LOGDEB("Starting request...");
	$ch = curl_init($endpoint."/api/me/start");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
	$result = json_decode(curl_exec($ch),true) or die("Curl Failed");
	LOGDEB("Request received with code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE));
}

// get new token?
if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != "200") {
	LOGWARN("Token needs to be renewed!");
	// getting new token
	include_once 'includes/login.php';
	
	// resend request
	LOGDEB("Restarting request...");
	$ch = curl_init($endpoint."/api/me/start");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
	$result = json_decode(curl_exec($ch),true) or die("Curl Failed");
	LOGDEB("Re-Request received with code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE));
}

// print request moment
print "System@DateTime@".date('d.m.Y H:i:s')."<br>";
print "System@DateTimeLox@".epoch2lox(time())."<br><br>";

// getting household
LOGDEB("Getting households...");
$households = $result['data']['households'];
include 'includes/getHouseholds.php';

// getting devices
LOGDEB("Getting devices...");
$devices = $result['data']['devices'];
include 'includes/getDevices.php';

// Backward compatibility
if(isset($_GET['name'])) {
	$_GET['petname'] = $_GET['name'];
	LOGWARN("Parameter name should no longer be used! Please use petname instead.");
}

// getting pets
LOGDEB("Getting pets...");
$pets = $result['data']['pets'];
include 'includes/getPets.php';

if($background) {
	// do not print data in background
	ob_end_clean();
	LOGINF("Returning from getData.php...");
} else {	
	// Responce to virutal input?
	if($config_http_send == 1) {
		LOGDEB("Starting Response to miniserver...");
		include_once 'includes/sendResponces.php';
	} 
	// print data
	ob_end_flush();
	LOGEND("SureFlap HTTP getData.php stopped");	
}

?>
