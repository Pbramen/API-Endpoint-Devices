<?php

	// validate the correct size of sn from user input
	function checkSNLen($sn){
		return strlen($sn) <= 81;
	}

	// check if sn is in the correct format. Pre-append suffix if needed. 
	function checkSNString(&$sn){
		$err= 0;
	
		$res = preg_match("/^[a-fA-F\d]+$/i", substr($sn, 3), $matches, PREG_OFFSET_CAPTURE);
		if (!$res){
			//invalid hexstring
			$err += 2;
		}
		return $err;
	}
	
	// check from csv file if device/company is in correct format. 
	// adjusts format if possible. 
	function checkAlpha($string, &$res){
		$n = strlen($string);
		$res = '';
		$r = false;
		for ($i = 0; $i < $n; $i+=1){
			if((ctype_alpha($string[$i]) || $string[$i] == ' ')){
				$res .= $string[$i];
			}else{
				$r = true;
			}
		}
		return $r;
	}

	/** driver function for data type validation
		* params $c (value to be checked)
		* params $type (expected data type of $c)
		* params $value (variable name (for logging purposes))
			* returns array ['msg'] = 'error message' or 'ok' upon success
	*/ 
	function checkDataType($c, $type, $value){
		return gettype($c) == $type;
	}

	
	/** Queries database if sn already exists. Uses prepared statements for sql injection
	*   	params $dblink (mysqli object) Current connection
	*		params $sn (string) serial number to check 
	* 			returns true if not in database, else false.
	*/	
	function checkUnquie($dblink, $sn){
		
		$res = $dblink->prepare('Select sn_id from `sn` where sn=(?)');
		if(!$res){
			throw new Exception("Unable to query for sn. $dblink->error");
		}
		if(!$res->bind_param("s", $sn)){
			throw new Exception("Unable to bind parameters for prepared statment for unique sn. $res->error");
		}
		if(!$res->execute()){
			throw new Exception("Unable to execute prepared statement for unique sn. $res->error");
		}
		$res->store_result();
		$result = $res->fetch();
		if(!$result && $dblink->errno != 0){
			throw new Exception("Unable to fetch prepared statement for unique sn. $dblink->error");
		}
		$count = $res->num_rows;
		$res->close();
		if ($count == 1){
			return false;
		}
		return true;
	}

	
	/** Attempts to insert new SN from web endpoint. 
	*   	params $dblink (mysqli object) Current connection
	*		params $sn (string) serial number to check 
	*			throws error upon failure.
	* 			returns last inserted ID for sn. 
	*/	
	function insertSN($dblink, $sn){
		$sn_id;
		$res = $dblink->prepare("Insert into `sn` (sn) VALUES (?)");
		if(!$res){
			throw new Exception("Unable to query for sn. $dblink->error");
		}
		if(!$res->bind_param("s", $sn)){
			throw new Exception("Unable to bind parameters for prepared statmeent for sn create. $res->error");
		}
		if(!$res->execute()){
			throw new Exception("Unable to execute prepared statement for sn create. $res->error");
		}
		$sn_id = $dblink->insert_id;
		if($sn_id == 0){
			throw new Exception("Unable to find last inserted id for sn.$dblink->error");
		}
		return $sn_id;
	}

	/** Attempts to insert new relation from web endpoint. 
	*   	params $dblink (mysqli object) Current connection
	*		params $device (string) id of device
	*		params $company (string) id of serial number
	*		params $sn (string) serial number to check 
	*			throws error upon failure.
	* 			returns last inserted ID for sn. 
	*/	
	function insertRelation($db, $device, $company, $sn_id){
		$res = $db->prepare("Insert into `relation` (sn_id, device_id, company_id) VALUES (?, ?, ?)");
		if(!$res){
			throw new Exception("Unable to query for sn. $dblink->error");
		}
		if(!$res->bind_param("sii", $sn_id, $device, $company)){
			throw new Exception("Unable to bind parameters for prepared statmeent for relation sn. $res->error");
		}
		if(!$res->execute()){
			throw new Exception("Unable to execute prepared statement for sn create. $res->error");
		}
	}

	/**
	* No Need to use prepared statmenets since this is hardcoded.
	* Returns the total number of records in relation. 
	* throws exception if query fails.
	*/
	function countMaxRecords($db){
		$res = $db->query("SELECT COUNT('device_id') from `relation`");
		if(!$res){
			throw new Exception("Unable to query count. $db->error");
		}
		$row = $res->fetch_row();
		if(!$row && $db->errno != 0){
			throw new Exception("Unable to fetch results. $db->error");
		}
		return $row[0];
	}
	

	/**
	* Function to set up prepare statement using all selected filters.
	* 	$params (assoc array that contains all filters) as reference
	*	$table (table the filter is from)
	* 	$type (data type of filter)
	* 	$name (name of filter as specificed in database)
	* 
	*/
	function prepareFilters(&$params, $table, $type, $name, &$value){
		$field = "$table.$name";
		$params["attribute"] .= "$field, ";
		$params["p"] .= "$field = (?) AND ";
		$params["bind"] .= "$type";
		array_push($params["values"], $value);
	}

	/**
	* Call this function to build the prepared statement. DO NOT CALL if $params["p"] is empty.
	* $param (array of values to build from);
	*/
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

	// grabs the unique id from specified table
	function grabID($db, $val, $type, $attribute, $table){
		$id = $attribute.'_id';
		$sql = "SELECT $id from `$table` WHERE $table.$attribute = (?)";
		echo $sql;
		
		return bindAndExecuteSelect($db, $sql, $type, [$val]);
	}


	// builds the string for counting total number of results in relation table
	function buildCountString($d, $c, $sn_id, &$p){
		$a = "";
		$p= array();
		$p["bind"] = "";
		$p['val'] = array();
		if($d != 0){
			$a .= "device_id = (?) AND ";
			$p['val'][] = $d;
			$p["bind"] .= "i";
		}
		if($c != 0){
			$a .= "company_id = (?) AND ";
			$p['val'][] = $c;
			$p["bind"] .= "i";
		}
		if($sn_id != 0){
			$a .= "sn_id = (?) AND ";
			$p['val'][] = $sn_id;
			$p["bind"] .= "i";
		}
		$a = rtrim($a, "AND ");
		$res = 'SELECT COUNT(device_id) FROM relation WHERE '.$a;
		return $res;
		
	}

	// binds and executes parameters for prepared statements
	function bindAndExecute($db, $sql, $bind, $args){
		
		$res = $db->prepare($sql);
		if(!$res){
			throw new Exception("Unable to prepare $sql query. $db->errno: $db->error");
		}
		if(!$res->bind_param($bind, ...$args)){
			throw new Exception("Unable to bind $bind to $sql. $res->errno: $res->error ");
		}
		if(!$res->execute()){
			throw new Exception("Unable to execute $sql. $res->errno: $res->error");
		}
		return $res;
	}

	// binds and executes parameters for select statements expecting multiple results.
	function bindAndExecuteSelect($db, $sql, $bind, $args){
	
		$res = bindAndExecute($db, $sql, $bind, $args);
		$r = array();
		$result = $res->get_result();
	
		if($result && $res->errno == 0){
			while ($row = $result->fetch_array(MYSQLI_ASSOC)){
				array_push($r, $row);
			}
		}
		else{
			throw new Exception("Unable to grab results search query. $res->error");
		}
		$res->close();
		return $r;
	}

	// sanitize user input
	function sanitize($a){
		return htmlspecialchars(addslashes($a));
	}

	// driver for sanitizing. Logs warning operation if sanitization occurs.
	function sanitizeDriver($logger, $a, $source, $file){
		$b = sanitize($a);
		if($b != $a){
			$logger->insertSysErr("Sanitized user input to $b from $source", $file);		
		}
		return $b;
	}

	// (NOT IN USE) Creates a sql prepared statement to update SN. 
	function updateSN($db, $old, $new){
		$sql = "UPDATE sn SET sn = (?) where sn = (?)";
		return bindAndExecute($db, $sql, "ss", [$new, $old]);
	}

	// logs operation from webendpoint. 
	function logOperation($db, $endpoint, $descript, $method, $src = "web"){
		$sql = "Insert into operation (src, webpoint, date, descript, method) VALUES (?, ?, ?, ?, ?)";
		$date = date('Y-m-d H:i:s');
		return bindAndExecute($db, $sql, 'sssss', [$src, $endpoint, $date, $descript, $method]);
	}

?>