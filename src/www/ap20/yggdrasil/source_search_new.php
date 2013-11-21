<?php

/***************************************************************************
 *   source_search_new.php                                                 *
 *   Yggdrasil: Search for sources                                         *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/* sms: branch filtered searches */

require "./settings/settings.php";
require_once "./langs/$language.php";

session_start();

// we'll display only raw dates here
if ($ui_mode = "expert") { pg_query("SET DATESTYLE TO GERMAN"); }

$title = "$_Search_for_sources NEW/Experimental";
$form = 'source';
$focus = 'src';

require "./functions.php";
require "./header.php";

echo "<div class=\"normal\">";
echo "<h2>$title</h2>\n";


// NOTE: The following function and paragraph is a preliminary implementation.
// The regexp replace terms are data rather than code, and should be moved into
// the database, see blog post http://solumslekt.org/blog/?p=151

function src_expand($s) {
    // regexp expansion of 'canonical' Norwegian names to catch variant forms.
    // NOTE: if eg. 'Tor' precedes 'Torsten', the 'Tor' part will be expanded,
    // and the string 'Torsten' is lost.
    $s = str_replace('Albert',    'Ah?lb[ceghir]+th?', $s);
    $s = str_replace('Anders',    'An+[ader]+[sz]+', $s);
    $s = str_replace('Anne',      'An+[aeæ]+', $s);
    $s = str_replace('Alet',      'A[dehl]+i?[dts]+[eh]*', $s);
    $s = str_replace('Amund',     'Aa?[mn]+[ou]+nd?', $s);
    $s = str_replace('Arnold',    'Ah?re?n+h?[aou]+l+[dfhtvw]*', $s);
    $s = str_replace('Aslak',     'As[ch]*la[cghk]+', $s);
    $s = str_replace('Auen',      '(A|O)u?[dgvw]+[eu]+n?', $s);
    $s = str_replace('Berte',     'B[eiø]+r[gi]*t+h?[ae]*', $s);
    $s = str_replace('Bjørn',     'B[ij]+ø[ehr]*n', $s);
    $s = str_replace('Boel',      'Bod?[ei]+ld?e?', $s);
    $s = str_replace('Brynil',    'Br[ouyø]+n+[eijuy]+l[dvf]*', $s);
    $s = str_replace('Bærulf',    'B[eæ]+r[ou]+l[dfvw]*', $s);
    $s = str_replace('Børge',     'B[eijoø]+r+[aegij]+r?', $s);
    $s = str_replace('Carl',      '(C|K)arl+', $s);
    $s = str_replace('Catrine',   '(C|K)h?ath?a?rin[ae]+', $s);
    $s = str_replace('Claus',     '(C|K)la[eu]*s', $s);
    $s = str_replace('Daniel',    'Dah?n+ie?l+d?', $s);
    $s = str_replace('David',     'Da[fuvw]+id', $s);
    $s = str_replace('Dorte',     'D(aa|o)ro?t+h?[ea]*', $s);
    $s = str_replace('Eilert',    'E[hijy]*l+ert?h?', $s);
    $s = str_replace('Einar',     'E[hijy]*n+[ae]+r', $s);
    $s = str_replace('Elin',      'El+[eij]+n?', $s);
    $s = str_replace('Ellef',     'El+[ei]+[fvw]+', $s);
    $s = str_replace('Engebret',  '(I|E)ng[el]+b[ceghir]+th?', $s);
    $s = str_replace('Erik',      'Er[ei]+[chk]+', $s);
    $s = str_replace('Even',      'Ei?[vw]+[ei]+nd?', $s);
    $s = str_replace('Fredrik',   'Fr[ei]+dri[chk]+', $s);
    $s = str_replace('Gaute',     'G[ahou]+[dt]+h?[ei]+', $s);
    $s = str_replace('Gjermund',  '(G|J)[ehij]+rm[ou]+nd?', $s);
    $s = str_replace('Gjertrud',  '(G|J)[ehij]+rd?th?ru[de]*', $s);
    $s = str_replace('Gjert',     '(G|J)[eij]+rd?th?', $s);
    $s = str_replace('Gjest',     '(G|J)[eijou]+s+(th?|e)', $s);
    $s = str_replace('Gudmund',   'Gu+[lmnd]+und?', $s);
    $s = str_replace('Gullik',    'Gun?l+[ei]+[chk]+', $s);
    $s = str_replace('Gunder',    'G[ouø]+n+d?[ae]+r', $s);
    $s = str_replace('Gunhild',   'G[ouø]+n+h?[ei]+l+d?[ae]*', $s);
    $s = str_replace('Halvor',    'H[ao]+l*[fuvw]+[aeo]+r+d?', $s);
    $s = str_replace('Håvald',    '[AHO]+[ao]*[vw]+[ao]+[lr]+d?', $s);
    $s = str_replace('Ingeborg',  '[EIJ]+e?ngeb[aijoø]r+[ghe]*', $s);
    $s = str_replace('Isak',      'Isa+[chk]+', $s);
    $s = str_replace('Jon',       'Jo[eh]*n', $s);
    $s = str_replace('Johan',     'Jo[aeh]*n+[eis]*', $s);
    $s = str_replace('Kari',      'Kar[ein]+', $s);
    $s = str_replace('Kirsti',    '(Ch?|K)[ij]+e?r?sth?[ein]+', $s);
    $s = str_replace('Kjøstol',   '(Th?|K)[iouyø]+r?st[aeou]+l+[dfhpvw]*', $s);
    $s = str_replace('Knut',      '(C|K)nu+[dt]+', $s);
    $s = str_replace('Lars',      'La[eu]*r[idt]*[sz]+', $s);
    $s = str_replace('Levor',     'Le+d?[vw]+[aeo]+r?d?', $s);
    $s = str_replace('Lisbet',    '(El|L)+i[sz]+a?[bp]+e[dht]+', $s);
    $s = str_replace('Lorens',    'L[ao]+[uvw]*r[ae]+n[tsz]+', $s);
    $s = str_replace('Mads',      'Ma[dht]*[aeiuæ]*[sz]+', $s);
    $s = str_replace('Malene',    'M[adeghir]+l+[ei]+n+[ae]*', $s);
    $s = str_replace('Margrete',  '(Gr?|Mar?g?)a?r?[ei]+t+h?[ae]*', $s);
    $s = str_replace('Mari',      'Mar[aein]+', $s);
    $s = str_replace('Mette',     'Met+h?e', $s);
    $s = str_replace('Mikkel',    'Mi[chk]+[ae]+l+', $s);
    $s = str_replace('Mons',      'Mog?e?n[dstz]+', $s);
    $s = str_replace('Nils',      'Nie?l+s', $s);
    $s = str_replace('Peder',     'P[det]+r', $s);
    $s = str_replace('Paul',      'P[aeouvw]+l+', $s);
    $s = str_replace('Rolf',      'R[oø]+l+[eouø]*[vwf]+', $s);
    $s = str_replace('Sissel',    '[CSZ]+[eiæ]+[dt]*[csz]+[ei]+l+[aei]*d?', $s);
    $s = str_replace('Siver',     'S[iy]+[gjvw]+[aeu]+[lr]+[ht]*', $s);
    $s = str_replace('Sofie',     'So[fhp]+[ij]+[aeæ]*', $s);
    $s = str_replace('Steffen',   'Ste[fhp]+[ae]+n', $s);
    $s = str_replace('Synnøve',   'S[eiouyø]+n+[aeiouyø]+[fhvw]*[ae]*', $s);
    $s = str_replace('Søren',     'Søf*ren', $s);
    $s = str_replace('Tallak',    'Th?[ao]+l+a[chgk]+', $s);
    $s = str_replace('Tollef',    'Th?[eoø]+l+[eouø]+[vwf]+', $s);
    $s = str_replace('Tomas',     'Th?om+[ae]+s', $s);
    $s = str_replace('Torbjørn',  'Th?oe?rb[ij]+ør?n', $s);
    $s = str_replace('Torger',    'Th?or[egiju]+[rs]+', $s);
    $s = str_replace('Torkil',    'Th?[eoø]+r[chk]+[ie]+l+d?', $s);
    $s = str_replace('Tormod',    'Th?ormo[de]*', $s);
    $s = str_replace('Torsten',   'Th?(aa|o)r?ste+n', $s);
    $s = str_replace('Tor',       'Th?o[der]+', $s);
    $s = str_replace('Tov',       'Th?o[fuvw]+', $s);
    $s = str_replace('Trond',     'Th?roe?nd?', $s);
    $s = str_replace('Tyge',      'Th?[yø]+[chgk]+[ei]+r?', $s);
    $s = str_replace('Vrål',      '(V|W)r(aa|o)[eh]*l+d?', $s);
    $s = str_replace('Wilhelm',   '(V|W)[ei]+l+[ehlou]+m', $s);
    $s = str_replace('Zacharias', '(S|Z)a[chk]+[airs]+', $s);
    $s = str_replace('Åge',       '(Aa|O)[cghk]+[ei]+', $s);
    $s = str_replace('Åse',       '(Aa|O)ste+', $s);
    $s = str_replace('Åshild',    '(Aa|O)r?s+h?[ei]+l+[de]*', $s);
    $s = str_replace('Åsold',     '(Aa|O)s+[eou]+l+[dfvw]*', $s);
    $s = str_replace('Åvet',      '(Aa?|O)[gvw]+[aeio]+[dht]+[ae]*', $s);
    return $s;
}

