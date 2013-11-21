<?php

/***************************************************************************
 *   person_delete.php                                                     *
 *   Yggdrasil: Delete Person Action                                       *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// Note: This script will delete a person. It should not be used if a person
// already has been published on the net; use merge instead.

// The calling code in family.php will not display the link to this routine
// if any dependencies to the person exist. (See also the SQL function
// conn_count() and the corresponding get_connection_count() in functions.php)

require "../settings/settings.php";
require "../functions.php";

$person = $_GET['person'];

pg_query("BEGIN");
// table 'merged' should probably have been created with
// old_person_fk INTEGER REFERENCES persons (person_id) ON DELETE CASCADE
pg_query("DELETE FROM merged WHERE old_person_fk = $person");
pg_query("DELETE FROM persons WHERE person_id = $person");
pg_query("DELETE FROM participant_notes WHERE person_fk = $person");
pg_query("COMMIT");

// this script is the one obvious exception to the rule that every action
// invoked from the family view should return to the current person.
header("Location: $app_root/index.php");
?>
