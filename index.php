<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
 
 <!-- MENU -->
 	 <?php
          $root = '/var/www/html';
		//imports all base css
		//head tag ends and starts body tag
		include("./assets/php/components/templateCSS.php");
		
		include('./assets/php/components/nav.php');
	 ?>     
 <!-- HOME -->
     <section id="home">
          </div>
     </section>
     <!-- FEATURE -->
     <section id="feature">
          <div class="container">
               <div class="row">
                   <?php
                   if(isset($_REQUEST['msg'])){ 
						$msg = $_REQUEST['msg'];
					    switch($msg){
							case 'EquipmentAdded':
								echo '<div class="alert alert-success" role="alert">Equipment successfully added!</div>';
								break;
							case 'update200':
								echo '<div class="alert alert-success" role="alert">Equipment updated successfully!!</div>';
								break;
                                   case 'add200':
                                        echo '<div class="alert alert-success" role="alert">Equipment updated successfully!!</div>';
                                        break;
						}
				   }
                    ?>
                        
                    <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <h3>Search Equipment</h3>
                              <p>Click here to search the equipment database.</p>
                              <a href="./web/search/search.php" class="btn btn-default smoothScroll">Discover more</a>
                         </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <h3>Add Equipment</h3>
                              <p>Click here to add new equipment</p>
                             <a href="./web/add/addHome.php" class="btn btn-default smoothScroll">Discover more</a>
                         </div>
                    </div>
				   
				     <div class="col-md-4 col-sm-4">
                         <div class="feature-thumb">
                              <h3>Update Equipment</h3>
                              <p>Click here to update an equipment</p>
                             <a href="./web/update/update.php" class="btn btn-default smoothScroll">Discover more</a>
                         </div>
                    </div>

                    

               </div>
          </div>
     </section>
</body>
</html>