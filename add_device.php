<?php
	// validateAPI by name here..
	if(!mb_detect_encoding($d, 'ASCII ', true) || !mb_detect_encoding($d, 'UTF-8', true)){
		handleAPIResponse(200, "Invalid character set.", "", $endPoint, $time_start);
		exit();
	}
	
	if( strlen($d) >= 32){
		handleAPIResponse(200, "Invalid character set.", "", $endPoint, $time_start);
	}
	sanitizeAlpha($d, $d_sanitized, $extra);
		if(!$d_sanitized){
		handleAPIResponse(200, "Device name should only have alpha characters.", "", $endPoint, $time_start);
		//log data here...
		exit();
	}
	
	
	if($extra){ 
		//handleAPIResponse(200, 'Extra characters: '. implode($extra), "", $endPoint, $time_start);
		// log extra character removed here...
		//exit();
	}

	// insert device if non-existant

	// set active to 1 by default.
	$active = $active | 1;
	$sql = "INSERT INTO `device` (device, active) VALUES (?, ?)";

	try{
		$res = bindAndExecute($db, $sql, 'si', [$d_sanitized, $active]);
		$res->get_results();
		$row = $res->fetch_assoc();
		$res->close();
	
		
		if($row){
		// success...
		handleAPIRsponse(200, "Device added", [ 'Fields' => array('d'=> $d, 'active' => $active)], $endPoint, $time_start );
		exit();
	}
	} catch (Mysqli_sqli_Exception $mse){
		echo $mse;
		exit();
	} catch (Exception $e){
		echo $e;
		exit();
	}



?>