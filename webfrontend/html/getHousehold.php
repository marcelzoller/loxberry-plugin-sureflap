<?php

include_once 'login.php';

$ch = curl_init($endpoint."/api/household");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
$result = json_decode(curl_exec($ch),true) or die("Curl Failed\n");


if($result['data']) {
		if($result['data'][0]['name']==$_GET['holdername']){
			print "HouseholdID@".$result['data'][0]['id']."<br>HouseholdName@".$result['data'][0]['name']."<br>";
		}
		$household = $result['data'][0]['id'];
	} else {
		die("No Household!<br>");
	
}
?>
