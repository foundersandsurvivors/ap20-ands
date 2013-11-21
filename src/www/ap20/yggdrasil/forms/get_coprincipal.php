<?php
/***************************************************************************
 *   get_coprincipal.php                                                   *
 *   Yggdrasil: return person id input field + name for dynamic update     *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require "../functions.php";

$person = $_GET['person'];
$event = $_GET['event'];
$event_type = $_GET['event_type'];
if (has_coprincipal($event_type)) {
    $coprincipal = get_second_principal($event, $person);
    echo "Med <input type=\"text\" size=\"10\" value=\"$coprincipal\" ";
    // dynamic AJAX update of source text
    echo "name=\"coprincipal\" onchange=\" get_name(this.value)\">";
    echo "<span id=\"name\">";
    echo ' ' . linked_name($coprincipal, '../family.php');
    echo "</span>\n";
}

?>

