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
function log_sys_err($logger, $err_code, $err_msg, $uri, $type, $action){
	$date = date('Y-m-d H:i:s');
	$sql = 'Insert into `sys_api_error` (err_code, err_msg, uri, type, action, date) VALUES ("'.$err_code.'", "'.$err_msg.'", "'.$uri.'", "'.$type.'", "'.$action.'", "'.$date.'")';
	$logger->query($sql);
}

// Logs operation if failed.
function log_API_error($status, $msg, $metehod, $uri, $action, $protocol){
	$date = date('Y-m-d H:i:s');
	$sql = 'INSERT INTO `api_error` (status, err_msg, method, uri, action, date, protocol)
	VALUES ("'.$status.'", "'.$msg.'", "'.$method.'", "'.$uri.'", "'.$action.'", "'.$date.'", "'.$protocol.'")';
}

// same as log_api_error, but for successful api calls.
function log_API_op($logger, $method, $url, $status, $msg){
	$date = date('Y-m-d H:i:s');
	$sql = 'INSERT INTO `api_req` (method, url, date, status, msg) VALUES ("'.$method.'", "'.$url.'", "'.$date.'", "'.$status.'", "'.$msg.'")';
	$logger->query($sql);
}

// reusable curl init function for api endpoint calls 
// MUST be wrapped in try/catch and microtime
function curl_POST($endpoint, $data, $logger, $url){
	
	$ch = curl_init($endpoint);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DANGEROUS ignore self-signed ssl.
	curl_setopt($ch, CURLOPT_POST, 1); // post
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // fill data
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // response
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/x-www-form-urlencoded','content-length: '.strlen($data))); // content type and length of data sent by requester

	$result = curl_exec($ch);
	if($result == false){
		log_API_error($logger, curl_errno($ch), curl_error($ch), $url, "POST", "None");
		echo 'Curl error ('.curl_errno($ch).'): '.curl_error($ch);
		exit();
	}
	curl_close($ch);
	echo $result;
}

// calls the endpoint to query if record exists. 
function check_Unique($endpoint, $field, $logger, $url){
	$d_json = curl_POST($endpoint, $field, $logger, $url);
	
	if($d_json){
		if (isset($d_json['MSG']) && $d_json['MSG'] == 'Status: NOT FOUND'){
			//log error here
			
			//send back json with status message. 
			exit();
		}
	}
	else{
		// log json error here
		//log_API_error($logger, json_last_error(), json_last_error_msg(), $url, "JSON", "Try again");
		handleJsonError();
	}
	

?>