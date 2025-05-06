<?php
if($devices) {	
	if(isset($_GET['devicename'])) {
		LOGDEB("Parameter: devicename=\"".$_GET['devicename']."\"");
	}
	
	$found = false;
	foreach($devices AS $index => $device) {
		$multi = $device['name']."@";
		
		if(!isset($_GET['devicename']) or $device['name'] == $_GET['devicename']) {
			$found = true;
			// ID
			print $multi."DeviceID@".$device['id']."<br>";			
			LOGINF("DeviceID@".$device['id']);
			// Type			
			print $multi."DeviceType@".$device['product_id']."<br>";
			switch($device['product_id']) {
				case 1:
					$devicetype = "Hub";
					$hubindex  = $index;
					$hub     = $device['id'];
					$hubname = $device['name'];
					break;
				case 2:
					$devicetype = "Repeater";
					break;
				case 3:
					$devicetype = "Pet Door Connect";
					$flapindex  = $index;
					$flap     = $device['id'];					
					$flapname = $device['name'];
					$flaptype = $device['product_id'];
					break;
				case 4:
					$devicetype = "Pet Feeder Connect";
					break;
				case 5:
					$devicetype = "Programmer";
					break;
				case 6:
					$devicetype = "DualScan Cat Flap Connect";
					$flapindex  = $index;
					$flap     = $device['id'];
					$flapname = $device['name'];
					$flaptype = $device['product_id'];
					break;
			}
			print $multi."DeviceTypeDesc@".$devicetype."<br>";
			// Name
			print $multi."DeviceName@".$device['name']."<br>";
			LOGINF("DeviceName@".$device['name']);
			// MAC
			print $multi."DeviceMACAddress@".$device['mac_address']."<br>";
			// Serial
			if(isset($device['serial_number'])) {
				print $multi."DeviceSerialNumber@".$device['serial_number']."<br>"; 
			}
			// Online
			print $multi."DeviceOnline@".$device['status']['online']."<br>";
			// Signal
			if(isset($device['status']['signal'])) {
				print $multi."DeviceSignal@".round($device['status']['signal']['device_rssi'],1)."<br>";
				print $multi."DeviceSignalHub@".round($device['status']['signal']['hub_rssi'],1)."<br>";
			}
			// HUB-data
			if($device['product_id'] == 1) { 
				// LED-Mode
				$device_led_id = $device['control']['led_mode'];				
				unset($device_led);
				switch($device_led_id) {	
					case "0":
						$device_led_id_out = 1;
						$device_led = "off";
						break;
					case "1":
						$device_led_id_out = 2;
						$device_led = "bright";
						break;		
					case "4":
						$device_led_id_out = 3;
						$device_led = "dim";
						break;
				}
				print $multi."DeviceLedMode@".$device_led_id_out."<br>";
				$device_led_lox = $device_led_id_out - 1;
				print $multi."DeviceLedModeLox@".$device_led_lox."<br>";
				print $multi."DeviceLedModeDesc@".$device_led."<br>"; 	
			}			
			// Flap-data
			if($device['product_id'] == 3 or $device['product_id'] == 6) { 
				// Battery
				$device_batt = floatval($device['status']['battery']);
				print $multi."DeviceBattery@".$device_batt."<br>";
				if ($device_batt <= 4.8) {
					$device_batt_perc = 0;
				} elseif ($device_batt >= 5.6) {
					$device_batt_perc = 100;
				} else {
					$device_batt_perc = round(($device_batt - 4.8) / 0.8 * 100);
				}
				print $multi."DeviceBatteryPerc@".$device_batt_perc."<br>";
				// Locking
				$device_lock_id = $device['control']['locking'];
				unset($device_lock);
				switch($device_lock_id) {	
					case "0":
						$device_lock = "none";
						break;
					case "1":
						$device_lock = "in";
						break;		
					case "2":
						$device_lock = "out";
						break;
					case "3":
						$device_lock = "both";
						break;
				}
				$device_lock_id_out = $device_lock_id + 1;
				print $multi."DeviceLockMode@".$device_lock_id_out."<br>";
				print $multi."DeviceLockModeLox@".$device_lock_id."<br>";
				print $multi."DeviceLockModeDesc@".$device_lock."<br>";
				// Curfew-Status
				$device_curfew = $device['control']['curfew'];
				$curfew_string = "";
				$curfew_activ = 0;
				foreach($device_curfew AS $curfew) {
					if($curfew['enabled'] == true) {
						$curfew_overlap = false;
						if(!empty($curfew_string)) { 
							$curfew_string .= ","; 
						}
						// Is curfew activ?
						$curfew_from = strtotime($curfew['lock_time'].' UTC');
						$curfew_to   = strtotime($curfew['unlock_time'].'  UTC');
						$curfew_string .= date('H:i', $curfew_from)."-".date('H:i', $curfew_to);
						if($curfew_to < $curfew_from) {
							$curfew_overlap = true;
						}
						if(time() >= $curfew_from and ($curfew_overlap or time() < $curfew_to)) {
							$curfew_activ = 1;
						} elseif($curfew_overlap and time() < $curfew_to) {
							$curfew_activ = 1;
						}						
					}
				}
				if(empty($curfew_string)) { 
					$curfew_string = "-"; 
				}
				print $multi."DeviceCurfew@".$curfew_string."<br>";
				print $multi."DeviceCurfewState@".$curfew_activ."<br>";
				// Pet-Locking
				$device_pet_locking = $device['tags'];
			}
			print("<br>");
		}
	}
	if(!$found) {
		print "Device does not match!<br><br>";
		LOGWARN("Device does not match!");
	}
} else {	
	print "No devices!<br><br>";
	LOGERR ("No devices!");
}	
?>
