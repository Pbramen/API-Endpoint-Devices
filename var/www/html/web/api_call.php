<?php
$fqdn = "https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/api/query_device";	

$data["d"] = "1";
$data = json_encode($data);

$ch = curl_init($fqdn);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DANGEROUS ignore self-signed ssl.
curl_setopt($ch, CURLOPT_POST, 1); // post
curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // fill data
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // response
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'content-type: application/json' )); // content type and length of data sent by requester


$result = curl_exec($ch);
if($result == false){
	echo 'Curl error ('.curl_errno($ch).'): '.curl_error($ch);
	exit();
}

header("Content-type: application/json");
curl_close($ch);
echo $result;

?>
