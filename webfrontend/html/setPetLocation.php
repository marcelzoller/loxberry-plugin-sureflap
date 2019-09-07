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

LOGSTART("SureFlap HTTP setPedLocation.php started");


if(empty($_GET['location'])){
	switch($_GET['mode'] ) {
		case "1":
			LOGDEB("setPetLocation: in");
			print "SetPetLocation@".$_GET['location']."<br>";
			break;
		case "2":
			LOGDEB("setPetLocation: out");
			print "SetPetLocation@".$_GET['location']."<br>";
			break;;
		default:
			die("Usage: ".$_SERVER['PHP_SELF']."?location=[1=in|2=out]\n");
	}
}



include_once 'getPet.php';

$json = json_encode(array("where" => $_GET['location'], "since" => date("Y-m-d H:i")));	
$ch = curl_init($endpoint."/api/pet/$pet/position");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json),"Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed\n");

if($result['data']['where']==$_GET['location']) {
	//print "Successfully Set Location\n";
	print "SetPetLocation@".$_GET['location'];
	LOGDEB("SetPetLocation: ".$_GET['location']);
} else {
	die("Set Location Failed\n");
}
LOGEND("SureFlap HTTP setPedLocations.php stopped");
?>

