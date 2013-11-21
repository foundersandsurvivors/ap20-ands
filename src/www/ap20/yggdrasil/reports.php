<?php

/***************************************************************************
 *   reports/reports.php                                                   *
 *   Yggdrasil: Reports Page                                               *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
$title = "Reports"; 
require "./header.php";

// TODO - read directory of reports
$pcount = fetch_val("SELECT COUNT(*) FROM persons")
               - fetch_val("SELECT COUNT(*) FROM merged");

if ( $_AP20 ) { 
    require "reports_local.php"; 
}
else {

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
