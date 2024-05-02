<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Update</title>
     <!-- MENU -->
     <?php
		include("../../assets/php/components/templateCSS.php");
		include("../../assets/php/components/nav.php");
	?>
	<!-- lOGO TEXT HERE -->
	<a href="#" class="navbar-brand">Update Equipment</a>
	
 <!-- HOME -->
 <section id="feature">
	  <div class="container">
		   <div class="row">
		<div class="col-md-4 col-sm-4">
			<div class="feature-thumb">
				<h3>Update Equipment</h3>
				<p>Click here to add new equipment.</p>
				<a href="./updateEquip.php" class="btn btn-default smoothScroll">Discover more</a>
			 </div>
		</div>
		<div class="col-md-4 col-sm-4">
			 <div class="feature-thumb">
				  <h3>Update Device</h3>
				  <p>Click here to add new device</p>
				 <a href="./updateAttrib.php?device=true" class="btn btn-default smoothScroll">Discover more</a>
			 </div>
		</div>

		 <div class="col-md-4 col-sm-4">
			 <div class="feature-thumb">
				  <h3>Update Company</h3>
				  <p>Click here to new an manufacturer</p>
				 <a href="./updateAttrib.php?company=true" class="btn btn-default smoothScroll">Discover more</a>
			 </div>
			</div>
		</div>
	 </div>
</section>


</body>
</html>