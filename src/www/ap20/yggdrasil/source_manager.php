<?php

/***************************************************************************
 *   source_manager.php                                                    *
 *   Yggdrasil: Source Manager                                             *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/* Logic here relies on part_type 1004 being a GROUP */

/*
This script will display events, relations, and subnodes associated with each
node in the source tree. It has evolved to become the central point of the
application besides the family view. I don't think that any other genealogy
app has anything like it.
*/

// *****************************************************************************
//                               initialization
// *****************************************************************************

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";

// we'll display only raw dates here
pg_query("SET DATESTYLE TO GERMAN");

// $self = this node, default node is root
$self = isset($_GET['node']) ? $_GET['node'] : 0;

// ap20 extension -- certain high value keys are externally managed; test: $isEXTERNAL
$isEXTERNAL = 0;
if ( $_AP20 ) {
    include 'fasidmapping.lib.php'; /* see /srv/ydev/diggers-load/10-vdlbdm-load/phpgen.sh */
     if ( $self > $XMAPLo ) {
        $isEXTERNAL = 1;
        $divi = $XMAPLo_div;
        if ( $self > $XMAPHi ) {
            $divi = $XMAPHi_div;
            $mapkey = intval($self / $XMAPHi_div);
            $maxid = preg_replace('/0+$/', '', $self);
            $diff = strlen($self) - strlen($maxid);
            if ( $diff ) { $minid = $self + 1; } else { $minid = $self; }
            while ( $diff > 0 ) { $maxid .= '9'; $diff--; }
        }
        else {
            $mapkey = intval($self / $XMAPLo_div);
        }
        $xmltype = $SOURCEMAP[$mapkey]["id"];
        $desc = $SOURCEMAP[$mapkey]["desc"];
        $branch = ""; $lz = ""; $db = ""; $xq=""; $xmlurl = ""; $xmlid = "";
   
        if ( preg_match('/^branch\./',$xmltype) ) {
            // calc min/max keys of child branches
            $branch = "yes";
        }
        else {
            // calc the xmlid of the external resource
            $lz = $SOURCEMAP[$mapkey]["lz"]; $db = $SOURCEMAP[$mapkey]["db"]; $xq = $SOURCEMAP[$mapkey]["xq"];
            $recnum = $self - ( $mapkey * $divi );
            if ($lz) {
                $xmlid = $xmltype . str_pad($recnum,$lz,"0",STR_PAD_LEFT);
            }
            else {
                $xmlid = $xmltype . $recnum;
            } 
            $xq = preg_replace('/ID/', "$xmlid", $xq);
            $xmlurl = "http://klaatu:8984/rest/" . $db . "/?query=" . $xq; 
        }
     }
}

// localized source part type label
$label = 'label_' . $language;

