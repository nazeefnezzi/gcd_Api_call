<?php
#set_time_limit(2000);
ini_set('max_execution_time', 2000);

// import file

require_once(__DIR__ . "/services/generate_csv.php");


$baseurl = "https://maps.googleapis.com/maps/api/geocode/json?address=";
$API_key = "&key=";
$looparr = [];
#$source_file = __DIR__ . "/csv/sourcedata.csv";
$source_file = __DIR__ . "/csv/arngr_bkp.csv";
if (!file_exists($source_file)) {
    throw new Exception('Source data is not found.');
    exit();
}

if (($handel = fopen($source_file, "r")) !== false) {
    if (($data = fgetcsv($handel, 1000, ";")) !== false) {
        $keys = $data;
    }
    while (($data = fgetcsv($handel, 1000, ";")) !== false) {
        $looparr[] = array_combine($keys, $data);
    }
    fclose($handel);
}

// exit();
$i = 0;
$api_test_arr = [];
foreach ($looparr as $item) {
    $d_address = $item['SCHOOL_NAME'] . " + " . $item['VILLAGE_NAME'] . " + " . $item['BLOCK_NAME'] . " + " . $item['PINCODE'];
    $url[$i] = $d_address;

    $api_test_arr[$i] = test_call_map(urlencode($url[$i]), $baseurl, $API_key);
    foreach ($api_test_arr as $r) {

        if (is_array($r)) {

            $final_arr[$i]  = [

                'SchoolCode' => $item['SCHOOL_CODE'],
                'SchoolName' => $item['SCHOOL_NAME'],
                'VillageName' => $item['VILLAGE_NAME'],
                'PinCode' => $item['PINCODE'],
                'District' => $item['DISTNAME'],
                'lattitude' => $r['lat'],
                'longitude' => $r['long']
            ];
        }
    }

    $i++;
}

#++++++++++++++++++++++++++++++++++++#
# ------- Generate CSV --------------#
#++++++++++++++++++++++++++++++++++++#
echo "Generating Target file";


$csv_file_path = __DIR__ . "/csv/result.csv";
$csv_header = array_keys($final_arr[0]);
generateCsv($csv_file_path, $csv_header, $final_arr);

#++++++++++++++++++++++++++++++++++++#
# ------- call map api --------------#
#++++++++++++++++++++++++++++++++++++#
function test_call_map($url, $baseurl, $API_key)
{

    $curl = curl_init();
    // curl_setopt_array($curl, $defaults);
    curl_setopt_array($curl, array(
        CURLOPT_URL => $baseurl . $url . $API_key,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $curl_response = curl_exec($curl);

    curl_close($curl);
    $latlong = json_decode($curl_response, true);
    foreach ($latlong as $ltld) {
        if (is_array($ltld)) {

            $tinyarr = [
                'lat' => @$ltld['0']['geometry']['location']['lat'],
                'long' => @$ltld['0']['geometry']['location']['lng']
            ];
        }
    }
    return $tinyarr;
}
