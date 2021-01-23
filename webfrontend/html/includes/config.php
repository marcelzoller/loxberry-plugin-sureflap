<?php
require_once "Config/Lite.php";
require_once "loxberry_system.php";

// save timezone
$server_timezone = date_default_timezone_get();

// load configfile
$cfg = new Config_Lite("$lbpconfigdir/pluginconfig.cfg");

// logindata
$config_email_address = $cfg['MAIN']['EMAIL'];
$config_password = $cfg['MAIN']['PASSWORD'];
$config_miniserver = $cfg['MAIN']['MINISERVER'];
$config_http_send = $cfg['MAIN']['HTTPSEND'];

$endpoint = "https://app.api.surehub.io";

// send http?
$found = false;
if($config_http_send == 1) {
	$miniservers = LBSystem::get_miniservers();
	foreach ($miniservers as $miniserver) {		
		if($miniserver['Name'] == $config_miniserver) {
			if($miniserver['PreferHttps'] == 1) {
				LOGDEB("sending encrypted in https-Mode");
				$response_endpoint = "https://";
				$miniserver_port = $miniserver['PortHttps'];	
			} else {
				LOGDEB("sending not encrypted in http-Mode");
				$response_endpoint = "http://";
				$miniserver_port = $miniserver['Port'];
			}
			$response_endpoint = $response_endpoint.$miniserver['Credentials']."@".
								 $miniserver['IPAddress'].":".$miniserver_port."/dev/sps/io/";
		}
		break;
	}
}

// Invent something for mandatory fingerprintJs login value. Any 32bit integer will suffice.
$config_device_id = (string) rand(1000000000,1999999999);

// get last token
LOGDEB("Getting last token");
$token = file_get_contents("$lbpdatadir/token.dat");

?>
