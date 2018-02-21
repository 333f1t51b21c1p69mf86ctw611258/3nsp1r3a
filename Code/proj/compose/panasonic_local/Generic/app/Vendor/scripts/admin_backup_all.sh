#! /bin/bash
. ./env.sh

TS=`date +%y%m%d-%H%M%S`

bash admin_backup_redis.sh $TS
#bash admin_backup_ldap.sh $TS
bash admin_backup_database.sh $TS

chmod -R a+r $ADMINBACKUP
