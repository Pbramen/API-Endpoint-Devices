<?php
	if($active == null){
		$active = 1;
	}
	//validate active here...
	if($active != 0 && $active != 1){
		handle_logger('log_API_error', $logger, 200, 'Invalid paramater given for active.', 'None taken', $endPoint, $time_start);
		handleAPIResponse(200, "Invalid active param.", "", 'api/search_equip', $time_start);
		exit();
	}

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
	$empty = 0;
	// validate all other inputs here if not null
	if($sn){
		// validate and sanitize sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized =  validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		$sn_json = curl_POST('query_sn', "sn=$sn_sanitized&active=$active", $logger, $endPoint);
		$sn_json = handle_decode($sn_json);
		
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
		$empty += 1;
	}
	// if $d == 0 -> same as no company filter. Returns error msg on negative
	if($d){
		validateAPI($logger, $d, "device", $endPoint, $endPoint, $time_start);
		prepareFilters($params, "relation", "i", "device_id", $d);
		
		// 
	}
	else {
		$empty += 1;
	}
	// if $c == 0 -> same as no company filter. Returns error msg on negative.
	if($c){ 
		validateAPI($logger, $c, "company", $endPoint, $endPoint, $time_start);
		prepareFilters($params, "relation", "i", "company_id", $c);
	}
	else{
		$empty += 1;
	}

	$sql = buildSQL($params, $active, $empty, $limit, $offset);
	//echo $sql;
	print_r($params['values']);

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
			handleAPIResponse(200, "Success", buildPayload($payload), 'api/search_equip', $time_start);
			exit();
		} 
		else{
			handleAPIResponse(200, "DNE", "", 'api/search_equip', $time_start);
			exit();
		}
	} catch (MySQLi_Sql_Exception $mse){
		echo $mse;
		exit();
	} catch(Exception $e){
		echo $e;
		exit();
	}

	
	function buildSQL(&$params, $active, $empty, $limit, $offset){
		if($empty == 3){
			$params['bind'] = 'ii';
			$params['values'] = [$limit, $offset];

			$sn_active = "";
			$d_active = "";
			$c_active = "";

			if ($active){
				$params['bind'] .= 'iiii';
				array_push($params['values'], $limit);
				array_push($params['values'], $offset);
				array_push($params['values'], $limit);
				array_push($params['values'], $offset);
				$sn_active = "WHERE sn.active = 1";
				$d_active = "WHERE d.active = 1";
				$c_active = "WHERE c.active = 1";
			}
			// Query Optimizer for mysql optimizer...
			$sql = "(Select r.r_id, sn.sn_id, c.company, d.device from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id $sn_active LIMIT ? OFFSET ?) UNION ALL (Select r.r_id, sn.sn_id, c.company, d.device from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id $c_active LIMIT ? OFFSET ?) UNION ALL (Select r.r_id, sn.sn_id, c.company, d.device from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id $d_active LIMIT ? OFFSET ?)";

			//TODO: query without filters
		}
		else{
			$sql = buildString($params);
			array_push($params['values'], $limit);
			array_push($params['values'], 0);
		}
		return $sql;
	}
	
?>