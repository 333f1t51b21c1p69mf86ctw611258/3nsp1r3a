#! /bin/bash
MYSQLCMD="mysql -h mysql -u root genericdata "

$MYSQLCMD < /loader/genericdata_20180131.sql

# User:password somehow does not allow default NULL
$MYSQLCMD -e "alter table users modify password varchar(255) default NULL;"

redis-cli -h redis hset App_base_url external https://admin-eval.briode.com
