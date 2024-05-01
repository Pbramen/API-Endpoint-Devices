<!-- Genrates a search form  -->
<form method="get" action="" class="flex-container" >
		<div class="form-inline">			
			<div class="form-group" >
				<label for="device">Device:</label>
				<select class="form-control" name="device" id="device" default="0">
					<?php
						foreach($device as $key=>$value)
							echo '<option value="'.$key.'">'.$value.'</option>';
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="company">Manufacturer:</label>
				<select class="form-control" name="company" id="company" default="0">
					<?php
						foreach($company as $key=>$value)
							echo '<option value="'.$key.'">'.$value.'</option>';
					?>
				</select>
			</div>
			<div class="form-group" >
				<label for="serialInput">Serial Number:</label>
				<div class="input-group">
					<div class="input-group-addon">SN-</div>
					<input class="form-control border-fix" type="text" maxlength="81" id="serialInput" name="serialnumber">
				</div>
			</div>
			
			<?php
				if(isset($_GET['count']) && is_numeric($_GET['count']))
					echo '<input type="hidden" value='.$_GET['count'].' name="count"></input>';
				if( isset($_GET['limit']) && is_numeric($_GET['limit']))
					echo '<input type="hidden" value='.$_GET['limit'].' name="limit"></input>';
				if(isset($_GET['page']) && is_numeric($_GET['page'])){
					echo '<input type="hidden" value='.$_GET['page'].' name="page"></input>';
				}
			?>
			<button type="submit" class="btn btn-primary " name="submit" value="submit">Search</button>	
		</div>
			
			
		</form>

	   </div>
  </div>
</section>