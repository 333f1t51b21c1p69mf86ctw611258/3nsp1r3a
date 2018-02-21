#! /bin/bash
. ./env.sh
if [ $# -ne 1 ]; then
    "Usage $0 <snapshot_timestamp>"
    exit 1
fi

BACKUP_TS=$1
BACKUP_FILENAME=${BACKUP_TS}.rdp

# backup current snapshot
bash admin_backup_redis.sh

# restore with given snapshot
cp $REDIS_BACKUP_DIR/${BACKUP_FILENAME}.gz /tmp
gzip -d /tmp/${BACKUP_FILENAME}.gz
service redis-server stop
cp /tmp/${BACKUP_FILENAME} /var/lib/redis/dump.rdb
chown redis:redis /var/lib/redis/dump.rdb
service redis-server start
