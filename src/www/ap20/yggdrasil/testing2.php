<?php

/***************************************************************************
 *   testing2.php (simplexml)                                              *
 *   Yggdrasil: Miscellaneous tests by sms                                 *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
$title = "Tests2"; 
require "./header.php";

$relpath='x/';
$test1 = "";
$test2 = "";
$test3 = "test3";
$test4 = "";
$test5 = "";

if ($test1) {
    /* .............................. xml file as a table */
    echo "<div class=\"normal\">";

    $f = $relpath.'test/test-n.xml';
    $xp_spec = '//tr/td[1]';
    $title = "Table, column names";
    echo "<h2>[test1] A. $dbname $title -- simplexml_load_file f[$f] xp_spec[$xp_spec]</h2>\n";
    $xpath = simplexml_load_file( $f )->xpath( $xp_spec );
    foreach( $xpath as $node ) {
       echo para((string)$node);
    }
    echo "</div>";
    echo "<hr/>";
}
else { echo "<p>skipped test 1.</p>"; }

if ($test2) {
    /* ...................... comvict life course */
    echo "<div class=\"normal\">";
    $f = $relpath.'test/c31a31330090';
    $title = "Convict life course";
    $xp_spec = '//result';
    $xp_spec = "//list[@type='events']//event";
    echo "<h2>[test2] 1. $dbname $title -- simplexml_load_file f[$f] xp_spec[$xp_spec]</h2>\n";
    $xml = simplexml_load_file( $f );
    $xpath = $xml->xpath( $xp_spec );
    text_eac("",$xpath, 'list',  array('type'=>1,'desc'=>1,'id'=>1, 'when'=>1) );
    echo "</div>";
    echo "<hr/>";
}
else { echo "<p>skipped test 2.</p>"; }


if ($test3) {
    /* .............................. xml via a rest call */
    echo "<div class=\"normal\">";
    $restxq_request = '/ap20/'; # WORKS!!! Best to add iframe for results
    $restxq_request = '/hello/World'; # WORKS!!!
    $restxq_request = '/id/dcm8'; # WORKS!!!
    $restxq_request = '/stats';
    $restxq_request = '/hello/xxx'; # WORKS!!! on dev but not on y1 yet  bxrxq '/hello/World' works??
    $xml = get_restxq_xml2($restxq_request); /* debug */ 
    //file_put_contents("/tmp/ygg_get_restxq_xml2", $xml);
    if ($xml) {
        $xp_data = simplexml_load_string($xml);
        $xp_spec = '/*/*'; /* means whatever the root element is */
        $root_name = $xp_data->getName();
        $title = "&lt;$root_name&gt;";
        echo "<h2>[$test3] 2.(get_restxq_xml) \$basex_restxq[$basex_restxq] \$restxq_request[$restxq_request]<br/> returned $title</h2>\n";
        if ($root_name == "html") { echo $xml . '<iframe id="results" width="100%" height="400" scrolling="yes"></iframe>'; }
        else {
            $xp_spec = '/*/*'; /* means whatever the root element is */
            $xml_fields = $xp_data->xpath( $xp_spec );
            text_eac("",$xml_fields, 'list');
        }
    }
    else {
        echo "<h2>[$test3] 2.(get_restxq_xml) failed (possible timeout. check get_url). empty \$xml \$basex_restxq[$basex_restxq] \$restxq_request[$restxq_request]</h2>\n";
    }
}
else { echo "<p>skipped test 3.</p>"; }

$do_if = 1;
$recid = "ert01709953";
#$recid = "ccc86817";
$xp_spec = '/*/*'; 
if ($do_if) {
    // so it works from an iframe, so what is my curl calling doing wrong?????????????????????
    echo "<h2>Integrated xquery id service in an iframe; id[$recid]</h2>";
    echo para('iframe start --- XXXXX its xml, needs to be rendered server side?');
    echo '<iframe src="'.$basex_restxq_iframe.'/id/'.$recid.'" width="760" height="1000" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe>';
    echo para('------------------------------- iframe end');
}

if ($test4) {
    echo "<h2>4.xml ASIS: (get_restxq_xml) $dbname $title -- simplexml_load_string recid[$recid] xp_spec[$xp_spec]</h2>\n";
    echo "<pre>raw xml[$xml]</pre>";
}
else { echo "<p>skipped test 4.</p>"; }

if ($test5) {
    echo "<h2>5. xml2html: (get_restxq_xml) $dbname $title -- simplexml_load_string recid[$recid] xp_spec[$xp_spec]</h2>\n";
    $xml_fields = $xp_data->xpath( $xp_spec );
    text_eac("",$xml_fields, 'list');
    echo "</div>";
    echo "<hr/>";
}
else { echo "<p>skipped test 5.</p>"; }

echo "<hr/>";

/* .............................. xml via get_external_xml  */
echo "<div class=\"normal\">";

$recid = "dcm8";
$xml = get_external_xml($recid,""); file_put_contents("/tmp/ygg_xml", $xml);
$xp_data = simplexml_load_string($xml);
$xp_spec = '/*/*'; 
$root_name = $xp_data->getName();
$title = "&lt;$root_name&gt; id[$recid]";
echo "<h2>(get_external_xml) $dbname $title -- basex_server[$basex_server] simplexml_load_string recid[$recid] xp_spec[$xp_spec]</h2>\n";
$xml_fields = $xp_data->xpath( $xp_spec );
text_eac("",$xml_fields, 'list');
echo "</div>";
echo "<hr/>";


/* .............................. xml file as a fas src record */
echo "<div class=\"normal\">";
$f = $relpath.'test/basex-xmldbAjax/test3.xml';
$xp_data = simplexml_load_file( $f );
$xp_spec = '/*/*'; 
$root_name = $xp_data->getName();
$rec = $xp_data->xpath('/*');

$id = $rec[0]->attributes()->id; 
$title = "&lt;$root_name&gt; id[$id]";
echo "<h2>$dbname $title -- XXXXXXXXXXXXXXXXXXXsimplexml_load_file f[$f] xp_spec[$xp_spec]</h2>\n";
$xml_fields = $xp_data->xpath( $xp_spec );

text_eac("",$xml_fields, 'list');

/*
echo "<pre>var_dump(\$xml_fields):\n"; echo var_dump($xml_fields); echo "</pre>";
echo "<pre>var_dump(\$rec[0]):\n"; echo var_dump($rec[0]); echo "</pre>";
*/
echo "</div>";
echo "<hr/>";


echo '<iframe src="nyi.php" width="760" height="300" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe>';

echo '</div>';

echo "<div class=\"normal\">";
echo "<h2>$dbname $title</h2>\n";

echo "</div>\n";

include "./footer.php";
?>

