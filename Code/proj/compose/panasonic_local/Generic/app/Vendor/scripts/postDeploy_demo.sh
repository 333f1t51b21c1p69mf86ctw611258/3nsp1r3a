#! /bin/bash
. ./env.sh

# $PYTHONEXEPATH/python3 import_excel_data.sh App16 

$MYSQLPATH/mysql -h $DBHOST -u root genericdata < ../projects/data/gpdtl.sql
$MYSQLPATH/mysql -h $DBHOST -u root genericdata < ../projects/data/qtdtl.sql
$MYSQLPATH/mysql -h $DBHOST -u root genericdata < ../projects/data/sbdtl.sql

# required for BIRT report demo
$MYSQLPATH/mysql -h $DBHOST -u root mejitest < ../projects/data/gpdtl.sql
$MYSQLPATH/mysql -h $DBHOST -u root mejitest -e "alter table gpdtl change SBranch Branch varchar(12);"
$MYSQLPATH/mysql -h $DBHOST -u root mejitest -e "alter table gpdtl change Invdate date date;"

