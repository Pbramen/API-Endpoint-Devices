<?php
	
	if($sn !== null){
		if($d === null && $c === null){
				handleAPIResponse(200, "Missing Params", '', 'api/modify_equipment', $time_start);
				handle_logger('log_API_error', $logger, 200, 'No parameters given to update.', 'api/modify_equipment', $endPoint, $time_start);
				exit();
		}
		
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
		
		if($d == $old_d){
			if($c == $old_c){
				handleAPIResponse(200, "No change made.", buildPayload(['sn' => $sn]), 'api/modify_equipment', $time_start);
				handle_logger('log_API_error', $logger, 200, 'No change', 'api/modify_equipment', $endPoint, $time_start);
				exit();	
			}	
		}
		
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
			
		}
		
		if($d !== null){
			$res = curl_POST('query_device', "d=$d", $logger, $endPoint);
			$res = handle_decode($res, $logger, "device", $endPoint, "None Taken", $time_start);
			if(validResponse($res, 'Success')){
				$filter .= ' device_id = (?),';
				$bind .= "i";
				$new = $res['Payload']['Fields']['id'];
				array_push($val, $new);
				$logUpdate .= $old_d.' changed to '.$new.',';
			}
		}
		if($c !== null){
			$res = curl_POST('query_company', "c=$c", $logger, $endPoint);
			$res = handle_decode($res, $logger, "company", $endPoint, "None Taken", $time_start);
			if(validResponse($res, 'Success')){
				$filter .= ' company_id = (?),';
				$bind .= "i";
				$new = $res['Payload']['Fields']['id'];
				array_push($val, $new);
				$logUpdate .= $old_c.' changed to '.$new.',';
			}
		}
		
		try {
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