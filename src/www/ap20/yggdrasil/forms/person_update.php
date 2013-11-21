<?php

/***************************************************************************
 *   person_update.php                                                     *
 *   Yggdrasil: Update Persons Form and Action                             *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "../settings/settings.php";
require_once "../langs/$language.php";
require "../functions.php";
require "./forms.php";

if (!isset($_POST['posted'])) {
    $person = $_GET['person'];
    $title = "$_Edit person $person";
    require "./form_header.php";
    echo "<h2>$title</h2>\n";
    $row = fetch_row_assoc("SELECT * FROM persons WHERE person_id = $person");
    $gender = $row['gender'];
    $given = $row['given'];
    $patronym = $row['patronym'];
    $toponym = $row['toponym'];
    $surname = $row['surname'];
    $occupation = $row['occupation'];
    $epithet = $row['epithet'];
    form_begin('person_insert', $_SERVER['PHP_SELF']);
    hidden_input('posted', 1);
    hidden_input('person', $person);
    radio_gender($gender);
    text_input("$_Given:", 50, 'given', $given);
    text_input("$_Patronym:", 50, 'patronym', $patronym);
    text_input("$_Toponym:", 50, 'toponym', $toponym);
    text_input("$_Surname:", 50, 'surname', $surname);
    text_input("$_Occupation:", 50, 'occupation', $occupation);
    text_input("$_Epithet:", 50, 'epithet', $epithet);
    form_submit();
    form_end();
    help_local_file('action','person_update');
    echo "</body>\n</html>\n";
}
else {
    $person = $_POST['person'];
    $gender = $_POST['gender'];
    $given = $_POST['given'];
    $patronym = $_POST['patronym'];
    $toponym = $_POST['toponym'];
    $surname = $_POST['surname'];
    $occupation = $_POST['occupation'];
    $epithet = $_POST['epithet'];
    pg_query("
        UPDATE
            persons
        SET
            last_edit = NOW(),
            gender = $gender,
            given = '$given',
            patronym = '$patronym',
            toponym = '$toponym',
            surname = '$surname',
            occupation = '$occupation',
            epithet = '$epithet'
        WHERE
            person_id = $person
    ");
    header("Location: $app_root/family.php?person=$person");
}

?>
