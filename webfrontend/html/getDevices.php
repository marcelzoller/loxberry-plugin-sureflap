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

LOGSTART("SureFlap HTTP getDevices.php started");

$ch = curl_init($endpoint."/api/household/$household/device");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed<br>");

//print ("Devname: ".$_GET['devicename']."<br>");
LOGDEB("GET Devicename: ".$_GET['devicename']);

if($result['data']) {
	foreach($result['data'] as $foo) {
		if($foo['name']==$_GET['devicename'] OR empty($_GET['devicename'])){
			print "DeviceID@".$foo['id']."<br>";
			LOGDEB("DeviceID@".$foo['id']);
			switch($foo['product_id']) {
				case 1:
					print "DeviceType@Hub<br>";
					$hub = $foo['id'];
					$hubname = $foo['name'];
					break;
							case 2:
									print "DeviceType@Repeater<br>";
									break;
							case 3:
									print "DeviceType@Pet Door Connect<br>";
					$flap = $foo['id'];
					$flapname = $foo['name'];
									break;
							case 4:
									print "DeviceType@Pet Feeder Connect<br>";
									break;
							case 5:
									print "DeviceType@Programmer<br>";
									break;
							case 6:
									print "DeviceType@DualScan Cat Flap Connect<br>";
					$flap = $foo['id'];
					$flapname = $foo['name'];
									break;
			}
			print "DeviceName@".$foo['name']."<br>";
			LOGDEB("DeviceName@".$foo['name']);
			print "DeviceMACAddress@".$foo['mac_address']."<br>";
			if($foo['serial_number']) {
				print "DeviceSerialNumber@".$foo['serial_number']."<br>";
				LOGDEB("DeviceSerialNumber@".$foo['serial_number']);
			}
			print("<br>");
		}
	}
}
LOGEND("SureFlap HTTP getDevices.php stopped");
?>
