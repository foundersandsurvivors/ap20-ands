<?php

/***************************************************************************
 *   family.php                                                            *
 *   Yggdrasil: Interactive Family Group Sheet                             *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/***************************************************************************
 *   This script is the primary "workplace" of Exodus. This is where you   *
 *   add / edit / delete parents, spouses, and children, as well as events *
 *   and sources. It is modeled after the traditional genealogy            *
 *   "Family Group Sheet".                                                 *
 ***************************************************************************/


/**************************************************************************
 ***             Functions used only in this script                     ***
 **************************************************************************/

function print_bd($p, $g) {
    // This func prints birth and death events for spouses and children,
    // appended with inline source citations if primary sources exists for
    // those events.
    // NOTE that the $g var is used to reference both
    // tag_groups.tag_group_id where birth = 1 and death = 3,
    // and sources.part_type where baptism = 1 and burial = 3.
    // If you have selected other part_type keys for your primary birth and
    // death sources, this func won't print source references to BD events.
    // In that case you may want to use the outcommented section of
    // pop_child() below which will cite sources to parent/child relations.
    if ($row = fetch_row_assoc("
        SELECT
            event_key,
            tag_key,
            event_date,
            get_place_name(place_key) place_name
        FROM
            person_event_groups
        WHERE
            person = $p
        AND
            group_key = $g
        ")) {
        $src = fetch_val("
            SELECT
                get_source_text(source_id)
            FROM
                event_citations e, sources s
            WHERE
                e.event_fk = " . $row['event_key'] . "
            AND
                e.source_fk = s.source_id
            AND
                s.part_type = $g
            ORDER BY
                s.source_date ASC
            LIMIT 1
        ");
        echo para(get_tag_name($row['tag_key'])
            . conc(fuzzydate($row['event_date']))
            . conc($row['place_name'])
            . conc($src
                ? span_type(paren($src), "inline_source")
                : ''), "bmd");
    }

}

function print_marriage($p, $p2=0)  {
    global $_Married, $language;
    $handle = pg_query("
        SELECT
            event_date,
            place_name,
            spouse
        FROM
            marriages
        WHERE
            person = $p
    ");
    while ($row = pg_fetch_assoc($handle)) {
        if (!$p2 || $p2 != $row['spouse']) {
            echo para($_Married
                . conc(fuzzydate($row['event_date']))
                . conc($row['place_name'])
                . conc(fetch_val("SELECT prepose(4, '$language')"))
                . conc(linked_name($row['spouse']))
                . conc(child_of($row['spouse'])), "bmd");
        }
    }
}

function pop_child($child, $parent, $coparent=0) {
    global $_Child, $_Source, $_with, $_toolhelp_has_descendants;
    $name = get_name($child);
    $sentence = bold($_Child . ':')
        . conc(linked_name($child));
    if ($coparent) // illegitimate child, print coparent
        $sentence .= conc($_with) . conc(linked_name($coparent));
    if (has_descendants($child))
        $sentence .= conc(span_type('+',
            "alert", sprintf($_toolhelp_has_descendants, $child)));
    $sentence = para($sentence, "name");
    /*
    // This section has become obsolete with the addition of inline source
    // citations in print_bd() above. Left here because you may prefer to
    // document parent/child relations rather than BD events of spouses and
    // children, or maybe both.
    // print relation source(s)
    $handle = pg_query("
        SELECT
            source_text
        FROM
            relation_notes
        WHERE
            note_id = (
            SELECT
                relation_id
            FROM
                relations
            WHERE
                child_fk = $child
            AND
                parent_fk = $parent
        )
    ");
    while ($row = pg_fetch_assoc($handle)) {
        $sentence .= para(paren($_Source . ':'
            . conc(ltrim($row['source_text']))), "childsource");
    }
    */
    echo $sentence;
    print_bd($child,1);
    print_marriage($child);
    print_bd($child,3);
    pg_query("DELETE FROM tmp_children WHERE child = $child");
}

function cite($record, $type, $person, $principal=1) {
    // build list of cited sources and return note numbers
    // $record is event_id or relation_id, depending on $type
    // $type can take the values 'event' or 'relation'
    global $_delete, $_toolhelp_cit_delete;
    $notes = $type . '_notes';
    $handle = pg_query("
        SELECT
            source_id
        FROM
            $notes
        WHERE
            note_id = $record
    ");
    while ($row = pg_fetch_row($handle)) {
        // build string for each citation
        // note side effect of cite_seq() - cf. ddl/functions.sql
        $cit = fetch_val("SELECT cite_seq($row[0])");
        if ($principal)
            $cit .= conc(span_type(paren(to_url('./forms/citation_delete.php',
                array(  'person' => $person,
                        'source' => $row[0],
                        $type => $record), $_delete)),
                        "hotlink", sprintf($_toolhelp_cit_delete, $row[0])));
        $citation_list[] = $cit;
    }
    if (isset($citation_list))
        return sup(join($citation_list, ', '));
    else
        return '';
}

function show_parent($person, $gender) {
    // print names and lifespans of parents.
    // valid $gender values are 1=father, 2=mother
    global $language, $_Add, $_Insert, $_edit, $_delete,
        $_Father, $_father, $_Mother, $_mother,
        $_toolhelp_edit_parent, $_toolhelp_add_parent, $_toolhelp_insert_parent,
        $_toolhelp_delete_parent;
    $parent_id = fetch_val("SELECT get_parent($person, $gender)");
    $surety = fetch_val("
        SELECT get_lsurety((
            SELECT surety_fk
            FROM relations
            WHERE parent_fk = $parent_id
            AND child_fk = $person
        ), '$language')
    ");
    if ($gender == 1) {
        $Parent = $_Father;
        $parent = $_father;
        $para = '<p>';
        $newline = '<br />';
    }
    else { // $gender == 2
        $Parent = $_Mother;
        $parent = $_mother;
        $para = '';
        $newline = '</p>';
    }
    echo $para
        . conc(bold($Parent) . ':')
        . conc(get_name_and_dates('', $parent_id));
    if ($parent_id) {
        echo conc(curly_brace($surety))
            . conc(span_type(paren(
            to_url('./forms/relation_edit.php',
                array(  'person' => $person,
                        'parent' => $parent_id), $_edit,
                        sprintf($_toolhelp_edit_parent, $person, $parent))
            . ' / '
            . to_url('./forms/relation_delete.php',
                array(  'person' => $person,
                        'parent' => $parent_id), $_delete,
                        sprintf($_toolhelp_delete_parent, $person, $parent))
                        ), "hotlink"))
            . cite(get_relation_id($person, $gender), 'relation', $person);
    }
    else {
        echo conc(span_type(paren(
            to_url('./forms/person_insert.php',
                array(  'person' => $person,
                        'addparent' => 'true',
                        'gender' => $gender), "$_Add $parent",
                        sprintf($_toolhelp_add_parent, $parent))
            . ' / '
            . to_url('./forms/relation_edit.php',
                array(  'person' => $person,
                'gender' => $gender), "$_Insert $parent",
                sprintf($_toolhelp_insert_parent, $parent))
                ), "hotlink"));
    }
    echo "$newline\n";
}

function is_principal($p, $e) {
    if (fetch_val("
        SELECT
            is_principal
        FROM
            participants
        WHERE
            person_fk=$p AND event_fk=$e
        ") == 't')
        return 1;
    else
        return 0;
}

function get_principals($e) {
    global $_and;
    $handle = pg_query("
        SELECT
            person_fk
        FROM
            participants
        WHERE
            event_fk=$e AND is_principal IS TRUE
        ORDER BY
            sort_order
        ");
    while ($row = pg_fetch_row($handle)) {
        $p[] = linked_name($row[0]);
    }
    return join($p, " $_and ");
}

/**************************************************************************
 ***                           MAIN PROGRAM                             ***
 **************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
$person = $_GET['person'];
$name = get_name($person);
$title = "$person $name, $_family";
// get gender and last_edit
$row = fetch_row_assoc("
    SELECT
        gender,
        last_edit,
        keys,
        is_public(person_id) AS is_public
    FROM
        persons
    WHERE
        person_id = $person
");

$gender = $row['gender'];
$last_edited = $row['last_edit'];
$is_public = $row['is_public'];
$family = true;
require "./header.php";

// create temporary sources table
pg_query("
    CREATE TEMPORARY TABLE tmp_sources (
        citation_id     SERIAL PRIMARY KEY,
        source_id       INTEGER
    )
");

// heading
echo "<div class=\"normal\">\n";
echo "<h2";
if ($is_public == 'f')
    echo ' class="faded"';
echo ">$name";
echo "</h2>\n";

// build edit / delete person string
$ep = to_url('./forms/person_update.php',
            array('person' => $person), $_Edit_person, "$_Edit $_person $person");
// if this person is unconnected and "has" no events, display delete hotlink
// see note in person_delete.php
if (get_connection_count($person) == 0)
    $ep .= ' / '
        . to_url('./forms/person_delete.php',
            array('person' => $person), $_Delete_person);

// print person vitae
echo para("$_ID: $person ".$row['keys'].", "
        . $_Gender . ': ' . gname($gender) . '<br />'
        . "$_last_edited  " . mydate($last_edited)
        . conc(span_type(paren($ep), "hotlink","Edit person(".$person.")")));

show_parent($person, 1); // father
show_parent($person, 2); // mother

// print annotated events
echo "<h3>$_Events</h3>\n";

$handle = pg_query("
    SELECT
        event_number,
        event_type_number,
        event_date,
        event_place,
        event_note
    FROM
        person_events
    WHERE
        person = $person
");
$icd10done = 0;
while ($row = pg_fetch_assoc($handle)) {
    $event_string = '';
    $head = '<p>';
    $principal = 1; // show 'edit / delete' hotlink by default
    $fade = 0; // display "secondary" events as faded
    $event = $row['event_number'];
    $tag = $row['event_type_number'];
    // To create flowing text with inline source citations, a note may be split
    // into multiple parts. A note which starts with '++' is considered a
    // continuation of the previous note, while a note which ends with '++' must
    // suppress the closing paragraph.
    if (substr($row['event_note'], -2) == '++') {
        $row['event_note'] = rtrim($row['event_note'],' +');
        $tail = ' ';
    }
    else {
        $tail = "</p>\n";
    }
    if (substr($row['event_note'], 0, 2) == '++') {
        $event_string = ltrim($row['event_note'],' +');
        $head = '';
    }
    else {
        // display each "event" as
        // Event_id EVENT-TYPE[ DATE][ PLACE][ with Name Of Coprincipal][: NOTE]
        // note that every item except for EVENT-TYPE is optional.
        $event_string .= span_type("[$event]", 'hotlink',"Event ID $event") . ' ';
        // preliminary hack to display non-participant of probate event
        // note that tag type id is hard coded, which is prbly not a good idea.
        if ($row['event_type_number'] == 31 && !(is_principal($person, $event))) {
            $event_string .= $_Mentioned_in_probate_after . ' ' . get_principals($event);
            if(!$row['event_note'] = get_participant_note($person, $event))
                $fade = 1;
            $principal = 0;
        }
        else
            $event_string .= get_tag_name($tag);
        // fuzzydate() returns empty string if date is undetermined
        $event_string .= conc(fuzzydate($row['event_date']));
        $event_string .= conc($row['event_place']);
        // is there a second principal of this event?
        if (fetch_val("SELECT get_event_type($event)") == 2
                && $coprincipal = get_second_principal($event, $person)) {
            $event_string .= conc(fetch_val("select prepose($tag, '$language')"))
                . conc(linked_name($coprincipal))
                . conc(child_of($coprincipal));
        }
        if ($row['event_note'])
            $event_string .= ': ' . $row['event_note'];
    }
    if ($fade)
        $event_string = span_type($event_string, "faded");
    // display links to edit / delete actions
    if ($principal) {
        // probably okay to delete one-person events
        if (fetch_val("SELECT get_event_type($event)") == 1)
            $delstr = to_url('./forms/event_delete.php', array('person' => $person, 'event' => $event), $_delete);
        else
            $delstr = "<a href=\"javascript:nanny('./forms/event_delete.php?person=$person&amp;event=$event')\">$_delete</a>";
        $event_string .= conc(span_type(paren(
            to_url('./forms/event_update.php', array(
                            'person' => $person,
                            'event' => $event), $_edit)
            . ' / ' . $delstr), "hotlink", sprintf( $_toolhelp_editdel_event, $event, $person) ));
    }
    else { // non-principal, display links to edit "witness" note
        $event_string .= conc(span_type(paren(
            to_url('./forms/part_note.php', array(
                            'person' => $person,
                            'event' => $event), $_edit)
                ), "hotlink", sprintf($_toolhelp_editdel_event_witness, $event) ));
    }
    // store and display source references
    $event_string .= cite($event, 'event', $person, $principal);
    echo $head . $event_string . $tail;

    // ap20 extension - if death, display cause of death data from google spreadsheet

    // need a user interface for toggling $icd10_googleIntegration
    if ( $icd10_googleIntegration && $tag == 3 && isset($ICD10_key) && $icd10done == 0 ) {    
         $icd10info = icd10_deathCause_from_google_for_person('0AoiyjW0rNNcCdGczUU5kOUY0SV9NTjYwNV8tVi01SXc',$person,'html');
         if ( $icd10info ) {
              echo "<ul><li>Cause of death reasons and ICD10 encoding (experimental): " . $icd10info . "</li></ul>";
         }
         $icd10done++;
    }

}

// print sources as ordered list. note that the list item numbers are
// logically disconnected from the citation_ids, but this is no problem as
// strict order is enforced on both sides
$handle = pg_query("SELECT
                        source_id,
                        get_part_type(source_id) AS part_type,
                        get_source_text(source_id) AS source_text
                    FROM
                        tmp_sources
                    ORDER BY
                        citation_id");
if (pg_num_rows($handle)) {
    echo "<h4>$_Sources $_underlying_events</h4>\n";
    echo "<ol class=\"sources\">\n";
    while ($sources = pg_fetch_assoc($handle)) {
        echo li(($sources['part_type'] == 0
                ? span_type($sources['source_text']."<p/>", 'alert', $_toolhelp_untyped_source)
                : $sources['source_text']." ")
            . conc(span_type(paren(
                to_url('./source_manager.php',
                            array(
                                'node' => $sources['source_id']), "$_view $_source")
            . ' / '
            .   to_url('./forms/source_edit.php',
                            array(
                                'person' => $person,
                                'source' => $sources['source_id']), $_edit)
            . ' / '
            . to_url('./forms/source_select.php',
                            array(
                                'person' => $person,
                                'source' => $sources['source_id']), $_use)
            ), "hotlink", sprintf($_toolhelp_edituse_source, $sources['source_id']) ))
            . "<p/>" );  // $item_number genned from the <ol> now.
    }
    echo "</ol>\n";
}

// new section: "mentioned in sources"

if (fetch_val("SELECT COUNT(*) FROM source_linkage WHERE person_fk=$person")) {
    echo "<h3>$_Mentioned_In_Source:</h3>\n";
    $label = 'label_' . $language;
    $handle = pg_query("
        SELECT
            s.source_id AS source,
            l.per_id AS per_id,
            spt.$label AS s_type,
            get_lsurety(l.surety_fk,'$language') AS surety,
            get_lrole(l.role_fk,'$language') AS rolle,
            l.s_name AS name,
            get_source_text(s.source_id) AS txt,
            link_expand(l.sl_note) AS note
        FROM
            sources s,
            source_linkage l,
            source_part_types spt
        WHERE
            l.source_fk = s.source_id
        AND
            l.person_fk = $person
        AND
            spt.part_type_id = s.part_type
        ORDER BY
            s.source_date
    ");
    echo "<ul>\n";
    // sms: show source_linkage to people for ALL roles, not just children (was AND role_fk = 1)
    while ($row = pg_fetch_assoc($handle)) {
        if ($principal = fetch_val("SELECT person_fk FROM source_linkage WHERE source_fk = ". $row['source'] )) 
        {
            echo li($row['s_type']
                . conc(linked_name($principal), " $_of ")
                . conc($row['surety'], ', ')
                . conc($row['rolle'])
                . ' «' . $row['name'] . '»<br />'
                . conc(square_brace(to_url('./source_manager.php', array('node' => $row['source']), $row['source'], $_View_source)))
                . " " . $row['txt']
                . conc(italic($row['note']))
                . conc(paren(
                    to_url("$app_path/forms/linkage_edit.php",
                            array(
                                'node'      => $row['source'],
                                'id'        => $row['per_id'],
                                'person'    => $person
                            ), $_edit, $_Edit_link)))
            );
        }
    }
    echo "</ul>\n";
}

// conditionally print spouses and children
if (has_spouses($person) || has_descendants($person)) {
    if (has_descendants($person)) {
        // create and populate temporary children table
        pg_query("
            CREATE TEMPORARY TABLE tmp_children (
                child INTEGER PRIMARY KEY,
                coparent INTEGER,
                pb_date CHAR(18)
            )"
        );
        pg_query("
            INSERT INTO tmp_children
                SELECT
                    child_fk,
                    get_coparent($person, child_fk),
                    get_pbdate(child_fk) AS pbd
                FROM
                    relations
                WHERE
                    parent_fk = $person
                ORDER BY
                    pbd
            ");
    }

    echo "<h3>$_Family</h3>\n";

    // get spouses
    $handle = pg_query("
        SELECT
            spouse
        FROM
            marriages
        WHERE
            person = $person
        ");
    while ($spouses = pg_fetch_row($handle)) {
        echo para(bold($_Spouse . ':')
            . conc(linked_name($spouses[0])), "name");
        print_bd($spouses[0],1);
        print_marriage($spouses[0], $person);
        print_bd($spouses[0],3);

        // for each spouse, get children
        if (has_descendants($person)) {
            $subhandle = pg_query("SELECT child
                                    FROM tmp_children
                                    WHERE coparent = $spouses[0]
                                    ORDER BY pb_date");
            while ($children = pg_fetch_row($subhandle)) {
                pop_child($children[0], $person);
            }
        }

        // add child with this spouse?
        if ($gender == 1) {
            $father = $person;
            $mother = $spouses[0];
        }
        else {
            $mother = $person;
            $father = $spouses[0];
        }
        echo para(
            to_url('./forms/person_insert.php', array(
                'father' => $father, 'mother' => $mother),
                $_Add_child_with . conc(get_name($spouses[0]))), "hotlink");
    }

    // get children with unknown coparent
    if (has_descendants($person)) {
        $subhandle = pg_query("SELECT child
                                FROM tmp_children
                                WHERE coparent = 0
                                ORDER BY pb_date");
        if (pg_num_rows($subhandle)) {
            $coparent = $gender == 1 ? $_Mother : $_Father;
            echo para(bold("$coparent $_unidentified"));
            while ($children = pg_fetch_row($subhandle)) {
                pop_child($children[0], $person);
            }
        }

        // get other children, ie. coparent is known, but no marriage is implied
        $subhandle = pg_query("SELECT child, coparent
                                FROM tmp_children
                                ORDER BY pb_date");
        if (pg_num_rows($subhandle)) {
            echo "<h4>$_Other_children:</h4>\n";
            while ($children = pg_fetch_assoc($subhandle)) {
                pop_child($children['child'], $person, $children['coparent']);
            }
        }
    }
}
echo "</div>\n";
include "./footer.php";
?>
