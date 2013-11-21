<?php

/***************************************************************************
 *   part_note_edit.php                                                    *
 *   Yggdrasil: add / edit "witness" note to non-primary participants      *
 *   A very very simple edit form, with one input field                    *
 *   and an "upsert" construct that does inserts, updates or deletes.      *
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
    $person = $_GET['person'];
    $event = $_GET['event'];
    $node = $_GET['node'];
    $title = "$_Edit_event #$event";
    require "./form_header.php";
    echo "<h2>$title</h2>\n";
    $note = note_from_db(fetch_val("SELECT COALESCE(
                        (SELECT part_note
                        FROM participant_notes
                        WHERE person_fk=$person AND event_fk=$event), '')"));

    $notelen = strlen($note);
    $notelen < 1000 ? $note_height = 10 : $note_height = 20;
    $note_width = 80;
    form_begin('edit_event', $_SERVER['PHP_SELF']);
    hidden_input('person', $person);
    hidden_input('event', $event);
    hidden_input('node', $node);
    hidden_input('posted', 1);
    textarea_input("$_Text:<br />$notelen", $note_height, $note_width, 'note', $note);
    form_submit();
    form_end();
    echo "<h3>$_Citations</h3>\n";
    $handle = pg_query("SELECT source_fk FROM event_citations WHERE event_fk = $event");
    while ($row = pg_fetch_row($handle)) {
        echo '<p>'.$row[0].' ';
        echo fetch_val("SELECT get_source_text($row[0])");
        echo "</p>\n";
    }
    echo "</body>\n</html>\n";
}
else {
    // do action
    $person = $_POST['person'];
    $event = $_POST['event'];
    $node = $_POST['node'];
    $note = note_to_db($_POST['note']);
    pg_query("BEGIN");
    // $note_exists will always be 0 or 1.
    $note_exists = fetch_val("SELECT COUNT(*) FROM participant_notes
                    WHERE person_fk=$person AND event_fk=$event");
    if (!$note_exists && $note != '') {
        $query = "INSERT INTO participant_notes (person_fk, event_fk, part_note)
                    VALUES ($person, $event, '$note')";
    }
    else if ($note_exists && $note != '') {
        $query = "UPDATE participant_notes SET part_note='$note'
                    WHERE person_fk=$person AND event_fk=$event";
    }
    else if ($note_exists && $note == '') {
        $query = "DELETE FROM participant_notes
                    WHERE person_fk=$person AND event_fk=$event";
    }
    pg_query($query);
    pg_query("COMMIT");
    if ($node)  // return to source manager
        header("Location: $app_root/source_manager.php?node=$node");
    else        // return to family view
        header("Location: $app_root/family.php?person=$person");
}

?>
