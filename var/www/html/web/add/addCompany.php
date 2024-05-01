<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add company</title>
     <!-- MENU -->
     <?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
		

	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Add New Company</a>
	
 <!-- HOME -->
 <section id="feature">
	<div class="container">
		<div class="row">
			
	<?php
		if(isset($_GET['msg'])){
			$msg = $_GET['msg'];
			switch($msg){
				case 'success':
					echo '<div class="alert alert-success" role="alert">Manufacturer Added!</div>';
					break;
				case 'InvalidLen':
					echo '<div class="alert alert-danger" role="alert">Manufacturer must be <= 32 length.</div>';
					break;
				case 'InvalidFormat':
					echo '<div class="alert alert-danger" role="alert">Manufacturer must consist of only alphabetical characters.</div>';
					break;
				case 'CompanyExist':
					echo '<div class="alert alert-warning" role="alert">Manufacturer already exists.</div>';
					break;
				case 'DBERR':
					echo '<div class="alert alert-danger" role="alert">System error. Please try again.</div>';
					break;
				default:
			}
		}
	?>
			<!--FORM GROUP HERE-->	
			<form method="post" action="">
				<div class="form-group">
					<label for="company">Company:</label>
					<input type="text" name="company" maxlength="32">
				</div>
				<button class="btn btn-primary" type="submit" name="submit">Add</button>
			</form>
		</div>
	</div>
</section>

	<?php
		
		if(isset($_POST["company"]) && isset($_POST["submit"])){
		// 	$d = $_POST["company"];
			
		// 	// Validate and sanitize company
		// 	$n = mb_strlen($d, 'UTF-8');
		// 	$d = sanitizeDriver($logger, $d, "company", "company");
			
		// 	if($n >= 32){
		// 		$logger->insertSysErr("Company length exceeded: $n", "addCompany.php");
		// 		header("Location: $baseURL?msg=InvalidLen");
		// 		exit();
		// 	}
		// 	if(checkAlpha($d, $res)){
		// 		$logger->insertSysErr("Attempted to add company with invalid character $res", "addCompany.php");
		// 		header("Location: $baseURL?msg=InvalidFormat");
		// 		exit();
		// 	}
		// 	// check if unique
		// 	$sql = "Select company_id from company where company = (?)";
		// 	try{
		// 		$p = [$d];
		// 		$res = bindAndExecute($db, $sql, "s", $p);	
		// 		$res->store_result();
		// 		$res->fetch();
				
		// 		// log and message user if already exists.
		// 		if($res->num_rows == 1){
		// 			$logger->insertSysErr("Attempted to add an existing company $d", "addCompany.php");
		// 			header("Location: $baseURL?msg=CompanyExist");
		// 			exit();
		// 		}
		// 		$res->close();
				
		// 		// create new company 
		// 		$sql= "INSERT into company (company) VALUES (?)";
		// 		$res = bindAndExecute($db, $sql, "s", $p);
		// 		$res->close();
				
		// 		//insert operation here
		// 		logOperation($logger, 'addDevice.php', "Added company $d", 'post');
		// 		header("Location: $baseURL?msg=success");
		// 		exit();
		// 	} catch (Mysqli_Sql_exception $mse){
		// 		$logger->insertSysErr($mse, "addCompany.php");
		// 		header("Location: $baseURL?msg=DBERR");
		// 		exit();
		// 	} catch (Exception $e){
		// 		$logger->insertSysErr($e, "addCompany.php");
		// 		header("Location: $baseURL?msg=DBERR");
		// 		exit();
		// 	}
		}
	
	?>
	
	</body>
</html>