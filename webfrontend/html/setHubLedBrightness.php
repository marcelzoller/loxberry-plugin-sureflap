<?php
require_once "loxberry_log.php";

// check inputs
$led_mode = $_GET['mode'].$_GET['modeid'];
switch($led_mode) {
	case "0":
	case "off":
		$led = 0;
		$led_str = "off";
		break;	
	case "1":
	case "bright":
		$led = 1;
		$led_str = "bright";
		break;
	case "4":
	case "dim":
		$led = 4;
		$led_str = "dim";
		break;
	default:
		die("Usage: ".$_SERVER['PHP_SELF']."?modeid=[0|1|4] or ?mode=[off|bright|dim]<br>");			
}

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setHubLedBrightness.php started");
LOGDEB("SetHubLedMode:".$led_str);

// get new data - no output
$background = true;
include 'getData.php';

if($device_led_id == $led) {
	print "LED mode on \"$hubname\" is \"$led_str\". No change necessary.<br><br>";
	LOGINF("LED mode on \"$hubname\" is \"$led_str\". No change necessary.");
} else {
	LOGDEB("Starting request...");
	$json = json_encode(array("led_mode" => $led));
	$curl = put_curl($endpoint."/api/device/$hub/control", $token, $json);
	LOGDEB("Request received with code: ".$curl['http_code']);

	if($curl['result']['data']['led_mode'] == $led) {
		print "Successfully set LED mode for \"$hubname\" to \"$led_str\"<br><br>";
		LOGINF("Successfully set LED mode for \"$hubname\" to \"$led_str\"");

		// Build data to responce
		$devices = array(array("id" => $hub, "name" => $hubname, "product_id" => 1, "control" => $curl['result']['data']));		
	} else {
		print "LED Brightness Change Failed!<br>";
		LOGERR("LED Brightness Change Failed!");
	}	
}

if($config_http_send == 1) {	
	// Only send changed values
	$_GET['viparam'] = "DeviceLedMode;DeviceLedModeDesc";
	// Convert value
	include 'includes/getDevices.php';
	// Responce to virutal input
	LOGDEB("Starting Response to miniserver...");	
	include_once 'includes/sendResponces.php';
}	

LOGEND("SureFlap HTTP setHubLedBrightness.php stopped");
?>
