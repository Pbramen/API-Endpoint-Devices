<table class="table">
  <thead>
    <tr>
	  <th scope="col"></th>
      <th scope="col">Serial Number</th>
      <th scope="col">Device</th>
      <th scope="col">Manufacturer</th>
    </tr>
  </thead>
  <tbody>
    <?php
		foreach($result as $row){
			$sn = $row["sn"];
			$r = $row["r_id"];
			if(isset($device[$row["device_id"]])){
				$d = $device[$row["device_id"]];
			} else{
				// query for database
			}
			if(isset($company[$row["company_id"]])){
				$c = $company[$row["company_id"]];
			}
			else{
				// query for database
			}
			echo '<tr>';
			echo '<form method="post" action="/form/web/update/updater.php">';
				
				echo '<td><button >Edit</a></td>';
				echo '<input hidden="true" name="d" value="'.$d.'">';
	  			echo '<input hidden="true" name="c" value="'.$c.'">';
	  			echo '<input hidden="true" name="s" value="'.$sn.'">';
			    echo '<input hidden="true" name="r" value="'.$r.'">';
	  			
			echo '</form>';
           
			echo '<td>'.$sn.'</td>';
			echo '<td>'.$d.'</td>';
			echo '<td>'.$c.'</td>';
		}  
	?>
  </tbody>
</table>