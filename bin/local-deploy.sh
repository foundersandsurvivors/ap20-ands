#!/bin/bash

# for deploying repo contents when pulled from code repo

DIR="$( dirname "$_" )"
cd $DIR
if ! [ -f .env ]; then
   echo "ERROR: .env does not exist!"
   echo "Create $DIR/.env by copying/symlinking to $DIR/.env.sample and modify as required."
   exit 1
fi
. .env
if [ -z $HOSTNAME ]; then
   echo "ERROR: environemnt variable HOSTNAME is not defined!"
   exit 2
fi

# do not run if the hostname is defined in the array YGGDEP_EXCLUDED_HOSTS (see .env)
(for e in ${YGGDEP_EXCLUDED_HOSTS[@]}; do [[ "$e" == $HOSTNAME ]] && exit 0; done) && exit 2 || echo Deploying Yggdrasil on $HOSTNAME

DP=$YGGDEP_DEFAULT_PERMS
CGIBIN=$YGGDEP_CGIBIN_DIR

function copy {
  FROM=$1
  TO=$2
  OWNER=$3
  PERMS=$4
  DO=$5
  
  # if target does not exist, do it unless excluded host
  # if they are different 
  echo -- cp $FROM $TO [$DO]
  if [ -f $TO ]; then
     # it exists - reapply permissions
     sudo chown $OWNER $TO
     sudo chmod $PERMS $TO
     DIFF=`sudo diff -C0 $FROM $TO`
     if [ "$DIFF" == "" ]; then
         echo "   Exists and is identical - good."
     else
         echo "   *****************"
         echo "   * MANUAL ACTION * Please resolve DIFFERENCES and commit changes to the local repo. Differences:"
         echo "   *****************"
         sudo diff -C0 $FROM $TO
         ls -la $FROM $TO
     fi
  else
       # it doesn't exist
       if [ "$DO" == "do" ]; then
          echo "   Creating with $OWNER $PERMS"
          sudo cp $FROM $TO
          sudo chown $OWNER $TO
          sudo chmod $PERMS $TO
          ls -la $TO
       else
       echo "   To be created with $OWNER $PERMS"
       fi
  fi
}

# deploy cgi-bin
copy ../src/cgi-bin/first $CGIBIN/first $DP 775 "$1"

