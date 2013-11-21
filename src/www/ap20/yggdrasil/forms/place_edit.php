<?php

/***************************************************************************
 *   place_edit.php                                                        *
 *   Yggdrasil: Update Places Form                                         *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

function get_place_desc($n) {
    global $language;
    $label = 'desc_' . $language;
    $str = fetch_val("
        SELECT $label FROM place_level_desc WHERE place_level_id = $n
    ");
    $str .= ':';
    return $str;
}

if (!isset($_POST['posted'])) {
    $title = $_Edit_place_name;
    require "./form_header.php";
    $place_id = $_GET['place_id'];
    if ($place_id == 0) { // new place
        $level_1 = '';
        $level_2 = '';
        $level_3 = '';
        $level_4 = '';
        $level_5 = '';
    }
    else {
        $place = fetch_row_assoc("SELECT * FROM places WHERE place_id = $place_id");
        $level_1 = $place['level_1'];
        $level_2 = $place['level_2'];
        $level_3 = $place['level_3'];
        $level_4 = $place['level_4'];
        $level_5 = $place['level_5'];
    }
    echo "<h2>$title</h2>\n";
    form_begin('place_edit', $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    hidden_input('place_id', $place_id);
    text_input(get_place_desc(1), 80, 'level_1', $level_1);
    text_input(get_place_desc(2), 80, 'level_2', $level_2);
    text_input(get_place_desc(3), 80, 'level_3', $level_3);
    text_input(get_place_desc(4), 80, 'level_4', $level_4);
    text_input(get_place_desc(5), 80, 'level_5', $level_5);
    form_submit();
    form_end();
    echo "</body>\n</html>\n";
}
else {
    $place_id = $_POST['place_id'];
    $level_1 = note_to_db($_POST['level_1']);
    $level_2 = $_POST['level_2'];
    $level_3 = $_POST['level_3'];
    $level_4 = $_POST['level_4'];
    $level_5 = $_POST['level_5'];
    if ($place_id == 0) { // insert new place
        pg_query("BEGIN WORK");
        $place_id = fetch_val("
            INSERT INTO places (
                level_1,
                level_2,
                level_3,
                level_4,
                level_5
            )
            VALUES (
                '$level_1',
                '$level_2',
                '$level_3',
                '$level_4',
                '$level_5'
            )
            RETURNING place_id
        ");
        pg_query("COMMIT");
    }
    else { // modify existing place
        pg_query("
            UPDATE places SET
                level_1 = '$level_1',
                level_2 = '$level_2',
                level_3 = '$level_3',
                level_4 = '$level_4',
                level_5 = '$level_5'
            WHERE place_id = $place_id
        ");
    }
    set_last_selected_place($place_id);
    header("Location: $app_root/place_manager.php");
}

?>
