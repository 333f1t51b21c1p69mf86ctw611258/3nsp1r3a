#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_App27.py $SCRIPTROOT/../projects/data/App27_data_2015.xlsx 

mysql -u root genericdata -e "update attrapp27s set creator_id = 'test1', created_at = CURTIME()"
