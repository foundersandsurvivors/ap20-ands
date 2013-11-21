<?php

/***************************************************************************
 *   source_delete.php                                                     *
 *   Yggdrasil: Source Delete                                              *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// This script will delete a source. It is callable from source_manager.php
// if and only if there are no associated citations or subsources.

require "../settings/settings.php";
require "../functions.php";

$node = $_GET['node'];
$id = $_GET['id'];

pg_query("
    DELETE FROM sources
    WHERE source_id = $id
");

header("Location: $app_root/source_manager.php?node=$node");

?>
