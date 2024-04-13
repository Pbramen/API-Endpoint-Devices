<?php
$fqdn = "https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/api/";	

$data = "";

$ch = curl_init($fqdn);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DANGEROUS ignore self-signed ssl.
curl_setopt($ch, CURLOPT_POST, 1); // post
curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // fill data
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // response
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'content-type: application/x-www-form-urlencoded','content-length: '.strlen($data))); // content type and length of data sent by requester


$result = curl_exec($ch);
if($result == false){
	echo 'Curl error ('.curl_errno($ch).'): '.curl_error($ch);
	exit();
}
curl_close($ch);
echo $result;
//$jsonResult = json_decode($result, true);
//print_r($jsonResult);
?>
