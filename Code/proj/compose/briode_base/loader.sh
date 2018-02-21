#! /bin/bash
MYSQLCMD="mysql -h mysql -u root genericdata "

$MYSQLCMD < /loader/genericdata.sql

# User:password somehow does not allow default NULL
$MYSQLCMD -e "alter table users modify password varchar(255) default NULL;"
