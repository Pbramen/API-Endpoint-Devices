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
			$active = getField('active');
			include("add_equipment.php");
			break;
		case "add_device":
			$d = getField('d');
			$active = getField('active');
			$name = 'device';
			include("add.php");
			break;
		case "add_company":
			$d = getField('c');
			$active = getField('active');
			$name = 'company';
			include('add.php');
			break;
		case "query_device":
			$d = getField('d');
			$name = "device";
			$active = getField("active");
			include("query.php");
			break;
		case "query_company":
			$d = getField('c');
			$name = "company";
			$active = getField("active");
			include("query.php");
			break;
		case "query_sn":
			$d = getField('sn');
			$name = "sn";
			$active = getField("active");
			include("query.php");
			break;
		case "update_equipment":
			$d = getField('d');
			$c = getField('c');
			$sn = getField('sn');
			$active = getField('active');
			include("update_equipment.php");
			break;
		case 'update_device':
			$d = getField('d');
			$new = getField('new');
			$name = "device";
			$short = 'd';
			$active = getField('active');
			include("update.php");
			break;
		case 'update_company':
			$d = getField('c');
			$new = getField('new');
			$name = "company";
			$short = 'c';
			$active = getField('active');
			include("update.php");
			break;
		case 'search_equip':
			$d = getField('d');
			$c = getField('c');
			$sn = getField('sn');
			$limit = getField('limit');
			$active = getField('active');
			include("search_equip.php");
			break;
		case 'search_one_equip':
			$sn = getField('sn');
			$r = getField('r');
			include("search_one_equip.php");
			break;
		default:
			handleAPIResponse('OK', 'Invalid endpoint', buildPayload(['endPoint' => $endPoint]), 'api/man', $time_start);
			handle_logger("log_API_error", $logger, 200, "Invalid EndPoint", 'api/man', $endPoint,  $time_start );
			break;
	}	


?>
