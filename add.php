<?php
	$short = $name[0];

	// validateAPI by name here..
	$d_sanitized = validateAndSanitize($d, $logger, $name, $short, $endPoint, $time_start);

	// check if unique
	$sql = 'SELECT * from `'.$name.'` WHERE '.$name.' = (?)';
	try{
		$res = bindAndExecute($db, $sql, 's', [$d_sanitized]);
		$r = $res->get_result();
		$row = $r->fetch_assoc();

		if($row){
			handle_logger('log_API_error', $logger, 200, $name.' '.$d_sanitized.' exists already.', 'api/query_'.$name, $endPoint, $time_start);
			handleAPIResponse(200, 'Device already exists.', buildPayload([$short=>$d_sanitized, 'id'=> $row[$name.'_id']]), $endPoint, $time_start, "api/query_$name");
			exit();
		}
		$res->close();
	} catch(Mysqli_sql_exception $mse){
		// TODO log here
		handle_logger('log_API_error', $logger, 200, 'Failed to execute select query for uniquiness: '.$mse->getMessage(), 'None taken', $endPoint, $time_start);	
		handleAPIResponse(200, 'Failed to query for uniquiness.', buildPayload([$short=>$d]), $endPoint, $time_start, "api/query_$name");
		exit();
		
	} catch(Exception $e){
		// TODO log here
		handle_logger('log_API_error', $logger, 200, 'Failed to execute select query for uniquiness: '.$e->getMessage(), 'None taken', $endPoint, $time_start);
		handleAPIResponse(200, 'Failed to query for uniquiness.', buildPayload([$short=>$d]), $endPoint, $time_start, "api/query_$name");
		exit();
	}
	// insert $name if non-existant
	// set active to 1 by default.
	$active = $active | 1;
	$sql = "INSERT INTO `$name` ($name, active) VALUES (?, ?)";

	try{
		$res = bindAndExecute($db, $sql, 'si', [$d_sanitized, $active]);
		$res->close();
		handle_logger('log_API_op', $logger, $endPoint, 200, "New $name inserted: $d_sanitized", $time_start);
		handleAPIResponse(200, "Success", [ 'Fields' => array($short=> $d, 'active' => $active)], $endPoint, $time_start );
	} catch (Mysqli_sqli_Exception $mse){
		handle_logger('log_API_error', $logger, 200, 'Insertion failed: '.$mse->getMessage(), 'api/query_'.$name, $endPoint, $time_start);
		handleAPIResponse(200, "MSE error", [ 'Fields' => array($short=> $d)], $endPoint, $time_start );
		exit();
	} catch (Exception $e){
		handle_logger('log_API_error', $logger, 200, 'Other exception: '.$e->getMessage(), 'api/query_'.$name, $endPoint, $time_start);
		handleAPIResponse(200, "Other exception", [ 'Fields' => array($short=> $d)], $endPoint, $time_start );
		exit();
	}
?>