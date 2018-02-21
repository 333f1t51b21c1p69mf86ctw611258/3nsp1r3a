#! /bin/sh

# usage 
# deploy.sh [-i]
#   -i:  initialize database
. ./env.sh

$MYSQLPATH/mysql -u root $RINGIDBNAME -e "drop table attributes;"
python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH attributes     > $SQLPATH/_attributes.sql
$XAMPPBINPATH/mysql -u root $RINGIDBNAME < $SQLPATH/_attributes.sql
$MYSQLPATH/mysql -u root $RINGIDBNAME -e "alter table attributes ENGINE=MYISAM;"

sudo chmod -R a+w ../../uploads
sudo rm ../../uploads/*
sudo chmod -R a+w ../../webroot/js
sudo rm ../../webroot/js/Generic*

