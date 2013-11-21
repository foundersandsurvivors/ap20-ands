<?php

/***************************************************************************
 *   testing.php                                                           *
 *   Yggdrasil: Miscellaneous tests by sms                                 *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
$title = "Tests"; 
require "./header.php";

include "./test-barchart.php";

echo "<div class=\"normal\">";
echo "<h2>$dbname $title -- attempt edit of icd10 experimental spreadsheet with an embedded google form and curl</h2>\n";

$formkey='dGczUU5kOUY0SV9NTjYwNV8tVi01SXc6MQ';
$googleformURL = "https://spreadsheets.google.com/formResponse?formkey=$formkey";

echo para("this has possibilities: https://developers.google.com/apps-script/storing_data_spreadsheets");
echo para("formkey=dGczUU5kOUY0SV9NTjYwNV8tVi01SXc6MQ / See http://www.jazzerup.com/blog/google-forms-without-api");
// src="https://docs.google.com/spreadsheet/embeddedform?formkey=dGczUU5kOUY0SV9NTjYwNV8tVi01SXc6MQ&entry_0=10060"
// change /static --> https://docs.google.com/static

echo '<iframe src="./x/googleforms/test-icd10-form.html" width="760" height="1632" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe>';

echo '</div>';

echo "<div class=\"normal\">";
echo "<h2>$dbname $title -- live access to google spreadsheet with query</h2>\n";

if ( isset($google_authtoken) ) {

    $key = '0AkHAk0-MPMbwdHlfd01xVUVTWDFVZzhaM005Mk1Zc0E'; // Aboukir_1852_M280_om_b4a370.26_vjs
    $key = '0AoiyjW0rNNcCdGczUU5kOUY0SV9NTjYwNV8tVi01SXc'; // khrd icd10 experiment
    $name = "khrd_icd_demo_incomplete";
    $desc = "Spreadsheet Len, Gary, Sandra ICD10 encoding test";
    $apiquery = 'select count(A)';
    $apiquery = 'select A,B,C,F,M,Z,AJ where A=10004';
    $apiquery = 'select A,B,C,F,M,Y,AJ,AK,AL,AM,AN,AR,AS,AW,AX,CA';
    $apiquery = 'select A,B,C,F,M,N,O,P,Q where L="Lake Tyers Aboriginal Station"';


$apiquery = 'select A,H,L,M,N,O,P,T,U,Y,Z,AD,AE,AI,AJ,AN,AO where A=10158';

    echo ("<h3>$name: $desc</h3>");
    echo para("Below is an extract based on query: <b>$apiquery</b>. You user[$authuser] have been authorised to access this spreadsheet on google by $google_shared_username, AND if you are logged into Google drive, you can go directly to the spreadsheet: <a target=\"_new\" href=\"https://docs.google.com/spreadsheet/ccc?key=$key\">$name</a>");

    $curl = curl_init();
    $headers = array( "Authorization: GoogleLogin auth=" . urlencode($google_authtoken), "GData-Version: 3.0",);
    curl_setopt($curl, CURLOPT_URL, 'https://spreadsheets.google.com/tq?tqx=out:html&tq='.urlencode($apiquery).'&key='.$key);
    //curl_setopt($curl, CURLOPT_URL, 'https://spreadsheets.google.com/feeds/spreadsheets/private/full');
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, false);
    $r = curl_exec($curl);
    curl_close($curl);
    echo $r;
    echo "<hr/>";

    echo ("<h3>icd10_deathCause_from_google_for_person 10158 (via json, custom formatting to html):</h3>");
    echo icd10_deathCause_from_google_for_person($key,10158,'json');

}
else {
    echo "Sorry but user[$authuser] is not authorised to access google documents for database[$dbname].";
}

echo "</div>\n";

include "./footer.php";
?>

