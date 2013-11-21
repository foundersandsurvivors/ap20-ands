<?php

/***************************************************************************
 *   source_toggle_sd.php                                                  *
 *   Yggdrasil: Toggle show delete link for unused sources                 *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require "../functions.php";

$node = $_GET['node'];

if (fetch_val("SELECT show_delete FROM user_settings WHERE username = current_user") == 'f')
    pg_query("
        UPDATE user_settings
        SET show_delete = TRUE
        WHERE username = current_user
    ");
else
    pg_query("
        UPDATE user_settings
        SET show_delete = FALSE
        WHERE username = current_user
    ");

header("Location: $app_root/source_manager.php?node=$node");
?>
