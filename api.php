<?php 
	
	include("./assets/php/loader/loadData.php");
	include("./assets/php/apiHelper.php");

	$url = sanitizeDriver($logger, $_SERVER['REQUEST_URI'], "response URI", "api.php");
	// error log for when mysqli connection fails.

	
	// get endpoint from the url
	$path = parse_url($url, PHP_URL_PATH);
	$pathComponents = explode("/", trim($path, "/"));
	if(count($pathComponents) >= 2)
		$endPoint = $pathComponents[1];
	else{
		$endPoint = "";
	}
	
	$time_start= microtime(true);
	switch($endPoint){
		case "add_equipment":
			$d = getField('d');
			$c = getField('c');
			$sn = getField('sn');
			include("add_equipment.php");
			break;
		case "add_device":
			$d = getField('d');
			$active = getField('active');
			include("add_device.php");
			break;
		case "add_company":
			$c = getField('c');
			$active = getField('active');
			break;
		case "query_device":
			$d = getField('d');
			include("query_device.php");
			break;
		case "query_company":
			$c = getField('c');
			include("query_company.php");
			break;
		case "query_sn":
			$sn = getField('sn');
			include("query_sn");
			break;
		case "update_equipment":
			$d = getField('d');
			$c = getField('c');
			$sn = getField('sn');
			include("update_equipment.php");
			break;
		case 'updgrade_device':
			$d = getField('d');
			include("update_device.php");
			break;
		case 'upgrade_company':
			$c = getField('c');
			include("update_company");
			break;
		case 'upgrade_sn':
			$sn = getField('sn');
			include("update_sn");
			break;
		case 'search_equip':
			$d = getField('d');
			$c = getField('c');
			$sn = getField('sn');
			$limit = getField('limit');
			$active = getField('active');
			include("search_equip");
			break;
		default:
			$output = handleAPIResponse('OK', 'Invalid endpoint', buildErrorPayload(['endPoint' => $endPoint]), 'api/man', $time_start);
			handle_logger("log_API_error", $logger, 200, "Invalid EndPoint", 'api/man', $endPoint,  $time_start );
			
	}	


?>
