(: common user interface elements for fas restxq services, using twitter bootsrap/angular js :)

module namespace fui = "http://fas/fui";

import module namespace request = "http://exquery.org/ns/request";
import module namespace session = "http://basex.org/modules/session";
import module namespace fu = "http://fas/fu" at '/usr/local/lib/xquery/fu';
import module namespace fenv = "http://fas/fenv" at "/usr/local/lib/xquery/fenv";

declare variable $fui:users_who_can_update := $fenv:users_who_can_update;
declare variable $fui:staffsearch := "http://dev.founders-and-survivors.org/prot/staffsearch";
declare variable $fui:convictsearch := concat($fui:staffsearch,"/convict/chain/");
declare variable $fui:llkt_server := "http://dev.founders-and-survivors.org/pub/util/llkt/";
declare variable $fui:bootsrap_server := "http://dev.founders-and-survivors.org/bootstrap/";
declare variable $fui:LF := "&#10;";
declare variable $fui:xmlmaster_for_new_links := "klaatu";
declare variable $fui:dbname_for_new_links := "diggers_genealogy";
declare variable $fui:mypath := "/restxq";
declare variable $fui:fasgaz-baseuri := "http://y1.founders-and-survivors.org/restxq/ap20/fasgazFT";
declare variable $fui:fasgaz-service := $fui:fasgaz-baseuri || "?pn=";
declare variable $fui:perl-fasgaz-service := "http://dev.founders-and-survivors.org/pub/fasgaz/find/"; 
declare variable $fui:ert-service := concat($fui:mypath,"/ap20/ert");
declare variable $fui:id-service := concat($fui:mypath,"/id");
declare variable $fui:updatelinkage-service := concat($fui:mypath,"/updatelinkage");
declare variable $fui:possible_convict_if_born_before := 1855;
declare variable $fui:noTasBirthsBefore := 1838;
declare variable $fui:noTasBirthsAfter := 1899;
declare variable $fui:ERTBirthsAfter := 1855;
declare variable $fui:PmMarriagesBefore := 1858;
declare variable $fui:JavascriptFragmentsPath := "/var/www/bootstrap/js/";
declare variable $fui:defaultGazetteId := "gaz2010.xml"; (: default to Geosciences Australia OLD Gazette :)
declare variable $fui:defaultGazetteState := "TAS";
declare variable $fui:defaultGazetteCountry := "AU";
declare variable $fui:defaultMarker := "/ol/img/marker-gold.png";
declare variable $fui:extraMarker := "/ol/img/marker.png";
declare variable $fui:minOfAgeMotherOrFather := 14;
declare variable $fui:mapHeight := 388;
declare variable $fui:mapHeightDiv := $fui:mapHeight - 4;

(:
  <link rel="stylesheet" type="text/css" href="/css/default.css"/>
  <link rel="stylesheet" type="text/css" href="/css/useit_style.css"/>
:)

declare function fui:is-human() as xs:string? {
   (: Note in apache ip restricted requests to /rx/ /rxq/; humans login and access via /restxq/ and /rest/ :)
   if (matches(request:path(),"^/rest")) then "human" else ""
};

declare function fui:llkt2html($xmlid as xs:string*) as element()* {
if ($xmlid) then
   let $llkt-uri := concat($fui:llkt_server,$xmlid)
   let $llkt := http:send-request(<http:request method='get'/>,$llkt-uri)[2]
   return <div id="LKT"><h5>LKT linkage for {$xmlid}:</h5>
          <ul>
          {
             for $id in $llkt//link/text()
             return if ($id=$xmlid) then <li>{$xmlid} [HERE]</li>
                    else <li>{fui:idservice($id,"")}</li>
          }</ul></div>
else ()
};

(: kill off quotes and other nasty chars for data we might need as attributes. eg trCmmt :)
declare function fui:killbadstuff ($s as xs:string*) as xs:string* {
   for $str in $s
   let $killquotes := replace($str,"[^\?:, a-zA-Z;\.-_\(\)\[\]0-9$#@%\*\+=/]+","")
   where $killquotes
   return $killquotes
};

declare function fui:errorPage ( $msg as xs:string*, $auth as element() ) {
  (: log the error to file :)
  let $reference := string-join((string(current-dateTime()),environment-variable("FASHOST"),$auth/uid/text())," : ")
  let $log := string-join(($reference,$msg,request:uri(),request:query(),"Referer",request:header("Referer"))," : ")||$fui:LF
  let $x := if (file:exists($fenv:ERRORLOG)) then file:append($fenv:ERRORLOG,$log) else file:write($fenv:ERRORLOG,$log)
(:


  let $q := if (request:header("X-Forwarded-Host")) then replace(request:uri(),request:header("Host"),request:header("X-Forwarded-Host"))
            else request:uri()
  let $parms := if ($q) then concat("?",request:query()) else ()
  let $referer := if ( request:header("Referer") ) then $fui:LF || "Referer:" || request:header("Referer") else ()
  return (<hr/>,<pre style="font-size:small;">{current-dateTime() || " "} {fui:session_recid()} {$fui:LF || $q || $parms || $referer }</pre>)



:)
return
<html xmlns="http://www.w3.org/1999/xhtml">{ fui:incbootstrap_head( "ERROR MESSAGE" ) }
<body>
{ fui:tab-navigation-row( "6",(<a onclick="history.back();">Back</a>) ) }
<div class="error">
<h5>Erroneous request!</h5>
{for $m in ($msg,$reference) return <p>{$m}</p>}</div>
<hr/>
{fui:whenwhat()}
{fui:bootstrap_foot()}
</body>
</html>
};

declare function fui:scriptify ( $script_doc as element(text) ) {
   (: passed a text element with cdata return the processing needed to preserve embedded xml :)
   let $script_string := string($script_doc)
   return parse-xml($script_string)
};


declare function fui:notAuthorised ( $r as element() ) {
   if (local-name($r)="html") then 1 else 0
};

declare function fui:checkAuth ( $authUsers as xs:string*, $authTokensStr as xs:string* ) as element()* {
    let $auth := fui:auth_pubtkt()
    let $user := $auth/uid/text()
    let $tokens := $auth/tokens/text()
    let $authTokens := tokenize($authTokensStr,",")
    let $checkAuthInfo := <checkAuthRequires users="{$authUsers}" authTokens="{$authTokensStr}"></checkAuthRequires>

let $x := file:write("/tmp/1","$auth="||$auth||"&#10;$user="||$user||"&#10;$tokens="||$tokens||" $authTokens="||$authTokensStr||"&#10;$authUsers="||string-join($authUsers," "))

    let $isAuthorised :=
        if ($user)
        then if (count($authTokens))
             then for $t in tokenize($tokens,",")
                  where $t=$authTokens
                  return $t
             else if ($user=$authUsers)
                  then $user
                  else ()
        else data($auth/@machineIsAuthorised)  (: from fui:auth_pubtkt checking forwarded host header against 00config authorised-clients :)
    return if (count($isAuthorised)) 
           then <div class="hide" id="auth" user="{$user}" tokens="{$tokens}">{$auth}{$checkAuthInfo}</div>
           else <html><body><p style="color:red;font-weight:bold;">User {$user} from {request:header("X-Forwarded-For")} not authorised!</p>{fui:whenwhat()}</body></html>
   (: <p>Yo {$user} from {environment-variable("FASHOST")}! Your authorisations: {$tokens}</p> :)
};

declare function fui:naaImg ( $barcode as xs:string ) as element()* {
    let $bc := replace($barcode,"^ert0+","")
    let $icon := <img src="/img/primary-source.png" title="Primary source: National Archives Service Record Barcode: {$barcode}"/>
    return <a target="_blank" title="Barcode: {$barcode}" href="http://recordsearch.naa.gov.au/scripts/Imagine.asp?B={$bc}&amp;I=1&amp;SE=1&amp;P=CHECKED">{$icon}</a>
};

(: this one uses the free text gaz :)
declare function fui:incAp20FTGeocoderIframe ($hideOrShow as xs:string, $fuzzy as xs:string, $using_placenames as xs:string* ) as element()* {
   let $mapuri := fui:tasPlaces2fasgazURIonly ($fuzzy, $using_placenames)
   let $mapuri := replace($mapuri,"fasgaz\?","fasgazFT?")
   return
   (
   <input type="button" value="FT-Map" title="Click to show/hide map of placenames: {$using_placenames}&amp;fuzzy={$fuzzy}" onclick="$('#mymap').toggleClass('hide')" />,
   <iframe class="{$hideOrShow}" width="100%" height="{$fui:mapHeight}" id="mymap" src="{$mapuri}" />
   )
};
declare function fui:incAp20GeocoderIframe ($hideOrShow as xs:string, $fuzzy as xs:string, $using_placenames as xs:string* ) as element()* {
   let $mapuri := fui:tasPlaces2fasgazURIonly ($fuzzy, $using_placenames)
   return
   (
   <input type="button" value="Map" title="Click to show/hide map of placenames: {$using_placenames}&amp;fuzzy={$fuzzy}" onclick="$('#mymap').toggleClass('hide')" />,
   <iframe class="{$hideOrShow}" width="100%" height="{$fui:mapHeight}" id="mymap" src="{$mapuri}" />
   )
};

