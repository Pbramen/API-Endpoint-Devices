<?php
function validActive($logger, &$active, $endPoint, $time_start){
	if($active === null){
		$active = 1;
		return;
	}
	if($active != 0 && $active != 1){
		handle_logger('log_API_error', $logger, 200, 'Invalid paramater given for active.', 'None taken', $endPoint, $time_start);
		handleAPIResponse(200, "Invalid active param.", "", $endPoint, $time_start);
		exit();
	}
}

function minRange($entry, $limit){
	return $entry >= $limit ? $entry : $limit;
}

function maxRange($entry, $limit){
	return $entry < $limit ? $entry : $limit;
}

function validPagination($logger, &$entry, $fn, $limit, $name, $endPoint, $time_start){
	if($entry === null){
		$entry = $limit; 
	}
	else if( is_numeric($entry) ){
		$entry = call_user_func($fn, $entry, $limit);
	}
	else{
		handle_logger('log_API_error', $logger, 200, 'Invalid '.$name.' data type'. $type, 'None taken', $endPoint, $time_start);
		handleAPIResponse(200, "Invalid data type.", "", $endPoint, $time_start);
		exit();
	}
}


function validResponse($json, $msg){
	return isset($json['Status']) && $json['Status'] == 200 && isset($json['MSG']) && $json['MSG'] == $msg;
}

function __validate($d, $logger, $name, $endPoint, $time_start){
	if($d === null){	
		handle_logger('log_API_error', $logger, 200, $name.' is missing.', $name, $endPoint, $time_start);
		handleAPIResponse(200, "Missing $name", '', $endPoint, $time_start);
		exit();
	}
	if(!mb_detect_encoding($d, "ASCII", true) || !mb_detect_encoding($d, "UTF-8", true)){
		handle_logger('log_API_error', $logger, 200, 'Detected invalid encoding for  '.$name, 'api/query_'.$name, $endPoint, $time_start);
		handleAPIResponse(200, "Invalid character encoding.", buildPayload(['expected' => ["ASCII", "UTF-8"]]), $endPoint, $time_start);
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
		handleAPIResponse(200, "Max length exceeded", buildPayload([$short=>$d_sanitized, 'maxLength'=>$size, 'exceededLength'=> $n]), $endPoint, $time_start, 'api/query_'.$name);
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
	__validate($d, $logger, $name, $endPoint, $time_start);

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

?>