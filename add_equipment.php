<?php

	// handle errors and exit if missing	
	$c_json = curl_POST("query_company", "c=$c&active=$active", $logger, $url);
	$d_json = curl_POST("query_device", "d=$d&active=$active", $logger, $url);
	$sn_json = curl_POST("query_sn", "sn=$sn&active=$active", $logger, $url);

	// convert into assoc_array...
	try {
		$c_json = json_decode($c_json, true);
		$d_json = json_decode($d_json, true);
		$sn_json = json_decode($sn_json, true);

		if($c_json == null){
			//invalid data type.
			//log and exit here
		}
		if($d_json == null){
			//invalid data type.
			//log and exit here
		}
		if($sn_json == null){
			//invalid data type.
			//log and exit here
		}
		
	} catch (Exception $e){
		echo json_last_error_msg();
		exit();
	}

?>