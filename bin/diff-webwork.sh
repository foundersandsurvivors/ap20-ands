#!/bin/bash

# $AP20_HOME/bin/diff-dbinit.sh : shows all code changes between the distro dir (here)
#                                 and the deployed utility code for web utilities

. $AP20_DISTRO/bin/bash.functions

# run from the dir this script is located
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

DESC="Yggdrasil webwork utilities"
ENVDESC="AP20_WEBWORK"
DISTRO="../src/webwork"

if [[ "$AP20_WEBWORK" == "" ]]; then
    echo "$0 requires AP20_WEBWORK environment to be set (location of your deployed web utilities)"
    exit 0
fi

if [[ -d "$1" ]]; then
    AP20_WEBWORK=$1
    echo "##==== **** using passed parameter for \$AP20_WEBWORK[$AP20_WEBWORK]"
fi
DEPLOY=$AP20_WEBWORK

echo "##==== $0: differences between repo and deployed $desc"
echo "-- dir deployed[$DEPLOY] Using: \$$ENVDESC"
echo "-- dir   distro[$DISTRO/]"

echo "============================================================================ \$$ENVDESC/bin dir:" 
checkdir $DISTRO/bin                $DEPLOY/bin
echo "============================================================================ \$$ENVDESC/db_init/demo dir:" 
checkdir $DISTRO/db_init/demo       $DEPLOY/db_init/demo
echo "============================================================================ \$$ENVDESC/xml_export dir:" 
checkdir $DISTRO/xml_export         $DEPLOY/xml_export

exit 0
