#! /bin/sh
. ./env.sh

cd $SCRIPTROOT

if [ "$#" -ne 1 ]  ; then
    echo "Usage: $0 <hostname>" >&2
    exit 1
fi

./dumpUser.sh
./createLDAPTree.sh $1
