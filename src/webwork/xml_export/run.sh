#!/bin/bash

HEREDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. /etc/environment

DATABASE=$1
if [[ "$1" == "" ]]; then DATABASE="demo"; fi
CHECK=`psql -l | grep -w $DATABASE | wc -l`
NOW=`date +"%Y-%m-%d_%T"`
echo "##============= $0 Exporting $DATABASE [$CHECK] as xml $NOW"
if [[ "$CHECK" == "0" ]]; then
   echo "##ERROR## Database [$DATABASE] does not exist."
   echo "usage: $0 dbname"
   echo "       available databases: (psql -l)"
   psql -l
   exit 1
fi
echo ""
echo "== Generating list of [$DATABASE] tablenames"

WORKDIR="$AP20_EXPORT/xml"
if ! [[ -d $WORKDIR ]]; then
    echo "##ERROR## WORKDIR[$WORKDIR] does not exist. Check your installation."
    exit 1
fi

psql $DATABASE -f list_tables.sql

SCRIPT_NAME=`basename $0`
SCRIPT_PATH="$HEREDIR/$SCRIPT_NAME"
DIR="$WORKDIR/$DATABASE"

COLLECTION_FILENAME="${DATABASE}_collection.xml"
COLLECTION_DOC="$DIR/$COLLECTION_FILENAME"
TABLENAMES_OUT="/tmp/tablenames.txt"
if [ -d $DIR ]; then
   echo "Exporting xml to existing dir: $DIR" 
else
   echo "Creating new export dir: $DIR" 
   mkdir -p $DIR
fi
if [ -d "$DIR/tables" ]; then
   echo "Exporting tables to existing dir: $DIR/tables" 
else
   echo "Creating new export dir: $DIR/tables" 
   mkdir -p $DIR/tables
fi

ATTRS="when=\"$NOW\" generated_by=\"$HOSTNAME:$SCRIPT_PATH/$SCRIPT_NAME\""
echo "<collection key=\"$DATABASE\" $ATTRS id=\"$COLLECTION_DOC\">" > $COLLECTION_DOC

TMP="/tmp/$$"
echo "\a" > $TMP
echo "\pset tuples_only" >> $TMP

for T in `cat /tmp/tablenames.txt`
do
   OUT="$DIR/tables/${T}.xml"
   OUTURI="tables/${T}.xml"
   #####echo "  <doc href=\"$OUT\"/>" >> $COLLECTION_DOC
   echo "  <doc href=\"$OUTURI\"/>" >> $COLLECTION_DOC

   # $TMP is the sql commands file
   echo "" >> $TMP
   echo "\o $OUT" >> $TMP
   ORDER_BY=""
   case $T in
      dead_children) 
         ORDER_BY="order by person_fk"
         ;; 
      event_citations) 
         ORDER_BY="order by event_fk, source_fk"
         ;; 
      linkage_roles) 
         ORDER_BY="order by role_id"
         ;; 
      participants) 
         ORDER_BY="order by person_fk, sort_order, event_fk"
         ;; 
      persons) 
         ORDER_BY="order by person_id"
         ;; 
      place_level_desc) 
         ORDER_BY="order by place_level_id"
         ;; 
      places) 
         ORDER_BY="order by place_id"
         ;; 
      relation_citations) 
         ORDER_BY="order by relation_fk,source_fk"
         ;; 
      relations) 
         ORDER_BY="order by relation_id"
         ;; 
      source_linkage) 
         ORDER_BY="order by source_fk, per_id"
         ;; 
      source_part_types) 
         ORDER_BY="order by part_type_id"
         ;; 
      sources) 
         ORDER_BY="order by source_id"
         ;; 
      sureties) 
         ORDER_BY="order by surety_id"
         ;; 
      tag_groups) 
         ORDER_BY="order by tag_group_id"
         ;; 
      tag_prepositions) 
         ORDER_BY="order by tag_fk,lang_code"
         ;; 
      tags) 
         ORDER_BY="order by tag_group_fk,tag_id"
         ;; 
      templates) 
         ORDER_BY="order by source_fk"
         ;; 
      user_settings) 
         ORDER_BY="order by username"
         ;; 
      *)
         ORDER_BY=""
         ;;
   esac
   echo "select query_to_xml('select * from $T $ORDER_BY',false,true,'');" >> $TMP
done
echo "</collection>" >> $COLLECTION_DOC

echo "== See saxon $COLLECTION_DOC ":
echo ""
cat $COLLECTION_DOC

echo ""
echo "== Generating xml for each table -f TMP[$TMP]":
echo ""
psql $DATABASE -f $TMP

echo ""
echo "== Remove crud ":
echo ""
for T in `cat /tmp/tablenames.txt`
do
   OUT="$DIR/tables/${T}.xml"
   echo "<table name=\"${T}\" id=\"${DATABASE}.$T\" $ATTRS>" > $TMP
   # remove all white space between tags
   perl -pe 's/<row[^\<]+/\<'$T'\>/; s/<\/row/\<\/'$T'/;' $OUT | perl -0 -pe 's/\>[\n\s]+(\<)/\>\n\1/g;' >> $TMP
   echo "</table>" >> $TMP
   mv $TMP $OUT
done
ls -la $DIR

OUT="$DIR/${DATABASE}_database.xml"
#SEEQ='java net.sf.saxon.Query -qversion:1.1 -config:/srv/fasrepo/common-bin/saxonsql-conf.xml'
echo ""
echo "== Running saxon on $COLLECTION_FILENAME to make $OUT"
echo ""
cd $DIR
$SEEQ -s:$COLLECTION_FILENAME -qs:'<database name="'$DATABASE'">{for $doc in collection("'$DATABASE'_collection.xml") return $doc}</database>' -o:$OUT
ls -la $OUT
# we don't want any mixed content here so remove all white space before tags
perl -0 -pe 's/[\n\s]+(\<)/\1/g;' $OUT > $TMP
echo ""
echo "-- normalise $TMP after perl hack"
$SEEQ -s:$TMP -o:$OUT -qs:/ \!indent=yes
echo ""

echo "#== eoj."
echo ""
echo "-- DIR[$DIR]:"
ls -la $DIR
echo ""
echo "-- Collection document COLLECTION_DOC[$COLLECTION_DOC]:"
ls -la $COLLECTION_DOC
echo ""
echo "-- database all in one from saxon document OUT[$OUT]:"
ls -la $OUT
rm /tmp/tablenames.txt
exit 0

