<!-- Separte form file to keep things more readible  -->
<form method="post" action="">
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
				<label for="serialInput">Serial Number:</label>
				<div class="flex-container">
					<span class="sn-prefix">SN-</span>
					<input class="flex-item form-control border-fix" type="text" maxlength="81" required="true" id="serialInput" name="serialnumber">
				</div>
			</div>
				<button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
		   </form>

	   </div>
  </div>
</section>