#! /bin/sh
. ./env.sh

cd $SCRIPTROOT

if [ "$#" -ne 1 ] ; then
    echo "Usage: $0 <DN>" >&2
    exit 1
fi

$XAMPPBINPATH/ldapdelete -x -h $LDAPHOST -D "$LDAPADMINUSER" -w $LDAPADMINPASSWORD $1


