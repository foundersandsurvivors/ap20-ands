(: 
   vjs-spreadsheet-ana-utils.xqm : vjs spreadsheet analysis utilities
   ==================================================================

   Defines columns specifications for Founders and Survivors "vjs" ships research project.

   Sandra Silcot 2013

   --------------------------------------------------------------------------------- Change history:
   * Aug 2013: initial version to suit vjs FAS project.
   
<col id="04___Year">b4a358.27</col>

<col id="19___Birth_family [5]">b4a362.28</col>

:)
module namespace  vjs-ss = "http://fas/vjs-ss" ;

declare variable $vjs-ss:data-items := <fieldSpecs desc="Columns generally present in spreadsheets. Order may vary. data-item-heading gives lookup in vjs-codes-legend.xml">
<list>
  <c find-by="create-VJSSEQ"><label>VJSSEQ</label><desc>Unique number for the generated row. First 3 digits is the ship. Last 3 are the convict within the ship (these start at 2 as 1 is always the spreadsheets column titles row). You can sort by this value to return to the generated sort sequence.</desc></c>
  <c find-by="create-SHIPID"><label>SHIPID</label><desc>Key of the convict ship (column added when aggregating).</desc></c>
  <c find-by="create-SHIPNAME"><label>SHIPNAME</label><desc>Name of the convict ship (column added when aggregating).</desc></c>
  <c find-by="create-SHIPYEAR"><label>SHIPYEAR</label><desc>Year of arrival in Tasmania of the convict ship (column added when aggregating).</desc></c>
  <c find-by="pos" n="1"><label>SURNAME</label><desc>Convict surname.</desc></c>
  <c find-by="pos" n="2"><label>FORENAME</label><desc>Convict forename.</desc></c>
  <c find-by="pos-FASID" n="3"><label>FASID</label><desc>FAS record identifier (retained id from Url for audit/traceback)</desc></c>
  <c find-by="pos-CCCID" n="4" ><label>CCCID</label><desc>CCC record identifier (retained id from Url for audit/traceback)</desc></c>
  <c find-by="pos" n="5" coding="Traced?"><label>TRACED</label><desc>Code indicating how far the researcher was able to trace the convict.</desc></c>
  <c find-by="pos" n="6"><label>BIRTH_YEAR</label><desc>Convict year of birth.</desc></c>
  <c find-by="pos" n="8"><label>BIRTH_PLACE</label><desc>Name of the place of birth.</desc></c>
  <c find-by="pos" n="7" derived_from="BIRTH_PLACE" coding="CHAPMAN-COUNTY"><label>BIRTH_PLACE-C</label><desc>Codified place of birth.</desc></c>
  <c find-by="pos" n="9" derived-from="BIRTH_PLACE-C" coding="ORIGIN-PLACETYPE-CODE"><label>BIRTH_PLACE-CAT</label><desc>Categorisation of the codified place of birth.</desc></c>
  <c find-by="pos" n="11"><label>CONVICTED_PLACE</label><desc>Name of place of conviction.</desc></c>
  <c find-by="pos" n="10" derived_from="CONVICTED_PLACE" coding="CHAPMAN-COUNTY"><label>CONVICTED_PLACE-C</label><desc>Codified place of conviction.</desc></c>
  <c find-by="pos" n="12" coding="ORIGIN-PLACETYPE-CODE" coding-alt-CHECK="ORIGIN-PLACETYPE-CODE or Eco_location" derived-from="CONVICTED_PLACE-C"><label>Eco_location</label><desc>TAKE CARE -- not sure if contents reflect coding described in "Eco_location" or "ORIGIN-PLACETYPE-CODE" -- confirm with Janet.</desc></c>
  <c find-by="pos" n="13" ><label>SENTENCE</label><desc>Convict's sentence.</desc></c>
  <c type="F" find-by="pos" n="14" coding="Crime"><label>CRIME-C</label><desc>Codification of the crime.</desc></c>
  <c type="M" find-by="pos" n="14" coding="Crime"><label>CRIME-C</label><desc>Codification of the crime.</desc></c>
  <c find-by="pos" n="15"><label>AGE</label><desc>Age at conviction/transportation/arrival in colony? (? check with Janet)</desc></c>
  <c find-by="pos" n="16"><label>HEIGHT</label><desc>Convict height in inches</desc></c>
  <c find-by="pos" n="17" coding="Literacy"><label>LITERACY-C</label><desc></desc></c>
  <c find-by="head-start" n="Occupation" coding="Occupation"><label>OCCUPATION-C</label><desc>Codification of convict occupation(s) (from indents?)</desc></c>
  <c find-by="head-start" n="Religion" coding="Religion"><label>RELIGION</label><desc>Codification of convict's religion.</desc></c>
  <c find-by="head-start" n="Birth_family;Birth Family" coding="Birth_family"><label>BIRTH_FAMILY-C</label><desc>Codification of a convict's birth family capital.</desc></c>
  <c find-by="head-start" n="Number_children;Number of children;No_of_children" ><label>N_CHILDREN_BFAM</label><desc>####CHECK#### Assuming its the first "Number_children" column where content is their birth family, hence the "BFAM" (Birth Family) context (please confirm with Janet)</desc></c>
  <c find-by="head-start" n="Marital_family" coding="Marital_family"><label>MARITAL_FAMILY-C</label><desc>Codification of a convict's marital family.</desc></c>
  <c except-ship="b4a366.26" except-ship-pos="23"
     find-by="head-start" n="Number_children;Number of children;No_of_children" select-from-multiples="2"><label>N_CHILDREN_MFAM</label><desc>####CHECK#### Assuming its the second "Number_children" column where content is their marital family, hence the "MFAM" (Marital Family) context (please confirm with Janet)</desc></c>
  <c find-by="head-start" n="Number_on_board;No on board" ><label>N_CHILDREN_ONBOARD</label><desc>No of children on board transportation ship.</desc></c>
  <c find-by="head-start" n="On_the_town" coding="On_the_town" type="F"><label>ON_THE_TOWN</label><desc>Codification of female convict's "on the town" status.</desc></c>
  <c find-by="head-start" n="Conduct" coding="Conduct"><label>CONDUCT</label><desc>Codification of researcher's count of convict's conduct offenses.</desc></c>
  <c find-by="head-start" n="Alcohol" ><label>N_ALCOHOL</label><desc>Count of alcohol related offenses.</desc></c>Y
  <c find-by="head-start" n="Reactive_behaviour;Reactive Behaviour" coding="Reactive_behaviour"><label>REACTIVE_BEHAVIOUR</label><desc>Codification of researcher's interpretation of convict behaviour.</desc></c>
  <c find-by="head-start" n="Times_absconded" ><label>N_ABSCONDED</label><desc>No of times a convict absconded.</desc></c>
  <c find-by="head-start" n="Convict_births" ><label>N_CONVICT_BIRTHS</label><desc>No of births a female convict had as a convict.</desc></c>
  <c find-by="head-start" n="Sexual" coding="Sexual"><label>SEXUAL_OFFENSES-C</label><desc>Codification of sexual offenses.</desc></c>
  <c find-by="head-start" n="Cells_total_days;Cells_days_total;Days in cells" ><label>N_CELLS_TOTAL_DAYS</label><desc>Total no of days spent in cells.</desc></c>
  <c find-by="head-start" n="Solitary_total_days" ><label>N_SOLITARY_DAYS</label><desc>Total no of days spent in solitary (should be less than or equal to N_CELLS_TOTAL_DAYS?)</desc></c>
  <c find-by="head-start" n="Insults" coding="Insults"><label>INSULTS-C</label><desc>Codification of researcher's interpretation of insults inflicted upon a convict whilst under sentence.</desc></c>
  <c find-by="head-start" n="Exit" coding="Exit"><label>EXIT-C</label><desc>Codification of how the convict exited the convict system.</desc></c>
  <c find-by="head-start" n="Year" select-from-multiples="1"><label>EXIT-YEAR</label><desc>Year of exit from the convict system.</desc></c>
  <c find-by="head-start" n="Left_Tas;Departed Tas;Departure" coding="Left_Tas"><label>LEFT_TAS-C</label><desc>Codification of whether a convict left Tasmania.</desc></c>
  <c find-by="head-start" n="Year" select-from-multiples="2"><label>LEFT_TAS_YEAR</label><desc>Year a convict left Tasmania.</desc></c>
  <c find-by="head-start" n="Later_crimes;Later crimes" coding="Later_crimes"><label>LATER_CRIMES-C</label><desc>Codification of crimes committed after exit from the convict system.</desc></c>
  <c except-ship="b4a368.43" except-ship-pos="40"
     find-by="head-start" n="Marriage_after_transportation;Marriage_after_sentence;Marriage" coding="Marriage_after_transportation"><label>MARRIAGE_AFTER_TRANS-C</label><desc>Codification of whether a convict married after transporation/sentence (?)</desc></c>
  <c except-ship="b4a368.43" except-ship-pos="41"
     find-by="head-start" n="Year_of_Marriage"><label>MARRIAGE_YEAR</label><desc>Year of marriage.</desc></c>
  <c find-by="head-start" n="Spouse_names;Spouse"><label>SPOUSE_NAMES</label><desc>Name(s) of spouses.</desc></c>
  <c find-by="head-start" n="Children_after_sentence"><label>N_CHILDREN_AFTER_SENT</label><desc>Number of children born after sentence. If no evidence of a family put nil.</desc></c>
  <c find-by="head-start" n="Total_fertility"><label>TOTAL_FERTILITY</label><desc>Total fertility is total of children born before transportation, under sentence and afterwards.</desc></c>
  <c find-by="head-start" n="Child mortality"><label>CHILD_MORTALITY</label><desc>Total number of children died under 14 or nil if no evidence from all stages of the woman's reproductive life.</desc></c>
  <c find-by="head-start" n="3rd_gen" coding="3rd_gen"><label>GEN3-C</label><desc>Codification of whether a convict produced grandchildren.</desc></c>
  <c find-by="head-start" n="AIF" coding="AIF"><label>AIF-C</label><desc>Codification of whether a convict produced AIF descendants.</desc></c>
  <c find-by="head-start" n="Death_year"><label>DEATH_YEAR</label><desc>Year of a convict's death.</desc></c>
  <c find-by="head-start" n="Age" select-from-multiples="2"><label>DEATH_AGE</label><desc>Convict age at death.</desc></c>
  <c find-by="head-start" n="Place_of_death"><label>DEATH_PLACE</label><desc>Name of convict's place of death.</desc></c>
  <c except-ship="b4a360.06" except-ship-pos="51"
     find-by="head-start" n="Geog_location;Geog_code" coding="DEATH-GEOGLOCN-CODE"><label>DEATH_LOCN-C</label><desc>Codification of geographical location of the convict's place of death.</desc></c>
  <c find-by="head-start" n="Type_code;Type" coding="DEATH-TYPE-CODE"><label>DEATH_TYPE-C</label><desc>Codification of the nature of the convict's death place as an indicator of socio-economic status.</desc></c>
  <c find-by="head-start" n="Causes_of_death;cause of death"><label>DEATH_CAUSES</label><desc>Causes of death</desc></c>
  <c find-by="head-start" n="ICD_codes;ICD codes"><label>ICD_Code</label><desc>ICD Coding of causes of death.</desc></c>
  <c find-by="head-start" select-from-multiples="2" n="Occupation" CHECK="1; Occ at death?"><label>OCCUPATION_ON_DEATHCERT</label><desc>Occupation as indicated on death certificate (? check/confirm with Janet).</desc></c>
  <c find-by="head-start" n="Notes"><label>NOTES</label><desc>Researchers notes.</desc></c>