if ($language == 'nb') echo
"<p>Regulære søkeuttrykk er definert for:<br />
Albert, Alet, Anders, Anne, Amund, Arnold, Aslak, Auen, Berte, Bjørn, Boel,
Brynil, Bærulf, Børge, Carl, Catrine, Claus, Daniel, David, Dorte, Eilert,
Einar, Elin, Ellef, Engebret, Erik, Even, Fredrik, Gaute, Gjermund, Gjert,
Gjertrud, Gjest, Gudmund, Gullik, Gunder, Gunhild, Halvor, Håvald, Ingeborg,
Isak, Jon, Johan(nes), Kari, Kirsti, Kjøstol, Knut, Lars, Ledvor, Lisbet,
Lorens, Mads, Malene, Margrete, Mari, Mette, Mikkel, Mons, Nils, Peder, Paul,
Rolf, Sissel, Siver, Sofie, Steffen, Synnøve, Søren, Tallak, Tollef, Tomas, Tor,
Torbjørn, Torger, Torkil, Tormod, Torsten, Tov, Trond, Tyge, Vrål, Wilhelm,
Zacharias, Åge, Åse, Åshild, Åsold, Åvet.</p>\n";

$search_scope = 0; // sms: default to full, otherwise see what it was
$search_src = '';
$search_filter = '';
if (isset($_GET['src'])) {
    $search_scope = $_GET['scope'];
    $search_src = $_GET['src'];
    $search_filter = $_GET['filter'];
}

