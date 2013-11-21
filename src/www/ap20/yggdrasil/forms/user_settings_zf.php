<?php

/***************************************************************************
 *   user_settings.php                                                     *
 *   Yggdrasil: User settings Form                                         *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/
/*
    Modifications by S. Silcot University of Melbourne 2012              
    2012-09-16: Zebra forms for ajax
    2012-09-13: added validation to prevent sql injection attacks      
*/ 

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
    $log->debug($_SERVER['PHP_SELF']." authuser[$authuser] title[$title] settings[".join("|",$settings)."]");
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

    echo "<hr/>";

    /* 
       =====================================
       Zebra forms/ajax version of this form. For api documentation see:
       http://stefangabos.ro/wp-content/docs/Zebra_Form/Generic/Zebra_Form_Control.html
       =====================================
    */

    // instantiate a Zebra_Form object
    $form = new Zebra_Form('form');
    echo "<p>Experimental user settings form using client-side validation (Zebra forms):</p>";
    //$form->add('label', 'label_name', 'head1', "User details");

    // "language" element
    $form->add('label', 'label_user_lang', 'user_lang', "$_Language:");
/*

    If we were to support extra languages, use a selection menu:

    $obj = & $form->add('select', 'user_lang', $settings['user_lang'] ); // array('other' => true)
    $obj->add_options(array( 'en', 'nb',));
    $obj->set_rule(array(
        'required' => array('error', "$_Language is required!")
    ));
*/
    $obj = & $form->add('radios', 'user_lang', array( 'en'=>'English', 'nb'=>'Norwegian' ), $settings['user_lang'] );
    $form->add('note', 'note_user_lang', 'user_lang', 'Only these two languages are supported at this time');
    

    // the "name" element
    $form->add('label', 'label_name', 'user_full_name', "$_Full_name:");
    $obj = & $form->add('text', 'user_full_name', $settings['user_full_name']);
    $obj->set_rule(array(
        'required' => array('error', 'Name is required!'),
        'alphabet' => array('- \'', 'error', 'Value must be alphabetic plus hyphen'),
        'length' => array(5,40,'error','Value must be 5 to 40 characters')
    ));
    // add a note to this control
    $form->add('note', 'note_user_full_name', 'user_full_name', 'Enter your full name as forename surname');


    // "email"
    $form->add('label', 'label_email', 'user_email', "$_Email_addr:");
    $obj = & $form->add('text', 'user_email', $settings['user_email']);
    $obj->set_rule(array(
        'required'  =>  array('error', "$_Email_addr is required!"),
        'email'     =>  array('error', "$_Email_addr seems to be invalid!"),
        'length' => array(5,40,'error','Value must be no more than 40 characters')
    ));

    // the "initials" element
    $form->add('label', 'label_initials', 'initials', "$_Initials:");
    $obj = & $form->add('text', 'initials', $settings['initials']);
    $obj->set_rule(array(
        'required' => array('error', 'Initials are required!'),
        'alphabet' => array('', 'error', 'Value must be alphabetic'),
        'length' => array(2,3,'error','Value must be 2-3 characters')
    ));

    /*
    ----------------------------------------------------------------
    config_ Fixed values for now for Place filter level and contents
            To be updated when structure of gazeteer is known.
    ----------------------------------------------------------------
    */

    // "place_filter_level" element
    $form->add('label', 'label_place_filter_level', 'place_filter_level', "$_Place_filter:");
    $obj = & $form->add('select', 'place_filter_level', $settings['place_filter_level'] ); 
    // config_ : todo when gazette data structure finalised or read from database as above
    $obj->add_options(array( 'level_1'=>'Detail',
                             'level_2'=>'City',
                             'level_3'=>'County',
                             'level_4'=>'State',
                             'level_5'=>'Country')); 
    $obj->set_rule(array(
        'required' => array('error', "$_Place_filter is required!")
    ));
    $form->add('note', 'note_place_filter_level', 'place_filter_level', 'To be finalised when gazeteer structure determined');

    // the "place_filter_content" element
    $form->add('label', 'label_place_filter_content', 'place_filter_content', "Place filter $_Contents:");
    $obj = & $form->add('text', 'place_filter_content', $settings['place_filter_content']);
    $obj->set_rule(array(
        'required' => array('error', "Place filter $_Contents are required!"),
        'regexp' => array('^\%$', 'error', 'Value must be "%" (to be implemented when gazeteer data structure known)'),
    ));
    $form->add('note', 'note_place_filter_contents', 'place_filter_contents', 'To be finalised when gazeteer structure determined');

    $form->add('submit', 'btnsubmit', 'Submit');
    $obj = &$form->add( 'reset', 'my_reset', 'Reset', array( 'alt' => 'Click to reset values'));
    
    // if the form is valid
    if ($form->validate()) {

        // show results
        process_results();
        //header("Location: $app_root/index.php");
        echo "<p><a href=\"$app_root/index.php\">Back to index page.</a></p>";

    // otherwise
    } else

        // generate output using a custom template
        $form->render('*horizontal');


    echo "<hr /><p>The second form is experimental using Zebra forms <a href=\"http://stefangabos.ro/php-libraries/zebra-form/\">http://stefangabos.ro/php-libraries/zebra-form/</a>.</p>";

    // for zebra forms 
    echo "<script src=\"../x/jquery.min.js\"></script>\n";
    echo "<script>window.jQuery || document.write('<script src=\"../x/jquery.min.js\"><\/script>')</script>\n";
    echo "<script src=\"../x/zf/public/javascript/zebra_form.js\"></script>\n";

    echo "</body>\n</html>\n";

}
else {
    // ************************************************************** old method ************************************
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

function process_results() {

    /* Update the database for new user settings. Assume the data is valid, but secure it from injection attacks. */

    global $authuser, $log, $settings;

    $db_fields = array('user_full_name','user_email','place_filter_level','place_filter_content','initials','user_lang');
    echo "<p>show_results called: the form was valid</p>";
    echo "<pre>";
    $changed = 0;
    foreach($db_fields as $input) {
        $_POST[$input] = secure($_POST[$input]);
        if ( $_POST[$input] != $settings[$input] ) { $changed++; }
        echo "$input = [$_POST[$input]] changed[$changed] on_db[".$settings[$input]."]]\n";
        $log->debug("zf after validation: input[$input] value[".$_POST[$input]."]");
    }
    
    // make the changes
    $statement = "UPDATE user_settings SET
            user_full_name       = '".$_POST['user_full_name']."',
            user_email           = '".$_POST['user_email']."',
            place_filter_level   = '".$_POST['place_filter_level']."',
            place_filter_content = '".$_POST['place_filter_content']."',
            initials             = '".$_POST['initials']."',
            user_lang            = '".$_POST['user_lang']."'
        WHERE
            username = '$authuser' ";

    echo $statement;

    if ($changed) {
        update_db($log,$authuser,$_SERVER['PHP_SELF'],$statement);
    }
    else {
        echo "\nNO CHANGES. Database unchanged.\n";
    }
    echo "</pre>";
    1;
}

?>
