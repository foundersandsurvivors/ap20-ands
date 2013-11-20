(: 
 : ----------------------------------------------------
 : The FAS XQuery Function Library for bdm/diggers work
 : ----------------------------------------------------
:)
module namespace bdm = "http://fas/bdm" ;
import module namespace functx="http://www.functx.com" at "/usr/local/lib/xquery/functx";

declare variable $bdm:SEXMAP := <map>
<e type="Female">2</e>
<e type="Male">1</e>
</map>;

(: data MAP for diggers/BDM linkage/genealogy

   rtcode is the yggdrasil source parent number
   evType is gedcom event/yggdrasil tag

   Sources:   RT   Range of keys (not necessarily in sequence)
   10 ww1 aif  : ers:
   11 births   : kgb: 100001 -- 295453
   12 marriages: kgm:      1 --  99999
   13 deaths   : kgd:      1 -- 200079

   idnums get normalised to 6 digit with leading zeroes
   prefix: 2 digits: for rectype in yggdrasil (see sources)
   suffix: 1 digit : various roles in records
   (9 digit key fits in int4 and can be used in yggrasil as recids)

   e.g source     : 10000001 is yggdrasil key for source record xml:id="ers1"
       event      : 10000001 is yggdrasil key for the associated enlistment event
       participant: 100000010 is yggdrasil key for the digger who enlisted

:)

declare variable $bdm:RTMAP := <map>
<e type="pm" cite="PtoM">
   <recodes>
     <ename type="marriageRef" spec="correct">
        <v recode="RGD36/3 : 1836/3326">RGD36/3 3326:1836</v>
        <v recode="RGD36/3 : 1836/3326">RGD36/3: 3326/1836</v>
        <v recode="RGD37/10 : 1851/89">RGD37/10: 1851/89</v>
        <v recode="RGD37/11 : 1852/287">RGD37/11: 1852/287</v>
        <v recode="RGD37/12 : 1853/1072">RGD37/12: 1853/1072</v>
        <v recode="RGD37/13 : 1853/726">RGD37/13: 1853/726</v>
        <v recode="RGD37/13 : 1854/1327">RGD37/13 . 1854/1327</v>
        <v recode="RGD37/13 : 1854/632">RGD37/13 :1854/632</v>
        <v recode="RGD37/2 : 1841/1146">RGD37/2: 1841/1146</v>
        <v recode="RGD37/4 : 1844/1489">RGD37/4 : 1844/1489d</v>
     </ename>
   </recodes>
</e>
<e type="ers" cite="AIF(WWI)-ServiceNo:">
   <rtcode>10</rtcode>
   <evType>_ENLWWI</evType>
   <link2desc>digger</link2desc>
   <role type="enlister">0</role>
   <recodes>
     <ename type="relNextOfKin1" spec="replace">
        <v recode="aunt">Aunt</v>
        <v recode="aunt">AUNT</v>
        <v recode="friend">Bingham</v>
        <v recode="brother">B</v>
        <v recode="child">C</v>
        <v recode="daughter">Daughter</v>
        <v recode="daughter">DAUGHTER</v>
        <v recode="son">ELDEST SON</v>
        <v recode="father">F</v>
        <v recode="friend">Friend</v>
        <v recode="friend">FRIEND</v>
        <v recode="grandmother">GRANDMOTHER</v>
        <v recode="guardian">GUARDIAN</v>
        <v recode="mother">M</v>
        <v recode="n/a">N/A</v>
        <v recode="son">SON</v>
        <v recode="son">S</v>
        <v recode="uncle">UNCLE</v>
        <v recode="wife">W</v>
     </ename>
   </recodes>
</e>
<e type="kgb" cite="RGB:TAS">
   <rtcode>11</rtcode>
   <evType>BIRT</evType>
   <link2desc>birth</link2desc>
   <role type="baby" note="no role - baby in birth dbid is same as source dbid"></role>
   <role type="father">1</role>
   <role type="mother">2</role>
   <role type="informant">8</role>
   *<role type="registrar">9</role>
   <recodes>
     <ename type="bregNo" spec="correct">
        <v recode="0">1E-160</v>
        <v recode="0">3E-082</v>
     </ename>
     <ename type="birthRegDate" spec="correct">
        <replace recid="101787">1843-12-03</replace>
     </ename>
     <ename type="bregDistrict" spec="correct">
        <v recode="?"></v>
        <v recode="Hobart">Hobart Town</v>
        <v recode="Horton">Hortin</v>
        <v recode="Horton">Horton (Circular Head)</v>
        <v recode="Norfolk Island">Norfolk Island (V.D.L.)</v>
        <v recode="Prossers Plains/Sorell">Sorell &amp; Prossers</v>
        <v recode="Tasman Peninsula">Tasman Peninsula (Forresters Peninsula)</v>
        <v recode="Victoria">Victoria, Huon</v>
     </ename>
   </recodes>
</e>
<e type="kgm" cite="VDL:RGM">
   <rtcode>12</rtcode>
   <evType>MARR</evType>
   <link2desc>marriage</link2desc>
   <role type="husband">1</role>
   <role type="wife">2</role>
