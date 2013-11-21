<?php
/***************************************************************************
 *   get_name_string.php                                                   *
 *   Yggdrasil: return person name for dynamic update                      *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require "../functions.php";

$person_id = $_GET['id'];
if ($person_id)
    echo ' ' . linked_name($person_id, '../family.php');
else
    echo ' [Undefined]';
?>
