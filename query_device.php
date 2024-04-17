<?php

if($d == null){
	//log error here.
	log_API_error($logger, '200', 'Device id is missing.', $uri, 'api/query_device');
	echo handleAPIResponse(200, 'Device id missing.', '', 'api/query_device');
	exit();
}

if(!is_numeric($d)){
	// log error here
	log_API_error($logger, $status, $msg, $uri, $action);
	echo handleAPIResponse(200, 'Invalid data type. Device must be numeric', '', 'api/query_device');
}

$d = sanitizeDriver($logger, $d, "device", "device");

$sql = 'SELECT * from device where device_id = (?)';

try{
	$res = bindAndExecute($db, $sql, 'i', [$d]);
} catch (Mysqli_SQL_Exception $mse){
	echo handleAPIResponse("DB_ERROR",  'Failed to execute query: ', "");
	exit();
} catch (Exception $e) {
	// log error here
	echo handleAPIResponse("SYS_ERROR", 'Failed to prepare query for device.', "");
	exit();
}

$r = $res->get_result();
$row = $r->fetch_assoc();
$res->close();

if ($row && $row['active']){
	$payload = [
		'id' => $d,
		'device' => $row['device']
	];
	
	echo handleAPIResponse("OK", "Device Found", $payload);
	exit();
}
// device is not active, log output and return not found
else {
	if($row){
		// log operation here
		
	}
	echo handleAPIResponse("OK", "Device Not Found", '', 'api/add_device');
}
?>