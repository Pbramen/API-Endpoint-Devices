<?php

function curl_GET($url, $data){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/api/'.$url.$data);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); // delete this in real-world apps
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // delete this in real-world apps
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
    $json = curl_exec($curl);
    if(curl_errno($curl) != 0){
        echo curl_errno($curl);
        echo curl_error($curl);
    }
    curl_close($curl);
    $json = json_decode($json, true);
  
    return $json;
}

function curl_POST($url, $data){
	$ch = curl_init("https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/api/".$url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DANGEROUS ignore self-signed ssl.
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_POST, 1); // post
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // fill data
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // response
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/json')); // content type and length of data sent by requester

	$result = curl_exec($ch);
	if($result == false){
		echo 'Curl error ('.curl_errno($ch).'): '.curl_error($ch);
		exit();
	}
	curl_close($ch);
	$result = json_decode($result, true);
	return $result;
}

function curl_PUT($url, $data){
	$ch = curl_init("https://ec2-18-117-229-80.us-east-2.compute.amazonaws.com/api/".$url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DANGEROUS ignore self-signed ssl.
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Put
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // fill data
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // response
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/json')); // content type and length of data sent by requester

	$result = curl_exec($ch);
	if($result == false){
		echo 'Curl error ('.curl_errno($ch).'): '.curl_error($ch);
		exit();
	}
	curl_close($ch);
	$result = json_decode($result, true);
	return $result;
}
?>