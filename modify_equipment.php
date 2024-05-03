<?php
	
	if($sn !== null){
		
		$sn = "SN-".validateAndSanitize($sn, $logger, "sn", "sn", $endPoint, $time_start, 84, 1);
		
		$r = curl_POST('search_one_equip', "sn=$sn&active=0", $logger, $endPoint);
		$res = handle_decode($r, $logger, "sn", $endPoint, "None Taken", $time_start);
		
		if(!validResponse($res, "Success")){
			header("Content-type: application/json");
			echo $r;
			exit();
		}
		
		$sn_id = $res['Payload']['Fields']['sn']['id'];
		$old_d = $res['Payload']['Fields']['device']['id'];
		$old_c = $res['Payload']['Fields']['company']['id'];
		$old_active = $res['Payload']['Fields']['active'];
		
		
		$filter = '';
		$bind = '';
		$logUpdate = '';
		$val =array();
		
		if($active !== null){
			if($active != 0 && $active != 1){
				handle_logger('log_API_error', $logger, 200, 'Invalid paramater given for active.', 'None taken', $endPoint, $time_start);
				handleAPIResponse(200, "Invalid active param.", "", $endPoint, $time_start);
				exit();
			}
			$filter .=' active = '.$active.',';
			if($active != $old_active){
				$logUpdate .= "active updated to $active, ";
			}
			
		}
		if($d != null && $d != 0){
			$r = curl_POST('query_device', "d=$d", $logger, $endPoint);
			$res = handle_decode($r, $logger, "device", $endPoint, "None Taken", $time_start);
			if(validResponse($res, 'Success')){
				$filter .= ' device_id = (?),';
				$bind .= "i";
				$new = $res['Payload']['Fields']['id'];
				array_push($val, $new);
				$logUpdate .= $old_d.' device changed to '.$new.',';
			}else{
				header("HTTP 1.1 200");
				header("Content-type: application/json");
				echo $r;
				exit;
			}
		}
		if($c != null && $c != 0){
			$r = curl_POST('query_company', "c=$c", $logger, $endPoint);
			$res = handle_decode($r, $logger, "company", $endPoint, "None Taken", $time_start);
			if(validResponse($res, 'Success')){
				$filter .= ' company_id = (?),';
				$bind .= "i";
				$new = $res['Payload']['Fields']['id'];
				array_push($val, $new);
				$logUpdate .= $old_c.' company changed to '.$new.',';
			}else{
				header("HTTP 1.1 200");
				header("Content-type: application/json");
				echo $r;
				exit;
			}
		}
		// check for new SN
		$new_id = 0;
		$add = false;
		if($newSN != null){
			$newSN = 'SN-'.validateAndSanitize($newSN, $logger, "sn", "sn", $endPoint, $time_start, 84, 1);
			$r = curl_POST('query_sn', "sn=$newSN&active=0", $logger, $endPoint);
			$res = handle_decode($r, $logger, "device", $endPoint, "None Taken", $time_start);
			if(validResponse($res, 'Success')){
				// sn exists -> search relation to see if we can insert it. 
				$r2 = curl_POST('search_one_equip', "sn=$newSN&active=0", $logger, $endPoint);
				$res2 = handle_decode($r2, $logger, 'newSN', $endPoint, "None Taken", $time_start);
				if(validResponse($res2, 'DNE')){
					// we do not have to insert a new sn and the sn can be inserted!
					$new_id = $res['Payload']['Fields']['id'];
				}
				else if (validResponse($res2, 'Success')){
					// we cannot insert the newSN since it is already registered
					handleAPIResponse(200, "New SN already exists", buildPayload(['id' => $res['Payload']['Fields']['id']]), 'api/modify_equipment', $time_start);
					handle_logger('log_API_error', $logger, 200, 'Attempted to modify to pre-exsiting sn: '.$newSN, 'api/modify_equipment', $endPoint, $time_start);
					exit();
				}
				else{
					// other error occured.
					header("HTTP 1.1 200");
					header("Content-type: application/json");
					echo $r2;
					exit();
				}
			} else if( validResponse($res, "DNE")){
				// sn does not exist and must be added before updating...
				$add = true;
			}
			else{
				// other error occured.
				header("HTTP 1.1 200");
				header("Content-type: application/json");
				echo $r;
				exit();
			}
		}
		
		// check if any changes were made
		if($d == $old_d && $sn == $newSN && $c == $old_c && $active == $old_active){
				handleAPIResponse(200, "No change made.", buildPayload(['sn' => $sn]), 'api/modify_equipment', $time_start);
				handle_logger('log_API_error', $logger, 200, 'No change', 'api/modify_equipment', $endPoint, $time_start);
				exit();
		}
		try {
			if($add){
				$sql = "INSERT INTO `sn` (sn) VALUES (?)";
				$res = bindAndExecute($db, $sql, 's', [$newSN]);
				$new_id = $db->lastInsertId();
				$res->close();
			}
			if($new_id != 0){
				$filter .= 'sn_id = ?,';
				$bind .= 's';
				$val[] = $new_id;
				$logUpdate .= "$sn changed to $new_id,";
			}
			$filter = rtrim($filter, ",");
			$logUpdate = rtrim($logUpdate, ',');
			array_push($val, $sn_id);
			$sql = "UPDATE `relation` SET ".$filter." where sn_id = (?)";
			$res = bindAndExecute($db, $sql, $bind.'s', $val);
			
			if($res->affected_rows != 1){

				handleAPIResponse(200, "No change made.", buildPayload(['sn' => $sn]), 'api/modify_equipment', $time_start);
				handle_logger('log_API_error', $logger, 200, 'No change', 'api/modify_equipment', $endPoint, $time_start);
				exit();	
			}
			else{
				handleAPIResponse(200, "Success", '', 'api/modify_equipment', $time_start);
				handle_logger("log_API_op", $logger, $endPoint, '200', "$sn updated: $logUpdate", $time_start );
				exit();	
			}
			
		} catch (Mysqli_sql_exception $mse){
			handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
			handleAPIResponse(500, "DB_ERR", '', 'None taken', $time_start);
			exit();
		} catch (Exception $e){
			handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
			handleAPIResponse(500, "OTHER_ERR", '', 'None taken', $time_start);
			exit();
		}
	}
	handleAPIResponse(200, "Missing required SN", "", 'api/modify_equipment', $time_start);
	handle_logger('log_API_error', $logger, 200, 'Missing sn', 'api/modify_equipment', $endPoint, $time_start);

?>