#!/bin/bash

# $AP20_HOME/bin/diff.sh : shows all code changes between the distro dir (here)
#                          and the deployed web application ($AP20_WEBPATH)

# run from the dir this script is located
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

OUT="/tmp/diff.log"
OUT2="/tmp/diff-summary.log"
DISTRO="../src/www/ap20/yggdrasil"
if [[ "$AP20_WEBPATH" == "" ]]; then
    echo "$0 requires AP20_WEBPATH environment to be set (location of your deployed code distro)"
    exit 0
fi
echo "##==== $0: Whole dir diff for Yggdrasil webapp"
echo "-- from deployed[$AP20_WEBPATH/]"
echo "-- to   distro[$DISTRO]"
diff -Naur -X rsync.phpapp.dep2repo.exclusions $AP20_WEBPATH ../src/www/ap20/yggdrasil > $OUT 2>&1
agrep '^--- ,^\+\+\+' $OUT > $OUT2
ls -la $OUT $OUT2
