#! /bin/bash
. ~/.bashrc

#export DEBUG=1

export REDISHOST=redis
export LDAPHOST=ldap
export LDAPADMINPASSWORD=briodeRocks
export LDAPADMINUSER=cn=admin,dc=enspirea,dc=com
export XAMPPBINPATH=/usr/bin/
#export LD_LIBRARY_PATH=/usr/lib/
export BIRTWORKROOT=/home/enspirea/workspace/Test

### make sure python is picked up from /usr/bin
export PYTHONEXEPATH=/usr/bin

export ADMINBACKUP=../../../backups/adminui

### third party location
export OPENLDAPSCHEMADIR=../ldap/schema
export MYSQLPATH=$XAMPPBINPATH

### ringi configuration
export SCRIPTROOT=`realpath ../scripts`
export BUDGETROOT=../budget
export SCHEMAPATH=../db/schema
export USERINFOPATH=../user
export SQLPATH=../db
export UPLOADROOT=../../uploads
export REPORTROOT=$UPLOADROOT/reports

### data source file names
ATTACHMENT_FILENAME=attachment.zip
USERTABLE_CSV_FILENAME=Users.csv

### default system account and passwords
export DEFAULT_USER_PASSWORD=root
export DEFAULT_ADMIN_PASSWORD=r00t

### db configuration
export RINGIDBNAME=genericdata
export DBHOST=mysql
export DBUSER=root
export DBPASSWORD=
export DBNAME=$RINGIDBNAME

### workflow configuration
WORKFLOW_OPTION=dept

### sudo askpass
export SUDO_ASKPASS=../Vendor/scripts/askpass.sh

export BASE_GROUP_ID_APPROVAL=100 # for approval_op_as, create_as
export APP_CONFIG_DIR=./appconfig
export PROJ_CONFIG_DIR=./projects
export EXCEL_IMPORT_DIR=../projects/data
export EXCEL_FORMAT_DIR=../projects/ExcelFormat
export LDAP_BACKUP_DIR=/backup
export DB_BACKUP_DIR=/backup
export REDIS_BACKUP_DIR=/backup

