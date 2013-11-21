<?php

/***************************************************************************
 *   reports_local.php (AP20 extensions)                                   *
 *   Yggdrasil: Custom reports for AP20 applications                       *
 ***************************************************************************/

echo "<!-- reports_local.php start -->\n";

echo "<div class=\"normal\">";
echo "<h1>$dbname $title ($pcount distinct persons)</h1>\n";
echo "<h2>Statistics</h2>\n<ul>";

$pcount_a = fetch_val("SELECT COUNT(*) FROM persons");
$pcount_m = fetch_val("SELECT COUNT(*) FROM merged");
echo li("$pcount_m merged persons (equivalent to LKT LINK) in total of $pcount_a person records.");

$pcount_r = fetch_val("SELECT COUNT(*) FROM relations");
echo li("$pcount_r relationships (person-person).");

$pcount_s = fetch_val("SELECT COUNT(*) FROM sources");
$pcount_sb = fetch_val("SELECT COUNT(*) FROM sources where parent_id = 0");
$pcount_t = fetch_val("SELECT COUNT(*) FROM templates");
echo li("$pcount_s sources, $pcount_t with templates. $pcount_sb top level branches.");

echo "</ul>\n";

echo file_get_contents ("./reports/overnight_index_$dbname");
echo "<p></p></div>\n";

echo "<!-- reports_local.php end -->\n";

?>