// get all attributes of $self in one query
$count_children_spec = "0 AS number_of_relations,0 AS number_of_subsources,";
if (!$isEXTERNAL) { $count_children_spec = "ecc($self) AS number_of_events,rcc($self) AS number_of_relations,ssc($self) AS number_of_subsources,usc($self) AS number_of_unused_subsources,"; }
// sms added msc (metasources), is_leaf and description from spt so we can test whether not to display add source
$props = fetch_row_assoc("
    SELECT
        ecc($self) AS number_of_events, $count_children_spec  msc($self) AS number_of_metasources,
        get_source_text($self) AS source_txt,
        get_prev_page($self) AS prev_page,
        get_next_page($self) AS next_page,
        link_expand(source_text) AS node_txt,
        parent_id,
        sort_order,
        source_date,
        part_type,
        ch_part_type,
        spt.$label as label,
        spt.is_leaf,
        spt.description,
        spt2.$label as subtype_label,
        xids,
        sdata,
        stree
    FROM
        sources, source_part_types spt, source_part_types spt2
    WHERE
        spt.part_type_id = sources.part_type
    AND
        spt2.part_type_id = sources.ch_part_type
    AND
        source_id = $self
");


if ( !$props ) {
    echo "<html><head><link rel='stylesheet' type='text/css' href='useit_style.css' /></head><body><div class=\"error\">\n";
    echo para("Quelle horreur -- source $self not present in database [$dbname]. Cosmic Ray Shower?");
    echo "</div></body></html>";
    return;
}

// set $self as active source
set_last_selected_source($self);

$title_prev = get_source_plain_text($props['prev_page']);
$title_next = get_source_plain_text($props['next_page']);

$title = "S:$self " . get_source_plain_text($self);
// limit title tag to a sensible length
if (mb_strlen($title) > 80)
    $title = mb_substr($title, 0, 80) . '...';

// var used by header.php to display context dependent menu items
$source_manager = true;

require "./header.php";

// *****************************************************************************
//                                   main
// *****************************************************************************

echo "<div class=\"normal\">\n";
// sms: was 'Node'. Use stree (ltree) to define an entity name; use sdata for structured data
$meta_type = 0;
if ( ($props['part_type'] >= 1000) || ($props['ch_part_type'] >= 1000) ) $meta_type = 1;
$sdata = hstore_to_array(pg_escape_string($props['sdata']));

// ap20 extension -- calc data about managed sources
if ( $isEXTERNAL ) {
     $contains_n = '';
     if (isset($sdata["n"])) $contains_n = $sdata["n"];
     if ( $branch ) {
         // a nicer label
         $branch = "Note: \"" . $props['source_txt'] . "\" branch for $desc is not editable. ";
         if ($contains_n) $branch .= "Contains a total of $contains_n external sources.";
     }
}

if ( isset($sdata['ACTION']) ) $log->debug("${meta_type}------------------------- action: $sdata[ACTION]");
//$entity = $props['stree'] ? $props['label'] : ($self ? 'Source' : 'Top of sources tree');
$entity = $self ? $props['label'] : 'Top of sources tree';

// Heading part of source

echo "<h2>\n";
if ($isEXTERNAL) {
    if ($branch) {
        // usually virtual constructions for hierarchical presentation of external sources
        echo "$entity: ". $props['source_txt']." ($desc) ". conc($props['stree'], ': ');
    }
    else {
        // an externally managed leaf source
        echo "$entity: $desc $xmlid". conc($props['stree'], ': ');
    }
}
else {
  echo "$entity #$self"
    . node_details(
        $props['number_of_events'],
        $props['number_of_relations'],
        $props['number_of_subsources'],
        $props['number_of_unused_subsources']
    )
    . conc($props['stree'], ': ');
}


if ($principal = get_source_principal($self))
    echo conc($props['source_date'])
        . conc(get_name_and_lifespan($principal), " $_of ");

echo "</h2>\n";

if ( $isEXTERNAL && $xmlid ) {
    echo para("..full source text start isEXTERNAL[$isEXTERNAL] xmlid[$xmlid]");
    $so = $props['sort_order'];
    if ($xmltype == "kgb") { $so = $props['sort_order'] / 10; }
    //square_brace($so)
    //. conc(square_brace($props['source_date']))
    // node_txt
    echo para( conc(square_brace($props['source_date']))
             . conc($props['source_txt']) );
    echo para( 
        conc(paren(
        to_url('./forms/source_xcorrect.php',
            array(
                'person'    => 0,
                'source'    => $self,
                'self'      => 1
            ), "Submit a correction to the following external record:"))
        )
    );
    $xml = get_url($xmlurl);
    // var_dump($xml);
    $xp_data = simplexml_load_string($xml[0]);
    $xp_spec = '/*'; /* means whatever the root element is */
    $xml_fields = $xp_data->xpath( $xp_spec );
    text_eac("",$xml_fields, 'list');
    echo para("..xml end");
}
else if ( !$meta_type && !$isEXTERNAL) {
    // full source text
    //echo para("..full source text start");
    echo para(
    square_brace($props['sort_order'])
    . conc(square_brace($props['source_date']))
    . conc($props['source_txt'])
    . conc(paren(
        to_url('./forms/source_edit.php',
            array(
                'person'    => 0,
                'source'    => $self,
                'self'      => 1
            ), $_edit))
        )
    );
    //echo para("..full source text end");
}

// sms: if subsources are system types, highlight this sources text
if ( $meta_type )  {
    echo para("################################################################ meta start.");
    echo "<table><tr><th align=\"top\">Meta-source:</th><th></th></tr><tr><td></td><td>$props[node_txt]</td></tr></table>";
    if ( isset($sdata['ACTION']) ) 
         require "./source_meta_manager.php";    
    echo para("################################################################ meta end. "
            . paren(to_url('./forms/source_edit.php', array( 'person'    => 0, 'source'    => $self), $_edit)) );
}

// displays feedback from add_source depending on outcome; (not i18n'd yet)
// see ddl/functions.sql function add_source() for details
$new = isset($_GET['new']) ? $_GET['new'] : 0;
if ($new && $new < 0) {
    $new = abs($new);
    echo '<p class="alert">Kilden finnes allerede, se nr. ['
        . to_url($_SERVER['PHP_SELF'], array('node' => $new), $new)
        . "]!</p>\n";
}

// *****************************************************************************
// experimental section: print list of persons mentioned in this source
// *****************************************************************************

// sms: for ap20 we like this on all LEAF sources
if ( $props['part_type'] == 1 || ( $_AP20 && $props['is_leaf'] == "t" ) ) {
    /* Source type is a birth record (or an ap20 leaf) */
    if (fetch_val("
    	    SELECT COUNT(*) FROM source_linkage WHERE source_fk=$self")) {
        echo "<h3>$_Persons_Mentioned_In_Source :</h3>\n";
        list_mentioned($self, 1);
    }
    //else
        echo para(to_url('./forms/linkage_add.php',
                    array('node' => $self), $_Add_link));
}

// *****************************************************************************
// section I: print list of events cited by this source
// *****************************************************************************

if ($props['number_of_events']) {
    echo "<h3>$_Events $_cited_by_source:</h3>\n<ol>";
    $handle = pg_query("
        SELECT
            e.event_id,
            e.tag_fk,
            e.event_date,
            get_place_name(e.place_fk) AS event_place,
            link_expand(e.event_note) AS event_note,
            get_event_type(e.event_id) AS event_type
        FROM
            events e,
            event_citations s
        WHERE
            e.event_id = s.event_fk
        AND
            s.source_fk = $self
        ORDER BY
            get_event_type(e.event_id),
            e.sort_date,
            e.event_id
    ");
    while ($row = pg_fetch_assoc($handle)) {
        $event = $row['event_id'];
        echo '<li>';
        echo square_brace($event)
            . conc(italic(get_tag_name($row['tag_fk'])))
            . conc(fuzzydate($row['event_date']))
            . conc($row['event_place']);
        if ($row['event_type'] < 3)
            // event has one or two participants, print names inline
            echo conc(list_participants($event), ': ');
        echo conc($row['event_note'], ': ');
        echo ' ' .
            paren(
            to_url('./forms/source_event_edit.php',
                    array(
                        'event'     => $event,
                        'source'    => $self
                    ), $_edit)
            . ' / '
            . to_url('./forms/citation_delete.php',
                    array(
                        'person'    => 0,
                        'event'     => $event,
                        'source'    => $self
                    ), $_delete)
            );
        if ($row['event_type'] == 3) {
            // event has any number of participants, print names as ordered list
            $subhandle = pg_query("
                SELECT
                    person_fk,
                    is_principal,
                    sort_order
                FROM
                    participants
                WHERE
                    event_fk=$event
                ORDER BY
                    sort_order
            ");
            echo '<ol>';
            while ($subrow = pg_fetch_assoc($subhandle)) {
                $participant = $subrow['person_fk'];
                $bp = $subrow['is_principal'] == 't' ? 'H ' : 'B ';
                echo '<li>' . $bp . linked_name($participant, './family.php');
                // a non-principal, eg a person mentioned as heir in a probate,
                // who may or may not be described in a separate note
                if ($subrow['is_principal'] == 'f') {
                    // print participant note if it exists
                    if ($note = fetch_val("
                        SELECT link_expand(part_note)
                        FROM participant_notes
                        WHERE person_fk = $participant
                        AND event_fk = $event
                    "))
                        echo ': ' . $note;
                    // print link to edit participant note
                    echo ' ' . paren(
                        to_url('./forms/part_note.php',
                            array(
                                'person'    => $participant,
                                'event'     => $event,
                                'node'      => $self
                            ), $_edit)
                        );
                }
                echo "</li>\n";
            }
            echo "</ol>\n";
        }
        echo "</li>\n";
    }
    echo "</ol>\n";
}

// *****************************************************************************
// section II: print list of relations cited by this source
// *****************************************************************************

if ($props['number_of_relations']) {
    $child[1] = $_son;
    $child[2] = $_daughter;
    echo "<h3>$_Relations $_cited_by_source:</h3>\n<ol>";
    $handle = pg_query("
        SELECT
            r.relation_id,
            r.parent_fk,
            r.child_fk,
            get_lsurety(r.surety_fk) AS surety
        FROM
            relations r,
            relation_citations c
        WHERE
            c.relation_fk = r.relation_id
        AND
            c.source_fk = $self
        ORDER BY
            get_pbdate(r.child_fk),
            r.child_fk,
            get_gender(r.parent_fk)
    ");
    while ($row = pg_fetch_assoc($handle)) {
        echo li(linked_name($row['child_fk'], './family.php')
            . " $_is " . $row['surety'] . ' '
            . $child[get_gender($row['child_fk'])] . " $_of "
            . linked_name($row['parent_fk'], './family.php')
        );
    }
    echo "</ol>\n";
}

// *****************************************************************************
// section III: print list of subsources
// *****************************************************************************

//if ( $administrator[$authuser] ) echo para("..debug1111");

/* sms: If parent source not 0, show parents hotlinked */
if ( $_AP20 && $props['parent_id'] > 0 ) {
   // if its a branch show it in the branch. If its a leaf, do not.
   $displaySelf = 1;
   $leafOrBranch = "Branch";
   if ( $props['is_leaf'] == "t" ) { $displaySelf = 0; $leafOrBranch = "Leaf on branch"; }
   else if ($meta_type) { $displaySelf = 0; }
   echo para( "<em><b>$leafOrBranch</b></em>: "
            . fetch_val( "select get_source_text_p1($self,$displaySelf,'".$_SERVER['SCRIPT_NAME']."');" ) );
}

if ( $administrator[$authuser] ) echo para("..debug for administrator[$authuser]:{isExt[$isEXTERNAL] entity[$entity] stree[".$props['stree']."] part_type[".$props['part_type']."] label[".$props['label']."] ch_part_type[".$props['ch_part_type']."] subtype_label[".$props['subtype_label']."] parent=[".$props['parent_id']."] ".$props['number_of_subsources']." / nmeta: ".$props['number_of_metasources']."}");

$meta_sources = ""; // to gather info for ap20 "magic" sources and print them at bottom
// if its a particular type do not calc a bunch of stuff
if ( $entity == "GROUP" && $props['subtype_label'] == "GROUP" ) { $info_about_this_subsource = ""; } // $props['part_type']
else if ($isEXTERNAL) { $info_about_this_subsource = ""; }
else { $info_about_this_subsource = "ecc(source_id) AS e, rcc(source_id) AS r, ssc(source_id) AS s, usc(source_id) AS u,"; }

if ($isEXTERNAL) {
    if ($branch) { echo para($branch); }
    else { echo para($desc . " (not editable)."); }
}

if ($isEXTERNAL || $props['number_of_subsources'] ) { //&& $props['number_of_subsources'] > $props['number_of_metasources']) {
    if ( $self == 0 ) { echo "<h3>Browse $_sources:</h3>"; }
    elseif ( $props['number_of_subsources'] > $props['number_of_metasources'] ) { echo "<h3>$_Subsources:</h3>"; }
    echo "<table>";
    $handle = pg_query("
        SELECT
            source_id,
            parent_id,
            link_expand(source_text) AS txt,
            sort_order,
            source_date,
            $info_about_this_subsource
            spt.$label AS $label,
            spt.is_leaf as is_leaf,
            part_type,
            ch_part_type,
            xids,
            sdata,
            stree
        FROM
            sources, source_part_types spt
        WHERE
            spt.part_type_id = sources.part_type
        AND
            parent_id = $self
        AND
            source_id <> 0
        ORDER BY
            sort_order,
            source_date,
            source_text
    ");
    // change between FALSE and TRUE to select terse / informative mode
    $friendly = TRUE;
    //$friendly = FALSE; 
    echo "\n<!-- subsources START: -->\n";
    while ($row = pg_fetch_assoc($handle)) {
        $id = $row['source_id'];
        $subsrc_display = ''; // sms: we want to reuse this below, make it a string

        // protect external sources/branches from user changes

        $editable = '';
        $f_editable = '';
        if ( !$isEXTERNAL ) { 
             $f_editable = ' / ' . to_url('./forms/source_edit.php', array( 'person'    => 0, 'source'    => $id), $_Edit);
             $editable =           to_url('./forms/source_edit.php', array( 'person'    => 0, 'source'    => $id), $row[$label], $_Edit);
        }
        if ($isEXTERNAL) {
            $s_o = '';
            if ( $row['is_leaf'] == "t" ) {
                 // add sort_order where its a leaf (its meaningful for vdlbdm)
                 $s_o = td_numeric(square_brace(bold($row['sort_order'])));
            }
            $subsrc_display = '<tr>'
            . td(paren(to_url($_SERVER['PHP_SELF'],
            array('node' => $id), $_Select)))
            . td(paren($row[$label]))
            . $s_o;
        }
        else if ($friendly) {
            $subsrc_display = '<tr>'
            . td(paren(to_url($_SERVER['PHP_SELF'],
            array('node' => $id), $_Select)
            . $f_editable
                        ))
            . td_numeric(square_brace($row['sort_order']))
            . td(paren($row[$label]));
        }
        else {
            $subsrc_display = '<tr>'
            . td_numeric(square_brace(to_url($_SERVER['PHP_SELF'],
                                      array('node' => $id), $id, $_goto)))
            . td_numeric(square_brace($row['sort_order']))
            . td(paren($editable
                    ));
        }

        // ap20: sms add for system/workflow source controllers (if this or its parent is SYSTEM part_type i.e. > 1000)

        if ( ($row['part_type'] >= 1000) || ($row['ch_part_type'] >= 1000) ) {
            // stree "entity" is displayed
            $entity_label = $row['stree'] ? $row['stree'] : "Entity label not defined";
            // get the ch_part_type label
            $r = pg_fetch_assoc(pg_query("select $label from source_part_types where part_type_id=$row[ch_part_type]"));
            $meta_sources .= $subsrc_display . td("<em>$entity_label</em> $r[$label]")."</tr>"; // .$row['txt']); //." (system: type[".$row['part_type']."] subtype[".$row['ch_part_type']."])");
        }
        // sms back to normal
        

        elseif ( $entity == "GROUP" && $row['part_type'] == 1004 ) { // alot of overhead calculating e, r, s, u -- bypass
            echo $subsrc_display . td(square_brace(italic($row['source_date']))
                . ' ' . $row['txt'] )
                . '</tr>';
        }
        elseif ( $isEXTERNAL ) {
            $sd = hstore_to_array($row['sdata']);
            $contains_n_here = '';
            if ( isset($sd["n"]) ) $contains_n_here = " (". italic($sd["n"]).") ";
            $isDated = '';
            if ( $row['source_date'] ) { $isDated = square_brace(italic($row['source_date'])); }
            if ( $props['is_leaf'] == "f" ) {
                 // no sort order
            }
            else {
                 // add sort_order where its a leaf (its meaningful for vdlbdm)
            }
            echo $subsrc_display . td( $isDated . ' '. $desc . ' ' . bold($row['txt']) . $contains_n_here )
                . '</tr>';
        }
        elseif ($info_about_this_subsource and ($row['e'] || $row['r'] || $row['s'])) {
            echo $subsrc_display . td(square_brace(italic($row['source_date']))
                . ' ' . $row['txt']
                . node_details($row['e'], $row['r'], $row['s'], $row['u']))
                . '</tr>';
        }
        else { // source is unused, print with gray text
            if ($show_delete) { // show link for source deletion
                echo $subsrc_display . td(span_type(square_brace(italic($row['source_date']))
                    . conc($row['txt']),"faded","Source is unused -- ok to delete.")
                    . conc(paren(to_url('./forms/source_delete.php',
                                array(
                                    'node'  => $self,
                                    'id'    => $id
                                ), bold($_delete), "Source is unused -- ok to delete."))))
                    . '</tr>';
            }
            else
                echo $subsrc_display . td(span_type(square_brace(italic($row['source_date']))
                    . conc($row['txt']), "faded", "Source is unused -- ok to delete, but you do not have deletion rights."))
                    . '</tr>';
        }
    }
    echo "</table>\n";
    echo "<!-- subsources END -->\n";
}

// sms 29 July 2011 check for leaf source and don't display Add source link if its a leaf.
// If $_No_Add_source_leaf is not defined in langs/LANG.php then program behaves as before.
// dbg: echo "<p>.._No_Add_source_leaf[$_No_Add_source_leaf] is_leaf[".$props['is_leaf']."]</p>";
// Hmm?????       if ( $_No_Add_source_leaf and $props['is_leaf'] == "t" ) {
/*if ( $props['is_leaf'] == "t" ) {
   echo para("[" . $props['description']."] ".$_No_Add_source_leaf." ..".$props['ch_part_type']);
} */
if ( $props['ch_part_type'] == 0 && $props['is_leaf'] == "f") {
   echo para("$_Subsource type is not defined. Edit this source and choose a subsource type to be able to add subsources.");
}
elseif ( $isEXTERNAL ) {

   if ($contains_n and preg_match('/^branch\./',$xmltype) and !preg_match('/0000$/',$self) ) {

       // mapped special high dbids to external xmlids -- special handling (Yggdrasil is portaal/aggregator of these)
       echo "<hr/".para("abc..xml  $self xmlid[$xmlid] mapkey[$mapkey] branch[$branch] type[$xmltype] desc[$desc] lz[$lz] contains_n[$contains_n]");
       $restxq_request = "/id/".$self.'?debug';
       echo "<!-- \$restxq_request=[$restxq_request] start -->";
       $xml = get_restxq_xml2($restxq_request);
   
       if ($xml) {
           $xp_data = simplexml_load_string($xml);
           $xp_spec = '/*/*'; /* means whatever the root element is */
           $root_name = $xp_data->getName();
           $title = "&lt;$root_name&gt;";
           #echo "<h4>(get_restxq_xml) \$basex_restxq[$basex_restxq] \$restxq_request[$restxq_request]<br/> returned $title</h2>\n";
           if ($root_name == "html") { 
               echo $xml . '<iframe id="results" width="100%" height="400" scrolling="yes"></iframe>'; 
           }
           else if ($root_name == "div") {
               /* show the html */
               echo $xml;
           }
           else if ($root_name == "nyi") {
               $xp_spec = '/*'; /* means whatever the root element is */
               $xml_fields = $xp_data->xpath( $xp_spec );
               text_eac("",$xml_fields, 'list');
           }
           else {
               $xp_spec = '/*/*'; /* means whatever the root element is */
               $xml_fields = $xp_data->xpath( $xp_spec );
               text_eac("",$xml_fields, 'list');
           }
       }
       else {
           echo "<h2>[$test3] 2.(get_restxq_xml) failed. empty \$xml \$basex_restxq[$basex_restxq] \$restxq_request[$restxq_request]</h2>\n";
       }
       echo "<!-- \$restxq_request=[$restxq_request] end -->";
   }
   echo "<hr/>";
}
elseif ( $props['is_leaf'] == "f" ) {

   // where source stree matches: khrd.families.XXXX generate list of people from person keys

   if ( preg_match("/^khrd.families.([A-Z]{4})$/",$props['stree'], $m) ) {
       search_and_show_persons("keys::text like '%$m[1]%'","<h3>Persons assigned to $m[1] clan by legacy keys:</h3>",".");
   }

   // sms: check for some special programmed cohorts (these are unusual cases)

   elseif ( preg_match("/^cohorts.([A-Za-z]+)$/",$props['stree'], $m) ) {
       search_and_show_persons("defined(p_sdata,'$m[1]')","<h3>Persons in cohort \"$m[1]\":</h3>",".");
   }
   else {
       // For branches, extra add source link to avoid scrolling
       echo para(to_url('./forms/source_add.php', array('node' => $self), $_Add_source));
   }
}

// sms display meta stuff
if ( $meta_sources ) {
    echo "<hr /><h3>Meta-subsources:</h3>"
       . "<p>These pseudo-sources define read-only reference material, cohorts, and workflow controllers.</p>"
       . "<table>$meta_sources</table>";
}
echo "</div>\n";


include "./footer.php";

?>
