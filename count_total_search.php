<?php
	// ONLY CALL THIS FUNCTION IF SEARCH_ONE_EQUIP != 0 OR 1000. 
		//
	// NOTE offset is only used in pagination -> r_id > offset
	validPagination($logger, $offset, "minRange", 0, 'offset', $endPoint, $time_start);
	validActive($logger, $active, $endPoint, $time_start);
	
	$params = array(
					'p' => array(),
					'bind' => "",
					'values' => array()
				   );
	$empty = 0;
	searchPrepare($params, 'i', 'r.r_id >= ?', $offset);
	// validate all other inputs here if not null
	if($sn != null){
		// validate and sanitize sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized = validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		
		$sn_json = curl_POST('query_sn', "sn=$sn_sanitized&active=$active", $logger, $endPoint);
		$sn_json = handle_decode($sn_json, $logger, "sn", $endPoint,"None Taken", $time_start);
		
		if(isset($sn_json['Status']) &&$sn_json['Status'] == 200 && $sn_json['MSG'] == 'Success'){
			//sn and sn id found...
			searchPrepare($params, "i", 'AND sn.sn_id = (?)',  $sn_json['Payload']['Fields']['id']);
		}
		else{
			handle_logger('log_API_error', $logger, 200, "Search sn DNE: $sn, $active", 'add_equipment.php', $endPoint, $time_start);
			handleAPIResponse(200, "DNE", "", 'api/search_equip', $time_start);
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
	$sql = buildSQL($params, $active, $extra, $offset);
	
	try{	
		$res = bindAndExecute($db, $sql, $params['bind'], $params['values']);
		$r = $res->get_result();
		$row = $r->fetch_assoc();
		$payload = ['count' => $row['c']];
		//TODO: LOG OPERATIONS AND ERRORS
		if($payload){
			handleAPIResponse(200, "Success", buildPayload($payload), 'api/search_equip', $time_start);
			exit();
		} 
		else{
			handle_logger('log_API_error', $logger, 200, "Search sn DNE: $sn, $active", 'add_equipment.php', $endPoint, $time_start);
			handleAPIResponse(200, "DNE", "", 'api/search_equip', $time_start);
			exit();
		}
	} catch (MySQLi_Sql_Exception $mse){
			handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE:'.$mse->getCode(), 'None taken.', $time_start );
			handleAPIResponse(500, 'DB_ERROR', '', $endPoint, $time_start);
			exit();
	} catch(Exception $e){
			handleAPIResponse(500, 'DB_ERROR', '', $endPoint, $time_start);
			handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'E:'.$e->getCode(), 'None taken.', $time_start );
			exit();
	}

	
	function buildSQL(&$params, $active, $empty, $offset){
		
		
		$filters = "WHERE";
		$filters .= $params['p'][0].' '.$params['p'][1].' '.$params['p'][2].' '.$params['p'][3];
		if ($active == 1){
			$filters .= ' AND ';
			$filters .= "sn.active = 1 AND d.active = 1 AND c.active = 1 AND r.active = 1";
		}
			// Query Optimizer for mysql optimizer...
		$sql = "SELECT COUNT(a.d) as c FROM (Select d.device_id as d from `relation` as r JOIN `company` as c ON c.company_id = r.company_id JOIN `device` as d ON d.device_id = r.device_id JOIN `sn` ON sn.sn_id = r.sn_id $filters LIMIT 1000 ) as a; ";
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