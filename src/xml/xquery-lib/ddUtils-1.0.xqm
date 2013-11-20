(: utilities for ddi and data description functions :)
module namespace dd = "http://fas/dd";
import module namespace functx="http://www.functx.com" at "/usr/local/lib/xquery/functx";
import module namespace  vjs-ss = "http://fas/vjs-ss" at "/usr/local/lib/xquery/vjs-ss";

declare variable $dd:DATADICT-DOC  := collection("00datadict/datadict.xml");
declare variable $dd:FILETYPES-DOC := collection("00datadict/datadict-filetypes.xml");
declare variable $dd:VERSIONING    := collection("00datadict/versions.xml");
declare variable $dd:ELEMENTDEFS   := $dd:DATADICT-DOC//list[@type="elementDef"];
declare variable $dd:RECTYPEDEFS   := $dd:DATADICT-DOC//list[@type="rectypeDef"]//rectypeDef;
declare variable $dd:DDVERSION     := data($dd:DATADICT-DOC/*/@version);
declare variable $dd:ddi-extras    := $dd:FILETYPES-DOC//ddi-extras;
declare variable $dd:cats-to-list  := 20;

declare function dd:docid ( $theTargetDb as xs:string, $theTargetDb-version as xs:string ) as xs:string {
   concat("convix.ddi.",$theTargetDb,".xmldb",".",$theTargetDb-version)
};

declare function dd:txt-desc( $name as xs:string*, $rectype as xs:string*, $sample as element()* ) as node()* {
(: if the sample element has embedded description, use it :)
if ($sample/@desc) then <txt>{data($sample/@desc)}</txt>
else if ($sample/@description) then <txt>{data($sample/@description)}</txt> 
else
  if ($name)
  then if ($dd:ELEMENTDEFS)
       then if ($dd:ELEMENTDEFS//elementDef[@id=$name])
            then let $els := $dd:ELEMENTDEFS//elementDef[@id=$name]
                 let $n := count($els)
                 return if ($n=1) then <txt>{$els/description/text()}</txt>
                        else if ($els[@rectype=$rectype])
                             then <txt>{$els[@rectype=$rectype]/description/text()}</txt>
                             else <txt>{$els[not(string-length(@rectype))]/description/text()}</txt>
            else if ($dd:RECTYPEDEFS[@id=$name])
                 then <txt>{$dd:RECTYPEDEFS[@id=$name]/description/text()}</txt>
                 else
                 switch ($name) (: generic things here :)
                 case("configuration") return <txt>Fas federation system configuration.</txt>
                 case("fasfiles") return <txt>Generic root container for multiple &lt;fasfile&gt; elements.</txt>
                 case("fasfile") return <txt>Generic root container element in fas domain.</txt>
                 case("persName") return <txt>Normalised name label for a person typically: [Surname, Forename(s) any-other-info e.g convict-ship] .</txt>
                 case("sn") return <txt>Normalised lower-case surname including soundex and nysiis code attributes for fuzzy searching. Multiple words in &lt;surname&gt; generates multiple &lt;sn&gt; elements, one per word.</txt>
                 case("fn") return <txt>Normalised lower-case forename including soundex and nysiis code attributes for fuzzy searching. Multiple words in &lt;forename&gt; generates multiple &lt;fn&gt; elements, one per word.</txt>
                 case("vid") return <txt>Voyage id. {$dd:ELEMENTDEFS//elementDef[@id=$name]/warning/text()}</txt>
                 case("ana") return <txt>Analytics derived from content.</txt>
                 case("trCmmt") return <txt>Transcribers comment.</txt>
                 case("legacy") return <txt>Legacy content carried over from previous workflow stage, probably redundant.</txt>
                 case("key") return <txt>Reference to an fas unique record identifier in this or a different database.</txt>
                 case("link") return <txt>Linkage submission or when contained by a &lt;linkGrp&gt; is a link. Contains a unique record identifier.</txt>
                 case("nym") return <txt>Definition for a canonical name or name component of any kind (see TEI p5).</txt>
                 default return () (: <txt>{$name} (no further description defined)</txt> :)
       else <txt>##ERROR## dd:txt-desc: $dd:ELEMENTDEFS not defined. Passed name[{$name}] rectype[{$rectype}]</txt>
  else ()
};

declare function dd:attr-info ($a as xs:string*) as xs:string* {
switch ($a)
case("id") return "Unique record identifier. Typically XXX9999 where XXX is a rectype and the numeric part uniquely locates the particular instance of the record of that type. On root elements, usually the filename."
case("source") return "Metadata about the file origin."
case("corresp") return "Corresponding to a Unique record identifier."
case("precision") return "Accuracy of a data item."
case("key") return "Reference to an fas unique record identifier in this or a different database."
case("ny") return "Nysiis code of a string used for inexact matching; calculated by Perl library String::Nysiis."
case("nysiis") return "Nysiis code of a string used for inexact matching; calculated on ingest by Perl library String::Nysiis."
case("sdx") return "Soundex code of a string used for inexact matching; calculated on ingest by Perl library Text::Soundex."
case("soundex") return "Soundex code of a string used for inexact matching; calculated on ingest by Perl library Text::Soundex."
case("role") return "Nature of a person&apos;s participation in an event."
case("n") return "Count (as per TEI global attr @n)."
case("i") return "Internal system counter. Ignore."
case("k") return "Internal system counter. Ignore."
case("RT") return "FAS record type."
case("sample") return "Sampling methodology metadata."
case("desc") return "Descriptive metadata."
case("generatedby") return "Descriptive metadata about the process which generated this file."
case("description") return "Descriptive metadata."
case("type") return "Type of element (as per TEI global attr @type)."
case("ypid") return "XML @id converted to unique postgres integer for relational DB equivalence (id/ypid mapping calculated in FAS XQuery module namespace http://fas/fu)."
case("when") return "When an event occured as ISO8601 date YYYY-MM-DD."
case("where") return "Where an event occurred; a placename (not normalised)."
case("citation") return "Short descriptive label."
case("bornYr") return "Birth year derived from content."
case("diedYr") return "Death year derived from content."
case("sort") return "Used for sort sequence."
case("sortPlace") return "Label used to sort/group by placename, derived from content."
case("sortYr") return "Label used to sort/group by a year, typically YYYY, derived from content."
case("sortRegNo") return "Label used to sort/group by a registration number, derived from content."
case("deathDateX") return "Date normalised as ISO8601 date YYYY-MM-DD"
case("daynum") return "Julian day number relative to 1656-01-01. Used for calculating period of time in days."
case("render") return "Display value for humans."
default return ()
};


declare function dd:databases-in-series ($theTargetDb as xs:string*) as node()* {
   for $r in db:list-details("00ddi")//text()
   let $db := replace($r,".xml$","")
   order by $db
   where not(starts-with($db,"datadict")) and not(matches($db,"khrd","i"))
   return if ($db=$theTargetDb) then text{ concat("(",$db,")") }
          else <ExtLink URI="{$db}-codebook.html">{$db}</ExtLink>
};


declare function dd:has-children($distinct-element-paths as xs:string*,
                                 $xp_as_parent as xs:string,
                                 $xp as xs:string ) as element()* {
<children>{
   for $p in $distinct-element-paths
       let $theChildName := replace(replace(replace($p,"^/",""),$xp_as_parent,""),"^/","")
       let $c_size := count( tokenize($theChildName,"/" ) )
   where $c_size=1 and starts-with($p,$xp) and not($p=$xp)
   return <cn>{replace(replace(replace($p,"^/",""),$xp,""),"^/","")}</cn>
}</children>

};


declare function dd:ana-element($n-all-of-these as xs:integer, $sample as element()) as element()* {
  let $elname := local-name($sample)
  let $sample-attrs := element {local-name($sample)} {$sample/@*}
  let $attrs := functx:distinct-attribute-names($sample-attrs)
  let $attrs := if (count($attrs)) 
      then <attributes>{
           for $a in $attrs 
           let $adesc := dd:attr-info($a)
           return <an name="{$a}">{$adesc}</an>
           }</attributes> 
      else ()
  let $keep-sample := if ($n-all-of-these=1 or $elname=("fasfile")) 
                      then <example attrs-only="1">{serialize($sample-attrs)}</example>
                      else <example>{serialize($sample)}</example>
  return ($attrs,$keep-sample)
};

declare function dd:content-model($theTargetDb as xs:string) as xs:string {
   if (matches($theTargetDb,"^norm[-_]"))
   then if (matches($theTargetDb,"kg[bdm]"))
        then "Normalised evidence (source,event,participants)"
        else "Normalised life course (person,events)"
   else if (matches($theTargetDb,"^raw[-_]"))
        then "Flat semantic"
        else "Customised" 
};

declare function dd:rectype-from-dbname($theTargetDb as xs:string) as xs:string {
  let $rectype := $theTargetDb
  let $rectype := replace($rectype,"[-_.]sample$","")
  let $rectype := replace($rectype,"raw[-_]","")
  let $rectype := replace($rectype,"norm[-_]","")
  let $rectype := replace($rectype,"FT$","")
  return switch ($rectype)
         case("00config") return "configuration"
         default return $rectype
};

declare function dd:rectypeDef($rectype as xs:string) as element()* {
   $dd:RECTYPEDEFS[@id=$rectype]
};

declare function dd:rectypeDescription($theTargetDb as xs:string, $rectype as xs:string) as node()* {
   let $rectypeDef := dd:rectypeDef($rectype)
   let $desc := $rectypeDef//description//node()
   return if ($desc) then $desc 
          else <p>{concat("------------- missing rectypeDescription for theTargetDb[",$theTargetDb,"] rectype[",$rectype,"]")}</p>
};

declare function dd:database-abstract($theTargetDb as xs:string, $rectype as xs:string) as node()* {
   (: If we don't have a specific desc for the dbname, derive it from the rectype with raw/norm prefixes :)
   let $ddDBinfo := $dd:DATADICT-DOC//databases/db[name/text()=$theTargetDb]
   return
       if ($ddDBinfo) then $ddDBinfo//desc/node()
       else
           let $filetype-desc := $dd:FILETYPES-DOC//type[@code=$rectype]
           return if ($filetype-desc//*) then $filetype-desc/node()
                  else if ($filetype-desc)
                  then let $prefix := if (matches($theTargetDb,"^raw[-_]")) then "Ingest version of "
                                 else if (matches($theTargetDb,"^norm[-_]")) then "Normalised version of "
                                 else if (matches($theTargetDb,"FT$")) then "Free text indexed version of "
                                 else ()
                        return <p>{concat($prefix,$filetype-desc)}</p>
                  else <p>{concat("------------- missing database-abstract for theTargetDb[",$theTargetDb,"] rectype[",$rectype,"]")}</p>
};

declare function dd:file-notes ( $file as node() ) as element()* {
   let $fileNotes1 := if ($file/@derivativeOf) then <notes>Derivative of: {data($file/@derivativeOf)}</notes> else ()
   let $fileNotes2 := if ($file/@description)  then <notes>{data($file/@description)}</notes>  else ()
   let $fileNotes3 := if ($file/@generatedBy)  then <notes>Generated by: {data($file/@generatedBy)}</notes>  else ()
   return ($fileNotes1,$fileNotes2,$fileNotes3)
};

declare function dd:location-to-filename( $path as xs:string ) as xs:string* {
  let $path := replace($path,"/tmp/basexrun/","")
  let $path := replace($path,"/media/disk2/basex/basex/","")
  let $path := replace($path,"/data/bx/xml/","")
  return $path
};

declare function dd:ddi-method ($rectype as xs:string, $rectypeDef as element()*) as element()* {
  let $TBD := "##### to be determined in CHAINS agreement ##### "

  let $columnCleansing :=
    if ($rectypeDef//cleansingStyle//column)
    then <ul>{
         for $column in $rectypeDef//cleansingStyle//column
         return <li>Incoming column "{$column/name/text()}" was converted using style "{$column/style/node()}"</li>
         }</ul>
    else if ($rectypeDef//teip5)
         then <p>{$rectypeDef//teip5/node()}</p>
         else if ($rectypeDef//workDir)
              then <p>Custom workflow shell, perl, xquery at {$rectypeDef//workDir/node()}</p>
              else ()

  let $cleansingStylesUsed :=
    if ($rectypeDef//cleansingStyle//style)
    then (<p>Cleaning styles used above are:</p>,<dl>{
         for $style in distinct-values($rectypeDef//cleansingStyle//style/node())
         let $styledesc := $dd:ddi-extras//cleaning-styles//style[@id=$style]/node()
         return (<dt>{$style}:</dt>,<dd>{$styledesc}</dd>)
         }</dl>)
    else ()

  let $method := 
      <method>
      <dataColl>
        <dataCollector>{dd:ddi-extra($dd:ddi-extras//dataCollector, $rectype)}</dataCollector>
        <sampProc>{dd:ddi-extra($dd:ddi-extras//sampProc, $rectype)}</sampProc>
        <collMode>{dd:ddi-extra($dd:ddi-extras//collMode, $rectype)}</collMode>
        <sources/>
        { if ($columnCleansing or $cleansingStylesUsed)
          then <cleanOps>{$columnCleansing}{$cleansingStylesUsed}</cleanOps> else () }
      </dataColl>
      </method>

  return $method
};

declare function dd:ddi-extra ( $ddi-extra as element()*, $rectype as xs:string ) as node()* {
    if ($ddi-extra[@key=$rectype]) 
    then $ddi-extra[@key=$rectype]/node()
    else $ddi-extra[@default]/node()
};

(: recursive routine to process basex facet info. ID to use is passed by caller. :)
declare function dd:analyse-element-type-from-basex-facets ( $parent as xs:string, $elementId as xs:string, $e as element() ) as element()* {
   let $me := if ($parent) then concat( $parent,"/", data($e/@name) ) else data($e/@name)
   let $parent-id := replace($elementId,".e\d+$","")
   let $parent-attrs := if ($parent) then <x xpath="{$me}" parent="{$parent}" parent-id="{$parent-id}"/> else <x root="yes"/>
   let $ana-attrs := for $a in $e/child::attribute
                     let $nCats := if ($a/@type="category") then <x distinct="{count($a//entry)}"/> else ()
                     return <an>{$a/@*}{$nCats/@*}{dd:attr-info(data($a/@name))}</an>
   let $attrs := if ($ana-attrs) then <attributes>{$ana-attrs}</attributes> else ()

   let $directChildren := $e/child::element
   let $nChildren := count($directChildren)
   let $dcnames := for $n in $directChildren return <cn IDREF="{$parent-id}">{data($n/@name)}</cn>
   let $children := if ($nChildren)
                    then <children>{$dcnames}</children>
                    else ()
   let $children2 := 
       if ($nChildren)
       then for $c at $i in $directChildren
            let $id := concat($elementId,".e",$i)
            return dd:analyse-element-type-from-basex-facets($me,$id,$c)
       else () 

   return if ($children)
          then <varGrp ID="{$elementId}" nChildren="{$nChildren}">{$parent-attrs/@*}{$e/@*} {$children} {$children2} {$attrs} </varGrp>
          else <var ID="{$elementId}">{$parent-attrs/@*}{$e/@*}</var>
};

(: recurse facets to show a simple html list :)
declare function dd:facets-to-ul($elementId as xs:string,$facet as node()) as element()* {
  let $name := data($facet/@name)
  let $isRoot := if (matches($elementId,"\.")) then 0 else 1
  let $parent-id := if ($isRoot) then () else replace($elementId,".e\d+$","")
  let $attrs := for $a in $facet/child::attribute 
                let $nocc := data($a/@count)
                let $category := if ( $a[@type="category"] )
                                 then let $ndistinct := count($a//entry)
                                      let $detail := if ($ndistinct > 0 and $ndistinct <= $dd:cats-to-list ) 
                                                     then let $vals := string-join(($a//entry/text())," / ")
                                                          return concat(": [",$vals,"]") 
                                                     else "."
                                      return <x type="category" title="Attr occurs {$nocc} times. Category has {$ndistinct} distinct values{$detail}"/> 
                                 else <x title="Attr occurs {$nocc} times."/>
                return <span>{$category/@*}{concat("@",$a/@name)} </span>
  let $attrs := if (count($attrs)) then $attrs else ()
  let $children := $facet/child::element
  let $nc := count($children)
  let $name_uri := <a href="#{$elementId}">&lt;{$name}&gt;</a>
  let $showCount := concat("(",$facet/@count,") ")
  let $nocc := data($facet/text/@count) 
  let $text-context := $facet/text
  let $category := if ( $text-context[@type="category"] )
                   then let $ndistinct := count($text-context//entry)
                        let $detail := if ($ndistinct > 0 and $ndistinct <= $dd:cats-to-list  )
                                       then let $vals := string-join(($text-context//entry/text())," / ")
                                            return concat(": [",$vals,"]") 
                                       else "."
                        return <x type="category" title="Text content in {$nocc} cases. Category has {$ndistinct} distinct values{$detail}"/> 
                   else if ( $text-context )
                        then <x title="Text content in {$nocc} cases."/>
                        else ()
  
  return
  if ($nc=0)
  then <li id="X{$elementId}" name="{$name}"><span class="el_t">{$category/@*}{$name_uri} </span>{$showCount} {$attrs}</li>
  else let $con := <ul class="small">{ 
           for $e at $i in $children
           let $id := concat($elementId,".e",$i)
           return dd:facets-to-ul($id,$e) }
           </ul>
       return if ($isRoot) 
              then (<p id="X{$elementId}" name="{$name}"><span class="el_r">{$name_uri} </span> {$attrs}</p>,$con)
              else <li id="X{$elementId}" name="{$name}"><span class="el_c">{$name_uri} </span>{$showCount}{$attrs} {$con} </li> 
};

(: not recursive :)
declare function dd:facets-analysis-to-xml-element-recGroups ( $db as xs:string,
                                                               $elementId as xs:string,
                                                               $root as element()*, 
                                                               $elType as xs:string, 
                                                               $rectype as xs:string,
                                                               $fileCont as element()?
                                                             ) as element()* {
   (: for $root at $enum in $ana
         let $elementId := concat("E",$enum)
         let $root-children := $root//varGrp[@ID=$elementId]/child::children
         let $root-attrs := $root/child::attributes
   :)
         (: NOTE: for vjsaggF/M use the descriptions from vjs-ss library :)
         let $vjs-sex := if ($db=("vjsaggF","vjsaggM")) then substring($db,7,1) else ()
         let $idNum := number(replace($elementId,"E",""))
         let $next := $idNum + 1
         let $prev := $idNum - 1
         let $f-next := <x next="fileTxt_E{$next}"/>
         let $f-prev := if ($prev) then <x prev="fileTxt_E{$prev}"/> else ()
         let $d-next := <x next="dataDscr_E{$next}"/>
         let $d-prev := if ($prev) then <x prev="dataDscr_E{$prev}"/> else <x prev="Logical" />
         let $root-children := $root//varGrp[@ID=$elementId]/child::children
         let $root-attrs := $root/child::attributes
         let $root-name := data($root/@type)
         let $root-label := dd:txt-desc( $root-name, $rectype, ())
let $root-example-query := concat("(collection('",$db,"')//",$root-name,")[1]")
let $root-example := xquery:eval($root-example-query) (: ($CONTEXT//*[local-name()=$name])[1] :)
let $root-example-attrs := element {local-name($root-example)} {$root-example/@*}
let $root-ex := serialize($root-example-attrs) 
let $root-ex := <example attrs-only="1">{$root-ex}</example>


         let $con := for $xp in $root//xp/text()
                     let $item := $root//*[@xpath=$xp and local-name()=$elType]
                     let $count := data($item/@count)
                     let $name := data($item/@name)
let $example-query := concat("(collection('",$db,"')//",$name,")[1]")
let $example := xquery:eval($example-query) (: ($CONTEXT//*[local-name()=$name])[1] :)
let $example-attrs := element {local-name($example)} {$example/@*}
let $ex := if ($count=1 or $name=("fasfile","fasfiles")) then serialize($example-attrs) else serialize($example)
let $ex := if (string-length($ex)>2000) then concat( substring($ex,1,2000),"... truncated") else $ex
let $ex := if ($count=1 or $name=("fasfile","fasfiles")) then <example attrs-only="1">{$ex}</example> else <example>{$ex}</example>
                     (: let $ex := $item/child::example :)
                     let $label := if ($vjs-sex)
                                   then let $data-item := $vjs-ss:data-items//c[label/text()=$name 
                                                                         and (not(@type) or @type=$vjs-sex)]
                                        return <txt>{($data-item//desc)[1]/text()}</txt>
                                   else dd:txt-desc( $name, $rectype, ())
                     let $labl := if ($label) 
                                  then if ($elType="varGrp") then <labl>{$label/node()}</labl> else (<labl>{$name}</labl>,$label)
                                  else ()
                     let $directChildren := <children>{
                                            for $c at $i in $item/child::*[local-name()=("var","varGrp")]
                                            where $c/@ID
                                            return <cn i="{$i}" IDREF="{$c/@ID}">{data($c/@name)}</cn>
                                            }</children>
                     let $attrs := $item/child::attributes
                     let $stats := if ($elType="var") then <sumStat type="vald">{$count}</sumStat> 
                                   else <recDimnsn><caseQnty>{$count}</caseQnty></recDimnsn>
                     where $item
                     return if ($elType="varGrp")
                            then element recGrp { $item/@*, $labl, $stats, $directChildren, $attrs, $ex }
                            else element var { $item/@*, $labl, $stats, $attrs, $ex }
   return if ($elType="varGrp")
          then <fileTxt ID="fileTxt_{$elementId}" data="dataDscr_{$elementId}">{$f-next/@*}{$f-prev/@*}
               <fileName>{data($root/@type)}</fileName>
               {$fileCont}
               <fileStrc type="hierarchical">
               <recGrp rectype="{$root/@type}" ID="{$elementId}" root="yes" name="{$root/@type}" xpath="{$root/@type}">
                  <labl>{$root-label/node()}</labl>
                  {$root-attrs}
                  {$root-children}
                  {$root-ex}
               </recGrp>
               {$con}
               </fileStrc>
               </fileTxt>
          else <dataDscr ID="dataDscr_{$elementId}" files="fileTxt_{$elementId}" filename="{$root-name}">{$d-next/@*}{$d-prev/@*}{$con}</dataDscr>
};
