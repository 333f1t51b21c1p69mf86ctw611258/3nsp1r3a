#! /bin/bash

# run this script in briode_base folder

if [ $# -ne 1 ]; then
    echo "usage: $0 <proj_name>"
    exit 1
fi

BRIODE_BASE_DIR=../briode_base
PROJ_NAME=$1

HOST_URL=${PROJ_NAME}.briode.com
SSH_BASE="ssh -o StrictHostKeyChecking=no -i ~/PKI/enspirea2.pem ubuntu@${HOST_URL}"
SCP_BASE="scp -o StrictHostKeyChecking=no -i ~/PKI/enspirea2.pem"


if [ 1 -eq 0 ]; then
mkdir -p ../$PROJ_NAME
cd ../$PROJ_NAME
ln -s $BRIODE_BASE_DIR/run.sh
ln -s $BRIODE_BASE_DIR/deploy.sh
ln -s $BRIODE_BASE_DIR/setup.sh
ln -s $BRIODE_BASE_DIR/tools
cp $BRIODE_BASE_DIR/uploads.ini .
cp $BRIODE_BASE_DIR/loader.sh loader
cp $BRIODE_BASE_DIR/docker-compose.template.yml docker-compose.yml

cp -r $BRIODE_BASE_DIR/share .
cp -r $BRIODE_BASE_DIR/redis .
cp -r $BRIODE_BASE_DIR/loader .
cp -r $BRIODE_BASE_DIR/backup .

cp htaccess $BRIODE_BASE_DIR/htaccess
sed -i "s/__PROJNAME__/${PROJ_NAME}/" $BRIODE_BASE_DIR/htaccess

git submodule add -b docker_migration git@bitbucket.org:enspirea/briodecore.git Generic
cd Generic
git submodule init 
git submodule update 
cd ..
#fi

# copy attachments from production
$SSH_BASE "cd /opt/lampp/htdocs/Generic/app/Plugin; tar cvf /tmp/briode.tar App*/uploads App*/attachments"
$SCP_BASE ubuntu@${HOST_URL}:/tmp/briode.tar share
fi

# backup ldap from production
LDAP_BACKUP_DIR=./backup/ldap
$SSH_BASE "sudo /usr/sbin/slapcat -b cn=config -l /tmp/conf.ldif; sudo /usr/sbin/slapcat -v -l /tmp/database.ldif"
$SCP_BASE ubuntu@${HOST_URL}:/tmp/conf.ldif $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-config
$SCP_BASE ubuntu@${HOST_URL}:/tmp/database.ldif $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-data
gzip $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-config
gzip $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-data
cp $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-config.gz loader
cp $LDAP_BACKUP_DIR/ldap${PROJ_NAME}-data.gz loader

# backup redis from production
#$SSH_BASE "redis-cli bgrewriteaof; sudo cp /var/lib/redis/appendonly.aof /tmp; sudo chmod a+r /tmp/appendonly.aof"
#$SCP_BASE ubuntu@${HOST_URL}:/tmp/appendonly.aof loader
#cp loader/appendonly.aof redis
#fi

# backup mysql from production
echo "dumping mysql"
$SSH_BASE "/opt/lampp/bin/mysqldump -u root genericdata > /tmp/mysql.sql"
$SCP_BASE ubuntu@${HOST_URL}:/tmp/mysql.sql loader/genericdata.sql
