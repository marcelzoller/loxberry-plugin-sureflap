<?php
require_once "loxberry_log.php";

// check inputs
if(empty($_GET['from']) or empty($_GET['to'])) {
	die("Usage: ".$_SERVER['PHP_SELF']."?from=[eg. 18:00]&to=[eg. 06:00]<br>");
}

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setEnableCurfew.php started");
LOGDEB("SetCurfew:".$_GET['from']."-".$_GET['to']);
print "SetCurfew:".$_GET['from']."-".$_GET['to']."<br>";

// get new data - no output
$background = true;
include_once 'getData.php';

LOGDEB("Starting request...");
$json = json_encode(array("curfew" => array("enabled" => true, "lock_time" => $_GET['from'], "unlock_time" => $_GET['to'])));

$ch = curl_init($endpoint."/api/device/$flap/control");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json),"Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed\n");
LOGDEB("Request received with code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE));

if($result['data']['curfew']['enabled'] == true) {
	print "Successfully enabled curfew for \"$flapname\" between ".$_GET['from']." & ".$_GET['to']."<br>";
	LOGINF("Successfully enabled curfew for \"$flapname\" between ".$_GET['from']." & ".$_GET['to']);
} else {	
	print "Enable Curfew Failed!";
	LOGERR("Enable Curfew Failed!<br>");
}

LOGEND("SureFlap HTTP setEnableCurfew.php stopped");/* */
?>
