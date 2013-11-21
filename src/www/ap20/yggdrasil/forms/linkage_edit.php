<?php

/***************************************************************************
 *   linkage_edit.php                                                      *
 *   Yggdrasil: Linkage Edit Form                                          *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// This script is called from the Source Manager.
// It will edit a linkage with the get parameter $node as default source_id,
// and return to the Source Manager.

/*
CREATE TABLE source_linkage (
    source_fk   INTEGER NOT NULL REFERENCES sources (source_id),
    per_id      INTEGER NOT NULL, -- running id of name in source
    role_fk     INTEGER REFERENCES linkage_roles (role_id),
    person_fk   INTEGER REFERENCES persons (person_id),
    surety_fk   INTEGER REFERENCES sureties (surety_id),
    s_name      TEXT, -- person name (and contextual info) as mentioned in source
    c_name      TEXT, -- canonical name (real name if name in source is proven wrong)
    sl_note     TEXT, -- notes and inferences
    PRIMARY KEY (source_fk, per_id)
);
*/

require "../settings/settings.php";
require "../functions.php";
require_once "../langs/$language.php";
require "./forms.php";

if (!isset($_POST['posted'])) {
    $node = $_GET['node'];
    $id = $_GET['id']; //
    $f_person = $_GET['person'];
    $title = $_Edit_link; // "Rediger lenke";
    $form = 'linkage_edit';
    $focus = 'text';
    require "./form_header.php";
    echo "<h2>$_Edit_link from source #$node</h2>"; // "<h2>Rediger lenke $id</h2>\n";
    echo '<p>'
        . fetch_val("SELECT source_text FROM sources WHERE source_id=$node")
        . "</p>\n";
    $row = fetch_row_assoc(
        "SELECT * FROM source_linkage WHERE source_fk=$node AND per_id=$id");
    $person = $row['person_fk'] ? $row['person_fk'] : 0;
    form_begin($form, $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    hidden_input('node', $node);
    hidden_input('per_id', $id);
    hidden_input('f_person', $f_person);
    // role_fk
    select_role($row['role_fk'],$language);
    person_id_input($person, 'person_fk', 'Person: ');
    select_surety($row['surety_fk'],$language);
    text_input("$_Name_in_the_source:", 100, 's_name', $row['s_name']);
    textarea_input("Note: ", 5, 100, 'sl_note', $row['sl_note']);
    form_submit();
    form_end();

    echo "<h3>$_Persons_Mentioned_In_Source:</h3>\n";
    list_mentioned($node, 0);
    help_local_file('action','source_linkage');
    echo "</body>\n</html>\n";
}
else {
    $node = $_POST['node'];
    $note = rtrim($_POST['sl_note']);
    $f_person = $_POST['f_person'];
    pg_prepare("query",
        "UPDATE
            source_linkage
        SET
            role_fk = $1,
            person_fk = $2,
            surety_fk = $3,
            s_name = $4,
            sl_note = $5
        WHERE
            source_fk = $6
        AND
            per_id = $7
    ");
    pg_execute("query",
        array(
            $_POST['role_id'],
            $_POST['person_fk']?$_POST['person_fk']:NULL,
            $_POST['surety'],
            $_POST['s_name'],
            $note,
            $node,
            $_POST['per_id']
        )
    );
    if ($f_person)
        // called from family.php
        header("Location: $app_root/family.php?person=$f_person");
    else
        // called from source_manager.php
        header("Location: $app_root/source_manager.php?node=$node");
}

?>
