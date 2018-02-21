#! /bin/sh
. ./env.sh
$MYSQLPATH/mysql -u root genericdata -e 'delete from users where id>1 and id<89;'
$MYSQLPATH/mysql -u root genericdata -e "update users set manager='Nobuaki Nakamura' where username='test1';"

$MYSQLPATH/mysql -u root genericdata -e "delete from attrapp32s where id>396"

