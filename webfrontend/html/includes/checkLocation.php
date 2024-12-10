<?php

$location = true;
// check pet location
foreach($pets AS $index => $pet) {
	if (!array_key_exists('position', $pet)) {
		// save flage to check location
		$location = false;
	}
}

if ($location == false) {
	LOGINF("Getting timeline to set location");
	$curl = get_curl($endpoint."/api/timeline/household/$householdid/pet?page_size=20", $token);
	LOGDEB("Request received with code: ".$curl['http_code']);

	// loop at all pets
	foreach($pets AS $index => $pet) {
		$petname = $pet['name'];
		// location in array?
		if (array_key_exists('position', $pet)) continue;
		// typ to find last loaction in timeline
		foreach($curl['result']['data'] AS $timeline) {
			// not the current pet?
			if ($timeline['pets'][0]['id'] != $pet['id']) continue;
			// set last location
			$direction = $timeline['movements'][0]['direction'];
			// only inside and outside messages
			if ($direction != 1 and $direction != 2) continue;
			// write values to pet array
			$pets[$index]['position']['where'] = $direction;
			$pets[$index]['position']['since'] = $timeline['created_at'];
			
			LOGINF("PetLocation for $petname set to $direction");
			break;
		}
	}
}
?>

