#! /bin/sh
. ./env.sh
if [ $# -ne 1 ]; then
    echo "Usage: $0 <User_Excelfile.xlsx>"
    exit 1
fi

USER_EXCEL=$1

$PYTHONEXEPATH/python readUsers.py $USERINFOPATH/$USER_EXCEL > $USERINFOPATH/$USERTABLE_CSV_FILENAME
$PYTHONEXEPATH/python loadUser.py $USERINFOPATH/$USERTABLE_CSV_FILENAME
bash ./createLDAPTree.sh $LDAPHOST

#$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedUsers.csv $DEFAULT_USER_PASSWORD
#$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedAdminUsers.csv $DEFAULT_ADMIN_PASSWORD

