<?php

/***************************************************************************
 *   Source_event_edit.php                                                 *
 *   Yggdrasil: Event Update Form and Action accessed from Source Manager  *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

if (!isset($_POST['posted'])) {
    // do form
    $source = $_GET['source'];
    $event = $_GET['event'];
    $title = "$_Edit_event #$event";
    require "./form_header.php";
    echo "<h2>$title</h2>\n";
    $rec = fetch_row_assoc("
        SELECT
            tag_fk,
            place_fk,
            event_date,
            date2text(sort_date) AS sd,
            event_note
        FROM
            events
        WHERE
            event_id = $event
    ");
    $note = note_from_db($rec['event_note']);
    $notelen = strlen($note);
    $notelen < 1000 ? $note_height = 10 : $note_height = 20;
    $note_width = 80;
    form_begin('edit_event', $_SERVER['PHP_SELF']);
    hidden_input('source', $source);
    hidden_input('event', $event);
    hidden_input('posted', 1);
    select_tag($rec['tag_fk']);
    select_place($rec['place_fk']);
    date_input($rec['event_date'], $rec['sd']);
    textarea_input("$_Text:<br />$notelen", $note_height, $note_width,
        'event_note', $note);
    form_submit();
    form_end();
    echo "<h3>$_Citations</h3>\n";
    $handle = pg_query("
        SELECT source_fk
        FROM event_citations
        WHERE event_fk = $event
    ");
    while ($row = pg_fetch_row($handle)) {
        echo '<p>'.$row[0].' ';
        echo fetch_val("SELECT get_source_text($row[0])");
        echo "</p>\n";
    }
    echo "</body>\n</html>\n";
}
else {
    // do action
    $source= $_POST['source'];
    $event = $_POST['event'];
    $tag = $_POST['tag_fk'];
    $place = $_POST['place_fk'];
    if ($place == 0) $place = 1;
    $note = note_to_db($_POST['event_note']);
    $event_date = pad_date($_POST['date_1']) . $_POST['date_type']
                    . pad_date($_POST['date_2']) . '1';
    $sort_date = parse_sort_date($_POST['sort_date'],$event_date);
    pg_query("
        BEGIN
    ");
    pg_query("
        UPDATE EVENTS SET
            tag_fk = $tag,
            place_fk = $place,
            event_date = '$event_date',
            sort_date = '$sort_date',
            event_note = '$note'
        WHERE event_id = $event");
    set_last_selected_place($place);
    pg_query("
        COMMIT
    ");
    header("Location: $app_root/source_manager.php?node=$source");
}

?>
