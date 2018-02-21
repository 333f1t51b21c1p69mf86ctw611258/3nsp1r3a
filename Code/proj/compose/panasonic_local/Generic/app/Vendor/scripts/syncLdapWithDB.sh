#! /bin/sh

. ./env.sh

cd $SCRIPTROOT

$PYTHONEXEPATH/python readUsers.py $UPLOADROOT/uploadUserlist.xlsx > $USERINFOPATH/$USERTABLE_CSV_FILENAME

#$PYTHONEXEPATH/python loadUser.py $USERINFOPATH/$USERTABLE_CSV_FILENAME
./createLDAPTree.sh $LDAPHOST

$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedUsers.csv $DEFAULT_USER_PASSWORD

