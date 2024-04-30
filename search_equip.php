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
	if($sn != null){
		// validate and sanitize sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized = validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		$sn_json = curl_POST('query_sn', "sn=$sn_sanitized&active=$active", $logger, $endPoint);
		$sn_json = handle_decode($sn_json);
		
		if(isset($sn_json['Status']) &&$sn_json['Status'] == 200 && $sn_json['MSG'] == 'Success'){
			//sn and sn id found...
			searchPrepare($params, "i", 'AND sn.sn_id = (?)',  $sn_json['Payload']['Fields']['id']);
		}
		else{
			// TODO handle error
			echo "system failure";
			exit();
		}
	}
	else{
		applyNoFilter($extra, $params);
	}
	// if $d == 0 -> same as no company filter. Returns error msg on negative
	if($d){
		validateAPI($logger, $d, "device", $endPoint, $endPoint, $time_start);
		searchPrepare($params, "i", "AND d.device_id = (?) ", $d);
	}
	else {
		applyNoFilter($extra, $params);
	}
	// if $c == 0 -> same as no company filter. Returns error msg on negative.
	if($c){ 
		validateAPI($logger, $c, "company", $endPoint, $endPoint, $time_start);
		searchPrepare($params, "i", "AND c.company_id = (?) ", $c);
	}
	else{
		applyNoFilter($extra, $params);	
	}
	$sql = buildSQL($params, $active, $empty, $limit, $offset);
	
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
		
		if($empty != 3){
			$filters = "WHERE ";
		}
		$filters = $params['p'][0].' '.$params['p'][1].' '.$params['p'][2];
		if ($active == 1){
			$filters .= " AND sn.active = 1 AND d.active = 1 AND c.active = 1";
		}
		array_push($params['values'], $limit);
		array_push($params['values'], $offset);
		$params['bind'] .= "ii";
		
			// Query Optimizer for mysql optimizer...
		$sql = "SELECT r.r_id, c.company_id, d.device_id, sn.sn FROM `relation` as r
					JOIN `company` as c ON c.company_id = r.company_id
					JOIN `device` as d ON d.device_id = r.device_id
					JOIN `sn` ON sn.sn_id = r.sn_id 
						$filters LIMIT ? OFFSET ?";
		
		return $sql;
	}

	function searchPrepare(&$params, $bind, $cond, $value){
		$params['bind'] .= $bind;
		array_push($params['p'], " $cond");
		array_push($params['values'], $value);
	}

	function applyNoFilter(&$extra, &$params){
		$extra += 1;
		array_push($params['p'], "");
	}
?>