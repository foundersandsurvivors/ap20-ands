<?php

/***************************************************************************
 *   person_insert.php                                                     *
 *   Yggdrasil: Insert Person Form and Action                              *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

if (!isset($_POST['posted'])) {
    $person = isset($_GET['person']) ? $_GET['person'] : 0;
    if (isset($_GET['addparent'])) {
        $child = $_GET['person'];
        $gender = $_GET['gender'];
    }
    else {
        $child = 0;
        $gender = 0;
    }
    $spouse = 0;
    $spgender = 0;
    if (isset($_GET['addspouse'])) {
        $spouse = $_GET['person'];
        $spgender = get_gender($spouse);
        if ($spgender == 1) $gender = 2;
        if ($spgender == 2) $gender = 1;
    }
    $father = isset($_GET['father']) ? $_GET['father'] : 0;
    $mother = isset($_GET['mother']) ? $_GET['mother'] : 0;
    $title = "$_Add";
    if ($father && $mother)
        $title .= " $_child $_of " . get_name($father) . " $_and " . get_name($mother);
    elseif ($father)
        $title .= " $_child $_of " . get_name($father);
    elseif ($mother)
        $title .= " $_child $_of " . get_name($mother);
    elseif ($spouse)
        $title .= " $_spouse $_to " . get_name($spouse);
    else
        $title .= ' person';
    require "./form_header.php";
    echo "<h2>$title</h2>\n";
    form_begin('person_insert', $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    if ($child)
        hidden_input('child', $child);
    if ($spouse)
        hidden_input('spouse', $spouse);
    if ($father)
        hidden_input('father', $father);
    if ($mother)
        hidden_input('mother', $mother);
    radio_gender($gender);
    name_input();
    // if we add a spouse, assume that we want to enter a marriage
    // event, not a birth event.
    $selected = $spouse ? MARR : BIRT;
    select_tag($selected);
    select_place(0);
    date_input();
    // text_input("$_Sort_date:", 25, 'sort_date', '');
    textarea_input("$_Text:", 5, 80, 'event_note', '');
    source_input();
    text_input("$_Age:", 10, 'age', '', "($_Adds_birth_event)");
    form_submit();
    form_end();
    echo "</body>\n</html>\n";
}
else {
    $gender = $_POST['gender'];
    $given = $_POST['given'];
    $patronym = $_POST['patronym'];
    $toponym = $_POST['toponym'];
    $surname = $_POST['surname'];
    $occupation = $_POST['occupation'];
    $epithet = $_POST['epithet'];
    // a piece of inference due to a tendency to overlook the 'gender' button
    // unless patronym is set, the POST value of gender should be used.
    // You may want to change this code if you're researching a non-patronymic culture.
    if ($gender == 0) {
        if (substr($patronym, -3, 3) == 'sen')
            $gender = 1;
        if (substr($patronym, -3, 3) == 'ter')
            $gender = 2;
    }
    // start transaction
    pg_query("BEGIN");
    // person record
    $person = fetch_val("
        INSERT INTO persons (
            last_edit,
            gender,
            given,
            patronym,
            toponym,
            surname,
            occupation,
            epithet
        )
        VALUES (
            NOW(),
            $gender,
            '$given',
            '$patronym',
            '$toponym',
            '$surname',
            '$occupation',
            '$epithet'
        )
        RETURNING
            person_id
    ");
    // add event
    $tag = $_POST['tag_fk'];
    $place = $_POST['place_fk'];
    if ($place == 0) $place = 1;
    $event_date = pad_date($_POST['date_1'])
        . $_POST['date_type'] . pad_date($_POST['date_2']) . '1';
    $sort_date = parse_sort_date($_POST['sort_date'],$event_date);
    // minimal cleanup of event note
    $event_note = note_to_db($_POST['event_note']);
    $event = fetch_val("
        INSERT INTO events (
            tag_fk,
            place_fk,
            event_date,
            sort_date,
            event_note
        )
        VALUES (
            $tag,
            $place,
            '$event_date',
            '$sort_date',
            '$event_note'
        )
        RETURNING event_id
    ");
    set_last_selected_place($place);
    // participant data
    add_participant($person,$event);
    // if the script was called with the 'addspouse' param, insert second participant
    if (isset($_POST['spouse']) && $tag == MARR) {
        add_participant($_POST['spouse'],$event);
    }
    // note that add_source() returns 0 for no source, else current source_id
    $source_id = add_source($person, $tag, $event, $_POST['source_id'], $_POST['source_text']);
    // add relation if this script was called with an 'addparent' param:
    if ($_POST['child']) {
        $child = $_POST['child'];
        $relation_id = fetch_val("
            INSERT INTO relations (
                child_fk,
                parent_fk
            )
            VALUES (
                $child,
                $person
            )
            RETURNING relation_id
        ");
        if ($source_id) { // add relation citation
            pg_query("
                INSERT INTO relation_citations (
                    relation_fk,
                    source_fk
                )
                VALUES (
                    $relation_id,
                    $source_id
                )
            ");
        }
    }
    // add relation if this script was called with a 'father' param:
    if ($_POST['father']) {
        $father = $_POST['father'];
        $relation_id = fetch_val("
            INSERT INTO relations (
                child_fk,
                parent_fk
            )
            VALUES (
                $person,
                $father
            )
            RETURNING relation_id
        ");
        if ($source_id) { // add relation citation
            pg_query("
                INSERT INTO relation_citations (
                    relation_fk,
                    source_fk
                )
                VALUES (
                    $relation_id,
                    $source_id
                )
            ");
        }
    }
    // add relation if this script was called with a 'mother' param:
    if ($_POST['mother']) {
        $mother = $_POST['mother'];
        $relation_id = fetch_val("
            INSERT INTO relations (
                child_fk,
                parent_fk
            )
            VALUES (
                $person,
                $mother
            )
            RETURNING relation_id
        ");
        if ($source_id) { // add relation citation
            pg_query("
                INSERT INTO relation_citations (
                    relation_fk,
                    source_fk
                )
                VALUES (
                    $relation_id,
                    $source_id
                )
            ");
        }
    }
    if ($_POST['age']) // generate birth event
        add_birth($person, $event_date, $_POST['age'], $source_id);
    pg_query("COMMIT") or die(pg_last_error());
    header("Location: $app_root/family.php?person=$person");
}

?>
