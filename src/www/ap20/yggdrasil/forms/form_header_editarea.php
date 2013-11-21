<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="no" lang="no">
<?php

/***************************************************************************
 *   form_header.php                                                       *
 *   Yggdrasil: Common Form Header                                         *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

?>
<head>
<?php echo ("<title>$title</title>\n"); ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Author" content="Leif Biberg Kristensen" />
<link rel="stylesheet" type="text/css" href="form.css" />
<link rel="shortcut icon" href="http://localhost/~leif/yggdrasil/forms/favicon.ico" />
<script type="text/javascript" src="forms.js"></script>
<script language="javascript" type="text/javascript" src="/editarea/edit_area/edit_area_full.js"></script>
<script language="javascript" type="text/javascript">
editAreaLoader.init({
        id : "editarea_text"            // textarea id
        ,toolbar: "new_document, save, load, syntax_selection, search, go_to_line, fullscreen, |, undo, redo, |, select_font,|, change_smooth_selection, highlight, reset_highlight, word_wrap, |, help"
        ,syntax: "xml"                  // syntax to be uses for highgliting
        ,allow_resize: "both"
        ,font_family: "monospace"
        ,load_callback: "my_load"
        ,word_wrap: true
        ,debug: false
        ,start_highlight: true          // to display with highlight mode on start-up
});
function my_load(id){
    editAreaLoader.setValue(id, "<source>The content is loaded from the load_callback function into EditArea</source>");
}

</script>
</head>
<?php
    require_once "../langs/$language.php";
    echo "<body lang=\"$_lang\"";
    if (isset($form) && isset($focus)) // place cursor
        echo " onload=\"document.forms['$form'].$focus.focus()\"";
    echo ">\n";
?>
<!-- Header slutter her -->
