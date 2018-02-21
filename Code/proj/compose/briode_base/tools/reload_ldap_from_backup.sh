#! /bin/bash

if [ $# -ne 2 ]; then
    echo "usage: $0 <project_name> <date_to_restore>"
    exit 1
fi

PROJECT=$1
LDAP_INSTID=${PROJECT}_ldap_1
TS=$2
DATA_TS=${TS}T040001
CONFIG_TS=${TS}T050001
BACKUP_FN=${PROJECT}_${TS}.tar.gz
CONFIG_FQFN=${CONFIG_TS}-config.gz
DATABASE_FQFN=${DATA_TS}-data.gz

# restore file from s3
cd ../../${PROJECT}
aws s3 cp s3://enspireacom/Public/docker/backup/$BACKUP_FN .
tar xvf ${BACKUP_FN}

# restore backup from loader/ldap
docker exec -i $LDAP_INSTID slapd-restore 0 $CONFIG_FQFN
docker exec -i $LDAP_INSTID slapd-restore 1 $DATABASE_FQFN

