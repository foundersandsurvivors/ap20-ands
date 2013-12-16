<?php

/***************************************************************************
 *   reports.php                                                           *
 *   Yggdrasil: Reports Page                                               *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
$title = "Reports"; 
require "./header.php";

$pcount_a = fetch_val("SELECT COUNT(*) FROM persons");
$pcount_m = fetch_val("SELECT COUNT(*) FROM merged");
$pcount_r = fetch_val("SELECT COUNT(*) FROM relations");
$pcount = $pcount_a - $pcount_m;
$scount = fetch_val("SELECT COUNT(*) FROM sources");
$scount_sb = fetch_val("SELECT COUNT(*) FROM sources where parent_id = 0");
$scount_t = fetch_val("SELECT COUNT(*) FROM templates");

if ( $_AP20 ) { 

    echo "<div class=\"normal\">\n<h1>Reports for [$dbname] on ",$_SERVER['HTTP_HOST'],"</h1>";

    # general stats
    echo "<h2>Overall counts:</h2>\n<ul>";
    echo li("$pcount_m merged persons (equivalent to LKT LINK) in total of $pcount_a person records.");    
    echo li("$pcount_r relationships (person-person).");
    echo li("$scount sources, $scount_t with templates. $scount_sb top level branches."); 
    echo "</ul>\n";

    # process dynamic report specs defined in xml

    $specs = simplexml_load_file("domain/report-specs.xml");
    $reportGrp = $specs->text->reportGrp;
    foreach ( $reportGrp as $rg ) {
        if (isset($rg['domain']) && ! preg_match("/\b".$mydomain."\b/", $rg['domain'])) continue; 
        echo "<h2>",$rg->head,":</h2>\n";
        #text_eac("",$rg,'list',array('all'=>1));
        #echo "<hr/>";
        foreach ( $rg->report as $r ) {
            if (isset($r['domain']) && ! preg_match("/\b".$mydomain."\b/", $r['domain'])) continue; 
            echo "<h3>[$dbname.",$r['id'],":",$r['target'],"] ",$r->head,":</h3>\n";
            if (isset($r->desc)) echo $r->desc->div->asXML();
            if ($r['target']=="persons" && !$scount) echo para("No sources yet.");
            if ($r['target']=="sources" && !$pcount) echo para("No people yet.");
            echo "<hr/>";

        }
    }
    
    # list all files found in domain/$mydomain/rpt/dbversion

    if (file_exists($myreports)) {
        echo "<h4>Batch reports for [$dbname] found in $myreports:</h4>";
        $dirlist = getFileList($myreports, true);
        echo "<ul>";
        foreach($dirlist as $file) {
           if($file['type'] == 'dir') continue;
           echo "<li>";
           echo "<a href=\"{$file['name']}\">",preg_replace("~^$myreports~","",$file['name']),"</a> (";
           echo $file['type']," ",$file['size']," ",date('r', $file['lastmod']);
           echo ")</li>\n";
        }
        echo "</ul>";
    } 
    else {
        echo "<h4>No other reports published (not found: $myreports).</h4>";
    }
    # process domain specific reports if present
    $domain_reports_locn = "domain/$mydomain/domain_specific_reports.php";
    if (file_exists($domain_reports_locn)) echo file_get_contents($domain_reports_locn);

    echo "</div>\n";
}
else {

   // Old Yggdrasil report page
   echo "<div class=\"normal\">";
   echo "<p>reports_local_${dbname}.php</p>";
   echo "<h2>$title ($pcount $_persons)</h2>\n";

   // by default, we will display the 50 most recently edited persons.

   $headline = "$_The_last_50_edited";

   // This query is sluggish without the following db modification:
   // create index last_edited_persons_key on persons(last_edit,person_id);
   $query = "select person_id, last_edit from persons
               where is_merged(person_id) is false
               order by last_edit desc, person_id desc limit 50";

   echo "<h3>$headline:</h3>\n";
   $handle = pg_query($query);
   echo "<p>";
   while ($row = pg_fetch_row($handle)) {
       $p = $row[0];
       echo get_name_and_dates("./family.php", $p)
           . conc(child_of($p))
           . "<br />\n";
   }
   echo "</p>\n";
   echo para(paren(fetch_num_rows($query)
       . conc($_persons)));
   echo "</div>\n";
}
include "./footer.php";
?>
