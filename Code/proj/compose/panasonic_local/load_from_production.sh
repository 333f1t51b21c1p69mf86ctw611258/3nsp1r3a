#! /bin/bash

# run this script in briode_base folder

if [ $# -ne 1 ]; then
    echo "usage: $0 <proj_name>"
    exit 1
fi

PROJ_NAME=$1

HOST_URL=${PROJ_NAME}.briode.com
SSH_BASE="ssh -o StrictHostKeyChecking=no -i ~/PKI/enspirea2.pem ubuntu@${HOST_URL}"
SCP_BASE="scp -o StrictHostKeyChecking=no -i ~/PKI/enspirea2.pem"


# copy attachments from production
$SSH_BASE "cd /home/ubuntu/git/docker/compose/$PROJ_NAME/share; tar cvf /tmp/briode.tar App*/uploads App*/attachments"
$SCP_BASE ubuntu@${HOST_URL}:/tmp/briode.tar share
exit 1
# backup ldap from production
#LDAP_BACKUP_DIR=./backup/ldap
#$SSH_BASE "sudo /usr/sbin/slapcat -b cn=config -l /tmp/conf.ldif; sudo /usr/sbin/slapcat -v -l /tmp/database.ldif"
#$SCP_BASE ubuntu@${HOST_URL}:/tmp/conf.ldif $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-config
#$SCP_BASE ubuntu@${HOST_URL}:/tmp/database.ldif $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-data
#gzip $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-config
#gzip $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-data
#cp $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-config.gz loader
#cp $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-data.gz loader

# backup redis from production
#$SSH_BASE "s-cli bgrewriteaof; sudo cp /var/lib/redis/appendonly.aof /tmp; sudo chmod a+r /tmp/appendonly.aof"
$SCP_BASE ubuntu@${HOST_URL}:/home/ubuntu/git/docker/compose/$PROJ_NAME/redis/appendonly.aof loader
cp loader/appendonly.aof redis
#fi

# backup mysql from production
echo "dumping mysql"
$SSH_BASE docker exec -it $PROJ_NAME mysqldump -h mysql -u root genericdata --result-file /loader/pana_mysql.sql
$SCP_BASE ubuntu@${HOST_URL}:/home/ubuntu/git/docker/compose/$PROJ_NAME/loader/pana_mysql.sql loader/genericdata.sql

