<?php
require_once "Config/Lite.php";
require_once "loxberry_system.php";
$cfg = new Config_Lite("$lbpconfigdir/pluginconfig.cfg");

//echo $cfg->get("MAIN","EMAIL");


$email_address = $cfg['MAIN']['EMAIL'];
//$email_address = "test@test.com";
$password = $cfg['MAIN']['PASSWORD'];;
//$password = "test1234";

$endpoint = "https://app.api.surehub.io";

// Invent something for mandatory fingerprintJs login value. Any 32bit integer will suffice.
//$device_id = (string) rand(1000000000,9999999999);
$device_id = (string) rand(100000000,999999999);
?>
