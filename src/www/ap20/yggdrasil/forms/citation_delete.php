<?php

/***************************************************************************
 *   citation_delete.php                                                   *
 *   Yggdrasil: Delete Citation                                            *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";

$person = $_GET['person'];
$source = $_GET['source'];

if ($_GET['relation']) {
    $record = $_GET['relation'];
    $query = "DELETE FROM relation_citations WHERE source_fk = $source AND relation_fk = $record";
}

if ($_GET['event']) {
    $record = $_GET['event'];
    $query = "DELETE FROM event_citations WHERE source_fk = $source AND event_fk = $record";
}

pg_query($query) or die(pg_last_error());

// this script is called from two different locations. One sets $person, the other doesn't.
if ($person) {
    header("Location: $app_root/family.php?person=$person");
}
else {
    header("Location: $app_root/source_manager.php?node=$source");
}

?>