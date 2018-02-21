#! /bin/bash

if [ $# -ne 1 ]; then
    echo "usage: $0 <project_name>"
    exit 1
fi

PROJECT=$1
LDAP_INSTID=${PROJECT}_ldap_1

CONFIG_FQFN=ldap${PROJECT}-config.gz
DATABASE_FQFN=ldap${PROJECT}-data.gz

if [ 1 -eq 0 ]; then
docker stop $LDAP_INSTID
docker rm $LDAP_INSTID

cd ..
docker-compose up -d
cd tools
fi

sleep 3
# restore backup from loader/ldap
docker exec -i $LDAP_INSTID slapd-restore 0 $CONFIG_FQFN
docker exec -i $LDAP_INSTID slapd-restore 1 $DATABASE_FQFN

