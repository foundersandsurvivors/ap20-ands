<?php
/***************************************************************************
 *   get_source_text.php                                                   *
 *   Yggdrasil: return source text                                         *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "settings/settings.php";
require "functions.php";

$srcid = $_GET['srcid'];
if ($srcid)
    echo ' ' . get_source_text($srcid);
?>
