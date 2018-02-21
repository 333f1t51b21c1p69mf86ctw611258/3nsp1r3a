#! /bin/bash
. ./env.sh

$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "drop view $DBNAME.vwfapp2s;"
# TODO
# add columns as b.X when adding new main view columns
$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "create view $DBNAME.vwfapp2s as select a.*, b.Ship_to, b.Requested_by from wfapp2s a left join attrapp2s b on a.subject_id=b.id;"

