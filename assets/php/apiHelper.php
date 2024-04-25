<?php

function __validate($d, $logger, $name, $endPoint, $time_start){
	if($d == null){	
		handle_logger('log_API_error', $logger, 200, $name.' is missing.', $name, $endPoint, $time_start);
		handleAPIResponse(200, "Missing $name", '', $endPoint, $time_start);
		exit();
	}

	if(!mb_detect_encoding($d, 'ASCII ', true) || !mb_detect_encoding($d, 'UTF-8', true)){
		$encoding = mb_detect_encoding($d);
		handle_logger('log_API_error', $logger, 200, 'Detected invalid encoding for  '.$name.' : '.$encoding, 'api/query_'.$name, $endPoint, $time_start);
		handleAPIResponse(200, "Invalid character encoding.", buildPayload(['encoding' => $encoding]), $endPoint, $time_start);
		exit();
	}

}


function validateAndSanitize($d, $logger, $name, $short, $endPoint, $time_start, $size = 32, $fn = 0){
	// validateAPI by name here..
	__validate($d, $logger, $name, $endPoint, $time_start);
	
	sanitizeAlpha($d, $d_sanitized, $extra, $fn);
	if(!$d_sanitized){
		handle_logger('log_API_error', $logger, 200, 'No alpha characters detected in .'.$name, 'api/query_'.$name, $endPoint, $time_start);
		handleAPIResponse(200, "$name name should only have alpha characters.", "", $endPoint, $time_start);
		exit();
	}
	
	$n = strlen($d);
	if( $n >= $size){
		handle_logger('log_API_error',$logger, 200, $name.' length '.$n.' exceeded limit of '.$size, 'api/query_'.$name, $endPoint, $time_start);
		handleAPIResponse(200, "Max length of $name exceeded limit of $size.", buildPayload([$short=>$d_sanitized, 'length'=>$n]), $endPoint, $time_start, 'api/query_'.$name);
		exit();
	}

	if($extra){ 
		$msg = '';
		foreach($extra as $key => $value){
			$msg .= $key.': '.$value.', ';
		}
		$msg = rtrim($msg, ', ');
		handle_logger('log_API_error', $logger, 200, 'Warning: removed '.$msg, 'Continued operation.', $endPoint, $time_start);
	}
	return $d_sanitized;
}

// TODO add search by index or name for sn.
function validateAPI($logger, $d, $name, $endPoint, $action, $time_start){
		if($d == null){
		//log error here.
		handle_logger("log_API_error", $logger, 200, "$name is missing", $endPoint, $action, $time_start);
		handleAPIResponse(200, "Missing param", "", $endPoint, $time_start);
		exit();
	}

	if(!is_numeric($d)){
		handle_logger("log_API_error", $logger, 200, 'Invalid '.$name.'_id data type.', $endPoint, $action, $time_start);
		handleAPIResponse(200, "$name must be digits only.", buildPayload([ $name[0] => $d]), $endPoint, $time_start);
		exit();
	}
	// maybe overkill...
	$d = sanitizeDriver($logger, $d, $endPoint, $name);
}

function getField($name){
	// for json:
	$res = null;
	$d = file_get_contents("php://input");
			$d = json_decode($d);
			// get from object d
			if(isset($d->{$name}))
				$res = $d->{$name};
			// check if params was set instead
			else if(isset($_REQUEST[$name])){
				$res = $_REQUEST[$name];
			}
	return $res;
}

// Builds a payload array
// $args is an assoc array containing fields and (santitized) values.s
function buildPayload($args){
	$payload = [
		'Fields' => array()
	];
	foreach($args as $key => $value){
		$payload['Fields'][$key] = $value;
	}
	return $payload;
}

