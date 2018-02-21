#! /bin/bash
. ./env.sh

if [ ! -z ${DEBUG} ]; then
    ARGS=""
    for i in $(seq 1 $#); do
        eval "ARG=\${$i}"
        ARGS="${ARGS} \"${ARG}\""
    done
    echo "$0 $ARGS"

fi

if [ $# -ne 3 ]; then
    echo "Usage: $0 <dn> <username> <password>"
    exit 1
fi

if [ ! -z ${DEBUG} ]; then
    exit 1
fi

LDAPHOST=$LDAPHOST
DN=$1
USERNAME=$2
PASSWORD=$3
COMMAND_LOG=/tmp/admin_reset_password.log
TS=$(date +%y%m%d_%H%M%S)
DN_CSV=/tmp/_resetpw_user_inst_${TS}.csv

echo "DN" > $DN_CSV
echo \"$DN\" >> $DN_CSV
$PYTHONEXEPATH/python resetAllPasswords.py $DN_CSV $PASSWORD > $COMMAND_LOG 2>&1

echo "Password reset successfully. New password for $USERNAME: $PASSWORD"

