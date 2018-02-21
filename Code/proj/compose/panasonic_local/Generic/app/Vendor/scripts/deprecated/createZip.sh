#! /bin/sh

. ./env.sh

# usage createZip.sh <attachment_dir>

cd $1
if [ -f $ATTACHMENT_FILENAME ]; then
    echo 'removing $ATTACHMENT_FILENAME'
    rm $ATTACHMENT_FILENAME
fi

zip $ATTACHMENT_FILENAME *

