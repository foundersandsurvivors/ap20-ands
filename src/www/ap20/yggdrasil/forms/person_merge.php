<?php

/***************************************************************************
 *   person_merge.php                                                      *
 *   Yggdrasil: Merge Persons Form and Action                              *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/*
Merging persons is an essential feature of any genealogy database application.
For a variety of reasons, it is also one of the most complex operations.

In this script, I have defined the two persons to be merged as the 'source'
and the 'target'. All events and relations will be transferred from the
'source' person to the 'target' person. Rather than deleting the 'source'
person, I'll keep him/her in the database, with a single link to the 'target'
person. Every merge is also written to a table called 'merged', with the id's
of the two persons, and the date of the merge. This table does provide a
rudimentary audit trail, but it has another important function. In my
presentation application, there's a check in the main view: If the person_id is
found in the 'old_person_fk' of the 'merged' table, a header is sent that will
redirect the browser to the 'new_person_fk' of the same record. This satisfies
at least two important objectives:

1) External or static links to persons will never cease to function.

2) A 'skeleton' person is preserved, which will make it easier to revert a
merge that later may be proven fallacious.

This approach is a nod to the GDM 'tree-structured' approach to the 'persona',
and goes a long way towards meeting my own objections to the 'single person ID'
in my articles in the 'Forays' series.

It might be considered prudent to preserve all events of the 'source' person,
and merely copy them to the 'target' person. That, however, is a different can
of worms, and is currently not implemented.
*/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

