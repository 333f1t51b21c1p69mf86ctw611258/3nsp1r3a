#! /bin/bash
LDAP_INSTID=$1
TS=`date +'%y%m%d-%H%M%S'`
docker exec -it $1 slapcat -b cn=config > ${TS}.cn=config.master.ldif
docker exec -it $1 slapcat -v > ${TS}.database.ldif