</e>
<e type="kgd" cite="VDL:RGD">
   <rtcode>13</rtcode>
   <link2desc>death</link2desc>
   <evType>DEAT</evType>
   <role type="deceased">1</role>
   <recodes>
     <ename type="bregNo" spec="correct">
        <v recode="1.1">1.01</v>
     </ename>
   </recodes>
</e>
</map>;

(: correction utilities :)

(: recodes :)

declare function bdm:recode ( $rt as xs:string, $e as element()* ) as element()* {
   let $recodeSpec := $bdm:RTMAP/e[@type=$rt]/recodes/ename[@type=local-name($e)]
   return if ( $recodeSpec )
          then
              let $newval := data($recodeSpec/v[text()=$e/text()]/@recode)
              let $newcon := if ( $newval ) then $newval else $e/text()
              return element { node-name($e) } {$e/@*, $newcon } 
          else $e
};

(: function bdm:idnumToRef convert number to NNnnnnnnN where NN=rectype nnnnnn=key N=role :)

declare function bdm:idnumToRef ( $rt as xs:string, $role as xs:string, $num as xs:string? ) as xs:string* {

  let $roleNum := if ($role='source') then '' else $bdm:RTMAP/e[@type=$rt]/role[@type=$role]
  let $padNum  := if ($num) then functx:pad-integer-to-length($num,6) else ''
  return concat($bdm:RTMAP/e[@type=$rt]/rtcode,$padNum,$roleNum)
} ;

declare function bdm:sourceParent ( $rt as xs:string ) as xs:string* {
  $bdm:RTMAP/e[@type=$rt]/rtcode/text()
} ;

declare function bdm:makeLinkElement ( $elWithId as element()*, $link2rectype as xs:string, $linkrole as xs:string, $rec as element()* ) as element()* {
   let $linkdesc := $bdm:RTMAP/e[@type=$link2rectype]/link2desc/text()
   let $informationReLinkage := 
       if ( $link2rectype = 'kgm' ) 
       then $rec//(marriageId|marriagePlace|marriageDate) 
       else if ( $link2rectype = 'kgb' and local-name($rec) = 'ers' )
            then $rec//(birthDateCalc|birthPlaceTown|birthPlaceState|birthPlaceAddnl|birthcode1|birthcode2|birthYr|birthMo|birthDy)
            else ()
   return
   if ( not($elWithId) ) 
   then 
      let $reason := if ( $rec/birthDate/text() and substring($rec/birthDate,1,4) < '1838' )
                     then <x reason="{ concat( 'Birth date ',$rec/birthDate,' prior to VDL marriage regn' ) }" />
                     else if ( $rec/birthDate/text() )
                          then <x task="start" queue="{ concat( 'birthYr',substring($rec/birthDate,1,4) ) }" />
                          else <x task="start" queue="birthYr0000" />
      return <todolink to="{$linkdesc}" role="{$linkrole}" >{ $reason/@* } { $informationReLinkage } </todolink>
   else
      let $recnum := xs:integer( $elWithId/text() )
      let $fasid  := concat( $link2rectype, functx:pad-integer-to-length( $recnum, 6) )
      let $dbid   := bdm:idnumToRef ( $link2rectype, $linkrole, $elWithId/text() )
      let $linkdesc := $bdm:RTMAP/e[@type=$link2rectype]/link2desc/text()
      return <link type="{$linkdesc}" recnum="{$recnum}" role="{$linkrole}" dbid="{$dbid}"><match>{$fasid}</match> { $informationReLinkage }</link>
};

declare function bdm:sexToIsonum ( $n as xs:string* ) as element()* {
   let $sexNum := if ( $n ) then $bdm:SEXMAP/e[@type=$n] else "9"
   return <x sex="{$sexNum}"/>
};

declare function bdm:correctionV4 ( $v as xs:string* ) as xs:string* {
   if ( $v = '235' ) then '23' else if ( $v = '351' ) then '35' else if ( $v = '355' ) then '35' else if ( $v = '356' ) then '35' else $v
};

declare function bdm:correctionV5 ( $v as xs:string* ) as xs:string* {
   if ( $v = '671' ) then '67' else if ( $v = '421' ) then '42' else $v
};

