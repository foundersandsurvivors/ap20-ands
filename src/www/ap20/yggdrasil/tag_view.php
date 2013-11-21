<?php

/***************************************************************************
 *   tag_view.php                                                          *
 *   Yggdrasil: Tag View                                                   *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// This script is basically a "report" listing events and persons associated
// with a tag. It is accessed from the Tag Manager through the 'browse'
// (se på) link.

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";

$LIMIT = 100;

$tag = isset($_GET['tag']) ? $_GET['tag'] : false;
$year = isset($_GET['year']) ? $_GET['year'] : false;
$task = isset($_GET['task']) ? $_GET['task'] : false;
$lastid = isset($_GET['lastid']) ? $_GET['lastid'] : false;

$tag_name = fetch_val("SELECT get_tag_name($tag)");
$title = "$_All $_events $_of type: $tag_name";
require "./header.php";

echo "<div class=\"normal\">\n";
echo "<h2>$title</h2>\n";
/* echo "<pre>..tag[$tag] year[$year] task[$task]</pre>\n"; */

$local_header_link = "<p><a href=\"./tag_manager.php\">$_Event_Manager</a></p>\n";
echo $local_header_link;

/* construct a query and get count */
$q_qual = '';
if ( preg_match( '/^\d+$/', $year )) { 
    $q_qual .= " and f_year(event_date)='$year' ";
}
if ( $task && preg_match( '/^[a-zA-Z0-9]+$/', $task )) {
    $q_qual .= " and event_note like '%task=$task%' ";
}
$q_qual_batch = '';
$q_qual_batch_info = '';
if ( preg_match( '/^\d+$/', $lastid )) { 
    $q_qual .= " and event_id>'$lastid' ";
    $q_qual_batch = " LIMIT $LIMIT";
    $q_qual_batch_info = "Next $LIMIT from ID($lastid). ";
}

$theQuery = "SELECT count(*) as n FROM tag_events WHERE tag_fk = $tag $q_qual $q_qual_batch";
$handle = pg_query($theQuery);
while ( $row = pg_fetch_assoc($handle)) {
   $total_to_display = $row['n'] + 0;
}

# debugging
#if ( $administrator[$authuser] ) {
#   echo "<pre>..debug tag[$tag] year[$year] task[$task] lastid[$lastid] LIMIT[$LIMIT]\ntheQuery[$theQuery]\ntotal_to_display[$total_to_display]</pre>\n";
#}

if ( $q_qual && $total_to_display > $LIMIT && !$q_qual_batch ) {
    $q_qual_batch = " LIMIT $LIMIT";
    $q_qual_batch_info = "First $LIMIT."; 
}

if ( !$q_qual_batch && !$q_qual && $total_to_display > $LIMIT ) {

    $handle = pg_query("
        SELECT 
            tag_fk,
            get_tag_name(tag_fk) as tagname,
            count(event_id) as tc,
            f_year(event_date) as y,
            tasktype(event_note) as task 
        FROM 
            events 
        WHERE 
            tag_fk = $tag
        GROUP BY
            tag_fk,
            tagname,
            y,
            task 
        ORDER BY 
            tagname,
            y,
            task
    ");

    $n_short = pg_num_rows($handle);

    echo "<p>There are $total_to_display  $_of $_events type $tag_name. Too many too show at once - choose a subset based on when the event occurred and data management tasks required: </p>\n";

    echo "<table>\n";
    echo "<tr><th>Action</th><th>$_Type</th><th>Year</th><th width=\"10%\">Occurs</th><th title=\"Count of events of this type containing a task note of the form [task=...]\">Tasks pending</th></tr>\n";

    while ($row = pg_fetch_assoc($handle)) {
        echo "<tr>";
        echo "<td><a href=\"./tag_view.php?tag=".$row['tag_fk']."&y=".$row['y']."&task=".$row['task']."\">$_report</a></td>";
        echo "<td><code>".$row['tagname']."</code></td>";
        echo "<td>".$row['y']."</td>";
        echo "<td align=\"right\">".$row['tc']."</td>";
        echo "<td>".$row['task']."</td>";
        echo "</tr>\n";
    }

    echo "</table>\n";


}
else {

    /* under the limit; ok to show them all */

    echo "<h4>$total_to_display $_events type $tag_name $q_qual. $q_qual_batch_info</h4>\n";

    $handle = pg_query("
        SELECT
            event_id,
            event_name,
            event_date,
            event_note,
            place_name,
            p1,
            p2
        FROM
            tag_events
        WHERE
            tag_fk = $tag
            $q_qual
        ORDER BY
            event_date,
            event_id
        $q_qual_batch
    ");

    $set_lastid = 0;
    while ($row = pg_fetch_assoc($handle)) {
        if ( $q_qual_batch ) $set_lastid = $row['event_id'];
        echo '<p>[' . $row['event_id'] . '] ';
        echo $row['event_name'];
        echo ' ' . fuzzydate($row['event_date']);
        echo ' ' . $row['place_name'] . ': ';
        echo list_participants($row['event_id']);
        echo ' ' . $row['event_note'];
        // print source(s)
        $innerhandle = pg_query("
        SELECT
            source_text
        FROM
            event_notes
        WHERE
            note_id = " . $row['event_id']
        );
        while ($row = pg_fetch_assoc($innerhandle)) {
                echo conc(paren($_Source . ':'
                . conc(ltrim($row['source_text']))));
        }
        echo "</p>\n";
    }
    $n_short = pg_num_rows($handle);
    if ( $q_qual_batch ) {
        if ( $n_short < $LIMIT ) {
            echo "<p>Done.</p>\n";
        }
        else {
            echo "<h4><a href=\"./tag_view.php?tag=$tag&y=$year&task=$task&lastid=$set_lastid\">Next $LIMIT from ID($set_lastid)</h4>\n";
        }
    }

}
echo $local_header_link;
echo "</div>\n";
include "./footer.php";
?>
