<?php
require_once "loxberry_log.php";

// better use with radiobutton
if(isset($_GET['modeLox'])) {
	$_GET['mode'] = $_GET['modeLox'] + 1;
}

// check inputs
$lock_mode = $_GET['mode'].$_GET['modeid'];
switch($lock_mode) {	
	case "1":
	case "none":
		$lock = 0;
		$lock_str = "none";
		break;
	case "2":
	case "in":
		$lock = 1;
		$lock_str = "lock in";
		break;		
	case "3":
	case "out":
		$lock = 2;
		$lock_str = "lock out";
		break;
	case "4":
	case "both":
		$lock = 3;
		$lock_str = "lock both";
		break;
	default:
		die("Usage: ".$_SERVER['PHP_SELF']."?modeid=[1|2|3|4] or ?mode=[none|in|out|both|]<br>");
}

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setLockMode.php started");
LOGDEB("LockMode: ".$lock_str);

// get new data - no output
$background = true;
include 'getData.php';

if($device_lock_id == $lock) {
	print "Lockmode on \"$flapname\" is \"$lock_str\". No change necessary.<br><br>";
	LOGINF("Lockmode on \"$flapname\" is \"$lock_str\". No change necessary.");
} else {
	LOGDEB("Starting request...");
	$json = json_encode(array("locking" => "$lock"));
	$curl = put_curl($endpoint."/api/device/$flap/control", $token, $json);
	LOGDEB("Request received with code: ".$curl['http_code']);

	if($curl['result']['data']['locking'] == $lock) {
		print "Successfully set lockmode for \"$flapname\" to \"$lock_str\"<br><br>";
		LOGINF("Successfully set lockmode for \"$flapname\" to \"$lock_str\"");
		
		// Build data to responce
		$devices = array(array("id" => $flap, "name" => $flapname, "product_id" => $flaptype, "control" => $curl['result']['data']));		
	} else {
		print "Lockmode change failed!<br>";
		LOGERR("Lockmode change failed!");
	}
}

if($config_http_send == 1) {
	// Only send changed values
	$_GET['viparam'] = "DeviceLockMode;DeviceLockModeLox;DeviceLockModeDesc";
	// Convert value
	include 'includes/getDevices.php';
	// Responce to virutal input
	LOGDEB("Starting Response to miniserver...");
	include_once 'includes/sendResponces.php';
}

LOGEND("SureFlap HTTP setLockMode.php stopped");
?>
