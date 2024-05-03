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
        $device[0] = "N/A";
        $company[0] = "N/A";
        loadAttribute('device', $device);
        loadAttribute('company', $company);

		$baseURL= "https://qta422.eastus.cloudapp.azure.com/web/update/updateEquip.php";
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
					   case 'NotFound':
						   echo '<div class="alert alert-danger" role="alert">Equipment not found. Please add values instead.</div>';
						   break;
					   case 'NoUpdate':
                       case 'noChange':
						   echo '<div class="alert alert-warning" role="alert">No changes made. Please enter different values to modify equipment.</div>';
						    break;
                        case 'noSN':
                        case 'DNE':
                            echo '<div class="alert alert-warning" role="alert">Selected serial number does not exist.</div>';
						    break;
                        case 'noCD':
                            echo '<div class="alert alert-warning" role="alert">Selected company or device does not exist.</div>';
						    break;
                        case 'InvalidActive':
                            echo '<div class="alert alert-warning" role="alert">Invalid Status.</div>';
						    break;
                        case 'sys':
                            echo '<div class="alert alert-warning" role="alert">System failure. Please try again later.</div>';
						    break;
                        case 'SNE':
                            echo '<div class="alert alert-warning" role="alert">New value already registered.</div>';
						    break;
					   default:
                            echo '<div class="alert alert-warning" role="alert">Other unexpected error.</div>';
						    break;
				   }	   
			   }
			   // on first page load (from select.php) 
			?>
			<h1> Set new values for selected equipment: </h1>
			<!--FORM GROUP HERE-->   
            <?php	
			   // upon hitting the submit button...	   
			    if(isset($_POST["device"]) && isset($_POST["company"]) && isset($_POST["sn"]) && isset($_POST['submit']) ){
                    $d = $_POST['device'];
                    $c = $_POST['company'];
                    $sn = 'SN-'.$_POST['sn'];
                    $newSN;
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

                    if(isset($_POST['newSN']) && $_POST['newSN'] != ''){
                        $newSN = 'SN-'.$_POST['newSN'];
                        if(!checkSNLen($newSN)){
                            header("Location: $baseURL?&page=0&msg=InvalidSNLength");
                            exit();
                        }
                        $err = checkSNString($newSN);
                        if($err >= 2){
                            header("Location: $baseURL?&page=0&msg=InvalidSNFormat");
                            exit();
                        }
                        $payload['newSN'] = $newSN;
                    }

                    if(!isset($device[$d])){
                        // invalid device sent
                        header("Location: $baseURL?msg=InvalidD");
                        exit();
                    } else if($d != 0){
                        $payload['d'] = $d;
                    }

                    if(!isset($company[$c])){
                        header("Location: $baseURL?msg=InvalidC");
                        exit();
                    } else if ($c != 0){
                        $payload['c'] = $c;
                    }

                    // validators for sn
                    if(!checkSNLen($sn)){
                        header("Location: $baseURL?msg=InvalidLength");
                        exit();
                    }	
                    if(checkSNString($sn) >= 2){
                        header("Location: $baseURL?msg=InvalidSN");
                        exit();
                    }
                    $payload['sn'] = $sn;
                    $payload = json_encode($payload);
                    $res = curl_POST('update_equipment', $payload);
           
                    if(isset($res['Status']) && isset($res['MSG'])){
                        $msg = $res['MSG'];
                        switch($msg){
                            case "Success":
                                header('Location: https://qta422.eastus.cloudapp.azure.com?update200');
                                exit();
                            case 'No change made.':
                                header('Location: '.$baseURL.'?msg=noChange');
                                break;
                            case 'Missing required SN':
                                header('Location: '.$baseURL.'?msg=noSN');
                                break;
                            case 'Missing Params':
                                header('Location: '.$baseURL.'?msg=noCD');
                                break;
                            case "Invalid active param.":
                                header('Location: '.$baseURL.'?msg=InvalidActive');
                                break;
                            case "DNE":
                                header('Location: '.$baseURL.'?msg=DNE');
                                break; 
                            case "DB_ERROR":
                            case "OTHER_ERROR":
                            case "JSON_ERROR":
                                header('Location: '.$baseURL.'?msg=sys');
                                break;
                            case "New SN already exists":
                                header('Location: '.$baseURL.'?msg=SNE');
                                break;
                            default:
                                header('Location: '.$baseURL.'?msg=uERR');
                                break;
                        }
                    }
                    else{
                        print_r($res);
                    }
                }
                echo '<form method="post" action="'.$baseURL.'">'; ?>

                    <div class="form-group">
                        <label for="serialInput">Enter serial number to update:</label>
                        <div class="flex-container">
                            <span class="sn-prefix">SN-</span>
                            <input class="flex-item form-control border-fix" type="text" maxlength="81" required="true" id="sn" name="sn">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="serialInput">New Value:</label>
                        <div class="flex-container">
                            <span class="sn-prefix">SN-</span>
                            <input class="flex-item form-control border-fix" type="text" maxlength="81" id="newSN" name="newSN">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="device">Device:</label>
                        <select class="form-control" name="device" id="device">
                            <?php
                                foreach($device as $key=>$value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                                
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="company">Manufacturer:</label>
                        <select class="form-control" name="company" id="company">
                            <?php
                                foreach($company as $key=>$value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';	
                            ?>
                        </select>
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