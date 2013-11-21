<?php

/***************************************************************************
 *   linkage_delete.php                                                    *
 *   Yggdrasil: Source Linkage Delete Action                               *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// Note: This script will summarily delete a row in the source_linkage table.

require "../settings/settings.php";

$node = $_GET['node'];
$id = $_GET['id'];

pg_query("DELETE FROM source_linkage WHERE source_fk = $node AND per_id = $id")
    or die(pg_last_error());

header("Location: $app_root/source_manager.php?node=$node");
?>
