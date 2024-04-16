<?php 
	
	include("./assets/php/loader/loadData.php");
	include("./assets/php/apiHelper.php");

	$url = sanitizeDriver($logger, $_SERVER['REQUEST_URI'], "response URI", "api.php");

	// get endpoint from the url
	$path = parse_url($url, PHP_URL_PATH);
	$pathComponents = explode("/", trim($path, "/"));
	if(count($pathComponents) >= 2)
		$endPoint = $pathComponents[1];
	else{
		$endPoint = "";
	}

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
			$d = file_get_contents("php://input");
			$d = json_decode($d);
			if(isset($d->{'d'}))
				$d = $d->{'d'};
			
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
			
			$output = handleInvalidEndpoint();
			$res = json_encode($output);
			if(!$res){
				log_sys_err($logger, 0, 'Inavlid endpoint JSON failed', $url, 'POST', 'None.');
				handleJsonError();
			}
			echo $res;
			break;
	}	


?>
