(: low level functions for loading yggdrasil/postgresql, database schemea dependant. :)

module namespace ap20 = "http://fas/ap20";

declare function ap20:comment ( $c as xs:string* ) as element()* {
   let $cmmt := for $s in $c return <s>--- {$s}</s>
   return (<s>---</s>,$cmmt,<s>---</s>)
};

declare function ap20:escape( $s as xs:string* ) as xs:string* {
   replace($s,"'","''","m")
};

declare function ap20:stringarray ( $s as xs:string* ) as xs:string* {
   
   let $str := string-join( for $k in $s return concat('"',$k,'"') , "," )
   return concat("{",$str,"}")
   
};

declare function ap20:paras_to_sourcetext ( $p as element()* ) as xs:string* {
    let $theText := for $para in $p return $para/text()
    let $joined := string-join( $theText," ##BR##" )
    return $joined
};

declare function ap20:elements_to_hstore ( $sdata as element()* ) as xs:string* {
   let $str := for $e in $sdata
               let $h := local-name($e)
               let $v := ap20:escape($e/text())
               return concat(',"',$h,'"=&gt;"',$v,'"')
   let $str := string-join($str,"")
   return substring($str,2)
};

declare function ap20:add_source (
   $source_id    as xs:integer,
   $parent_id    as xs:integer,
   $source_text  as xs:string*,
   $sort_order   as xs:integer,
   $source_date  as xs:string*,
   $part_type    as xs:integer,
   $ch_part_type as xs:integer,
   $xids         as xs:string*,
   $sdata        as xs:string*,
   $stree        as xs:string* ) as element()* {

<s id="{$source_id}" ent="{$stree}">{concat(
  'INSERT into sources VALUES(',
     $source_id,',',
     $parent_id,',',
     "'",ap20:escape($source_text),"'",',',
     $sort_order,',',
     $source_date,',',
     $part_type,',',
     $ch_part_type,',',
     "'",$xids,"'",',',
     "'",$sdata,"'",',',
     "'",$stree,"'",
 ');'
         )
}</s>

};



declare function ap20:event_date( $type as xs:string, $s as xs:string* ) as xs:string* {
   (: return 2 dates - the string and a valid date and perhaps some text/task:)
   let $dparts := tokenize($s,"-")
   return
   if ((count($dparts) = 0) or ($s = "-1"))
   then (: missing birth date :)
        if ($type="birth")
        then ('000000003000000001','0001-01-01',concat('[task=findBirthdate]',$s))
        else if ($type="death")
             then ('000000003000000001','0001-01-03',concat('[task=findDeathdate]',$s))
             else if ($type="marriage")
                  then ('000000003000000001','0001-01-02',concat('[task=findMarriagedate]',$s))
                  else ('000000003000000001','0001-01-02','')
   else if (count($s) = 1)
        then
            if (matches($s,'^\d{4}$'))
            then (concat($s,'00003000000001'),concat($s,'-01-01'),'')
            else if (matches($s,'^\d{4}\-\d{2}$'))
                 then (concat($dparts[1],$dparts[2],'003000000001'), concat($s,'-01'),'')
                 else if (matches($s,'^\d{4}\-\d{2}\-\d{2}$'))
                      then (concat($dparts[1],$dparts[2],$dparts[3],'3000000001'),  $s, '')
                      else ('000000003000000001','','')
        else (: multiples :)
            ('000000003000000001','','[task=resolveMultipleDates]',$s,']')
};


declare function ap20:add_participant_principal ( $person_fk   as xs:integer, $event_fk    as xs:integer) as element()* {
<s>
    {concat(
                       'INSERT into participants VALUES(',
                       $person_fk,',',
                       $event_fk,
                       ');'
                     )
    }</s>
};


declare function ap20:add_place (
   $place_id   as xs:integer,
   $level_1    as xs:string,
   $level_2    as xs:string,
   $level_3    as xs:string,
   $level_4    as xs:string,
   $level_5    as xs:string
) as element()* {

<s>
    {concat(
                       'INSERT into places VALUES(',
                       $place_id,',',
                       "'",ap20:escape($level_1),"'",',',
                       "'",ap20:escape($level_2),"'",',',
                       "'",ap20:escape($level_3),"'",',',
                       "'",ap20:escape($level_4),"'",',',
                       "'",ap20:escape($level_5),"'",
                       ');'
                     )
    }</s>
};

declare function ap20:add_event (
   $event_id   as xs:integer,
   $tag_fk     as xs:integer,
   $place_fk   as xs:integer,
   $event_date as xs:string*,
   $sort_date  as xs:string*,
   $event_note as xs:string,
   $sdata        as element()*
) as element()* {

<s eid="{$event_id}">
    {concat(
                       'INSERT into events VALUES(',
                       $event_id,',',
                       $tag_fk,',',
                       $place_fk,',',
                       "'",$event_date,"'",',',
                       "'",$sort_date,"'",',',
                       "'",ap20:escape($event_note),"'",',',
                       "'",ap20:elements_to_hstore($sdata),"'",
                       ');'
                     )
    }</s>
};

declare function ap20:add_person (
   $person_id    as xs:integer,
   $gender       as xs:integer,
   $forename     as xs:string*,
   $patronym     as xs:string*,
   $toponym      as xs:string*,
   $surname      as xs:string*,
   $occ          as xs:string*,
   $epithet      as xs:string*,
   $keys         as xs:string,
   $first_key    as xs:string,
   $sdata        as element()*
) as element()* {


   let $person := <s id="{$person_id}" key="{$first_key}">{concat(
                       'INSERT into persons VALUES(',
                       $person_id,',',
                       'now(),',
                       $gender,',',
                       "'",ap20:escape($forename),"'",',',
                       "'",ap20:escape($patronym),"'",',',
                       "'",ap20:escape($toponym),"'",',',
                       "'",ap20:escape($surname),"'",',',
                       "'",ap20:escape($occ),"'",',',
                       "'",ap20:escape($epithet),"'",',',
                       "'",$keys,"'",',',
                       "'",ap20:elements_to_hstore($sdata),"'",
                       ');'
                     )
           }</s>

   return $person

};

