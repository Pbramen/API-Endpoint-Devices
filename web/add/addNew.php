<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
<link href="../../assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/font-awesome.min.css">
<link rel="stylesheet" href="../../assets/css/owl.carousel.css">
<link rel="stylesheet" href="../../assets/css/owl.theme.default.min.css">
<link rel="stylesheet" href="../../assets/css/custom.css">
<!-- MAIN CSS -->
<link rel="stylesheet" href="../../assets/css/templatemo-style.css">
</head>
<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">
<!-- lOGO TEXT HERE -->
<a href="#" class="navbar-brand">Add New Equipment</a>

 <!-- FEATURE -->
 <section id="feature">
	  <div class="container">
		   <div class="row">	
			  
	
<?php
	// MENU
	include("../../assets/php/helperFunctions.php");
	include('../../assets/php/components/nav.php');


	include('../../assets/php/loader/loadAttributes.php');

	$device = [];
	$commpany = [];
	loadAttribute('device', $device, 1);
	loadAttribute('company', $company, 1);
	include("../../assets/php/loader/directory.php");
	$baseURL .= 'add/addNew.php';
	
	if(!$_REQUEST){
		include("../../assets/php/components/formComponent.php");
	}
	else if(isset($_REQUEST['msg'])){

	

		$msg = $_REQUEST['msg'];
		switch($msg){
			case 'InvalidInput':
				echo '<div class="alert alert-danger" role="alert">Invalid serial number. Please only use characters a-f or digits.</div>';
				break;
			case 'InvalidSNLength':
				echo '<div class="alert alert-danger" role="alert">Maximun length of sn is 84.</div>';
				break;
			case 'InvalidSNData':
				echo '<div class="alert alert-danger" role="alert">SN must be a string.</div>';
				break;
			case 'InvalidSNFormat':
				echo '<div class="alert alert-danger" role="alert">SN must be valid hexcode only.</div>';
				break;
			case 'DM':
			case 'deviceM':
				echo '<div class="alert alert-danger" role="alert">Device missing.</div>';
				break;
			case 'CM':
			case 'companyM':
				echo '<div class="alert alert-danger" role="alert">Compoany missing</div>';
				break;
			case 'SNM':
				echo '<div class="alert alert-danger" role="alert">Serial Number Missing</div>';
				break;
			case 'sys':
				echo '<div class="alert alert-danger" role="alert">Internal System Error. Please try again later</div>';
				break;
			case 'E':
				echo '<div class="alert alert-danger" role="alert">Equipment already exists.</div>';
				break;
			default;
				break;
		}
		include("../../assets/php/components/formComponent.php");
	}

	else if(!isset($_GET['$msg']) && isset($_POST['submit']) && isset($_POST['serialnumber']) && isset($_POST['device']) && isset($_POST['company'])){
		//sanitize all params here 
		$d = $_POST['device'];
		$payload = [];
		if(!isset($device[$d])){
			header("Location: $baseURL?&msg=InvalidDevice");	
			exit();
		}
		$payload['d'] = $d;

		$c = $_POST['company'];
			if(!isset($company[$c])){
				header("Location: $baseURL?&msg=InvalidCompany");
				exit();
			}
		$payload['c'] = $c;
		
		$sn = $_POST['serialnumber'];
		if( $sn != ''){
			$sn = "SN-".$sn;
				
			if(!checkSNLen($sn)){
				header("Location: $baseURL?&page=0&msg=InvalidSNLength");
				exit();
			}
			if(!checkDataType($sn, "string", "sn" )){
				header("Location: $baseURL?&page=0&msg=InvalidSNData");
				exit();
			}
			$err = checkSNString($sn);
			if($err >= 2){
				header("Location: $baseURL?&page=0&msg=InvalidSNFormat");
				exit();
			}
		}	
		$payload['sn'] = $sn;

		$payload = json_encode($payload);
		// curl 
		$res = curl_POST("add_equipment", $payload);
		// on success -> header back home
		if(isset($res['MSG']) && $res['MSG'] == 'Success'){

			header('Location: https://qta422.eastus.cloudapp.azure.com?msg=add200');
			exit();
		}
		else if(isset($res['MSG'])){
			
			$msg = $res['MSG'];
			switch($msg){
				case 'Missing param: device':
					echo "missing device";
					header("Location: $baseURL?&msg=DM");
					break;
				case 'Missing param: company':
					header("Location: $baseURL?&msg=CM");
					break;
				case 'Missing param: sn':
					header("Location: $baseURL?&msg=SNM");
					break;
				case 'DB_ERROR':
				case 'OTHER_ERROR':
				case 'Invalid JSON format.':
					header("Location: $baseURL?&msg=sys");
					break;
				case 'DNE':
					if(isset($res['Payload'])){
						$parm = $res['Payload'];
						if($parm == 'device' || $parm == 'company'){
							header('Location: '.$baseURL.'?&msg='.$parm.'M');
						}
					}
					break;
				case 'Record already exists':
					header('Location: '.$baseURL.'?&msg=E');
					break;
				default:
					echo $msg;
					//header('Location: '.$baseURL.'?&msg=uER');
					break;
			}
		}
		
		// on fail -> header back here with msg.
		// MSG = "Record already exists"
	}
	?>
</body>
</html>