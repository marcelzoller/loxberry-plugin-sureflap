<?php
require_once "Config/Lite.php";
require_once "loxberry_system.php";
require_once "loxberry_log.php";

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setHubLedBrightness.php started");


switch($_GET['modeid']) {
		case "2":
			$led = 1;
			$ledid =2;
			LOGDEB("SetHubLedMode: bright");
			break;
		case "3":
			$led = 4;
			$ledid =3;
			LOGDEB("SetHubLedMode: dim");
			break;
		case "1":
			$led = 0;
			$ledid =1;
			LOGDEB("SetHubLedMode: off");
			break;
	}

if(empty($_GET['modeid'])){
	switch($_GET['mode']) {
		case "bright":
			$led = 1;
			$ledid =2;
			LOGDEB("SetHubLedMode: bright");
			break;
		case "dim":
			$led = 4;
			$ledid =3;
			LOGDEB("SetHubLedMode: dim");
			break;
		case "off":
			$led = 0;
			$ledid =1;
			LOGDEB("SetHubLedMode: off");
			break;
		default:
			//die("Usage: php ".$_SERVER['PHP_SELF']." [bright|dim|off]\n");
			die("Usage: ".$_SERVER['PHP_SELF']."?mode=[bright|dim|off]\n");
	}
}

include_once 'getDevices.php';

$json = json_encode(array("led_mode" => $led));
$ch = curl_init($endpoint."/api/device/$hub/control");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json),"Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed\n");

if($result['data']['led_mode']==$led) {
	//print "Successfully Set $hubname LED Brightness!\n";
	print "SetHubLedMode@".$ledid;
	LOGDEB("SetHubLedMode: ".$ledid);
} else {
	die("LED Brightness Change Failed!\n");
}

LOGEND("SureFlap HTTP setHubLedBrightness.php stopped");
?>
