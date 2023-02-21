<?php
require_once "loxberry_log.php";

function isTime($time) {
	if (preg_match("/^([1-2][0-3]|[01]?[1-9]):([0-5]?[0-9])$/", $time))
		return true;
	return false;
}

// check mode
$input_mode = @$_GET['mode'].@$_GET['modeid'];
switch($input_mode) {	
	case "0":
	case "off":
		$input_enable = false;
		break;
	case "1":
	case "on":
		$input_enable = true;
		break;	
	default:
		die("Usage: ".$_SERVER['PHP_SELF']."?mode=[0|off] or ?mode=[1|on]&from=[eg. 18:00]&to=[eg. 06:00]");
}

// check correct time format
if($input_enable) {
	if(isTime(@$_GET['from']) and isTime(@$_GET['to'])) {
		$input_from = date("H:i", strtotime($_GET['from']));
		$input_to   = date("H:i", strtotime($_GET['to']));
	} else {
		die("Usage: ".$_SERVER['PHP_SELF']."?from=[eg. 18:00]&to=[eg. 06:00]");
	}
}

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setCurfew.php started");
if($input_enable) {
	LOGDEB("SetCurfew: ".$input_from."-".$input_to);
	print "SetCurfew: ".$input_from."-".$input_to."<br>";
} else {
	LOGDEB("Disable Curfew");
	print "Disable Curfew<br>";
}

// get new data - no output
$background = "setCurfew";
include_once 'getData.php';

// check if already there
$found = false;
if($input_enable) {
	foreach($device_curfew AS $curfew) {
		if($curfew['enabled'] == true && $curfew['lock_time'] == $input_from && $curfew['unlock_time'] == $input_to) {
			$found = true;
			print "Curfew already set. No change necessary.";
			LOGINF("Curfew already set. No change necessary.");			
			break;
		}
	}
} elseif(empty($device_curfew)){
	$found = true;
	print "Curfew already disabled. No change necessary.";
	LOGINF("Curfew already disabled. No change necessary.");	
}

if($found == false) {
	LOGDEB("Starting request...");
	$json = json_encode(array("curfew" => array( array("enabled" => $input_enable, "lock_time" => $input_from, "unlock_time" => $input_to))));
	$curl = put_curl($endpoint."/api/device/$flap/control", $token, $json);
	LOGDEB("Request received with code: ".$curl['http_code']);

	$found = false;
	foreach($curl['result']['data']['curfew'] AS $curfew) {
		if($curfew['enabled'] == $input_enable) {
			if ($input_enable == false) {
				$found = true;
				break;
			} elseif($curfew['lock_time'] == $input_from && $curfew['unlock_time'] == $input_to) {
				$found = true;
				break;
			}				
		}
	}
	
	if($found) {
		if($input_enable) {
			print "Successfully enabled curfew for \"$flapname\" between ".$input_from." & ".$input_to;
			LOGINF("Successfully enabled curfew for \"$flapname\" between ".$input_from." & ".$input_to);
		} else {
			print "Successfully disabled curfew for \"$flapname\"";
			LOGINF("Successfully disabled curfew for \"$flapname\"");
		}
		
		// Build data to responce
		$devices[$flapindex]['control'] = $curl['result']['data'];				
	} else {	
		print "Enable Curfew Failed!";
		LOGERR("Enable Curfew Failed!");
	}
}


if($config_send) {
	print "<br><br>";
	// Only send changed values
	$_GET['viparam'] = "DateTime;DateTimeLox;DeviceCurfew";
	// Convert value	
	include 'includes/getDevices.php';
	// Responce to virutal input
	LOGDEB("Starting Response to miniserver...");
	include_once 'includes/sendResponces.php';
}

LOGEND("SureFlap HTTP setCurfew.php stopped");/* */
?>