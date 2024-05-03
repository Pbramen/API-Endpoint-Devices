<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add Company</title>
     <!-- MENU -->
     <?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
		include("../../assets/php/helperFunctions.php");
		
	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Add New Company</a>
	
	
 <!-- HOME -->
 <section id="feature">
	<div class="container">
		<div class="row">
			
			<?php
			$baseURL = "https://qta422.eastus.cloudapp.azure.com/web/add/addCompany.php";
			include('../../assets/php/curlHandler.php');
	
				if(isset($_GET['msg'])){
					$msg = $_GET['msg'];
					switch($msg){
						case 'success':
							echo '<div class="alert alert-success" role="alert">Company Added!</div>';
							break;
						case 'sys':
							echo '<div class="alert alert-danger" role="alert">System Error. Please try again later</div>';
							break;
						case 'missD':
							echo '<div class="alert alert-warning" role="alert">Company missing</div>';
							break;
						case 'encd':
							echo '<div class="alert alert-warning" role="alert">Invalid encoding</div>';
							break;
						case 'InvalidLen':
							echo '<div class="alert alert-danger" role="alert">Company must be <= 32 length.</div>';
							break;
						case 'InvalidFormat':
							echo '<div class="alert alert-danger" role="alert">Company must consist of only alphabetical characters.</div>';
							break;
						case 'CompanyExist':
							echo '<div class="alert alert-warning" role="alert">Company already exists.</div>';
							break;
						case 'DBERR':
							echo '<div class="alert alert-danger" role="alert">System error. Please try again.</div>';
							break;
						default:
					}
				}
			?>
			

	<?php
		if(isset($_POST["Company"]) && isset($_POST["submit"])){
			$d = $_POST['Company'];
			if(checkAlpha($d, $d)){
				header('Location:'.$baseURL.'?msg=InvalidFormat');
				exit();
			}
			if(strlen($d) > 32){
				header('Location:'.$baseURL.'?msg=InvalidFormat');
				exit();
			}
			$payload['c'] = $d;
			$payload = json_encode($payload);
			$res = curl_POST("add_company", $payload);
			if(isset($res['Status']) && isset($res['MSG'])){
				$msg = $res['MSG'];
				switch($msg){
					case "Success":
						header("Location: https://qta422.eastus.cloudapp.azure.com?msg=add200");
						exit();
					case "Company already exists.":
						header('Location:'.$baseURL.'?msg=CompanyExist');
						break;
					case "DNE":
					case "OTHER_ERROR":
						header('Location:'.$baseURL.'?msg=sys');
						break;
	
					case "Missing Company":
						header('Location:'.$baseURL.'?msg=missD');
						break;
					case "Invalid character encoding.":
						header('Location:'.$baseURL.'?msg=encd');
						break;
					case "Max length exceeded":
						header('Location:'.$baseURL.'?msg=InvalidLen');
						break;
					case "Company name should only have alpha characters.";
						header('Location:'.$baseURL.'?msg=InvalidFormat');
						break;

					default:
						echo $msg;
						break;
					
				}
			}
			else{
				echo $res;
				
			}
		}
	
	?>
	
				
	<form method="post" action="">
				<div class="form-group">
					<label for="Company">Company:</label>
					<input type="text" name="Company" maxlength="32">
				</div>
				<button class="btn btn-primary" type="submit" name="submit">Add</button>
			</form>
		</div>
	</div>
</section>
	</body>
</html>

