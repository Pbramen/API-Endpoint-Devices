<?php

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
function buildErrorPayload($args){
	$payload = [
		'Fields' => array()
	];
	foreach($args as $key => $value){
		$payload['Fields'][$key] = $value;
	}
	return $payload;
}

function handle_json_encode($url, $endPoint, $start){
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
	try{
		call_user_func($fn, ...$args);
	} catch (MySQLi_Sql_Exception $mse){
		// mysqli error occured -> log to text file.
		// mysqli connection error occured.
		$date = date('Y-m-d H:i:s');
		error_log("\r\n[$date]: $mse\r\n", 3, $error_log);
	} catch (Exception $e){
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
	// must header and logging elsewhere...
	return $output;
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
function log_API_op($logger, $method, $url, $status, $msg, $start){
	$exec_time = (microtime(true) - $start) / 60;
	$date = date('Y-m-d H:i:s');
	$sql = 'INSERT INTO `api_req` (method, url, date, status, msg, execution_time) VALUES ("'.$method.'", "'.$url.'", "'.$date.'", "'.$status.'", "'.$msg.'", "'.$exec_time.'")';
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