declare function bdm:citeref ( $rt as xs:string, $n as element()* ) as xs:string* {

  (: reb to help me clean this up. registerNum ??? pdfNum ??? should bregNo be yyyy-bregNo ??? :)
  (: check these citation formats with rebecca :)
  if ( $rt = 'kgb' ) then
          concat( $bdm:RTMAP/e[@type=$rt]/@cite,
                  functx:pad-integer-to-length($n/no4,3),
                  '_',
                  functx:pad-integer-to-length($n/no5,3),
                  '/',
                  functx:pad-integer-to-length($n/no6,3),
                  ':',
                  functx:pad-integer-to-length($n/bregNo,6)
                )
  else if ( $rt = 'kgm' ) then 
          concat( $bdm:RTMAP/e[@type=$rt]/@cite,
                  '/',
                  'kgm',
                  functx:pad-integer-to-length($n/rec_no,6) 
                )
  
  else if ( $rt = 'kgd' ) then 
          let $v3x := if (matches($n/V3/text(),"\.")) then $n/V3/text() else concat($n/V3,".0")
          let $v3 := tokenize($v3x,"\.")
          let $v3_seq_integer := (number($v3[1]) * 10) + number($v3[2])
          let $regYr := substring($n/deathRegDate/@deathRegDateX,1,4)
          let $regYr := if ( $regYr = "-1" ) then substring($n/deathDate/@deathDateX,1,4) else $regYr (: use death year if missing regyr :)

          (: use regPlace and V3, which appears to be a sequence no for that place,
             with occasional .1 insertions. V4/5/6 appears to be a microfilm reference number i.e. series.
          :)
          return concat( $bdm:RTMAP/e[@type=$rt]/@cite,
                  ":",
                  $n/district,
                  ":",
                  $v3_seq_integer,
                  "__",
                  $regYr,
                  "__",
                  functx:pad-integer-to-length(bdm:correctionV4($n/V4/text()),3),
                  '/',
                  functx:pad-integer-to-length(bdm:correctionV5($n/V5/text()),2),
                  '/',
                  functx:pad-integer-to-length($n/V6,2)
                )
  else if ( $rt = 'ers' ) then concat( $bdm:RTMAP/e[@type=$rt]/@cite, $n/serviceNumber)
  else ''
};

declare function bdm:normnamesShort ( $fn as element()*, 
                                 $midn as element()*, 
                                 $maidenn as element()*, 
                                 $sn as element()* ) as element()* {
  
  let $maidenname := if ($maidenn) then <maidenname>{$maidenn/text()}</maidenname> else ()
  let $midname := if ($midn) then <middlename>{$midn/text()}</middlename> else ()
  let $label := normalize-space( concat ( $sn, ', ', $fn, ' ', $midn) )
  return <persName>{$label}</persName>
};

declare function bdm:normnames ( $fn as element()*,
                                 $midn as element()*,
                                 $maidenn as element()*,
                                 $sn as element()* ) as element()* {

  let $maidenname := if ($maidenn) then <maidenname>{$maidenn/text()}</maidenname> else ()
  let $midname := if ($midn) then <middlename>{$midn/text()}</middlename> else ()
  let $label := normalize-space( concat ( $sn, ', ', $fn, ' ', $midn) )
  return (<persName>{$label}</persName>,<forename>{$fn/text()}</forename>,
  $midname,$maidenname,
  <surname>{$sn/text()}</surname>)
};

(: .................................. date functions :)

(: calc a correct date for use in postgresql :)
declare function bdm:isodate_for_postgres ( $s as xs:string? ) as xs:string {
   if ( bdm:date_is_valid($s) ) then $s
   else (: check year/month/day in turn and choose defaults :)
        let $parts := tokenize($s,"-")
        return
        if ( bdm:year_is_valid($parts[1]) )
        then if ( bdm:month_is_valid($parts[2]) )
             then (: day is bad - use 1 :)
                  string-join( ($parts[1], $parts[2], "01"), "-")     
             else 
                  (: invalid month :)
                  if ( bdm:day_is_valid($parts[3]) )
                  then string-join( ($parts[1], "01", $parts[3]), "-")
                  else string-join( ($parts[1], "01", "01"), "-")
        else (: bad year -- cannot recover :)
             ""
};


declare function bdm:day_is_valid ( $s as xs:string? ) as xs:boolean {
   if ( matches($s,"^\d\d$") )
   then if ( (xs:integer($s) >= 1) and (xs:integer($s) <= 31) ) then true()
        else false()
   else false()
};

declare function bdm:month_is_valid ( $s as xs:string? ) as xs:boolean {
   if ( matches($s,"^\d\d$") )
   then if ( (xs:integer($s) >= 1) and (xs:integer($s) <= 12) ) then true()
        else false()
   else false()
};

declare function bdm:year_is_valid ( $s as xs:string? ) as xs:boolean {
   if ( matches($s,"^\d\d\d\d$") )
   then if ( xs:integer($s) > 1828 )
        then if ( xs:integer($s) < 2013 ) then true()
             else false()
        else false()
   else false()
};

declare function bdm:date_is_valid ( $s as xs:string? ) as xs:boolean {
   let $parts := tokenize($s,"-")
   return
   if ( bdm:year_is_valid($parts[1])  and
        bdm:month_is_valid($parts[2]) and 
        bdm:day_is_valid($parts[3])  
      ) 
   then true()
   else false()

};

(: pass a no like 100.1 return a string like 00010010 which is sortable :)

declare function bdm:refnum2number ( $s as xs:string? ) as xs:integer {
  if ( functx:is-a-number($s) )
  then
    if (matches($s,"^\d*\.\d*$"))
    then let $part := tokenize( $s,"\.")
         return (xs:integer(concat("0",$part[1])) * 10) + xs:integer(concat("0",$part[2]))
    else xs:integer($s) * 10
  else 0

};

declare function bdm:refnum2sortablestring ( $s as xs:string?, $length as xs:integer ) as xs:string* {
  functx:pad-integer-to-length( bdm:refnum2number($s), $length )
};


