<?php
	// handle errors and exit if missing
	if($d == null){

		handleMissingField("device");	
	
	}
	if($c == null){

		handleMissingField("company");
	}
	if($sn == null){

		handleMissingField("sn");
	}
	
	// we now need to validate
	if(!is_numeric($d)){
		// log error here

		
		
	}
	if(!is_numeric($c)){
		// log error here

		
	}
	
	// validate sn 
	if(!checkSNLen($sn)){
		// log error here

		return;
	}
	
	if(strlen($sn) < 3 || substr($sn, 3) != 'SN-'){
		// log warning here
		$sn = 'SN-'.$sn;
	}
	
	if(checkSNString($sn) >= 2){
		// log error here

		return;
	}
	// check if sn does not exist 
	if(checkUnique($sn)){
		// log error here

		return;
	}

	// sanitize input.
	$d = sanitizeDriver($logger, $d, $url, "add_equipment.php");
	$c = sanitizeDriver($logger, $c, $url, "add_equipment.php");
	$sn = sanitizeDriver($logger, $sn, $url, "add_equipment.php");

	// check if device and company exist calling other endpoints.
	$time_start = microtime(true);
	$d_json = curl_POST("query_device", "d=$d", $logger, $url);
	$d_json = json_decode($d_json);
	
	if($d_json){
		if (isset($d_json['MSG']) && $d_json['MSG'] == 'Status: NOT FOUND'){
			//log error here
			
			//send back json with status message. 
			exit();
		}
	}
	else{
		// log json error here
		 log_sys_err($logger, json_last_error(), json_last_error_msg(), $url, "JSON", "Try again");
		 echo handleAPIResponse(400, 'Failed to parse json.', '', 'api/query_device');
	}
	
	$c_json = curl_POST("query_company", "c=$c", $logger, $url);

	$d_json = curl_POST("query_device", "d=$d", $logger, $url);
	
	if(isset($c_json["Status"]) && isset($c_json["MSG"])){
		if($c_json["Status"] == "ERROR"){
			//handle error here
			
			$output[] = "Status: Missing device";
			$response = json_encode($output);
			if(!$response) {
				// log sys error here
				echo handleAPIResponse(400, 'Failed to parse json', '', 'api/query_device');
				exit();
			}
			// log api error here
			echo $output;
			exit();
		}
	}

	if(isset($c_json["Status"]) && $c_json["Status"] == "OK"){
		
	}

	// insert here 
	try{
		$sql = 'INSERT INTO `relation` (sn_id, device_id, company_id) VALUES (?, ?, ?)';
		$res = bindAndExecute($db, $sql, 'iii', $sn_id, $d, $c);
		
	} catch (MySQLi_Sql_Exception $mse){
		 log_sys_err($logger, $mse.code, $mse.message, $url, "POST", "Review Pending");
		// construct JSON here for return code.
	}
?>