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

bash admin_backup_database.sh

# delete database for a given ID
DB_TABLE='attr'${1,,}'s'
DBAPP_ID=$2

$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "delete from $DB_TABLE where id=$DBAPP_ID;"
