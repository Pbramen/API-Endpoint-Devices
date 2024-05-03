<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Update</title>
    <link rel="stylesheet" href="/assets/css/custom.css">
     <!-- MENU -->
     <?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
		include("../../assets/php/loader/loadAttributes.php");
        include("../../assets/php/helperFunctions.php");
        $company[0] = "N/A";
        $company[0] = "N/A";
        loadAttribute('company', $company);

		$baseURL= "https://qta422.eastus.cloudapp.azure.com/web/update/updateCompany.php";
	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Update company</a>
	
 <!-- HOME -->

 <section id="feature">
	  <div class="container">
		   <div class="row">	
			<?php
			   // handle all user error messages
			   if(isset($_GET['msg'])){
				   $msg = $_GET['msg'];
				   switch($msg){
					   case "DNE":
						    echo '<div class="alert alert-danger" role="alert">Unknown company selected.</div>';
						    break;
					   case 'NC':
                       case 'noChange':
						   echo '<div class="alert alert-warning" role="alert">No changes made. Please enter different values to modify company.</div>';
						    break;
                        case 'InvalidActive':
                            echo '<div class="alert alert-warning" role="alert">Invalid Status.</div>';
						    break;
                        case 'InvalidNewD':
                            echo '<div class="alert alert-warning" role="alert">Company must be valid alpha characters only..</div>';
						    break;
                        case 'sys':
                            echo '<div class="alert alert-warning" role="alert">System failure. Please try again later.</div>';
						    break;
                        default:
                            echo '<div class="alert alert-warning" role="alert">Other unexpected error.</div>';
						    break;
                        }	   
			   }
			   // on first page load (from select.php) 
			?>
			<h1> Set new values for selected company: </h1>
			<!--FORM GROUP HERE-->   
            <?php	
			   // upon hitting the submit button...	   
			    if(isset($_POST["company"]) && isset($_POST['submit']) ){
                    $d = $_POST['company'];

                    $newD = "";
                    if(isset($_POST['newcompany'])){
                        $newD  = $_POST['newcompany'];
                    }
                    
                    $payload = [];

                    $active = 0;
                    if(isset($_POST['active']) && $_POST['active'] == 'true'){
                        $payload['active'] = 1;
                    }
                    else if(isset($_POST['active'])){
                        header("Location: $baseURL?msg=InvalidActive");
                        exit();
                    }
                    else{
                        $payload['active'] = 0;
                    }

                   
                    //only accept already validated companys.
                    if(!isset($company[$d])){
                        // invalid company sent
                        header("Location: $baseURL?msg=InvalidD");
                        exit();
                    } else if($d != 0){
                        $payload['c'] = $d;
                    }
                    if($newD != "" ){
                        if(checkAlpha($newD, $newD)){
                            header("Location: $baseURL?msg=InvalidNewD");
                            exit();
                        }
                        $payload['new'] = $newD;
                    }
                    print_r($payload);
                    $payload = json_encode($payload);
                    $res = curl_POST('update_company', $payload);
           
                    if(isset($res['Status']) && isset($res['MSG'])){
                        $msg = $res['MSG'];
                        switch($msg){
                            case "Success":
                                header('Location: https://qta422.eastus.cloudapp.azure.com?update200');
                                exit();
                            case "DNE":
                                header('Location: '.$baseURL.'?msg=DNE');
                                exit();
                            case "DB_ERROR":
                            case "OTHER_ERROR":
                                header('Location: '.$baseURL.'?msg=sys');
                                exit();
                            case "company already exists.":
                                header('Location: '.$baseURL.'?msg=DE');
                                exit();
                            case "No change":
                                header('Location: '.$baseURL.'?msg=NC');
                                exit();
                            case "Invalid active param.":
                                header('Location: '.$baseURL.'?msg=invalidA');
                                exit();
                            default:
                                echo $msg;

                        }
                    }
                    else{
                        echo $res;
                        print_r($res);
                    }
                }
                echo '<form method="post" action="'.$baseURL.'">'; ?>

                    <div class="form-group">
                        <label for="company">Select company:</label>
                        <select class="form-control" name="company" id="company">
                            <?php
                                foreach($company as $key=>$value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                                
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="serialInput">Set New company:</label>
                            <input class="form-control" type="text" maxlength="32" id="newcompany" name="newcompany">
                    </div>

                    <div class="form-group">
                        <input class="flex-item" style="margin-bottom:5px" type="checkbox" id="active" name="active" value="true">
                        <label for="active">Set Active Status</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
                </form>
           </div>
      </div>
    </section>
</body>
</html>