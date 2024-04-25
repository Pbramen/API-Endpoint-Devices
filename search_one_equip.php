<?php
	// view single equipment 
	// search by primary keys sn or r_id

	if($sn != null){ // query by sn value (client side)
		// sanitize and validate sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized =  validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		try{
			// DO NOT join relation with sn table (extremely slow). 
			// select id and status from sn -> then query for relation based on sn_id
			$sql = "Select sn_id, active from `sn` where sn = (?)";
			$res = bindAndExecute($db, $sql, "s", ['SN-'.$sn_sanitized]);
			$r = $res->get_result();
			$row = $r->fetch_assoc();
			$sn_status = $row['active'];
			
			if(!$row){
				handleAPIResponse(200, "DNE", buildPayload(['sn' => $sn_sanitized]), 'api/search_equip', $time_start);
				handle_logger('log_API_error', $logger, 200, 'SN: '. $sn_sanitized .' does not exist in database.', 'api/search_equip', $endPoint, $time_start);
				exit();
			}
			$sn_id = $row['sn_id'];

			$sql = "Select d.device, d.device_id, d.active as d_active, c.company_id, c.company, c.active as c_active from `relation` JOIN device as d on d.device_id = relation.device_id JOIN company as c on c.company_id = relation.company_id where relation.sn_id = (?)";
			$res = bindAndExecute($db, $sql, "i", [$sn_id]);
			$r = $res->get_result();
			$row = $r->fetch_assoc();
			
			if($row){
				$payload = ['sn' => ['id'=> $sn_id, 'value'=> $sn_sanitized, 'status' => $sn_status],
							'device' => ['id'=> $row['device_id'], 'value' => $row['device'], 'status' => $row['d_active']],
							'company'=>	['id'=> $row['company_id'], 'value' => $row['company_id'], 'status' => $row['c_active']]];
				
				handleAPIResponse(200, "Success", buildPayload($payload), 'api/search_one_equip', $time_start);
				handle_logger('log_API_op', $logger, $endPoint, 200, "Equipment SN($sn_id) queried.", $time_start);
				exit();
			}
			else{
				handleAPIResponse(200, "DNE", ['sn'=> ['id'=>$sn_id, 'value'=> $sn_sanitized, 'status' => $sn_status]], 'api/search_equip', $time_start);
				handle_logger('log_API_error', $logger, 200, 'SN: '. $sn_sanitized .' is registered without company and device info.', 'api/search_equip', $endPoint, $time_start);
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

	} // query by r_id (system use only)
 	else if($r != null){
		// r must be numeric TODO : revist this function more more detailed loggging.
		if(!is_numeric($r)){
			handle_logger("log_API_error", $logger, 200, 'Invalid r_id data type', $endPoint, 'api/search_one_equip', $time_start);
			handleAPIResponse(200, "r_id must be digits only.", '', $endPoint, $time_start);
			exit();
		}
		if(intval($r) <= 0){
			handle_logger("log_API_error", $logger, 200, 'Negative r_id .', $endPoint, 'api/search_one_equip', $time_start);
			handleAPIResponse(200, "r_id must be positive.", '', $endPoint, $time_start);
			exit();
		}
		
		$sql = "Select r_id, sn_id, d.device, d.device_id, d.active as d_active, c.company_id, c.company, c.active as c_active from `relation` JOIN device as d on d.device_id = relation.device_id JOIN company as c on c.company_id = relation.company_id where relation.r_id = (?)";
		$res = bindAndExecute($db, $sql, "i", [$r]);
		$r2 = $res->get_result();
		$row = $r2->fetch_assoc();
		$sn_id = $row['sn_id'];
		$payload = [
			'sn' => ['id' => $row['sn_id']],
			'device' => ['id' => $row['device_id'], 'value'=> $row['device']],
			'company' => ['id' => $row['company_id'], 'value' => $row['company']]
		];
		
		if($row){
			// grab sn 
			$sql = "Select sn, active from `sn` where sn_id=(?)";
			try {
				$res = bindAndExecute($db, $sql, "i", [$sn_id]);
				$r2 = $res->get_result();
				$row = $r2->fetch_assoc();
				
				if($row){
					$payload['sn']['value'] = $row['sn'];	
					handleAPIResponse(200, "Success", buildPayload($payload), 'api/search_one_equip', $time_start);
					handle_logger('log_API_op', $logger, $endPoint, 200, "Equipment R_ID($r) queried.", $time_start);
					exit();
				}
				else{
					// SYSTEM ERROR 
					handle_logger("log_API_error", $logger, 500, 'Missing sn value for '.$sn_id, 'api/search_one_equip', $time_start);
					handleAPIResponse(500, "DB missing SN value", '' , $endPoint, $time_start);
					exit();
				}
				
			} catch( Mysqli_sql_exception $mse ){
				handleAPIResponse($mse->getCode(), "DB_ERR", buildPayload(['r' => $r]), 'api/search_one_equip', $time_start);
				handle_logger('log_API_error', $logger, $mse->getCode(), 'Select SN value failed: '.$mse->getMessage(), 'None taken', $endPoint, $time_start);
				exit();
			} catch (Exception $e){
				handleAPIResponse($e->getCode(), "DB_ERR", buildPayload(['r' => $r]), 'api/search_one_equip', $time_start);
				handle_logger('log_API_error', $logger, $e->getCode(), 'Select SN value failed: '.$e->getMessage(), 'None taken', $endPoint, $time_start);
				exit();
			}
		}
	}
	// no valid params given
	else{
		handleAPIResponse(200, "Missing Params", "", 'api/search_equip', $time_start);
		handle_logger('log_API_error', $logger, 200, 'Missing Param', 'api/search_one_equip', $endPoint, $time_start);
		exit();
	}
?>