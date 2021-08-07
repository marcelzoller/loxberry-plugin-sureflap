<?php

function init_curl($endpoint) {
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
	return $ch;
}

function start_curl($ch) {
	$curl_array = array("result" => "", "http_code" => 0);	
	
	$curl_array['result'] = json_decode(curl_exec($ch),true);
	$curl_array['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return $curl_array;
}

function get_curl($endpoint, $token) {
	$ch = init_curl($endpoint);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
	return start_curl($ch);
}

function lox_curl($endpoint) {
	$ch = init_curl($endpoint);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	return start_curl($ch);
}

function put_curl($endpoint, $token, $json) {
	$ch = init_curl($endpoint);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json),"Authorization: Bearer $token"));
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);	
	return start_curl($ch);
}

function post_curl($endpoint, $token, $json) {
	$ch = init_curl($endpoint);
	if(empty($token)) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json)));
	} else {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($json),"Authorization: Bearer $token"));
	}
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);	
	return start_curl($ch);
}

?>
