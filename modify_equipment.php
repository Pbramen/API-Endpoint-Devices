<?php
	// get by sn_id
	if($active == null){
		$active = 1;
	}
	//validate active here...
	if($active != 0 && $active != 1){
		handle_logger('log_API_error', $logger, 200, 'Invalid paramater given for active.', 'None taken', $endPoint, $time_start);
		handleAPIResponse(200, "Invalid active Paramter.", "", 'api/search_equip', $time_start);
		exit();
	}

	

	if($sn != null){
		
		if($d == null && $c == null){
				handleAPIResponse(200, "Missing Params", '', 'api/modify_equipment', $time_start);
				handle_logger('log_API_error', $logger, 200, 'No parameters given to update.', 'api/modify_equipment', $endPoint, $time_start);
				exit();
		}
		
		$sn = "SN-".validateAndSanitize($sn, $logger, "sn", "sn", $endPoint, $time_start, 84, 1);
		$res = curl_POST('search_one_equip', "sn=$sn", $logger, $endPoint);
		$res = handle_decode($res);
		
		if(!validResponse($res, "Success")){
			echo "equipment does not exist.";
			exit();
		}
		
		$sn_id = $res['Payload']['Fields']['sn']['id'];
		$old_d = $res['Payload']['Fields']['device']['id'];
		$old_c = $res['Payload']['Fields']['company']['id'];
		
		if($d == $old_d){
			if($c == $old_c){
				handleAPIResponse(200, "No change made.", buildPayload(['sn' => $sn_sanitized]), 'api/modify_equipment', $time_start);
				handle_logger('log_API_error', $logger, 200, 'No change', 'api/modify_equipment', $endPoint, $time_start);
				exit();	
			}	
		}
		
		$filter = '';
		$bind = '';
		$val =array();
		if($d != null){
			$res = curl_POST('query_device', "d=$d", $logger, $endPoint);
			$res = handle_decode($res);
			if(validResponse($res, 'Success')){
				$filter .= ' device_id = (?),';
				$bind .= "i";
				array_push($val, $res['Payload']['Fields']['id']);
			}
		}
		if($c != null){
			$res = curl_POST('query_company', "c=$c", $logger, $endPoint);
			$res = handle_decode($res);
			if(validResponse($res, 'Success')){
				$filter .= ' company_id = (?),';
				$bind .= "i";
				array_push($val, $res['Payload']['Fields']['id']);
			}
		}
		try {
			$filter = rtrim($filter, ",");
			array_push($val, $sn_id);
			$sql = "UPDATE `relation` SET ".$filter." where sn_id = (?)";

			
			$res = bindAndExecute($db, $sql, $bind.'s', $val);
			if($res->affected_rows != 1){
				echo "no update";
				exit();
			}
			else{
				echo "update successful!";
				exit();
			}
			
		} catch (MySQLi_Sql_Exception $mse){
			echo $mse;
			exit();
		} catch (Exception $e){
			echo $e;
			exit();
		}
		exit();
	}
	handleAPIResponse(200, "Missing required SN", "", 'api/modify_equipment', $time_start);
	
?>