function handle_json_encode($url, $endPoint, $start){
	$error_log = '/var/www/html/api/logs/mysqli_error.txt';
	try{
		log_sys_err($logger, json_last_error_msg(), $url, "", 'JSON', "None taken", $start);
	} catch (MySQLi_Sql_Exception $mse){
		// mysqli error occured -> log to text file.
		error_log("\r\n$mse\r\n", 3, $error_log);
	} finally {
		header('Content-type: text/html; charset=utf-8');
		echo 'Error Code: ('.json_last_error().'). JSON Encoding failed. Try again later';
	}
}
// callback function to handle all logging functions. 
function handle_logger($fn, ...$args){
	$error_log = '/var/www/html/api/logs/mysqli_error.txt';
	try{
		call_user_func($fn, ...$args);
	} catch (MySQLi_Sql_Exception $mse){
		// mysqli error occured -> log to text file.
		// mysqli connection error occured.
		echo $mse;
		$date = date('Y-m-d H:i:s');
		error_log("\r\n[$date]: $mse\r\n", 3, $error_log);
	} catch (Exception $e){
		echo $e;
		$date = date('Y-m-d H:i:s');
		error_log("\r\n[$date]: $e\r\n", 3, $error_log);
	}
}
// Builds a json response with a payload
function handleAPIResponse($status, $msg, $payload, $url, $start, $next = "None Taken"){

	$output = [
		'Status' => $status,
		'MSG' => $msg,
		'Payload' => $payload,
		'Action' => $next
	];
	$output =  json_encode($output);
	
	if(!$output){
		handle_json_encode($url, "Unable to encode JSON.", $url, $start);
		exit();
	}
	header("Content-type: application/json");
	header('HTTP/1.1 200 OK');
	echo $output;
}


// uri should be santizied and validated before inserting. 
// must be wrapped in a try/catch block for mysqli_sql_exception.
function log_sys_err($logger, $msg, $uri, $stack_trace, $type, $action, $start){
	$date = date('Y-m-d H:i:s');
	$exec_time = (microtime(true) - $start) / 60;
	$sql = 'Insert into `sys_api_error` (action, date, message, uri, stack_trace, type, execution_time) VALUES ("'.$action.'", "'.$date.'", "'.$msg.'","'.$uri.'", "'.$stack_trace.'",  "'.$type.'", "'.$exec_time.'")';
	$logger->query($sql);
}

// Logs operation if failed.
function log_API_error($logger, $status, $msg, $action, $url, $start){
	$method = $_SERVER['REQUEST_METHOD'];
	if($method == null){
		$method = 'N/A';
	}
	$method = sanitizeDriver($logger, $method, "API", $url);
	
	$exec_time = (microtime(true) - $start) / 60;
	$date = date('Y-m-d H:i:s');
	$sql = 'INSERT INTO `api_error` (status, err_msg, method, uri, action, date, execution_time)
	VALUES ("'.$status.'", "'.$msg.'", "'.$method.'", "'.$url.'", "'.$action.'", "'.$date.'", "'.$exec_time.'")';
	$logger->query($sql);
}

// same as log_api_error, but for successful api calls.
function log_API_op($logger, $url, $status, $msg, $start){
	$method = $_SERVER['REQUEST_METHOD'];
	if($method == null){
		$method = 'N/A';
	}
	$method = sanitizeDriver($logger, $method, "API", $url);
	
	$exec_time = (microtime(true) - $start) / 60;
	$date = date('Y-m-d H:i:s');
	$sql = 'INSERT INTO `api_req` (method, url, date, status, msg, execution_time) VALUES ("'.$method.'", "'.$url.'", "'.$date.'", "'.$status.'", "'.$msg.'", "'.$exec_time.'")';
	$logger->query($sql);
}

// reusable curl init function for api endpoint calls 
// MUST be wrapped in try/catch and microtime
function curl_POST($endpoint, $data, $logger, $url){
	
	$ch = curl_init("https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/api/".$endpoint);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DANGEROUS ignore self-signed ssl.
	curl_setopt($ch, CURLOPT_POST, 1); // post
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // fill data
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // response
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/x-www-form-urlencoded', 'content-length: '.strlen($data))); // content type and length of data sent by requester

	$result = curl_exec($ch);
	if($result == false){
		//log_API_error($logger, curl_errno($ch), curl_error($ch), $url, "POST", "None");
		echo 'Curl error ('.curl_errno($ch).'): '.curl_error($ch);
		exit();
	}
	curl_close($ch);
	//echo $result;
	return $result;
}

// TODO curl_JSON 


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
}

/*
	Handle output from curl to query endpoints. 
	@param {Object} - JSON response from curl
	@param {String} - attribute that was queried.
*/
function handleQueryEndpoint($json, $type){
		if(isset($json["Status"]) && isset($json["MSG"])){
		if($json["Status"] == "ERROR"){
			//handle error here
			$output[] = "Status: Missing $type";
			$response = json_encode($output);
			if(!$response) {
				// log sys error here
				handleJsonError();
				exit();
			}
			// log api error here
			echo $output;
			exit();
		}
	}
}

?>