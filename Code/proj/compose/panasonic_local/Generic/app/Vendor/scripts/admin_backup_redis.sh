#! /bin/bash
. ./env.sh

if [ $# -gt 1 ]; then
    echo  "Usage: $0 <backup_timestamp>"
    exit 1
fi

TS=`date +%Y%m%d-%H%M%S`
if [ $# -eq 1 ]; then
    TS=$1
fi

#FILENAME=${TS}.rdp
FILENAME=${TS}.aof

cp /redis/appendonly.aof $REDIS_BACKUP_DIR/$FILENAME
gzip $REDIS_BACKUP_DIR/$FILENAME

