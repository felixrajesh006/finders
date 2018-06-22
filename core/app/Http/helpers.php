<?php

//
//  This is a Helper Module can be called across by all the functions up there needs, Following are the Function used in it.
//    pr, pre, datetoapi, datetimetoapi, datetodb, createddate, createddatetime
//
//  Helper              : Helper functions
//  Author              : Jeya Prakash
//  Project             : 2iequip
//
// Print array with pre tag 
function pr($param) {
    echo "<pre>";
    print_r($param);
    echo "</pre>";
}

// Print array with pre tag and exit
function pre($param) {
    echo "<pre>";
    print_r($param);
    echo "</pre>";
    exit();
}

// DB Date format to api Date format
function datetoapi($date) {
    if ($date != '' && (strpos($date, '/') == false) && (strpos($date, '-') == true)) {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);

        if (getallheadersdata() == 1) {
            return $new_str->format('d/m/Y H:i:s');
        } else {
            return $new_str->format('Y-m-d H:i:s');
        }
    } else {
        
    }
}

function datetimetoapi($date) {
    if ($date != '' && (strpos($date, '/') == false) && (strpos($date, '-') == true)) {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);

        if (getallheadersdata() == 1) {
            return $new_str->format('d/m/Y H:i:s');
        } else {
            return $new_str->format('Y-m-d H:i:s');
        }
    }
}

// DB DateTime format to api DateTime format
function dateonly($date) {
    if ($date != '') {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str->format('d/m/Y');
    }
    return $date;
}

// DB Date format to api Date format
function datetodb($date) {
    $date = explode("/", $date);
    return str_replace(' 00:00:00', '', $date[2]) . '-' . $date[1] . '-' . $date[0];
}

// DB Date format to api Date format
function convertdatetoDB($date) {
    return date('Y-m-d', strtotime(str_replace('/', '-', $date)));
}

// DB DateTime format to api DateTime format
function datetimetodb($date) {
    $time = explode(" ", $date);
    $dateate = explode("/", $time[0]);
    // if date in yyyy-mm-dd format return the same value - calendar 
    if (count($dateate) < 2) {
        return $date;
    }

    $datetimestring = $dateate[2] . '-' . $dateate[1] . '-' . $dateate[0] . ' ' . $time[1];
    if ($datetimestring != '' && (strpos($datetimestring, '/') == false) && (strpos($datetimestring, '-') == true)) {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($datetimestring, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str->format('Y-m-d H:i:sP');
    }
}

// DB DateTime format to api DateTime format
function datetimewithouttimezone($date) {
    if ($date != '' && (strpos($date, '/') == true) && (strpos($date, '-') == false)) {
        $time = explode(" ", $date);
        $dateate = explode("/", $time[0]);
        $datetimestring = $dateate[2] . '-' . $dateate[1] . '-' . $dateate[0] . ' ' . $time[1];
        if ($datetimestring != '') {
            if (date_default_timezone_get()) {
                $la_time = new DateTimeZone(date_default_timezone_get());
            } else {
                $la_time = new DateTimeZone('UTC');
            }
            $new_str = new DateTime($datetimestring, $la_time);
            $new_str->setTimezone($la_time);
            return $new_str->format('Y-m-d H:i:s');
        }
    }
}

// Create current Date
function createddate($date = FALSE) {
    $date = date('Y-m-d');
    if ($date != '') {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str->format('Y-m-d');
    }
}

// Create current Date
function getdateformat($date = '', $format = '/') {

    if ($date == '') {
//        $date=date('Y-m-d');
        return '';
    }

    $date = str_replace("/", "-", $date);

    if ($date != '') {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);
        if ($format == '/') {
            return $new_str->format('d/m/Y');
        } else {
            return $new_str->format('d-m-Y');
        }
    }
}

// Create current DateTime
function createddatetime() {
//    return $date = date('Y-m-d H:i:sO');
    return $date = date('Y-m-d H:i:s');
}

