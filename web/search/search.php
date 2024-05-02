<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
    <link href="/assets/css/custom.css" rel="stylesheet">
	<?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
		include("../../assets/php/helperFunctions.php");
		include("../../assets/php/loader/directory.php");
		$baseURL .= 'search/search.php';

	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Search Equipment Database</a>
	
	<!-- load data and form -->
	 <?php
		// default option - no filter: (similar to 'select * from device where 1')
		$device[0] = "N/A";
		$company[0] = "N/A";

		include("../../assets/php/loader/loadAttributes.php");
		loadAttribute('device', $device, 0);
		loadAttribute('company', $company, 0);
		
	?>
	 <section id="feature">
	  <div class="container">
		   <div class="row">
		  </div>
		</div>
		
	<?php
	
		// // first page load
		// if(!isset($_GET['count']) && isset($_GET['device'])){
		// 	$queryParams = "";
			
		// 	if(isset($_GET['device'])){
		// 		$queryParams .= '&device='.$_GET['device'];	
		// 	}
		// 	if(isset($_GET['company'])){
		// 		$queryParams .= '&company='.$_GET['company'];	
		// 	}
		// 	if(isset($_GET['sn'])){
		// 		$queryParams .= '&sn='.$_GET['sn'];
		// 	}
			
		// 	$limit = 20;
		// 	header("Location: $baseURL?count=-1&limit=$limit&page=0$queryParams&first=1");
			
		// } 
		// on submit 
		if(isset($_GET['submit']) && isset($_GET['sn']) && isset($_GET['device']) && isset($_GET['company'])) {
			if(isset($_GET['limit'])){
				if(!is_numeric($page)){
					header('Location: $baseURL?msg=InvalidPage');
					exit();
				}
				$limit = $_GET['limit'];
			} else{
				$limit = 20;
			}
			
			if(isset($_GET['page'])){
				if(!is_numeric($page)){
					header('Location: $baseURL?msg=InvalidPage');
					exit();
				}
				$page = $_GET['page'];
			} else{
				$page = 0;
			}

			$queryParams = "/?";
			
			$d = $_GET['device'];
			if($d != 0 ){ //ignore if n/a
				if(!isset($device[$d])){
					header("Location: $baseURL?&msg=InvalidDevice");
					exit();
				}
				$queryParams .= "d=$d&";
			}
	
			$c = $_GET['company'];
			if($c != 0){
				if(!isset($company[$c])){
					header("Location: $baseURL?&msg=InvalidCompany");
					exit();
				}
				$queryParams .= "c=$c&";
			
			}
			
			$sn = $_GET['sn'];
			if( $sn != ''){
				$sn = $_GET['sn'];
				$sn = "SN-".$sn;
					
				if(!checkSNLen($sn)){
					header("Location: $baseURL?&limit=$limit&page=0&msg=InvalidSNLength");
					exit();
				}
				if(!checkDataType($sn, "string", "sn" )){
					header("Location: $baseURL?&limit=$limit&page=0&msg=InvalidSNData");
					exit();
				}
				$err = checkSNString($sn);
				if($err >= 2){
					header("Location: $baseURL?&limit=$limit&page=0&msg=InvalidSNFormat");
					exit();
				}
				$queryParams .= "sn=$sn&";
			}			
			
			$queryParams .= 'limit='.$limit;
			$result = curl_GET('search_equip', $queryParams);
			$n = 0;
			if(isset($result['Payload']['Fields']['total'])){
				$n = $result['Payload']['Fields']['total'];
			}
			$queryParams .= '&offset='.$offset;
			
			include("../../assets/php/components/searchForm.php");
			if($n != 0){
				echo "<p> $n total records found</p>";
				include("../../assets/php/components/tableComponent.php");
			} else{
				echo "<p> No results found</p>";
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