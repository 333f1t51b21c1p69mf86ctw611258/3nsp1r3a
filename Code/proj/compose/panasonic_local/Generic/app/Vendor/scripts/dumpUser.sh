#! /bin/sh
. ./env.sh
cd $SCRIPTROOT
$PYTHONEXEPATH/python exportUser.py > $USERINFOPATH/$USERTABLE_CSV_FILENAME
