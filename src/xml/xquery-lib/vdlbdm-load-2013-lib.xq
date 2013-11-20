(: utility functions for loading khrd :)

(: ############# NON-A flag???? :)

module namespace condig = "http://fas/condig";
import module namespace functx="http://www.functx.com" at "/usr/local/lib/xquery/functx";
import module namespace ap20="http://fas/ap20" at "/usr/local/lib/xquery/ap20";

declare function condig:add_person_common ( $s as element(person), $id as xs:integer, $keys as xs:string, $first_key as xs:string ) as element()* {

   let $gender := if ( $s/identity/sex[text()="Male"] ) then 1
                  else if ( $s/identity/sex[text()="Female"] ) then 2
                       else 9

   let $occupation :=
       let $s := for $o in $s/eventList//occupation
                 return concat(",",$o)
       return substring($s,2)
   let $occupation := "" (: for alternate surnames :)

   let $toponym := if ($s/eventList/event[@type="birth"]/place/text())
                   then $s/eventList/event[@type="birth"]/place/text()
                   else ""
   let $toponym := "" (: for alternate forenames :)

   let $nonAboriginal := if ( $s/cohorts/flags/text() = "Non Aboriginal" ) then <notA>1</notA> else ()
   let $sdatax := () 
   let $sdata := 
       for $grp in $s/cohorts//group
          let $e := data($grp/@type)
          let $v := $grp/text()
       where $e != "clan" (: do this through the family sources :)
       return element {$e} { $v }
   let $sdata := ($nonAboriginal,$sdata)

   (: create the person :)
   let $persons := ap20:add_person($id,$gender,$s/identity/forename,
                                      "",
                                      $toponym,
                                      $s/identity/surname,
                                      $occupation,
                                      "",
                                      $keys,$first_key,$sdata)

   (: create a birthevent, eventid tag+id :)

   let $Ev := $s/eventList/event[@type="birth"]
   let $tag := 2
   let $evId := xs:integer(concat($tag,$id))
   let $evDate := $Ev/date
   let $date := data($evDate/@when)
   let $date_text := $evDate/text()
   let $dparts := ap20:event_date("birth",$date)
   let $sdata := ()
   let $b_ev := ( ap20:add_event($evId,$tag,0,$dparts[1],$dparts[2],$dparts[3],$sdata),
                  ap20:add_participant_principal($id,$evId) )
   let $birth_date := if ($dparts[2] = '0001-01-01') then () else $dparts[2]


   (: and a death event :)
   let $Ev := $s/eventList/event[@type="death"]
   let $tag := 3
   let $evId := xs:integer(concat($tag,$id))
   let $evDate := $Ev/date
   let $date := if (data($evDate/@when)) then data($evDate/@when)
       else if ($birth_date and functx:is-a-number($Ev/deathAge/text()))
            then let $age := xs:integer($Ev/deathAge/text())
                 let $birthYr := xs:integer(substring($birth_date,1,4))
                 let $deathYr := $birthYr + $age
                 return xs:string($deathYr)
            else ()
   let $date_text := $evDate/text()
   let $dparts := ap20:event_date("death",$date)
   let $sdata := ()

   let $d_ev := if ($Ev)
                then (ap20:add_event($evId,$tag,0,$dparts[1],$dparts[2],$dparts[3],$sdata),
                      ap20:add_participant_principal($id,$evId) )
                else ()

   return ($persons,$b_ev,$d_ev,$sdata,<s/>)

};

declare function condig:add_person_from_multiple_elements ( $s as element(person)* ) as element()* {
   let $id := xs:integer(data($s/@dbid_v1)[1])

   (: choose the one with the most events and elements -- the omitted ones need to be checked by Nick :)

   let $evMax := <x>{for $e at $i in $s//eventList 
                     let $ne := count($e//*[local-name() != "MATCH" and local-name() != "link"])
                     let $n := count($e//event) 
                     order by $n descending, $ne descending
                     return <eventList i="{$i}" n="{$n}" ne="{$ne}">{$e}</eventList> }</x>
   let $evMaxNo := data(($evMax//eventList)[1]/@i)
   let $source := for $e at $i in $s
                  where $i = $evMaxNo
                  return $e
   let $useId := data($source/@id)
   let $keys := ($useId,
       for $p in $s
       let $id := data($p/@id)
       where $id != $useId
       return $id )

   let $use_legid := $source/identity/legacy_id/text()
   let $leg_ids := ($use_legid,
       for $p in $s
       let $id := $p/identity/legacy_id/text()
       where $id != $use_legid
       return $id
       )
   let $leg_ids := distinct-values($leg_ids)
   let $first_key := $useId
   let $keys := ap20:stringarray( ($keys, $leg_ids) )
   
   return condig:add_person_common($source,$id,$keys,$first_key) 

};

declare function condig:add_person_from_element ( $s as element(person) ) as element()* {
   let $id := if      ($s/@dbid_v1) then data($s/@dbid_v1)
              else if ($s/@dbid_new) then data($s/@dbid_new)
              else "-1" (: do not load :)

   let $keys := ap20:stringarray( (data($s/@id),$s/identity/legacy_id/text()) )
   let $first_key := data($s/@id)

   return condig:add_person_common($s,xs:integer($id),$keys,$first_key) 
};

declare function condig:add_relations_from_element ( $s as element(person) ) as element()* {

   let $id := if      ($s/@dbid_v1) then data($s/@dbid_v1)
              else if ($s/@dbid_new) then data($s/@dbid_new)
              else "-1" (: do not load :)

   let $relid_father := concat($id,"1")
   let $father_dbid := ($s/RELATIONS/father/@dbid)
   let $father := 
       if ($father_dbid) 
       then <s>{concat( 'INSERT into relations values (', $relid_father,',', $id,',', $father_dbid, ');') }</s>
       else ()

   let $relid_mother := concat($id,"2")
   let $mother_dbid := ($s/RELATIONS/mother/@dbid)
   let $mother := 
       if ($mother_dbid) 
       then <s>{concat( 'INSERT into relations values (', $relid_mother,',', $id,',', $mother_dbid, ');') }</s>
       else ()
   return ($father,$mother)

};



