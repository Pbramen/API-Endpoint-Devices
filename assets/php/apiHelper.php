<?php

// error handler for invalid endpoint requests
// does NOT log data 
function handleInvalidEndpoint(){
	header('Content-type: application/json');
	header('HTTP/1.1 200 OK');
	$output[] = 'Status: ERROR';
	$output[] = 'MSG: Invalid Endpoint';
	$output[] = 'Action: None';
	return $output;
}

// error handling for json_encode()
function handleJsonError(){
	$output[] = 'Status: ERROR';
	$output[] = 'MSG: ('.json_last_error().'): '.json_last_error_msg();
	$output[] = 'Action: None';
	$res = json_encode($output);
	echo $res;
	exit();
}

// handle missing field 
function handleMissingField($value){
	header('Content-type: application/json');
	header('HTTP/1.1 200 OK');
	$output[] = 'Status: ERROR';
	$output[] = 'MSG: Missing '.$value;
	$output[] = 'Action: query_'.$value;
	$responseData = json_encode($output);
	echo $responseData;
	exit();
}

function handleInvalidType($value, $expected){
	header('Content-type: application/json');
	header('HTTP/1.1 200 OK');
	$output[] = 'Status: ERROR';
	$output[] = 'MSG: Invalid type. Expected '.$expected.' for '. $value;
	$output[] = 'Action: query_'.$value;
	$responseData = json_encode($output);
	return $responseData;
}

// uri should be santizied and validated before inserting. 
// must be wrapped in a try/catch block for mysqli_sql_exception.
function log_API_error($logger, $err_code, $err_msg, $uri, $type, $action){
	$date = date('Y-m-d H:i:s');
	$sql = 'Insert into `api_error` (err_code, err_msg, uri, type, action, date) VALUES ("'.$err_code.'", "'.$err_msg.'", "'.$uri.'", "'.$type.'", "'.$action.'", "'.$date.'")';
	$logger->query($sql);
}

// same as log_api_error, but for successful api calls.
function log_API_op($logger, $method, $url, $status, $msg){
	$date = date('Y-m-d H:i:s');
	$sql = 'INSERT INTO `api_req` (method, url, date, status, msg) VALUES ("'.$method.'", "'.$url.'", "'.$date.'", "'.$status.'", "'.$msg.'")';
	$logger->query($sql);
}
?>