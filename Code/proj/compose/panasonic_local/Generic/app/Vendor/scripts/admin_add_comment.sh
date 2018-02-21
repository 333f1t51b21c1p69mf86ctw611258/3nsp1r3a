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

if [ $# -ne 3 ]; then
    echo "Usage: $0 <appname> <id> <comment>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

# delete database for a given ID
DB_TABLE='comm'${1,,}'s'
DBAPP_ID=$2
#TIMESTAMP=`date '+%Y-%m-%d %H:%M:%S'`
TIMESTAMP_QUERY="select update_time from attribute_event_logs where subject_id=$DBAPP_ID order by id desc limit 1"
TIMESTAMP=`$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "$TIMESTAMP_QUERY"|awk '{if(NF>1){print}}'`
#echo $TIMESTAMP
CREATOR_ID=admin
COMMENT=$3":"
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "insert into $DB_TABLE (subject_id, created_at, creator_id, comment) values ($DBAPP_ID, '$TIMESTAMP', '$CREATOR_ID', '$COMMENT')"

