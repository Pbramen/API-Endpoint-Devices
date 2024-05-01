<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add device</title>
     <!-- MENU -->
     <?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Add New Device</a>
	
 <!-- HOME -->
 <section id="feature">
	<div class="container">
		<div class="row">
			
			<?php
			
				if(isset($_GET['msg'])){
					$msg = $_GET['msg'];
					switch($msg){
						case 'success':
							echo '<div class="alert alert-success" role="alert">Device Added!</div>';
							break;
						case 'InvalidLen':
							echo '<div class="alert alert-danger" role="alert">Device must be <= 32 length.</div>';
							break;
						case 'InvalidFormat':
							echo '<div class="alert alert-danger" role="alert">Device must consist of only alphabetical characters.</div>';
							break;
						case 'DeviceExist':
							echo '<div class="alert alert-warning" role="alert">Device already exists.</div>';
							break;
						case 'DBERR':
							echo '<div class="alert alert-danger" role="alert">System error. Please try again.</div>';
							break;
						default:
					}
				}
			?>
			
			
			<form method="post" action="">
				<div class="form-group">
					<label for="device">Device:</label>
					<input type="text" name="device" maxlength="32">
				</div>
				<button class="btn btn-primary" type="submit" name="submit">Add</button>
			</form>
		</div>
	</div>
</section>

	<?php
		if(isset($_POST["device"]) && isset($_POST["submit"])){
			// $d = $_POST["device"];
			// $n = mb_strlen($d, 'UTF-8');
			// $d = sanitizeDriver($logger, $d, "device", "device");
			
			// if($n >= 32){
			// 	$logger->insertSysErr("Device length exceeded: $n", "addDevice.php");
			// 	header("Location: $baseURL?msg=InvalidLen");
			// 	exit();
			// }
			// if(checkAlpha($d, $res)){
			// 	$logger->insertSysErr("Attempted to add device with invalid character $res", "addDevice.php");
			// 	header("Location: $baseURL?msg=InvalidFormat");
			// 	exit();
			// }
			// // check if unique
			// $sql = "Select device_id from device where device = (?)";
			// try{
			// 	$p = [$d];
			// 	$res = bindAndExecute($db, $sql, "s", $p);	
			// 	$res->store_result();
			// 	$res->fetch();
				
			// 	// if not -> send warning
			// 	if($res->num_rows == 1){
			// 		$logger->insertSysErr("Attempted to add an existing device $d", "addDevice.php");
			// 		header("Location: $baseURL?msg=DeviceExist");
			// 		exit();
			// 	}
			// 	$res->close();
				
			// 	// insert new device
			// 	$sql= "INSERT into device (device) VALUES (?)";
			// 	$res = bindAndExecute($db, $sql, "s", $p);
			// 	$res->close();
				
			// 	//insert operation here
			// 	logOperation($logger, 'addDevice.php', "Added device $d", 'post');
			// 	header("Location: $baseURL?msg=success");
			// 	exit();
			// } catch (Mysqli_Sql_exception $mse){
			// 	$logger->insertSysErr($mse, "addDevice.php");
			// 	header("Location: $baseURL?msg=DBERR");
			// 	exit();
			// } catch (Exception $e){
			// 	$logger->insertSysErr($e, "addDevice.php");
			// 	header("Location: $baseURL?msg=DBERR");
			// 	exit();
			// }
		}
	
	?>
	
	</body>
</html>

