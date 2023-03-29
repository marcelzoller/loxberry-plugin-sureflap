<?php
require_once "loxberry_log.php";

$params = [
    "name" => "Daemon",
    "filename" => "$lbplogdir/sureflap.log",
    "append" => 1
];
$log = LBLog::newLog ($params);

LOGSTART("SureFlap HTTP getTimeline.php started");

// get new data - no output
$background = "getTimeline";
include 'getData.php';

// get last timeline id
LOGDEB("Getting last timeline id");
$last_timeline = "";
if(file_exists("$lbpdatadir/timeline.dat")) {
	$last_timeline = file_get_contents("$lbpdatadir/timeline.dat");
}

LOGDEB("Starting request...");
//$curl = get_curl($endpoint."/api/timeline/household/$householdid/pet?before_id=1178509940&page=1&page_size=1", $token);
$curl = get_curl($endpoint."/api/timeline/household/$householdid/pet", $token);
LOGDEB("Request received with code: ".$curl['http_code']);

$last_timeline = 1179607293;
print "Last Timeline:".$last_timeline."<br><br>";

$multi = "Timeline@";

foreach($curl['result']['data'] AS $timeline) {
	if(empty($first_timeline)) {
		$first_timeline = $timeline[id];
	}
	if($timeline[id] == $last_timeline) {
		break;
	}
	print $multi."Id@".$timeline['id']."<br>";
	$timeline_time = strtotime($timeline['created_at']);
	print $multi."CreateTime@".date('d.m.Y H:i:s', $timeline_time)."<br>";
	print $multi."CreateTimeLox@".epoch2lox($timeline_time)."<br>";
	$direction = $timeline['movements'][0]['direction'];
	print $multi."Direction@".$direction."<br>";
	print $multi."PetId@".$timeline['pets'][0]['id']."<br>";
	$petname = $timeline['pets'][0]['name'];
	print $multi."PetName@$petname<br>";
	
	if($timeline['devices'] <> NULL) {
		print $multi."DeviceId@".$timeline['devices'][0]['id']."<br>";	
		$devicename = $timeline['devices'][0]['name'];
		print $multi."DeviceName@$devicename<br>";
		
		$timeline_text = "$petname hat ";
		if($direction > 0) {
			$timeline_text = "$timeline_text das Haus ";
		}
		switch($direction) {	
			case "0":
				$timeline_end = "gesehen";
				break;
			case "1":
				$timeline_end = "betreten";
				break;		
			case "2":
				$timeline_end = "verlassen";
				break;
		}
		$timeline_text = "$timeline_text durch \"$devicename\" $timeline_end";
		
	} elseif ($timeline['users'] <> NULL) {
		print $multi."UserId@".$timeline['users'][0]['id']."<br>";	
		$username = $timeline['users'][0]['name'];
		print $multi."UserName@$username<br>";

		switch($direction) {
			case "1":
				$timeline_end = "drinnen";
				break;		
			case "2":
				$timeline_end = "draußen";
				break;
		}
		$timeline_text = "$username hat $petname Standort auf $timeline_end aktualisiert";
	}
	
	print $multi."Text@$timeline_text<br><br>";
}

// set last timeline ID
print "Now Last Timeline:".$first_timeline;
file_put_contents("$lbpdatadir/timeline.dat",$first_timeline);

if($config_send) {
	print "<br><br>";
	// Only send changed values
	// $_GET['viparam'] = "PetLocation;PetLocationLox;PetLocationDesc;PetLocationSince;PetLocationSinceLox";
	// Convert value
	// include 'includes/getPets.php';
	// Responce to virutal input
	LOGDEB("Starting Response to miniserver...");
	include_once 'includes/sendResponces.php';
}

LOGEND("SureFlap HTTP getTimeline.php stopped");/**/
?>

