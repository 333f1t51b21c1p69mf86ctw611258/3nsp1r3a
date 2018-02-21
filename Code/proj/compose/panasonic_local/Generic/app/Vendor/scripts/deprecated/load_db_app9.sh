#! /bin/sh
. ./env.sh

mysql -u root genericdata -e "alter table attrapp9s modify Application varchar(10000);"

