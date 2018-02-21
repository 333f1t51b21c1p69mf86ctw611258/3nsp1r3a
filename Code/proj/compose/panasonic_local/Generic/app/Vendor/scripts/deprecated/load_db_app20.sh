#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_App20.py $SCRIPTROOT/../projects/data/App20_data.xlsx 

mysql -u root genericdata -e "update attrapp20s set creator_id = 'test1', created_at = CURTIME()"
