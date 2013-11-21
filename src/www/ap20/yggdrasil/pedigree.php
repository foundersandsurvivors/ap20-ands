<?php

/***************************************************************************
 *   pedigree.php                                                          *
 *   Yggdrasil: Pedigree Chart                                             *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/* Mods sms: 2012-10-05 remove &nbsp; its not valid html */

require "./settings/settings.php";
require_once "./langs/$language.php"; 
require "./functions.php";
$person = $_GET["person"];
$name = get_name($person);

function get_sidebar_name($p) {
    $name = get_name($p);
    $name .= has_descendants($p) ? '<span title="Has descendants">+</span>' : '';
    return to_url('./pedigree.php', array('person' => $p), $name);
}

function childbox($p) {
    global $_Child; /* In PHP global variables must be declared global inside a function if used in that function */
    $query = "
      SELECT
          child_fk,
          get_pbdate(child_fk) as pb_date
      FROM
          relations
      WHERE
          parent_fk = $p
      ORDER BY
          pb_date
    ";
    $handle = pg_query($query);
    $num_rows = pg_num_rows ($handle);
    if ($num_rows) {
        echo "<!-- list children of proband -->\n"
            . "<table class=\"nav\">\n"
            . "<tr><td class=\"linkbox\">$_Child:</td></tr>\n";
        while($row = pg_fetch_row($handle)) {
            echo '<tr><td class="nav">'
                . get_sidebar_name($row[0]) . "</td></tr>\n";
        }
        echo "</table>\n";
    }
}

function spousebox($p) {
    global $_Spouse; 
    $query = "
        SELECT
            p2,
            sort_date
        FROM
            couples
        WHERE
            p1 = $p
        ORDER BY
            sort_date
    ";
    $handle = pg_query($query);
    $num_rows = pg_num_rows ($handle);
    if ($num_rows) {
        echo "<!-- list spouses of proband -->\n"
            . "<table class=\"nav\">\n"
            . "<tr><td class=\"linkbox\">$_Spouse:</td></tr>\n";
        while($row = pg_fetch_row($handle)) {
            echo '<tr><td class="nav">'
                . get_sidebar_name($row[0]) . "</td></tr>\n";
        }
        echo "</table>\n";
    }
}

/* future extension: dashed lines to uncertain parents

function get_father_with_surety($p) {
    if ($row = fetch_row_assoc("
            SELECT
                parent_fk,
                surety_fk
            FROM
                relations
            WHERE
                child_fk = $p
            AND
                get_gender(parent_fk) = 1
        ")) {
        $par[0] = $row['parent_fk'];
        $par[1] = $row['surety_fk'];
    } else
        $par[0] = 0;
        $par[1] = 0;
    }
    return $par;
}

function get_mother_with_surety($p) {
    if ($row = fetch_row_assoc("
            SELECT
                parent_fk,
                surety_fk
            FROM
                relations
            WHERE
                child_fk = $p
            AND
                get_gender(parent_fk) = 2
        ")) {
        $par[0] = $row['parent_fk'];
        $par[1] = $row['surety_fk'];
    } else
        $par[0] = 0;
        $par[1] = 0;
    }
    return $par;
}
*/

// "When in doubt, use brute force" -- Ken Thompson
// There are undoubtedly more elegant algorithms for filling out a pedigree.
// But as we're only doing a four generation tree, and any person can have
// only 2 parents, an array of 32 elements is sufficient for all of them.
// Doing anything more sophisticated is probably a wild goose chase.

