<?php
	// validate for id (integer)
	validateAPI($logger, $d, $short, $endPoint, $endPoint, $time_start);
	$new_sanitized = validateAndSanitize($new, $logger, $name, $short, $endPoint, $time_start);
	if($active && ($active != 0 || $active != 1)){
		// invalid active.
		handleAPIResponse(200, "Invalid active param. Must be 0 or 1.", "", 'api/query_'.$name, $time_start);
		handle_logger('log_API_error', $logger, 200, 'Invalid active param type.', 'api/query_'.$name, $endPoint, $time_start);
		exit();
	}
	// sanitation succeded
	$active = $active | 1;
	try {
		$sql = 'Select * from '.$name.' where '.$name.'_id = (?) AND active = (?)';
		$res = bindAndExecute($db, $sql, "ii", [$d, $active]);
		$r = $res->get_result();
		$row = $r->fetch_assoc();
		$old = $row[$name];
		if($row){
			$sql = 'Update `'.$name.'` SET '.$name.'=(?) where '.$name.'_id = (?) and active = (?) ';
			$res = bindAndExecute($db, $sql, "sii", [$new_sanitized, $d, $active]);
			handle_logger('log_API_op', $logger, $endPoint, 200, "Device $d changed to $new_sanitized", $time_start);
			handleAPIResponse(200, "Success", buildPayload(['old'=> $old, 'new'=> $new_sanitized, 'id'=> $d]), $endPoint, $time_start);
			exit();
		}
		else{
			handleAPIResponse(200, "Non-existant value", buildPayload(['id'=> $d]), 'api/query_'.$name, $time_start);
			handle_logger('log_API_error', $logger, 200, 'Device id '.$d.' does not exist.', 'api/query_'.$name, $endPoint, $time_start);
			exit();
		}
	} catch (MySQLi_Sql_Exception $mse){
		handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE:'.$mse->getCode(), 'None taken.', $time_start );
		handleAPIResponse(500, "DB_ERROR", "", $endPoint, $time_start );
		exit();
	} catch (Exception $e) {
		handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'E:'.$e->getCode(), 'None taken.', $time_start );;
		handleAPIResponse(500, "OTHER_ERROR", "", $endPoint, $time_start );
		exit();
	}
?>