#! /bin/sh
. ./env.sh
cd $SCRIPTROOT
$PYTHONEXEPATH/python loadUser.py $USERINFOPATH/$USERTABLE_CSV_FILENAME
./createLDAPTree.sh $LDAPHOST

