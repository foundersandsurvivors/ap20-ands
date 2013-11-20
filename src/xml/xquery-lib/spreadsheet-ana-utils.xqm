(: 
   spreadsheet-ana-utils.xqm : spreadsheet analysis utilities
   ==========================================================

   Utility functions for processing generic table/row/column spreadsheet data.

   These functions are used when processing multiple spreadsheets where layouts may vary
   e.g. same datums may appear in different columns, labelled differently, in different speadsheets.

   For the Founders and Survivors "vjs" ships research project, the data
   originated from multiple controlled google spreadsheets (one per convict ship).
   These spreadsheets were created programmatically from templates, exported daily as html,
   made valid xml by html tidy, and converted to a tei style generic
   table, row, column format. Row 1 is required as a column specification row.
   Annotations are retained as front matter, and used to derived numeric codifications
   and to identify columns.

   Here is a skeletal structure for the kind of xml structures this library can process:

   Any xml wrapping around these components passed to the functions here:

      $annotations: e.g.  <item>[1] generaldescription 1. descn of value-1 2. descn of value-2 3. descn of value-3 ...... </item>
                                ### NOTE: the text prior to the first space MUST appear in 
                                          the colspec heading for that column e.g. column 5
      $colspec should contain:

      <row n="1" type="column_spec">
          ...
          <col n="5">Traced? [1]</col>
      </row>

      $statSpec should contain elements like this:

          <item><name>BORN_PLACECODE</name>  <------------------- column name for output
                <desc>Standardised country and country codes for place of birth.</desc> <--- annotation text to match
                <fallback occurance="2">Country_and county_codes</fallback>  <--- used if annotation text not found, 
                                                                                  occurance indicates sequnce
                <fallback occurance="2">Code</fallback>                      <--- alternate text.
          </item>
          or
          <item><name>TRACED</name>
                <match>traced to death</match>
                <fallback>Traced?</fallback>
          </item>

      $updated_rows contain a series of data e.g.
          NOTE: Only the @n attribute, identifying the row, is required.
          <row n="2">
               <col n="1">value...</col> <-- @n attribute identifying the column is required.
               <col n="2">value...</col>
               ...
          </row>

    The functions here can self-discover the location of data, provide codified descriptions of columns,
    and aggregate common fields across multiple spreadsheets which may VARY in the ordering/content of columns.

    Sandra Silcot 2013

    --------------------------------------------------------------------------------- Change history:
    * Aug 2013: initial version to suit vjs FAS project.
   
:)
module namespace  ssana = "http://fas/ssana" ;

declare variable $ssana:DERIVATIVES := 
<derive-extras-for-aggregates>
<item><name>CONVICTED_PLACECODE</name><derive>CONVICTED_COUNTRY</derive><length>3</length></item>
</derive-extras-for-aggregates>;

declare function ssana:which-annotation( $annotations as element()*, $annotation-matches as xs:string ) as element()* {
    $annotations//item[matches(text(),$annotation-matches)]
};

