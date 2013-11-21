<?php

/***************************************************************************
 *   person_toggle_pf.php                                                  *
 *   Yggdrasil: Toggle private flag                                        *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require "../functions.php";

$person = $_GET['person'];

if (fetch_val("SELECT is_public($person)") == 't')
    pg_query("
        INSERT INTO private_persons (person_fk)
        VALUES ($person)
    ");
else
    pg_query("
        DELETE FROM private_persons
        WHERE person_fk = $person
    ");

header("Location: $app_root/family.php?person=$person");
?>
