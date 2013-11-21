<?php

/***************************************************************************
 *   source_meta_manager.php                                               *
 *   Yggdrasil: Extended logic and functions for metasource handling (ap20)*
 ***************************************************************************/

echo "<hr />";
//echo "<p>...source_meta_manager.php (start): ACTION=".$sdata['ACTION']." stree[".$props['stree']."] sdinfo[";
//var_dump($sdata);
//echo "]</p>";
$supported_objects = file_get_contents("$webwork/metasource_config/supported_objects.txt");
if (!isset($_POST['posted'])) {
    if ( $sdata['ACTION'] == "searchform" ) {
         $form = file_get_contents("$webwork/metasource_config/searchform.".$props['stree']);
         if ($form) {
             echo $form;
         }
         else {
             echo para("..processing searchform nyi [$webwork/metasource_config/searchform.".$props['stree']."]");
         }
    }
    elseif ( $sdata['ACTION'] == "crowdsource" ) {

        // check the configuration
        $errors = 0;

        // find the target source tree containing facsimilies
        if ( preg_match("/^(.+)\.facsimilie$/", $sdata['target'], $spec ) ) {
             $my_object = $spec[1];
             echo "<pre>".$supported_objects."</pre>";
        }
        else {
             $errors++;
             echo para("CONFIGURATION ERROR [$sdata[target]]: crowdsource action requires the entity name of the source which defines a set of facsimilies used to drive the transcription workflow. Please locate that source, note its entity name, and edit this source's structured data with \"target=&amp;entityname\".");
        }

        if (!$errors) {
            echo para("Valid config. source_meta_manager.php show crowdsource form. my_object[$my_object] target=$sdata[target]");
        }
    }
    else {
        echo para("source_meta_manager.php unsupported ACTION: $sdata[ACTION]");
    }
}
else {
    echo para("source_meta_manager.php process form");
}
//echo "<p>source_meta_manager.php end</p>";
echo "<hr />";

?>
