#! /bin/sh
. ./env.sh
cd $SCRIPTROOT

$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/$USERTABLE_CSV_FILENAME $DEFAULT_USER_PASSWORD

