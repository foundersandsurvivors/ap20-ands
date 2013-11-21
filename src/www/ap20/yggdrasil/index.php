<?php

/***************************************************************************
 *   index.php                                                             *
 *   Yggdrasil: Entry Page                                                 *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
$title = "$_Index_and_name_search";
$form = 'search';
$focus= 'given';
require "./functions.php";
require "./header.php";

$pcount = fetch_val("SELECT COUNT(*) FROM persons")
               - fetch_val("SELECT COUNT(*) FROM merged");

echo "<div class=\"normal\">";
echo "<h2>$title ($pcount $_persons)</h2>\n";

// this is a rather special form compared to the rest of the package,
// hence it doesn't use the forms.php abstractions


// sms: we want to carry what was entered
$given = "";
$surname = "";
$bdate = "";
$diff = "";
$searchResultInfo = "";
if (!isset($_GET['Clear'])) {

if (isset($_GET['given'])) $given   = $_GET['given'];
if (isset($_GET['surname'])) $surname = $_GET['surname'];
if (isset($_GET['bdate'])) $bdate   = $_GET['bdate'];
if (isset($_GET['diff'])) $diff = $_GET['diff'];
$searchResultInfo = "($given $surname $bdate";
if ( $diff > 0 ) $searchResultInfo .= "+/- $diff yrs";
$searchResultInfo .= ")";

}

echo "<form id=\"search\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n<div>\n";
echo "$_Given_name: <input type=\"text\" size=\"12\" name=\"given\" default=\"\" value=\"$given\"/>\n";
echo "\"$_Surname\": <input type=\"text\" size=\"12\" name=\"surname\" default=\"\" value=\"$surname\"/>\n";
echo "$_Birth_year: <input type=\"text\" size=\"8\" name=\"bdate\" default=\"\" value=\"$bdate\"/>\n";

echo "&plusmn;<select name = \"diff\">\n";
echo "<option selected=\"selected\" value=\"0\"></option>\n";
echo "<option value=\"2\">2</option>\n";
echo "<option value=\"5\">5</option>\n";
echo "<option value=\"10\">10</option>\n";
echo "<option value=\"20\">20</option>\n";
echo "</select></td></tr>\n";

echo "<input type=\"submit\" value=\"$_Search\" />\n";
if ($basex_restxq) { echo "<input name=\"Xml\" type=\"submit\" value=\"Xml\" />\n"; }
echo "<input name=\"Clear\" type=\"submit\" value=\"Clear\" />\n";
echo "</div>\n</form>\n\n";

// if they've hit this, reset the form and stop
if ( isset($_GET['Clear']) ) {
    echo "</div>\n";
    include "./footer.php";
    exit;
}


// XML portal search
if ( $_AP20 and isset($_GET['Xml']) ) {
    include 'fasidmapping.lib.php';
    if ( preg_match('/(([a-zA-Z][0-9]?[a-zA-Z]+)(\d+.*))$/',$given,$keys) ) {
         $rectype = $keys[2]; $recid = $keys[0];

         # lookup mapping
         if ($rectype) {
            $map = '';
            foreach ($SOURCEMAP as $m) { if ( $m['id'] == $rectype ) { $map = $m; break; } }
            #var_dump ($map);
            $map_desc = $map['desc'];
            $map_db = $map['db'];
         }
         $headline = "Xml Portal search [$given] $rectype: $map_desc in db[$map_db]";
         #$headline = "Xml Portal search [$given] rectype[$rectype] recid[$recid] map_db[$map_db] map_desc[$map_desc] basex_restxq[$basex_restxq]";
         $json = $J = '';
         if ($map) {
             echo "<h3>$headline</h3>";
             display_get_restxq_xml2("/id/$recid" . '?xml');
             #$json = get_restxq_xml2("/id/$recid" . '?json');
             #file_put_contents("/tmp/json2",$json);
             #$J = json_encode($SOURCEMAP);
             #file_put_contents("/tmp/json3",$J);
             #$J = json_decode($json);
         }
         else {
             $headline = "(UNRECOGNISED) Xml Portal search [$given] rectype[$rectype] recid[$recid] basex_restxq[$basex_restxq]";
             echo "<h3>$headline</h3>";
         }
         #var_dump( $J[3][1][3] );
         
    }
    echo "</div>\n";
    include "./footer.php";
    exit;
}


// note that for reasons of convenience, a search for 'surname'
// will include patronym, toponym, surname, and occupation.


// by default, we will display the 20 most recently edited persons.
########if ($pcount > 0) {

$style = "keys";
if(!$given && !$surname && !$bdate) {
    $headline = "$_The_last_50_edited";

// This query is sluggish without the following db modification:
// create index last_edited_persons_key on persons(last_edit,person_id);
   $query = "select person_id, last_edit,keys from persons
               where is_merged(person_id) is false
               order by last_edit desc, person_id desc limit 20";
   $style = "";
   $searchResultInfo = "";
}
// sms: if there is a number in the forename field, assume its a key; search for the keys (allow multiples)
else if ( preg_match_all('/([a-zA-Z][0-9]?[a-zA-Z]+\d+)/',$given,$keys) ) {
    $headline = "Key search [$given]";
    $query =
        "SELECT
            person_id,
            get_pbdate(person_id) as pbd,
            keys,p_sdata
        FROM
            persons
        WHERE
            ";
    foreach ( $keys[0] as &$k ) {
        $query .= "keys @> '{".$k."}' or ";
    }
    $query = substr_replace($query ,"",-3);
}
else {
    if (substr($surname, 0, 1) == '!')
        $literal = ltrim($surname, '!');
    else
        $literal = "%$surname%";
    $headline = "$_Search_result";
    $query =
        "SELECT
            person_id,
            get_pbdate(person_id) as pbd,
            keys,p_sdata
        FROM
            persons
        WHERE
            given ILIKE '$given%'
            AND (
                patronym ILIKE '$surname%'
                OR toponym ILIKE '$literal'
                OR surname ILIKE '$surname%'
                OR occupation ILIKE '$surname%'
            )
            AND is_merged(person_id) IS FALSE
        ";
    if ($bdate)
        $query .= "
            AND f_year(get_pbdate(person_id))
                    BETWEEN (($bdate)::INTEGER - $diff)
                    AND (($bdate)::INTEGER + $diff)
            ";
$query .= "
    ORDER BY pbd, surname";
}

//echo "..query=$query";
search_and_show_persons_custom($query,"<h3>$headline $searchResultInfo:</h3>",".",$style);
########}

echo "</div>\n";
include "./footer.php";
?>
