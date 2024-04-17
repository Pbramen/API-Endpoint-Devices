<?php

if($d == null){
	//log error here.

}

if(!is_numeric($d)){
	
}

$d = sanitizeDriver($logger, $d, $endPoint, "device");

$sql = 'SELECT * from device where device_id = (?)';

try{
	$res = bindAndExecute($db, $sql, 'i', [$d]);
} catch (Mysqli_SQL_Exception $mse){

	
} catch (Exception $e) {
	// log error here
	
}

$r = $res->get_result();
$row = $r->fetch_assoc();
$res->close();

if ($row && $row['active']){
	$payload = [
		'id' => $d,
		'device' => $row['device']
	];
	
	
	exit();
}
// device is not active, log output and return not found
else {
	if($row){
		// log operation here
		
	}
	
}
?>