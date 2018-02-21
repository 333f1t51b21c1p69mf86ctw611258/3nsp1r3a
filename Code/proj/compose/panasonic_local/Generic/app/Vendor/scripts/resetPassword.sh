#! /bin/sh
. ./env.sh
if [ $# -ne 3 ]; then
    echo "Usage: $0 <hostname> <DN> <newPassword>"
    exit 1
fi

$XAMPPBINPATH/ldappasswd -x -h $1 -D "$LDAPADMINUSER" -w $LDAPADMINPASSWORD -s $3 "$2"