// Create current DateTime
function createddatetimeonly() {
    $date = date('Y-m-d H:i:s');
    if ($date != '') {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str->format('Y-m-d H:i:s');
    }
}

// Create current DateTime
function servertime() {
    return date('Y-m-d H:i:s');
}

// Create current DateTime
function createddatetimetimestamp() {
    return date('YmdHis');
}

// Change date Format in foreach
function dateformatinforeach($array) {
    foreach ($array as $key => $value) {
        foreach ($value as $k => $v) {
            $impoldvalue = explode('_', $k);
            if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date')
                if (isset($v) && $v != '' && (strpos($v, '/') == false) && (strpos($v, '-') == true))
                    $array[$key]->$k = datetoapi($v);
        }
    }
    return $array;
}

// Change date time Format in foreach
function datetimeformatinforeach($array) {
    foreach ($array as $key => $value) {
        foreach ($value as $k => $v) {
            $impoldvalue = explode('_', $k);
            if (isset($impoldvalue[1]) && $impoldvalue[1] == 'datetime')
                if (isset($v) && $v != '')
                    $array[$key]->$k = datetimetoapi($v);
        }
    }
    return $array;
}

// Create Password for new user
function createpassword() {
    $alphabet = 'ajlmWXokq5E6p7SsdfT80DwnU2_9NVxyzABrst)QRuv1dedOP3J&4*79CLFbcIdeKM!@#$%^fghiG*(YHZ';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 10; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass) . date('YmdHis');
}

// Create Password for new user
function customeridandsecret() {
    $alphabet = 'ajlmWXokq5E6p7ST80DwnU2_9Nasdf VxyzABrst)QRuv1e275*rtfce5OP3J&4CLFbcIdeKM!@#$%^fghiG*(YHZ';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 10; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return md5(implode($pass) . date('YmdHis'));
}

// DB Date format to api Date format
function datemonthyeartoyearmonthdate($date) {
    if ($date != '') {
        $explodedate = explode("/", $date);
        $year = $explodedate[2];
        $month = $explodedate[1];
        $date = $explodedate[0];
        return $year . "-" . $month . "-" . $date;
    }
    return '';
}

function taskIDReplace($ticketID, $Message) {
    return str_ireplace("{{taskID}}", $ticketID, $Message);
}

function taskOwnerReplace($ticketID, $Message) {
    return str_ireplace("{{taskOwner}}", $ticketID, $Message);
}

function ticketIDReplace($ticketID, $Message) {
    return str_ireplace("{{ticketID}}", $ticketID, $Message);
}

function defectReplace($defect, $Message) {
    return str_ireplace("{{Defect}}", $defect, $Message);
}

function usernameReplace($userName, $Message) {
    return str_ireplace("{{userName}}", $userName, $Message);
}

function companynameReplace($companyName, $Message) {
    return str_ireplace("{{companyName}}", $companyName, $Message);
}

function agencynameReplace($agencyName, $Message) {
    return str_ireplace("{{agencyName}}", $agencyName, $Message);
}

function engineernameReplace($engineerName, $Message) {
    return str_ireplace("{{engineerName}}", $engineerName, $Message);
}

function tentativeTimeReplace($tentativeReachTime, $Message) {
    return str_ireplace("{{tentativeReachTime}}", $tentativeReachTime, $Message);
}

function reasonReplace($reason, $Message) {
    return str_ireplace("{{reason}}", $reason, $Message);
}

function timeReplace($time, $Message) {
    return str_ireplace("{{time}}", $time, $Message);
}

function sparepartsIDReplace($spareRequestID, $Message) {
    return str_ireplace("{{spareRequestID}}", $spareRequestID, $Message);
}

function dispatchedThroughReplace($dispatchedThrough, $Message) {
    return str_ireplace("{{dispatchedThrough}}", $dispatchedThrough, $Message);
}

