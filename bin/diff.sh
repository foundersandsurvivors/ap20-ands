#!/bin/bash

# $AP20_HOME/bin/diff.sh : shows all code changes between the distro dir (here)
#                          and the deployed web application ($AP20_WEBPATH)

. $AP20_DISTRO/bin/bash.functions

# run from the dir this script is located
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

DESC="Yggdrasil php application"
ENVDESC="AP20_WEBPATH"
DISTRO="../src/www/ap20/yggdrasil"

if [[ "$AP20_WEBPATH" == "" ]]; then
    echo "$0 requires AP20_WEBPATH environment to be set (location of your deployed code distro)"
    exit 0
fi
if [[ -d "$1" ]]; then
    AP20_WEBPATH=$1
    echo "##==== **** using passed parameter for \$AP20_WEBPATH[$AP20_WEBPATH]"
fi
DEPLOY=$AP20_WEBPATH

echo "##==== $0: differences between repo and deployed $DESC" 
echo "-- dir deployed[$DEPLOY/] Environment variable: \$$ENVDESC"
echo "-- dir   distro[$DISTRO/]"

checkdir $DISTRO            $DEPLOY
echo "============================================================================ forms dir:"
checkdir $DISTRO/forms      $DEPLOY/forms
echo "============================================================================ graphics dir:"
checkdir $DISTRO/graphics   $DEPLOY/graphics
echo "============================================================================ help_local dir:"
checkdir $DISTRO/help_local $DEPLOY/help_local
echo "============================================================================ rdf dir:"
checkdir $DISTRO/rdf        $DEPLOY/rdf
echo "============================================================================ settings dir:"
checkdir $DISTRO/settings   $DEPLOY/settings

exit 0


#echo "##==== $0: Whole dir diff for Yggdrasil webapp"
#echo "-- from deployed[$AP20_WEBPATH/]"
#echo "-- to   distro[$DISTRO]"
#diff -Naur -X rsync.phpapp.dep2repo.exclusions $AP20_WEBPATH ../src/www/ap20/yggdrasil > $OUT 2>&1
#agrep '^--- ,^\+\+\+' $OUT > $OUT2
#ls -la $OUT $OUT2



