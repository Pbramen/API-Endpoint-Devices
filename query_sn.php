<?php
if($sn == null){
	//log error here.
	handle_logger("log_API_error", $logger, 200, "Serial Number is missing", "api/query_sn", $endPoint, $time_start);
	handleAPIResponse(200, "Serial Number is missing.", "", $endPoint, $time_start);
	exit();
}

if(!is_numeric($sn)){
	handle_logger("log_API_error", $logger, 200, "Invalid sn_id data type.", "api/query_sn", $endPoint, $time_start);
	handleAPIResponse(200, "Serial Number must be digits only.", buildErrorPayload(['sn' => $sn]), $endPoint, $time_start);
	exit();
}

// maybe overkill...
$sn = sanitizeDriver($logger, $sn, $endPoint, "Serial Number");

$sql = 'SELECT * from sn where sn_id = (?)';

try{
	$res = bindAndExecute($db, $sql, 'i', [$sn]);
} catch (Mysqli_SQL_Exception $mse){
	handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
	handleAPIResponse(500, 'Unable to query database.', buildErrorPayload(['sn' => $sn]), $endPoint, $time_start);
	exit();
} catch (Exception $e) {
	// log error here
	handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
	handleAPIResponse(500, 'Unable to query database.', buildErrorPayload(['sn' => $sn]), $endPoint, $time_start);
	exit();
}

$r = $res->get_result();
$row = $r->fetch_assoc();
$res->close();

if ($row && $row['active']){
	$serial = $row['sn'];
	$payload = [
		'id' => $sn,
		'sn' => $serial
	];
	
	handle_logger("log_API_op", $logger, $endPoint, '200', "Serial Number $sn($sn) queried.", $time_start );
	handleAPIResponse(200, 'Serial Number Found!', buildErrorPayload($payload), $endPoint, $time_start);
	exit();
}
// device is not active, log output and return not found
else {
	$payload = [
		'id' => $sn
	];
	if($row){
		// log operation here
		handle_logger("log_API_op", $logger, $endPoint, '200', "Serial Number $sn($sn) queried, but not active.", $time_start );
	}
	else{
		handle_logger("log_API_op", $logger, $endPoint, '200', "Serial Number $sn queried, not found.", $time_start );
	}
		handleAPIResponse(200, 'Serial Number Not found', buildErrorPayload($payload), $endPoint, $time_start);
		exit();
}

?>