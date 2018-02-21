#! /bin/sh

. ./env.sh
cd $SCRIPTROOT

if [ -f $1 ]; then
   cat $1
fi
  