function locationReplace($replaceLocationName, $Message) {
    return str_ireplace("{{location}}", $replaceLocationName, $Message);
}

function notificationStringReplacement($notificationMessage, $replaceString) {
    $message = $notificationMessage;
    if (isset($replaceString['replaceTicketID'])) {
        $message = ticketIDReplace($replaceString['replaceTicketID'], $message);
    }
    if (isset($replaceString['replaceTaskID'])) {
        $message = taskIDReplace($replaceString['replaceTaskID'], $message);
    }
    if (isset($replaceString['replaceTaskOwner'])) {
        $message = taskOwnerReplace($replaceString['replaceTaskOwner'], $message);
    }

    if (isset($replaceString['replaceDefect'])) {
        $message = defectReplace($replaceString['replaceDefect'], $message);
    }
    if (isset($replaceString['replaceUserName'])) {
        $message = usernameReplace($replaceString['replaceUserName'], $message);
    }
    if (isset($replaceString['replaceCompanyName'])) {
        $message = companynameReplace($replaceString['replaceCompanyName'], $message);
    }
    if (isset($replaceString['replaceAgencyName'])) {
        $message = agencynameReplace($replaceString['replaceAgencyName'], $message);
    }
    if (isset($replaceString['replaceTentativeReachTime'])) {
        $message = tentativeTimeReplace($replaceString['replaceTentativeReachTime'], $message);
    }
    if (isset($replaceString['replaceEngineerName'])) {
        $message = engineernameReplace($replaceString['replaceEngineerName'], $message);
    }
    if (isset($replaceString['replaceReason'])) {
        $message = reasonReplace($replaceString['replaceReason'], $message);
    }
    if (isset($replaceString['replaceTime'])) {
        $message = timeReplace($replaceString['replaceTime'], $message);
    }
    if (isset($replaceString['replaceSpareRequestID'])) {
        $message = sparepartsIDReplace($replaceString['replaceSpareRequestID'], $message);
    }
    if (isset($replaceString['replaceDispatchedThrough'])) {
        $message = dispatchedThroughReplace($replaceString['replaceDispatchedThrough'], $message);
    }
    if (isset($replaceString['replaceLocationName'])) {
        $message = locationReplace($replaceString['replaceLocationName'], $message);
    }
    if (isset($replaceString['replaceScheduleName'])) {
        $message = scheduleNameReplace($replaceString['replaceScheduleName'], $message);
    }
    if (isset($replaceString['replaceScheduleID'])) {
        $message = scheduleIDReplace($replaceString['replaceScheduleID'], $message);
    }
    if (isset($replaceString['replaceTaskName'])) {
        $message = taskNameReplace($replaceString['replaceTaskName'], $message);
    }
    if (isset($replaceString['replaceAssetName'])) {
        $message = assetNameReplace($replaceString['replaceAssetName'], $message);
    }
    if (isset($replaceString['replaceTaskType'])) {
        $message = taskTypeReplace($replaceString['replaceTaskType'], $message);
    }
    if (isset($replaceString['replaceModelName'])) {
        $message = ModelNameReplace($replaceString['replaceModelName'], $message);
    }
    if (isset($replaceString['replaceserialNumber'])) {
        $message = serialNumberReplace($replaceString['replaceserialNumber'], $message);
    }
    if (isset($replaceString['replaceCustomerName'])) {
        $message = customerNameReplace($replaceString['replaceCustomerName'], $message);
    }
    return $message;
}

function customerNameReplace($replaceCustomerName, $Message) {
    return str_ireplace("{{customer}}", $replaceCustomerName, $Message);
}

function taskTypeReplace($replaceScheduleName, $Message) {
    return str_ireplace("{{taskType}}", $replaceScheduleName, $Message);
}

function scheduleNameReplace($replaceScheduleName, $Message) {
    return str_ireplace("{{scheduleName}}", $replaceScheduleName, $Message);
}

