<?php

/***************************************************************************
 *   person_toggle_dcf.php                                                 *
 *   Yggdrasil: Toggle "dead child" flag                                   *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require "../functions.php";

$person = $_GET['person'];

if (fetch_val("SELECT dead_child($person)") == 'f')
    pg_query("
        INSERT INTO dead_children (person_fk)
        VALUES ($person)
    ");
else
    pg_query("
        DELETE FROM dead_children
        WHERE person_fk = $person
    ");

header("Location: $app_root/family.php?person=$person");
?>