echo para("\"$_Text\" will be used to search source leafs i.e. transcripts (required).  "
        . "\"Branch\" will be used to match the source branch text (optional). " 
        . "The menu will constrain which kind of leaf sources are searched.");


echo "<form id=\"$form\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n<div>\n";
echo "$_Text: <input type=\"text\" size=\"40\" name=\"src\" value=\"$search_src\"/>\n";
echo "Branch: <input type=\"text\" size=\"12\" name=\"filter\" value=\"$search_filter\"/>\n";
echo "<select name=\"scope\">";
$label = 'label_' . $language;
$handle = pg_query("
    SELECT
        part_type_id,
        $label,
        part_type_count(part_type_id) AS tc
    FROM
        source_part_types
    WHERE
        is_leaf IS TRUE
    ORDER BY
        tc DESC,
        part_type_id ASC
");
// sms: save what was there
if ( $search_scope == 0 ) {
    echo '<option selected="selected" value="0">Full</option>';
}
else {
    echo '<option value="0">Full</option>';
}
while ($rec = pg_fetch_assoc($handle)) {
    $option = "<option ";
    if ($rec['part_type_id'] == $search_scope)
        $option .= "selected=\"selected\" ";
    $option .= "value=\"" . $rec['part_type_id'] . "\">" . $rec[$label] . "</option>\n";
    echo $option;
}
echo "</select></td></tr>\n"
    . "<input type=\"submit\" value=\"$_Search\" />\n"
    . "</div>\n</form>\n\n";

// ----------------------------------------- sms mods for smart bdm search/lookup
if ( $bdm_source_search && $authfedfas) {
    echo "<hr/>";
    // instantiate a Zebra_Form object
    $form = new Zebra_Form('form');
    echo "<p>Experimental BDM source search</p>";
    // Custom form for selecting jurisdiction, year, and certificate/reference number
    // of a BDM certificate in Australia

    // certificate type
    $form->add('label', 'label_bdm_type', 'bdm_type', "Certificate type:");
    $obj = & $form->add('select', 'bdm_type', 'Death Certificates' );
    $obj->add_options(array( 'Birth Certificates'=>'Birth Certificates','Death Certificates'=>'Death Certificates','Marriage Certificates'=>'Marriage Certificates'));
    $obj->set_rule(array( 'required' => array('error', "Type is required!")));

    // jurisdiction
    $form->add('label', 'label_bdm_jurisdiction', 'bdm_jurisdiction', "State:");
    $obj = & $form->add('select', 'bdm_jurisdiction', 'NSW' );
    $obj->add_options(array( 'NSW'=>'NSW','VIC'=>'VIC','QLD'=>'QLD','WA'=>'WA','SA'=>'SA','TAS'=>'TAS','ACT'=>'ACT','NT'=>'NT','OTHER'=>'OTHER'));
    $obj->set_rule(array( 'required' => array('error', "State is required!")));

    // year
    $form->add('label', 'label_bdm_year', 'bdm_year', "Year:");
    $obj = & $form->add('text', 'bdm_year');
    $obj->set_rule(array(
        'required' => array('error', 'Year is required!'),
        'digits' => array('', 'error', 'Value must be numeric'),
        'length' => array(4,4,'error','Value must be 4 digits'),
        'regexp' => array('^(18)|(19)|(20)$', 'error', 'Year must be sensible.'),
    ));

    // certificate/regno
    $form->add('label', 'label_certno', 'certno', "Certificate No:");
    $obj = & $form->add('text', 'certno');
    $obj->set_rule(array(
        'required' => array('error', 'Certificate No is required!'),
        'digits' => array('', 'error', 'Value must be numeric'),
        'length' => array(3,5,'error','Value must be 3-5 digits'),
    ));

    $form->add('submit', 'btnsubmit', 'Submit');

    if ($form->validate()) {

        // show results
        $found_id = search_for_bdm();
        if ($found_id) {
            echo "<p>FOUND source $found_id. You can <a href=\"$app_root/source_manager.php?node=$found_id\"> go to source $found_id</a></p>";
        }
        else {
            echo "<p>Take user to add a new source with the right parent</p>";
        }
        
    } else

        // generate output using a custom template
        $form->render('*horizontal');

    echo "<hr/>";
}
// ----------------------------------------- end
$src = isset($_GET['src']) ? $_GET['src'] : false;
$scope = isset($_GET['scope']) ? $_GET['scope'] : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : false;
if ($src) {
    // sms: highlight the matches using ts_headline, whole of text
    //      Alt: omit the HighlightAll for just 35 words, or 'MaxWords=100,MinWords=50'
    $ts_query_str = preg_replace('/\s+/',' & ',pg_escape_string($src));
    if ($language == 'nb') // This is pretty useless for non-Norwegians
        $src = src_expand($src);
    if ($scope == 0)
        $query = "
            SELECT
                source_id,
                parent_id,
                is_unused(source_id) AS unused,
                ts_headline('english', 
                             get_source_text(source_id), 
                             to_tsquery('english','".$ts_query_str."'),
                             'HighlightAll=true'
                            ) AS src_txt,
                source_date
            FROM
                sources
            WHERE
                source_text SIMILAR TO '%$src%'
            ORDER BY
                source_date
        ";
      else $query = "
            SELECT
                source_id,
                parent_id,
                is_unused(source_id) AS unused,
                ts_headline('english', 
                             get_source_text(source_id), 
                             to_tsquery('english','".$ts_query_str."'),
                             'HighlightAll=true'
                           ) AS src_txt,
                source_date
            FROM
                sources
            WHERE
                part_type = $scope
            AND
                source_text SIMILAR TO '%$src%'
            ORDER BY
                source_date
        ";
    //debug_log($query);
    $handle = pg_query($query);
    echo "<table>\n";
    $n_filter_match = 0;
    $n_filter_nomatch = 0;
    $match = 0;
    while ($row = pg_fetch_assoc($handle)) {
        $match = 0; $branch_text = '';
        $b_o = ''; $b_e = '';
        $branch_text = fetch_val("select get_source_text_p1(".$row['parent_id'].",1,'')");
        if ( $filter ) {
             // get the parental text and match it if we are filtering
             if ( preg_match("/".$filter."/i", $branch_text ) ) { $n_filter_match++; $match = 1; $b_o = '<b>'; $b_e = '</b>'; } 
             else { $n_filter_nomatch++; }
        }
        else {
             $match = 1;
        }
        if ($match) {
            $id = $row['source_id'];
            echo '<tr>';
            echo td_numeric(square_brace(
                to_url('./source_manager.php', array('node' => $id), $id)));
            if ($row['unused'] == 't')
                echo td(span_type(
                    square_brace(italic($row['source_date']))
                    . ' ' . $row['src_txt'] . '<br />' . $b_o.$branch_text.$b_e, 'faded', "Source text is faded because it is unused!"));
            else
                echo td(square_brace(italic($row['source_date']))
                    . ' ' . $row['src_txt'] . '<br />' . $b_o.$branch_text.$b_e);
            echo "</tr>\n";
        }
    }
    echo "</table>\n";
    if ( $filter ) {
        echo '<p>' . pg_num_rows($handle) . ' ' . $_hits . "; $n_filter_match matched branch filter, $n_filter_nomatch did not.</p>";
    }
    else {
        echo '<p>' . pg_num_rows($handle) . ' ' . $_hits . '</p>';
    }
}
echo "</div>\n";

include "./footer.php";

function search_for_bdm() {
    global $authuser,$app_path;
    $fields = array('bdm_type','certno','bdm_jurisdiction','bdm_year');
    echo "<pre>";
    foreach($fields as $input) {
        echo ".. $input = [$_POST[$input]]\n";
    }
    $statement = "SELECT * from sources where sort_order=".$_POST['certno']." and parent_id=(select source_id from sources where parent_id=(select parent_id from sources where parent_id=(select source_id from sources where source_text='".$_POST['bdm_jurisdiction']."' and parent_id=(select source_id from sources where source_text='{".$_POST['bdm_type']."}'))))";
    echo $statement . "\n";

    $rs = pg_query($statement);
    $num_rows = pg_num_rows($rs);
    echo "</pre><hr />";
    echo para("We found $num_rows matches:");
    if ( $num_rows ) echo "<ol>";
    $id = 0;
    while ($row = pg_fetch_assoc($rs)) {
        $id = $row['source_id'];
        echo li("<a href=\"$app_path/source_manager.php?node=$id\">FOUND id[$id]</a>: ".$row['source_text']);
    } 
    echo "</pre>";
    if ( $num_rows ) echo "</ol>";
    return $id;
}
?>
