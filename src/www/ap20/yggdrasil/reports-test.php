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

echo "<div class=\"normal\">";
echo "<h1>####TEST#### $dbname $title</h1>\n";
echo file_get_contents ("./reports/overnight_index");
echo "</div>\n";

echo "<div class=\"normal\">";
echo "<h2>Legacy data - OHRM and Generations</h2>\n";
echo para("This data was used to populate $_App_name. It is no longer maintained and is included here for reference/audit purposes.");
echo "<ul>";
echo li(to_url("/prot/khrd/eac-complete/ohrm-web/browse.htm","",
               "Biographical web reports of 7279 people in KHRD 20 Aug 2007",
               "info..."));
echo "</ul>";
echo "</div>\n";


/*
echo "<div class=\"normal\">";
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

*/

include "./test-barchart.php";

include "./footer.php";
?>
