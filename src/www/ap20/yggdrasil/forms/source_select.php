<?php

/***************************************************************************
 *   source_select.php                                                     *
 *   Yggdrasil: Source Select                                              *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// This script will update last selected source (LSS).

require "../settings/settings.php";
require "../functions.php";

$person = $_GET['person'];
$source = $_GET['source'];

set_last_selected_source($source);

header("Location: $app_root/family.php?person=$person");

?>