declare function fui:processPlacenames ( $placenames as xs:string* ) as xs:string* {
  for $pn in distinct-values($placenames)
  where not(matches($pn," St(reet)?","i"))
  return if (matches($pn,",")) then $pn else concat($pn,",",$fui:defaultGazetteState)
};

declare function fui:tasPlaces2fasgazURIonly ( $fuzzy as xs:string, $placenames as xs:string* ) as xs:string {
  let $all_placenames := fui:processPlacenames($placenames)
  let $n_all_placenames := count($all_placenames)
  let $placenames := string-join($all_placenames,":")
  return $fui:fasgaz-service || $placenames || "&amp;fuzzy=" || $fuzzy
};

declare function fui:tasPlaces2fasgaz ( $placenames as xs:string* ) {
  let $all_placenames := fui:processPlacenames($placenames)
  let $n_all_placenames := count($all_placenames)
  let $placenames := string-join($all_placenames,":")
  let $placenamesLabel := concat($n_all_placenames, " found placenames")
  return fui:fasgaz_uri_xq($placenames, $placenamesLabel, 0, "buffer")
};

declare function fui:fasgaz_uri_xq ( $placename as xs:string, $anchor_text as xs:string*, 
                                     $n2mark as xs:integer, $iframe_target as xs:string* ) {
    let $anchor_text := if ($anchor_text) then $anchor_text else $placename
    let $pass_n2mark := if ($n2mark = 0 ) then "" else ":StyleMARK"||xs:string($n2mark)
    let $uri := concat($fui:fasgaz-service,$placename,$pass_n2mark)
    return if ($iframe_target)
         (:  then <input type="button" value="Map places" onclick="{$iframe_target}.location.href='{$uri}'"/> :)
                (: see http://www.dyn-web.com/tutorials/iframes/hidden/demo.php :)
           then <a target="buffer" href="{$uri}" title="View place [{$placename}] on map"
                   onclick="return dw_Loader.load(this.href, 
                     'buffer', // iframe id
                     'display', // id of div where iframe contents displayed
                     ifrmCallback // function to call after contents transferred to div
                     )"
                >{$anchor_text}</a>
           else <a target="_new" href="{$uri}" title="View place [{$placename}] on map">{$anchor_text}</a>
};

declare function fui:fasgaz_post ( $placename as xs:string, $anchor_text as xs:string*, $n2mark as xs:integer ) {
    let $anchor_text := if ($anchor_text) then $anchor_text else $placename
    let $pass_n2mark := if ($n2mark = 0 ) then "" else ":StyleMARK"||xs:string($n2mark)
    let $uri := concat($fui:perl-fasgaz-service,$placename,$pass_n2mark,"?html") (:old:)
    let $uri := concat($fui:fasgaz-service,$placename,$pass_n2mark) (:new:)
    return <button onclick="window.location.href='{$uri}';">{$anchor_text}</button>
};

declare function fui:fasgaz_uri ( $placename as xs:string, $anchor_text as xs:string*, $n2mark as xs:integer ) {
    let $anchor_text := if ($anchor_text) then $anchor_text else $placename
    let $pass_n2mark := if ($n2mark = 0 ) then "" else ":StyleMARK"||xs:string($n2mark)
    let $uri := concat($fui:perl-fasgaz-service,$placename,$pass_n2mark,"?html") (:old:)
    let $uri := concat($fui:fasgaz-service,$placename,$pass_n2mark) (:new:)
    return <a href="{$uri}" title="View place [{$placename}] on map">{$anchor_text}</a>
};

declare function fui:proxiedHostUri () as xs:string {
   let $server := if (request:header("X-Forwarded-Host")) then request:header("X-Forwarded-Host") else request:header("Host")
   return "http://" || $server
};

