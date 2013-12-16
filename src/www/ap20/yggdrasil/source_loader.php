<?php

/***************************************************************************
 *   source_loader.php                                                     *
 *   Yggdrasil: Source Manager                                             *
 ***************************************************************************/

/*
This script will display possible xml trees to load up sources
Allow display if source already populated an query string=model
*/

// *****************************************************************************
//                               initialization
// *****************************************************************************

require "./settings/settings.php";
require_once "./langs/$language.php";
require "./functions.php";
require "./forms/forms.php";

// var used by header.php to display context dependent menu items
$source_manager = false;
$title = "Source branch load assistant";
require "./header.php";

// *****************************************************************************
//                                   main
// *****************************************************************************

echo "<div class=\"normal\">\n";

if (isset($_POST['modelfile'])) {
    $mf = $_POST['modelfile'];
    $modelfile = basename($mf);
    echo para("you chose $mf ...");
    if (isset($_POST['regenerate'])) {
        echo "<h2>action=regenerate; $webwork/source_init/run.sh:</h2>";
        echo "<pre>";
        echo "shell_exex whoami: [".shell_exec("whoami")."] modelfile[$modelfile]\n";
        $cmd = "$webwork/source_init/run.sh $modelfile";
        echo( "<h4>cmd to run: ".$cmd."</h4>" );
        echo passthru( $cmd, $rc);
        echo "\nrc[$rc] dbname[$dbname] ".$_SERVER['HTTP_HOST'] ."</pre>";
        echo "</pre>";
        if ($rc > 0) {
            echo( "<h4>Something went wrong!</h4>");
            exit;
        }
        echo "<h2>action=regenerate; selected model $mf has /model/sources:</h2>";
        // get saxon in the picture, low freq, just wear the JVM invokation overhead
        echo "<pre>";
        $cmd = "$webwork/bin/saxon_xq.sh $webwork/$mf /model/sources /tmp/mymodel";
        echo( "<h4>cmd to run: ".$cmd."</h4>" );
        echo passthru( $cmd, $rc);
        echo "\nrc[$rc] dbname[$dbname] ".$_SERVER['HTTP_HOST'] ."</pre>";
    }
    if (isset($_POST['apply'])) {
        echo "<h2>TODO: action-apply; use the model to generate entries in the sources tree</h2>";
    }
}
echo "<h2>xslt test START</h2><pre>";
$xslt = new XsltProcessor();
echo "webwork[$webwork]\n";
echo "\$_SERVER[PHP_SELF]=[".$_SERVER['PHP_SELF']."]\n";
echo "</pre><h2>xslt test END</h2>";

echo "<h2>No sources loaded</h2>";
echo para("Choose a model tree to use to populate sources:");

// read files in 'models' directory
$dirname = "models";
$DIR = opendir($dirname);
while($entryName = readdir($DIR)) {
    if ( substr($entryName, 0, 1) != "." && preg_match ( '/\.xml$/', $entryName) ) $dirArray[] = "$dirname/$entryName";
}
closedir($DIR);

//  count elements in array
$indexCount = count($dirArray);
echo ("$indexCount models:<br>\n");

// sort 'em
sort($dirArray);

// print 'em
echo ("<TABLE border=1 cellpadding=5 cellspacing=0 class=whitelinks>\n");
echo ("<TR><TH>Filename</TH><th>Description</th><th>Action</th></TR>\n");
// loop through the array of files and print them all
for($index=0; $index < $indexCount; $index++) {

    // get the desc from the xml
    $model = new SimpleXMLElement($dirArray[$index], null, true);
    $t = $model->xpath('//description/title/text()');
    $desc = $model->xpath('//description/pre/text()');

    echo ("<TR><TD><a href=\"$dirArray[$index]\">$dirArray[$index]</a></td>");

    echo ("<td>");
    echo ("<h4>");
    foreach ($t as $t) { echo ( $t ); }
    echo ("</h4>");
    echo ("<pre>");
    foreach ($desc as $desc) { echo ( $desc ); }
    echo ("<hr/>");
    $generated_model_uri = preg_replace('/^models\//', 'models/generated/out_', $dirArray[$index]);
    $generated_model_html_uri = preg_replace('/^models\/(.+)/', 'models/generated/\1.html', $dirArray[$index]);
    $generated_model_sql_uri = preg_replace('/^models\/(.+)/', 'models/generated/out_sql_\1.txt', $dirArray[$index]);
    echo ("<ul>");
    echo ("<li>Generated (xml): <a href=\"$generated_model_uri\">$generated_model_uri</a></li>");
    echo ("<li>Generated (html): <a href=\"$generated_model_html_uri\">$generated_model_html_uri</a></li>");
    echo ("<li>Generated (sql): <a href=\"$generated_model_sql_uri\">$generated_model_sql_uri</a></li>");
    echo ("</ul>");
    echo ("<hr/>");

    echo "<form id=\"model\" method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">";
    echo "<input type=\"hidden\" name=\"modelfile\" value=\"$dirArray[$index]\" />";
    echo "<input type=\"submit\" name=\"regenerate\" value=\"Regenerate\" />";
    echo "<input type=\"submit\" name=\"apply\" value=\"Apply generated model to database\" />";
    echo ( "</form>" );
    echo ("</td></pre>");

    echo ("<td>");
    echo ("help");
    echo ("</td>");

    print("</TR>\n");
}
print("</TABLE>\n");

echo "</div>\n";
include "./footer.php";
?>
