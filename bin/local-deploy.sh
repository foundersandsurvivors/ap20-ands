#!/bin/bash

# for deploying repo contents when pulled from code repo

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

. bash.functions

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
(for e in ${YGGDEP_EXCLUDED_HOSTS[@]}; do [[ "$e" == $HOSTNAME ]] && exit 0; done) && exit 2 || echo Deploying AP20-Yggdrasil on $HOSTNAME

# create empry semaphore only if its not already present
if ! [ -f /srv/.first ];then
   sudo touch /srv/.first
   sudo chown ${YGGDEP_WEBUSER}:${YGGDEP_WEBUSER} /srv/.first
   sudo chmod 600 /srv/.first
   echo "-- initiased semaphore /srv/.first"
fi
# ensure dirs exist

makedir $YGGDEP_WEBWORK               root:root                         755
makedir $YGGDEP_WEBWORK/ap20          ${YGGDEP_WEBUSER}:${YGGDEP_GROUP} 775
makedir $YGGDEP_WEBROOT/documentation ${YGGDEP_WEBUSER}:${YGGDEP_GROUP} 775
makedir $YGGDEP_WEBROOT/test          $YGGDEP_DEFAULT_PERMS             775

# copy files

cd $DIR
copy ../src/cgi-bin/first             $YGGDEP_CGIBIN_DIR/first         $YGGDEP_DEFAULT_PERMS 775 "$1"
copy ../src/usr-local-bin/ap20init.sh /usr/local/bin/ap20init.sh       root:root             700 "$1"
copy ../src/www/index-first.html      $YGGDEP_WEBROOT/index-first.html root:root             644 "$1"
copy ../src/www/index-app.html        $YGGDEP_WEBROOT/index-app.html   root:root             644 "$1"

# manual copies/ancilliary web stuff
echo ""
echo "-- (web support files) rsync -ax ../src/www/css $YGGDEP_WEBROOT"
rsync -ax ../src/www/css $YGGDEP_WEBROOT                  