function scheduleIDReplace($replaceScheduleID, $Message) {
    return str_ireplace("{{scheduleID}}", $replaceScheduleID, $Message);
}

function taskNameReplace($replaceTaskName, $Message) {
    return str_ireplace("{{taskName}}", $replaceTaskName, $Message);
}

function assetNameReplace($ticketID, $Message) {
    return str_ireplace("{{assetName}}", $ticketID, $Message);
}

function ModelNameReplace($replaceModelName, $Message) {
    return str_ireplace("{{modelName}}", $replaceModelName, $Message);
}

function serialNumberReplace($replaceSerialNumber, $Message) {
    return str_ireplace("{{serialNumber}}", $replaceSerialNumber, $Message);
}

function convertdatetime($date, $timezone = NULL) {

    if (!empty($timezone)) {
        $timezone = new DateTimeZone($timezone);
    } else {
        $timezone = new DateTimeZone('UTC');
    }
    $time = new DateTime($date);
    $time->setTimezone($timezone);
    $currenttime = strtotime($time->format('Y-m-d H:i:s'));

    return $currenttime;
}

function convertdatetimeusertimezone($date, $timezone = NULL) {

    if (!empty($timezone)) {
        $timezone = new DateTimeZone($timezone);
    } else {
        $timezone = new DateTimeZone('UTC');
    }

    $time = new DateTime($date);
    $time->setTimezone($timezone);
    $currenttime = $time->format('Y-m-d H:i:s');
    return $currenttime;
}

function createdatetimewithcustomertimezone($date, $timezone = NULL) {

    if (!empty($timezone)) {
        $timezone = new DateTimeZone($timezone);
    } else {
        $timezone = new DateTimeZone('UTC');
    }
    $time = new DateTime($date);
    $time->setTimezone($timezone);
    $currenttime = $time->format('Y-m-d H:i');

    return $currenttime;
}

// Create current DateTime
function currentdatetimeofcustomer($timezone = NULL) {
    $date = date('Y-m-d H:i:s');
    if ($date != '') {
        if (!empty($timezone)) {
            $la_time = new DateTimeZone($timezone);
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str->format('Y-m-d H:i');
    }
}

function trimtime($date) {
    return date('H:i', strtotime($date));
}

function getallheadersdata() {
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }

    if (isset($headers['Logintype']) || isset($headers['logintype'])) {
        return 0;
    } else {
        return 1;
    }
}

// Add Mintus to today date
function addtimetocurrenttime($mintus) {
    $date = date('Y-m-d H:i:s', strtotime("+" . $mintus . " minutes"));
    if ($date != '') {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str;
    }
}

// Replace double space
function removedoublespace($input) {
    $result = preg_replace('!\s+!', ' ', trim($input));
    return $result;
}

// Replace double space and case lower
function removedoublespaceandlower($input) {
    $result = preg_replace('!\s+!', ' ', trim($input));
    return strtolower($result);
}

// Get L
function getlatlang($input) {
    $url = "http://maps.google.com/maps/api/geocode/json?address=$input&sensor=false";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response);
    $lat = $response_a->results[0]->geometry->location->lat;
    $long = $response_a->results[0]->geometry->location->lng;
    return array('lat' => $lat, long => $long);
}

function expectedfinishtime($checkintime, $expectedtime) {
    $ntime = date('h:i:s', strtotime($expectedtime));
    list($hours, $minutes, $seconds) = explode(":", $ntime);
    return date('H:i', strtotime('+' . $hours . ' hour' . ' +' . $minutes . ' minutes', strtotime($checkintime)));
}

function expectedcompletetime($checkintime, $checkouttime) {
    $date1 = $checkintime;
    $date2 = $checkouttime;
    $diff = abs(strtotime($date2) - strtotime($date1));

    $years = floor($diff / (365 * 60 * 60 * 24));
    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
    $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

    $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));

    $minuts = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);

    $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minuts * 60));

    return $hours . ' : ' . $minuts . ' : ' . $seconds;
}

