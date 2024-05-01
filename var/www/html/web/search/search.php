<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
    <link href="/form/assets/css/custom.css" rel="stylesheet">
	<?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Search Equipment Database</a>
	
	<!-- load data and form -->
	 <?php
		include("../../assets/php/components/nav.php");
		$device = array();
		$company = array();
		// default option - no filter: (similar to 'select * from device where 1')
		$device["0"] = "N/A";
		$company["0"] = "N/A";
	
		// start db connection
		include('../../assets/php/loader/loadData.php');
		// get all devices and companies from db
		include("../../assets/php/loader/loadDevices.php");
		include("../../assets/php/loader/loadCompanies.php");		
		$baseURL = "https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/search/search.php";
	?>
	 <section id="feature">
	  <div class="container">
		   <div class="row">
		  </div>
		</div>

	<?php
		// first page load
		 

		if(!isset($_GET['count']) && isset($_GET['device'])){
			$queryParams = "";
			
			if(isset($_GET['device'])){
				$queryParams .= '&device='.$_GET['device'];	
			}
			if(isset($_GET['company'])){
				$queryParams .= '&company='.$_GET['company'];	
			}
			if(isset($_GET['serialnumber'])){
				$queryParams .= '&serialnumber='.$_GET['serialnumber'];
			}
			
			$limit = 20;
			logOperation($logger, 'Search', "$queryParams", 'get');
			header("Location: $baseURL?count=-1&limit=$limit&page=0$queryParams&first=1");
			
		} 
		else if(isset($_GET['count']) &&  isset($_GET['limit']) && isset($_GET['page']) && !isset($_GET['msg'])) {
			//echo "this is running";
			$count = $_GET['count'];
			$limit = $_GET['limit'];
			$page = $_GET['page'];
			$queryParams = "?";
			$d = 0;
			$c = 0;
			$sn = "";
			// check datatype
			if(!is_numeric($count)){
				header("Location: $baseURL?msg=InvalidCount");
				exit();
			}
			if(!is_numeric($limit)){
				header("Location: $baseURL?count=$count&msg=InvalidLimit");
				exit();
			}
			if(!is_numeric($page)){
				header("Location: $baseURL?count=$count&msg=InvalidPage");
				exit();
			}
			
			
			$params= array();
			$params["attribute"] ='';
			$params["bind"] = '';
			$params["p"] = '';
			$params["values"] = array();
			
			//$timestart = microtime(true);
			
			if(isset($_GET['device'])){
				$d = $_GET['device'];
				$queryParams .= "&device=$d";
				if($d != 0 ){
					$res = '';
					if(!isset($device[$d])){
						header("Location: $baseURL?count=$count&msg=InvalidDevice");
					}
					prepareFilters($params, "relation", "i", "device_id", $d);
					//echo "filtering by device\n";
				}
			}
			//echo 'getDevice: '.(microtime(true) - $timestart )/ 60;
			//echo "<br>";
			//$timestart = microtime(true);
			if(isset($_GET['company'])){
				$c = $_GET['company'];
				$queryParams .= "&company=$c";
			   if($c != 0){
					if(!isset($company[$c])){
						header("Location: $baseURL?count=$count&msg=InvalidCompany");
					}
					prepareFilters($params, "relation", "i", "company_id", $c);
					//echo "filtering by company\n";
				}
			}
			
			//echo 'getCompany: '.(microtime(true) - $timestart )/ 60;
			//echo "<br>";
			//$timestart = microtime(true);
			if(isset($_GET['serialnumber'])){
				$sn = $_GET['serialnumber'];
				$queryParams .= "&serialnumber=$sn";
				if( $sn != ''){
				$sn = $_GET['serialnumber'];
				$sn = "SN-".$sn;
					
				if(!checkSNLen($sn)){
					header("Location: $baseURL?count=$count&limit=$limit&page=0&msg=InvalidSNLength");
					exit();
				}
				if(!checkDataType($sn, "string", "sn" )){
					header("Location: $baseURL?count=$count&limit=$limit&page=0&msg=InvalidSNData");
					exit();
				}
				$err = checkSNString($sn);
				if($err >= 2){
					header("Location: $baseURL?count=$count&limit=$limit&page=0&msg=InvalidSNFormat");
					exit();
				}
					$queryParams .= "&serialnumber=$sn";
					prepareFilters($params, "sn", "s", "sn", $sn);
				}			
			}
			
			//echo 'getSN '.(microtime(true) - $timestart )/ 60;
			//echo "<br>";
			//$timestart = microtime(true);
			
			if(isset($_GET['submit']) || isset($_GET['first'])){
				if($params['attribute'] != ""){
					// count numbner of relations per the filters
					// better performance when querying by device AND/OR company
					$sn_id = 0;
					if($sn != ''){
						//grab the id for sn.
						try{
						$sql = "Select sn_id from `sn` where sn = (?)";
						$res = $db->prepare($sql);
						if(!$res){
							throw new Exception("Unable to prepare $sql query. $db->errno: $db->error");
						}
						if(!$res->bind_param('s', $sn)){
							throw new Exception("Unable to bind $bind to $sql. $res->errno: $res->error ");
						}
						if(!$res->execute()){
							throw new Exception("Unable to execute $sql. $res->errno: $res->error");
						}
						$res->store_result();
						$res->bind_result($sn_id);
						$res->fetch();
					    if ($res->num_rows() == 0){
							$logger->insertSysErr("No exisitng sn for $sn", "search.php");
							header("Location: $baseURL?msg=NaNSN");
							exit();
						}
						} catch(MySQLi_Sql_Exception $mse){
							$logger->insertSysErr($mse, "search.php");
							echo $mse;
							exit();
						} catch(Exception $e){
							$logger->insertSysErr($e, "search.php");
							echo $e;
							exit();
						}
					}
					// sanitize then build query string based on selected filters
					// sanitize will log if different
					$p;
					$sql = buildCountString($d, $c, $sn_id, $p);
					$sql = sanitizeDriver($logger, $sql, "search endpoint", "search.php");
					try{
						$result = bindAndExecuteSelect($db, $sql, $p["bind"], $p['val']);
					
					} catch (MySQLi_Sql_Exception $mse){
						$logger->insertSysErr("Unable to prepare search: $db->error", 'search.php');
						echo $mse;
						header("Location: $baseURL?msg=DB_ER");
						exit();
					} catch (Exception $e){
						$logger->insertSysErr("Other exception occured: $db->error", 'search.php');
						echo $mse;
						header("Location: $baseURL?msg=DB_ER");
						exit();
					}
					if($result){
						$count = $result[0]["COUNT(device_id)"];
					}
					else{
						echo "<div> No records found</div>";
						exit();
					}
				}
				else{
					$count = countMaxRecords($db);
				}		
			}
			$queryParams .= "&count=$count";
			$sql = buildString($params);
		
			$params["values"][] = &$limit;
			$params["values"][] = &$page;
			
			$sanitized = sanitizeDriver($logger, $sql, "search webpoint", "search.php");
			
			try{
				$result = bindAndExecuteSelect($db, $sanitized, $params["bind"], $params["values"]);
				$totalPages = ceil($count / $limit);
			} catch (MySQLi_Sql_Exception $mse){
				// TODO: LOG EXCEPTION HERE!
				
				header("Location: $baseURL?count=$count&msg=DB_ER");
			} catch (Exception $e){
				// TODO: LOG EXCEPTION HERE!
				header("Location: $baseURL?count=$count&msg=DB_ER");
			}
			if(!$result){
				echo '<div>No results found.</div>';
			}
			else{
				include("../../assets/php/components/searchForm.php");

				echo '<div class="container"><div class="row">';
					echo '<p>Page '.$page.' out of '.$totalPages.'. '.$count.' total records...</p>';
					include("../../assets/php/components/tableComponent.php");
				echo '</div></div>';

				include("../../assets/php/components/paginationComponent.php");
				//echo 'print results '.(microtime(true) - $timestart )/ 60;
				//echo "<br>";
			}
		}
		else if (count($_GET) != 0 && isset($_GET['msg'])){
			//log unexpected error here.
			$msg = $_GET['msg'];
			switch($msg){
				case 'DB_ER':
					echo '<div class="alert alert-danger" role="alert">System down. Please try again later</div>';
					break;
				case 'NaNSN':
					echo '<div class="alert alert-warning" role="alert">SN does not exist.</div>';					
					break;
				case 'InvalidCount':
					echo '<div class="alert alert-danger" role="alert">Count invalid. Must be a number.</div>';
					break;
				case 'InvalidDevice':
					echo '<div class="alert alert-danger" role="alert">Device does not exist.</div>';
					break;
				case 'InvalidCompany':
					echo '<div class="alert alert-danger" role="alert">Company does not exist.</div>';
					break;
				case 'InvalidLimit':
					echo '<div class="alert alert-danger" role="alert">Limit invalid. Must be a number.</div>';
					break;
				case 'InvalidPage':
					echo '<div class="alert alert-danger" role="alert">Page invalid. Must be a number.</div>';
					break;
				case 'InvalidSNLength':
					echo '<div class="alert alert-danger" role="alert">Serial number must be less than 84 characters long.</div>';
					break;
				case 'InvalidSNData':
					echo '<div class="alert alert-danger" role="alert">Serial number must be entered directly in the textbox.</div>';
					break;
				case 'InvalidSNFormat':
					echo '<div class="alert alert-danger" role="alert">Incorrect format for serial number. Please use only a-f or digits.</div>';
					break;
				default:
			}
			
			include("../../assets/php/components/searchForm.php");
				
			exit();
		}
		else {
			include("../../assets/php/components/searchForm.php");
		}

	?>
	</section>
</body>
</html>