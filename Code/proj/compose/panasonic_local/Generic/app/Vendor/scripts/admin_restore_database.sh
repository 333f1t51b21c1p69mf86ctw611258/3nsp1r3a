#! /bin/bash
. ./env.sh

if [ $# -ne 1 ]; then
    "Usage $0 <snapshot_timestamp>"
    exit 1
fi

BACKUP_TS=$1
BACKUP_FILENAME=${BACKUP_TS}.sql

# backup current snapshot
bash admin_backup_database.sh

# restore given snapshot
cp $DB_BACKUP_DIR/${BACKUP_FILENAME}.gz /tmp
gzip -d /tmp/${BACKUP_FILENAME}.gz
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME < $DB_BACKUP_DIR/$BACKUP_FILENAME
