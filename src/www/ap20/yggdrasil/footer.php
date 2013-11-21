<!-- Start Footer -->
<?php

/***************************************************************************
 *   footer.php                                                            *
 *   Yggdrasil: Common Page Footer                                         *
 *                                                                         *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

#require "settings/settings.php";
global $footer_line2,$authuser,$show_delete;

$p_can_delete = ''; 
$p_current_source = 'No current source.';
$p_xml_integration = 'No Xml.';
$p_authfedfas = '';
$now = date("D d M Y H:i:s"); 
//localtime(time(),true);
$time_end = getmicrotime();
$mtime = number_format(($time_end - $time_start),3);
// SMS 20 July 2011: added userid as we don't allow public access and we use apache basic auth
$lastSrc = get_last_selected_source();
# Info for footer
if ($show_delete) { $p_can_delete = "(Can delete unused sources)"; }
if ($lastSrc) { $p_current_source = "Use[$lastSrc]"; }
if ($basex_restxq) { $p_xml_integration = "<br />Xml portal: $basex_restxq"; }
if ($authfedfas) { $p_authfedfas = "In Fas federation. "; }
print ("<p class=\"bluebox\">$_App_name $_This_page_was_generated_in $mtime$_seconds for $authuser $p_can_delete ${now}. $p_current_source $p_authfedfas $p_xml_integration<br />$footer_line2</p>\n");
$loud = 0;
if ( $loud && $administrator[$authuser] ) {
    print "<hr/><pre>Debug for administrator[$authuser]. Global variables:\n";
    print_r ($_SERVER);
    print_r ($_ENV);
    print_r ($GLOBALS);
    print "</pre>";
    $log->debug("settings.php authuser[$authuser] num_rows[$num_rows]" );
}
// for zebra forms (make this conditional??? getting a stray ') e.g. spt_view.php
if ($enable_zebraforms) {
   echo "<script src=\"x/jquery.min.js\"></script>\n";
   echo "<script>window.jQuery || document.write('<script src=\"x/jquery.min.js\"></script>')</script>\n";
   echo "<script src=\"x/zf/public/javascript/zebra_form.js\"></script>\n";
}
echo "</body>\n</html>\n\n";

?>

