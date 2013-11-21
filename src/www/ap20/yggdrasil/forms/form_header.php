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

/* Changes:
   2012-09-16 sms Added ../useit_style.css and zebra form ajax experiment
*/

?>
<head>
<?php echo ("<title>$title</title>\n"); ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Author" content="Leif Biberg Kristensen" />
<link rel="stylesheet" href="../x/zf/public/css/zebra_form.css">
<link rel="stylesheet" type="text/css" href="../useit_style.css" />
<link rel="stylesheet" type="text/css" href="form.css" />
<link rel="shortcut icon" href="http://localhost/~leif/yggdrasil/forms/favicon.ico" />
<script type="text/javascript" src="forms.js"></script>
</head>
<?php
    require_once "../langs/$language.php";
    echo "<body lang=\"$_lang\"";
    if (isset($form) && isset($focus)) // place cursor
        echo " onload=\"document.forms['$form'].$focus.focus()\"";
    echo ">\n";
    require_once "../x/zf/Zebra_Form.php";
?>
<!-- Header slutter her -->
