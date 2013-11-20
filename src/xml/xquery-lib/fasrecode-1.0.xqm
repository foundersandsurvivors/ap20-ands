(: General purpose recoding and xml rewriting functions with actions logged in SYSTEM element :)

module namespace fr = "http://fas/fr";
import module namespace functx="http://www.functx.com" at "/usr/local/lib/xquery/functx";


declare function fr:xpathcalc ($s as xs:string, $me as element(), $top_ancestorname as xs:string) as xs:string {
    if ( local-name($me/parent::*) = $top_ancestorname )
    then $s
    else let $new := concat( local-name($me/parent::*),"/",$s )
         return fr:xpathcalc($new,$me/parent::*,$top_ancestorname)
};

declare function fr:apply-recodes ( $RECNAME as xs:string,                                                                                                                          $MYTAG as xs:string, $RECODES as element()*, $myid as xs:string, $target as element(),
                                       $isTop as xs:string ) as element() {
      let $match_id_pattern := concat($myid,",")
      let $target_el := local-name($target)
      let $match_ypid := if ($target/@ypid) then concat($target/@ypid,",") else () (: where we have multiple participants :)

      (: apply changes as per each $RECODES here to each element in the target :)

      let $new := for $e at $i in $target/child::*[not(local-name()="SYSTEM")]

                  let $xp := fr:xpathcalc(local-name($e),$e,$RECNAME)

                  let $updated :=

                      (: where we have an xpath match and the value matches
                         apply the correction and log it with a corrItem :)
                      for $corr in $RECODES[@target=$xp and del/text()=$e/text()]
                          let $apply :=
                              if ( not($corr/@xids) ) then 1
                              else if ( matches($corr/@xids,$match_id_pattern) ) then 1
                                   else if ($match_ypid)
                                        then if (matches($corr/@xids,$match_ypid)) then 1 else 0
                                        else 0
                      where $apply
                      return (functx:replace-element-values($e, $corr/add/text()),
                             <SYS>{ fr:corrItem ( $e, ($corr/add/text(),$xp,$corr/@n) ) } </SYS>)

                  return if ( $updated )
                         then $updated
                         else if ( $e/child::* )
                              then fr:apply-recodes($RECNAME,$MYTAG,$RECODES,$myid,$e,"") (: recurse if children :)
                              else $e

      let $allCorr := if ($isTop) then $new//corr[parent::SYS] else ()
      let $makeSystem := if ($allCorr) then fr:corrBlock ( $allCorr, $MYTAG, $target/SYSTEM ) else ()
      let $new := if ($isTop)
                  then for $e in $new
                       return functx:remove-elements-deep($e,"SYS")
                  else $new

   return element {local-name($target)} { $target/@*, $new,  $makeSystem }
};

(: CORRECTIONS: pass around SYSTEM element with corrections and various contents as required :)
declare function fr:corrBlock ( $corrections as element()*, $responsible as xs:string, $systemExisting as element()* ) as element()* {
   if ($corrections)
   then let $newCorrections := <ab type="corrections" date="{current-date()}" resp="{$responsible}">{$corrections}</ab>
        return if ($systemExisting)
               then <SYSTEM>{$systemExisting/@*}{$newCorrections}{$systemExisting/*}</SYSTEM>
               else <SYSTEM>{$newCorrections}</SYSTEM>
   else $systemExisting
};

(: create a system corr record: pass changed element + the new value + optionally pas a custom type and a tag :)
declare function fr:corrItem ( $correctedElement as element(), $add as xs:string* ) as element() {
let $newval := $add[1]
let $type := if ($add[2]) then <x e="{$add[2]}"/> else <x e="{local-name($correctedElement)}"/>
let $tag := if ($add[3]) then <x n="{$add[3]}"/> else ()
return <corr>{$type/@*}{$tag/@*}<del>{$correctedElement/text()}</del><add>{$newval}</add></corr>
};

