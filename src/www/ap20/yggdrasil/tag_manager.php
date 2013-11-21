<?php

/***************************************************************************
 *   tag_manager.php                                                       *
 *   Yggdrasil: Tag Manager                                                *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
$title = $_All . " " . $_Event_types;
require "./header.php";

echo "<div class=\"normal\">\n";
echo "<h2>$title</h2>\n";
echo "<p>( <a href=\"./forms/tag_edit.php?tag=0\">$_insert</a> )</p>\n";
echo "<table>\n";
$tag_group_name = 'tag_group_name_' . $language;
$handle = pg_query("SELECT tag_id, tag_type_fk, tag_types.description as tag_type_desc, $tag_group_name, tag_name,
                        gedcom_tag, tag_count(tag_id) AS tc, tag_count_task(tag_id) AS ttc
                    FROM tags, tag_groups, tag_types
                    WHERE tag_group_fk = tag_group_id and tag_type_fk=tag_type_id
                    ORDER BY tc DESC, $tag_group_name ASC, tag_name ASC");
/* sms: interim. Labels need to be defined in lang files */
echo "<tr><th>Action</th><th>Occurs</th><th>Gedcom</th><th>$_Type</th><th>Participants</th><th>Group</th><th title=\"Count of events of this type containing a task note of the form [task=...]\">Task count</th></tr>\n";
while ($row = pg_fetch_assoc($handle)) {
    echo "<tr>";
    if ($row['tc'] == 0) // if tag is unused, display link for deletion
        echo "<td><a href=\"javascript:alert('Send Sandra an email asking to delete tag ".$row['tag_id']." (".$row['tag_name'].") from the $dbname yggdrasil database')\">$_delete</a></td>";
        /* echo "<td><strong><a href=\"./forms/tag_delete.php?tag=".$row['tag_id']."\">$_delete</a></strong></td>"; */
    else
        echo "<td><a href=\"./tag_view.php?tag=".$row['tag_id']."\">$_report</a></td>";
    echo "<td align=\"right\">".$row['tc']."</td>";
    // echo "<td>".$row['tag_group_label']."</td>";
    echo "<td><code>".$row['gedcom_tag']."</code></td>";
    echo "<td><a href=\"./forms/tag_edit.php?tag=".$row['tag_id']."\">".$row['tag_name']."</a></td>";
    /* echo "<td>".$row['tag_type_fk']."</td>"; */
    echo "<td>".$row['tag_type_desc']."</td>";
    echo "<td>".$row[$tag_group_name]."</td>";
    if ( $row['ttc'] > 0 ) {
        echo "<td title=\"View remaining tasks\"><a href=\"./tag_view.php?tag=".$row['tag_id']."&tasks=1\">".$row['ttc']."</td>";
    }
    else {
        echo "<td>".$row['ttc']."</td>";
    }
    echo "</tr>\n";
}
echo "</table>\n";
echo "</div>\n";
include "./footer.php";
?>
