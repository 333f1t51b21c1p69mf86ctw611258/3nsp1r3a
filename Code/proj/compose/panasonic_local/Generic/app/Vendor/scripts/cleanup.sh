#! /bin/sh

# usage 
# cleanup.sh

. ./env.sh

cd $SCRIPTROOT
echo 'initializing database'
$XAMPPBINPATH/mysql -u root	     < $SQLPATH/dropdb.sql
$XAMPPBINPATH/mysql -u root      < $SQLPATH/createdb.sql
$MYSQLPATH/mysql -u root $RINGIDBNAME -e "drop table histories;"
$MYSQLPATH/mysql -u root $RINGIDBNAME -e "drop table attributes;"

$PYTHONEXEPATH/python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH users          > $SQLPATH/_users.sql
$PYTHONEXEPATH/python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH histories      > $SQLPATH/_histories.sql
$PYTHONEXEPATH/python $SCRIPTROOT/convXlsSchemaToSql.py $SCHEMAPATH attributes     > $SQLPATH/_attributes.sql

$XAMPPBINPATH/mysql -u root $RINGIDBNAME < $SQLPATH/_users.sql
$XAMPPBINPATH/mysql -u root $RINGIDBNAME < $SQLPATH/_histories.sql
$XAMPPBINPATH/mysql -u root $RINGIDBNAME < $SQLPATH/_attributes.sql

#$PYTHONEXEPATH/python readUsers.py $USERINFOPATH/Users.xlsx > $USERINFOPATH/$USERTABLE_CSV_FILENAME

#$PYTHONEXEPATH/python loadUser.py $USERINFOPATH/$USERTABLE_CSV_FILENAME
#./createLDAPTree.sh $LDAPHOST

#$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedUsers.csv $DEFAULT_USER_PASSWORD

chmod -R a+w ../../tmp/logs
chmod a+w $USERINFOPATH/$USERTABLE_CSV_FILENAME
chmod a+w $USERINFOPATH/*.ldif
chmod a+w $USERINFOPATH/*.csv

rm ../../uploads/*
