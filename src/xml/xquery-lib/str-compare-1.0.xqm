(: http://bennettweb.wordpress.com/2009/11/13/levenshtein-distance-in-xquery/ :)
module namespace  str-compare = "http://fas/str-compare" ;

declare function str-compare:lsd($string1 as xs:string?, $string2 as xs:string?)
as xs:integer
{ 
  str-compare:_lsd(
                        fn:string-to-codepoints($string1),
                        fn:string-to-codepoints($string2),
                        fn:string-length($string1),
                        fn:string-length($string2),
                       (1, 0, 1),
                       2
  ) 
};
declare function str-compare:_lsd(
                                              $chars1 as xs:integer*, 
                                              $chars2 as xs:integer*, 
                                              $length1 as xs:integer, 
                                              $length2 as xs:integer,
                                              $lastDiag as xs:integer*, 
                                              $total as xs:integer)
as xs:integer 
{ 
  let $shift := if ($total > $length2) then ($total - ($length2 + 1)) else 0 
  let $diag := 
    for $i in (fn:max((0, $total - $length2)) to fn:min(($total, $length1))) 
  let $j := $total - $i let $d := ($i - $shift) * 2 
  return ( 
    if ($j lt $length2) then 
      $lastDiag[$d - 1]
    else () ,
    if ($i = 0) then $j 
    else if ($j = 0) then $i 
    else fn:min(($lastDiag[$d - 1] + 1, 
                      $lastDiag[$d + 1] + 1,
                      $lastDiag[$d] + (if ($chars1[$i] = $chars2[$j]) then 0 else 1)
                    ))
    )
  return
    if ($total = $length1 + $length2) then fn:exactly-one($diag)
    else str-compare:_lsd($chars1, $chars2, $length1, $length2, $diag, $total + 1) 
};
