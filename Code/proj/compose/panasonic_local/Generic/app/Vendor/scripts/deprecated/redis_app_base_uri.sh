#! /bin/bash 
. ./env.sh

cnt=0
for arg in "$@"
do
  let "cnt += 1"
done

if [ $cnt -lt 1 ]; then
    echo "usage: redis_app_base_url.sh <external_url> <internal_url(optional)"
    exit 1
fi

EXT_URL=$1
INT_URL=$2

redis-cli hset App_base_url external $EXT_URL

if [ -n "$INT_URL" ]; then
    redis-cli hset App_base_url internal $INT_URL
fi