if (!isset($_POST['posted'])) {
    $person = $_GET['person'];
    $title = "$_Merge_persons";
    require "./form_header.php";
    echo "<h2>$title</h2>\n";
    form_begin('person_merge', $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    text_input("Person 1:", 10, 'person_1', $person);
    text_input("Person 2:", 10, 'person_2');
    form_submit();
    form_end();
    echo "</body>\n</html>\n";
}
else {
    $person_1 = $_POST['person_1'];
    $person_2 = $_POST['person_2'];
    // To avoid messups, this script will run some checks to ensure that the
    // two merge candidates are sufficiently identical. As a minimum, we'll
    // check if given name, sex, and birth year are within bounds.
    $row_1 = fetch_row_assoc("
        SELECT
            given,
            patronym,
            gender,
            f_year(get_pbdate($person_1)) AS pbd
        FROM
            persons
        WHERE
            person_id = $person_1
    ");
    $row_2 = fetch_row_assoc("
        SELECT
            given,
            patronym,
            gender,
            f_year(get_pbdate($person_2)) AS pbd
        FROM
            persons
        WHERE
            person_id = $person_2
    ");
    $given_1f = fonetik($row_1['given']);
    $given_2f = fonetik($row_2['given']);
    $patronym_1f = fonetik($row_1['patronym']);
    $patronym_2f = fonetik($row_2['patronym']);
    $bdate_1 = $row_1['pbd'];
    $bdate_2 = $row_2['pbd'];
    $okay = true;
    $reason = '';
    // compare gender of candidates
    if ($row_1['gender'] != $row_2['gender']) {
        $okay = false;
        $reason .= " $_Differing_genders.";
    }
    // if both candidates have birth dates, compare them
    if ($bdate_1 && $bdate_2) {
        if (!year_comp($bdate_1, $bdate_2, 20)) {
            $okay = false;
            $reason .= " $_Differing_birth_years.";
        }
    }
    // compare given names.of candidates
    if (!soundex_comp($given_1f, $given_2f, 20)) {
        $okay = false;
        $reason .= " $_Differing_given_names. ($given_1f &lt;&gt; $given_2f)";
    }
    // compare patronyms. Don't care if it's missing for one or both candidates.
    // You may want to change this code if you're researching a non-patronymic
    // culture.
    if ($row_1['patronym'] && $row_2['patronym']) {
        if (!soundex_comp($patronym_1f, $patronym_2f, 20)) {
            $okay = false;
            $reason .=
                " $_Differing_patronyms. ($patronym_1f &lt;&gt; $patronym_2f).";
        }
    }
    // Check for parents. Abort merge if conflicting parental relations.
    $father_1 = fetch_val("SELECT get_parent($person_1, 1)");
    $mother_1 = fetch_val("SELECT get_parent($person_1, 2)");
    $father_2 = fetch_val("SELECT get_parent($person_2, 1)");
    $mother_2 = fetch_val("SELECT get_parent($person_2, 2)");
    $merge_fathers = false;
    $merge_mothers = false;
    // if both merge candidates have a father, check if they are equal.
    if ($father_1 && $father_2) {
        if ($father_1 == $father_2) { // okay to merge father relations
            $merge_fathers = true;
        }
        else { // reject merge
            $okay = false;
            $reason .= " $_Different_fathers.";
        }
    }
    // if both merge candidates have a mother, check if they are equal.
    if ($mother_1 && $mother_2) {
        if ($mother_1 == $mother_2) { // okay to merge mother relations
            $merge_mothers = true;
        }
        else { // reject merge
            $okay = false;
            $reason .= " $_Different_mothers.";
        }
    }
    // if all seems good, proceed to merge. if not, produce some sensible screen output
    if ($okay) {
        // always merge to lowest ID number
        $source = max($person_1, $person_2);
        $target = min($person_1, $person_2);
        // writing the results of this transaction to an audit log would probably be a good idea.
        pg_query("
            BEGIN
        ");
        // transfer all "events" from source to target
        pg_query("
            UPDATE participants
            SET person_fk = $target
            WHERE person_fk = $source
        ");
        pg_query("
            UPDATE participant_notes
            SET person_fk = $target
            WHERE person_fk = $source
        ");
        // merge parental relations according to discovery process above
        if ($merge_fathers) {
            // delete 'source' father relationship, transfer citation(s)
            $relid_s = get_relation_id($source, 1);
            $relid_t = get_relation_id($target, 1);
            pg_query("
                UPDATE relation_citations
                SET relation_fk = $relid_t
                WHERE relation_fk = $relid_s
            ");
            pg_query("
                DELETE FROM relations
                WHERE relation_id = $relid_s
            ");
        }
        if ($merge_mothers) {
            // delete 'source' mother relationship, transfer citation(s)
            $relid_s = get_relation_id($source, 2);
            $relid_t = get_relation_id($target, 2);
            pg_query("
                UPDATE relation_citations
                SET relation_fk = $relid_t
                WHERE relation_fk = $relid_s
            ");
            pg_query("
                DELETE FROM relations
                WHERE relation_id = $relid_s
            ");
        }
        // transfer remaining relations
        pg_query("
            UPDATE relations
            SET child_fk = $target
            WHERE child_fk = $source
        ");
        pg_query("
            UPDATE relations
            SET parent_fk = $target
            WHERE parent_fk = $source
        ");
        // update source_linkage
        pg_query("
            UPDATE source_linkage
            SET person_fk = $target
            WHERE person_fk = $source
        ");
        // insert "event" for source person with a link to target person
        $event_note = " [p=$target|ID #$target]";
        $event = fetch_val("
            INSERT INTO events (
                tag_fk,
                place_fk,
                event_date,
                sort_date,
                event_note
            )
            VALUES (
                1040,
                1,
                '000000003000000001',
                '00010101',
                '$event_note'
            )
            RETURNING event_id
        ");
        add_participant($source,$event);
        pg_query("
            INSERT INTO merged (old_person_fk,new_person_fk)
            VALUES ($source, $target)
        ");
        set_last_edit($source);
        set_last_edit($target);
        pg_query("
            COMMIT
        ");
        // return to main view of "stripped" person.
        header("Location: $app_root/family.php?person=$source");
    }
    else { // explain why the merge failed
        $title = "$_App_name: $_Merge_persons_failed";
        require "./form_header.php";
        echo "<h2>$title!</h2>\n";
        $name_1 = get_name($person_1);
        $name_2 = get_name($person_2);
        echo "<p>$_Cannot_merge $name_1 ($_born $bdate_1), $_with $name_2 ($_born $bdate_2).<br />\n";
        echo "$_Reason: $reason</p>\n";
        echo para(to_url('../family.php',
            array(person => $person_1),
            "$_Return_to $person_1 $name_1"));
        echo para(to_url('../family.php',
            array(person => $person_2),
            "$_Return_to $person_2 $name_2"));
        echo "</body>\n</html>\n";
    }
}

?>
