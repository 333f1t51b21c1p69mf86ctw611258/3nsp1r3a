#! /bin/bash
. ./env.sh

if [ ! -z ${DEBUG} ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"
fi

if [ $# -ne 2 ]; then
    echo "Usage: $0 <appname> <id>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

# backup will be invoked in delete_dbapp entry script
# bash admin_backup_database.sh

APPNAME=$1
WF_TABLE='wf'${1,,}'s'
DBAPP_ID=$2

# delete entry from both workflow and dbapp tablesj
#echo "bash admin_delete_dbapp_entry.sh $APPNAME $DBAPP_ID"
bash admin_delete_dbapp_entry.sh $APPNAME $DBAPP_ID

#echo "$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e delete from $WF_TABLE where subject_id=$DBAPP_ID;"
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "delete from $WF_TABLE where subject_id=$DBAPP_ID;"
