#!/bin/bash

OUT="/tmp/diff.log"
OUT2="/tmp/diff-summary.log"
DISTRO="../src/www/ap20/yggdrasil"
if [[ "$AP20_WEBPATH" == "" ]]; then
    echo "$0 requires AP20_WEBPATH environment to be set (location of your deployed code distro)"
    exit 0
fi
echo "##==== $0: Whole dir diff for Yggdrasil webapp"
echo "-- from   distro[$DISTRO]"
echo "-- to   deployed[$AP20_WEBPATH]"
diff -Naur -X rsync.phpapp.dep2repo.exclusions ../src/www/ap20/yggdrasil $AP20_APP > $OUT 2>&1
grep '^--- ' $OUT > $OUT2
ls -la $OUT $OUT2