</list>
</fieldSpecs>;

declare variable $vjs-ss:numericCodedFields :=
<fieldSpecs>
    <item><name>TRACED</name><match>traced to death</match><fallback>Traced?</fallback></item>
    <item><name>LITERACY</name><match>Literacy:</match><fallback>Literacy</fallback></item>
    <item><name>CRIME</name><match>larceny from the person</match><fallback>Crime</fallback></item>
    <item><name>ECONOMIC_LOCATION</name><match>place living prior to conviction</match><fallback>Economic Location</fallback></item>
    <item><name>OCCUPATION</name><match>Occupation</match><fallback>Occupation</fallback></item>
    <item><name>RELIGION</name><match>Religion:</match><fallback>Religion</fallback></item>
    <item><name>FAMILY_BIRTH</name><match>Birth Family</match><fallback>Birth Family</fallback></item>
    <item><name>FAMILY_MARITAL</name><match>Marital Family:</match><fallback>Marital Family</fallback></item>
    <item><name>CONDUCT_FREQ</name><match>Conduct frequency:</match><fallback>Conduct: offences</fallback></item>
    <item><name>REACTIVE_BEHAVIOUR</name><match>Reactive behaviour</match><fallback>Reactive_behaviour</fallback></item>
    <item><name>AIF</name><match>AIF</match><fallback>AIF</fallback></item>
