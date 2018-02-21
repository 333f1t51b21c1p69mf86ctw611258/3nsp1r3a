#! /bin/bash
. ./env.sh

#bash load_db_app9.sh
#bash load_db_app16.sh
#bash load_db_app20.sh
#bash load_db_app25.sh
#bash load_db_app27.sh
#bash load_db_app28.sh
#bash load_db_app32.sh
#bash load_db_app35.sh
$PYTHONEXEPATH/python3 import_excel_data.sh App9 
$PYTHONEXEPATH/python3 import_excel_data.sh App16 
$PYTHONEXEPATH/python3 import_excel_data.sh App20
$PYTHONEXEPATH/python3 import_excel_data.sh App25
$PYTHONEXEPATH/python3 import_excel_data.sh App27
$PYTHONEXEPATH/python3 import_excel_data.sh App28
$PYTHONEXEPATH/python3 import_excel_data.sh App32
$PYTHONEXEPATH/python3 import_excel_data.sh App35

$MYSQLPATH/mysql -h $DBHOST -u root genericdata < ../projects/data/gpdtl.sql
$MYSQLPATH/mysql -h $DBHOST -u root genericdata < ../projects/data/qtdtl.sql
$MYSQLPATH/mysql -h $DBHOST -u root genericdata < ../projects/data/sbdtl.sql

bash load_padding_app25.sh

$MYSQLPATH/mysql -h $DBHOST -u root genericdata -e 'drop view target;'
$MYSQLPATH/mysql -h $DBHOST -u root genericdata -e 'drop view worksheet;'
$MYSQLPATH/mysql -h $DBHOST -u root genericdata -e 'create view target as select * from attrapp25s;'
$MYSQLPATH/mysql -h $DBHOST -u root genericdata -e 'create view target as select * from attrapp25s;'
$MYSQLPATH/mysql -h $DBHOST -u root genericdata -e 'create view worksheet as select * from attrapp26s;'

bash postDeploy_panasonic.sh

