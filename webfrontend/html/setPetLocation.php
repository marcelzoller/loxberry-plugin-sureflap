<?php
require_once "loxberry_log.php";

// check parameter "petname"
// Backward compatibility
if(isset($_GET['name'])) {
	$_GET['petname'] = $_GET['name'];
	LOGWARN("Parameter name should no longer be used! Please use petname instead.");
}
if(!isset($_GET['petname'])){
	die("Usage: ".$_SERVER['PHP_SELF']."?petname=[...]&location=[1|2] or [in|out]\n");
}
// better use with radiobutton
if(isset($_GET['locationLox'])) {
	$_GET['location'] = $_GET['locationLox'] + 1;
}	

// check parameter "location"
$location_mode = @$_GET['location'].@$_GET['locationid'];
switch($location_mode){
	case "1":
	case "in":
		$location = 1;
		$location_str = "inside";
		break;
	case "2":
	case "out":
		$location = 2;
		$location_str = "outside";
		break;
	default:
		die("Usage: ".$_SERVER['PHP_SELF']."?petname=[...]&locationid=[0|1] or ?location=[in|out]\n");
}

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setPetLocation.php started");
LOGDEB("setPetLocation: ".$location_str." for ".$_GET['petname']);

// get new data - no output
$background = "setPetLocation_".$_GET['petname']."_".$location_str;
include 'getData.php';

// Check if pet match
if($petname != $_GET['petname']) {
	LOGERR("Pet does not match!");
	die("Pet does not match!");
}

if($curr_location_id == $location) {
	print "Location for \"$petname\" is \"$location_str\". No change necessary.";
	LOGINF("Location for \"$petname\" is \"$location_str\". No change necessary.");
} else {
	// Set Timezone to UTC
	date_default_timezone_set('UTC');
	$json = json_encode(array("where" => $location, "since" => date("Y-m-d H:i:s")));
	// reset timezone
	date_default_timezone_set($server_timezone);

	LOGDEB("Starting request...");
	$curl = post_curl($endpoint."/api/pet/$petid/position", $token, $json);
	LOGDEB("Request received with code: ".$curl['http_code']);

	if($curl['code_ok'] and $curl['result']['data']['where'] == $location) {
		print "Successfully set pet location for \"$petname\" to \"$location_str\"";
		LOGINF("Successfully set pet location for \"$petname\" to \"$location_str\"");
		
		// Build data to responce
		$pets[$petindex]['position']['where'] = $location;
		$pets[$petindex]['position']['since'] = date("Y-m-d H:i:s");
		// Send data
		$send_data = true;
	} else {
		print "Set Location Failed!";
		LOGERR("Set Location Failed!");
	}
}

if($config_send and $send_data) {
	print "<br><br>";
	// Only send changed values
	$_GET['viparam'] = "DateTime;DateTimeLox;DateTimeUnix;PetLocation;PetLocationLox;PetLocationDesc;PetLocationSince;PetLocationSinceLox;PetLocationSinceUnix";
	// Convert value
	include 'includes/getPets.php';
	// Responce to virutal input
	LOGDEB("Starting Response to miniserver...");
	include_once 'includes/sendResponces.php';
}

LOGEND("SureFlap HTTP setPedLocations.php stopped");/**/
?>