function quotereplace($str) {
    $value = str_replace(array('\'', '"'), '', $str);
    return $value;
}

function getMessageValue($getmessage, $userid = 'null') {
    $getlist = Cache::get($userid);
    if (isset($getlist[$getmessage])) {
        return $getlist[$getmessage];
    } else {

        return $getmessage;
    }
}

function getVariableValue($variablename, $getvalue, $language = 'en') {
    $getmessagejsonpath = storage_path() . "/constants/variable_" . $language . ".json";
    $getlist = json_decode(utf8_encode(file_get_contents($getmessagejsonpath)), true);
    $getvariablename = $getlist['response'][$variablename];
    foreach ($getvariablename as $key => $value) {
        if ($value['id'] == $getvalue) {
            return $value['name'];
        }
    }
}

function getVariable($language) {
    $getmessagejsonpath = storage_path() . "/constants/variable_" . $language . ".json";
    $getlist = json_decode(utf8_encode(file_get_contents($getmessagejsonpath)), true);
    return $getlist;
}

function notificationFormation($replace, $message) {
    return str_replace(array_keys($replace), array_values($replace), $message);
}

// Create current DateTime
function convertoservertimezome($date) {
    if ($date != '') {
        $la_time = new DateTimeZone('UTC');
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str->format('Y-m-d H:i');
    }
}

// Create current DateTime
function convertoservertimezomefortime($date) {
    if ($date != '') {
        if (date_default_timezone_get()) {
            $user_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $user_time = new DateTimeZone('UTC');
        }
        $usertime_str = new DateTime($date, $user_time);
        $usertime_str->setTimezone($user_time);
        $usertime = $usertime_str->format('Y-m-d H:i:sP');
        $la_time = new DateTimeZone('UTC');
        $new_str = new DateTime($usertime, $la_time);
        $new_str->setTimezone($la_time);
        return $new_str->format('Y-m-d H:i:s');
    }
}

function calculatetimediff($timeone, $timetwo) {
    $datetime1 = new DateTime($timeone);
    $datetime2 = new DateTime($timetwo);
    $interval = $datetime1->diff($datetime2);
    return $interval->format('%h') . " Hours " . $interval->format('%i') . " Minutes";
}

function converservertimetousertimewithtimezone($date) {
    if ($date != '' && (strpos($date, '/') == false) && (strpos($date, '-') == true)) {
        if (date_default_timezone_get()) {
            $la_time = new DateTimeZone(date_default_timezone_get());
        } else {
            $la_time = new DateTimeZone('UTC');
        }
        $new_str = new DateTime($date, $la_time);
        $new_str->setTimezone($la_time);

        return $new_str->format('Y-m-d H:i:sO');
    }
}

function getCurrentTimeZone() {
    if (date_default_timezone_get()) {
        $user_time = date_default_timezone_get();
    } else {
        $user_time = 'UTC';
    }
    return $user_time;
}

function getAddress($latitude, $longitude) {
    if (!empty($latitude) && !empty($longitude)) {
        $map = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($latitude) . ',' . trim($longitude) . '&sensor=false';
        $geocodeFromLatLong = file_get_contents($map);
        $output = json_decode($geocodeFromLatLong);
        $status = $output->status;
        $address = ($status == "OK") ? $output->results[1]->formatted_address : '';
        if (!empty($address)) {
            return $address;
        } else {
            $geocodeFromLatLong = file_get_contents($map);
            $output = json_decode($geocodeFromLatLong);
            $status = $output->status;
            $address = ($status == "OK") ? $output->results[1]->formatted_address : '';
            if (!empty($address)) {
                return $address;
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
}

function converdatetimetodisplayformat($date) {
    if ($date != '') {
        $new_str = strtotime($date);
        return date('d-M-Y H:i:s', $new_str);
    }
}
