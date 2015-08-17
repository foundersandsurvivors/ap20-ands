(: fasutil-1.0.xqm
   fas xml to ap20 recid mapping and misc/common utilities copied from klaatu; saxon version; no map function; no functx dependancy
:)
module namespace  fu = "http://fas/fu" ;

(: 10 to 199 for taho CON series <db>raw_ai</db><xq>//ai[@id="ID"]</xq> :)

(: we don't have maps in saxon so adapt logic for 2 synchronised sequences :)
(: vdlbdm SOURCE recs are 301-303 with 2nd digit reserved for ROLES 0=baby; 1=father 2=mother :)
(: ASSUME standard integers ie. max value = 2147483648 :)
(: silentkey means the key is NOT prefixed, its already embedded in the number :)
(: Warnings:
   -- NOTE the ainnnnCh ids represent a personList of ALL the children of a convict
   -- role keys need to be same length as the base types key
   -- we can't do hga/hgf 
   Con's unlikely to be of use: 85 93
   Seq on role's means notation of ".NN" is allowed to indicate the sequence of the part
   MAXINT(4)=2147483647
:)
declare variable $fu:rectypes-in-pubsearch := ("ai","dcm","dcf","om","of","dlm","dlf","c23a","c31a","c33a","c40a");
declare variable $fu:fas := <map>
<rec person="convict" key="12"><id>ccc</id><db>ccc_mediaflux</db><xq>//person[@id="ID"]</xq><desc>Convicts Contrib</desc><role altid="Scc" key="13" seq="marriage" name="Spouse">Sp</role><role altId="Bcc" key="14" seq="birthOfChild" name="Child">Ch</role><role altId="Dcc" key="15" seq="aifDescendant" name="AifDescendant">Di</role></rec>
<rec person="convict" key="16" lz="5"><id>ai</id><db>raw_ai</db><xq>//ai[@id="ID"]</xq><role key="17" info="Child of convict" name="Children">Ch</role><desc>Taho Index</desc></rec>
<rec person="convict_M" key="18" silentkey="1"><id>dlm</id><db>raw_dlm</db><xq>//dlm[@id="ID"]</xq><desc>Con18</desc></rec>
<rec person="convict_F" key="19" silentkey="1"><id>dlf</id><db>raw_dlf</db><xq>//dlf[@id="ID"]</xq><desc>Con19</desc></rec>
<rec person="convict_M" key="23"><id>c23a</id><db>raw_c23a</db><xq>//c23a[@id="ID"]</xq><desc>Con23</desc></rec>
<rec person="convict_M" key="31" silentkey="1"><id>c31a</id><db>raw_c31a</db><xq>//c31a[@id="ID"]</xq><desc>Con31</desc></rec>
<rec person="convict_M" key="33" silentkey="1"><id>c33a</id><db>raw_c33a</db><xq>//c33a[@id="ID"]</xq><desc>Con33</desc></rec>
<rec person="convict_F" key="40"><id>c40a</id><db>raw_c40a</db><xq>//c40a[@id="ID"]</xq><desc>Con40</desc></rec>
<rec person="convict_M" key="155"><id>om</id>><db>raw_om</db><xq>//om[@id="ID"]</xq><desc>Oxley Male</desc></rec>
<rec person="convict_F" key="156"><id>of</id><db>raw_of</db><xq>//of[@id="ID"]</xq><desc>Oxley Female</desc></rec>
<rec person="person" key="157"><id>di</id><db>raw_di</db><xq>//di[@id="ID"]</xq><desc>Departures from TAS</desc></rec>
<rec person="convict_M" key="158"><id>dcm</id><db>raw_dcm</db><xq>//dcm[@id="ID"]</xq><desc>Deaths Male</desc></rec>
<rec person="convict_F" key="159"><id>dcf</id><db>raw_dcf</db><xq>//dcf[@id="ID"]</xq><desc>Deaths Female</desc></rec>
<rec person="convict_F" key="210"><id>ff</id><db>raw_ff</db><xq>//ff[@id="ID"]</xq><desc>FCRC convict id (female)</desc></rec>
<rec person="wwidigger" key="220"><id>if</id><db>raw_if</db><xq>//if[@id="ID"]</xq><role key="220" name="DiggerWWI">DiI</role><desc>WWI Diggers</desc></rec>
<rec person="person"    key="240"><id>pm</id><db>norm_pm</db><xq>//pm[@id="ID"]</xq><desc>Permission to marry index (Taho)</desc></rec>
<rec person="tasborn" key="510"><id>kgb</id><db>norm_kgb</db><xq>//source[@ysid="ID"]</xq><role key="510" name="Baby">Ba</role><role key="511" name="Father">Fa</role><role key="512" name="Mother">Mo</role><role key="519" name="Informant">In</role><desc>TAS Birth Regn</desc></rec>
<rec person="person" key="520"><id>kgm</id><db>norm_kgm</db><xq>//source[@ysid="ID"]</xq><role key="521" name="Husband">Hu</role><role key="522" name="Wife">Wi</role><role key="523" seq="participant[role='witness']" nokey="reason:multiple" name="Witness">wt</role><desc>TAS Marriage Regn</desc></rec>
<rec person="person" key="530"><id>kgd</id><db>norm_kgd</db><xq>//source[@ysid="ID"]</xq><role key="530" name="Deceased">De</role><desc>TAS Death Regn</desc></rec>
<rec key="100" hi="1"><id>branch.vdlbdm</id><desc>TAS BDM Registrations</desc></rec>
<rec ev="BIRT" key="101" hi="1"><id>branch.kgb</id><db>vdlbdm/kgb_RegDistrictYear.xml</db><xq>//item[@dbid="ID"]</xq><desc>TAS Birth Regn</desc></rec>
<rec ev="MARR" key="102" hi="1"><id>branch.kgm</id><db>vdlbdm/kgm_RegDistrictYear.xml</db><xq>//item[@dbid="ID"]</xq><desc>TAS Marriage Regn</desc></rec>
<rec ev="DEAT" key="103" hi="1"><id>branch.kgd</id><db>vdlbdm/kgd_RegDistrictYear.xml</db><xq>//item[@dbid="ID"]</xq><desc>TAS Death Regn</desc></rec>
<rec person="ert" key="9" hi="1" lz="8" population="pop0"><id>ert</id><db>raw_ert</db><xq>//ert[@id="ID"]</xq><desc>Tas born WW1 enlistee</desc></rec>
</map>;

(:                               ert1709953:201709953 :)
(:
declare variable $fu:ManagedVHi_divisor  := 100000000;
                                            212082106
                                             32137991
                                             31010001
                                            310285652
                                            201765659
:)
declare variable $fu:ManagedVHi_above    :=1000000000;
declare variable $fu:ManagedHi_above     := 900000000;
declare variable $fu:ManagedHi_divisor   :=  10000000;
declare variable $fu:ManagedHi_divisor1  := 100000000;
declare variable $fu:ManagedLo_above     :=  10000000;
declare variable $fu:ManagedLo_divisor   :=   1000000;
declare variable $fu:numeric_length_lo   := 6;
declare variable $fu:numeric_length_hi   := 7;
declare variable $fu:numeric_length_vh   := 8;

(: convert <map> to php structure :)
declare function fu:map2php () as xs:string {
   let $cr := '&#10;'
   let $sq := "'"
   let $closeArray := ");"
   let $php := concat("$XMAPHi     = ", $fu:ManagedHi_above,"; # src dbids above this are special, map to xml rectypes/ids",$cr,
                      "$XMAPHi_div = ", $fu:ManagedHi_divisor,"; # ?",$cr,
                      "$XMAPLo     = ", $fu:ManagedLo_above,"; # src dbids above this are special, map to xml rectypes/ids",$cr,
                      "$XMAPLo_div = ", $fu:ManagedLo_divisor,"; # ?",$cr,
                      $cr,"/* entries with the 'hi' attribute exceed $XMAPHi; those without exceed $XMAPLo */",$cr
                     )

   (: these ids appear as prefixes to managed/external Yggdrasil sources :)

   let $SOURCEMAP := concat("$SOURCEMAP = array(","")
   let $entries :=
       for $rec in $fu:fas//rec[id]
       return concat($cr,"     ",$rec/@key, ' => array( "id" => "',$rec/id,'",',
                                                      ' "eventtype" => ',$sq,$rec/@ev,$sq,',',
                                                      ' "persontype" => ',$sq,$rec/@person,$sq,',',
                                                      ' "lz" => ',$sq,$rec/@lz,$sq,',',
                                                      ' "desc" => ',$sq,$rec/desc,$sq,',',
                                                      ' "db" => ',$sq,$rec/db,$sq,',',
                                                      ' "xq" => ',$sq,$rec/xq,$sq,',',
                                                      ' "hi" => ',$sq,$rec/@hi,$sq,
                                                      ' )')
   let $SOURCEMAP_array := concat($SOURCEMAP,string-join($entries,","),$closeArray)

   (: these ids appear as prefixes to people derived from managed/external Yggdrasil sources :)
   let $PEOPLEMAP := concat("$PEOPLEMAP = array(","")
   let $entries :=
       for $rec in $fu:fas//rec[id]
           let $related_roles := 
               for $role in $rec//role
                   (: these cohort attributes get added to p_sdata person hstore 
                      e.g. person's p_sdata: "pop"=>1 flags the person as part of population 1 i.e. the core population
                   :)
                   let $pop := if ($role/@population) then concat(', "cohortTag" => ',$sq,$role/@population,$sq) else ""
               return concat($cr,"     ",$role/@key, ' => array( "role" => "',$role/text(),'",',
                                                      ' "participantInSource" => ',$rec/@key,',',
                                                      ' "desc" => ',$sq,$role/@name,$sq, $pop, 
                                                      ' )')
       return $related_roles
   let $PEOPLEMAP_array := concat($PEOPLEMAP,string-join($entries,","),$closeArray)

   return concat('<?php',$cr,
                 "/* php source dbid to xml recid mappings (generated by http://www.fu.org) */",$cr,
                 $php,
                 $SOURCEMAP_array,$cr,$cr,
                 $PEOPLEMAP_array,$cr,
                 '?>',$cr)
};

(: ensure correct leading zeros e.g. for ert :)
declare function fu:normalise_xmlid ( $xmlid as xs:string* ) as xs:string {
   let $x := replace($xmlid,"^([a-zA-Z][0-9]*[a-zA-Z]+)(\d+)([a-zA-Z]*\d*)$", "$1:$2:$3")
   let $x := tokenize($x,":")
   let $rectype := $x[1]
   let $recnum := $x[2]
   let $role := $x[3]
   let $m := $fu:fas//rec[id/text()=$rectype]
   return if ($m/@lz)
          then concat($rectype,fu:pad-integer-to-length($recnum,$m/@lz),$role)
          else concat($rectype,xs:integer($recnum),$role)
};

(: 2147483647 allow sequenced elements e.g. 201086794.1 => ccc86794S1 => Scc86794.1 => ccc86794-marriage-1 :)
declare function fu:anyid2xmlid ( $xmlid as xs:string* ) as xs:string {
   let $orig-id := $xmlid
   let $xmlid := if ( matches($xmlid,"^\d+$") ) then fu:p2x( xs:integer($xmlid) ) else $xmlid
   let $m := fu:xmlid2map_entry($xmlid)

(: saxon no understand file:write
let $x := if ($m and matches($xmlid,"^[a-zA-Z][0-9]*[a-zA-Z]+\d+[a-zA-Z]*\d*$"))
          then file:write("/tmp/fu1","MATCHES $orig-id=["||$orig-id||"] $xmlid="|| $xmlid ||" m["|| $m ||"]")
          else file:write("/tmp/fu1","NOT-MATCHES $orig-id=["||$orig-id||"] $xmlid="|| $xmlid ||" m["|| $m ||"]")
:)

   return if ( $m and matches($xmlid,"^[a-zA-Z][0-9]*[a-zA-Z]+\d+[a-zA-Z]*\d*$") ) 
          then fu:normalise_xmlid($xmlid) (: ensure its valid too :)
          else if (matches($xmlid,"^branch."))
               then  let $rectype := replace($xmlid,"\d+$","") (: e.g. branch.kgd :)
                     let $distict-num := substring($orig-id,4,3)
                     let $year        := substring($orig-id,7,4)
                     return string-join(($rectype,$distict-num,$year),":")
               else concat("BAD orig-id=[",$orig-id,"] xmlid=[",$xmlid,"] maptext=[",$m,"]") 
};

declare function fu:anyid2rectype ( $id as xs:string* ) as xs:string {
   let $m := if ( matches($id,"^\d+$") ) then fu:p2map_entry(xs:integer($id)) else fu:xmlid2map_entry($id)
   return if ($m) then $m/id/text() else ""
};

declare function fu:anyid2map_entry( $id as xs:string* ) as element()* {
   let $xmlid := fu:anyid2xmlid($id)
   return fu:xmlid2map_entry($xmlid)
};

declare function fu:xmlid2rectype ( $xmlid as xs:string ) as xs:string {
(: replace($xmlid,'^([a-zA-Z][a-zA-Z0-9]*[a-zA-Z]+)\d+([a-zA-Z]*\d*)?$','$1') :)
   if (matches($xmlid,"^branch.")) then replace($xmlid,"\d+$","") (: e.g. branch.kgd9999 :)
   else replace($xmlid,'^([a-zA-Z][0-9]*[a-zA-Z]+).+','$1')
};

declare function fu:xmlid2map_entry( $xmlid as xs:string* ) as element()* {
   let $xml_rectype := fu:xmlid2rectype( $xmlid )
   return $fu:fas//rec[id/text()=$xml_rectype]
};


declare function fu:xmlid2map-role-subentry( $xmlid as xs:string* ) as element()* {
   let $xml_rectype := fu:xmlid2rectype( $xmlid )
   let $map := fu:anyid2map_entry($xmlid)
   let $rolename := fu:x2rolename($xmlid)
   return if ($rolename) then $map//role[@name=$rolename] else ()
};


(:
                                            191450110
                                            521051170
declare variable $fu:ManagedVHi_above    :=1000000000;
declare variable $fu:ManagedHi_above     := 900000000;
declare variable $fu:ManagedHi_divisor   :=  10000000; <--
declare variable $fu:ManagedHi_divisor1  := 100000000;
                                           1000000000
declare variable $fu:ManagedLo_above     :=  10000000;
declare variable $fu:ManagedLo_divisor   :=   1000000;
declare variable $fu:numeric_length_lo   := 6;
declare variable $fu:numeric_length_hi   := 7;
declare variable $fu:numeric_length_vh   := 8;

:)

declare function fu:is_special ( $numstr as xs:string* ) as xs:integer {
   if (string-length($numstr)=9 and starts-with($numstr,"19") ) then 1 else 0
};

declare function fu:p2map_entry( $dbid as xs:integer ) as element()* {
   let $s-dbid := string($dbid)
   let $is-branch := if (string-length($s-dbid)=10 and matches($s-dbid,"^10[0-4]") ) then 1 else 0
   let $special := fu:is_special(xs:string($dbid))
let $t1 := if ($dbid > $fu:ManagedHi_above) then "XXX" else ()
   let $dbg-divisor := if ($is-branch) then "$fu:ManagedHi_divisor"
                   else if ($dbid > $fu:ManagedVHi_above) then "1000000000"
                        else if ( $dbid > $fu:ManagedHi_above or fu:is_special(xs:string($dbid))) 
                             then "$fu:ManagedHi_divisor" 
                             else "$fu:ManagedLo_divisor"
   let $divisor := if ($is-branch) then $fu:ManagedHi_divisor
                   else if ($dbid > $fu:ManagedVHi_above) then 1000000000
                        else if ( $dbid > $fu:ManagedHi_above or fu:is_special(xs:string($dbid))) 
                             then $fu:ManagedHi_divisor 
                             else $fu:ManagedLo_divisor
   let $mapkey := xs:string(xs:integer($dbid div $divisor))
   let $map := if ($fu:fas//rec[@key=$mapkey]) then $fu:fas//rec[@key=$mapkey] else $fu:fas//rec[role/@key=$mapkey]

(:
let $x := file:write("/tmp/fu","$dbid=["||$dbid||"] $t1["||$t1||"] $special["||$special||"] $is-branch="||$is-branch ||" $dbg-divisor="||$dbg-divisor||" $divisor="||$divisor||"] mapkey["||$mapkey||"] map["|| $map ||"]")
:)

   let $map := if ($fu:fas//rec[@key=$mapkey]) then $fu:fas//rec[@key=$mapkey] else $fu:fas//rec[role/@key=$mapkey]
   return      if (not($map) and $dbid > $fu:ManagedHi_above )
               then let $divisor := $fu:ManagedHi_divisor1
                    let $mapkey := xs:string(xs:integer($dbid div $divisor))
                    return if ($fu:fas//rec[@key=$mapkey]) then $fu:fas//rec[@key=$mapkey] else $fu:fas//rec[role/@key=$mapkey]
               else $map
};

(: postgres dbid to xmlid via map. largest integer=2147483647 :)
declare function fu:p2x ( $dbid as xs:integer ) as xs:string {
   let $m := fu:p2map_entry($dbid)
   return if ($m/@silentkey) then concat($m/id, xs:string($dbid))
          else
   (: if it doesnt start with the matching key, the key is from a role :)
   let $base_rectype_key := data($m/@key)
   let $rectype := $m/id/text()
   let $key_value := substring( xs:string($dbid), 1, string-length($base_rectype_key) )
   let $role_value := if ($key_value = $base_rectype_key) then "" else $m//role[@key=$key_value]/text()
   (: numeric keys where role/@seq need to have last 2 digits removed, its a sequence no for the role 
      e.g. 1408679402 => ccc86794Ch2
           2147483647 OK for int4!!!
   :)
   let $remove-suffix := if ($role_value and $m//role[@key=$key_value]/@seq) 
                         then tokenize(replace(xs:string($dbid),"(\d+)(\d\d)$","$1:$2"),":")
                         else ()
   let $suffix-num := if ($role_value and $m//role[@key=$key_value]/@seq) then $remove-suffix[2] else ()
   let $dbid := if ($suffix-num) then xs:integer($remove-suffix[1]) else $dbid

   let $keepfrom := string-length($base_rectype_key) + 1
   let $kept := if ($m/@lz)
                then let $k := substring( xs:string($dbid), $keepfrom)
                     let $n_keep_directive := xs:integer(data($m/@lz)) 
                     let $len_kept := string-length($k)
                     let $n_to_chop := if ($n_keep_directive < $len_kept) then $len_kept - $n_keep_directive + 1 else 0
(:
let $x := if ($rectype="ai") then file:write("/tmp/ap20xx_00",concat("keepfrom[",$keepfrom,"] k[",$k,"] n_keep_directive[",$n_keep_directive,"] len_kept[",$len_kept,"] n_to_chop[",$n_to_chop,"]")) else ()
:)
                     return if ($n_to_chop) then substring($k,$n_to_chop) else $k
                else substring( xs:string($dbid), $keepfrom)
   return if ($m)
          then (: keep the significant digits and prepend the rectype and role :)
               if ($m/@lz) 
               then concat($m/id, $kept, $role_value, $suffix-num )
               else concat($m/id, replace  ( $kept, '^0*(.+)$', '$1' ), $role_value, $suffix-num)
          else concat("p2x no map dbid:",xs:string($dbid))
};

declare function fu:p2x_OLD  ( $dbid as xs:integer ) as xs:string {

   if      ($dbid > 900000000 ) then string($dbid)
   else if ($dbid > 10000000  ) then

       let $type := xs:integer($dbid div 1000000)
       (: basex: let $m := map:get($fu:fas, $type ) :)
       let $m := $fu:fas//rec[@key=$type]
       let $recnum := $dbid - ($type * 1000000)
       return if ($m/@lz)
              then concat($m/id,fu:pad-integer-to-length($recnum,$m/@lz))
              else concat($m/id,$recnum)
   else ""
};


(: postgres xml key to postgres dbid 
   Get the alpha part, look up that <id> in $fu:fas and return the
   key attribute (this is the prefix of the database id)
:)
declare function fu:xq2pid_test ( $xmlid as xs:string ) as xs:string {
   let $s := replace($xmlid,'^([a-zA-Z][a-zA-Z0-9]*[a-zA-Z]+)(\d+)([a-zA-Z]*)$','$1:$2:$3')
   let $s := tokenize($s,":")
   let $rectype := $s[1]
   let $number := $s[2]
   let $role := $s[3]
   let $map_entry := $fu:fas//rec[id/text()=$rectype]
   let $len := if ($map_entry/@hi) then $fu:numeric_length_hi else $fu:numeric_length_lo
   let $num := fu:pad-integer-to-length( $number, $len ) 
   let $dbid_suffix := if ($role) 
                       then if ($map_entry/role[text()=$role] or $map_entry/role[@name=$role] or $map_entry/role[matches(@name,$role,"i")])
                            then data($map_entry/role[text()=$role]/@key) 
                            else data($map_entry/@key)
                       else data($map_entry/@key)
return concat($rectype,":",$number,":",$role," num:",$num," dbid_suffix:",$dbid_suffix)
};

(: e.g. ccc86794Ch2 => 9208679402 *** too long!!
                       2147483647
:)
declare function fu:xq2pid ( $xmlid as xs:string ) as xs:string {
   let $s := replace($xmlid,'^([a-zA-Z][0-9]*[a-zA-Z]+)(\d+)(([a-zA-Z]*)(\d*))$','$1:$2:$3:$4:$5')
   let $s := tokenize($s,":")
   let $rectype := $s[1]
   let $number := $s[2]
   let $role-and-sequence := $s[3]
   let $role := $s[4]
   let $role-seq := $s[5]
   let $map_entry := $fu:fas//rec[id/text()=$rectype]
   let $role-map_entry := if ($role) then $map_entry/role[text()=$role] else ()
   let $suffix := if ($role-map_entry/@seq)
                  then if ($role-seq)
                       then fu:pad-integer-to-length($role-seq,2)
                       else ()
                  else ()
   let $len := if ($map_entry/@hi) then $fu:numeric_length_hi else $fu:numeric_length_lo
   let $lz := if ($map_entry/@lz) then xs:integer(data($map_entry/@lz)) else 0
   let $len := max(($len,$lz))
   return if ( $map_entry/@silentkey ) then $number
          else
(:
               let $len := if ($map_entry/@lz) then xs:integer(data($map_entry/@lz)) 
                           else if ($map_entry/@hi) then $fu:numeric_length_hi else $fu:numeric_length_lo
:)
               let $num := fu:pad-integer-to-length( $number, $len ) 
               let $dbid_suffix := if ($role) 
                                   then if ($map_entry/role[text()=$role] or $map_entry/role[@name=$role])
                                        then data($map_entry/role[text()=$role]/@key) 
                                        else data($map_entry/@key)
                                   else data($map_entry/@key)
               return concat($dbid_suffix,$num,$suffix)
};

(: The rolename used in the participant role for an xmlid :)
declare function fu:x2rolename ( $xid as xs:string* ) as xs:string* {
   let $s := replace($xid,'^([a-zA-Z][0-9]*[a-zA-Z]+)\d+(.+)$',"$1:$2")
   let $s := tokenize($s,":")
   let $rectype := $s[1]
   let $role-and-seq := $s[2]
   let $rolecode := replace($role-and-seq,"\d+$","")
   let $map_entry := $fu:fas//rec[id/text()=$rectype]
   let $role := $map_entry//role[text()=$rolecode]
   return data($role/@name)
};


(: get descriptive info for an xmlid :)
declare function fu:x2info ( $xid as xs:string* ) as xs:string* {
   let $s := replace($xid,'^([a-zA-Z][a-zA-Z0-9]*[a-zA-Z]+)\d+(.+)$',"$1:$2")
   let $s := tokenize($s,":")
   let $rectype := $s[1]
   let $rolecode := $s[2]
   let $map_entry := $fu:fas//rec[id/text()=$rectype]
   let $role := if ($rolecode) then $map_entry//role[text()=$rolecode] else ()
   return if ($role) then data($role/@info) else $map_entry/desc/text()
};

(: postgres xml key and a person role name to postgres dbid :)
declare function fu:xq2pid_rolename ( $xmlid as xs:string, $rolename as xs:string ) as xs:string {
   let $s := replace($xmlid,'^([a-zA-Z][a-zA-Z0-9]*[a-zA-Z]+)(\d+)$','$1:$2')
   let $s := tokenize($s,":")
   let $id := $s[1]
   let $map_entry := $fu:fas//rec[id/text()=$id]
   let $len := if ($map_entry/@hi) then $fu:numeric_length_hi else $fu:numeric_length_lo
   let $num := fu:pad-integer-to-length( $s[2], $len ) 
   (: numeric part the same, but get related entry for the role and use that for the suffix :)
   let $role_entry := $map_entry//role[@name=$rolename]
   let $dbid_suffix := data($role_entry/@key)
   return
       if ( $role_entry ) then concat($dbid_suffix,$num)
       else concat("-1 ERROR: "," id=",$id," num=",$num, " xmlid=",$xmlid," rolename=",$rolename," mapkey=",$map_entry/@key," role_entry=",$role_entry) (: "-1" :)
};


(: calculate dbid for a branch e.g. 101-103 :)
declare function fu:branch_dbid_calc ( $rectype as xs:string, $partnum as xs:integer* ) as xs:string {
   (: LOGIC by which branch ids are constructed lives here 
      This function constructs the id bassed on components in $partnum :)
   let $map_entry := $fu:fas//rec[id/text()=$rectype] 
   return 
   if ($rectype = ("branch.kgb","branch.kgm","branch.kgd") )
   then
      let $suffix := data($map_entry/@key)
      (: parts are YEAR(4) and PLACEID(3) :)
      let $year := fu:pad-integer-to-length($partnum[1],4)
      let $placenum := fu:pad-integer-to-length($partnum[2],3)
      return concat($suffix,$year,$placenum)
   else
      let $errstr := concat('fu:branch_dbid_calc_UNSUPPORTED_rectype_',$rectype)
      return error(xs:QName($errstr))
      (: error(xs:QName('fu:branch_dbid_calc_UNSUPPORTED_rectype')) :)
}; 


(:................................................................. low level functions from functx :)

declare function fu:repeat-string ( $stringToRepeat as xs:string? , $count as xs:integer )  as xs:string {
   string-join((for $i in 1 to $count return $stringToRepeat), '')
};

declare function fu:pad-integer-to-length ( $integerToPad as xs:anyAtomicType? , $length as xs:integer )  as xs:string {
   let $numstr := replace( string($integerToPad), "^0*(.+)$", "$1")
   return
   if ($length < string-length($numstr))
   then $numstr 
(: error(xs:QName('fu:Integer_Longer_Than_Length'),concat("In fu:pad-integer-to-length(",$integerToPad,",",$length,") numstr=[",$numstr,"]")) :)
   else concat (fu:repeat-string( '0',$length - string-length($numstr)), $numstr)
};

declare function fu:p2xq ( $dbid as xs:integer ) as element()* {
   fu:anyid2xml(xs:string($dbid))
};

(: ................................................................... not for saxon :)
(: Comment out for Saxon: postgres dbid to xml xquery, get actual xml :)
(: suffix P__ for previous, N__ for next sibling, to enable browsing :)
declare function fu:anyid2xml ( $xmlid as xs:string* ) as node()* {
   let $passed-xmlid := $xmlid
   let $parts := tokenize($xmlid,"__")
   let $xmlid := if (count($parts)=1) then $xmlid else $parts[2]
   let $position := if (count($parts)=1) then () else $parts[1]
   let $sibling := if      ($position="N") then "/following-sibling::*[1]" 
                   else if ($position="P") then "/preceding-sibling::*[1]" else ()
   let $xmlid := if (matches($xmlid,"^\d+$")) then fu:anyid2xmlid($xmlid) else $xmlid
   let $xmlid_norole := replace($xmlid,"^([a-zA-Z][0-9]*[a-zA-Z]+\d+).*$","$1")
   let $dbid_norole := fu:xq2pid($xmlid_norole)
   let $m := fu:anyid2map_entry($xmlid_norole)
   let $type := $m/id/text()
   let $xq := if ($type = ("kgb","kgm","kgd")) then replace($m/xq,"ID",$dbid_norole)
              else replace($m/xq,"ID",$xmlid_norole)
(:   let $xq := replace($m/xq,"ID",$xmlid_norole) :)
   let $role-map := fu:xmlid2map-role-subentry($xmlid)
   let $xq := if ($role-map/@seq)
              then let $sequence := replace($xmlid,"^[a-zA-Z][0-9]*[a-zA-Z]+\d+[a-zA-Z]+(\d*)$","$1")
                   return if ($sequence) then concat($xq,"/",$role-map/@seq,"[",$sequence,"]")
                          else (: get all of them :)
                               concat($xq,"//",$role-map/@seq)
              else $xq
   return if ($xq)
          (: ----------------------------------------------------------------------------------- basex only
          then if ($sibling)
               then let $xml := concat('data(collection("' , $m/db , '")',$xq,$sibling,'/@id)')
                    return text{xquery:eval($xml)}
               else let $xml := concat('collection("' , $m/db , '")' , $xq)
                    return <external xid="{$xmlid}"><xquery>{$xml}</xquery>
                               <resp>{xquery:eval($xml)}</resp>
                          </external>
          ----------------------------------------------------------------------------------------------- :)
          then let $xml := concat('http://localhost:8984/rest/',$m/db,'?query=',$xq)   (: for saxon usage :)
               return <external xid="{$xmlid}"><xquery>{$xml}</xquery>
                                <resp>{doc($xml)}</resp>
                      </external>     
          else <nyi xmlid="{$passed-xmlid}">{$xmlid}</nyi>
};

