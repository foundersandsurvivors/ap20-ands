#! /bin/bash

# upload.sh - export and backup db
# leifbk 2008

BASEDIR=/home/leif/projects/transfer
SCRIPTS=$BASEDIR/scripts
REMOTEHOST=your.remotedomain.tld
DB=exodus
cd $BASEDIR/backup
echo "Backing up the database ..."
pg_dump $DB --file=$DB.sql
gzip -f $DB.sql
SIZE=`stat -c %s $DB.sql.gz`
echo "Sending backup to $REMOTEHOST ($SIZE bytes) ..."
ftp -n $REMOTEHOST < $SCRIPTS/backup.txt
echo "Backing up Exodus ..."
cd /home/leif/public_html
# remove temp files
find . -name "*~" -exec rm {} \;
tar -zcf exodus.tgz exodus
echo "Sending backup of Exodus to $REMOTEOST ..."
ftp -n $REMOTEHOST < $SCRIPTS/exodus.txt
echo "Done."
date -R
echo "Uploaded $SIZE bytes `date -R`" >> $BASEDIR/transfer.log
