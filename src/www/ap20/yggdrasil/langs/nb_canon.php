<?php

/***************************************************************************
 *   nb_canon.php                                                          *
 *   Yggdrasil: Norwegian "canonical" names                                *
 *                                                                         *
 *   Copyright (C) 2009-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

// Split from source_search.php 2011-07-20

// NOTE: The following function and paragraph is a preliminary implementation.
// The regexp-replace terms are data rather than code, and should be moved into
// the database, see blog post http://solumslekt.org/blog/?p=151

function src_expand($s) {
    // regexp expansion of 'canonical' Norwegian names to catch variant forms.
    // NOTE: if eg. 'Tor' precedes 'Torsten', the 'Tor' part will be expanded,
    // and the string 'Torsten' is lost.
    $s = str_replace('Abelone',   'A[bp]+[eo]+l+on[aei]+', $s);
    $s = str_replace('Albert',    'Ah?lb[cdeghir]+th?', $s);
    $s = str_replace('Amborg',    'A[mn]+b[joø]+rg?', $s);
    $s = str_replace('Anders',    'An+[ader]+[sz]+', $s);
    $s = str_replace('Anne',      'An+[aeæ]+', $s);
    $s = str_replace('Anniken',   'An+[ie]+[chk]+en', $s);
    $s = str_replace('Alet',      'A[dehl]+i?[dts]+[eh]*', $s);
    $s = str_replace('Amund',     'Aa?[mn]+[ou]+nd?', $s);
    $s = str_replace('Arnold',    'Ah?re?n+h?[aou]+l+[dfhtvw]*', $s);
    $s = str_replace('Arnstein',  'A[hr]*nste+[hij]*n', $s);
    $s = str_replace('Asgerd',    'As[cghij]+erd?', $s);
    $s = str_replace('Aslaug',    'Ad?s[ch]*l[aeo]+[fguvw]+', $s);
    $s = str_replace('Aslak',     'As[ch]*la[cghk]+', $s);
    $s = str_replace('Auen',      '(A|O)u?[dgvw]+[eu]+n?', $s);
    $s = str_replace('August',    'Au?g[eou]+sth?[einsu]*', $s);
    $s = str_replace('Berte',     'B[eiø]+r[gi]*t+h?[ae]*', $s);
    $s = str_replace('Bjørn',     'B[ij]+ø[ehr]*n+', $s);
    $s = str_replace('Bodil',     'Bod?[ei]+ld?e?', $s);
    $s = str_replace('Brynil',    'Br[ouyø]+n+[eijuy]+l+[dvf]*', $s);
    $s = str_replace('Bærulf',    'B[eæ]+h?r+[ou]+[dfhlpvw]+', $s);
    $s = str_replace('Børre',     'B[eijoø]+r+[aegij]+r?', $s);
    $s = str_replace('Boje',      'B[oø]+[ijy]+e', $s);
    $s = str_replace('Daniel',    'Dah?n+ie?l+d?', $s);
    $s = str_replace('David',     'Da[fuvw]+i[dth]+', $s);
    $s = str_replace('Didrik',    'D[ei]+[dt]e?ri[chk]+', $s);
    $s = str_replace('Dorte',     'D(aa|o)r[dhot]+[aeij]+', $s);
    $s = str_replace('Eilert',    'E[hijy]*l+ert?h?', $s);
    $s = str_replace('Einar',     'E[hijy]*n+[ae]+r', $s);
    $s = str_replace('Elin',      'El+[eij]+n?', $s);
    $s = str_replace('Elias',     'El+[ij]+[aæ]s', $s);
    $s = str_replace('Ellef',     'El+[ei]+[fvw]+', $s);
    $s = str_replace('Engebret',  '(I|E)(ng[el]+|m)b[ceghir]+th?', $s);
    $s = str_replace('Erik',      'Er[ei]+[chk]+', $s);
    $s = str_replace('Eskil',     'E[chks]+[ei]+l+d?', $s);
    $s = str_replace('Even',      'Ei?[fvw]+[eiu]+nd?', $s);
    $s = str_replace('Fredrik',   'Fr[ei]+dri[chk]+', $s);
    $s = str_replace('Gaute',     'G[ahou]+[dt]+h?[ei]+', $s);
    $s = str_replace('Gjermund',  '(G|J)[ehij]+rm[ou]+nd?', $s);
    $s = str_replace('Gjertrud',  '(G|J)[ehij]+rd?th?ru[de]*', $s);
    $s = str_replace('Gjert',     '(G|J)[eijæ]+rd?th?', $s);
    $s = str_replace('Gjest',     '(G|J)[eijouæ]+s+(th?|e)', $s);
    $s = str_replace('Greger',    '(Gr[eæ]+g[aeo]+r[ius]*|Gre+s)', $s);
    $s = str_replace('Gudmund',   'Gu+[dlmn]+und?', $s);
    $s = str_replace('Gulbrand',  'Gul?d?bran+d?', $s);
    $s = str_replace('Gullik',    'Gun?l+[ei]+[chk]+', $s);
    $s = str_replace('Gunder',    'G[ouø]+n+d?[ae]+r?', $s);
    $s = str_replace('Gunhild',   'G[ouø]+n+h?[ei]+l+d?[ae]*', $s);
    $s = str_replace('Guri',      'G[uo]?r+[aeiou]+', $s);
    $s = str_replace('Halvor',    'H[ao]+l*[fuvw]+[aeo]+r+d?', $s);
    $s = str_replace('Hans',      'Hand?s', $s);
    $s = str_replace('Harald',    'Har+al+d?', $s);
    $s = str_replace('Hedvig',    'He(l|d)e?[vw][ei]g', $s);
    $s = str_replace('Helge',     'H[eæø]+l+[cghijk]+[aer]*', $s);
    $s = str_replace('Henrik',    'Hend?r[ei]+[chk]+', $s);
    $s = str_replace('Hieronymus','[HJ][ie]+r[eio]+[mn]?[aeiouy]+s?', $s);
    $s = str_replace('Håvald',    '[AHO]+[ao]*[vw]+[aeo]+[lr]+d?', $s);
    $s = str_replace('Ingeborg',  '[EIJ]+e?ngeb[aijoø]r+[egh]*', $s);
    $s = str_replace('Isak',      'Isa+[chk]+', $s);
    $s = str_replace('Iver',      'I[fuvw]+[ae]+r', $s);
    $s = str_replace('Jakob',     'Ja[chk]+[ou]+[bp]+', $s);
    $s = str_replace('Johannes',  'Johan+[ei]+s', $s);
    $s = str_replace('Jon',       'Jo[eh]*n', $s);
    $s = str_replace('Jan',       'J(a[eh]*|[ao]+ha)n', $s);
    $s = str_replace('Kari',      'Kar[eijn]+', $s);
    $s = str_replace('Karl',      '[CK]ar[eo]*l+[ius]*', $s);
    $s = str_replace('Kasper',    '[CK]as[bp][ae]rg?', $s);
    $s = str_replace('Katrine',   '(C|K)h?ath?a?rin[ae]+', $s);
    $s = str_replace('Kirsti',    '(Ch?|K)[ij]+e?r?sth?[eijn]+', $s);
    $s = str_replace('Kittil',    'K[eij]+t+[ei]+l+d?', $s);
    $s = str_replace('Kjell',     'K[ij]+el+d?', $s);
    $s = str_replace('Kjøstol',   '(Th?|K)[ijouyø]+r?st[aeou]+l+[dfhpvw]*', $s);
    $s = str_replace('Klaus',     '(C|K)la[eu]*s+', $s);
    $s = str_replace('Klemet',    '(C|K)lem+e[hnst]*', $s);
    $s = str_replace('Knut',      '(C|K)nu+[dt]+', $s);
    $s = str_replace('Kolbjørn',  '(C|K)[ao]+l+b[ij]+ø[ehr]*n+', $s);
    $s = str_replace('Kristine',  '((Ch|K)[eir]+s|S)th?in[ae]+', $s);
    $s = str_replace('Kristen',   '(Ch|K)r[ei]+sth?en', $s);
    $s = str_replace('Kristian',  '(Ch|K)r[ei]+sth?[ij]+an', $s);
    $s = str_replace('Kristoffer','(Ch|K)r[ei]+sto[fhpv]+er', $s);
    $s = str_replace('Lars',      'La[eu]*r[idt]*[sz]+', $s);
    $s = str_replace('Levor',     'Le+d?[vw]+[aeo]+[dlr]+', $s);
    $s = str_replace('Lisbet',    '(El|L)+i?[sz]+[ae]?[bp]+e[dht]*', $s);
    $s = str_replace('Liv',       'Li+[fvw]+e?', $s);
    $s = str_replace('Lorens',    'L[ao]+[uvw]*r[ae]+n[tsz]+', $s);
    $s = str_replace('Lukas',     'Lu[chk]+as', $s);
    $s = str_replace('Mads',      'Ma[dht]*[aeiuæ]*[sz]+', $s);
    $s = str_replace('Magnhild',  'Man?g[ehiln]+d?', $s);
    $s = str_replace('Malene',    'Ma[adeghilr]+[ei]+n+[ae]+', $s);
    $s = str_replace('Marte',     'Mart+h?[ae]', $s);
    $s = str_replace('Margrete',  '(Gr?|Mar?g?)a?r?[eij]+t+h?[ae]*', $s);
    $s = str_replace('Mariken',   'Mari?[chk]+en', $s);
    $s = str_replace('Mari',      'Mar[aeijnæ]+', $s);
    $s = str_replace('Mette',     'Met+h?[ae]+', $s);
    $s = str_replace('Mikkel',    'M[ei]+[chk]+[ae]+l+', $s);
    $s = str_replace('Mons',      'M[ao]+g?e?n+[dstz]+', $s);
    $s = str_replace('Morten',    'M[ao]+rth?en', $s);
    $s = str_replace('Nils',      'Nie?l+s', $s);
    $s = str_replace('Peder',     'P[deht]+r', $s);
    $s = str_replace('Pernille',  'P[deht]+rn[ei]+l+[ae]+', $s);
    $s = str_replace('Paul',      'P[aeouvw]+l+', $s);
    $s = str_replace('Ragnhild',  'Ran?g[ehiln]+d?', $s);
    $s = str_replace('Realf',     'Real[fhpv]+', $s);
    $s = str_replace('Reier',     'Re[ijy]+[ae]+r', $s);
    $s = str_replace('Rolf',      'R[oø]+l+[eouø]*[fvw]+', $s);
    $s = str_replace('Rønnaug',   'R[oø]+n+[aeouø]+[fgvw]*', $s);
    $s = str_replace('Sakarias',  '(S|Z)a[chk]+[aeirsæ]+', $s);
    $s = str_replace('Sibille',   '(S|Z)+[eiy]+b+[eiy]+l+[ae]+', $s);
    $s = str_replace('Simon',     'Sim+[eo]+nd?', $s);
    $s = str_replace('Sissel',    '[CSZ]+[eiæ]+[dt]*[csz]+[ei]+l+[aei]*d?', $s);
    $s = str_replace('Siver',     'S[iy]+[gjvw]+[aeu]+[lr]+[dht]*', $s);
    $s = str_replace('Siri',      'Sig?[rn]?[ie]+d?', $s);
    $s = str_replace('Sofie',     'So[fhp]+[ij]+[aeæ]*', $s);
    $s = str_replace('Steffen',   'Ste[fhp]+[ae]+n', $s);
    $s = str_replace('Steinar',   'St[eih]+n+[ae]+rd?', $s);
    $s = str_replace('Sveinung',  'S[vw]+ei?n+[ou]+[dgmn]+', $s);
    $s = str_replace('Synnøve',   'S[eiouyø]+n+[aeiouyø]+[fhvw]*[ae]*', $s);
    $s = str_replace('Søren',     'S[eø]+[fvw]*e?r[ei]+n', $s);
    $s = str_replace('Såmund',    'S[ao]+m+[ou]n+d?', $s);
    $s = str_replace('Tallak',    'Th?[ao]+l+a[chgk]+', $s);
    $s = str_replace('Tarald',    'Th?[ao]+r+[vw]?al+d?e?', $s);
    $s = str_replace('Tollef',    'Th?[eoø]+l+[eouø]+[vwf]+', $s);
    $s = str_replace('Tomas',     'Th?om+[aeæ]+s', $s);
    $s = str_replace('Tone',      'Th?[ou]n+e', $s);
    $s = str_replace('Torbjørn',  'Th?oe?rb[ij]+ør?n', $s);
    $s = str_replace('Torberg',   'Th?[oeø]+rb([ij]?ø|[oeæ])rg?', $s);
    $s = str_replace('Torger',    'Th?[aeo]+r[egiju]+[rs]*', $s);
    $s = str_replace('Torkil',    'Th?[eoø]+r[chk]+[ie]+l+d?', $s);
    $s = str_replace('Tormod',    'Th?ormo[de]*', $s);
    $s = str_replace('Torsten',   'Th?(aa|o)r?st[ei]+n', $s);
    $s = str_replace('Torvil',    'Th?[ou]+r[vw]+[ie]+l+d?', $s);
    $s = str_replace('Tor',       'Th?o[der]+', $s);
    $s = str_replace('Tov',       'Th?o[fuvw]+', $s);
    $s = str_replace('Trond',     'Th?roe?nd?', $s);
    $s = str_replace('Tyge',      'Th?[yø]+[chgk]+[ei]+r?', $s);
    $s = str_replace('Valentin',  '[FVW]+al+[en]*th?in[us]*', $s);
    $s = str_replace('Vetle',     '(V|W)[eiæ]+[dht]+l[eouø]+[fvw]*', $s);
    $s = str_replace('Vrål',      '(V|W)r(aa|o)[eh]*l+[dfvw]*', $s);
    $s = str_replace('Wilhelm',   '(V|W)[ei]+l+[ehlou]+m', $s);
    $s = str_replace('Åge',       '[AOÅ]+a?[cghk]+[ei]+', $s);
    $s = str_replace('Åse',       '[AOÅ]+a?st?h?e+', $s);
    $s = str_replace('Åshild',    '[AOÅ]+a?r?s+h?[ei]+l+[de]*', $s);
    $s = str_replace('Åsold',     '[AOÅ]+a?s+[eou]+l+[dfvw]*', $s);
    $s = str_replace('Åvet',      '[AOÅ]+a?[gvw]+[aeio]+[dht]+[ae]*', $s);
    return $s;
}

echo
"<p>Regulære søkeuttrykk er definert for:<br />
Abelone, Albert, Alet, Amborg, Anders, Anne, Anniken, Amund, Arnold, Arnstein,
Asgerd, Aslak, Aslaug, Auen, August, Berte, Bjørn, Bodil, Brynil, Bærulf, Børre,
Boje, Daniel, David, Didrik, Dorte, Eilert, Einar, Elias, Elin, Ellef, Engebret,
Erik, Eskil, Even, Fredrik, Gaute, Gjermund, Gjert, Gjertrud, Gjest, Greger,
Gudmund, Gulbrand, Gullik, Gunder, Gunhild, Guri, Halvor, Hans, Harald, Hedvig,
Helge, Henrik, Hieronymus, Håvald, Ingeborg, Isak, Iver, Jakob, Jan(Johan),
Johannes, Jon, Kari, Karl, Kasper, Katrine, Kirsti, Kittil, Kjell, Kjøstol,
Klaus, Klemet, Knut, Kolbjørn, Kristen, Kristian, Kristine, Kristoffer, Lars,
Levor, Lisbet, Liv, Lorens, Lukas, Mads, Magnhild, Malene, Margrete, Mari,
Mariken, Marte, Mette, Mikkel, Mons, Morten, Nils, Peder, Pernille, Paul,
Ragnhild, Realf, Reier, Rolf, Rønnaug, Sakarias, Sibille, Simon, Sissel, Siver,
Sofie, Steinar, Steffen, Sveinung, Synnøve, Søren, Såmund, Tallak, Tarald,
Tollef, Tomas, Tone, Tor, Torberg, Torbjørn, Torger, Torkil, Tormod, Torsten,
Torvil, Tov, Trond, Tyge, Valentin, Vetle, Vrål, Wilhelm, Åge, Åse, Åshild,
Åsold, Åvet.</p>\n";

?>
