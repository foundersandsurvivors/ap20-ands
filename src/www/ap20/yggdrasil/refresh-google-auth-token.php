<?php

/***************************************************************************
 *   refresh-google-auth-token.php                                         *
 *   Yggdrasil: Refresh a coogle auth token for google docs integration    *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
$title = "Tests"; 
require "./header.php";

echo "<div class=\"normal\">";
echo "<h1>$dbname $title -- QGoogleVisualizationAPI v.0.2 by Tom Schaefer</h1>\n";
echo "</div>\n";

include "./test-barchart.php";

echo "<div class=\"normal\">";
echo "<h1>$dbname $title -- accessing a google spreadsheet</h1>\n";
echo "<pre>google_authtoken[$google_authtoken]\n";

if ( isset($google_authtoken) ) {

    $curl = curl_init();
    $headers = array(
        "Authorization: GoogleLogin auth=" . urlencode($google_authtoken),
        "GData-Version: 3.0",
    );

//echo "\nNEW REQUEST curl[$curl] .......................... start dump headers:";
//var_dump ($headers);
//echo "\n.......................... end dump headers";

    $key = '0AkHAk0-MPMbwdHlfd01xVUVTWDFVZzhaM005Mk1Zc0E'; // Aboukir_1852_M280_om_b4a370.26_vjs
    $key = '0AoiyjW0rNNcCdGczUU5kOUY0SV9NTjYwNV8tVi01SXc'; // khrd icd10 experiment

    $apiquery = urlencode('select count(A)');
    $apiquery = urlencode('select B,C,F,M,W where A=10004');

    // Make the request
    curl_setopt($curl, CURLOPT_URL, 'https://spreadsheets.google.com/tq?tqx=out:html&tq='.$apiquery.'&key='.$key);
    //curl_setopt($curl, CURLOPT_URL, 'https://spreadsheets.google.com/feeds/spreadsheets/private/full');
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_POST, false);

    $response2 = curl_exec($curl);
    curl_close($curl);


echo ".......................... dump response2:\n";
var_dump ($response2);
echo "\n.......................... dump response2 END\n";
}
else {
    echo "Sorry but user[$authuser] is not authorised to access google documents for database[$dbname].";
}

echo "</pre></div>\n";

include "./footer.php";
?>
