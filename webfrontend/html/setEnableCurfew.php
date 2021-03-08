<?php
require_once "loxberry_log.php";

function isTime($time) {
	if (preg_match("/^([1-2][0-3]|[01]?[1-9]):([0-5]?[0-9])$/", $time))
		return true;
	return false;
}

// check inputs
if(empty($_GET['from']) or empty($_GET['to'])) {
	die("Usage: ".$_SERVER['PHP_SELF']."?from=[eg. 18:00]&to=[eg. 06:00]");
}

// check correct time format
if(isTime($_GET['from']) and isTime($_GET['to'])) {
	$input_from = date("H:i", strtotime($_GET['from']));
	$input_to   = date("H:i", strtotime($_GET['to']));
} else {
	die("Usage: ".$_SERVER['PHP_SELF']."?from=[eg. 18:00]&to=[eg. 06:00]");
}

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setEnableCurfew.php started");
LOGDEB("SetCurfew: ".$input_from."-".$input_to);
print "SetCurfew: ".$input_from."-".$input_to."<br>";

// get new data - no output
$background = true;
include_once 'getData.php';

// check if already there
$found = false;
foreach($device_curfew AS $curfew) {
	if($curfew['enabled'] == true && $curfew['lock_time'] == $input_from && $curfew['unlock_time'] == $input_to) {
		$found = true;
		break;
	}
}

if($found) {
	print "Curfew already set. No change necessary.";
	LOGINF("Curfew already set. No change necessary.");
} else {
	LOGDEB("Starting request...");
	$json = json_encode(array("curfew" => array( array("enabled" => true, "lock_time" => $input_from, "unlock_time" => $input_to))));
	$curl = put_curl($endpoint."/api/device/$flap/control", $token, $json);
	LOGDEB("Request received with code: ".$curl['http_code']);

	$found = false;
	foreach($curl['result']['data']['curfew'] AS $curfew) {
		if($curfew['enabled'] == true && $curfew['lock_time'] == $input_from && $curfew['unlock_time'] == $input_to) {
			$found = true;
			break;
		}
	}
	if($found) {
		print "Successfully enabled curfew for \"$flapname\" between ".$input_from." & ".$input_to."";
		LOGINF("Successfully enabled curfew for \"$flapname\" between ".$input_from." & ".$input_to);
		
		// Build data to responce
		$devices = array(array("id" => $flap, "name" => $flapname, "product_id" => $flaptype, "control" => $curl['result']['data']));			
	} else {	
		print "Enable Curfew Failed!";
		LOGERR("Enable Curfew Failed!");
	}
}


if($config_http_send == 1) {
	print "<br><br>";
	// Only send changed values
	$_GET['viparam'] = "DeviceCurfew";
	// Convert value	
	include 'includes/getDevices.php';
	// Responce to virutal input
	LOGDEB("Starting Response to miniserver...");
	include_once 'includes/sendResponces.php';
}

LOGEND("SureFlap HTTP setEnableCurfew.php stopped");/* */
?>