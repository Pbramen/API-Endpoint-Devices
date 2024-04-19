<?php
//validates and sanitizes $sn.
validateAPI($logger, $sn, "sn", $endPoint, $endPoint, $time_start);


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