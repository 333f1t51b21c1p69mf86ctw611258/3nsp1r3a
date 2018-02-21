#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_App32.py $SCRIPTROOT/../projects/data/App32_data.xlsx 

mysql -u root genericdata -e "update attrapp32s set creator_id = 'test1', created_at = CURTIME()"
