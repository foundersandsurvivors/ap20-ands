<?php

/***************************************************************************
 *   spt_view.php                                                          *
 *   Yggdrasil: Source Part Type View                                      *
 *                                                                         *
 *   Copyright (C) 2011 by Leif B. Kristensen <leif@solumslekt.org>        *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// This script is basically a report listing sources of one type.
// It is accessed from the Source Part Type Manager via the 'report' link.

require "./settings/settings.php";
require "./functions.php";
require_once "./langs/$language.php";

$spt = $_GET['spt'];
$label = 'label_' . $language;
$spt_name = fetch_val("
    SELECT $label FROM source_part_types WHERE part_type_id = $spt
");
$title = "$_All $_sources $_of type $spt_name";
require "./header.php";

echo "<div class=\"normal\">\n";
echo "<h2>$title</h2>\n";

echo "<table>";
$handle = pg_query("
    SELECT
        source_id,
        link_expand(source_text) AS txt,
        source_date,
        ecc(source_id) AS e,
        rcc(source_id) AS r,
        ssc(source_id) AS s,
        usc(source_id) AS u,
        spt.$label AS $label
    FROM
        sources, source_part_types spt
    WHERE
        spt.part_type_id = sources.part_type
    AND
        spt.part_type_id = $spt
    AND
        source_id <> 0
    ORDER BY
        source_date,
        source_text
");
while ($row = pg_fetch_assoc($handle)) {
    $id = $row['source_id'];
    echo '<tr>';
    echo td(paren(to_url('source_manager.php',
    array('node' => $id), $_Select)
    . '&nbsp;/&nbsp;'
    . to_url('./forms/source_edit.php',
            array(
                'person'    => 0,
                'source'    => $id), $_Edit)));
    if ($row['e'] || $row['r'] || $row['s']) {
        echo td(square_brace(italic($row['source_date']))
            . ' ' . $row['txt']
            . node_details($row['e'], $row['r'], $row['s'], $row['u']));
    }
    else { // source is unused, print with gray text
        echo td(span_type(square_brace(italic($row['source_date']))
            . conc($row['txt']), "faded"));
    }
    echo "</tr>\n";
}
echo "</table>\n";

$enable_zebraforms = 0;
include "./footer.php";
?>
