<?php
if($households) {	
	if(isset($_GET['householdname'])) {
		LOGDEB("Parameter: householdname=\"".$_GET['householdname']."\"");
	}
	
	$found = false;
	foreach($households AS $index => $household) {	
		$multi = $household['name']."@";
		
		if(!isset($_GET['householdname']) or $household['name'] == $_GET['householdname']){
			$found = true;
			$householdindex = $index;
			// ID
			$householdid = $household['id'];
			print $multi."HouseholdID@".$householdid."<br>";
			LOGINF("HouseholdID@".$householdid);
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
