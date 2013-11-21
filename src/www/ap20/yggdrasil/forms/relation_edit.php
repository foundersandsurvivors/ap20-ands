<?php

/***************************************************************************
 *   relation_edit.php                                                     *
 *   Yggdrasil: Relations Update Form and Action                           *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/*
This script is called from family.php with the GET params $person and
$parent. Depending on whether $parent is defined or not, it will either alter
an existing parent relation or insert a new parent. This is also where source
citations for relations are inserted or altered.
*/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

if (!isset($_POST['posted'])) { // print form
    $person = $_GET['person'];
    $name = get_name($person);
    $title = "$person $name: Rediger relasjon";
    $form = 'edit_relation';
    if (!isset($_GET['parent'])) // focus on parent_id
        $focus = 'parent';
    require "./form_header.php";
    $ptype[1] = $_father;
    $ptype[2] = $_mother;
    if (isset($_GET['parent'])) {
        // prepare for update of existing relation
        $qtype = 'update';
        $atype = $_Edit;
        $parent = $_GET['parent'];
        $handle = pg_query("
            SELECT
                relation_id,
                get_gender($parent) AS relation_type,
                surety_fk
            FROM
                relations
            WHERE
                child_fk = $person
            AND parent_fk = $parent
        ");
        $rec = pg_fetch_assoc($handle);
        $relation = $rec['relation_id'];
        $gender = $rec['relation_type'];
        $surety = $rec['surety_fk'];
        $pname = get_name($parent);
    }
    else {
        // prepare for insert of new relation
        $qtype = 'insert';
        $atype = $_Insert;
        $parent = 0;
        $pname = '';
        $gender = $_GET['gender'];
        $surety = 3;
    }
    $pprompt = ucfirst($ptype[$gender]) . ':';
    echo "<h2>$atype " . $ptype[$gender] . " for $name</h2>\n";
    form_begin($form, $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    hidden_input('person', $person);
    hidden_input('qtype', $qtype);
    if ($qtype == 'update') {
        hidden_input('relation', $relation);
        hidden_input('oldparent', $parent);
    }
    person_id_input($parent, 'parent', $pprompt);
    checkbox('bsource', $_Use_source_for_birth_event);
    select_surety($surety);
    source_input();
    form_submit();
    form_end();
    if ($qtype == 'update') {
        echo "<h3>$_References</h3>\n";
        $handle = pg_query("
            SELECT
                source_fk,
                get_source_text(source_fk) AS source_text
            FROM
                relation_citations
            WHERE
                relation_fk = $relation
        ");
        while ($row = pg_fetch_assoc($handle)) {
            echo para($row['source_fk'] . ' ' . $row['source_text']);
        }
    }
    echo "</body>\n</html>\n";
}
else { // do action
    $person = $_POST['person'];
    $surety = $_POST['surety'];
    $_POST['parent'] ? $parent = $_POST['parent'] : $parent = 0;
    pg_query("BEGIN");
    if ($_POST['qtype'] == 'update') { // update existing relation
        $relation = $_POST['relation'];
        $oldparent = $_POST['oldparent'];
        $oldsurety = fetch_val("
            SELECT surety_fk
            FROM relations
            WHERE relation_id = $relation
        ");
        // change parent or surety
        if ($parent != $oldparent || $surety != $oldsurety) {
            pg_query("
                UPDATE
                    relations
                SET
                    parent_fk = $parent,
                    surety_fk = $surety
                WHERE
                    relation_id = $relation
            ");
        }
    }
    else { // insert new relation
        $relation = fetch_val("
            INSERT INTO relations (
                child_fk,
                parent_fk,
                surety_fk
            )
            VALUES (
                $person,
                $parent,
                $surety
            )
            RETURNING relation_id
        ");
    }
    if ($_POST['bsource']) { // use source(s) for birth event
        // birth_sources is a view, cf ddl/views.sql
        $handle = pg_query("
            SELECT
                source_fk
            FROM
                birth_sources
            WHERE
                person = $person
        ");
        while ($row = pg_fetch_row($handle)) {
            $source_id = $row[0];
            // check for duplicates
            if (fetch_val("
                SELECT
                    COUNT(*)
                FROM
                    relation_citations
                WHERE
                    relation_fk = $relation
                AND
                    source_fk = $source_id
            ") == 0)
                pg_query("
                    INSERT INTO relation_citations
                    VALUES ($relation, $source_id)
                ");
        }
    }
    else if ($_POST['source_id']) { // if not bsource
        if ($_POST['source_text']) { // add new source
            $parent_id = $_POST['source_id'];
            $text = note_to_db($_POST['source_text']);
            // use two-param overload of add_source
            $source_id = fetch_val("SELECT add_source($parent_id, '$text')");
            // remove old citation if new source is an expansion,
            // ie. parent of new source == old source
            pg_query("
                DELETE FROM
                    relation_citations
                WHERE
                    relation_fk = $relation
                AND
                    source_fk = $parent_id
            ");
        }
        else
            $source_id = $_POST['source_id'];
        // Entering the same source twice for the same relation will violate the
        // composite primary key (relation_fk, source_fk) constraint.
        // Test before trying to insert a relation citation.
        if ($relation &&
                fetch_val("
                    SELECT
                        COUNT(*)
                    FROM
                        relation_citations
                    WHERE
                        relation_fk = $relation
                    AND
                        source_fk = $source_id
                ") == 0) {
            pg_query("
                INSERT INTO relation_citations
                VALUES ($relation, $source_id)
            ");
        }
    }
    pg_query("COMMIT");
    header("Location: $app_root/family.php?person=$person");
}

?>
