#! /bin/bash

#TODO
# supply password from GWT
# DN should not change at update - enforce this 

. ./env.sh

#DEBUG=1
if [ ! -z ${DEBUG} ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"
fi

if [ $# -ne 9 ]; then
    echo "Usage: $0 <dn> <username> <title> <mail> <department> <name> <usertype> <manager> <password>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

DN="$1"
USERNAME="$2"
TITLE="$3"
MAIL="$4"
DEPARTMENT="$5"
NAME="$6"
USERTYPE="$7"
MANAGER="$8"
PASSWORD="$9"

TS=$(date +%y%m%d_%H%M%S)
USER_INSTANCE_CSV=/tmp/_userinst_${TS}.csv
USER_INSTANCE_LDIF=/tmp/_ldif_${TS}.ldif
SCRIPT_LOG=/tmp/admin_create_user.log

rm -f $USER_INSTANCE_CSV

# check if DN or username exists
QUERY_USERNAME="select count(*) from users where username='$USERNAME'"
USERNAME_COUNT=`$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "$QUERY_USERNAME"`
USER_FOUND=`echo $USERNAME_COUNT|awk '{print $2}'`
if [ "$USER_FOUND" != "0" ]; then
    echo "User $USERNAME already exists"
    exit 1
fi

# find next id in users table
USER_CTR=`$MYSQLPATH/mysql -h $DBHOST -u $DBUSER $DBNAME -e "select max(id)+1 as id_next from users"`

# generate csv file
echo '"DN","username","title","mail","department","name","usertype","manager"' > $USER_INSTANCE_CSV
#FIXME
#No space allowed as CSV given to convUsertableToLdif does not allow double quote
echo "\"$DN\",\"$USERNAME\",\"$TITLE\",$MAIL,\"$DEPARTMENT\",\"$NAME\",$USERTYPE,\"$MANAGER\"" >> $USER_INSTANCE_CSV

$PYTHONEXEPATH/python adminLoadUser.py $USER_INSTANCE_CSV > $SCRIPT_LOG 2>&1
$PYTHONEXEPATH/python convUsertableToLdif.py $USER_INSTANCE_CSV > $USER_INSTANCE_LDIF
$XAMPPBINPATH/ldapadd -c -h $LDAPHOST -x -w $LDAPADMINPASSWORD -D "$LDAPADMINUSER" -f $USER_INSTANCE_LDIF >> $SCRIPT_LOG 2>&1 
$PYTHONEXEPATH/python resetAllPasswords.py  $USERINFOPATH/_loadedUsers.csv $PASSWORD >> $SCRIPT_LOG 2>&1

echo "User created with password $PASSWORD"
