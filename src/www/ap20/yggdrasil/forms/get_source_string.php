<?php
/***************************************************************************
 *   get_source_string.php                                                 *
 *   Yggdrasil: return source text for dynamic update                      *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require "../functions.php";

$source_id = $_GET['id'];
if ($source_id)
    echo ' ' . get_source_text($source_id);
?>
