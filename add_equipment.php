<?php
	// handle errors and exit if missing
	if($d == null){
		log_API_error($logger, 0, "Device Missing", $url, "POST", "None");
		handleMissingField("device");	
	
	}
	if($c == null){
		log_API_error($logger, 0, "Company Missing", $url, "POST", "None");
		handleMissingField("company");
	}
	if($sn == null){
		log_API_error($logger, 0, "SN Missing", $url, "POST", "None");
		handleMissingField("sn");
	}
	
	// we now need to validate
	if(!is_numeric($d)){
		// log error here
		log_API_error($logger, 0, "Device invalid data type", $url, "POST", "None");
		return handleInvalidType("device", "int");
		
	}
	if(!is_numeric($c)){
		// log error here
		log_API_error($logger, 0, "Company invalid data type", $url, "POST", "None");
		return handleInvalidType("company", "int");
	}
	if(!checkSNLen($sn)){
		// log error here
		log_API_error($logger, 0, "SN length invalid", $url, "POST", "None");
		return;
	}
	
	if(strlen($sn) < 3 || substr($sn, 3) != 'SN-'){
		// log warning here
		$sn = 'SN-'.$sn;
	}

	if(checkSNString($sn) >= 2){
		// log error here
		log_API_error($logger, 0, "SN invalid format", $url, "POST", "None");
		return;
	}

	if(checkUnique($sn)){
		// log error here
		log_API_error($logger, 0, "Attempted to insert already existing SN.", $url, "POST", "None");
		return;
	}

	$d = sanitizeDriver($logger, $d, $url, "add_equipment.php");
	$c = sanitizeDriver($logger, $c, $url, "add_equipment.php");
	$sn = sanitizeDriver($logger, $sn, $url, "add_equipment.php");
	// check if device and company exist
	// TODO

	// insert here 
?>