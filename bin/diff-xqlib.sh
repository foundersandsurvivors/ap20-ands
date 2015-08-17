#!/bin/bash

OUT="/tmp/diff-xqlib.log"
OUT2="/tmp/diff-xqlib-summary.log"
DISTRO="../src/xml/xquery-lib"
AP20_XQLIB="/usr/local/lib/xquery"
if [[ "$AP20_XQLIB" == "" ]]; then
    echo "$0 requires $AP20_XQLIB to be the location for deployed ap20 xquery modules"
    exit 0
fi
if [[ -d "$1" ]]; then
    AP20_XQLIB=$1
    echo "##==== **** using passed parameter for \$AP20_XQLIB[$AP20_XQLIB]"
fi
echo "##==== $0: differences between repo and deployed Yggdrasil xquery modules"
echo "-- dir deployed[$AP20_XQLIB/] Environment variable: \$AP20_XQLIB"
echo "-- dir   distro[$DISTRO/]"
echo "   Note: The path for \$AP20_XQLIB is hard coded into the modules"
echo "         because there is no standard way in xquery to do module deployment."
echo "         We have chosen to hard code the location: /usr/local/lib/xquery."
#
# Loop through each file in the distro and compare it to the deployed version
#
for F in $DISTRO/*
do
   fname=`basename $F`
   deployed=$AP20_XQLIB/$fname
   distro="$DISTRO/$fname"
   echo "............................................. $fname:"
   ls -la $distro $deployed
   diff -y --suppress-common-lines $distro $deployed
   rc=$?
   #echo "rc[$rc]"
   if [ $rc -eq 0 ]; then
       if [ $deployed -nt $distro ]; then 
           echo "rc[$rc] No difference but deployed is NEWER than distro."
       else
           if [ $deployed -ot $distro ]; then
               echo "rc[$rc] No difference but deployed is OLDER than distro."
           else
               echo "rc[$rc] No difference."
           fi
       fi
   fi
   echo ""
done
#
# Check for deployed files not in distro
#
for F in $AP20_XQLIB/*
do
   fname=`basename $F`
   deployed=$AP20_XQLIB/$fname
   distro="$DISTRO/$fname"
   # skip directories
   if [ -f $deployed ]; then
       if [ -h $deployed ]; then
           # skip symlinks
           z=1
       else
           if [ ! -f $distro ]; then
               echo "Warning: deployed but NOT in distro: [$fname] $deployed"
           fi
       fi
   fi
done

exit 0
