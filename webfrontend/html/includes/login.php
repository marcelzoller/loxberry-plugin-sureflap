<?php

LOGDEB("Requesting new token");
$json = json_encode(array("email_address" => $config_email_address, "password" => $config_password, "device_id" => $config_device_id));
$curl = post_curl($endpoint."/api/auth/login", null, $json);
LOGDEB("Request received with code: ".$curl['http_code']);

if($curl['http_code'] == "200" and $curl['result']['data']['token']) {
	$token = $curl['result']['data']['token'];
	LOGINF("Login successful!");
	LOGDEB("Setting up new token");
	file_put_contents("$lbpdatadir/token.dat",$token);
} else {
	LOGERR("Login Failed!");
	die("Login Failed!");
}

?>
