#!/bin/bash

function usage {
   DB=$1
   MODELNAME=$2
   MODELS_DIR=$3
   echo "  DB[$DB] MODELNAME[$MODELNAME]"
   echo ""
   echo "usage: $0 dbname modelname"
   echo "       Note: models need to be generated. See /domain/db/source_loader.php"
   echo ""
   echo "-- Available models in $MODELS_DIR are:"
   cd $MODELS_DIR
   ls *.txt | perl -pe 's/out_sql_skeleton-//g; s/.xml.txt//g;'
   exit 1
}

. /etc/environment
DB="databasename"
MODELNAME="modelname"
MODELS_DIR="$AP20_WEBWORK/models/generated"

if ! [[ "$1" == "" ]];then 
   DB=$1 
fi
if ! [[ "$2" == "" ]];then 
   MODELNAME=$2 
fi
if ! [[ -d $AP20_WEBWORK ]];then 
   echo "##ERROR## $0 AP20_WEBWORK[$AP20_WEBWORK] directory does not exist!"
   echo "          This dir is required. Check your ap20 installation."
   exit 2
fi
if ! [[ -d $MODELS_DIR ]];then 
   echo "##ERROR## $0 MODELS_DIR[$MODELS_DIR] directory does not exist!"
   echo "          This dir is required. Its the location where source_loader.php"
   echo "          will generate models which contain sql load statements."
   exit 3
fi

SQL_STATEMENTS="$MODELS_DIR/out_sql_skeleton-${MODELNAME}.xml.txt"
if ! [[ -f $SQL_STATEMENTS ]]; then
   echo "##ERROR## $0 SQL_STATEMENTS[$SQL_STATEMENTS] file does not exist."
   echo "          Have you used source_loader.php to generate the models?"
   usage $DB $MODELNAME $MODELS_DIR
   exit 1
fi

LOG="sources_$DB_load_${MODELNAME}.log"
echo "##======== $0 Load sources in $DB using model $MODELNAME `date`"
echo "-- psql $DB < $SQL_STATEMENTS > $LOG 2>&1"
psql $DB < $SQL_STATEMENTS > $LOG 2>&1
ls -la $LOG
