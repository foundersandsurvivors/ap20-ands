<?php

/***************************************************************************
 *   functions.php                                                         *
 *   Yggdrasil: Common Functions                                           *
 *                                                                         *
 *   Copyright (C) 2006-2011 by Leif B. Kristensen <leif@solumslekt.org>   *
 *   All rights reserved. For terms of use, see LICENSE.txt                *
 ***************************************************************************/

/* extra functions from sms start */
// include(dirname(__FILE__)."/settings/external.php");

/* recurse through a directory */

function getFileList($dir, $recurse=false) {
    // array to hold return value
    $retval = array();

    // add trailing slash if missing
    if(substr($dir, -1) != "/") $dir .= "/";

    // open pointer to directory and read list of files
    $d = @dir($dir) or die("getFileList: Failed opening directory $dir for reading");
    while(false !== ($entry = $d->read())) {
      // skip hidden files
      if($entry[0] == ".") continue;
      if(is_dir("$dir$entry")) {
        $retval[] = array(
          "name" => "$dir$entry/",
          "type" => filetype("$dir$entry"),
          "size" => 0,
          "lastmod" => filemtime("$dir$entry")
        );
        if($recurse && is_readable("$dir$entry/")) {
          $retval = array_merge($retval, getFileList("$dir$entry/", true));
        }
      } elseif(is_readable("$dir$entry")) {
        $retval[] = array(
          "name" => "$dir$entry",
          "type" => mime_content_type("$dir$entry"),
          "size" => filesize("$dir$entry"),
          "lastmod" => filemtime("$dir$entry")
        );
      }
    }
    $d->close();

    return $retval;
}

/* xml utility functions */

function array_to_xml(array $arr, SimpleXMLElement $xml) {
    foreach ($arr as $k => $v) {
        is_array($v)
            ? array_to_xml($v, $xml->addChild($k))
            : $xml->addChild($k, $v);
    }
    return $xml;
}


function pg_query_to_simplexml_element ( $sql, $style='' ) {
   $escaped_sql = pg_escape_string($sql);
   $q2xml = "SELECT query_to_xml( '$escaped_sql',true,true,'');";
   $xml_string = fetch_val ( $q2xml );
   /* kill namespace shit */
   $xml_string = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml_string);
   $xml_string = preg_replace('/ xsi[^=]*="[^"]*"/i', '',  $xml_string);
   if ( $style == 'string' ) {
       return $xml_string;
   }
   else {
       return new SimpleXMLElement($xml_string);
   }
}

//########################################################################################
// Take table from util_tqjson_to_php and generate html, supporting some custom styles
//########################################################################################
function to_url_ICD10CM ( $terms ) {
   $uri = 'http://www.icd10data.com/Search.aspx?search='.$terms.'&codebook=ICD10CM';   
   return to_url( $uri, array(), $terms, "Search in ICD10CM");
}

function util_tqjson_table_to_html ( $cols, $rows, $style='' ) {
    $class = $style ? " class=\"$style\"" : '';
    $s = "<table$class border=\"1\">";
    if ( $style == "icd10" ) {

        // row for columns, combining multiple causes and durations
        // VERY hardcoded to the query in the calling function icd10_deathCause_from_google_for_person

        $n_first = 5;
        $combine = "MDESC1,MDUR1,MDESC2,MDUR2,MDESC3,MDUR3,MDESC4,MDUR4,MDESC5,MDUR5";
        $pos_ucause = count($cols) - 2;
        $c_text = "<ul>";
        $s .= "<tr class=\"gshead\">";
        for ($i=0; $i<$n_first; $i++) {
           $s .= "<th>" . $cols[$i]->{"label"} . "</th>"; 
        }
        $s .= "<th>Multiple death causes</th><th>Underlying cause ICD10</th>";
        $s .= "</tr>";

        // data rows, combining multiple causes and durations into ordered list

        for ($j=0; $j<count($rows); $j++) {
            $s .= "<tr>";
            for ($i=0; $i<$n_first; $i++) {
               $s .= "<td>" . $rows[$j]->{"c"}[$i]->{"v"} . "</td>"; 
            }
            $s .= "<td><ol>";
            $com = array(5,7,9);
            foreach ( $com as $pos ) {
               $pos2 = $pos + 1; // $com has the death cause description, pos2 is the duration
               $pos3 = $pos + 2; // for multiple icd10 codes when added in sheet -- positions may vary
               $desc = $rows[$j]->{"c"}[$pos]->{"v"};
               $durn = $rows[$j]->{"c"}[$pos2]->{"v"};
               // todo: icd10 code link
               if ( $desc || $durn ) {
                   $s .= "<li>";
                   if ($desc) $s .= to_url_ICD10CM($desc); 
                   if ($durn) $s .= " (". $durn . ")"; 
                   $s .= "</li>";
               }
            }
            $s .= "</ol></td>";

            // underlying cause, linked to web lookup
            $s .= "<td>";
            $s .= to_url_ICD10CM( $rows[$j]->{"c"}[$pos_ucause]->{"v"} );
            $s .= "</td>";

            $s .= "</tr>";
        }

    }
    else {

        // default - rows and columns as supplied by the google query/json to php

        // row for columns
        $s .= "<tr class=\"gshead\">";
        for ($i=0; $i<count($cols); $i++) {
           $s .= "<th>" . $cols[$i]->{"label"} . "</th>"; 
        }
        $s .= "</tr>";
        // data rows - and loop through each column
        for ($j=0; $j<count($rows); $j++) {
            $s .= "<tr>";
            for ($i=0; $i<count($cols); $i++) {
               $s .= "<td>" . $rows[$j]->{"c"}[$i]->{"v"} . "</td>"; 
            }
            $s .= "</tr>";
        }
    }
    $s .= "</table>";
    return $s;
}

