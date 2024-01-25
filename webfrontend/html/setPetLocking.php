<?php
require_once "loxberry_log.php";

// check parameter "petname"
// Backward compatibility
if(isset($_GET['name'])) {
	$_GET['petname'] = $_GET['name'];
	LOGWARN("Parameter name should no longer be used! Please use petname instead.");
}
if(!isset($_GET['petname'])){
	die("Usage: ".$_SERVER['PHP_SELF']."?petname=[...]&locking=[2|3] or [out|in]\n");
}
// better use with radiobutton
if(isset($_GET['lockingLox'])) {
	$_GET['locking'] = $_GET['lockingLox'] + 2;
}	

// check parameter "locking"
$locking_mode = @$_GET['locking'].@$_GET['lockingid'];
switch($locking_mode){
	case "2":
	case "in":
		$locking = 2;
		$locking_str = "outside";
		break;
	case "3":
	case "out":
		$locking = 3;
		$locking_str = "inside";
		break;
	default:
		die("Usage: ".$_SERVER['PHP_SELF']."?petname=[...]&lockingid=[2|3] or ?locking=[out|in]\n");
}

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP setPetLocking.php started");
LOGDEB("setPetLocking: ".$locking_str." for ".$_GET['petname']);

// get new data - no output
$background = "setPetLocking_".$_GET['petname']."_".$locking_str;
include 'getData.php';

// Check if pet match
if($petname != $_GET['petname']) {
	LOGERR("Pet does not match!");
	die("Pet does not match!");
}

if($curr_pet_locking['profile'] == $locking) {
	print "Locking for \"$petname\" is \"$locking_str\". No change necessary.";
	LOGINF("Locking for \"$petname\" is \"$locking_str\". No change necessary.");
} else {
	LOGDEB("Starting request...");
	$json = json_encode(array("profile" => $locking));
	$curl = put_curl($endpoint."/api/device/$flap/tag/".$curr_pet_locking['id'], $token, $json);
	LOGDEB("Request received with code: ".$curl['http_code']);

	if($curl['result']['data']['profile'] == $locking) {
		print "Successfully set pet locking for \"$petname\" to \"$locking_str\"";
		LOGINF("Successfully set pet locking for \"$petname\" to \"$locking_str\"");
		
		// Build data to responce
		$device_pet_locking = array($curl['result']['data']);		
	} else {
		print "Set Locking Failed!";
		LOGERR("Set Locking Failed!");
	}
}

if($config_send) {
	print "<br><br>";
	// Only send changed values
	$_GET['viparam'] = "DateTime;DateTimeLox;DateTimeUnix;PetLocking;PetLockingLox;PetLockingDesc";
	// Convert value	
	include 'includes/getPets.php';
	// Responce to virutal input
	LOGDEB("Starting Response to miniserver...");
	include_once 'includes/sendResponces.php';
}

LOGEND("SureFlap HTTP setPedLockings.php stopped");/**/
?>

