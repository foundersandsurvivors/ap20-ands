<?php

/***************************************************************************
 *   source_search.php                                                     *
 *   Yggdrasil: Search for sources                                         *
 *                                                                         *
 *   Copyright (C) 2009-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

require "./settings/settings.php";
require_once "./langs/$language.php";

// we'll display only raw dates here
pg_query("SET DATESTYLE TO GERMAN");

$title = "$_Search_for_sources";
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

echo "<form id=\"$form\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n<div>\n";
echo "$_Text: <input type=\"text\" size=\"40\" name=\"src\" />\n";
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
echo '<option selected="selected" value="0">Full</option>';
while ($rec = pg_fetch_assoc($handle)) {
    $option = "<option ";
    if ($rec['part_type_id'] == $selected)
        $option .= "selected=\"selected\" ";
    $option .= "value=\"" . $rec['part_type_id'] . "\">" . $rec[$label] . "</option>\n";
    echo $option;
}
echo "</select></td></tr>\n"
    . "<input type=\"submit\" value=\"$_Search\" />\n"
    . "</div>\n</form>\n\n";
$src = isset($_GET['src']) ? $_GET['src'] : false;
$scope = isset($_GET['scope']) ? $_GET['scope'] : 0;
if ($src) {
    if ($language == 'nb') // This is pretty useless for non-Norwegians
        $src = src_expand($src);
    if ($scope == 0)
        $query = "
            SELECT
                source_id,
                is_unused(source_id) AS unused,
                get_source_text(source_id) AS src_txt,
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
                is_unused(source_id) AS unused,
                get_source_text(source_id) AS src_txt,
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
    $handle = pg_query($query);
    echo "<table>\n";
    while ($row = pg_fetch_assoc($handle)) {
        $id = $row['source_id'];
        echo '<tr>';
        echo td_numeric(square_brace(
            to_url('./source_manager.php', array('node' => $id), $id)));
        if ($row['unused'] == 't')
            echo td(span_type(
                square_brace(italic($row['source_date']))
                . ' ' . $row['src_txt'], 'faded', "Source text is faded because it is unused!"));
        else
            echo td(square_brace(italic($row['source_date']))
                . ' ' . $row['src_txt']);
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo '<p>' . pg_num_rows($handle) . ' ' . $_hits . '</p>';
}
echo "</div>\n";

include "./footer.php";
?>