declare function ssana:which-column( $ann-traced as element()*,
                                     $colspec as element()*,
                                     $fallbackEl as element()* ) as element()* {
     (: note we might have multiple fallbackEl's pass because fields can have diff names in diff spreadsheets! :)
     (: use fallback for fields by value, or for numeric encodes
        where annotations might be missing as in Forfarshire_1843_M238_om_b4a366.29_vjs_tei.xml :)

     let $match-traced := if ($ann-traced) then tokenize($ann-traced," ")[1] else ()
     let $findByAnnotation := if ($match-traced) then $colspec//col[contains(text(),$match-traced)] else ()
     let $theCol := if ($findByAnnotation) then $findByAnnotation else
         for $fEl in $fallbackEl
         let $occ := if ($fEl/@occurance) then number(data($fEl/@occurance)) else 1
         let $txt := $fEl/text()
         let $found := for $c at $i in $colspec//col[contains(text(),$txt)] where $i=$occ return $c
         where $found
         return $found
     let $WARN := if (not($theCol)) then <ERROR><msg>Cannot find $theCol.</msg>{$fallbackEl}{$colspec}</ERROR>
                  else if (count($theCol)>1)
                       then <ERROR><msg>Ambiguous/multiple $theCols found.</msg>{$fallbackEl}{$colspec}
                                   <found>{$theCol}</found>
                            </ERROR>
                       else ()
     return if ($WARN) then ((),$WARN) else ($theCol[1],$WARN)
};

declare function ssana:decode-annotation ( $name as xs:string, $ann-traced as element()* ) as element()* {
    let $ann-parts := tokenize($ann-traced/text()," ")
    let $match-traced := $ann-parts[1]
    let $start := string-length($match-traced) + 2
    let $ann-desc := substring($ann-traced/text(), $start)
    let $ann-desc := replace($ann-desc,"(\d)\. ","___$1:::")
    let $codes := tokenize($ann-desc,"___")
    let $codes := for $c in $codes
                  let $codeAndDesc := normalize-space($c)
                  let $cv := tokenize($codeAndDesc,":::")
                  where $codeAndDesc and number($cv[1])>0
                  return <code value="{$cv[1]}">{$cv[2]}</code>
    return (<encoded-description>{$codes}</encoded-description>,
           <debug>{$ann-desc}</debug>)
};

declare function ssana:do-analysis-numericCode
                                   ( $statSpec as element(),
                                     $annotations as element()*,
                                     $colspec as element()*,
                                     $updated_rows as element()*,
                                     $nData as xs:integer,
                                     $srcKey as xs:string
                                   ) as element()* {

     let $datum-name := $statSpec//name/text()
     let $annotation-matches := $statSpec//match/text()
     let $fallbackEl := $statSpec//fallback

     let $ann-traced := ssana:which-annotation($annotations,$annotation-matches)
     let $col-traced := ssana:which-column($ann-traced,$colspec,$fallbackEl)
     let $col-number := data($col-traced/@n)
     let $traced := for $c in $updated_rows//col[@n=$col-number] return <col r="{$c/../@n}">{$c/@*}{$c/text()}</col>
     let $nUpdated := count($updated_rows)

     let $decoding_info := ssana:decode-annotation($datum-name,$ann-traced)
     let $decoding := $decoding_info[1]
     let $decoding_debug := $decoding_info[2]
     let $results := if (count($updated_rows)=0) then () else
                  for $v in $traced
                  let $val := $v/text()
                  let $rownum := data($v/@r)
                  group by $val
                  order by $val
                  return <n v="{$val}" rows="{$rownum}" meaning="{$decoding//code[@value=$val]/text()}">{count($v)}</n>

     let $results := for $r in $results
                     let $desc := $decoding//code[@value=$r/@v]/text()
                     let $percentage := (100 * number($r/text()) ) div $nUpdated
                     let $pc := format-number(xs:float($percentage),"###.00")
                     let $pc := replace(xs:string($pc),"^(\d*\.\d\d).*$","$1")
                     let $warn := if ($r/@meaning="") then <a MISSING-VALUE="yes"/> else ()
                     let $theValue := string(data($r/@v))
                     order by $percentage descending
                     return <n pc="{$pc}" of="{$nUpdated}" desc="{$desc}">{$r/@v}{$r/@rows}{$warn/@*}{$r/text()}</n>

     let $n_undecoded := count($results[@MISSING-VALUE])
     let $warning := if ($n_undecoded)
                     then <WARNING>{$n_undecoded} value(s) not analysable. Treat as missing?</WARNING>
                     else ()

     let $sumNums := for $r in $results return number($r/text())
     let $sum := sum($sumNums)

     let $res := <results n_updated="{count($updated_rows)}"
                          n_values_counted="{$sum}"
                          nbad="{$n_undecoded}"
                 >{ $results } </results>

     return <ana key="{$srcKey}" data-item-name="{$datum-name}" data-item-type="numeric-code" column="{$col-number}">
                  <description>{$ann-traced/text()}</description>
                  { $decoding }
                  { $res }
                  { $warning }
            </ana>
};

declare function ssana:do-analysis-values
                                   ( $statSpec as element(),
                                     $annotations as element()*,
                                     $colspec as element()*,
                                     $updated_rows as element()*,
                                     $nData as xs:integer,
                                     $srcKey as xs:string
                                   ) as element()* {

     let $datum-name := $statSpec//name/text()
     let $annotation-matches := $statSpec//match/text()
     let $fallbackEl := $statSpec//fallback
     let $description := $statSpec//desc/text()

     let $ann-traced := if ($annotation-matches) then ssana:which-annotation($annotations,$annotation-matches) else ()
     let $col-traced := ssana:which-column($ann-traced,$colspec,$fallbackEl)
     let $col-number := data($col-traced/@n)
     let $traced := for $c in $updated_rows//col[@n=$col-number] return <col r="{$c/../@n}">{$c/@*}{$c/text()}</col>
     let $nUpdated := count($updated_rows)

     let $results := if (count($updated_rows)=0) then () else
                  for $v in $traced
                  let $val := if ($v/text()) then $v/text() else ""
                  let $rownum := data($v/@r)
                  group by $val
                  order by $val
                  return <n v="{$val}" of="{$nUpdated}" rows="{$rownum}">{count($v)}</n>

     let $sumNums := for $r in $results return number($r/text())
     let $sum := sum($sumNums)

     let $res := <results n_updated="{count($updated_rows)}"
                          n_values_counted="{$sum}"
                 >{ $results } </results>

     let $debug := <dbg> {$fallbackEl} {$col-traced} </dbg>
     let $traced-details := <traced>{for $t at $i in $traced return <col i="{$i}">{$t/@*}{$t/text()}</col> }</traced>

     return <ana key="{$srcKey}" data-item-name="{$datum-name}" data-item-type="value" column="{$col-number}">
                  <description>{$description}</description>
                  { $res }
            </ana>
};

(: aggregate multiple ana elements from above routines :)

declare function ssana:aggregate-ana ( $fieldName as xs:string, $analysedDocs as element()* ) as element()* {
   let $ana-list := for $shipAnalysis in $analysedDocs
                    return $shipAnalysis//ana[@data-item-name=$fieldName]
   let $data-item-type := ($ana-list//@data-item-type)[1]
   let $is-numeric-code := if (local-name($data-item-type)="numeric-code") then 1 else 0
   let $occs := count($ana-list)
   let $of := for $ana in $ana-list for $n in ($ana//@of)[1]return number($n)
   let $sum-of-recs-updated := sum($of)

   (: aggregate with group by :)
   let $results := if ($sum-of-recs-updated=0) then () else
       for $stat in $ana-list//n
           let $code := data($stat/@v)
           let $num := number($stat/text())
           let $desc := data($stat/@desc)
       group by $code
       order by $code
       return <n of="{$sum-of-recs-updated}" in-n-sheets="{count($stat)}" v="{$code}" 
                 source="{string-join(data($ana-list[n/@v=$code]/@key)," ")}"
                 desc="{string-join(distinct-values($desc),"|")}">{sum($num)}</n>

   (: calc percentage :)
   
   let $omit-attrs := if ($is-numeric-code) then "source" else ("source","desc")
   let $results := for $r in $results
                   let $percentage := (100 * number($r/text()) ) div $sum-of-recs-updated
                   let $pc := format-number(xs:float($percentage),"###.00")
                   let $pc := replace(xs:string($pc),"^(\d*\.\d\d\d).*$","$1")
                   let $warn := if ($is-numeric-code and $r/@desc="") then <a MISSING-VALUE="yes"/> else ()
                   let $sources := if (not($warn)) then () else $r/@source (: show the source spreadsheets for the missing values :)
                   let $theValue := string(data($r/@v))
                   order by $percentage descending
                   return <n pc="{$pc}">{$r/@*[not(local-name()=$omit-attrs)]}{$warn/@*}{$sources}{$r/text()}</n>

   let $derived := $ssana:DERIVATIVES//item[name/text()=$fieldName]
   let $derivative := if ( not($derived) ) then () else
       let $theLen := number($derived/length/text())
       let $theNew := $derived/derive/text()
       let $dbg := <dbg>{$derived}<len>{$theLen}</len><new>{$theNew}</new></dbg>
       let $newRes := for $r in $results
                      let $val := substring($r/@v,1,$theLen)
                      let $num := $r/text()
                      group by $val
                      order by $val
                      return <n of="{$sum-of-recs-updated}" v="{$val}"> {sum($num)} </n>
       (: do percentages :)
       let $newRes := for $r in $newRes
                   let $percentage := (100 * number($r/text()) ) div $sum-of-recs-updated
                   let $pc := format-number(xs:float($percentage),"###.00")
                   let $pc := replace(xs:string($pc),"^(\d*\.\d\d\d).*$","$1")
                   let $warn := if ($is-numeric-code and $r/@desc="") then <a MISSING-VALUE="yes"/> else ()
                   let $sources := if (not($warn)) then () else $r/@source (: show the source spreadsheets for the missing values :)
                   let $theValue := string(data($r/@v))
                   order by $percentage descending
                   return <n pc="{$pc}">{$r/@*}{$warn/@*}{$r/text()}</n>

       return <ana aggregated-sheets="{$occs}" data-item-name="{$theNew}" data-item-type="derivative" derived-from="{$fieldName}"
                   n-recs-updated="{$sum-of-recs-updated}"> { $newRes } {$dbg} </ana>
                 

   let $debug := <debug>{$ana-list}<of sum="{$sum-of-recs-updated}">{$of}</of></debug>

   return (<ana aggregated-sheets="{$occs}" data-item-name="{$fieldName}" n-recs-updated="{$sum-of-recs-updated}">{$data-item-type}
          { $results }
          </ana>, $derivative)
};

