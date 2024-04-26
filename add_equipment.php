<?php

//	// handle errors and exit if missing	
//	$c_json = curl_POST("query_company", "c=$c&active=$active", $logger, $url);
//	$d_json = curl_POST("query_device", "d=$d&active=$active", $logger, $url);
//	$sn_json = curl_POST("query_sn", "sn=$sn&active=$active", $logger, $url);
//
//	// convert into assoc_array...
//	try {
//		$c_json = json_decode($c_json, true);
//		$d_json = json_decode($d_json, true);
//		$sn_json = json_decode($sn_json, true);
//		
//		// validate json data type
//		if($c_json == null){
//			handle_logger('log_API_error', $logger, json_last_error(), 'Invalid company json format: '.json_last_error_msg(), 'add_equipment.php', $endPoint, $time_start);
//			handleAPIResponse(200, 'Invalid JSON format.', '', $endPoint, $time_start, 'add_equipment.php');
//			exit();
//		}
//		if($d_json == null){
//			handle_logger('log_API_error', $logger, json_last_error(), 'Invalid device json format: '.json_last_error_msg(), 'add_equipment.php', $endPoint, $time_start);
//			handleAPIResponse(200, 'Invalid JSON format.', '', $endPoint, $time_start, 'add_equipment.php');
//			exit();
//		}
//		if($sn_json == null){
//			handle_logger('log_API_error', $logger, json_last_error(), 'Invalid sn json format: '.json_last_error_msg(), 'add_equipment.php', $endPoint, $time_start);
//			handleAPIResponse(200, 'Invalid JSON format.', '', $endPoint, $time_start, 'add_equipment.php');
//			exit();
//		}
//		
//	} catch (Exception $e){
//		handle_logger('log_API_error', $logger, 200, 'Other exception for json:'.$e->getMessage(), 'add_equipment.php', $endPoint, $time_start);
//		handleAPIResponse(200, 'Other Exception: '.$e->getMessage(), '', $endPoint, $time_start, 'add_equipment.php');
//		exit();
//	}
//	
//	$c_valid = validResponse($c_json, "Success");
//	$d_valid = validResponse($d_json, "Success");
//	$sn_valid = validResponse($sn_json, "DNE");
//
//	if($c_valid && $d_valid && $sn_valid){
//		echo "equipment can be inserted!";
//		exit();
//	}
//	else{
//		echo "c is $c_valid: d is $d_valid: sn is $sn_valid";
//		exit();
//	}

	$res = curl_POST("search_equip", "d=$d&c=$c&sn=$sn&active=$active&limit=1", $logger, $url);
//	if($res == null){
//		//handle_logger('log_API_error', $logger, json_last_error(), 'Invalid json format: '.json_last_error_msg(), 'add_equipment.php', $endPoint, $time_start);
//		handleAPIResponse(200, 'Invalid JSON format.', '', $endPoint, $time_start, 'add_equipment.php');
//		exit();
//	}
//	
//	header("Content-type: application/json");
//	echo $res;
//	exit();
	echo $res;
	function validResponse($json, $msg){
		return isset($json['Status']) && $json['Status'] == 200 && isset($json['MSG']) && $json['MSG'] == $msg;
	}

?>