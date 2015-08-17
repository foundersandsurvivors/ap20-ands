#!/bin/bash

# $AP20_HOME/bin/diff-xqlib.sh : shows all code changes between the distro dir (here)
#                                and the deployed xquery module library

. $AP20_DISTRO/bin/bash.functions

# run from the dir this script is located
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

DESC="Yggdrasil xquery modules"
ENVDESC="AP20_XQLIB"
DISTRO="../src/xml/xquery-lib"

# hardcoded
AP20_XQLIB="/usr/local/lib/xquery"
if [[ -d "$1" ]]; then
    AP20_XQLIB=$1
    echo "##==== **** using passed parameter for \$AP20_XQLIB[$AP20_XQLIB]"
fi
DEPLOY=$AP20_XQLIB

echo "##==== $0: differences between repo and deployed $desc"
echo "-- dir deployed[$DEPLOY] Using: \$AP20_XQLIB"
echo "-- dir   distro[$DISTRO/]"
echo "   Note: The path for \$AP20_XQLIB is hard coded into the modules"
echo "         because there is no standard way in xquery to do module deployment."
echo "         We have chosen to hard code the location: /usr/local/lib/xquery."

checkdir $DISTRO            $DEPLOY

exit 0
