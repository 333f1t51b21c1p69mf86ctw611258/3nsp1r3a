#! /bin/bash
# restore openldap database

. ./env.sh

if [ $# -ne 1 ]; then
    echo  "Usage: $0 <backup_timestamp>"
    exit 1
fi

CURRENT_TS=`date +'%y%m%d-%H%M%S'`

BACKUP_TS=$1
CONFIG_FQFN="$LDAP_BACKUP_DIR/${BACKUP_TS}.cn=config.master.ldif"
DATABASE_FQFN="$LDAP_BACKUP_DIR/${BACKUP_TS}.database.ldif"
LDAP_CONFIG_DIR=/etc/ldap
LDAP_DB_DIR=/var/lib
SLAPD_DIR=slapd.d
LDAPDB_DIR=ldap

service slapd stop

cd $LDAP_CONFIG_DIR
mv $SLAPD_DIR ${SLAPD_DIR}.backup.${CURRENT_TS}
mkdir $SLAPD_DIR
cd $SCRIPTROOT
slapadd -F $LDAP_CONFIG_DIR/$SLAPD_DIR -b cn=config -l $CONFIG_FQFN
chown -R openldap:openldap $LDAP_CONFIG_DIR/$SLAPD_DIR

#slapcat -b cn=config | grep "^dn: olcDatabase=\|^olcSuffix\|^olcDbDirectory"
cd $LDAP_DB_DIR
mv $LDAPDB_DIR ${LDAPDB_DIR}.backup.${CURRENT_TS}
mkdir $LDAPDB_DIR
cd $SCRIPTROOT
slapadd -F $LDAP_CONFIG_DIR/$SLAPD_DIR -b dc=enspirea,dc=com -l $DATABASE_FQFN
chown -R openldap:openldap $LDAP_DB_DIR/$LDAPDB_DIR

service slapd start

