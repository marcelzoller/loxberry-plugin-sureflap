<?php
require_once "Config/Lite.php";
require_once "loxberry_system.php";
require_once "loxberry_log.php";

include_once 'getPet.php';

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP getPetLocation.php started");

$ch = curl_init($endpoint."/api/pet/$pet/position");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed<br>");
if($petname==$_GET['name'] OR empty($_GET['name'])){
	if($result['data']) {
		if($result['data']['where']=="1") {
			// Pet Inside
			print "PetLocation@1<br>";
			print "$petname's Current Location@Inside<br>";
			LOGDEB("$petname's Current Location: Inside");
		} else {
			//Pet Outside
			print "PetLocation@2<br>";
			print "$petname's Current Location@Outside<br>";
			LOGDEB("$petname's Current Location: Outside");
		}
	}
}

print "<br>";
LOGEND("SureFlap HTTP getPetLocation.php stopped");
?>
