<?php

/***************************************************************************
 *   relation_delete.php                                                   *
 *   Yggdrasil: Relations Delete Script                                    *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";

$person = $_GET['person'];
$parent = $_GET['parent'];

pg_query("
    DELETE FROM relations
    WHERE child_fk = $person
    AND parent_fk = $parent
");

header("Location: $app_root/family.php?person=$person");

?>
