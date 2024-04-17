<?php 
	
	include("./assets/php/loader/loadData.php");
	include("./assets/php/apiHelper.php");

	$url = sanitizeDriver($logger, $_SERVER['REQUEST_URI'], "response URI", "api.php");
	// error log for when mysqli connection fails.
	$error_log = '/home/ubuntu/log/mysql_error.txt';
	
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
			$d = $_REQUEST['d'];
			$c = $_REQUEST['c'];
			$sn = $_REQUEST['serialnumber'];
			include("add_equipment.php");
			break;
		case "add_device":
			break;
		case "add_manufacturer":
			break;
		case "query_device":
			// check for json/param for POST/GET
			$d = getField('d');
			
			include("query_device.php");
			break;
		case "query_manufacturer":
			$c = $_REQUEST['c'];
			include("query_company");
			break;
		case "query_sn":
			$sn = $_REQUEST['sn'];
			include("query_sn");
			break;
		case "update_equipment":
			break;
		default:
			$output = handleAPIResponse('OK', 'Invalid endpoint', buildErrorPayload(['endPoint' => $endPoint]), 'api/man', $time_start);
			handle_logger("log_API_error", $logger, 200, "Invalid EndPoint", 'api/man', $endPoint,  $time_start );
			header("Content-type: application/json");
			header('HTTP/1.1 200 OK');
			// log here
			echo $output;
			
	}	


?>
