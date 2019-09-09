<?php
require_once "Config/Lite.php";
require_once "loxberry_system.php";
require_once "loxberry_log.php";

include_once 'getHousehold.php';

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP getPet.php started");
 
$ch = curl_init($endpoint."/api/household/$household/pet");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed\n");
if($result['data'][0]['name']==$_GET['name'] OR empty($_GET['name'])){
	if($result['data']) {
		print "PetID@".$result['data'][0]['id']."<br>";
		LOGDEB("PetID@".$result['data'][0]['id']);
		$pet = $result['data'][0]['id'];
		print "PetName@".$result['data'][0]['name']."<br>";
		LOGDEB("PetName@".$result['data'][0]['name']);
		$petname = $result['data'][0]['name'];
		print "PetDescription@".$result['data'][0]['comments']."<br>";
		print "PetDOB@".substr($result['data'][0]['date_of_birth'],0,10)."<br>";
		print "PetWeight@".$result['data'][0]['weight']." kg<br>";
		if($result['data'][0]['gender']=="0") {
			print "PetGender@Female<br>";
		} else {
			print "PetGender@Male<br>";
		}
		if($result['data'][0]['species_id']=="2") {
			print "PetSpecies@Dog<br>";
		} else {
			print "PetSpecies@Cat<br>";
		}
		$pet = $result['data'][0]['id'];
	} else {
		die("No Pet!<br>");
		LOGINF("No Pet!<br>");
	}
}
LOGEND("SureFlap HTTP getPet.php stopped");
?>
