<?php
	// view single equipment 
	// search by primary keys sn or r_id
	
	validActive($logger, $active, $endPoint, $time_start);

	$sn_active = "";
	$d_active = "";
	$c_active = "";
	if($active == 1){
		$sn_active = "AND sn.active = 1";
		$d_active = "AND d.active = 1";
		$c_active = "AND c.active = 1";
	}

	if($sn != null){ // query by sn value (client side)
		// sanitize and validate sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized =  'SN-'.validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		$sn_json = curl_POST('query_sn', "sn=$sn_sanitized&active=$active", $logger, $endPoint);
		$sn_json = handle_decode($sn_json);
		
		if(!(isset($sn_json['Status']) &&$sn_json['Status'] == 200 && $sn_json['MSG'] == 'Success')){
			handle_logger('log_API_error', $logger, 200, 'query_sn failed:'.$sn_json['MSG'], 'None taken', $endPoint, $time_start);
			header("Content-type: application/json");
			header("HTTP 1.1 200");
			$sn_json = json_encode($sn_json);
			echo $sn_json;
			exit();
		}
		try{
			$sql = "SELECT r.r_id, c.company_id, d.device_id, sn.sn FROM `relation` as r
				JOIN `company` as c ON c.company_id = r.company_id
				JOIN `device` as d ON d.device_id = r.device_id
				JOIN `sn` ON sn.sn_id = r.sn_id 
					WHERE sn.sn=(?) LIMIT 1";
			$res = bindAndExecute($db, $sql, "s", [$sn_sanitized]);
			$r = $res->get_result();
			$row = $r->fetch_assoc();
			
			if($row){
				$payload = ['sn' => ['value'=> $sn_sanitized, 'action'=> 'api/query_sn?sn='.$sn_sanitized],
							'device' => ['id'=> $row['device_id'], 'action'=> 'api/query_device?d='.$row['device_id']],
							'company'=>	['id'=> $row['company_id'], 'action'=> 'api/query_sn?c='.$row['company_id']],
						    'r_id' => $row['r_id']];
				
				handleAPIResponse(200, "Success", buildPayload($payload), 'api/search_one_equip', $time_start);
				handle_logger('log_API_op', $logger, $endPoint, 200, "Equipment SN($sn_id) queried.", $time_start);
				exit();
			}
			else{
				handleAPIResponse(200, "DNE", "", 'api/search_equip', $time_start);
				handle_logger('log_API_error', $logger, 200, 'SN: '. $sn_sanitized .' is not a registered equipment.', 'api/search_equip', $endPoint, $time_start);
				exit();
			}
		} catch (Mysqli_sql_exception $mse){
			handleAPIResponse($mse->getCode(), "DB_ERR", buildPayload(['sn' => $sn_sanitized]), 'api/search_one_equip', $time_start);
			handle_logger('log_API_error', $logger, $mse->getCode(), 'Select SN failed: '.$mse->getMessage(), 'None taken', $endPoint, $time_start);
			exit();
		} catch (Exception $e){
			handleAPIResponse($e->getCode(), "OTHER_ERR", buildPayload(['sn' => $sn_sanitized]), 'api/search_one_equip', $time_start);
			handle_logger('log_API_error', $logger, $e->getCode(), 'Other exception SN select query: '.$e->getMessage(), 'None taken', $endPoint, $time_start);
			exit();
		}
	}
	// no valid params given
	else{
		handleAPIResponse(200, "Missing Params", "", 'api/search_equip', $time_start);
		handle_logger('log_API_error', $logger, 200, 'Missing Param', 'api/search_one_equip', $endPoint, $time_start);
		exit();
	}
?>