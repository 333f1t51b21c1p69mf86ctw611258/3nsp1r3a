#! /bin/bash
. ./env.sh

if [ ! -z "$DEBUG" ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"
fi

if [ $# -ne 8 ]; then
    echo "Usage: $0 <dn> <username> <title> <mail> <department> <name> <usertype> <manager>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

# no ID since KeyID=dn 
DN="$1"
USERNAME="$2"
TITLE="$3"
MAIL="$4"
DEPARTMENT="$5"
NAME="$6"
USERTYPE="$7"
MANAGER="$8"

TS=$(date +%y%m%d_%H%M%S)
USER_INSTANCE_CSV=/tmp/_userinst_${TS}.csv
SCRIPT_LOG=/tmp/admin_update_user.log

echo '"DN","username","title","mail","department","name","usertype","manager"' > $USER_INSTANCE_CSV
#FIXME
#No space allowed as CSV given to convUsertableToLdif does not allow double quote
echo "\"$DN\",\"$USERNAME\",\"$TITLE\",\"$MAIL\",\"$DEPARTMENT\",\"$NAME\",\"$USERTYPE\",\"$MANAGER\"" >> $USER_INSTANCE_CSV

$PYTHONEXEPATH/python adminLoadUser.py $USER_INSTANCE_CSV > $SCRIPT_LOG 2>&1

