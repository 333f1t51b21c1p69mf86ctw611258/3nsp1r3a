#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python loadXls_Apptest.py ../projects/data/Apptest_data.xlsx 

mysql -u root genericdata -e "update attrapptests set creator_id = 'test1', created_at = CURTIME()"
