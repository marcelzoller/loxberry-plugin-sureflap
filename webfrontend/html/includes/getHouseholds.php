<?php
if($households) {	
	if($_GET['householdname']) {
		LOGDEB("Parameter: householdname=\"".$_GET['householdname']."\"");
	}
	
	$found = false;
	foreach($households AS $household) {	
		$multi = $household['name']."@";
		
		if($household['name'] == $_GET['householdname'] or empty($_GET['householdname'])){
			$found = true;
			// ID
			print $multi."HouseholdID@".$household['id']."<br>";
			LOGINF("HouseholdID@".$household['id']);
			// Name
			print $multi."HouseholdName@".$household['name']."<br>";
			print "<br>";
		}
	}
	if(!$found) {
		print "household does not match!<br><br>";
		LOGWARN("household does not match!");
	}
} else {
	print "No household!<br><br>";
	LOGERR("No household!");
}
?>
