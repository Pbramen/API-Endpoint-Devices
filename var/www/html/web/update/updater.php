<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Update</title>
     <!-- MENU -->
     <?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
		include("../../assets/php/loader/loadData.php");
		include("../../assets/php/loader/loadDevices.php");
		include("../../assets/php/loader/loadCompanies.php");
		$baseURL= "https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/update/updater.php";
	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Update Equipment</a>
	
 <!-- HOME -->

 <section id="feature">
	  <div class="container">
		   <div class="row">	
			<?php
			   // handle all user error messages
			   if(isset($_GET['msg'])){
				   $msg = $_GET['msg'];
				   switch($msg){
					   case 'DBERR':
						    echo '<div class="alert alert-danger" role="alert">Unable to connected to DB. Please notify admin.</div>';
						    break;
					   case 'InvalidC':
						   echo '<div class="alert alert-danger" role="alert">Unknown device selected.</div>';
						   break;
					   case "InvalidD":
						    echo '<div class="alert alert-danger" role="alert">Unknown manufacturer selected.</div>';
						    break;
					   case "InvalidLength":
						   	echo '<div class="alert alert-danger" role="alert">Exceeded max length for serial number. Must be below 84 characters</div>';
						    break;
					   case 'InvalidSN':
						   echo '<div class="alert alert-danger" role="alert">Invalid serial number format. Please enter only characters a-f or digits.</div>';
						   break;
					   case 'ExistSN':
						   echo '<div class="alert alert-danger" role="alert">Serial number already exists. Please enter a new serial number.</div>';
						   break;
					   case 'NotFound':
						   echo '<div class="alert alert-danger" role="alert">Equipment not found. Please add values instead.</div>';
						   break;
					   case 'NoUpdate':
						   echo '<div class="alert alert-warning" role="alert">No changes made. Please enter different values to modify equipment.</div>';
						   break;
					   default:
						   break;
				   }	   
			   }
			   // on first page load (from select.php)
			   if(isset($_REQUEST["d"]) && isset($_REQUEST["c"]) && isset($_REQUEST["s"]) && isset($_REQUEST["r"])){ 
			   		$d = $_REQUEST["d"];
				    $c = $_REQUEST["c"];
				    $sn = $_REQUEST["s"];
				    $r = $_REQUEST["r"];
				  
				    // sanitize all incoming inputs.
				  	$d = sanitizeDriver($logger, $d, "update device", "updater.php");
				   	$c = sanitizeDriver($logger, $c, "update company", "updater.php");
				    $sn = sanitizeDriver($logger, $sn, "update sn", "updater.php");
				    $r = sanitizeDriver($logger, $r, "sanitize r_id input", "updater.php");
					
				   //validate sn
				   	if(!checkSNLen($sn)){
						$logger->insertSysErr('Max length for sn input reached: '.strlen($sn), "updater.php");
						header("Location: $baseURL?msg=InvalidLength");
						exit();
					}	
				   if(checkSNString($sn) >= 2){
					    echo "invalid sn format on load";
					   	$logger->insertSysErr("Invalid serial number format on load", "updater.php");
						//header("Location: $baseURL?msg=InvalidSN");
						exit();
				   }
				    echo '<p>Selected: '.$d.', '.$c.', '.$sn.'</p>';
				   
				   ?>
			<h1> Set new values for selected equipment: </h1>
			<!--FORM GROUP HERE-->   
			<form method="post" action="">
			<div class="form-group">
				<label for="device">Device:</label>
				<select class="form-control" name="device" id="device">
					<?php
						foreach($device as $key=>$value){
							if($d == $value){
								echo '<option selected="true" value="'.$key.'">'.$value.'</option>';
							} else {
								echo '<option value="'.$key.'">'.$value.'</option>';
							}
						}
					?>
				</select>
			</div>
				<div class="form-group">
				<label for="company">Manufacturer:</label>
				<select class="form-control" name="company" id="company">
					<?php
						foreach($company as $key=>$value){
							if($c == $value){
								echo '<option selected="true" value="'.$key.'">'.$value.'</option>';	
							} else{
								echo '<option value="'.$key.'">'.$value.'</option>';	
							}
						}
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="serialInput">Serial Number:</label>
				<div class="flex-container">
					<span class="sn-prefix">SN-</span>
					<?php
				    	echo '<input class="flex-item form-control border-fix" type="text" maxlength="81" id="serialInput" name="serialnumber" value="'.substr($sn, 3).'">';
				   	?>
				</div> 
			</div>
				
				<?php
				   echo '<input hidden="true" name="r" value="'.$r.'">';
				   echo '<input hidden="true" name="oldc" value="'.$c.'">';
				   echo '<input hidden="true" name="oldd" value="'.$d.'">';
				   echo '<input hidden="true" name="old" value="'.substr($sn, 3).'">';
				?>
				   
				<button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
		   </form>
			   <?php
				   
			  }
			   // upon hitting the submit button...
			   
			   if(isset($_POST['r']) && isset($_POST["device"]) && isset($_POST["company"]) && isset($_POST["serialnumber"]) && isset($_POST['submit'])){
				   $d = $_POST['device'];
				   $c = $_POST['company'];
				   $r = $_POST['r'];
				   $sn = $_POST['serialnumber'];
				   $old = $_POST['old']; // prev serial number
				   $oldc = $_POST['oldc'];
				   $oldd= $_POST['oldd'];
				   
				   // begin sanitization and validation
				   $r = sanitizeDriver($logger, $r, "relation id", "update.php");
			
				   // queryParams to build for updater
				   $queryParams= "";
				   $queryParams .= 'r='.$r;
	
				   
				   if(!isset($device[$d])){
				   // invalid device sent
					$logger->insertSysErr('Invalid device '.santizeDriver($logger, $d, 'device', "updater.php"), "updater.php");
					header("Location: $baseURL?msg=InvalidD");
					exit();
				   }
				   $queryParams .= '&d='.$device[$d];
				   if(!isset($company[$c])){
					   	// invalid company sent
					   	$logger->insertSysErr('Invalid device '.santizeDriver($logger, $c, "company", "updater.php"), "updater.php");
						header("Location: $baseURL?msg=InvalidC");
						exit();
				   }
				   $queryParams .= '&c='.$company[$c];
				   $queryParams .= '&s='.sanitizeDriver($logger, 'SN-'.$old, "sn", "updater.php");
				   
				   $sn = sanitizeDriver($logger, $sn, "sn", "updater.php");				
				   $sn = "SN-".$sn;
				   $old = 'SN-'.$old;
				   
				   // validators for sn
				   if(!checkSNLen($sn)){
						$logger->insertSysErr('Max length for sn input reached: '.strlen($sn), "updater.php");
						header("Location: $baseURL?msg=InvalidLength&$queryParams");
						exit();
				   }	
				   if(checkSNString($sn) >= 2){
					   	$logger->insertSysErr('Invalid sn format.', "updater.php");
					   echo "invalid format";
					   echo $sn;
					   //header("Location: $baseURL?msg=InvalidSN&$queryParams");
						exit();
				   }
				   if($old != $sn && checkUnquie($db, $sn) == ""){
					   	$logger->insertSysErr('Attempted to update to existing sn', "updater.php");
						echo "sn is not unquie";
					    echo "$sn";
					    header("Location: $baseURL?msg=ExistSN&$queryParams");
						exit();
				   } 
				   // sn needs to be changed!
				   if($old != $sn){
					   // create a new sn! 
					   $sn_id = insertSN($db, $sn);
				   }
				   else{
					   // no change detected
					   if(($company[$c] == $oldc) && ($device[$d] == $oldd)){
						   header("Location: $baseURL?msg=NoUpdate&$queryParams");
						   exit();
					   }
					   try{
					   // attempt to update equipment with new variables.
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
						$row = $res->num_rows;
						$sn_id = $row['sn_id'];
						   
						if($row == 0){
							$logger->insertSysErr("Attempted to update a non-existing sn.", "updater.php");
							header("Location: $baseURL?msg=NaNSN&$queryParams");
							exit();
					   }
					   } catch (MySQLi_Sql_Exception $mse){
				   			$logger->insertSysErr("$mse", "updater.php");
						   header("Location: $baseURL?msg=DBERR&$queryParams");
							exit();
					   } catch (Exception $e){
							$logger->insertSysErr("$e", "updater.php");
					   		header("Location: $baseURL?msg=DBERR&$queryParams");
							exit();
					   }
					   
				   }
				   //echo $sn_id;
				   $sql = "UPDATE `relation` SET sn_id = (?), device_id = (?), company_id = (?) WHERE r_id = (?)";

				   try{
						$res = $db->prepare($sql);
					    if(!$res){
							throw new Exception("Unable to prepare: $db->error");
						}
					    if(!$res->bind_param("siii", $sn_id, $d, $c, $r)){
							throw new Exception("Unable to bind params: $res->error");
						}
					    if(!$res->execute()){
							throw new Exception("Unable to execute: $res->error");
						}
					    if($res->affected_rows == 0){
							header("Location: $baseURL?msg=NotFound&$queryParams");
					    	exit();
						}
					    // success! log operation.
					   	logOperation($logger, 'updater.php', $queryParams, 'post');
					    header("Location: https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/form/web/index.php?msg=update200");
					    exit();
				   } catch(Mysqli_sql_exception $mse){
					   $logger->insertSysErr("$mse", "updater.php");
					   	header("Location: $baseURL?msg=DBERR");
					   exit();
				   } catch(Exception $e){
					   $logger->insertSysErr("$e", "updater.php");
					   	header("Location: $baseURL?msg=DBERR&$queryParams");
						exit();
				   }
			   } 
			 ?>
		</div>
     </section>
</body>
</html>