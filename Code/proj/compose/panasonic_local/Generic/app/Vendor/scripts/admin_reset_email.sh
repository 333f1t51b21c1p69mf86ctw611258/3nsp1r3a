#! /bin/bash
. ./env.sh

$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "update users set mail='enspirea.dev@gmail.com'"

