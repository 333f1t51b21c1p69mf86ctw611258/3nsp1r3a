#! /bin/sh

# usage 
# deploy.sh [-i]
#   -i:  initialize database

. ./env.sh

#$PYTHONEXEPATH/python loadUser.py $USERINFOPATH/$USERTABLE_CSV_FILENAME
./createLDAPTree.sh 192.168.1.8

$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedUsers.csv $DEFAULT_USER_PASSWORD