function build_tree($p) {
    // The indices of the tree array are plain Sosa-Stradonitz numbers.
    // The proband is number 1. The father of any person P in the pedigree
    // has index P * 2, the mother of P has P * 2 + 1. Missing persons are
    // assigned a 0.
    $tree_array[1] = $p;
    // hunt down ancestors
    for ($i = 1; $i < 16; $i++) {
        if ($tree_array[$i])
            $tree_array[$i * 2] = find_father($tree_array[$i]);
        else $tree_array[$i * 2] = 0;
        if ($tree_array[$i])
            $tree_array[$i * 2 + 1] = find_mother($tree_array[$i]);
        else $tree_array[$i * 2 + 1] = 0;
    }
    // populate name array: 
    $name[1] = get_name_and_dates('./family.php', $p);
    for ($i = 2; $i < 32; $i++) {
        if ($tree_array[$i])
            $name[$i] = get_name_and_dates('', $tree_array[$i]);
        else
            $name[$i] = '';
    }
    // if a gggparent has registered ancestors, append right arrow
    for ($i = 16; $i < 32; $i++) {
        if (has_parents($tree_array[$i]))
            $name[$i] .= "</td><td rowspan=\"2\"><img src=\"./graphics/arr_rt.gif\" alt=\"\" />";
    }

    if (get_gender($p) == 1)
        $pcolor = 'blue';
    elseif (get_gender($p) == 2)
        $pcolor = 'red';
    else $pcolor = 'green';

    // The following vars are space-saving abbreviations for the matrix below.
    // Each line in the matrix, except for the proband, occupies *two* table rows.
    // The reason is of course that the lines and angles are done with pure CSS.
    // Even if it's actually validating with W3C, the semantic fundamentalists
    // will hold their noses over such blatant table abuse. I may consider
    // rewriting it for SVG when IE supports it out of the box, and all earlier
    // versions of IE have gone to bit heaven. (I'm probably pushing up daisies
    // myself before that happens.) Until then, this format is understood by all
    // browsers more recent than Netscape 4. It's even (sort of) working in Lynx.

    // two empty cells
    $ec = "<td> </td><td> </td>";
    // four empty cells in a two-by-two block
    $eb = "<td rowspan=\"2\" colspan=\"2\"> </td>";
    // a red box for a female ancestor
    $red = "<td rowspan=\"2\" colspan=\"2\" class=\"red\">";
    // a blue box for a male ancestor
    $blue = "<td rowspan=\"2\" colspan=\"2\" class=\"blue\">";
    // a box for the proband
    $proband = "<td colspan=\"2\" class=\"$pcolor\">";
    // the upper angle to a father box
    $tf = "<td> </td><td class=\"tofath\"> </td>";
    // the lower angle to a mother box
    $tm = "<td> </td><td class=\"tomoth\"> </td>";
    // vertical line
    $vl = "<td> </td><td class=\"vline\"> </td>";
    // table row end and newline
    $nl = "</tr>\n<tr>";

    // The Matrix From Hell. Don't mess with it. Note that every line in
    // the matrix is conditional, no need to print empty boxes.
    echo "<table cellspacing=\"0\"><!-- the pedigree monster table -->\n";
    if ($name[16])
        echo "<tr>$eb$eb$eb$ec$blue 16 $name[16]</td>$nl$tf</tr>\n";
    if ($name[8])
        echo "<tr>$eb$eb$ec$blue 8 $name[8]</td>$eb$nl$tf</tr>\n";
    if ($name[17])
        echo "<tr>$eb$eb$vl$tm$red 17 $name[17]</td>$nl$vl$ec</tr>\n";
    if ($name[4])
        echo "<tr>$eb$ec$blue 4 $name[4]</td>$eb$eb$nl$tf</tr>\n";
    if ($name[18])
        echo "<tr>$eb$vl$vl$ec$blue 18 $name[18]</td>$nl$vl$vl$tf</tr>\n";
    if ($name[9])
        echo "<tr>$eb$vl$tm$red 9 $name[9]</td>$eb$nl$vl$ec</tr>\n";
    if ($name[19])
        echo "<tr>$eb$vl$eb$tm$red 19 $name[19]</td>$nl$vl$ec</tr>\n";
    if ($name[2])
        echo "<tr>$ec$blue 2 $name[2]</td>$eb$eb$eb$nl$tf</tr>\n";
    if ($name[20])
        echo "<tr>$vl$vl$eb$ec$blue 20 $name[20]</td>$nl$vl$vl$tf</tr>\n";
    if ($name[10])
        echo "<tr>$vl$vl$ec$blue 10 $name[10]</td>$eb$nl$vl$vl$tf</tr>\n";
    if ($name[21])
        echo "<tr>$vl$vl$vl$tm$red 21 $name[21]</td>$nl$vl$vl$vl$ec </tr>\n";
    if ($name[5])
        echo "<tr>$vl$tm$red 5 $name[5]</td>$eb$eb$nl$vl$ec</tr>\n";
    if ($name[22])
        echo "<tr>$vl$eb$vl$ec$blue 22 $name[22]</td>$nl$vl$vl$tf</tr>\n";
    if ($name[11])
        echo "<tr>$vl$eb$tm$red 11 $name[11]</td>$eb$nl$vl$ec</tr>\n";
    if ($name[23])
        echo "<tr>$vl$eb$eb$tm$red 23 $name[23]</td>$nl$vl$ec</tr>\n";

    echo "<tr>$proband 1 $name[1]</td>$ec$ec$ec$ec</tr>\n";

    if ($name[24])
        echo "<tr>$vl$eb$eb$ec$blue 24 $name[24]</td>$nl$vl$tf</tr>";
    if ($name[12])
        echo "<tr>$vl$eb$ec$blue 12 $name[12]</td>$eb$nl$vl$tf</tr>\n";
    if ($name[25])
        echo "<tr>$vl$eb$vl$tm$red 25 $name[25]</td>$nl$vl$vl$ec</tr>\n";
    if ($name[6])
        echo "<tr>$vl$ec$blue 6 $name[6]</td>$eb$eb$nl$vl$tf</tr>\n";
    if ($name[26])
        echo "<tr>$vl$vl$vl$ec$blue 26 $name[26]</td>$nl$vl$vl$vl$tf</tr>\n";
    if ($name[13])
        echo "<tr>$vl$vl$tm$red 13 $name[13]</td>$eb$nl$vl$vl$ec</tr>\n";
    if ($name[27])
        echo "<tr>$vl$vl$eb$tm$red 27 $name[27]</td>$nl$vl$vl$ec</tr>\n";
    if ($name[3])
        echo "<tr>$tm$red 3 $name[3]</td>$eb$eb$eb$nl$ec</tr>\n";
    if ($name[28])
        echo "<tr>$eb$vl$eb$ec$blue 28 $name[28]</td>$nl$vl$tf</tr>\n";
    if ($name[14])
        echo "<tr>$eb$vl$ec$blue 14 $name[14]</td>$eb$nl$vl$tf</tr>\n";
    if ($name[29])
        echo "<tr>$eb$vl$vl$tm$red 29 $name[29]</td>$nl$vl$vl$ec</tr>\n";
    if ($name[7])
        echo "<tr>$eb$tm$red 7 $name[7]</td>$eb$eb$nl$ec</tr>\n";
    if ($name[30])
        echo "<tr>$eb$eb$vl$ec$blue 30 $name[30]</td>$nl$vl$tf</tr>\n";
    if ($name[15])
        echo "<tr>$eb$eb$tm$red 15 $name[15]</td>$eb$nl$ec</tr>\n";
    if ($name[31])
        echo "<tr>$eb$eb$eb$tm$red 31 $name[31]</td>$nl$ec</tr>\n";
    echo "</table>\n";
}

// *****************************************************************************
// ***                           MAIN PROGRAM                                ***
// *****************************************************************************

$pedigree = true;
$title = "$person $name, $_ancestors";
require "./header.php";
spousebox($person); 
childbox($person); 
echo '<div class="normal">';
echo "<h2>$_Pedigree_for $name</h2>\n";
build_tree($person);
echo "</div>\n";
include "./footer.php";
?>
