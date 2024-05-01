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
	include('../../assets/php/components/nav.php');
			   
	// start db connection
    include('../../assets/php/loader/loadData.php');
	// get all devices and companies from db
	include("../../assets/php/loader/loadDevices.php");
	include("../../assets/php/loader/loadCompanies.php");		   

	if (isset($_REQUEST['msg']) && $_REQUEST['msg']=="InvalidInput")
	{
		echo '<div class="alert alert-danger" role="alert">Invalid serial number. Please only use characters a-f or digits.</div>';

	}
	if (isset($_REQUEST['msg']) && $_REQUEST['msg']=='MaxLength'){
		echo '<div class="alert alert-danger" role="alert">Maximun length of sn is 84.</div>';
	}
	if (isset($_REQUEST['msg']) && $_REQUEST['msg']=='DeviceExists'){
		echo '<div class="alert alert-warning" role="alert">Device is already in the database.</div>';
	}

	include("../../assets/php/components/formComponent.php");

		if(isset($_POST["serialnumber"])){
			$sn = trim($_POST["serialnumber"]);
			$sn = "SN-".$sn;
			$err = checkSNString($sn);
			
			if($err >= 2){
				// not hexadecimal format
				$logger->insertSysErr("Invalid input for sn: $sn", "addNew");
				header("Location: https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/add/addNew.php?msg=InvalidInput");
				exit();
			}
			
			if($err <= 1 && !checkSNLen($sn)){
				// invalid length -> warning
				$logger->insertSysErr('Max length for sn input reached: '.strlen($sn), "addNew");
				header("Location: https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/addNew.php?msg=MaxLength");
				exit();
			}
			else if($err <= 1) {
				// check if unquie sn!
				try{
					if( checkUnquie($db, $sn) ){
						// insert into database here
						$device = $_POST['device'];
						$company = $_POST['company'];
						
						$sn_id = insertSN($db, $sn);
					
						insertRelation($db, $device, $company, $sn_id);
						
						header("Location: https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/index.php?msg=EquipmentAdded" );
						exit();
					}
					else{
						//echo "error: sn already exists";
						// log user error here
						$logger->insertSysErr("$sn already exists.", "addNew");
						header("Location: https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/add/addNew.php?msg=DeviceExists");
						exit();
					}
				} catch(Exception $e){
					//Log to database here.
					$logger->insertSysErr($e.message, "addNew");
					header("Location: https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/404?msg=SystemFailure");
					exit();
				}
				
			}
		}
	?>
</body>
</html>