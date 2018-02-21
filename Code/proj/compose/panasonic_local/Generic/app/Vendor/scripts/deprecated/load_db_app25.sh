#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_App25.py $SCRIPTROOT/../projects/data/App25_data.xlsx 

mysql -u root genericdata -e "update attrapp25s set creator_id = 'test1', created_at = CURTIME()"
