#! /bin/bash

# reload.sh - leifbk 2007
# downloads db backup and regenerates pgslekt

DB=pgslekt
INFILE=$DB.sql
ARCHIVE=$INFILE.gz
BACKUP=solumsle@solumslekt.org://home/solumsle/www/privat/dbm/backup/$ARCHIVE
BASEDIR=/home/leif/projects/transfer
RESTOREDIR=$BASEDIR/restore

# minimal password protection
# user=`cat ~/.params|cut -f1 -d:`
# passwd=`cat ~/.params|cut -f2 -d:`

pushd $RESTOREDIR
rm *
#wget --user=$user --password=$passwd $BACKUP
scp $BACKUP .
if [ -e "$ARCHIVE" ]
then
    # check if db is being accessed by other processes
    while [[ `ps -ef|grep $DB|grep postgres|wc -l` != 0 ]]
    do
        echo -n "Databasen er i bruk! Avslutt psql før du fortsetter."
        read key
    done
    SIZE=`stat -c %s $ARCHIVE`
    gunzip $ARCHIVE
    dropdb $DB
    createdb --encoding=UNICODE $DB
    psql -U postgres -d $DB -f $INFILE > restore.log 2>&1
    echo "Reloaded $SIZE bytes `date -R`" >> $BASEDIR/transfer.log
else
    echo "$INFILE finnes ikke."
    exit 2
fi
date -R
popd
