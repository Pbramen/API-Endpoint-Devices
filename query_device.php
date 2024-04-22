<?php

//validates and sanitizes $d. Exits if invalid.
validateAPI($logger, $d, "device", $endPoint, $endPoint, $time_start);

$sql = 'SELECT * from device where device_id = (?)';

try{
	$res = bindAndExecute($db, $sql, 'i', [$d]);
} catch (Mysqli_SQL_Exception $mse){
	handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
	handleAPIResponse(500, 'Unable to query database.', buildPayload(['d' => $d]), $endPoint, $time_start);
	exit();
} catch (Exception $e) {
	// log error here
	handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
	handleAPIResponse(500, 'Unable to query database.', buildPayload(['d' => $d]), $endPoint, $time_start);
	exit();
}

$r = $res->get_result();
$row = $r->fetch_assoc();
$res->close();

if ($row && $row['active']){
	$device = $row['device'];
	$payload = [
		'id' => $d,
		'device' => $device
	];
	
	handle_logger("log_API_op", $logger, $endPoint, '200', "Device $device($d) queried.", $time_start );
	handleAPIResponse(200, 'Success', buildPayload($payload), $endPoint, $time_start);
	exit();
}
// device is not active, log output and return not found
else {
	$payload = [
		'id' => $d
	];
	if($row){
		// log operation here
		handle_logger("log_API_op", $logger, $endPoint, '200', "Device $device($d) queried, but not active.", $time_start );
	}
	else{
		handle_logger("log_API_op", $logger, $endPoint, '200', "Device $d queried, not found.", $time_start );
	}
		handleAPIResponse(200, 'DNE', buildPayload($payload), $endPoint, $time_start);
		exit();
}
?>