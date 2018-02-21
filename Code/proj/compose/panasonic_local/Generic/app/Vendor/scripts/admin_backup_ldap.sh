#! /bin/bash
# backup openldap database

. ./env.sh

if [ $# -gt 1 ]; then
    echo  "Usage: $0 <backup_timestamp>"
    exit 1
fi

TS=`date +%Y%m%d-%H%M%S`
if [ $# -eq 1 ]; then
    TS=$1
fi

service slapd stop

cd $LDAP_BACKUP_DIR
/usr/sbin/slapcat -b cn=config -l ${TS}.cn=config.master.ldif
/usr/sbin/slapcat -v -l ${TS}.database.ldif

service slapd start

