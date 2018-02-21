#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_App28.py $SCRIPTROOT/../projects/data/App28_data.xlsx 

mysql -u root genericdata -e "update attrapp28s set creator_id = 'test1', created_at = CURTIME()"
