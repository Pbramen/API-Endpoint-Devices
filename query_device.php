<?php

if($d == null){
	//log error here.
	echo handleAPIResponse(200, 'Device id missing.', '', 'api/query_device');
	exit();
}

if(!is_numeric($d)){
	// log error here
	echo handleAPIResponse(200, 'Invalid data type. Device must be numeric', '', 'api/query_device');
}

$d = sanitizeDriver($logger, $d, "device", "device");
$d_output = [];

$sql = 'SELECT * from device where device_id = (?)';
try{
	$res = bindAndExecute($db, $sql, 'i', [$d]);
} catch (Mysqli_SQL_Exception $mse){
	// log error here

	
	exit();
} catch (Exception $e) {
	// log error here
	$d_output[] = "Status: ERROR";
	$d_output[] = 'MSG: '.$msg.message;
	
	exit();
}

$r = $res->get_result();
$row = $r->fetch_assoc();
$res->close();

if ($row){
	$payload = [
		'id' => $d,
		'device' => $row['device']
	];
	
	echo handleAPIResponse(200, "Device Found", $payload);
	exit();
}

?>