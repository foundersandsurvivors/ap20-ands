<?php

/***************************************************************************
 *   event_assoc.php                                                       *
 *   Yggdrasil: Event Associate Form                                       *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// this script is unused in the current implementation.

require "../settings/settings.php";
require "../functions.php";

$person = $_GET['person'];
$name = get_name($person);
$title = "Associate Event to Person #$person";

require "./header.php";

echo "<div class=\"normal\">\n";
echo "<h2>Knytt $person $name til hendelse</h2>\n";

echo "<form name=\"insert_event\" method=\"post\" action=\"./event_assoc_ack.php\">\n";
echo "<div><input type=\"hidden\" name=\"person\" value=\"$person\" />\n";
echo "<table>\n";

echo "<tr><td>Hovedperson?</td><td>\n";
echo ("<input type=\"radio\" name=\"is_principal\" checked value=\"t\" /> Ja\n");
echo ("<input type=\"radio\" name=\"is_principal\" value=\"f\" /> Nei\n");
echo "</td></tr>\n";

echo "<tr><td>Hendelses-id: </td><td><input type=\"text\" size=\"25\" name=\"event_id\" /></td></tr>\n";
echo "<tr><td>  </td><td><input type=\"submit\" value=\"Oppdater\" /></td></tr>\n";
echo "</table>\n";
echo "</div>\n";
echo "</form>\n";

echo "</div>\n";
echo "</body></html>\n";
