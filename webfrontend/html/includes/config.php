<?php
require_once "Config/Lite.php";
require_once "loxberry_system.php";
require_once "loxberry_io.php";

// save timezone
$server_timezone = date_default_timezone_get();

// load configfile
$cfg = new Config_Lite("$lbpconfigdir/pluginconfig.cfg");

// logindata
$config_email_address = @$cfg['MAIN']['EMAIL'];
$config_password      = @$cfg['MAIN']['PASSWORD'];
$config_miniserver    = @$cfg['MAIN']['MINISERVER'];
$config_http_send     = @$cfg['MAIN']['HTTPSEND'];
$config_mqtt_send     = @$cfg['MAIN']['MQTTSEND'];
$config_mqtt_topic    = @$cfg['MAIN']['MQTT_TOPIC'];

$endpoint = "https://app.api.surehub.io";
$config_send = false;

// send http?
$http_activ = false;
if( ($config_http_send == 1) && isset($_GET['viname']) ) {
	$miniservers = LBSystem::get_miniservers();
	foreach ($miniservers as $index => $miniserver) {	
		if($miniserver['Name'] == $config_miniserver) {
			$miniserver_no = $index;
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
			$http_activ = $config_send = true;
			break;
		}		
	}
}

// send mqtt?
$mqtt_activ = false;
if($config_mqtt_send == 1 && isset($config_mqtt_topic)) {
	$mqttcreds = mqtt_connectiondetails();
	if( is_array($mqttcreds) ) {		
		$mqtt_activ = $config_send = true;
		// MQTT requires a unique client id
		$mqttcreds['client_id'] = uniqid(gethostname()."_LoxBerry");
	}
}

// Invent something for mandatory fingerprintJs login value. Any 32bit integer will suffice.
$config_device_id = (string) rand(1000000000,1999999999);

// get last token
LOGDEB("Getting last token");
$token = "";
if(file_exists("$lbpdatadir/token.dat")) {
	$token = file_get_contents("$lbpdatadir/token.dat");
}

?>
