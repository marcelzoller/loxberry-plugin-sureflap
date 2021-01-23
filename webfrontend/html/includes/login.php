<?php

LOGDEB("Requesting new token");
$json = json_encode(array("email_address" => $config_email_address, "password" => $config_password, "device_id" => $config_device_id));

$ch = curl_init($endpoint."/api/auth/login");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json)));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed");
LOGDEB("Request received with code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE));

if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == "200" and $result['data']['token']) {	
	$token = $result['data']['token'];
	LOGINF("Login successful!");
	LOGDEB("Setting up new token");
	file_put_contents("$lbpdatadir/token.dat",$token);
} else {
	LOGERR("Login Failed!");
	die("Login Failed!");
}

?>
