<?php
	// id given
	if($active == 1 || $active == null){
		$active = 'AND active = 1';
	}
	else{
		$active = '';
	}

	//d queries by id
	if(is_numeric($d) && ($name == 'device' || $name == 'company')){
		//validates and sanitizes $d. Exits if invalid.
		
		if($d == 0){
			if($active){
				$active = "Where active = 1";;
			}
			$sql = 'Select * from '.$name .' '. $active;
			
			try{
				$res = $db->query($sql);
				$payload = array();
				while($row = $res->fetch_array(MYSQLI_ASSOC)){
					array_push($payload, ['id' => $row[$name.'_id'], 'value' => $row[$name], 'active' => $row['active'] ] );
				}
				if($payload){
					handle_logger("log_API_op", $logger, $endPoint, '200', "$name($d) queried.", $time_start );
					handleAPIResponse(200, 'Success', buildPayload($payload), $endPoint, $time_start);
					exit();
				}
				else{
					handleAPIResponse(200, "DNE", '', 'api/modify_equipment', $time_start);
					handle_logger('log_API_error', $logger, 200, 'No results found', $endPoint, $endPoint, $time_start);
					exit();	
				}
			} catch (Mysqli_sql_exception $mse){
				handle_logger("DB_ERROR", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE:'.$mse->getCode(), 'None taken.', $time_start );
				handleAPIResponse(500, 'Unable to query database.', '', $endPoint, $time_start);
				exit();
			} catch (Exception $e){
				handle_logger("DB_ERROR", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'E:'. $e->getCode(), 'None taken.', $time_start );
				handleAPIResponse(500, 'Unable to query database.', '', $endPoint, $time_start);
				exit();
			}
			
		}
		validateAPI($logger, $d, $name, $endPoint, $endPoint, $time_start);
		$sql = 'SELECT * from '.$name.' where '.$name.'_id = (?) '. $active;
		queryBy($db, $logger, $sql, "i", $d, $time_start, $endPoint, $name);;
		exit();
	}
	// check for string value -> return with id if exists.! 
	else {
		if($name == 'sn'){
			//validate for sn 
			$fn = 1;
			$len = 84;
			$short = "sn";
			// exits if not valid input (including null);
			$d = 'SN-'.validateAndSanitize($d, $logger, $name, $short, $endPoint, $time_start, $len, $fn);
		}
		else {
			$fn = 0;
			$len = 32;
			$short = $name[0];
			// exits if not valid input (including null);
			$d = validateAndSanitize($d, $logger, $name, $short, $endPoint, $time_start, $len, $fn);
		}

		
		$sql = 'SELECT * from '.$name.' where '.$name.' = (?) '.$active;

		queryBy($db, $logger, $sql, "s", $d, $time_start, $endPoint, $name);
		exit();
	}	

	// works for only sn, device, and company.
	function queryBy($db, $logger, $sql, $bind, $d, $time_start, $endPoint, $name){
		try{
			$res = bindAndExecute($db, $sql, $bind, [$d]);
		} catch (Mysqli_SQL_Exception $mse){
			handle_logger("DB_ERROR", $logger, $mse->getMessage(), $endPoint, $mse->getTraceAsString(), 'MSE:'.$mse->getCode(), 'None taken.', $time_start );
			handleAPIResponse(500, 'DB_ERROR', '', $endPoint, $time_start);
			exit();
		} catch (Exception $e) {
			// log error here
			handle_logger("log_sys_err", $logger, $e->getMessage(), $endPoint, $e->getTraceAsString(), 'E:'.$e->getCode(), 'None taken.', $time_start );;
			handleAPIResponse(500, 'OTHER_ERROR', '', $endPoint, $time_start);
			exit();
		}

		$r = $res->get_result();
		$row = $r->fetch_assoc();
		$res->close();

		$query = 'value';
		if($bind == 'i'){
			$query = 'id';
		}
		
		if ($row){
			$value = $row[$name];
			$payload = [
				'id' => $row[$name.'_id'],
				$name => $value,
				"active" => $row['active'],
				'queryBy' => $query
			];
			
			handle_logger("log_API_op", $logger, $endPoint, '200', "$name $value($d) queried.", $time_start );
			handleAPIResponse(200, 'Success', buildPayload($payload), $endPoint, $time_start);
			exit();
		}
		// device is not active, log output and return not found
		else {
			$payload = [
				'value' => $d,
				'queryBy' => $query
			];
			if($row){
				// log operation here
				handle_logger("log_API_op", $logger, $endPoint, '200', "$name $value($d) queried, but not active.", $time_start );
			}
			else{
				handle_logger("log_API_op", $logger, $endPoint, '200', "$name $d queried, not found.", $time_start );
			}
				handleAPIResponse(200, 'DNE', buildPayload($payload), $endPoint, $time_start);
				exit();
		}
	}
?>