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

		$baseURL= "https://qta422.eastus.cloudapp.azure.com/web/update/updateAttrib.php";
	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Update Device</a>
	
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
						    echo '<div class="alert alert-danger" role="alert">Unknown device selected.</div>';
						    break;
					   case 'NC':
                       case 'noChange':
						   echo '<div class="alert alert-warning" role="alert">No changes made. Please enter different values to modify device.</div>';
						    break;
                        case 'InvalidActive':
                            echo '<div class="alert alert-warning" role="alert">Invalid Status.</div>';
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
			<h1> Set new values for selected device: </h1>
			<!--FORM GROUP HERE-->   
            <?php	
			   // upon hitting the submit button...	   
			    if(isset($_POST["device"]) && isset($_POST['submit']) ){
                    $d = $_POST['device'];

                    $newD = "";
                    if(isset($_POST['newDevice'])){
                        $newD  = $_POST['newDevice'];
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

                   
                    //only accept already validated devices.
                    if(!isset($device[$d])){
                        // invalid device sent
                        header("Location: $baseURL?msg=InvalidD");
                        exit();
                    } else if($d != 0){
                        $payload['d'] = $d;
                    }
                    if($newD != "" ){
                        if(checkAlpha($newD, $newD)){
                            header("Location: $baseURL?msg=InvalidNewD");
                            exit();
                        }
                        $payload['new'] = $newD;
                    }
                    
                    $payload = json_encode($payload);
                    $res = curl_POST('update_device', $payload);
           
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
                            case "device already exists.":
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
                        <label for="device">Select Device:</label>
                        <select class="form-control" name="device" id="device">
                            <?php
                                foreach($device as $key=>$value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                                
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="serialInput">Set New Device:</label>
                            <input class="form-control" type="text" maxlength="32" id="newDevice" name="newDevice">
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