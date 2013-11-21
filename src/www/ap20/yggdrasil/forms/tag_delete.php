<?php

/***************************************************************************
 *   tag_delete.php                                                        *
 *   Yggdrasil: Delete Tag                                                 *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// This script will delete a tag. It is callable from tag_manager.php
// iff there are no associated events.

require "../settings/settings.php";
require "../functions.php";

$tag = $_GET['tag'];

pg_query("
    DELETE FROM tags
    WHERE tag_id = $tag
");

header("Location: $app_root/tag_manager.php");

?>
