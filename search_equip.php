<?php
	$active = $active | 1;
	//validate active here...

	if($limit == null){
		$limit = 1000;
	}
	else if( is_numeric($limit) ){
		$limit = strval($limit);
		$limit = $limit < 1000 ? $limit : 1000;
	}
	else{
		// log error here and exit
	}
	$params = array('attribute' => "",
					'p' => "",
					'bind' => "",
					'values' => array()
				   );
	$empty = array();
	// validate all other inputs here if not null
	if($sn){
		// validate and sanitize sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized =  validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		$sn_json = curl_POST('query_sn', "sn=$sn_sanitized&active=$active", $logger, $endPoint);
		$sn_json = json_decode($sn_json, true);
		
		if(isset($sn_json['Status']) &&$sn_json['Status'] == 200 && $sn_json['MSG'] == 'Success'){
			//sn and sn id found...
			prepareFilters($params, "sn", "i", "sn_id", $sn_json["Payload"]["Fields"]["id"]);
		}
		else{
			echo "system failure";
			exit();
		}
	}
	else{
		$empty[] = "sn";
	}
	// if $d == 0 -> same as no company filter. Returns error msg on negative
	if($d){
		validateAPI($logger, $d, "device", $endPoint, $endPoint, $time_start);
		prepareFilters($params, "relation", "i", "device_id", $d);
		
		// 
	}
	else {
		$empty[] = "d";
	}
	// if $c == 0 -> same as no company filter. Returns error msg on negative.
	if($c){ 
		validateAPI($logger, $c, "company", $endPoint, $endPoint, $time_start);
		prepareFilters($params, "relation", "i", "company_id", $c);
	}
	else{
		$empty[] = "c";
	}
	if(count($empty) == 3){
		handleAPIResponse(200, "Missing Parameters.", "", 'api/search_equip', $time_start);
		handle_logger('log_API_error', $logger, 200, 'No parameters given.', 'None taken', $endPoint, $time_start);
		exit();
	}
	$sql = buildString($params);
	array_push($params['values'], $limit);
	array_push($params['values'], 0);

	try{	
		$res = bindAndExecute($db, $sql, $params['bind'], $params['values']);
		$r = $res->get_result();
		$payload = array();
		while($row = $r->fetch_assoc()){
			array_push($payload, ['sn' => ['value' => $row['sn'], 'Action' => 'api/query_sn?sn='.$row['sn']], 
								  'device' => ['id' =>$row['device_id'], 'Action' => 'api/query_device?d='.$row['device_id']], 
								  'company' => ['id' => $row['company_id'], 'Action' => 'api/query_company?c='.$row['company_id']], 
								  'r_id' => $row['r_id']]
					  );
		}
		//TODO: LOG OPERATIONS AND ERRORS
		if($payload){
			handleAPIResponse(200, "Succss", buildPayload($payload), 'api/search_equip', $time_start);
			exit();
		} 
		else{
			handleAPIResponse(200, "DNE", "api/search_equip", 'api/search_equip', $time_start);
			exit();
		}
	} catch (MySQLi_Sql_Exception $mse){
		echo $mse;
		exit();
	} catch(Exception $e){
		echo $e;
		exit();
	}
	
?>