</fieldSpecs>;

declare variable $vjs-ss:fieldsWithValues :=
<fieldSpecs>
    <item><name>BORN_PLACECODE</name>
          <desc>Standardised country and country codes for place of birth.</desc>
          <fallback occurance="1">Country_and county_codes</fallback>
          <fallback occurance="1">Code</fallback>
    </item>
    <item><name>CONVICTED_PLACECODE</name>
          <desc>Standardised country and country codes for place of conviction.</desc>
          <fallback occurance="2">Country_and county_codes</fallback>
          <fallback occurance="2">Code</fallback>
    </item>
    <item><name>ALCHOHOL</name>
          <desc>Number of alchohol offences from 0 upwards in conduct record.</desc>
          <fallback>Alcohol</fallback>
    </item>
</fieldSpecs>;

declare function vjs-ss:headerFields ( $header as element() ) as element() {
   let $shipKey := $header//ship/text()
   let $shipParts := tokenize($header//spreadsheetTitle/text(),"_")
   let $shipName := <shipname>{$shipParts[1]}</shipname>
   let $arrYear := <arrYear>{$shipParts[2]}</arrYear>
   let $pop := <pop>{ replace($shipParts[3],"[MF]","") }</pop>
   let $baseForPop := <baseForPop>{$shipParts[4]}</baseForPop>
   let $derivedEls := ($shipName,$arrYear,$pop,$baseForPop)
   return <ship-info key="{$shipKey}">
          {$derivedEls}
          {$header//(ship|sex|spreadsheetTitle)}
          </ship-info>
};

(: passed a data-items colspec element, a row, and a sheet name, return the column position (@n) of the data-item this row/sheet :)
declare function vjs-ss:locate-data-item-in-this-sheet ( $c as element(), $row as element(), $SHIPID as xs:string ) as xs:string {
   ""
};

(: (0)A-Z 1-26; (1)AA-AZ 27-52; (2)BA-BZ 53-78; (3)CA-CZ 79-104 :)
declare variable $vjs-ss:LETTERS := "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
declare function vjs-ss:colnum-to-alpha ( $colpos as xs:string* ) as xs:string* {
   let $colnum := number($colpos)
   return
   if ($colnum >= 79)
   then let $prefix := "C"
        let $rem := $colnum - 78
        return concat($prefix, substring($vjs-ss:LETTERS,$rem,1))
   else if ($colnum >= 53)
        then let $prefix := "B"
             let $rem := $colnum - 52
             return concat($prefix, substring($vjs-ss:LETTERS,$rem,1))
        else if ($colnum >= 27)
             then let $prefix := "A"
                  let $rem := $colnum - 26
                  return concat($prefix, substring($vjs-ss:LETTERS,$rem,1))
             else if ($colnum >= 1)
                  then substring($vjs-ss:LETTERS,$colnum,1)
                  else ""
};

declare function vjs-ss:colpos-to-attrs ( $colpos as xs:string* ) as node()* {
   ( attribute n {$colpos}, attribute an { vjs-ss:colnum-to-alpha($colpos) } )
};

