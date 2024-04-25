<?php
	$active = $active | 1;
	//validate active here...

	if(!$limit){
		$limit = 1000;
	}
	else if( is_numeric($limit) ){
		$limit = $limit < 1000 ? $limit : 1000;
	}
	else{
		// log error here and exit
	}
	
	$params = array('attribute' => "",
					'p' => "",
					'bind' => "",
					'values' => array()
				   );
	$empty = array();
	// validate all other inputs here if not null
	if($sn){
		// validate and sanitize sn
		if(strlen($sn) > 3 && substr($sn, 0, 3) == "SN-"){
			$sn = substr($sn, 3);
		}
		$sn_sanitized =  validateAndSanitize($sn, $logger, 'sn', 'sn', $endPoint, $time_start, 84, 1);
		try{
			$sql = "Select sn_id from `sn` where sn = (?)";
			$res = bindAndExecute($db, $sql, "s", ['SN-'.$sn_sanitized]);
			$r = $res->get_result();
			$row = $r->fetch_assoc();
			
			if(!$row){
				handle_logger('log_API_error');
				handleAPIResponse();
				exit();
			}
			$sn_id = $row['sn_id'];
		} catch (Mysqli_sql_exception $mse){
			//
			echo $mse;
			exit();
		} catch (Exception $e){
		 	//
			echo $e;
			exit();
		}
		prepareFilters($params, "sn", "i", "sn", $sn_id );
	}
	else{
		$empty[] = "sn";
	}
	if($d){
		validateAPI($logger, $d, "device", $endPoint, $endPoint, $time_start);
		prepareFilters($params, "device", "i", "device_id", $d);
		
		// 
	}
	else {
		$empty[] = "d";
	}
	if($c){
		validateAPI($logger, $c, "company", $endPoint, $endPoint, $time_start);
		prepareFilters($params, "company", "i", "company_id", $c);
	}
	else{
		$empty[] = "c";
	}
	if(count($empty) == 3){
		handleAPIResponse(200, "Missing Parameters.", "", 'api/search_equip', $time_start);
		handle_logger('log_API_error', $logger, 200, 'No parameters given.', 'None taken', $endPoint, $time_start);
		exit();
	}
	
	exit();
	// query with limit


	
/*

	function buildString(&$params){
		if($params['attribute'] != "") {
			$res = 'SELECT sn.sn, relation.r_id, relation.device_id, relation.company_id FROM relation JOIN `sn` ON sn.sn_id = relation.sn_id WHERE '.$params['p'];
			$res = rtrim($res, "AND ");
		}
		else{
			$res =  "Select sn.sn, relation.device_id, relation.company_id FROM `relation` JOIN sn ON sn.sn_id=relation.sn_id";
		}
		$res .= " LIMIT ? OFFSET ?";
		$params["bind"] .= "ii";
		return $res;
	} 
*/
?>