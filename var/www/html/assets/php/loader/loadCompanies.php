<?php
	$result2 = $db->query("Select * from `company`") or 
		die("\r\n$data->errno : $data->error\r\n");
	while ($data = $result2->fetch_array(MYSQLI_ASSOC)){
		$company[$data["company_id"]] = $data["company"];
	}
?>