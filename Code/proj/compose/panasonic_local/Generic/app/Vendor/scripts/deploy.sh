#! /bin/bash

# usage 
# deploy.sh [-i]
#   -i:  initialize database

. ./env.sh

if [ $# -lt 2 ]; then
    echo "Usage: $0 <project_name> <init(optional)>"
    exit 1
fi

PROJECT_NAME=$1
INIT=$2

export PATH=$PATH:/usr/bin:/usr/local/bin
echo $PATH

cd $SCRIPTROOT
if ! [ -z "$INIT" ]; then
#if [ $1 = "-i" ]; then
echo 'initializing database'
$XAMPPBINPATH/mysql -h $DBHOST -u root	      < $SQLPATH/dropdb.sql
$XAMPPBINPATH/mysql -h $DBHOST -u root       < $SQLPATH/createdb.sql
$XAMPPBINPATH/mysql -h $DBHOST -u root genericdata < $SQLPATH/briodesessions.sql
$XAMPPBINPATH/mysql -h $DBHOST -u root $RINGIDBNAME < ../../Config/Schema/db_acl.sql
fi
#fi

$PYTHONEXEPATH/python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH users          > $SQLPATH/_users.sql
$PYTHONEXEPATH/python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH attribute_event_logs > $SQLPATH/_attributeeventlogs.sql
$PYTHONEXEPATH/python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH workflow_event_logs > $SQLPATH/_workfloweventlogs.sql
$PYTHONEXEPATH/python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH user_event_logs > $SQLPATH/_usereventlogs.sql

$XAMPPBINPATH/mysql -h $DBHOST -u root $RINGIDBNAME < $SQLPATH/_users.sql
$XAMPPBINPATH/mysql -h $DBHOST -u root $RINGIDBNAME < $SQLPATH/_attributeeventlogs.sql
$XAMPPBINPATH/mysql -h $DBHOST -u root $RINGIDBNAME < $SQLPATH/_workfloweventlogs.sql
$XAMPPBINPATH/mysql -h $DBHOST -u root $RINGIDBNAME < $SQLPATH/_usereventlogs.sql

$PYTHONEXEPATH/python readUsers.py $USERINFOPATH/Users.xlsx > $USERINFOPATH/$USERTABLE_CSV_FILENAME

$PYTHONEXEPATH/python loadUser.py $USERINFOPATH/$USERTABLE_CSV_FILENAME
bash ./createLDAPTree.sh $LDAPHOST

$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedUsers.csv $DEFAULT_USER_PASSWORD
$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedAdminUsers.csv $DEFAULT_ADMIN_PASSWORD

rm -f ../../Config/bootstrap.php
rm -f ../../Event/AttributeChangeLogger.php
rm -f ../../Event/WorkflowChangeLogger.php
redis-cli -h $REDISHOST flushall


# composer
#cd $SCRIPTROOT/../
#./composer.phar update
#cd $SCRIPTROOT

$PYTHONEXEPATH/python3 setup_proj.py $PROJECT_NAME
#bash setup_proj.sh $PROJECT_NAME

chmod a+w $USERINFOPATH/$USERTABLE_CSV_FILENAME
chmod a+w $USERINFOPATH
chmod a+w $USERINFOPATH/*.ldif
chmod a+w $USERINFOPATH/*.csv
chmod -R a+w ../../tmp
chmod -R a+w ../../webroot/images
