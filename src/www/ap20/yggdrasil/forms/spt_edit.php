<?php

/***************************************************************************
 *   spt_edit.php                                                          *
 *   Yggdrasil: Add / Edit Source Part Types Script                        *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

if (!isset($_POST['posted'])) {
    $spt = $_GET['spt'];
    if ($spt == 0) { // new tag type
        $title = "$_Insert $_source_type";
        $description = '';
        $label_en = '';
        $label_nb = '';
        $is_leaf = 'f';
    }
    else {
        $title = "$_Edit $_source_type #$spt";
        $spt_row = fetch_row_assoc("
            SELECT
                label_en,
                label_nb,
                description,
                is_leaf
            FROM
                source_part_types
            WHERE
                part_type_id = $spt
        ");
        $label_en    = $spt_row['label_en'];
        $label_nb    = $spt_row['label_nb'];
        $description = $spt_row['description'];
        $is_leaf     = $spt_row['is_leaf'];
    }
    require "./form_header.php";
    echo "<h2>$title</h2>\n";
    form_begin('spt_edit', $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    hidden_input('spt', $spt);
    text_input("Description:", 80, 'description', $description);
    text_input("Label (en):", 40, 'label_en', $label_en);
    text_input("Label (nb):", 40, 'label_nb', $label_nb);
    select_bool("Is leaf", 'is_leaf', $is_leaf);
    form_submit();
    form_end();
    help_local_file('action','spt');
    echo "</body>\n</html>\n";
}
else {
    $spt = $_POST['spt'];
    $description = $_POST['description'];
    $label_en = $_POST['label_en'];
    $label_nb = $_POST['label_nb'];
    $is_leaf = $_POST['is_leaf'];
    if ($spt == 0) { // insert new source part type
        pg_query("BEGIN");
        $spt = fetch_val("
        SELECT MAX(part_type_id) FROM source_part_types
        ") + 1;
        pg_query("
            INSERT INTO source_part_types (
                part_type_id,
                description,
                label_en,
                label_nb,
                is_leaf
            )
            VALUES (
                $spt,
                '$description',
                '$label_en',
                '$label_nb',
                '$is_leaf'
            )"
        );
        pg_query("COMMIT");
    }
    else { // modify existing tag
        pg_query("
        UPDATE source_part_types SET
                description = '$description',
                label_en = '$label_en',
                label_nb = '$label_nb',
                is_leaf = '$is_leaf'
            WHERE part_type_id = $spt"
        );
    }
    header("Location: $app_root/spt_manager.php");
}

?>
