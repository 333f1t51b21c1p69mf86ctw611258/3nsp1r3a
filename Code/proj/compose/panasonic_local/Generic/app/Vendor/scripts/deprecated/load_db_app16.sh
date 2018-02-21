#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_App16.py $SCRIPTROOT/../projects/data/App16_data.xlsx 

mysql -u root genericdata -e "update attrapp16s set creator_id = 'test1', created_at = CURTIME()"
