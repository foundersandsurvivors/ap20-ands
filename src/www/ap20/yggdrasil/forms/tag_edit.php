<?php

/***************************************************************************
 *   tag_edit.php                                                          *
 *   Yggdrasil: Update Tag Script                                          *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

if (!isset($_POST['posted'])) {
    $tag = $_GET['tag'];
    $tagname = fetch_val("SELECT get_tag_name($tag)");
    $title = "$_Edit $_event_definition #$tag ($tagname)";
    require "./form_header.php";
    if ($tag == 0) { // new tag type
        $tag_group    = 8;  // group 'other' by default
        $tag_name     = '';
        $gedcom_tag   = 'NOTE'; // GEDCOM tag = NOTE by default
        $tag_label    = '';
        $tag_type     = 1; // single-person by default
    }
    else {
        $tag_row = fetch_row_assoc("SELECT * FROM tags WHERE tag_id = $tag");
        $tag_group  = $tag_row['tag_group_fk'];
        $tag_name   = $tag_row['tag_name'];
        $gedcom_tag = $tag_row['gedcom_tag'];
        $tag_label  = $tag_row['tag_label'];
        $tag_type   = $tag_row['tag_type_fk'];
    }
    echo "<h2>$title</h2>\n";
    form_begin('tag_edit', $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    hidden_input('tag', $tag);
    select_tag_group($tag_group);
    select_tag_type($tag_type);
    text_input("GEDCOM :", 10, 'gedcom_tag', $gedcom_tag);
    text_input("Tag name :", 80, 'tag_name', $tag_name);
    text_input("Tag label:", 80, 'tag_label', $tag_label);
    form_submit();
    form_end();
    help_local_file('event',$tag);
    echo "</body>\n</html>\n";
}
else {
    $tag = $_POST['tag'];
    $tag_group = $_POST['tag_group'];
    $tag_name = $_POST['tag_name'];
    $gedcom_tag = $_POST['gedcom_tag'];
    $tag_label = $_POST['tag_label'];
    $tag_type = $_POST['tag_type'];
    //rev46: if ($tag == 0) { // insert new tag
    //rev46:     pg_query("BEGIN WORK");
    if (!$tag) { // insert new tag
        pg_query("BEGIN");
        $tag = get_next('tag');
        // SMS 20 July 2011: $tag_type repositioned in tags table, was last, now third column
        pg_query("INSERT INTO tags (
                      tag_id,
                      tag_group_fk,
                      tag_type_fk,
                      tag_name,
                      gedcom_tag,
                      tag_label
                  )
                  VALUES (
                      $tag, 
                      $tag_group, 
                      $tag_type, 
                      '$tag_name', 
                      '$gedcom_tag', 
                      '$tag_label'
                  )"
        );
        pg_query("COMMIT");
    }
    else { // modify existing tag
        pg_query("UPDATE tags SET tag_group_fk = $tag_group, tag_name = '$tag_name',
                    gedcom_tag = '$gedcom_tag', tag_label = '$tag_label', tag_type_fk = $tag_type
                        WHERE tag_id = $tag");
    }
    header("Location: $app_root/tag_manager.php");
}

?>
