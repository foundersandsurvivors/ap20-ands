<?php

/***************************************************************************
 *   place_delete.php                                                      *
 *   Yggdrasil: Delete Place Action                                        *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";

$place_id = $_GET['place_id'];
pg_query("
    DELETE FROM places
    WHERE place_id = $place_id
");

header("Location: $app_root/place_manager.php");
?>
