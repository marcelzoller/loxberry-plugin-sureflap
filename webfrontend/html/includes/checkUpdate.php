<?php

// who calls?
$update_caller = @$background;
if(empty($update_caller)) {
	$update_caller = "getData";
}

// get last update
LOGDEB("Getting last update for $update_caller");
$last_update = "";
if(file_exists("$lbpdatadir/lastUpdate_$update_caller.dat")) {
	$last_update = file_get_contents("$lbpdatadir/lastUpdate_$update_caller.dat");
}

// check interval
if(strtotime($last_update) > strtotime("-10 seconds")) {
	LOGWARN("Execution is only allowed with a minimum interval of 10 seconds!");
	die("Execution is only allowed with a minimum interval of 10 seconds");
}

// set last update
file_put_contents("$lbpdatadir/lastUpdate_$update_caller.dat",date('d.m.Y H:i:s'));

?>
