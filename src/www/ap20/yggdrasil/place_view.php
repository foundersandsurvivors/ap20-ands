<?php

/***************************************************************************
 *   place_view.php                                                        *
 *   Yggdrasil: Place View                                                 *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// This script is basically a "report" listing events and persons associated
// with a place. It is accessed from the Place Manager through the 'browse'
// (se på) link.

require "./settings/settings.php";
require "./functions.php";

$place_id = $_GET['place_id'];
$place_name = fetch_val("SELECT get_place_name($place_id)");
$title = "Hendelser knyttet til stedsnavn \"$place_name\"";
require "./header.php";

echo "<div class=\"normal\">\n";
echo "<h2>$title</h2>\n";
echo "<p><a href=\"./place_manager.php\">Alle stedsnavn</a></p>\n";
echo "<table>\n";
echo "<tr><th>ID</th><th>Hendelse</th><th>Dato</th><th>Deltagere</th></tr>\n";
$handle = pg_query("SELECT event_id, event_name, event_date, p1, p2
                        FROM place_events WHERE place_fk = $place_id ORDER BY event_date");
while ($row = pg_fetch_assoc($handle)) {
    unset($participant_list);
    $event = $row['event_id'];
    $p_handle = pg_query("SELECT person_fk FROM participants
                            WHERE event_fk = $event ORDER BY sort_order");
    while ($parts = pg_fetch_row($p_handle))
        $participant_list[] = linked_name($parts[0], './family.php');
    echo "<tr>";
    echo "<td>" . $row['event_id'] . "</td>";
    echo "<td>" . $row['event_name'] . "</td>";
    echo "<td>" . fuzzydate($row['event_date']) . "</td>";
    echo '<td>' . join($participant_list, ', ') . '</td>';
//    echo "<td>" . $row['p1'] . ' ' . linked_name($row['p1'], './family.php') . "</td>";
//    if ($row['p2'])
//        echo "<td>" . $row['p2'] . ' ' . linked_name($row['p2'], './family.php') . "</td>";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<p><a href=\"./place_manager.php\">Alle stedsnavn</a></p>\n";
echo "</div>\n";
include "./footer.php";
?>
