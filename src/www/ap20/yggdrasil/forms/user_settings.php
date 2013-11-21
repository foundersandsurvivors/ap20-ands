<?php

/***************************************************************************
 *   user_settings.php                                                     *
 *   Yggdrasil: User settings Form                                         *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 *   Modifications by S. Silcot University of Melbourne 2012               *
 *   2012-09-13: added validation to prevent sql injection attacks         *
 ***************************************************************************/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

session_start(); 

$log->debug("------------- start: ".$_SERVER['PHP_SELF']." authuser[$authuser]");
if (!isset($_POST['posted'])) {
    $settings = fetch_row_assoc("
        SELECT
            username,
            user_full_name,
            user_email,
            place_filter_level,
            place_filter_content,
            show_delete,
            initials,
            user_lang
        FROM
            user_settings
        WHERE
            username = '".$authuser."'"
    );
    $title = "$_User_settings for " . $settings['username'];
    $log->debug($_SERVER['PHP_SELF']." AUTHENTICATE_UID[".$authuser."] title[$title] settings[".join("|",$settings)."]");
    require "./form_header.php";
    echo "<h2>$title</h2>\n";
    echo "<p class=\"instruct\">Please ensure the following details are correct, then click 'OK'.</p>\n";
    $form = 'user_settings';
    form_begin($form, $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);

    //section: user language
    echo "<tr><td colspan=\"2\"><b>$_Language</b></td></tr>\n";
    echo "<tr><td>$_Language:  </td><td>\n<select name=\"user_lang\">\n";
    echo "<option";
    if ($settings['user_lang'] == 'en')
            echo " selected=\"selected\"";
    echo " value=\"en\">en</option>\n";
    echo "<option";
    if ($settings['user_lang'] == 'nb')
            echo " selected=\"selected\"";
    echo " value=\"nb\">nb</option>\n";
    echo "</select></td></tr><tr><td colspan=\"2\"> </td></tr>\n";

    // section: User details
    echo "<tr><td colspan=\"2\"><b>$_User_details</b></td></tr>\n";
    text_input("$_Full_name:", 40, 'user_full_name', $settings['user_full_name']);
    text_input("$_Email_addr:", 40, 'user_email', $settings['user_email']);
    text_input("$_Initials:", 10, 'initials', $settings['initials']);
    echo "<tr><td colspan=\"2\"> </td></tr>\n";
    
    // Section: place filter settings
    echo "<tr><td colspan=\"2\"><b>$_Place_filter</b></td></tr>\n";
    echo "<tr><td>$_Level:  </td><td>\n<select name=\"place_filter_level\">\n";
    $place_desc = 'desc_' . $language; // desc_en or desc_nb
    $handle = pg_query("
        SELECT
            place_level_name,
            $place_desc
        FROM
            place_level_desc
        ORDER BY place_level_id ASC
    ");
    while ($rec = pg_fetch_assoc($handle)) {
        $option = "<option ";
        if ($rec['place_level_name'] == $settings['place_filter_level'])
            $option .= "selected=\"selected\" ";
        $option .= "value=\"" . $rec['place_level_name'] . "\">" . $rec[$place_desc] . "</option>\n";
        echo $option;
    }
    echo "</select></td></tr>\n";
    text_input("$_Contents:", 10, 'place_filter_content', $settings['place_filter_content']);
    echo "<tr><td colspan=\"2\"> </td></tr>\n";

    form_submit();
    form_end();
    echo "</body>\n</html>\n";
}
else {
    // escape the posted text
    foreach($_POST as $input => $value) { 
        //$log->debug("before input[$input] value[$value]");
        $_POST[$input] = secure($value); 
        //$log->debug("after input[$input] value[".$_POST[$input]."]");
    } 

    // validate before updating

    $errors = 0;
    $err_msg = '';
    if (isset($_POST['user_email'])) {
        $email = filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $log->debug("$email is a valid email address"); 
        } else {
            $log->debug("$email is NOT valid email address"); 
            $errors++;
            $err_msg .= "<p class=\"usererror\">($errors) $_Email_addr [$email] is NOT valid email address.</p>\n";
        }
    }
    if (! filter_var($_POST['user_full_name'],FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[a-zA-Z \'\-]{4,30}$/')))) {
        $errors++;
        $err_msg .= "<p class=\"usererror\">($errors) $_Full_name is a required field (alphanumeric, 4-20 characters).</p>\n";
    }

    if (! filter_var($_POST['initials'],FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[a-zA-Z]{2,3}$/')))) {
        $errors++;
        $err_msg .= "<p class=\"usererror\">($errors) $_Initials [".$_POST['initials']."] is a required field (alphabetic 2-3 characters).</p>\n";
    }

    if ( $errors ) {
         $title = "ERROR Changing your $_User_settings";
         require "./form_header.php";
         echo "<h2>$title</h2>\n";
         echo "$err_msg<p class=\"instruct\">Go back and correct $errors error(s) please.</p>\n";
    }
    else {

        // do update

        $statement = "UPDATE user_settings SET
            user_full_name       = '".$_POST['user_full_name']."',
            user_email           = '".$_POST['user_email']."',
            place_filter_level   = '".$_POST['place_filter_level']."',
            place_filter_content = '".$_POST['place_filter_content']."',
            initials             = '".$_POST['initials']."',
            user_lang            = '".$_POST['user_lang']."'
        WHERE
            username = '$authuser' ";
    
        update_db($log,$authuser,$_SERVER['PHP_SELF'],$statement);

        header("Location: $app_root/index.php");
    }
}

?>
