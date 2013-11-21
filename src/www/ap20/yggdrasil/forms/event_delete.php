<?php

/***************************************************************************
 *   event_delete.php                                                      *
 *   Yggdrasil: Event Delete Action                                        *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// Note: This script will summarily delete an event along with all connected
// participants and event citations. No questions asked.

require "../settings/settings.php";

$person = $_GET['person'];
$event = $_GET['event'];

pg_query("DELETE FROM events WHERE event_id = $event") or die(pg_last_error());

header("Location: $app_root/family.php?person=$person");
?>