//########################################################################################
// Take json response from a google visualisation api query and return php rows, columns
//########################################################################################
function util_tqjson_to_php ( $r ) {
    $json_debug = 0; // ensure your spreadsheet has valid values e.g. NO text in numeric columns allowed
    if ($json_debug) { file_put_contents("/tmp/json1",$r); }
    $r = preg_replace('/^.+\ngoogle.visualization.Query.setResponse\((.+)$/',"$1",$r);
    $r = substr($r, 0, -2);
    if ($json_debug) {
        file_put_contents("/tmp/json2",$r);
        echo pre("\n\n_____________________________var_dump \$r:\n");
        var_dump($r);
        echo pre("\n\n_____________________________json start \$R:\n");
    }
    $R = json_decode($r); // convert the json to php variables
    if ($json_debug) { echo json_encode($R); }
    $cols = $R->{"table"}->{"cols"};
    $rows = $R->{"table"}->{"rows"};
    if ($json_debug) {
        file_put_contents("/tmp/jsonC",print_r($cols,true)); 
        file_put_contents("/tmp/jsonR",print_r($rows,true));
        echo pre("\n\n_____________________________\$R:\n");
        var_dump($R);
        echo pre("\n_____________________________json end\n");
        echo pre("status=".$R->{"status"});
    }
    return array(count($rows),$cols,$rows);

}

