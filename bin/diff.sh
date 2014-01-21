#!/bin/bash

OUT="/tmp/diff.log"
OUT2="/tmp/diff-summary.log"
DISTRO="../src/www/ap20/yggdrasil"
if [[ "$AP20_APP" == "" ]]; then
    echo "$0 requires AP20_APP environment to be set (location of deployed distro)"
    exit 0
fi
echo "##==== $0: Whole dir diff for Yggdrasil webapp"
echo "-- from distro[$DISTRO]"
echo "-- to deployed[$AP20_APP]"
diff -Naur -X rsync.phpapp.dep2repo.exclusions ../src/www/ap20/yggdrasil $AP20_APP > $OUT 2>&1
grep '^--- ' $OUT > $OUT2
ls -la $OUT $OUT2
