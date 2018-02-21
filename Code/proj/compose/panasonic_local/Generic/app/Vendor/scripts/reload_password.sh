#! /bin/sh
. ./env.sh

$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedUsers.csv $DEFAULT_USER_PASSWORD
$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedAdminUsers.csv $DEFAULT_ADMIN_PASSWORD

