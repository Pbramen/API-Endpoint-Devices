<?php

//	handle validation and sanitation here...
	
	if($active == null){
		$active == 1;
	}
	if($active != 0 && $active != 1){
		handle_logger('log_API_error', $logger, 200, 'Invalid paramater given for active.', 'None taken', $endPoint, $time_start);
		handleAPIResponse(200, "Invalid active Paramter.", "", 'api/search_equip', $time_start);
		exit();
	}

	if($d != null){
		//validate and sanitize digit
		checkDigit($logger, $d, "device", $endPoint, $time_start);
	}
	else{
		handle_logger('log_API_error', $logger, 200, "Missing device param.", 'add_equipment.php', $endPoint, $time_start);
		handleAPIResponse(200, 'Missing param: device', '', $endPoint, $time_start, 'add_equipment.php');
		exit();
	}
	if($c != null){
		//validate and sanitize digit
		checkDigit($logger, $c, "company", $endPoint, $time_start);
	}
	else{
		handle_logger('log_API_error', $logger, 200, "Missing company param.", 'add_equipment.php', $endPoint, $time_start);
		handleAPIResponse(200, 'Missing param: company', '', $endPoint, $time_start, 'add_equipment.php');
		exit();
	}
	if($sn != null){
		//validate and sanitize alpha
		
		if(strlen($sn) >= 3 && substr($sn, 0, 3) == "SN-")
			$sn = substr($sn, 3);

		$sn = "SN-".validateAndSanitize($sn, $logger, "sn", "sn", $endPoint, $time_start, 84, 1);
		$res = curl_POST("search_one_equip", "sn=$sn&active=$active", $logger, $url);
		$res = handle_decode($res, true);
		
		if(validResponse($res, "DNE")){
			// query if sn exists but has no relation.
			$res = curl_POST('query_sn', "sn=$sn", $logger, $endPoint);
			$res = handle_decode($res);
			
			// if valid -> insert
			if(validResponse($res, "Success")){
				$sn_id = $res['Payload']['Fields']['id'];
				$active = $res['Payload']['Fields']['active'];
				if($active != $active){
					// update sn...
				}
			}
			else{
				// insert sn and grab id...
				$sql = 'Insert into `sn` (sn, active) VALUES (?, ?) ';
				try{
					$res = bindAndExecute($db, $sql, 'si', [$sn, $active]);
					$sn_id = $res->insert_id;
					echo $sn_id;
					$res->close();
				} catch (Mysqli_sql_exception $mse){
					handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
					handleAPIResponse(500, 'Unable to query database.', buildPayload(['d' => $d]), $endPoint, $time_start);
					exit();
				} catch (Exception $e){
					handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
					handleAPIResponse(500, 'Unable to query database.', buildPayload(['d' => $d]), $endPoint, $time_start);
					exit();
				}
				
			}
			// insert new relation
			$sql = 'Insert into `relation` (sn_id, device_id, company_id) VALUES (?, ?, ?)';
				
			try{
				$res = bindAndExecute($db, $sql, "iii", [$sn_id, $d, $c]);
				
				if($res->affected_rows == 1){
				
					//success
					$payload = [ 'device' => ['id' => $d, 'Action' => 'api/query_device?d='.$d],
								 'company'=>['id'=> $c, "Action" => 'api/query_company?c='.$c],
								 'sn' => ['id' => $sn_id, 'Action' => 'api/query_sn?sn='.$sn],
	 							 'r_id' => $res->insert_id];
					handleAPIResponse(200, 'Success', buildPayload($payload), $endPoint, $time_start, 'api/search_one_equip?r='.$r);
					$res->close();
					exit();
				}
				else{
					handle_logger('log_API_error', $logger, 500, "Attempted to update existing record", 'add_equipment.php', $endPoint, $time_start);
					handleAPIResponse(500, 'Record already exists', "", $endPoint, $time_start, 'Pending Review');
					exit();
				}
			} catch (Mysqli_sql_exception $mse){
					handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
					handleAPIResponse(500, 'Unable to query database.', buildPayload(['d' => $d]), $endPoint, $time_start);
					exit();
			} catch (Exception $e){
					handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
					handleAPIResponse(500, 'Unable to query database.', buildPayload(['d' => $d]), $endPoint, $time_start);
					exit();
			}
		}
		else {
			$r = $res['Payload']['Fields']['r_id'];
			handle_logger('log_API_error', $logger, 200, "Attempted to add existing sn: $sn", 'add_equipment.php', $endPoint, $time_start);
			handleAPIResponse(200, 'Record already exists', buildPayload(['id' => $r]), $endPoint, $time_start, 'api/search_one_equip?r='.$r);
			exit();
		}
	}
	else{
		handle_logger('log_API_error', $logger, 200, "Missing sn param.", 'add_equipment.php', $endPoint, $time_start);
		handleAPIResponse(200, 'Missing param: sn', '', $endPoint, $time_start, 'add_equipment.php');
		exit();
	}


	function checkDigit($logger, $d, $name, $endPoint, $time_start){
		validateAPI($logger, $d, $name, $endPoint, "api/add_equipment.php", $time_start);
		if($d != null){
		//validate and sanitize digit
			validateAPI($logger, $d, $name, $endPoint, "api/add_equipment.php", $time_start);
			$res = curl_POST('query_'.$name, "$name[0]=$d", $logger, $endPoint);
			$res = handle_decode($res);
			if(!validResponse($res, "Success")){
				handle_logger('log_API_error', $logger, 200, "$name does not exist.", 'add_equipment.php', $endPoint, $time_start);
				handleAPIResponse(200, 'DNE', $name, $endPoint, $time_start, 'add_equipment.php');
				exit();
			}
		}
		else{
			handle_logger('log_API_error', $logger, json_last_error(), 'Invalid json format: '.json_last_error_msg(), 'add_equipment.php', $endPoint, $time_start);
			handleAPIResponse(200, 'Invalid JSON format.', '', $endPoint, $time_start, 'add_equipment.php');
			exit();
		}
	}
?>