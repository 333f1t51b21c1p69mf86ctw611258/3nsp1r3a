#! /bin/bash
. ./env.sh

if [ $# -ne 1 ]; then
    echo "Usage: $0 <snapshot_timestamp>"
    exit 1
fi

BACKUP_TS=$1
if [ ! -e "$REDIS_BACKUP_DIR/${BACKUP_TS}.rdp" ]; then
    echo "Snap file for Redis does not exist"
    exit 1
fi
if [ ! -e "$LDAP_BACKUP_DIR/${BACKUP_TS}.ldif" ]; then
    echo "Snap file for LDAP does not exist"
    exit 1
fi
if [ ! -e "$DB_BACKUP_DIR/${BACKUP_TS}.sql" ]; then
    echo "Snap file for MySQL does not exist"
    exit 1
fi

echo 'restoring database...'
#bash admin_restore_database.sh $BACKUP_TS
#bash admin_restore_ldap.sh $BACKUP_TS
#bash admin_restore_redis.sh $BACKUP_TS

