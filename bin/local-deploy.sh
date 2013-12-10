#!/bin/bash

# for deploying repo contents when pulled from code repo

THIS_DISTRO="ap20-ands"
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

# this will source /etc./environment and defines functions which we use here
. bash.functions

# source the variables needed for installation/deployment
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
(for e in ${YGGDEP_EXCLUDED_HOSTS[@]}; do [[ "$e" == $HOSTNAME ]] && exit 0; done) && exit 2 || echo Deploying $THIS_DISTRO on $HOSTNAME

# check that the runtime environment variables have been set
if [[ -f ../src/etc/environment ]]; then
   env_vars=`perl -ne 'print if (s/^export (\S+)=.+\n/\1 /);' ../src/etc/environment`
   if ! [[ "$env_vars" == "" ]]; then
       checkenv "$env_vars"
   fi
fi

if [[ "$1" == "check" ]]; then
   envinfo AP20
   echo "-- [check] was specified. Not proceeding."
   exit 0
fi

# create empry semaphore only if its not already present
if ! [ -f /srv/.first ];then
   sudo touch /srv/.first
   sudo chown ${YGGDEP_WEBUSER}:${YGGDEP_WEBUSER} /srv/.first
   sudo chmod 600 /srv/.first
   echo "-- initiased semaphore /srv/.first"
fi
# ensure dirs exist

makedir $YGGDEP_WEBLOGS                   ${YGGDEP_WEBUSER}:${YGGDEP_GROUP} 775

makedir $YGGDEP_WEBWORK                   root:root                         755
makedir $YGGDEP_WEBWORK/ap20              ${YGGDEP_WEBUSER}:${YGGDEP_GROUP} 775
makedir $YGGDEP_WEBWORK/ap20/bin          $YGGDEP_DEFAULT_PERMS             775
makedir $YGGDEP_WEBWORK/ap20/db_init      $YGGDEP_DEFAULT_PERMS             775
makedir $YGGDEP_WEBWORK/ap20/db_init/demo $YGGDEP_DEFAULT_PERMS             775
makedir $YGGDEP_WEBWORK/ap20/db_init/logs $YGGDEP_DEFAULT_PERMS             775
makedir $YGGDEP_WEBWORK/ap20/export       $YGGDEP_DEFAULT_PERMS             775
makedir $YGGDEP_WEBWORK/ap20/export/xml   $YGGDEP_DEFAULT_PERMS             775
 
makedir $YGGDEP_WEBROOT/documentation     ${YGGDEP_WEBUSER}:${YGGDEP_GROUP} 775
makedir $YGGDEP_WEBROOT/test              $YGGDEP_DEFAULT_PERMS             775

# copy files

cd $DIR
copy ../src/cgi-bin/first              $YGGDEP_CGIBIN_DIR/first         $YGGDEP_DEFAULT_PERMS 775 "$1"

# src/usr-local-bin
copy ../src/usr-local-bin/ap20init.sh  /usr/local/bin/ap20init.sh       root:root             700 "$1"
for F in ../src/usr-local-bin/*
do
   B=`basename $F`
   if ! [ "$B" == "ap20init.sh" ]; then copy $F /usr/local/bin/$B $YGGDEP_DEFAULT_PERMS 775 "$1"; fi
done

# src/webwork
for F in ../src/webwork/bin/*
do
   copy $F $YGGDEP_WEBWORK/ap20/bin $YGGDEP_DEFAULT_PERMS 775 "$1"
done

# src/www
copy ../src/www/index-first.html       $YGGDEP_WEBROOT/index-first.html root:root             644 "$1"
copy ../src/www/index-app.html         $YGGDEP_WEBROOT/index-app.html   root:root             644 "$1"

# xquery
for F in ../src/xml/xquery-lib/*
do
   B=`basename $F`
   copy $F $YGGDEP_XQUERYLIB/$B $YGGDEP_DEFAULT_PERMS 664 "$1"
done

# manual copies/ancilliary web stuff
DRYRUN="-n"
if ! [ "$1" == "" ]; then
   DRYRUN=""
fi
echo ""
echo "-- (web support files) rsync $DRYRUN -vax ../src/www/css $YGGDEP_WEBROOT"
sudo rsync $DRYRUN -vax ../src/www/css $YGGDEP_WEBROOT                  
echo ""
echo "-- (demo database files) rsync $DRYRUN -vax ../src/webwork/db_init/demo $YGGDEP_WEBWORK/ap20/db_init"
sudo rsync $DRYRUN -vax ../src/webwork/db_init/demo $YGGDEP_WEBWORK/ap20/db_init

# make a bunch of symlinks for the xquery modules
cd $YGGDEP_XQUERYLIB
symlink ap20        yggdrasil-load-2012-lib.xq
symlink bdm         bdmUtils-1.0.xq
symlink condig      vdlbdm-load-2013-lib.xq
symlink dd          ddUtils-1.0.xqm
symlink fr          fasrecode-1.0.xqm
symlink fu          fasutil-1.0.xqm
symlink fui         fasuserinterface-1.0.xqm
symlink functx      functx-1.0.xqm
symlink psql        psql-1.0.xqm
symlink ssana       spreadsheet-ana-utils.xqm
symlink str-compare str-compare-1.0.xqm
symlink vjs-ss      vjs-spreadsheet-ana-utils.xqm

echo "# eoj"

