<?php
	// validate for id (integer)
	
	validateAPI($logger, $d, $short, $endPoint, $endPoint, $time_start);
	
	if($active === null){
		$active = 1;
	}
	
	if($active != 0 && $active != 1){
		// invalid active.
		handleAPIResponse(200, "Invalid active param.", "", 'api/query_'.$name, $time_start);
		handle_logger('log_API_error', $logger, 200, 'Invalid active param type.', 'api/query_'.$name, $endPoint, $time_start);
		exit();
	}
	
	$filters = "";
	$values= [];
	$bind = "";

	try {
		$sql = 'Select * from '.$name.' where '.$name.'_id = (?)';
		$res = bindAndExecute($db, $sql, "i", [$d]);
		$r = $res->get_result();
		$row = $r->fetch_assoc();
		$old = $row[$name];
		
		if($row){
			if($new != null)
				$new_sanitized = validateAndSanitize($new, $logger, $name, $short, $endPoint, $time_start);
			else $new_sanitized = $row[$name];	
			
			if($row[$name] == $new_sanitized && $active == $row['active']){
				handleAPIResponse(200, "No change", "", 'api/query_'.$name, $time_start);
				handle_logger('log_API_error', $logger, 200, 'No change for '.$name.': '.$new_sanitized, 'api/query_'.$name, $endPoint, $time_start);
				exit();
			}
			
			if($new != null){
				$sql = 'Select '.$name.'_id from '.$name.' where '.$name.' = ?';
				$res = bindAndExecute($db, $sql, "s", [$new_sanitized]);
				$r = $res->get_result();
				$row = $r->fetch_assoc();
				
				$filters .= ' '.$name.'= (?), ';
				$values[] = $new_sanitized;
				$bind .= 's';
				if($row){
					handleAPIResponse(200, "$name already exists.", "", 'api/query_'.$name, $time_start);
					handle_logger('log_API_error', $logger, 200, 'Attempt to update to pre-existing '.$name.': '.$new_sanitized, 'api/query_'.$name, $endPoint, $time_start);
					exit();
				}
				
			}
			
			$filters .= ' active = (?), ';
			$values[] = $active;
			$bind .= 'i';
			
			$values[] = $d;
			$bind .= 'i';
			$filters = rtrim($filters, ', ');
			
			$sql = 'Update `'.$name.'` SET '.$filters.' where '.$name.'_id = (?)';

			$res = bindAndExecute($db, $sql, $bind, $values);
			handle_logger('log_API_op', $logger, $endPoint, 200, "Device $d changed to $new_sanitized", $time_start);
			handleAPIResponse(200, "Success", buildPayload(['old'=> $old, 'new'=> ['name' => $new_sanitized, 'active' => $active], 'id'=> $d]), $endPoint, $time_start);
			exit();
		}
		else{
			handleAPIResponse(200, "DNE", buildPayload(['id'=> $d]), 'api/query_'.$name, $time_start);
			handle_logger('log_API_error', $logger, 200, 'Device id '.$d.' does not exist.', 'api/query_'.$name, $endPoint, $time_start);
			exit();
		}
	} catch (MySQLi_Sql_Exception $mse){
		echo $mse;
		handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE:'.$mse->getCode(), 'None taken.', $time_start );
		handleAPIResponse(500, "DB_ERROR", "", $endPoint, $time_start );
		exit();
	} catch (Exception $e) {
		handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'E:'.$e->getCode(), 'None taken.', $time_start );;
		handleAPIResponse(500, "OTHER_ERROR", "", $endPoint, $time_start );
		exit();
	}
?>