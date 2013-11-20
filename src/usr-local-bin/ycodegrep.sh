#!/bin/bash

# ycodegrep.sh : look for string in DEPLOYED ap20 yggdrasil code

# get the yggdrasil repo location and source the local .env from there
. /etc/environment
. $AP20_DISTRO/bin/bash.functions

cd $YGGDEP_PHPAPP
echo "#........................................................................................................."
echo "#                                  search deployed YGGDEP_PHPAPP[$YGGDEP_PHPAPP] for [$1]"
ap20info|grep WEB
for TYPE in sql php js
do
   find . -name "*.$TYPE" -print | xargs agrep -n "$1"
done
echo "#........................................................................................................."
echo "#                                                     search misc (/usr/lib/cgi-bin/first) for [$1]"
agrep -n "$1" /usr/lib/cgi-bin/first

cd $AP20_HOME/work
echo "#........................................................................................................."
echo "#                                                 search AP20_HOME/work[$AP20_HOME/work] for [$1]"
ap20info|grep AP20
for TYPE in xq pl pm sh
do
   find . -name "*.$TYPE" -print | xargs agrep -n "$1"
done
exit 0
