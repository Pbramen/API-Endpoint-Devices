<?php

	validPagination($logger, $limit, "maxRange", 1000, 'limit', $endPoint, $time_start);
	validPagination($logger, $offset, "minRange", 0, 'offset', $endPoint, $time_start);
	validActive($logger, $active, $endPoint, $time_start);

	$params = array(
					'p' => array(),
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
			searchPrepare($params, "iii", 'AND sn.sn_id = (?)',  $sn_id, $limit, $offset);
		}
		else{
			// TODO handle error
			echo "system failure";
			exit();
		}
	}
	else{
		applyNoFilter($extra, $params, $limit, $offset);
	}
	// if $d == 0 -> same as no company filter. Returns error msg on negative
	if($d){
		validateAPI($logger, $d, "device", $endPoint, $endPoint, $time_start);
		searchPrepare($params, "iii", "AND d.device_id = (?) ", $d, $limit, $offset);
	}
	else {
		applyNoFilter($extra, $params, $limit, $offset);
	}
	// if $c == 0 -> same as no company filter. Returns error msg on negative.
	if($c){ 
		validateAPI($logger, $c, "company", $endPoint, $endPoint, $time_start);
		searchPrepare($params, "iii", "AND c.company_id = (?) ", $c, $limit, $offset);
	}
	else{
		applyNoFilter($extra, $params, $limit, $offset);	
	}
	$sql = buildSQL($params, $active, $empty, $limit, $offset);
	
	try{	
		$res = bindAndExecute($db, $sql, $params['bind'], $params['values']);
		$r = $res->get_result();
		$payload = array();
		while($row = $r->fetch_assoc()){
			
			array_push($payload, ['sn' => ['id' => $row['sn_id'], 'value' => $row['sn'], 'Action' => 'api/query_sn?sn='.$row['sn']], 
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
		$sn_active = "";
		$d_active = "";
		$c_active = "";
		
		if ($active == 1){
			$sn_active = "WHERE sn.active = 1";
			$d_active = "WHERE d.active = 1";
			$c_active = "WHERE c.active = 1";
		}
		
		if($empty == 3){
			$params['bind'] = 'iiiiii';
			$params['values'] = [$limit, $offset];

			array_push($params['values'], $limit);
			array_push($params['values'], $offset);
			array_push($params['values'], $limit);
			array_push($params['values'], $offset);
			
			// Query Optimizer for mysql optimizer...
			$sql = "(Select r.r_id, sn.sn_id, sn.sn, c.company_id, d.device_id from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id $sn_active LIMIT ? OFFSET ?)
			UNION ALL (Select r.r_id, sn.sn_id, sn.sn, c.company_id, d.device_id from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id $d_active LIMIT ? OFFSET ?) 
			UNION ALL (Select r.r_id, sn.sn_id, sn.sn, c.company_id, d.device_id from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id $c_active LIMIT ? OFFSET ?)";

			//TODO: query without filters
		}
		else{
			$sql = '(Select r.r_id, sn.sn_id, sn.sn, c.company_id, d.device_id from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id '.$sn_active.$params['p'][0].' LIMIT ? OFFSET ?)
			UNION ALL (Select r.r_id, sn.sn_id, sn.sn, c.company_id, d.device_id from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id '.$d_active.$params['p'][1].' LIMIT ? OFFSET ?) 
			UNION ALL (Select r.r_id, sn.sn_id, sn.sn, c.company_id, d.device_id from relation as r JOIN sn ON r.sn_id = sn.sn_id JOIN company as c ON r.company_id = c.company_id JOIN device as d ON d.device_id = r.device_id '.$c_active.$params['p'][2].' LIMIT ? OFFSET ?)';
			
		}
		return $sql;
	}

	function searchPrepare(&$params, $bind, $cond, $value, $limit, $offset){
		$params['bind'] .= $bind;
		array_push($params['p'], " $cond");
		array_push($params['values'], $value);
		array_push($params['values'], $limit);
		array_push($params['values'], $offset);
	}

	function applyNoFilter(&$extra, &$params, $limit, $offset){
		$extra += 1;
		array_push($params['p'], "");
		array_push($params['values'], $limit);
		array_push($params['values'], $offset);
		$params['bind'] .= 'ii';
	}
?>