(: stuff we always use -- jquery, jquery.xpath.js and a minimal default css which includes the yggdrasil css :)
(: NOTE: using <base href="{$base}"/> breaks all releative uri's :)
declare function fui:common_jquery ($here as xs:string* ) as  node()* {
   let $base := fui:proxiedHostUri()
   return
  (<!-- fui:common_jquery -->,
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>,
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>,
  <meta name="apple-mobile-web-app-capable" content="yes"/>,
  <link rel="stylesheet" type="text/css" href="{$here}/css/default.css"/>,
  <script type="text/javascript" src="{$here}/bootstrap/js/jquery.js"></script>,
  <script type="text/javascript" src="{$here}/bootstrap/js/jquery.xpath.js"></script>)
};

declare function fui:xqib_head ( $title as xs:string*, $javascripts ) as element()* {
let $here := fui:proxiedHostUri()
let $incJs := <script type="text/javascript" src="{$here}/bootstrap/js/sms/gaz-frag0.js"></script>
let $x_incJs := string($incJs)
let $js := $javascripts
return
<head>
  <title>{ $title }</title>
  {fui:common_jquery($here)}
  <script type="text/javascript" src="{$here}/bootstrap/xqib/mxqueryjs/mxqueryjs.nocache.js"></script>
  {$js}
</head>
};

declare function fui:incbootstrap_head ( $title as xs:string* ) as element()* {
let $here := fui:proxiedHostUri()
return
<head><!-- fui:incbootstrap_head -->
  <title>{ $title }</title>
  {fui:common_jquery($here)}
  <link href="{$here}/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="screen" />
  {fui:myJavascripts()}
</head>
};

declare function fui:bootstrap_head ( $title as xs:string* ) as element()* {
let $here := fui:proxiedHostUri()
return
<head><!-- fui:bootstrap_head -->
  <title>{ $title }</title>
  {fui:common_jquery($here)}
  {fui:myJavascripts()}
</head>
};

declare function fui:inline_css_for_xml ( $color as xs:string* ) as element() {
  let $color := if ($color) then $color else "red"
  return <style><!-- inline css for rendering xml -->
<![CDATA[
.attribs {font-style:italic; font-size: small; color: brown; }
[locType]:before { content: attr(locType) ":"; 
                     padding: 2pt; background: ]]>{$color}<![CDATA[; color: white; font-style:italic; font-size: small; }
[locType]:after { content: attr(aft) }
]]>
  </style>
};

declare function fui:myJavascripts ( ) as element()* {
   let $here := fui:proxiedHostUri()
   return <script src="{$here}/bootstrap/js/sms/form-field-set.js"></script>
};

declare function fui:xxxmyJavascripts ( ) as element()* {
   <script><![CDATA[

function myFun (fieldname,s)
{
    // alert ("myFun fieldname=["+fieldname+"] s=["+s+"]");
    var myform = $('#form1',window.parent.document).contents();
    var fstr = 'input[name='+fieldname+']';
    if ( s ) {
      if ( fieldname == "barcode") { 
         // set barcode and submit the form
         bc = myform.find('input[name=barcode]').val(s); 
         var scope1 = myform.find('input[name=db_ert]').prop('checked', true);
         var scope2 = myform.find('input[name=db_if]').prop('checked', true);
         var sub = myform.find('input[name=submit]').click(); 
         //alert("after submit");
      }
      if ( fieldname == "name") { 
         // set form to sn/fn of the passed name
         var parts = s.split(/ *, */);
         sn = myform.find('input[name=surname]').val(parts[0]); 
         fn = myform.find('input[name=forename]').val(parts[1]); 
         // alert ("sn=["+sn+"]");
      }
    }
    //alert("hello passed s="+s+" if="+iframe+" bc="+bc);
    return false;
}
]]></script>
};

declare function fui:bootstrap_foot ( ) as element()* {
   let $here := fui:proxiedHostUri()
   return <script src="{$here}/bootstrap/js/bootstrap.min.js"></script>
};

declare function fui:tab-navigation-row ( $spansize as xs:string*, $items as element()* ) as element() {
   <div class="container">  
      <div class="row">  
         <div class="span{$spansize}">  
            <ul class="nav nav-tabs">  
{
   for $item at $i in $items
   let $isActive := if ( $i = 1 ) then <x class="active"/> else ()
   return <li>{$isActive/@*}{$item}</li>
}
            </ul>  
         </div>  
      </div>  
</div>
};

(: 
$("#form1").contents().find("#f1").name("barcode")
:)
declare function fui:session_recid () as element()* {
   let $s := session:get("ertid")
   let $bc := replace($s,"ert0*","")
   return if ($s) then (<button onclick="javascript:myFun('barcode','{$bc}');" title="Redo the search for this digger.">ert{$bc}</button>) else ()
};

declare function fui:whenwhatauth ($auth as element()) as element()* {
  let $whenwhat := fui:whenwhat()
  let $ui := <input type="button" value="AuthInfo" onclick="$('#auth').toggleClass('hide')" />
  let $ui := <p style="font-size:small;"><a href="javascript:$('#auth').toggleClass('hide')">[ User info ]</a>
             </p>
  let $genBy := if ($auth/uid/text()=($fenv:system_administrators))
                then <pre style="font-size:small;">Sysadmin user: {$auth/uid/text()} Host: {environment-variable('FASHOST')} GeneratedBy: {static-base-uri()}</pre>
                else ()
  let $auth := if (local-name($auth)="auth") then <div id="auth" class="hide">{$auth}</div> else $auth
  return ($whenwhat,$ui,$auth,$genBy)
};

declare function fui:whenwhat () as element()* {
  let $q := if (request:header("X-Forwarded-Host")) then replace(request:uri(),request:header("Host"),request:header("X-Forwarded-Host"))
            else request:uri()
  let $parms := if (request:query()) then concat("?",request:query()) else ()
  let $referer := if ( request:header("Referer") ) then $fui:LF || "Referer: " || request:header("Referer") else ()
  return (<hr/>,<pre style="font-size:small;">{current-dateTime() || " "} {fui:session_recid()} {$fui:LF || "Request: " || $q || $parms || $referer }</pre>)
};

(: NEW gazetter search: passed multiple placenames, defaulting to tas, using free text gazFT :)


declare function fui:search_gazFT ($fuzzy as xs:string, $places as xs:string*) as element()* {
  let $finds := for $place at $j in $places
                let $parts := fui:parse_name_state_country($place)
                let $name  := $parts[1]
                let $state := $parts[2]
                let $ctry  := $parts[3]
                let $eventKind  := $parts[4]
                let $searchFor := concat($name,",",$state)
                return if ($ctry="AU")
                       then fui:search_gazFT_one($fuzzy,$searchFor,$j,$eventKind)
                       else <error>Unsupported country=[{$ctry}]. Please add gazette database/logic to fui:search_gazFT</error>
  return $finds
};

declare function fui:parse_name_state_country ( $parm1 as xs:string*) as xs:string* {
      let $parts := tokenize($parm1,",")
      let $nameParts := tokenize($parts[1],"@")
      let $optionalEventType := if (count($nameParts)=2) then normalize-space($nameParts[1]) else ""
      let $name := if (count($nameParts)=2) then $nameParts[2] else $parts[1]
      let $name := fui:capitalizeWords(normalize-space($name))
      let $state := if ($parts[2]) 
                    then if ($parts[2]="*") then "" (: asterisk indicates search in any state :)
                         else upper-case(normalize-space($parts[2])) 
                    else $fui:defaultGazetteState   (: the default state :)
      let $ctry := if ($parts[3]) 
                   then if ($parts[3]="*") then ""  (: asterisk indicates search in any country :)
                        else  upper-case(normalize-space($parts[3])) 
                   else $fui:defaultGazetteCountry
      return ($name,$state,$ctry,$optionalEventType)
};


declare function fui:search_gazFT_one ($fuzzy as xs:string, $place as xs:string*, $seq as xs:integer, $eventKind as xs:string*) as element()* {
   let $namestate := tokenize($place,",")
   let $name := fui:capitalizeWords(normalize-space($namestate[1]))
   return if (not($name)) then <error type="noname" name="{$place}"/> else

   let $state := if ($namestate[2]) then normalize-space($namestate[2]) else $fui:defaultGazetteState
   let $searchFor := concat($name,",",$state)

   (: search without fuzzy, then fuzzy if none found :)
   let $match := if ($fuzzy)
                 then ft:search("gazFT", $searchFor, map { "mode" := "all", "fuzzy" := true() })
                 else ft:search("gazFT", $searchFor, map { "mode" := "all" })
   let $fuzzy2 := if (not($fuzzy) and not($match)) then "fuzzy" else $fuzzy
   let $match := if (not($fuzzy) and not($match))
                 then ft:search("gazFT", $searchFor, map { "mode" := "all", "fuzzy" := true() })
                 else $match
   (: if still none, do all separate words :)
   let $fuzzy3 := if ($fuzzy2="fuzzy" and not($match)) then "fuzzy-words" else $fuzzy2
   let $match := if ($fuzzy2="fuzzy" and not($match)) 
                 then let $words := tokenize($searchFor,"[, \-]+")
                      return ft:search("gazFT", $words, map { "mode" := "all", "fuzzy" := true() })
                 else $match 

   (: get and sort and score the idx elements :)
let $x := file:write("/tmp/gazFTa",$match)
   let $words := tokenize($name,"[, \-]+")
   let $match := for $m in $match
                 let $nameNostate := replace($m,",[^,]+$","")
                 let $idxelement := $m/..
                 let $score := for $w in $words
                               return ft:score( $nameNostate contains text {$w} using fuzzy )
                 let $score := sum($score)
                 order by data($idxelement/@sort),$score descending,data($idxelement/@fc)
                 return <ftidx score="{$score}">{$idxelement/@*}{$m}</ftidx>
let $x := file:write("/tmp/gazFTb",$match)

   (: return just the first match (exact if possible), they've been sorted in priority order :)
   let $finalMatch := ($match[text()=$searchFor])[1]
   let $fuzzy3 := if ($finalMatch) then "exact" else $fuzzy3
   let $match := if ($finalMatch) then $finalMatch
                 else for $m in distinct-values($match/text())
                      return ($match[text()=$m])[1]

(:
   (: finally, score multiples :)
   let $match := if (count($match)>1) 
                 then for $m in $match
                      let $nameNostate := replace($m,",[^,]+$","")
                      let $score := for $w in $words
                                    return ft:score( $m/text() contains text {$w} using fuzzy )
                      let $score := sum($score)
                      order by $score descending
                      return <ftidx score="{$score}">{$m/@*}{$m/text()}</ftidx>
                 else $match
:)

let $x := file:write("/tmp/gazFTc",$match)
   (: return the results as an element :)
   return <found fuzzy="{$fuzzy3}" j="{$seq}" n="{count($match)}" name="{$name}" state="{$state}" eventKind="{$eventKind}">{$match}</found>
};

 
(: OLD gazetter search: passed multiple placenames, defaulting to tas 
   fuzzy = 'contains' : contains text {$name} using fuzzy
           else       : exact

   ############################### Modify for other Gazettes as needed
:)
declare function fui:search_gaz ($fuzzy as xs:string, $places as xs:string*) as element()* {
   for $parm1 at $i in $places
      let $parts := fui:parse_name_state_country($parm1)
      let $name  := $parts[1]
      let $state := $parts[2]
      let $ctry  := $parts[3]
      let $eventKind  := $parts[4]
      let $searchIn      := if ($ctry=$fui:defaultGazetteCountry) then collection("gaz/gaz2010.xml") else ()
      let $searchIn_str  := if ($ctry=$fui:defaultGazetteCountry) then 'collection("gaz/gaz2010.xml")' else ()
      let $gazCategories := if ($ctry=$fui:defaultGazetteCountry) then collection("gaz/gaz2010-fc.xml") else ()

let $filename := "/tmp/xx_01b_"||$name||"-"||$state

let $x := file:write($filename,"i="||$i||" fuzz="||$fuzzy||" $ctry="||$ctry||" state="||$state||" name:"||$name||" $searchIn_str="||$searchIn_str||" p[1]="||$parts[1]||" p[2]="||$parts[2]||" p[3]="||$parts[3]||" $parm1="||$parm1)
      (: let $x := file:write-text("/tmp/gg"||$i,$name) :)

   return if ($name) 
          then 
               let $found_xq := 
                   if ($state)
                   then switch ($fuzzy)
                        case "contains"   return $searchIn_str||'//gaz[name/text() contains text "'||$name||'" using fuzzy and state/text()=$state]'
                        case "startswith" return $searchIn_str||'//gaz[starts-with(., "'||$name||'") and state/text()=$state]'
                        default           return $searchIn_str||'//gaz[name/text()="'||$name||'" and state/text()=$state]'
                   else switch ($fuzzy)
                        case "contains"   return $searchIn_str||'//gaz[name/text() contains text "'||$name||'" using fuzzy]'
                        case "startswith" return $searchIn_str||'//gaz[name/text()[starts-with(., "'||$name||'")]]'
                        default           return $searchIn_str||'//gaz[name/text()="'||$name||'"]' 

let $x := file:append($filename,"&#10;found_xq="||$found_xq||"&#10;")

               let $found := 
                   if ($state)
                   then switch ($fuzzy)
                        case "contains"   return $searchIn//gaz[name/text() contains text {$name} using fuzzy and state/text()=$state]
                        case "startswith" return $searchIn//gaz[starts-with(., $name) and state/text()=$state]
                        default           return $searchIn//gaz[name/text()=$name and state/text()=$state] 
                   else switch ($fuzzy)
                        case "contains"   return $searchIn//gaz[name/text() contains text {$name} using fuzzy]
                        case "startswith" return $searchIn//gaz[name/text()[starts-with(., $name)]]
                        default           return $searchIn//gaz[name/text()=$name] 
let $x := file:append($filename,"--- 1 name["||$name||"]&#10;")
let $x := file:append($filename,$found)

               let $fuzzy2 := if (not($found) and not($fuzzy="contains")) then "contains" else $fuzzy
               let $fuzzy2 := if ($fuzzy2) then $fuzzy2 else "exact"

               let $found := if (not($found) and not($fuzzy="contains"))
                             then (: try contains so we get fuzzy search :)
                                  if ($state)
                                  then $searchIn//gaz[name/text() contains text {$name} using fuzzy and state/text()=$state]
                                  else $searchIn//gaz[name/text() contains text {$name} using fuzzy]
                             else $found

let $x := file:append($filename,"--- 2&#10;")
let $x := file:append($filename,$found)

               let $found_concise := if ($fuzzy) then () else $found[inC/text()="Y"] 
               let $found := if (count($found_concise)) then $found_concise else $found

               (: get categories and fc descriptions :)
               let $categories := if (not($found)) then () else
                   let $fcs := for $f in $found return $found/fc/text()
                   return for $f in distinct-values($fcs)
                          let $fcDesc := $gazCategories//item[label/text()=$f]/desc
                          let $fcCat := $fcDesc/ancestor::item[@sort]
                          let $cat := <category>{$fcCat/@sort}{$fcCat/label/text()}</category>
                          return <add fc="{$f}">{$fcDesc}{$cat}</add> 

let $x := file:append($filename,$categories)

               (: if duplicate names, order them by prioritised feature category and type :)

               let $found := for $f in $found
                             let $add := $categories[@fc=$f/fc/text()]
                             (: let $priority := if ($f/fc/text()=("SUB","POPL")) then "1" else "2" :)
                             let $priority := $add/category/@sort
                             order by $priority, $f/fc/text()
                             return <gaz>{$f/@*}{$f//child::*}{$add//child::*}</gaz>

               let $diffNames := for $n in $found return $n/name/text()
               let $found := for $f in distinct-values($diffNames)
                             return ($found[name/text()=$f])[1]

let $x := file:append($filename, <found fuzzy="{$fuzzy2}" j="{$i}" n="{count($found)}" name="{$name}" state="{$state}">{$found}</found>)

               return <found fuzzy="{$fuzzy2}" j="{$i}" n="{count($found)}" name="{$name}" state="{$state}">{$found}</found>
          else <error j="{$i}" type="noname" name="{$parm1}"/>
};

(: see if a link exists :)
declare function fui:have_linkage( $xid1 as xs:string*, $linked2type as xs:string*) as xs:string* {
  for $rectype in $linked2type
  let $link_exists := collection($fui:dbname_for_new_links)//link[key/text()=$xid1 and key[starts-with(text(),$rectype)]]
  return if (count($link_exists)=0) then () 
         else collection($fui:dbname_for_new_links)//link[key/text()=$xid1 
                                                     and key[starts-with(text(),$rectype)]]/key[starts-with(text(),$rectype)]/text()
};
declare function fui:have_linkage_html ( $xid1 as xs:string*, $linked2type as xs:string*) as element()* {
  let $link_exists := for $rectype in $linked2type
                      return collection($fui:dbname_for_new_links)//link[key/text()=$xid1 and key[starts-with(text(),$rectype)]]
  return ( <span style="font-size:small;"><a href="javascript:$('#linkageinfo').toggleClass('hide')">[Linkage info]</a></span>,
             <pre class="hide" id="linkageinfo">{
                for $link in $link_exists
                let $keys := string-join($link//key/text()," ")
                let $pn := if ($link//policeNum) then concat(" PoliceNumber: ",$link//policeNum) else ()
                let $cmmt := if ($link//comment) then (<br/>,concat("Comment: ",$link//comment)) else ()
                return concat("(",$link/@n,") user:",$link/@user," on host:",$link/@host," at ",$link/@when," linked keys:",$keys,$pn,$cmmt)
             }</pre>)
};

(: some utilities to assist updating :)

declare function fui:next_document_id ( $dbname as xs:string ) as xs:string {
    let $nextdoc := count( collection($dbname) ) + 1
    let $docid := "L" || format-number($nextdoc,"0000")
    return concat($docid,".xml")
};


(: some utilities to assist dev/debugging :)

declare function fui:auth_pubtkt () as element()* {
   let $ip := request:header("X-Forwarded-For")
   let $ip := if ($ip) then $ip else request:header("Host")
let $test := string-join( (request:header-names())," : ") 
   let $machineIsAuthorised := $fenv:system_admin_ipnos[.=$ip]
   let $machineIsAuthorised := if ($machineIsAuthorised) then <x machineIsAuthorised="yes"/> else ()
   return
   <auth fromip="{$ip}" test="{$test}">{$machineIsAuthorised/@*}{
         for $p in tokenize(request:cookie("auth_pubtkt"),"%3B")
         let $v := tokenize($p,'%3D')
         where ( $v[1] = ("uid","cip","tokens"))
         return element {$v[1]} { replace($v[2], "%2C", ",") }
   }</auth>
};

declare function fui:printenv () as element()* {
<pre>
ENVIRONMENT VARIABLES:{$fui:LF}
    request:query=[ {request:query()} ]{$fui:LF}
    request:uri  =[ {request:uri()} ]{$fui:LF}
    request:cookie-names =[ {request:cookie-names()} ]{$fui:LF}
    { for $h in request:cookie-names() return "cookie: name[" || $h || "] contents=[" || request:cookie($h)  || "]" || $fui:LF }
    request:header-names =[ {request:header-names()} ]{$fui:LF}
    { for $h in request:header-names() return "header:" || $h || "=[" || request:header($h)  || "]" || $fui:LF }
    { for $v in available-environment-variables() return concat($v,"=",environment-variable($v),'&#10;') }
</pre>
};

(: low level parameter handling :)

declare function fui:e ( $p as element()*, $e as xs:string* ) as xs:string* { $p/*[local-name()=$e]/text() };

declare function fui:capitalizeWords ($s as xs:string*) as xs:string* {
   let $words := for $w in tokenize(normalize-space($s)," ") return fui:capitalize-first($w)
   return string-join( $words," " )
};

declare function fui:capitalize-first ( $arg as xs:string? )  as xs:string? {
   concat(upper-case(substring($arg,1,1)), substring($arg,2))
 };

declare function fui:safeparm ( $value as xs:string* ) as xs:string* {
    (: legal characters :)
    replace( $value, "[^a-zA-Z0-9\._\-\(\)/ ,]*", "" )
};

declare function fui:qs_parms_decode () as element()* {
  if (request:query()) 
  then for $p in tokenize( request:query(),"&amp;" )
       let $attrval := tokenize($p,"=")
       return <f name="{$attrval[1]}">{fui:safeparm($attrval[2])}</f>
  else ()
};

(: ......................................................... simple html display specific to types of records :)
declare function fui:idserviceLk ( $xmlid as xs:string*, $anchor as xs:string, $linkedid as xs:string ) as element()* {
  if ($xmlid) then <a href="{$fui:id-service}/{$xmlid}/link/{$linkedid}">{$anchor}</a> else ()
};

declare function fui:idservice ( $ids as xs:string*, $anchor as xs:string* ) as element()* {
  for $xmlid in $ids
  return if ($xmlid) 
         then let $a := if ($anchor) 
                        then $anchor 
                        else let $info := fu:x2info($xmlid)
                             return if ($info) then $info else $xmlid
              return <a title="{$xmlid}" href="{$fui:id-service}/{$xmlid}">{$a} </a>
         else ()
};

(:                                                                                               possible births as table :)

declare function fui:kgb2html-table ( $xmlid_1 as xs:string*, 
                                      $originalPlaces as xs:string*, 
                                      $linkageInfo as xs:string*,
                                      $possible_births as element()* ) as element() {

  let $detail := <ol>{for $b at $i in $possible_births//source
                      return <li><a name="PB{$i}"/>{fui:kgb2html($b,$xmlid_1,"found")}</li>}</ol>
  let $index := <table class="kgb"><tr>{
                 for $e in ("Birth RecId","Registered","Yr/No","Place of birth","Birthdate","Baby","Father","Mother","Informant")
                 return <th>{$e}</th>
                }</tr>{
                for $b at $i in $possible_births//source
                  let $baby := $b/event/participant[@role="Baby"]
                  let $sex := if ($baby/@sex="1") then "boy"
                              else if ($baby/@sex="2") then "girl"
                              else "?"
                  let $m := $b/event/participant[@role="Mother"]
                  let $f := $b/event/participant[@role="Father"]
                  let $inf := $b/event/participant[@role="informant"]
                  let $inf := if ($inf) then concat($inf/persName," (", $inf/rank,") @",$inf/resides)
                              else $b/event/descInformant1/text()
                return <tr><td>{$i}. <a href="#PB{$i}">{data($b/@id)}</a></td>
                           <td>{$b/regDistrict/text()}</td>
                           <td>{concat($b/regYr,"/",$b/regNo)}</td>
                           <td>{$b/event/place}</td>
                           <td>{$b/event/date}</td>
                           <td>{concat("(",$sex,")",$baby/forename," ",$baby/middlename)}</td>
                           <td>{concat($f/persName," (", $f/rank,")")}</td>
                           <td>{concat($m/maidenname,", ", $m/forename," (", $m/rank,")")}</td>
                           <td>{$inf}</td>
                       </tr>
                }</table>
   let $placenames := distinct-values(($originalPlaces,$possible_births//(place|regDistrict)//text()))
   let $geocodingUri := fui:tasPlaces2fasgaz( $possible_births//(place|regDistrict)//text() )
   let $geo2 := fui:incAp20FTGeocoderIframe("hide","",($placenames))
  return <div><p>{$linkageInfo} Summary:</p>
              {$index}{$geo2}
                      <div id="display" width="100%" >
                         <p>Details:</p>{$detail}
                      </div>
              <p class="dbg">Xpath used: {$possible_births/xp/text()}</p>
         </div>
};

(:                                                                                               kgb -- birth reg :)
declare function fui:kgb2html ( $s as element()*, $link2id as xs:string*, $howRetrieved as xs:string* ) as element()* {
  let $id := data($s/@id)

  let $e := $s/event
  let $baby := $e//participant[@role="Baby"]
  let $babySex := if ($baby/@sex="1") then "boy"
                  else if ($baby/@sex="2") then "girl" else "?"
  let $babySex := concat("(",$babySex,")")
  let $mother := $e//participant[@role="Mother"]
  let $father := $e//participant[@role="Father"]


  let $witnesses := for $w at $i in $e//participant[@role=("Informant")] 
                    let $resides := if ($w/resides) then concat(" resides: ",$w/resides) else ()
                    return concat($i,") ",$w/persName,$resides," (",$w/@roledesc,"); ")
  let $witnesses := if (count($witnesses)) then <li>Informant: {$witnesses}</li> else ()


  let $birthPlace := if ($e/place) 
                     then let $ptas := concat($e/place,",TAS")
                          return fui:fasgaz_uri($ptas, $e/place/text(), 0) 
                     else <i>birthplace unspecified</i>
  let $regPlace := fui:fasgaz_uri( $s/regDistrict/text() || ",TAS", $s/regDistrict/text(), 0 )
  let $regRef := concat(" ",$s/regYr,"/",$s/regNo)
  
  (: If there is a link from the birth to an ert record, display it :)
  let $link2ert := (: collection($fui:dbname_for_new_links)//link[key/text()=$id and key[matches(text(),"ert")]] :)
                   fui:have_linkage($id,"ert")
  let $linked_key := distinct-values($link2ert)
  let $linked_ert := if (count($linked_key)) then concat("Linked digger ",$linked_key[1],"; ") else ""
  let $p_linked_ert := if ($linked_ert) then <p><span class="linkage">{$linked_ert}</span></p> else ()
  let $link_self := if ($link2id and not($linked_ert)) 
                    then <a href="{$fui:id-service}/{$id}?link={$link2id}">{data($s/@id)}</a>
                    else <a href="{$fui:id-service}/{$id}">{data($s/@id)}</a>
  (: show linked marriages -- these links are part of the original dataset :)
  let $marrid := $s//link[@type="MARR"]/text()
  let $marrid := if ($marrid) then $marrid else fui:have_linkage($id,"kgm") 
  let $linked_marriage := 
      if ($marrid) 
      then let $ysid := fu:xq2pid($marrid)
           let $sibs := collection("norm_kgb")//source[link/@ysid=$ysid]
           let $nsibs := count($sibs)
           let $nsibs1 := $nsibs - 1
           let $sibsId := concat("SIBS-",$marrid)
           let $sibsIcon := <img src="/img/siblings.png" onclick="$('#{$sibsId}').toggleClass('hide')" title="Show/hide siblings births"/>
           let $siblings := 
               if ($nsibs=1) then <p>Linked marriage <a href="{$fui:id-service}/{$marrid}">{$marrid}</a> (no siblings)</p>
               else for $s in $sibs
                    let $kgb_id := data($s/@id)
                    let $summary := tokenize($s/summary/text()," born:")
                    let $parents := tokenize($summary[2],"/ Fa:")
                    let $FaMo := concat("Fa:",$parents[2])
                    let $sib := $s//participant[@role="Baby"]
                    let $sibtype := if ($sib/@sex="1") then " brother" 
                                    else if ($sib/@sex="2") then " sister" else " "
                    let $label := concat(" ",$s/event/@citation," ",$s/event/date," ",$sibtype,":",$sib/persName," @ ",$s/event/place," / ",$FaMo)
                    order by $s/event/date
                    return if ($kgb_id=$id) 
                           then <li>---self---</li>
                           else <li>{fui:idservice($kgb_id,"")} {$label}</li>
           let $siblings := if ($nsibs=1) then $siblings
                            else if ($nsibs) 
                                 then (<p>{$sibsIcon} {$nsibs1} siblings via linked marriage <a href="{$fui:id-service}/{$marrid}">{$marrid}</a></p>,
                                      <div class="hide" id="{$sibsId}" ><ol>{$siblings}</ol></div>)
                                 else ()
           return if ($linked_ert) then ($siblings,$p_linked_ert) else $siblings
      else $p_linked_ert

  let $ba_Links :=
          let $ba_id := fu:p2x(data($baby/@ypid))
          let $ba_links := fui:have_linkage($ba_id,("kgm","ai"))
          let $ba_bIdsrv := fui:idservice($ba_links,"")
          return if ($ba_bIdsrv) then <ul><li>Have linkage to: {$ba_bIdsrv}</li></ul> else ()

  let $mo_bIdsrv := if ($marrid) then () else
          (: unlinked marriage, treat mother/father as per husb/wife in a marriage re searching workflows :)
          let $mo := $mother
          let $mo_id := fu:p2x(data($mo/@ypid))
          let $mo_birth := fui:have_linkage($mo_id,("kgb","ai"))
          let $mo_bIdsrv := fui:idservice($mo_birth,"")
          return if ($mo_bIdsrv) then <ul><li>Have linkage to: {$mo_bIdsrv}</li></ul> else ()

  let $fa_bIdsrv := if ($marrid) then () else
          let $fa := $father
          let $fa_id := fu:p2x(data($fa/@ypid))
          let $fa_birth := fui:have_linkage($fa_id,("kgb","ai"))
          let $fa_bIdsrv := fui:idservice($fa_birth,"")
          return if ($fa_bIdsrv) then <ul><li>Have linkage to: {$fa_bIdsrv}</li></ul> else ()

  return
       (<p class="{$howRetrieved}">Birth {$link_self} registered {$regPlace} {$regRef} on {data($s/regDate)} by {data($e/descInformant1/text())}<br/>
         Born {data($e/date/text())} @ {$birthPlace}:</p>,
        <ul>
        <li>Baby{$babySex}: <b>{$e//participant[@role=("baby","Baby")]/persName/text()}</b> {$ba_Links}</li>
        <li>Mother: {if ($mother/maidenname) then concat("(maidenname) ",$mother/maidenname,", ",$mother/forename) else $mother/persName/text()} {$mo_bIdsrv}</li>
        <li>Father: {$e//participant[@role=("father","Father")]/persName/text()} {$fa_bIdsrv}</li>
        {$witnesses}
        </ul>,$linked_marriage)
};

declare function fui:ofage ( $a as xs:string* ) as xs:string* {
    if ($a = "997") then "Of age i.e. &gt;= 21" else $a
};

declare function fui:kgm2pm2convict ( $pm as element()*, $sex as xs:string* ) as element()* {
for $p at $i in $pm
    let $src := concat($p/conref," @ ",$p/askDate)
    let $part := $p/participant[@sex=$sex]
    let $x := file:write("/tmp/abc12345",$part)

return
    if ($part) 
    then
        let $id := data($part/@id)
        let $pmLink := <a href="{$fui:id-service}/{$id}">Perm to Marry {$id} </a>
        return
            if ($part/@isConvict)
            then <li>{$pmLink} ({$src}); a convict from ship[{$part/ship/text()}] (linked): <a href="{$fui:convictsearch}{data($part/@isConvict)}">{data($part/@isConvict)} (fas lifecourse)</a></li>
            else if ($part/ship)
                 then <li>{$pmLink} ({$src}); a convict from ship[{$part/ship/text()}]</li>
                 else if ($part/free)
                      then <li>{$pmLink} ({$src}); is Free</li>
                      else <li>{$pmLink} ({$src}); BUG? No ship, not free???</li>
    else ()
};

(:                                                                                               kgm -- marriage reg :)
declare function fui:kgm2html ( $s as element()*, $pm as element()* ) as element()* {
  if ( not($s) ) then () else
let $x := file:write("/tmp/abc12345",$pm)
  let $id := data($s/@id)
  let $ysid := fu:xq2pid($id)
  let $e := $s/event
  let $place := if ($e//(marriedIn|marriagePlace2)) then concat(" @ ",$e/marriedIn," ",$e/marriagePlace2) else ""
  let $rites := if ($e/rites) then concat(" (",$e/rites,")") else ""

  let $wi := $e//participant[@role=("wife")]
  let $wi_id := fu:p2x(data($wi/@ypid))
  let $wi_birth := fui:have_linkage($wi_id,("kgb","ai"))
  let $wi_bIdsrv := fui:idservice($wi_birth,"")
  let $wi_bIdsrv := if ($wi_bIdsrv) then <ul><li>Have linkage to: {$wi_bIdsrv}</li></ul> else ()
  let $wi_pm := fui:kgm2pm2convict($pm, "2")
  let $wi_pm := if ($wi_pm) then <ul>{$wi_pm}</ul> else ()
  (: parents of wife :)
  let $wiParents := if ($wi//parents)
      then let $wiM := $wi//mother
           let $wiF := $wi//father
           let $wiMo := concat( "Mother: ",$wiM/surname," (nee:",$wiM/maidenname,") ",$wiM/forename," ",$wiM/rankOcc )   
           let $wiFa := concat( "Father: ",$wiF/surname," ",$wiF/forename," [",$wiF/rankOcc,"]")   
           let $extra := for $f in $wi//parents//mmf
                         return concat($f/@o,"(",$f/@type,"?)=",$f/text()," / ")
           return <ul><li>{$wiMo}</li><li>{$wiFa}</li><li>Extra info (unsure): {$extra}</li></ul>
      else ()

  let $hu := $e//participant[@role=("husband")]
  let $hu_id := fu:p2x(data($hu/@ypid))
  let $hu_birth := fui:have_linkage($hu_id,("kgb","ai"))
  let $hu_bIdsrv := fui:idservice($hu_birth,"")
  let $hu_bIdsrv := if ($hu_bIdsrv) then <ul><li>Linkage to: {$hu_bIdsrv}</li></ul> else ()
  let $hu_pm := fui:kgm2pm2convict($pm, "1")
  let $hu_pm := if ($hu_pm) then <ul>{$hu_pm}</ul> else ()
  (: parents of husband :)
  let $huParents := if ($hu//parents)
      then let $huM := $hu//mother
           let $huF := $hu//father
           let $huMo := concat( "Mother: ",$huM/surname," (nee:",$huM/maidenname,") ",$huM/forename," ",$huM/rankOcc )   
           let $huFa := concat( "Father: ",$huF/surname," ",$huF/forename," [",$huF/rankOcc,"]")   
           let $extra := for $f in $hu//parents//mmf
                         return concat($f/@o,"(",$f/@type,"?)=",$f/text()," / ")
           return <ul><li>{$huMo}</li><li>{$huFa}</li><li>Extra info (unsure): {$extra}</li></ul>
      else ()

  let $witnesses := for $w at $i in $e//participant[@role=("witness")] return concat($i,") ",$w/persName,"; ")
  let $witnesses := if (count($witnesses)) then <li>Witnesses: {$witnesses}</li> else ()

  let $subsequent_children := for $s in collection("norm_kgb")//source[link/@ysid=$ysid]
                              let $kgb_id := data($s/@id)
                              let $summary := tokenize($s/summary/text()," born:")
                              order by $s/event/date/text()
                              return <li>{fui:idservice($kgb_id,"")}:{$summary[1]}<br/>{$summary[2]}</li>
  let $subsequent_children := if ($subsequent_children) 
                              then <li>Linked births (children of the marriage):<ol>{$subsequent_children}</ol></li>
                              else <li>No births linked to this marriage ({$id} ysid:{$ysid})</li>
      
  return (<p>Registered {data($e/@citation)} {$place} on {$e/date/text()}{$rites}:</p>,
          <ul>
          <li>Husband: <b>{$hu/persName}</b> ({$hu/maritalStatus/text()}) age:{fui:ofage($hu/age/text())} / {$hu/rankOcc/text()} {$hu_bIdsrv} {$hu_pm} {$huParents}</li>
          <li>Wife: <b>{$wi/persName}</b> ({$wi/maritalStatus/text()}) age:{fui:ofage($wi/age/text())} / {$wi/rankOcc/text()} {$wi_bIdsrv} {$wi_pm} {$wiParents}</li>
          {$witnesses}
          {$subsequent_children}
          </ul>
         )
};

declare function fui:pm-marriageMultiSummaryAsHtml ($kgm as element(), $id as xs:string, 
                                                    $year as xs:string, $kgm_note as xs:string, $modifier as xs:string ) as element()* {
   (: see if we already have a link :)
   let $noneOfTheAbove := "kgm0"
   let $isLinked := fui:have_linkage( $id, "kgm")
   let $urlStart := concat($fui:updatelinkage-service,"/",$id,"?link=")
   return if ($isLinked) 
          then if ($isLinked = $noneOfTheAbove)
               then <p>Submission received: no likely matches found. <a href="{$urlStart}{$noneOfTheAbove}&amp;delete=1">[Delete that submission]</a></p>
               else <p>Has been linked to marriage <a href="{$fui:id-service}/{$isLinked}">{$isLinked}</a>.</p>
          else
   let $info := if ($kgm_note) then "Click below to make link to a marriage reg year " || $year || " found via fuzzy match " || $kgm_note || " surname(s):"
                else "Click below to select from the marriages which matched the reference " || $year || ":"
   let $noneOfTheAbove := <li><a href="{$urlStart}{$noneOfTheAbove}">None of the above</a></li>
   return
   if (count($kgm//summary)>0) 
   then (<p>{$info}</p>,
         <ul>{ for $m in $kgm//summary
               let $kgmid := replace(substring($m/text(),4),"^0+(\d+) .+","kgm$1")
               let $kgmlink := <a href="{$urlStart}{$kgmid}" title="Create linkage between this marriage and {$id}">Link to {$kgmid}</a>
               return <li>{$kgmlink}: { $m/text() }</li> }
             {$noneOfTheAbove}
         </ul>)
   else $kgm
};

declare function fui:pm-part2nyms ( $p as element()* ) as element()* {
   let $sn := $p/surname
   let $sn_nym := collection("norm_pm/pm_sn.xml")//nym[@n=$sn/@nr]
   let $fn := $p/forename
   let $fn_nym := collection("norm_pm/pm_fn.xml")//nym[@n=$fn/@nr]
   return ($sn_nym,$fn_nym)
};

declare function fui:pm2convict ( $m as element() ) as element()* {
   let $ship := <span>{concat(" (",$m/ship,") ")}</span>
   return
   if ($m/@isConvict) then ($ship,<a href="{$fui:convictsearch}{data($m/@isConvict)}">Is convict: {data($m/@isConvict)}</a>) 
   else if ($m/ship) 
        then let $linked := 
                 if ($m/@sex="1")
                 then fui:have_linkage(data($m/@id),("c31a","dlm","c33a","dcm","om","ai")) 
                 else fui:have_linkage(data($m/@id),("c40a","dlf","c41a","dcf","of","ai"))
             return if ($linked) then ($ship,<a href="{$fui:convictsearch}{$linked}" title="Submitted">Is convict: {$linked} (linkage submitted)</a>)
                    else ($ship,<em>Is convict; linkage work in progress ({data($m/@id)}).</em>)
        else <em> (FREE)</em>
};

(:                                                                                               pm -- perm to marry :)
declare function fui:pm2html ( $s as element()*, $modifier as xs:string* ) as element()* {
let $x := file:write("/tmp/111",$s)
   let $id := data($s/@id)
   let $pmhtml := fui:render-arbitrary-xml($s) 
   let $m := $s/participant[@sex="1"]
   let $w := $s/participant[@sex="2"]
   let $mlink := $s/marriageRef/link/text()
   let $mlink := if ($mlink) then $mlink else fui:have_linkage($id,"kgm") 
   let $mlink := if ($mlink) then (fui:idservice($mlink,"")," : ") else ()
   let $mref := if ($s/marriageRef) then string-join(("Marriage reference:",$s/marriageRef//text())," ") else "No marriage reference given."
   let $pmhtml := ($s/p,<ul>
                  <li>Reference: {$s/conref/text()} on {$s/askDate/text()}</li>
                  <li>Man: {concat($m/surname,", ",$m/forename," ")} {fui:pm2convict($m)}</li>
                  <li>Woman: {concat($w/surname,", ",$w/forename," ")} {fui:pm2convict($w)}</li>
                  <li>{$mlink} {$mref}</li>
                  </ul>)
   let $nolink_reason := data($s/marriageRef/@nolink)
   let $link2marriage := 
       if (count($s/marriageRef//summary) > 1)
       then (fui:inline_css_for_xml("green"),
             fui:pm-marriageMultiSummaryAsHtml($s, $id, $s/marriageRef/regYrNum/text(), "", $modifier))
       else (: conduct a search in kgm :)
       if ($nolink_reason=("notFound","referenceError?"))
       then (: get year and see if we can find the marriage, present a choice :)
            let $mref := if ($s/marriageRef/regYrNum/text())
                         then tokenize($s/marriageRef/regYrNum/text(),"/")
                         else if ($s/marriageRef/regYrNum/@sic)
                              then tokenize(data($s/marriageRef/regYrNum/@sic),"/")
                              else ()
            let $year := $mref[1]
            let $years := for $incr at $i in (0,2,3,4,5)
                          return xs:integer($year) + $incr
            let $m_nyms := fui:pm-part2nyms($s/participant[1])
            let $m_sn_ny := data($m_nyms[1]/@ny)
            let $m_sn_sdx := data($m_nyms[1]/@sdx)
            let $f_nyms := fui:pm-part2nyms($s/participant[2])
            let $f_sn_ny := data($f_nyms[1]/@ny)
            let $f_sn_sdx := data($m_nyms[1]/@sdx)
let $x := file:write("/tmp/111a",$m_sn_ny||"+"||$f_sn_ny)
            let $kgm_note := "male[" || $m_nyms[1]/text() || "] AND female[" || $f_nyms[1]/text() || "]"
            let $kgm := if ($year and $m_sn_ny and $f_sn_ny)
(:
                        then collection("norm_kgm")//surname[(@ny=$m_nyms[1]/@ny or @sdx=$m_nyms[1]/@sdx) and ../@sex="1"]/ancestor::source[@sortYr=$years]/summary
:)
                        then collection("norm_kgm")//source[@sortYr=$year and .//participant[(surname/@ny=$m_sn_ny or surname/@sdx=$m_sn_sdx) and @sex="1"] and .//participant[@sex="2" and (surname/@ny=$f_sn_ny or surname/@sdx=$f_sn_sdx)]]/summary
                        else ()
            let $kgm_note := if (count($kgm)) then $kgm_note else "male[" || $m_nyms[1]/text() || "] OR female[" || $f_nyms[1]/text() || "]"
            let $kgm1 := (: male surname and year :)
                         if (not($kgm) and $year and $m_sn_ny)
                         then collection("norm_kgm")//source[@sortYr=$year and .//participant[(surname/@ny=$m_sn_ny or surname/@sdx=$m_sn_sdx) and @sex="1"]]/summary
                         else ()
            let $kgm2 := (: female surname and year :)
                         if (not($kgm) and $year and $f_sn_ny)
                         then collection("norm_kgm")//source[@sortYr=$year and .//participant[(surname/@ny=$f_sn_ny or surname/@sdx=$f_sn_sdx) and @sex="2"]]/summary
                         else ()
            let $kgm := if ($kgm) then $kgm else ($kgm1,$kgm2)
            let $kgm := if (count($kgm)) then <matched n="{count($kgm)}">{$kgm}</matched> 
                        else <matched fail="1">Marriage search failed: In kgm, tried for find both, male or female surname fuzzy in year {$year} with no luck.</matched>
            let $color := if ($kgm/@n) then "green" else "red"
            return (fui:inline_css_for_xml($color),fui:pm-marriageMultiSummaryAsHtml($kgm, $id, $year, $kgm_note, $modifier))
       else ()
let $x := file:write("/tmp/111b",$link2marriage)
   return ($pmhtml,$link2marriage)
};

(: general xml display :)

declare function fui:span_type_eac ( $str as xs:string*, $cls as xs:string*, $after as xs:string* ) as element() {
   if ($after = "")
   then <span locType="{$cls}">{$str}</span>
   else <span locType="{$cls}" aft="after">{$str}</span>
};

declare function fui:render-arbitrary-xml ( $n as element()* ) as element() {
let $x := file:write("/tmp/abc123456",$n)
  let $show_links_on_outer_el := if (not($n/@id) or count($n)>1) then () else
      let $id := data($n/@id) 
      let $rectype := fu:xmlid2rectype(data($n/@id)) 
      let $have_links_for_this_id := collection($fui:dbname_for_new_links)//link[key/text()=$id] 
      let $li-linked-ids := if (count($have_links_for_this_id)=0) then () else <li>Linked to: {
          let $linked-ids := for $link in $have_links_for_this_id
                             for $to-id in $link//key/text()
                             where not($to-id=$id)
                             return $to-id
          let $linked-ids := distinct-values($linked-ids)
          return for $lk in $linked-ids return (fui:idservice($lk,""),"; ")
          }</li>
      let $pubsearch := if ( $rectype=$fu:rectypes-in-pubsearch )
                        then <li><a href="{$fui:convictsearch}{$id}">View {$id} in staffsearch</a></li>
                        else ()
      return ($li-linked-ids,$pubsearch)
   return
   <ul>{
      for $e in $n
         let $name := local-name($e)
         let $attrs := for $a in $e/@*
                       return concat(local-name($a), "=", data($a))
         let $attrs := if ( $e/@* ) then <span class="attribs">[{$attrs}]</span> else ()
      return if ( $e/child::* )
             then <li>{fui:span_type_eac($attrs, $name, "")};
                      {fui:render-arbitrary-xml($e/child::*)}</li>
             else <li>{fui:span_type_eac($attrs||$e/text(), $name, "")}</li>

   }{$show_links_on_outer_el}</ul>
};


(: .............................................................................. searching functions :)

declare function fui:search_convict_ancestors_of_enlistee ( $naa as xs:string* ) as element()* {
    (: get the barcode and search ccc_mediaflux and return xml with what we need in sequence of "ca" elements :)
    let $bc := replace($naa,"^ert0", "")
    let $ccc := collection("ccc_mediaflux/ccc_mediaflux.xml")//aifDescendant[@naa_key=$bc]
    return
        if ($ccc)
        then for $c in $ccc
             let $convict := $c/ancestor::*[local-name()="person"]
             let $ndesc := data($convict//*[local-name()="hasAifDescendants"]/@n)
             let $convict_id := data($convict/@id)
             let $fasid := data($convict/@fasid)
             let $convict_name := $convict//*[local-name()="persName"]
             let $linked_birth := $ccc//*[local-name()="KGBlink"]/text()
             let $link2birth := if ($linked_birth) then <x kgblink="{$linked_birth}"/> else ()
             return <ca key="{$convict_id}" faskey="{$fasid}" name="{$convict_name}" ndesc="{$ndesc}" bc="{$bc}">{$link2birth/@*}
                    {$c}
                    </ca>
        else ()
};

(: some utilities to assist searching :)

declare function fui:year_approx ( $yr as xs:integer ) as xs:string* {
   if ($yr < 0) then concat("~/before ",$yr)
   else if ($yr) then concat("~ ",$yr)
        else "?"
};

declare function fui:get_number( $num as  xs:string* ) as xs:integer {
   if ( $num and matches($num,"^\d+$") ) then xs:integer($num) else 0
};

(: if return positive integer, search for exact year; if negative, search BEFORE that year :)

declare function fui:estimated_birth_year ( $src_yr as xs:string*, $age as xs:string* ) as xs:integer {
   let $nyr := fui:get_number($src_yr)
   let $nage := fui:get_number($age)
   (: alot of ages in marriages are stuffed e.g. 997 -- means of age i.e. 21 or over :)
   let $nage := if ($nage = 997)
                then -21 (: of age :)
                else if ( $nage > 99 ) 
                     then 0 else $nage 
   return if ( $nyr and $nage ) 
          then if ($nage = -21)
               then - ($nyr - 22) 
               else if ( $nage > 99 )
                    then (: assume at least 14 years, and make the return value negative :)
                          - ($nyr - $fui:minOfAgeMotherOrFather)
                    else $nyr - $nage
          else if ($nyr)
               then - ($nyr - $fui:minOfAgeMotherOrFather)
               else 0
};

(: ................................................................ birth search is passed participant records 
   If $asat_year is negative, search before this year.
:)

declare function fui:search_birth_xpath_name ( $exact as xs:string*, $sns as xs:string*, $fns as xs:string* ) as xs:string {
   let $name := if ($exact)
                then if (starts-with($exact,"~")) 
                     then let $parts := tokenize($exact,", ")
                          let $sn := substring($parts[1],2)
                          let $fuzzy := (collection("norm_kgb/kgb-norm.xml")//surname[text()=$sn])[1]
                          let $nysiis := data($fuzzy/@ny)
                          let $soundex := data($fuzzy/@sdx)
                          let $forename := for $s in $fns
                                           return concat('(starts-with(forename/text(),"',$s,'") or starts-with(middlename/text(),"',$s,'"))')
                          return concat('surname[@ny="',$nysiis,'" or @sdx="',$soundex,'"] and (', string-join(($forename),' or '),')' )
                     else concat('persName/text()="',$exact,'" ')
                else let $surname := for $s in $sns
                                     return if (starts-with($s,"~"))
                                            then let $sn := replace($s,"~","")
                                                 let $fuzzy := (collection("norm_kgb/kgb-norm.xml")//surname[text()=$sn])[1]
                                                 let $nysiis := data($fuzzy/@ny)
                                                 let $soundex := data($fuzzy/@sdx)
                                                 return concat('surname[@ny="',$nysiis,'" or @sdx="',$soundex,'"]')
                                            else concat('starts-with(surname/text(),"',$s,'")')
                     let $surname :=  string-join(($surname)," or ")
                     let $fn_words := for $s in $fns return tokenize($s," ")
                     let $forename := for $s in $fn_words
                                      let $s := normalize-space($s)
                                      where $s
                                      return concat('(starts-with(forename/text(),"',$s,'") or starts-with(middlename/text(),"',$s,'"))')
                     let $forename := if ($surname) then string-join(($forename)," or ")
                                                    else string-join(($forename)," and ")
                     return if ($surname)
                            then if ($forename)
                                 then concat("(",$surname,") and (",$forename,")")
                                 else $surname
                            else if ($forename)
                                 then $forename
                                 else ""
   return $name
};

declare function fui:search_birth_xpath_time ( $asat_year as xs:string*, $years_s as xs:string, $year_aft as xs:integer ) as xs:string {
   let $t1 := "/ancestor::source[regYr/text()"
   return if (matches($asat_year,"^-"))
          then concat($t1,"<='",abs(xs:integer($asat_year)),"']")
          else if (matches($asat_year,"^\+"))
               then concat($t1,">='",abs(xs:integer($asat_year)),"']")
               else let $ys := for $y at $i in tokenize($years_s,",") 
                               return concat("'",$y,"'")
                    return concat($t1,"=(",string-join($ys,","),")]")
};

declare function fui:search_for_participants_birth ( $p as element()*, $asat_year as xs:string* , $year_range as xs:integer) as element()* {
   let $c := "norm_kgb/kgb-norm.xml"
   let $after_yr := if (matches($asat_year,"^\+")) then "1" else ""
   let $ebirthYr := if (matches($asat_year,"^-")) then xs:integer($asat_year)
                    else if ($after_yr) then let $a_yr := substring($asat_year,2)
let $x := file:write("/tmp/ap20xx_54aa","$after_yr="||$after_yr||" $asat_year="||$asat_year)
                                             return xs:integer($a_yr)
                    else fui:estimated_birth_year($asat_year, $p/age/text())
   let $yr := if ($ebirthYr < 0) then -$ebirthYr else $ebirthYr
   let $persName := if ( $p/@searchrole="Baby" and $p/@role="Mother" )
                    then (: look for Mothers maidenname :)
                         concat( $p/maidenname,", ",$p/forename )
                    else if ($p/persName) then $p/persName/text() else ()
   let $surname := $p//surname/text()
   let $maidenname := $p//maidenname/text()
   let $fuzzy_surnames := for $s in ($surname,$maidenname)
                          return if (starts-with($s,"~")) then $s else concat("~",$s)
   let $forename := $p//forename/text()
   let $sex_xp := if (data($p/@role)=("Father","Mother")) 
                  then if ($p/@searchrole="Baby") 
                       then if (data($p/@role)="Father")
                            then " and @sex=('1','','9')"
                            else " and @sex=('2','','9')"
                       else () (: Looking for Ma or Pa so won't know the sex of the baby :)
                  else if (data($p/@sex)=("1","2")) then concat(" and @sex=('",data($p/@sex),"','','9')") else ()
   let $middlename := $p//middlename/text()
   let $year_bef := $yr - $year_range
   let $year_aft := $yr + $year_range
   let $years := for $y in ($year_bef to $year_aft) return $y
   let $years_s := for $y in ($year_bef to $year_aft) return xs:string($y)
   let $years_s := string-join($years_s,",")

   (: pass 1 -- use persName i.e. exact search :)
   let $xp_time := if ($asat_year="ert")
                   then concat("/ancestor::source[regYr/text()>='",$fui:ERTBirthsAfter,"']")
                   else fui:search_birth_xpath_time($asat_year,$years_s,$year_aft)
   let $xp_start := concat("collection('",$c,"')//participant[")
   (: Note: we may be searching for Mothers/Fathers or the BIRTH of the Mother/Father if searchrole="Baby" :)
   let $xp_end := if (data($p/@role)=("Father","Mother"))
                  then if ($p/@searchrole="Baby")
                       then concat(" and @role='Baby' ",$sex_xp,"]",$xp_time)
                       else concat(" and @role='",$p/@role,"']",$xp_time)
                  else concat(" and @role='Baby' ",$sex_xp,"]",$xp_time)
   let $info_year := replace($xp_time,"^.+text\(\)(.+)\]$"," and birth year $1")
   let $info := concat("Exact ",$p/@role," name and ",$info_year)
   let $xp_exact := fui:search_birth_xpath_name($persName,($surname,$maidenname),($forename,$middlename))
   let $xp := concat($xp_start,$xp_exact,$xp_end)
   let $exact := xquery:eval($xp) 
let $x := file:write("/tmp/ap20xx_54a",$xp)
let $x := file:write("/tmp/ap20xx_54b",<x>{$exact}</x>)

   (: pass 2 -- if forename(s), use persName with fuzzy surname i.e. semi-exact search :)
   let $info := if ($exact) then $info else concat("Fuzzy surname, exact forenames ",$info_year)
   let $semiexact := 
       if ($exact or not(($forename,$middlename))) then ()
       else let $fuzzy_persname := concat("~",$persName)
            let $xp_startswith := fui:search_birth_xpath_name($fuzzy_persname,$fuzzy_surnames,($forename,$middlename))
            let $xp := concat($xp_start,$xp_startswith,$xp_end)
            let $x := file:append("/tmp/ap20xx_51",$fui:LF||"SEMIEXACT:"||$xp||$fui:LF)
            return xquery:eval($xp)

   let $info := if ($exact or $semiexact) then $info else concat("Names startwith ",$info_year)
   let $looser := 
       if ($exact or $semiexact) then ()
       else let $xp_startswith := fui:search_birth_xpath_name("",($surname,$maidenname),($forename,$middlename))
            let $xp := concat($xp_start,$xp_startswith,$xp_end)
            let $x := file:append("/tmp/ap20xx_51",$fui:LF||"LOOSER:"||$xp||$fui:LF)
            return xquery:eval($xp)

   let $info := if ($looser or $exact or $semiexact) then $info
                else if (($forename,$middlename)) then concat("Surname only startswith ",$surname,$info_year) else ()
   let $vloose := 
       if ($looser or $exact or $semiexact) then ()
       else if (($forename,$middlename)) 
            then  (: drop forenames :)
                let $xp_startswith := fui:search_birth_xpath_name("",($surname,$maidenname),())
                let $xp := concat($xp_start,$xp_startswith,$xp_end)
                let $x := file:append("/tmp/ap20xx_51",$fui:LF||"VLOOSE:"||$xp||$fui:LF)
                return xquery:eval($xp)
            else ()

   let $info := if ($looser or $exact or $semiexact or $vloose) then $info else concat("Surname only fuzzy (nyi) ",$info_year)
   let $fuzzy := ()

   let $found := ($exact,$semiexact,$looser,$vloose,$fuzzy)
       
   return <matches zz="2" xmlns="" n="{count($found)}" info="{$info} sorted by most recent birth year"><xp>{$xp}</xp>{
       for $birth in $found order by data($birth/event/@date) descending return $birth
   }</matches>
};


declare function fui:search_for_convict_child ( $p as element()*, $asat_year as xs:string* , $year_range as xs:integer) as element()* {
   let $c := "ccc_mediaflux/ccc_mediaflux.xml"
   return ()
};
