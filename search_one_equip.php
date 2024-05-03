<?php
	// view single equipment 
	// search by primary keys sn or r_id
	
	validActive($logger, $active, $endPoint, $time_start);

	$is_active = '';
	// record is only active if all elements have status of 1. 
	if($active == 1){
		$is_active = "AND sn.active = 1 AND d.active = 1 AND c.active = 1 AND r.active = 1";
	}

	if($sn != null){ // query by sn value (client side)
		// sanitize and validate sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized =  'SN-'.validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		$sn_json = curl_POST('query_sn', "sn=$sn_sanitized&active=$active", $logger, $endPoint);
		$sn_json = handle_decode($sn_json, $logger, 'sn', $endPoint, 'None Taken', $time_start);
		
		if(!(isset($sn_json['Status']) &&$sn_json['Status'] == 200 && $sn_json['MSG'] == 'Success')){
			handle_logger('log_API_error', $logger, 200, 'query_sn failed:'.$sn_json['MSG'], 'None Taken', $endPoint, $time_start);
			header("Content-type: application/json");
			header("HTTP 1.1 200");
			$sn_json = json_encode($sn_json);
			echo $sn_json;
			exit();
		}
		try{
			$sql = "SELECT r.r_id, sn.sn_id, c.company_id, d.device_id, sn.sn, r.active FROM `relation` as r
				JOIN `company` as c ON c.company_id = r.company_id
				JOIN `device` as d ON d.device_id = r.device_id
				JOIN `sn` ON sn.sn_id = r.sn_id 
					WHERE sn.sn=(?) $is_active LIMIT 1";
			$res = bindAndExecute($db, $sql, "s", [$sn_sanitized]);
			$r = $res->get_result();
			$row = $r->fetch_assoc();
			
			if($row){
				$payload = ['sn' => ['id' => $row['sn_id'], 'value'=> $sn_sanitized, 'action'=> 'api/query_sn?sn='.$sn_sanitized],
							'device' => ['id'=> $row['device_id'], 'action'=> 'api/query_device?d='.$row['device_id']],
							'company'=>	['id'=> $row['company_id'], 'action'=> 'api/query_sn?c='.$row['company_id']],
						    'r_id' => $row['r_id'], 'active' => $row['active']];
				
				handleAPIResponse(200, "Success", buildPayload($payload), 'api/search_one_equip', $time_start);
				handle_logger('log_API_op', $logger, $endPoint, 200, "$sn_sanitized queried.", $time_start);
				exit();
			}
			else{
				handleAPIResponse(200, "DNE", "", 'api/search_equip', $time_start);
				handle_logger('log_API_error', $logger, 200, 'SN: '. $sn_sanitized .' is not a registered equipment.', 'api/search_equip', $endPoint, $time_start);
				exit();
			}
		} catch (Mysqli_sql_exception $mse){
			handleAPIResponse(500, 'DB_ERROR', '', $endPoint, $time_start);
			handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE:'.$mse->getCode(), 'None taken.', $time_start );
			exit();
		} catch (Exception $e){
			handleAPIResponse(500, "OTHER_ERR", '', $endPoint, $time_start);
			handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'E:'.$e->getCode(), 'None taken.', $time_start );
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