#! /bin/bash
# Backup current database snapshot under the login user's work directory
. ./env.sh

if [ $# -gt 1 ]; then
    echo  "Usage: $0 <backup_timestamp>"
    exit 1
fi

TS=`date +%Y%m%d-%H%M%S`
if [ $# -eq 1 ]; then
    TS=$1
fi
BACKUP_FN=${DBNAME}_${TS}.sql

$MYSQLPATH/mysqldump -h $DBHOST -u $DBUSER $DBNAME > $DB_BACKUP_DIR/$BACKUP_FN
gzip $DB_BACKUP_DIR/$BACKUP_FN


