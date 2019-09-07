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

LOGSTART("SureFlap HTTP setLockMode.php started");

switch($_GET['modeid'] ) {
	case "3":
		$lock = 2;
		LOGDEB("LockMode: in");
		break;
	case "2":
		$lock = 1;
		LOGDEB("LockMode: out");
		break;
	case "4":
		$lock = 3;
		LOGDEB("LockMode: both");
		break;
	case "1":
		$lock = 0;
		LOGDEB("LockMode: none");
		break;
}
if(empty($_GET['modeid'])){
	switch($_GET['mode'] ) {
		case "in":
			$lock = 2;
			LOGDEB("LockMode: in");
			break;
		case "out":
			$lock = 1;
			LOGDEB("LockMode: out");
			break;
		case "both":
			$lock = 3;
			LOGDEB("LockMode: both");
			break;
		case "none":
			$lock = 0;
			LOGDEB("LockMode: none");
			break;
		default:
			die("Usage: ".$_SERVER['PHP_SELF']."?mode=[in|out|both|none]\n");
	}
}

include_once 'getDevices.php';

$json = json_encode(array("locking" => "$lock"));
$ch = curl_init($endpoint."/api/device/$flap/control");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json),"Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed\n");

if($result['data']['locking']==$lock) {
	//print "Successfully Set \"$flapname\" Lock Mode!\n";
	$lock=$lock+1;
	print "SetLockModeID@".$lock;
} else {
	die("Lock Mode Change Failed!\n");
}
LOGEND("SureFlap HTTP setLockMode.php stopped");
?>
