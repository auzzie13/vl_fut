<?php

include 'config.php';

function postForm($form_data) {
     $data = array(
    'token' => $GLOBALS['api_token'],
    'content' => 'record',
    'format' => 'json',
    'type' => 'flat',
    'overwriteBehavior' => 'normal',
    'forceAutoNumber' => 'false',
    'data' => json_encode($form_data),
    'dateFormat' => 'YMD',
    'returnContent' => 'count',
    'returnFormat' => 'json'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GLOBALS['api_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    $output = curl_exec($ch);
    print $output;
    // print "Successfully added: " + "file.";

    
    curl_close($ch);
};

function getNextRepeatInstance($record_id, $instrument_name) {
    $data = [
        'token' => $GLOBALS['api_token'],
        'content' => 'record',
        'format' => 'json',
        'type' => 'flat',
        'records' => [$record_id],
        'forms' => [$instrument_name],
        'fields' => ['redcap_repeat_instance'],
        'rawOrLabel' => 'raw',
        'rawOrLabelHeaders' => 'raw',
        'exportCheckboxLabel' => 'false',
        'returnFormat' => 'json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GLOBALS['api_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $records = json_decode($response, true);
    if (!$records || !is_array($records)) {
        return 1; // No existing instances found
    }

    $max_instance = 0;
    foreach ($records as $rec) {
        if (isset($rec['redcap_repeat_instance'])) {
            $instance = intval($rec['redcap_repeat_instance']);
            if ($instance > $max_instance) {
                $max_instance = $instance;
            }
        }
    }
    return $max_instance + 1;
}
