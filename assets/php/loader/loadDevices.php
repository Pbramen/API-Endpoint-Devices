<?php
	$result = $db->query("Select * from `device`") or 
		die("\r\n$data->errno : $data->error\r\n");
	while ($data = $result->fetch_array(MYSQLI_ASSOC)){
		$device[$data['device_id']] = $data['device'];
	}
?>