#! /bin/sh
. ./env.sh

#$PYTHONEXEPATH/python ./exportLdap.py >  ./directorydump.csv
#/Applications/XAMPP/xamppfiles/bin/mysql -u root ringidata < ./mysql.sql > ./output.log

$PYTHONEXEPATH/python ./loadLdap.py $USERINFOPATH/$USERTABLE_CSV_FILENAME $DEFAULT_USER_PASSWORD  > /dev/null
$PYTHONEXEPATH/python ./loadUser.py $USERINFOPATH/$USERTABLE_CSV_FILENAME  > /dev/null
