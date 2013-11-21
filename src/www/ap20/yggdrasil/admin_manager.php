<?php

/***************************************************************************
 *   admin_manager.php                                                     *
 *   Yggdrasil: System Administration functions                            *
 *   AP20 extension.                                                       *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require "./functions.php";
require_once "./langs/$language.php";

$title = "ADMINISTRATION";
require "./header.php";

if ( !$authuser or !isset($administrator[$authuser]) ) {
    echo para("Not authorised.");
    exit;
}

echo "<div class=\"normal\">\n";
echo "<h2>$title</h2>\n";

/* Experimental XML edit */

$admin_file = "$webwork/admin.xml";
$data = simplexml_load_file($admin_file);
$attrs = $data->attributes();
$master = $data['master'];
$installation = "standalone";
$isMaster = false;
if ($master == "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']) {
    $installation = "master"; 
    $isMaster = true;
}
elseif ($master) { $installation = "slave"; $isSlave = true; }
echo "<h3>Installation type: $installation</h3>";
if ( $installation == "slave" ) { echo to_url($master,array(),"Go to the master..."); }

/* show the instructions: html is embedded in the config file <header> (file=$admin_file) */

$instructions = $data->xpath('/*/header/child::*');
$xpath_str = '/*/text';
$con = $data->xpath($xpath_str);

foreach( $instructions as $node ) { echo $node->asXML(); }
echo "<pre>";
text_eac("",$con,'list',array('all'=>1));

$deployment_manager_config = $data->text->deployment_manager_config;
if ($isMaster) {
    $dmc = simplexml_load_file($deployment_manager_config);
}
elseif ($isSlave) {
    if ( file_exists($deployment_manager_config) ) {
    }
    else {
         echo para("..todo: get shared_services and smartsources from MASTER ${master}?copyforslave");
         exit;
    }
}

$hosts = $dmc->text->hosts;
$shared_services = $dmc->text->shared_services;
$domains = $dmc->text->domain;
$smartsources = $dmc->text->smartsource;

if ($isMaster) {
    echo "<h4>Deployment Manager. deployment_manager_config=[$deployment_manager_config]</h4>";

    echo "<h4>Domains:</h4>";
    foreach ( $domains as $n ) {

        echo "<h5>Domain: [".$n['id']."]</h5>";
        text_eac("text/domain[@id=\"".$n['id']."\"]",$n,'list',array('all'=>1));

        /* supported actions for a domain */

    }
/*
    echo "<hr/>".para("Config file whole:"); 
    text_eac("",$dmc,'list',array('all'=>1)); 
*/
}


/* List smart sources (class definitions of meta sources) and enable listing and creation */
// <p>Smart sources list form...$n['id']</p>

/*
<form id="$id" method="post" action="$_SERVER['PHP_SELF']">
<input type="submit" name="regenerate" value="Regenerate" />
</form>
*/

echo "<h4>Shared services</h4>";
foreach ($shared_services as $ss) {
   $service = $ss->service;
   foreach ( $service as $n ) {
        $id = $n['id'];
        $type = $n['type'];
        echo "<h5>Service: [$id]</h5>";
        text_eac("text/shared_services/service[@id=\"".$n['id']."\"]",$n,'list',array('all'=>1));
        if ( $n->url && $n->test ) {
             // allow multiple tests in any service
             foreach ( $n->test as $test_part ) {
                 $test_url = $n->url . $test_part;
                 echo "TEST: <a href=\"$test_url\">$test_url</a><br/>";
             }
        }
        // for code repos
        if ( $n->url && $n->repo ) {
             echo "<h5>Code repositories:</h5><ul>";
             foreach ( $n->repo as $r ) {
                 $raw_html = $r->pre->asXML();
                 //$raw_html = str_replace("\n", '', $raw_html ); // remove new lines
                 echo '<li>'."<a href=\"".$n->url.$r->name."\">".$r->name."</a>: $r->desc".$raw_html."</li>";
             }
             echo "</ul>";
        }
   }
   /*
   text_eac("",$ss,'list',array('all'=>1));
   foreach ($ss as $service) {
        text_eac("",$service,'para',array('all'=>1));
   }
   $gazeteer = $shared_services->gazetteer;
   */
}

echo "<h4>Smart sources</h4>";
foreach ( $smartsources as $n ) {
    $id = $n['id'];
    echo "<h5>Smart source class: [$id]</h5>";
    text_eac("",$n,'list',array('all'=>1));
    $list_template = $n->list_template;
    if ( $list_template->sql ) {

         echo "<hr/>asfunction pg_query_to_simplexml_element ###############################################################\n";
         $xml = pg_query_to_simplexml_element( $list_template->sql, '' );
         text_eac("",$xml,'list',array('all'=>1));
         echo "<hr/>";

         $h = pg_query( $list_template->sql );
         while ( $row = pg_fetch_assoc($h) ) {
            var_dump($row);
            $rx = array_to_xml($row, new SimpleXMLElement('<root/>'));
            text_eac("",$rx,'list',array('all'=>1));
         }

         $form = <<<SMARTSOURCELIST
<p>xxx: $id $list_template->sql</p>
<input type="submit" name="create_$id" value="Create a new $id" />

SMARTSOURCELIST;
         echo $form;
    }
}

echo "</pre>";

echo "</div>\n";
include "./footer.php";
?>
