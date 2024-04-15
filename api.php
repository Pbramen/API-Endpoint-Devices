<?php 
	
	include("./assets/php/loader/loadData.php");
	include("./assets/php/apiHelper.php");

	$url = sanitizeDriver($logger, $_SERVER['REQUEST_URI'], "response URI", "api.php");

	// get endpoint from the url
	$path = parse_url($url, PHP_URL_PATH);
	$pathComponents = explode("/", trim($path, "/"));
	$endPoint = $pathComponents[1] | "";

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
			break;
		case "query_manufacturer":
			break;
		case "query_sn":
			break;
		case "update_equipment":
			break;
		default:
			$output = handleInvalidEndpoint();
			$res = json_encode($output);
			if($res){
				 log_sys_err($logger, 0, 'Inavlid endpoint JSON failed', $url, 'POST', 'None.');
				handleJsonError();
			}
			echo $res;
			break;
	}	


?>
