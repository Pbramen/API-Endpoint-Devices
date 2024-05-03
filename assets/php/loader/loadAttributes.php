<?php
    include("/var/www/html/assets/php/curlHandler.php");
    function loadAttribute($atrb, &$fields, $active = 1 ){
        $url = 'query_'.$atrb.'/';
        $data = '?'.$atrb[0].'=0&active=0';
        $results = curl_GET($url, $data);
        if(validResponse($results, "Success")){
            foreach($results['Payload']['Fields'] as $obj){
                $fields[$obj['id']] = $obj['value'];
            }
        }
        return false;
    }


    function validResponse($json, $msg){
        return isset($json['Status']) && $json['Status'] == 200 && isset($json['MSG']) && $json['MSG'] == $msg && isset($json['Payload']) &&isset($json['Payload']['Fields']);
    }
?>