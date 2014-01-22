<?php
/***************************************************************************
 *   form_header.php                                                       *
 *   Yggdrasil: Common Form Header                                         *
 *   sms: - 2012-09-16 Added ../useit_style.css and zebra form ajax        *
 *                     (experimental. I really don't like it much.         *
 *                      Seems impossible to find a good free xml editor    *
 *                      widget that works on the web and configurable.)    *
 *        - 2014-01-22 cleaned up language setting                         *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/
echo '<?xml version="1.0" encoding="UTF-8" ?>';
echo <<<FORM_HEADER
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="$language" lang="$language">
<head>
<title>xxx $title</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Author" content="Leif Biberg Kristensen" />
<link rel="stylesheet" href="../x/zf/public/css/zebra_form.css">
<link rel="stylesheet" type="text/css" href="../default.css" />
<link rel="stylesheet" type="text/css" href="../useit_style.css" />
<link rel="stylesheet" type="text/css" href="form.css" />
<link rel="shortcut icon" href="http://localhost/~leif/yggdrasil/forms/favicon.ico" />
<script type="text/javascript" src="forms.js"></script>
</head>
<body lang="$language"
FORM_HEADER;
    if (isset($form) && isset($focus)) // place cursor
        echo " onload=\"document.forms['$form'].$focus.focus()\"";
    echo ">\n";
    require_once "../x/zf/Zebra_Form.php";
    echo "<!-- form_header.php end -->\n";
?>
