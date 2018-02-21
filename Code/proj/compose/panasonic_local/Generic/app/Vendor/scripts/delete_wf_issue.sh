#! /bin/bash
. ./env.sh

if [ $# -ne 2 ]; then
    echo "Usage: $0 <wf_app_no> <issue_id>"
    exit 1
fi

BACKUP_DIR_BASE=/home/ubuntu/update
TS=`date +%Y%m%d-%H%M%S`
TS_DATE=`date +%Y%m%d`
BACKUP_DIR=$BACKUP_DIR_BASE/$TS_DATE
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir $BACKUP_DIR
fi

$MYSQLPATH/mysqldump -h $DBHOST -u $DBUSER $DBNAME > $BACKUP_DIR/${DBNAME}_${TS}.sql

WF_TABLE='wfapp'$1's'
DB_TABLE='attrapp'$1's'
DBAPP_ID=$2
#WFAPP_ID=`$MYSQLPATH/mysql -u $DBUSER $DBNAME -e "select id,subject_id from $WFTABLE where subject_id='$SUBJECT_ID';" | awk '{print $1}'`

#$MYSQLPATH/mysql -u $DBUSER $DBNAME -e "select id,subject_id,creator_id from $WF_TABLE where subject_id=$DBAPP_ID;"
#$MYSQLPATH/mysql -u $DBUSER $DBNAME -e "select id,creator_id from $DB_TABLE where id=$DBAPP_ID;"
#exit 1

#delete workflow table
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "delete from $WF_TABLE where subject_id=$DBAPP_ID;"
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "delete from $DB_TABLE where id=$DBAPP_ID;"

