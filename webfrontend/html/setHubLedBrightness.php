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
	print "LED mode on \"$hubname\" is \"$led_str\". No change necessary.<br>";
	LOGINF("LED mode on \"$hubname\" is \"$led_str\". No change necessary.");
} else {
	LOGDEB("Starting request...");
	$json = json_encode(array("led_mode" => $led));
	$ch = curl_init($endpoint."/api/device/$hub/control");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json),"Authorization: Bearer $token"));
	$result = json_decode(curl_exec($ch),true) or die("Curl Failed\n");
	LOGDEB("Request received with code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE));

	if($result['data']['led_mode'] == $led) {
		print "Successfully set LED mode for \"$hubname\" to \"$led_str\"<br><br>";
		LOGINF("Successfully set LED mode for \"$hubname\" to \"$led_str\"");
	} else {
		print "LED Brightness Change Failed!<br>";
		LOGERR("LED Brightness Change Failed!");
	}
	
	if($config_http_send == 1) {
		// Build data to responce
		$devices = array(array("id" => $hub, "name" => $hubname, "product_id" => 1, "control" => $result['data']));
		include 'includes/getDevices.php';
		// Responce to virutal input
		LOGDEB("Starting Response to miniserver...");
		include_once 'includes/sendResponces.php';
	}		
}

LOGEND("SureFlap HTTP setHubLedBrightness.php stopped");
?>
