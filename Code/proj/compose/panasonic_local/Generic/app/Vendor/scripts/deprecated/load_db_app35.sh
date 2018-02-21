#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_App35.py $SCRIPTROOT/../projects/data/App35_data.xlsx 

mysql -u root genericdata -e "update attrapp35s set creator_id = 'test1', created_at = CURTIME()"
