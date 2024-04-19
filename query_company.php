<?php
//validates and sanitizes $c. Exits if invalid.
validateAPI($logger, $c, "company", $endPoint, $endPoint, $time_start);

// maybe overkill...
$c = sanitizeDriver($logger, $c, $endPoint, "company");

$sql = 'SELECT * from company where company_id = (?)';

try{
	$res = bindAndExecute($db, $sql, 'i', [$c]);
} catch (Mysqli_SQL_Exception $mse){
	handle_logger("log_sys_err", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
	handleAPIResponse(500, 'Unable to query database.', buildPayload(['c' => $c]), $endPoint, $time_start);
	exit();
} catch (Exception $e) {
	// log error here
	handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'MSE_ERR', 'None taken.', $time_start );
	handleAPIResponse(500, 'Unable to query database.', buildPayload(['c' => $c]), $endPoint, $time_start);
	exit();
}

$r = $res->get_result();
$row = $r->fetch_assoc();
$res->close();

if ($row && $row['active']){
	$company = $row['company'];
	$payload = [
		'id' => $c,
		'company' => $company
	];
	
	handle_logger("log_API_op", $logger, $endPoint, '200', "Company $company($c) queried.", $time_start );
	handleAPIResponse(200, 'Company Found!', buildPayload($payload), $endPoint, $time_start);
	exit();
}
// device is not active, log output and return not found
else {
	$payload = [
		'id' => $c
	];
	if($row){
		// log operation here
		handle_logger("log_API_op", $logger, $endPoint, '200', "Company $company($c) queried, but not active.", $time_start );
	}
	else{
		handle_logger("log_API_op", $logger, $endPoint, '200', "Company $c queried, not found.", $time_start );
	}
		handleAPIResponse(200, 'Company Not found', buildPayload($payload), $endPoint, $time_start);
		exit();
}

?>