//########################################################################################
// Lookup cause of death data from a custom google spreadsheet for a single person
// using google visualization api http query to spreadsheet, getting json.
// Reference: https://developers.google.com/chart/interactive/docs/querylanguage
//########################################################################################
function icd10_deathCause_from_google_for_person($key,$person_id,$format) {
  global $google_authtoken, $authtoken_file, $dbname;
  if ( isset($google_authtoken) && $key ) {
      // change this when Garry finalises ICD10 spreadsheet format
      $sheetname = $dbname . "_icd10";
      $apiquery_test = 'select A where A='.$person_id;
      $apiquery = 'select A,H,L,M,N,O,P,T,U,Y,Z,AD,AE,AI,AJ,AN,AO where A='.$person_id;
      //$apiquery = 'select M,AJ,AK,AL,AM,AN,AR,AS,AW,AX,CA where A='.$person_id;
      $c = curl_init();
      $headers = array( "Authorization: GoogleLogin auth=" . urlencode($google_authtoken), "GData-Version: 3.0",);
      // curl_setopt($c, CURLOPT_URL, 'https://spreadsheets.google.com/tq?tqx=out:'.$format.'&tq='.urlencode($apiquery).'&key='.$key);
      curl_setopt($c, CURLOPT_URL, 'https://spreadsheets.google.com/tq?tqx=out:json&tq='.urlencode($apiquery).'&key='.$key);
      curl_setopt($c, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
      curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($c, CURLOPT_POST, false);
      $r = curl_exec($c);
      $table = util_tqjson_to_php($r); 
      $found = $table[0];
      $cols = $table[1];
      $rows = $table[2];
      $status = curl_getinfo($c);
      curl_close($c);
      
      if ( $status["http_code"] == 200 ) {
           if ( preg_match("/Reason: User not signed in/", $r) ) {
                // delete the file and the refresh will recreate it
                //unlink($authtoken_file);
                return pre( "You need to login to Google to be able to access the spreasdsheet.", "error" );
           }
           else {
               if ( $found == 1 ) {
                   return util_tqjson_table_to_html($cols,$rows,"icd10"); 
               }
               elseif ( $found == 0 ) {
                   return para("Person $person_id not found in google spreadsheet<br/> (this is ok because only persons with id numbers < 10317 are in the experimental icd10 spreadsheet) as at 24 Jan 2013","error");
               }
               else {
                   return para("Person $person_id found $found times in google spreadsheet<br/> (this <em>might</em> be ok if you are editing but each person should only be in the causes of death spreadsheet \"$sheetname\" once.","error");
               }
           }
      }
      else {
           return pre( "Unexpected http_code=[".$status["http_code"]."]. Contact support.", "error" );
      }
  }
  else { return ""; }
}

function hstore_to_array($hstore) {
    return json_decode('{' . str_replace('"=>"', '":"', $hstore) . '}', true);
}

//########################################################################################
// modified person search - pass where clause, heading, and "." for same dir as family.php
//########################################################################################
function search_and_show_persons_custom ($query,$heading,$urldir,$style) {
    $handle = pg_query($query);
    $n = fetch_num_rows($query);
    if ($n) {
        echo $heading;
        echo para(paren($n . " found"));
        echo "<ul>";
        $showKeys = "";
        while ($row = pg_fetch_row($handle)) {
          $p = $row[0];
          $li_class = "<li>";
          if ($style == "keys") {
              $showKeys = " <em title=\"Keys\">".$row[2]." ".$row[3]."</em>";
              if ( preg_match('/"notA"/',$row[3]) ) $li_class = "<li class=\"notA\">"; 
          }
          echo $li_class . get_name_and_dates($urldir."/family.php", $p) . conc(child_of($p)) . $showKeys . "</li>\n";
        }
        echo "</ul>\n";
        echo para(paren($n . " found"));
    }
    else {
        echo $heading . para("None found.");
    }
}

function search_and_show_persons ($whereClause,$heading,$urldir) {
    $query = "select person_id,get_pbdate(person_id) as pbd,keys,p_sdata from persons where $whereClause 
              AND is_merged(person_id) IS FALSE ORDER BY pbd";
    search_and_show_persons_custom($query,$heading,$urldir,"keys");
}

//#########################################################################
// securing post vars
//#########################################################################
function secure($v) {
   // Stripslashes 
   if (get_magic_quotes_gpc()) { 
       $v = stripslashes($v); 
   } 
   // Quote if not a number 
   if (!is_numeric($v)) { 
       $v = pg_escape_string($v); // use pf_escape_literal when php >= 5.4.4 dev=5.2.6-2 (eeww!!)
   } 
   return $v;
}

function update_db($log,$authuser,$in,$command) {
   $rs = pg_query($command);
   $num_rows = pg_num_rows($rs);
   $log->info("<dbmod u=\"$authuser\" in=\"$in\" num_rows=\"$num_rows\" >\n".$command."\n</dbmod>");
}

//#########################################################################
// error page
//#########################################################################

//#########################################################################
// logging/debugging
//#########################################################################

/* logging */
function append_log($logfile,$info) {
   global $authuser;
   if (!$logfile) $logfile = "/srv/fasweb/webwork/yggdrasil/khrd/unnamed.log";
   $fh = fopen($logfile,'a');
   fwrite($fh,$_SERVER['REQUEST_URI'] ." $authuser ".$info);
   fclose($fh);
}

function debug_log($info) {
   global $logdebug;
   append_log($logdebug,$info);
}
/* extra functions from sms end */

//#########################################################################
// basic db retrieval wrapper functions
//#########################################################################

function fetch_val($query) {
    // wrapper func, gets single value from db
    debug_log("\n==== fetch_val($query)\n");
    $result = pg_query($query);
    $row = pg_fetch_row($result);
    return $row[0];
}

function fetch_row($query) {
    // wrapper func, gets single row from db
    $result = pg_query($query);
    $row = pg_fetch_row($result);
    return $row;
}

function fetch_row_assoc($query) {
    // wrapper func, gets single row from db
    $result = pg_query($query);
    $row = pg_fetch_assoc($result);
    return $row;
}

function fetch_num_rows($query) {
    // wrapper func, gets number of rows from db
    $result = pg_query($query);
    return pg_num_rows($result);
}

function get_next($entity) {
    // takes entity name, returns new id number
    // works because of consistent naming scheme
    return fetch_val("
        SELECT COALESCE(MAX(" . $entity . "_id), 0) + 1
        FROM " . $entity . "s");
}

//#########################################################################
// date functions
//#########################################################################

function mydate($datestring) {
    // takes iso-8601 date of format 'yyyy-mm-dd'
    // returns localised date string
    global $language;
    return fetch_val("SELECT mydate('$datestring', '$language')");
}
function fuzzydate($datestring) {
    // takes internal "fuzzy date" char(18) string
    // returns localised date string
    global $language;
    return fetch_val("SELECT fuzzydate('$datestring', '$language')");
}

function parse_sort_date($sdate, $edate) {
    // takes input from sort date and event date, returns valid date string
    if ($sdate && $sdate[0] == '!') // override if sort date preceded by exclamation mark
        return substr($sdate, 1, 8);
    // if a sort date is entered, and event date is blank or contains only year
    if ($sdate && (!$edate || substr($edate, 4, 4) == '0000')) {
        $sort_date = $sdate;
        if (strlen($sort_date) == 6)
            $sort_date .= '15'; // sms: append MIDPOINT day to bare month/year
        if (strlen($sort_date) == 4)
            $sort_date .= '0701'; // sms: append MIDPOINT month and day to bare year
            ##$sort_date .= '0101'; // append month and day to bare year
    }
    else { // build a valid sort date from the main date
        if ($edate) { // if the main date exists
            $sort_date = substr($edate, 0, 8);
            $mflag = 0;
            if (substr($sort_date, 0, 4) == '0000')
                $sort_date[3] = '1';
            if (substr($sort_date, 4, 2) == '00' )
                $mflag = 1; # means we want july 1st
                $sort_date[5] = '7'; # midpoint
                # sms: modded for midpoints, not 1st/1st
                ####$sort_date[5] = '1';
            if (substr($sort_date, 6, 2) == '00' && $mflag)
                $sort_date[7] = '1';
            if (substr($sort_date, 6, 2) == '00' && !$mflag)
                $sort_date[7] = '15'; # we want midpoint of the month
        }
        else {
            $sort_date = '00010101';
        }
    }
    return $sort_date;
}

function trim_date($fdate) {
    // trims empty days, months and years from YYYYMMDD string
    if (substr($fdate, 6, 2) == '00') {
        $fdate = substr($fdate, 0, 6);
        if (substr($fdate, 4, 2) == '00') {
            $fdate = substr($fdate, 0, 4);
            if (substr($fdate, 0, 4) == '0000')
                $fdate = '';
        }
    }
    return $fdate;
}

function pad_date($fdate) {
    // the reverse operation of previous func
    while (strlen($fdate) < 8)
        $fdate .= '0';
    return $fdate;
}

function year_comp($s1, $s2, $limit=10) {
    // compare two dates and return true if the difference is within limit.
    if (!$s1 || !$s2) // if one birth date is missing
        return true;
    if (abs($s1 - $s2) <= $limit)
        return true;
    else
        return false;
}

function died_young($p) {
    if (fetch_val("SELECT age_at_death($p)") < 15)
        return TRUE;
    else
        return FALSE;
}

//#########################################################################
// misc simple data retrieval functions
//#########################################################################

function get_name($p) {
    return fetch_val("SELECT get_person_name($p)");
}

function get_place($pl) {
    return fetch_val("SELECT get_place_name($pl)");
}

function get_tag_name($n) {
    global $language;
    if ($language == 'nb')
        return fetch_val("SELECT tag_label FROM tags WHERE tag_id = $n");
    elseif ($language == 'en')
        return fetch_val("SELECT tag_name FROM tags WHERE tag_id = $n");
}

function lifespan($born, $died) {
    if (substr($died,0,8) == '00000000') { $diedAlert = ''; }
    else { $diedAlert = '&#8224;'; }
    $isDead = fuzzydate($died);
    //debug_log("..died:[".$isDead."]\n");
return paren(fuzzydate($born) . ' - ' . $isDead.$diedAlert);
}

function get_name_and_dates($url, $p) {
    global $_unregistered;
    if ($url == '')
        $url = $_SERVER['PHP_SELF'];
    if (!$p) // $p == 0
        return "[$_unregistered]";
    $row = fetch_row_assoc("
        SELECT
            name,
            born,
            died
        FROM
            name_and_dates
        WHERE
            person = $p
    ");
    return to_url($url, array('person' => $p), $row['name'], get_name_and_lifespan($p))
        . conc(lifespan($row['born'], $row['died']));
}

function get_name_and_lifespan($p) {
    global $_unregistered;
    if (!$p) // $p == 0
        return "[$_unregistered]";
    $row = fetch_row_assoc("
        SELECT
            name,
            born,
            died
        FROM
            name_and_dates
        WHERE person = $p
    ");
    return $row['name'] . conc(lifespan($row['born'], $row['died']));
}

function get_parents($p) {
    $row = fetch_row_assoc("
        SELECT
            get_parent($p,1) AS father,
            get_parent($p,2) AS mother
    ");
    $parents[0] = get_name_and_dates('', $row['father']);
    $parents[1] = get_name_and_dates('', $row['mother']);
    return $parents;
}

function has_parents($p) {
    if ($p)
        return fetch_val("
            SELECT COUNT(*) FROM relations WHERE child_fk = $p
        ");
    else
        return 0;
}

function has_descendants($p) {
    if ($p)
        return fetch_val("
            SELECT COUNT(*) FROM relations WHERE parent_fk = $p
        ");
    else
        return 0;
}

function has_spouses($p) {
    if ($p)
        return fetch_val("
            SELECT COUNT(*) FROM marriages WHERE person = $p
        ");
    else
        return 0;
}

function get_second_principal($event, $person) {
    return fetch_val("SELECT get_principal($event,$person)");
}

function get_connection_count($person) {
    return fetch_val("SELECT conn_count($person)");
}

function get_coparent($p, $q) {
    return fetch_val("SELECT get_coparent($p, $q)");
}

function get_relation_id($p, $g) {
    return fetch_val("
        SELECT
            relation_id
        FROM
            relations
        WHERE
            child_fk = $p
        AND
            get_gender(parent_fk) = $g
    ");
}

function find_father($p) {
    return fetch_val("SELECT get_parent($p,1)");
}

function find_mother($p) {
    return fetch_val("SELECT get_parent($p,2)");
}

function get_gender($p) {
    return fetch_val("SELECT get_gender($p)");
}


function get_curl_fasprot_uri($uri) {
       global $xmluser, $xmlpassword; // defined in settings, caller needs to include it
       $ch = curl_init($uri);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       curl_setopt($ch, CURLOPT_USERPWD, "${xmluser}:{$xmlpassword}");
       curl_setopt($ch, CURLOPT_POST, 0);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
if ( isset($_COOKIE['auth_pubtkt']) && preg_match('/restxq/',$uri ) ) {
    $cookie_string='auth_pubtkt='.urlencode($_COOKIE['auth_pubtkt']).'; path=/; domain=.founders-and-survivors.org'; 
    curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
    file_put_contents("/tmp/ygg_get_curl_fasprot_uri_cookie", $cookie_string);
}
else {
    file_put_contents("/tmp/ygg_get_curl_fasprot_uri_cookie", "");
}
       $xml = curl_exec($ch);
       $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       curl_close($ch);
       if ($http_status == 307 ) {
           return "<debug>I got a 307: xml[$xml]</debug>";
       }
       else {
           return $xml;
       }
}

// The following is redundant as we use apache2 to allow access from selected machines.
function get_curl_fasprot_restxq_uri($uri) {
       global $xmluser, $xmlpassword; // defined in settings/external.php
       $ch = curl_init($uri);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       $prot_u = "****"; $prot_p = "****";
       curl_setopt($ch, CURLOPT_USERPWD, "${prot_u}:$prot_p");
       curl_setopt($ch, CURLOPT_POST, 1);
       curl_setopt($ch, CURLOPT_REFERER, $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"] );
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
       // check if we have an auth_pubtkt cookie set and if so include it in the request and check staff priviliges (todo - more flexible)

       $cookie_string="";
       foreach( $_COOKIE as $key => $value ) {
         // escape semicolons in the auth token cookie?
         $v_esc = urlencode($value); // str_replace(";", "%3D", $value);
         $cookie_string .= "$key=$v_esc; ";
       };
       if ($cookie_string) { $cookie_string .= 'path=/'; };
       $sstest = "no";
       if ( isset($_COOKIE['auth_pubtkt']) and preg_match( "/tokens=.*staff/", $_COOKIE['auth_pubtkt'] ) ) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie_string );
            $sstest = "we passed cookie_string see /tmp/ygg_get_curl_fasprot_uri_c";
            # debug. file_put_contents("/tmp/ygg_get_curl_fasprot_uri_c", "CURLOPT_COOKIE SET cookie_string[".$cookie_string."]\n\$_COOKIE['auth_pubtkt']=[".$_COOKIE['auth_pubtkt']."]\n" );
       }
       else {
            # debug. file_put_contents("/tmp/ygg_get_curl_fasprot_uri_c", "No cookie or no staff access" );
       }
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt( $ch, CURLOPT_MAXREDIRS, 1 );
       $xml = curl_exec($ch);
            $v = print_r($ch, true);
       $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
       curl_close($ch);
 file_put_contents("/tmp/ygg_get_curl_fasprot_uri_result", "uri[$uri]\nsstest[$sstest]\ncookie_string=[$cookie_string]\nhttp_status=[$http_status]\nxml=[$xml]\ncurl[$v]");
       return $xml;
}




// http://mondaybynoon.com/20091123/avoiding-iframes-via-php-and-curl/
function get_url( $url,  $javascript_loop = 0, $timeout = 30 )
{
    $url = str_replace( "&", "&", urldecode(trim($url)) );

//$cookie = tempnam ("/tmp", "CURLCOOKIE");

$cookie_string="";
foreach( $_COOKIE as $key => $value ) {
  // escape semicolons in the auth token cookie?
  $v_esc = urlencode($value); // str_replace(";", "%3D", $value);
  $cookie_string .= "$key=$v_esc; ";
};
//if ($cookie_string) { $cookie_string .= 'path=/'; };


    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
    curl_setopt( $ch, CURLOPT_URL, $url );

    //curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie_string );
    // pass cookies we have
    curl_setopt($ch, CURLOPT_COOKIE, $cookie_string );

    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_ENCODING, "" );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_MAXREDIRS, 1 );
    $content = curl_exec( $ch );
    $response = curl_getinfo( $ch );
    curl_close ( $ch );

    if ($response['http_code'] == 301 || $response['http_code'] == 302 )
    {
        ini_set("user_agent", $_SERVER['HTTP_USER_AGENT'] );

        if ( $headers = get_headers($response['url']) )
        {
            foreach( $headers as $value )
            {
                if ( substr( strtolower($value), 0, 9 ) == "location:" )
                    return get_url( trim( substr( $value, 9, strlen($value) ) ) );
            }
        }
    }

    if (    ( preg_match("/>[[:space:]]+window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/>[[:space:]]+window\.location\=\"(.*)\"/i", $content, $value) ) &&
            $javascript_loop < 5
    )
    {
        return get_url( $value[1], $javascript_loop+1 );
    }
    else
    {
        return array( $content, $response );
    }
}




# call this if you have the exact xmlid; raw rectype and basex is on the localhost

function get_external_xml ($xml_id, $qs="?rawFASTABLE") {
   global $basex_restxq,$basex_server,$basex_restxq_iframe;
   $collection = 'convict';
   if ( substr($xml_id,0,2) == 'FM' ) $collection = 'khrd';
   if ( $basex_server ) {
       $uri = $basex_restxq_iframe . "/id/$xml_id$qs";
       $uri = "http://dev.founders-and-survivors.org/prot/xmldbAjax/$collection/src/$xml_id$qs";
   }
   else {
       # needs work/redundant? -- only works on dev server
       $uri = 'http://'.$_SERVER['HTTP_HOST']."/prot/xmldbAjax/$collection/src/$xml_id$qs";
   }
   return get_curl_fasprot_uri($uri);
}

# ????
function get_restxq_xml ( $query ) {
   $uri = "http://klaatu:8984/restxq$query";
   //return get_curl_fasprot_restxq_uri($uri);
   return get_curl_fasprot_uri($uri);
}


################################################ restxq basex interface
# this is for more recent restxq basex interface.
# /restxq/ location on this server goes via apache rewrite/mod_proxy
# If you want authorisation, your restxq scripts should check for the fas SSO cookie named auth_pubtkt
# otherwise we end up in redirection/basic auth loops if we use "/restxq/" or "http://dev.founders-and-survivors.org/
# The mapping to 'klaatu' 

function get_restxq_xml2 ( $query ) {
   global $basex_restxq;
   if ($basex_restxq) {
       $uri = "$basex_restxq$query";
       $request_results = get_url($uri);
       //$x = var_dump($request_results[1]);
       //file_put_contents("/tmp/ygg_get_restxq_xml2", "uri[$uri] request_results con[".$request_results[0]."] resp[".$x."]\n");
       return $request_results[0];
   }
   else {
       return "<error><unsupportedFunction>get_restxq_xml2 is unsupported. Modify \$basex_restxq in settings to define the url to your basex restxq service.</unsupportedFunction></error>";
   }
}

function display_get_restxq_xml2 ( $query ) {
   $xml = get_restxq_xml2 ($query);
   if ($xml) {
       $xp_data = simplexml_load_string($xml);
       $root_name = $xp_data->getName();
       if ($root_name == "html") { echo $xml . '<iframe id="results" width="100%" height="400" scrolling="yes"></iframe>'; }
       else {
        $xml_fields = $xp_data->xpath( '/*/*' );
        text_eac("",$xml_fields, 'list');
       }
   }
}


// sms: show all upper branches more explicitly, just first line?
function get_source_text_tree($source_id) {
       return fetch_val("SELECT get_source_text_p1($source_id,1,'')");
}

function get_source_text($source_id) {
    global $note_to_db_style;

    # sources starting with an X give an XREF to xml database via YMAP function in mod_perl utility

    if ( substr($source_id,0,1) == 'X' ) {
       /* cross reference to xml database YMAP lookup 
          e.g. X131010022
               http://dev.founders-and-survivors.org/prot/xmldbAjax/convict/src/YMAP131010022
       */
       $uri = 'http://'.$_SERVER['HTTP_HOST']."/prot/xmldbAjax/convict/src/YMAP".substr($source_id,1);
       $xml_response = get_curl_fasprot_uri($uri);
       return "<pre>###################uri[$uri]" . $xml_response . "</pre>";
    }
//    elseif ( $note_to_db_style == 'preserveLines' ) {
//       /* normal Yggdrasil source in its database */
//       $t = fetch_val("SELECT get_source_text($source_id)");
//       return note_from_db($t);
//    }
    else {
       /* normal Yggdrasil source in its database */
       return fetch_val("SELECT get_source_text($source_id)");
    }
}

function get_source_text_plain($source_id) {
    return fetch_val("SELECT get_source_text($source_id)");
}

function has_coprincipal($tag) {
    return fetch_val("SELECT has_coprincipal($tag)");
}

function get_source_principal($node) {
    return fetch_val("
        SELECT person_fk
        FROM source_linkage
        WHERE source_fk = $node AND role_fk = 1
    ");
}

//#########################################################################
// string functions
//#########################################################################

function to_url($base_url, $params, $txt, $title='') {
    $str = '<a href="' . $base_url;
    if ($params) {
        foreach ($params as $key => $value)
            $pairs[] = $key . '=' . $value;
        $str .= '?' . join($pairs, '&amp;');
    }
    $str .= '"';
    if ($title)
        $str .= ' title="' . $title . '"';
    $str .= '>' . $txt . '</a>';
    return $str;
}

function fonetik($s) {
    // a minimal phonetic comparison routine
    $s = strtoupper($s); // convert to upper case
    $s = str_replace('Å','AA', $s);
    $s = str_replace('CH','K', $s);
    $s = str_replace('CA','KA', $s);
    $s = str_replace('CE','SE', $s);
    $s = str_replace('CI','SI', $s);
    $s = str_replace('CO','KO', $s);
    $s = str_replace('CU','KU', $s);
    $s = str_replace('TH','T', $s);
    $s = str_replace('ELISA', 'LIS', $s);
    $s = str_replace('ENGE', 'INGE', $s);
    $s = str_replace('KAREN', 'KARI', $s);
    $s = str_replace('MAREN', 'MARI', $s);
    return soundex($s);
}

function soundex_comp($s1, $s2, $limit=20) {
    // separate first character and numeric part of the two soundexes and compare them.
    // if difference is within acceptable bounds, return true, else return false.
    $first_char_1 = $s1{0};
    $first_char_2 = $s2{0};
    $number_1 = substr($s1, 1);
    $number_2 = substr($s2, 1);
    if ($first_char_1 == $first_char_2 && abs($number_1 - $number_2) <= $limit)
        return true;
    else
        return false;
}

function fixamp($str) {
    // nifty expression to replace naked &s with &amp;s. keeps W3C validator happy.
    // included here for compatibility with Slekta(SQL).
    $str = preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/", "&amp;", $str);
    return $str;
}

function linked_name($p, $url='./family.php') {
    global $_Link_to_person;
    return square_brace($p)
        . conc(to_url($url, array('person' => $p),
                    get_name($p),
                    get_name_and_lifespan($p))
        );
}

function child_of($p, $url='./family.php') {
    global $_son, $_daughter, $_child, $_of, $_and;
    $str = '';
    if (has_parents($p)) {
        $gender = get_gender($p);
        if ($gender == 0) $ch = $_child;
        elseif ($gender == 1) $ch = $_son;
        elseif ($gender == 2) $ch = $_daughter;
        else $ch = "child";
        $father = find_father($p);
        $mother = find_mother($p);
        if ($father && $mother)
            $str = paren("$ch $_of " .
                linked_name($father, $url) . " $_and " .
                linked_name($mother, $url)
            );
        elseif ($father)
            $str = paren("$ch $_of " . linked_name($father, $url));
        elseif ($mother)
            $str = paren("$ch $_of " . linked_name($mother, $url));
    }
    return $str;
}


function gname($gnum) {
    // takes gender number, returns localized gender name
    global $_Unknown, $_Male, $_Female, $_NslashA;
    if ($gnum==0) return span_type($_Unknown, "alert", "Gender unknown." );
    if ($gnum==1) return $_Male;
    if ($gnum==2) return $_Female;
    if ($gnum==9) return $_NslashA;
}

function yesno($yn) {
    // takes untyped value, returns localized yes or no
    global $_Yes, $_No;
    if ($yn == 't' || $yn == strtoupper('Y'))
        return $_Yes;
    else
        return $_No;
}

function char_conv($str) {
    global $_AP20;
    // converts some hard-to type html entities from text input

/*
I tried:
    $str = str_replace('--', '&#8212;', $str); // '--' -> mdash

but mdash is a very problematic character. It doesn't exist in iso 8859-1.
In UNICODE it's #8212, but in several Windows charsets it's placed at #151.
Cutting & pasting, even between random GNU/Linux applications, may put your
encoding in an illegal state. Don't use it.
*/

    $str = str_replace('<<<', '&laquo;<', $str);
    $str = str_replace('>>>', '>&raquo;', $str);
    $str = str_replace('<<', '&laquo;', $str);
    $str = str_replace('>>', '&raquo;', $str);
    $str = str_replace('1//2', '&frac12;', $str);
    $str = str_replace('1//4', '&frac14;', $str);
    $str = str_replace('3//4', '&frac34;', $str);
    // strip superfluous http marker from urls
    // sms: not superflous. 
    if (!$_AP20) { $str = str_replace('http:', '', $str); }
    return $str;
}

function note_to_db($str) {
    global $note_to_db_style,$_initials;
    if (!$str) return $str;
    //debug_log("==== START note_to_db style[$note_to_db_style] in[$str]\n");

/*
    $_initials = fetch_val("
        SELECT
            initials
        FROM
            user_settings
        WHERE
            username = current_user
    ");
*/

    $str = rtrim(char_conv($str));
    if ( $note_to_db_style == 'preserveLines' ) {
       $str = str_replace("'", '&apos;', $str); // avoid stupid quotes
       $str = str_replace("\r\n", "\n", $str);
       $str = str_replace("\r", "\n", $str); // 
       $str = str_replace("\n", '<br />', $str); // DON'T strip newlines
    }
    else {
        /* default as per Leif wants it */
       $str = str_replace("\n", '', $str); // strip newlines
       $str = str_replace("\r", '', $str); // I don't see why PHP needs this.
       // convert double linebreaks to paragraphs
       $str = str_replace('<br /><br />', '</p><p>', $str);
    }
    // expand "pseudo-tagging"
    $str = str_replace('<note>', '<span class="note">(', $str);
    $str = str_replace('</note>', " $_initials)</span>", $str);
    
    $str = pg_escape_string($str);
    //debug_log("==== END note_to_db style[$note_to_db_style] in[$str]\n\n");
    return $str;
}

function note_from_db($str) {
    global $note_to_db_style;
    //debug_log("==== START note_from_db style[$note_to_db_style] [$str]\n");
    if (!$str) return $str;
    // Format text from db for editing. Insert some "air"
    // newlines should be stripped by note_to_db() above at return time
    if ( $note_to_db_style == 'preserveLines' ) {
        # every <br/> to/from a carriage return
        $str = str_replace('<br />',"\n", $str); // for display
        $str = str_replace('<br/>',"\n", $str); // for display
    }
    else {
        /* default as per Leif wants it */
        $str = str_replace('<br />', "<br />\n", $str);
        $str = str_replace('</p><p', "</p>\n\n<p", $str);
        $str = str_replace('<ul>', "\n<ul>\n", $str);
        $str = str_replace('</p><ul', "</p>\n\n<ul", $str);
        $str = str_replace('</p><ol', "</p>\n\n<ol", $str);
        $str = str_replace('</ol>', "</ol>\n", $str);
        $str = str_replace('</ul>', "</ul>\n", $str);
        $str = str_replace('</li>', "</li>\n", $str);
    }
    //debug_log("==== END note_from_db style[$note_to_db_style] [$str]\n ======== \n\n");
    return $str;
}

function researcher_info($file) {
    // print researcher info at the end of a report
    $fh = fopen($file, 'r');
    echo "<p class=\"bmd\">";
    while ($line = fgets($fh)) {
        echo "$line<br />\n";
    }
    echo "</p>\n";
    fclose($fh);
}

function get_source_plain_text($n) {
    // get source text as plain text, eg. for titles
     return strip_tags(fetch_val("
        SELECT
            link_expand(source_text)
        FROM
            sources
        WHERE
            source_id = $n"
    ));
}

function conc($str, $prefix=' ') {
    if ($str)
        return $prefix . $str;
    else
        return '';
}

function bold($str) {
    return '<b>' . $str . '</b>';
}

function italic($str) {
    return '<i>' . $str . '</i>';
}

function sup($str) {
    return '<sup>' . $str . '</sup>';
}

function square_brace($str) {
    return '[' . $str . ']';
}

function curly_brace($str) {
    return '{' . $str . '}';
}

function paren($str) {
    return '(' . $str . ')';
}

/* .................................................... eac-cpf style outputs from xml (start) */

/* for eac-cpf span with localType; see useit_style.css for styling (localType attr is shown as a label) */
function span_type_eac($str, $cls, $after="") {
    if ($after) {
        return "<span localType=\"$cls\" aft=\"$after\">" . $str . '</span>';
    }
    else {
        return "<span localType=\"$cls\" >" . $str . '</span>';
    }
}

/* .................................................... eac-cpf style outputs from xml (end) */

/* $showattrs may be an array of attribute names; if omitted we get all attributes */
/* $xpn = xpath ie. element names of parent/caller(s) (null indicates root) */
function text_eac($xpn,$xmlfields, $type='', $showattrs=array('all'=>1) ) {

    /*
       Output to html arbitrary xml structures (calls itself for each element with children).
    */
if (isset($xmlfields[0])) {
    if     ($type=='para') { echo "<p>"; }
    elseif ($type=='list') { echo "<ul>"; }
    else                   { echo ""; }
    $theType = get_class($xmlfields[0]);
    if ($theType == 'SimpleXMLElement') {
        $newxpn = ""; $prevname = ""; $occ = 1;
        foreach( $xmlfields as $node ) {
           $nname = $node->getName();
           if ( $nname == $prevname ) { $occ++; } else { $occ = 1; }
           if ( $xpn ) { $newxpn = $xpn . "/". $nname; } else { $newxpn = $nname; }
           if ( $occ > 1 ) { $newxpn .= "[$occ]"; }
           $prevname = $nname;
           /* attributes */
           $attrs = " {";
           $na = 0;
           foreach($node->attributes() as $a => $b) {
               if ( array_key_exists($a, $showattrs) or array_key_exists('all', $showattrs) ) { $na++; $attrs .= $a.'="'.$b."\" "; }
           }
           if ( $na == 0 ) { $attrs = ""; } else { $attrs = "<span class=\"attributes\">$attrs"."}</span>"; }
           /* if children, recurse */
           if ( count($node->children()) > 0 ) {

               /* this element has children, so recurse */

               if     ($type=='para') { echo         span_type_eac($attrs, $nname) . "("; text_eac( $newxpn, $node->children(), $type, $showattrs); echo ")"; }
               elseif ($type=='list') { echo "<li>". span_type_eac($attrs, $nname);       text_eac( $newxpn, $node->children(), $type, $showattrs); echo "</li>"; }
               else                   { echo         span_type_eac($attrs, $nname) . "("; text_eac( $newxpn, $node->children(), $type, $showattrs); echo ")"; }
           }
           else {

               /* no children */

               $con = $node; 
               if ( preg_match( '/pass/', $nname) ) { $con = "******"; } // don't show passwords
               if     ($type=='para') { echo          span_type_eac($attrs.$con, $nname, "; "); }
               elseif ($type=='list') { echo "<li>" . span_type_eac($attrs.$con, $nname) . "</li>"; } // dbg: [xx$newxpn]
               else                   { echo          span_type_eac($attrs.$con, $nname, " "); }
           }
        }
    }
    else {

        /* we got passed something that's NOT an SimpleXMLElement */

        if     ($type=='para') { echo span_type_eac($xmlfields, $theType, "; "); }
        elseif ($type=='list') { echo "<li>" . span_type_eac($xmlfields, $theType ) . "</li>"; }
        else                   { echo span_type_eac($xmlfields, $theType, " "); }
    }

    if     ($type=='para') { echo "</p>"; }
    elseif ($type=='list') { echo "</ul>"; }
    else                   { echo ""; }
}
else {
    para("\$xmlfields[0] not set.");
}
}

function span_type($str, $cls, $help="") {
    if ($help) {
        return "<span class=\"$cls\" title=\"$help\">" . $str . '</span>';
    }
    else {
        return "<span class=\"$cls\">" . $str . '</span>';
    }
}

// sms added 24 Jan 2013
function pre($str, $type='') {
    $t = $type ? " class=\"$type\"" : '';
    return "<pre$t>" . $str . "</pre>\n";
}

function para($str, $type='') {
    $t = $type ? " class=\"$type\"" : '';
    return "<p$t>" . $str . "</p>\n";
}

function li($str, $type='') {
    $t = $type ? " class=\"$type\"" : '';
    return "<li$t>" . $str . "</li>\n";
}

function int_5($n) {
    return sprintf("%05d", $n);
}

function td($str) {
    return '<td valign="top">' . $str . '</td>';
}
function td_numeric($str) {
    return '<td class="numeric" valign="top"><code>' . $str . '</code></td>';
}

//#########################################################################
// data entry functions
//#########################################################################

function set_last_edit($person) {
    pg_query("UPDATE persons SET last_edit = NOW() WHERE person_id = $person");
}

function set_last_selected_source($source_id) {
    // in a future multi-user version, this code should be replaced
    // with a user-dependent setting, eg a cookie.
    pg_query("UPDATE user_settings SET last_selected_source = $source_id WHERE username = current_user");
}

function get_last_selected_source() {
    // in a future multi-user version, this code should be replaced
    // with a user-dependent setting, eg a cookie.
    return fetch_val("SELECT last_selected_source FROM user_settings WHERE username = current_user");
}

function set_last_selected_place($place) {
    pg_query("SELECT set_last_selected_place($place)");
}

function get_last_selected_place() {
    return fetch_val("SELECT place_fk FROM recent_places ORDER BY id DESC LIMIT 1");
}

function get_sort($id, &$text, $sort) {
    // parses sort order from text, returns sort order, modifies text
    // rewritten as wrapper for plgpgsql get_sort() function
    // note that $id is the parent id of a new source.
    $row = fetch_row_assoc("select * from get_sort($id, $sort, '$text')");
    $text = $row['string'];
    return $row['number'];
}

function add_source($person, $tag, $event, $source_id, $text, $sort=1) {
/*
Inserts sources and citations depending on input, returns current source_id
NOTE: To avoid breakage, NEVER call this routine outside of a transaction.
Update 2009-03-26: The major logic now has been moved to plpgsql, and this func
is left as a wrapper. Cf. ddl/functions.sql.
*/
    if (!$source_id && !$text) // don't bother if nothing has been entered.
        return 0;
    else {
        $text = note_to_db($text);
        return fetch_val("SELECT add_source($person, $tag, $event, $source_id, '$text', $sort)");
    }
}

function add_participant($person, $event) {
    pg_query("SELECT add_participant($person, $event)");
}

function add_birth($person, $date, $age, $source) {
    // 2009-04-03: the logic has been moved to the plpgsql function add_birth.
    return fetch_val("SELECT add_birth($person, '$date', $age, $source)");
}

function list_participants($event) {
    $handle = pg_query("SELECT person_fk, sort_order, is_principal
                            FROM participants
                            WHERE event_fk = $event
                            ORDER BY sort_order");
    while ($row = pg_fetch_row($handle)) {
        $bp = $row[2] == 'f' ? 'B' : '';
        $p_list[] = square_brace($bp . $row[1])
            . linked_name($row[0], './family.php');
    }
    return join($p_list, ', ');
}

function get_participant_note($p, $e) {
    return fetch_val("SELECT COALESCE(
                        (SELECT link_expand(part_note)
                        FROM participant_notes
                        WHERE person_fk=$p AND event_fk=$e), '')");
}

function node_details($e, $r, $s, $u) {
    // shorthand summary for number of events, relations, subnodes / unused
    // subnodes connected to this node
    $str = " ($e-$r-$s";
    if ($u)
        $str .= "/$u";
    $str .= ")";
    return $str;
}

function list_mentioned($node, $hotlink=0) {
    global $language, $app_path, $_edit, $_delete;
    echo "<ol>\n";
    $handle = pg_query("
        SELECT
            per_id,
            get_role(role_fk,'$language') AS rolle,
            person_fk,
            get_surety(surety_fk,'$language') AS surety,
            s_name,
            link_expand(sl_note) AS note
        FROM
            source_linkage
        WHERE
            source_fk = $node
        ORDER BY
            role_fk,
            per_id
    ");
    while ($row = pg_fetch_assoc($handle)) {
        echo '<li>' . $row['rolle'] . ': ';
        echo '«' . $row['s_name'] . '»';
        if ($row['person_fk'])
            echo conc(curly_brace($row['surety']))
                . conc(linked_name($row['person_fk'], "$app_path/family.php"));
            if (has_parents($row['person_fk']))
                echo conc(child_of($row['person_fk']));
        if ($row['note'])
            echo ': ' . $row['note'];
        if ($hotlink) {
            echo conc(paren(
                to_url("$app_path/forms/linkage_edit.php",
                        array(
                            'node'      => $node,
                            'id'        => $row['per_id']
                        ), $_edit)
                . ' / '
                . to_url("$app_path/forms/linkage_delete.php",
                        array(
                            'node'      => $node,
                            'id'        => $row['per_id']
                        ), $_delete)
                ));
        }
        echo "</li>\n";
    }
    echo "</ol>\n";
/* sms: don't think we need this. See source_manager.php which gens a link to linkage_add.php
    if ($hotlink) {
        echo '<p>'
            . to_url("$app_path/forms/linkage_add.php",
                    array('node' => $node), $_Add_link)
            . "abc";
        echo "</p>\n";
    }
*/

}

?>
