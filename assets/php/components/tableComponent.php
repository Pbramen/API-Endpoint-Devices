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

		if ($result['MSG'] == 'Success'){
			$row = $result["Payload"]['Fields'];
			for($i = 0; $i < $n; $i+= 1){
				$sn = $row[$i]["sn"]['value'];
				$d = $row[$i]['device'];
				$c = $row[$i]['company'];
				$r = $row[$i]["r_id"];
				if(isset($device[$d["id"]])){
					$d = $device[$d["id"]];
				} 
				if(isset($company[$c["id"]])){
					$c = $company[$c["id"]];
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
		}  
	?>
  </tbody>
</table>